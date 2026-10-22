<?php 

// Restringe esta tela a administradores; operações simples ficam nesta página.

require_once __DIR__ . '/../includes/auth.php';
/* A autorização é verificada no servidor antes de consultar ou alterar os cadastros. Esconder o link
 * de administração no menu não impediria o acesso pela URL. */
exigirAdmin();
require_once __DIR__ . '/../includes/functions.php';

$mensagem = '';
$curso_edicao = null;
// O token CSRF compara o valor enviado ao guardado nesta sessão, ajudando a recusar envios forjados; não é uma garantia isolada da origem do pedido.
if (empty($_SESSION['csrf_cursos'])) {
    /* Gera um token aleatório para proteger o formulário contra CSRF. */
    $_SESSION['csrf_cursos'] = bin2hex(random_bytes(32));
}

// Trata a exclusão de um curso solicitada pelo formulário.

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'excluir') {
    $token = $_POST['csrf_token'] ?? '';
    /* Confere o token CSRF antes de processar o formulário. */
    if (!hash_equals($_SESSION['csrf_cursos'], (string)$token)) {
        $mensagem = '<div class="alert alert-danger">Falha na validação de segurança (token expirado). Tente novamente.</div>';
    } else {
        $id_excluir = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if ($id_excluir && $conexao) {
            try {
                $stmt = $conexao->prepare('DELETE FROM cursos WHERE id = :id');
                /* Lê quantas linhas a última alteração afetou. Neste teste, sucesso de execute()
                 * sozinho não prova que o ID procurado realmente encontrou um registro. */
                if ($stmt->execute(['id' => $id_excluir]) && $stmt->rowCount() > 0) {
                    $mensagem = '<div class="alert alert-success">Curso excluído com sucesso!</div>';
                } else {
                    $mensagem = '<div class="alert alert-danger">Curso não encontrado ou já excluído.</div>';
                }
            } catch (PDOException $e) {
                error_log('Erro ao excluir curso: ' . $e->getMessage());
                $mensagem = '<div class="alert alert-danger">Erro ao excluir curso. Verifique se ele não possui matrículas ou aulas vinculadas.</div>';
            }
        } else {
            $mensagem = '<div class="alert alert-danger">Identificador de curso inválido.</div>';
        }
    }
}

// Busca os dados do curso escolhido para preencher o formulário de edição.
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($conexao && $id) {
        $stmt = $conexao->prepare('SELECT * FROM cursos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $curso_edicao = $stmt->fetch();
    }
}

// Valida os campos e grava um curso novo ou atualiza o curso existente.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') !== 'excluir') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_cursos'], (string)$token)) {
        $mensagem = '<div class="alert alert-danger">Falha na validação de segurança (token expirado). Tente novamente.</div>';
    } else {
        $id = $_POST['id'] ?? null;
        $nome = $_POST['nome'] ?? '';
        $categoria = $_POST['categoria'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $carga_horaria = $_POST['carga_horaria'] ?? 0;
        $ativo = $_POST['ativo'] ?? 'true';
        if (!$conexao) {
            $mensagem = '<div class="alert alert-danger">Não foi possível acessar o banco de dados.</div>';
        } elseif (!empty($nome) && !empty($categoria) && $carga_horaria > 0) {
            try {
                if ($id) {
                    $stmt = $conexao->prepare(
                        'UPDATE cursos
                         SET nome = :nome, categoria = :categoria, descricao = :descricao,
                             carga_horaria = :carga_horaria, ativo = :ativo
                         WHERE id = :id'
                    );
                    $salvo = $stmt->execute([
                        'nome' => trim($nome),
                        'categoria' => $categoria,
                        'descricao' => trim($descricao),
                        'carga_horaria' => (int) $carga_horaria,
                        /* O select envia o texto true ou false. A comparação transforma o texto
                         * recebido em um booleano para o parâmetro ativo; não se usa (bool) porque a
                         * string não vazia false também seria verdadeira no PHP. */
                        'ativo' => $ativo === 'true',
                        'id' => (int) $id
                    ]);
                    if ($salvo) {
                        $mensagem = '<div class="alert alert-success">Curso atualizado com sucesso!</div>';
                        $curso_edicao = null;
                    } else {
                        $mensagem = '<div class="alert alert-danger">Erro ao atualizar curso.</div>';
                    }
                } else {
                    $stmt = $conexao->prepare(
                        'INSERT INTO cursos (nome, categoria, descricao, carga_horaria, ativo)
                         VALUES (:nome, :categoria, :descricao, :carga_horaria, :ativo)'
                    );
                    $salvo = $stmt->execute([
                        'nome' => trim($nome),
                        'categoria' => $categoria,
                        'descricao' => trim($descricao),
                        'carga_horaria' => (int) $carga_horaria,
                        /* O select envia o texto true ou false. A comparação transforma o texto
                         * recebido em um booleano para o parâmetro ativo; não se usa (bool) porque a
                         * string não vazia false também seria verdadeira no PHP. */
                        'ativo' => $ativo === 'true'
                    ]);
                    if ($salvo) {
                        $mensagem = '<div class="alert alert-success">Novo curso cadastrado com sucesso!</div>';
                    } else {
                        $mensagem = '<div class="alert alert-danger">Erro ao cadastrar curso.</div>';
                    }
                }
            } catch (PDOException $e) {
                error_log('Erro ao salvar curso: ' . $e->getMessage());
                /* O código 23505 indica violação de unicidade, como e-mail ou ordem de questão
                 * duplicados. */
                if ($e->getCode() === '23505') {
                    $mensagem = '<div class="alert alert-warning">Já existe um curso cadastrado com este nome.</div>';
                } else {
                    $mensagem = '<div class="alert alert-danger">Erro ao salvar curso no banco de dados.</div>';
                }
            }
        } else {
            $mensagem = '<div class="alert alert-warning">Preencha Nome, Categoria e Carga Horária válida.</div>';
        }
    }
}

// Carrega os cursos para a tabela de administração.
// Qualquer falha de banco é convertida em uma mensagem amigável para não quebrar a tela administrativa.
$cursos = [];
if ($conexao) {
    try {
        $cursos = $conexao->query('SELECT * FROM cursos ORDER BY id ASC')->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao listar cursos: ' . $e->getMessage());
        $cursos = [];
        $mensagem = '<div class="alert alert-danger">Não foi possível carregar os cursos. Verifique a conexão com o banco e a existência da tabela de cursos.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos | HighTech School</title>
    
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Formulário de cadastro/edição e tabela de cursos cadastrados. -->
<main class="container">
        
        <h2>Gestão de Cursos (Área Restrita - Admin)</h2>
        <p>Esta área é a parte de administração do catálogo. Use a seção de início para visualizar os cursos ativos e a seção de cadastro para adicionar ou editar cursos.</p>

<?php echo $mensagem; ?>

<div class="admin-card">
            
            <h3>Início - Catálogo atual</h3>
            <p>Lista dos cursos cadastrados no sistema.</p>
            <br>
            <p><strong>Observação:</strong> esta seção é a visão geral do início. A parte de cadastro fica separada abaixo.</p>
        </div>

<div class="admin-card">

            <h3><?php /* O ternário condição ? valor_se_verdadeiro : valor_se_falso escolhe um dos dois valores. Já a forma
 * abreviada valor ?: alternativa usa a alternativa para qualquer valor considerado falso. */ echo $curso_edicao ? 'Editar Curso (ID: ' . htmlspecialchars($curso_edicao['id']) . ')' : 'Adicionar / editar curso'; ?></h3>
            <p>Use este formulário para cadastrar um novo curso ou alterar um curso existente.</p>
            <br>
            
            <form action="cursos.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_cursos'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php  if ($curso_edicao): ?>

<input type="hidden" name="id" value="<?php echo htmlspecialchars($curso_edicao['id']); ?>">
                <?php endif; ?>

<div class="form-grid">
                    
                    <div class="form-group">
                        <label for="nome">Nome do Curso: *</label>
                        
                        <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($curso_edicao['nome'] ?? ''); ?>" required>
                    </div>

<div class="form-group">
                        <label for="categoria">Categoria: *</label>
                        
                        <select name="categoria" id="categoria" required>
                            <?php
                            $cat = $curso_edicao['categoria'] ?? '';
                            ?>

<option value="Desenvolvimento" <?php echo ($cat === 'Desenvolvimento') ? 'selected' : ''; ?>>Desenvolvimento</option>
                            <option value="Gestão & Ágil" <?php echo ($cat === 'Gestão & Ágil') ? 'selected' : ''; ?>>Gestão & Ágil</option>
                            <option value="Inovação & PMEs" <?php echo ($cat === 'Inovação & PMEs') ? 'selected' : ''; ?>>Inovação & PMEs</option>
                            <option value="Infraestrutura & BD" <?php echo ($cat === 'Infraestrutura & BD') ? 'selected' : ''; ?>>Infraestrutura & BD</option>
                        </select>
                    </div>

<div class="form-group">
                        <label for="carga_horaria">Carga Horária (Horas): *</label>
                        
                        <input type="number" name="carga_horaria" id="carga_horaria" value="<?php echo htmlspecialchars($curso_edicao['carga_horaria'] ?? 40); ?>" required min="1">
                    </div>

<div class="form-group">
                        <label for="ativo">Status:</label>
                        <select name="ativo" id="ativo">
                            <?php
                            $isAtivo = isset($curso_edicao['ativo']) ? ($curso_edicao['ativo'] === true || $curso_edicao['ativo'] === 't' || $curso_edicao['ativo'] == 1) : true;
                            ?>
                            
                            <option value="true" <?php echo $isAtivo ? 'selected' : ''; ?>>Ativo</option>
                            
                            <option value="false" <?php echo !$isAtivo ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>

<div class="form-group" style="grid-column: span 2;">
                        <label for="descricao">Descrição Detalhada do Curso:</label>
                        
                        <textarea name="descricao" id="descricao" rows="3"><?php echo htmlspecialchars($curso_edicao['descricao'] ?? ''); ?></textarea>
                    </div>
                </div>

<button type="submit" class="btn btn-primary"><?php echo $curso_edicao ? 'Salvar Alterações' : 'Cadastrar Curso'; ?></button>
                <?php if ($curso_edicao): ?>
                    
                    <a href="cursos.php" class="btn btn-outline">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

<div class="admin-card">
            <h3>Catálogo de Cursos no Banco de Dados</h3>
            <?php if (empty($cursos)): ?>
                
                <p>Nenhum curso cadastrado.</p>
            <?php else: ?>
                
                <table class="styled-table">
                    <thead>
                        <tr>
                            
                            <th>ID</th>
                            <th>Curso</th>
                            <th>Categoria</th>
                            <th>Carga Horária</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php foreach ($cursos as $curso): ?>
                            <tr>
                                
                                <td><?php echo htmlspecialchars($curso['id']); ?></td>
                                <td>
                                    
                                    <strong><?php echo htmlspecialchars($curso['nome']); ?></strong><br>
                                    
                                    <small style="color: var(--text-muted);"><?php echo htmlspecialchars($curso['descricao'] ?? ''); ?></small>
                                </td>
                                
                                <td><span class="badge"><?php echo htmlspecialchars($curso['categoria']); ?></span></td>
                                
                                <td><?php echo htmlspecialchars($curso['carga_horaria']); ?>h</td>
                                <td>
                                    <?php
                                    if ($curso['ativo'] === true || $curso['ativo'] === 't' || $curso['ativo'] == 1): ?>
                                        
                                        <span class="badge" style="background-color: #D1FAE5; color: #065F46;">Ativo</span>
                                    <?php else: ?>
                                        
                                        <span class="badge" style="background-color: #FEE2E2; color: #991B1B;">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    
                                    <a href="cursos.php?action=edit&id=<?php echo $curso['id']; ?>" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Editar</a>
                                    
                                    <form action="cursos.php" method="post" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este curso?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?php echo (int) $curso['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_cursos'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.85rem; cursor: pointer;">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
