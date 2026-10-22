<?php

// Carrega autenticação e conexão compartilhadas; esta é a página pública dos cursos.

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$usuario_logado = obterUsuarioLogado();
$mensagem = '';

if (isset($_GET['erro']) && $_GET['erro'] === 'acesso_negado') {
    $mensagem = '<div class="alert alert-warning">Acesso negado: Você precisa ter perfil de Administrador para acessar os painéis de gestão.</div>';
}

if (!$conexao && $mensagem === '') {
    $mensagem = '<div class="alert alert-danger">Não foi possível conectar ao banco. Verifique a configuração e tente novamente.</div>';
}

if (empty($_SESSION['csrf_inscricao'])) {
    /* Gera um token aleatório para proteger o formulário contra CSRF. */
    $_SESSION['csrf_inscricao'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscrever'])) {
    $token = $_POST['csrf_token'] ?? '';
    /* Confere o token CSRF antes de processar o formulário. */
    if (!hash_equals($_SESSION['csrf_inscricao'] ?? '', (string)$token)) {
        $mensagem = '<div class="alert alert-danger">Falha na validação de segurança (token expirado). Atualize a página e tente novamente.</div>';
    } else {
        // Lê e valida os dados enviados no formulário público de inscrição.
        $nome    = $_POST['nome']     ?? '';
        $cpf     = normalizarCpf($_POST['cpf'] ?? '');
        $email   = $_POST['email']    ?? '';
        $id_curso = $_POST['id_curso'] ?? '';
        $turma = 'HT-2026';
        $nasc_input = trim($_POST['nasc'] ?? '');
        $nasc_erro = '';
        /* Esta chamada recebe dois resultados: $nasc guarda data/null/false; a variável de erro
         * passada no segundo argumento recebe a explicação por referência, conforme a declaração da
         * função. */
        $nasc = validarDataNascimento($nasc_input, $nasc_erro);

        // Cria ou localiza o cadastro do aluno e registra a matrícula no curso.
        if ($nasc === false) {
            $mensagem = '<div class="alert alert-warning">' . htmlspecialchars($nasc_erro) . '</div>';
        } else if ($cpf !== '' && !validarCpf($cpf)) {
            $mensagem = '<div class="alert alert-warning">CPF inválido. Digite 11 números, sem pontos ou traços.</div>';
        } else if (!$conexao) {
            $mensagem = '<div class="alert alert-danger">Sem conexão com o banco de dados. Tente novamente mais tarde.</div>';
        } else if (!empty($nome) && !empty($email) && !empty($id_curso)) {
            $aluno_db = buscarAlunoPorEmail($conexao, $email);
            $id_aluno = null;

            if ($aluno_db) {
                $id_aluno = $aluno_db['id'];
            } else {
                if (cadastrarAluno($conexao, $nome, $cpf, $email, $turma, $nasc, true)) {
                    $novo_aluno = buscarAlunoPorEmail($conexao, $email);
                    $id_aluno = $novo_aluno['id'] ?? null;
                }
            }

            if ($id_aluno) {
                $resultado_matricula = matricularAlunoEmCursoAtivo($conexao, $id_aluno, $id_curso);

                if ($resultado_matricula === 'created') {
                    $tem_usuario = buscarUsuarioPorEmail($conexao, $email);
                    if ($tem_usuario) {
                        $mensagem = '<div class="alert alert-success"><strong>Matrícula realizada.</strong> <a href="login/login.php">Entre para acessar suas aulas.</a></div>';
                    } else {
                        $mensagem = '<div class="alert alert-success"><strong>Matrícula realizada.</strong> <a href="login/cadastrar.php">Crie uma conta para acessar as aulas.</a></div>';
                    }
                } elseif ($resultado_matricula === 'already_enrolled') {
                    $mensagem = '<div class="alert alert-warning">Você já possui uma matrícula ativa neste curso. Acesse seu <a href="app/meu_painel.php" style="font-weight: 700; text-decoration: underline;">Painel do Aluno</a> para ver as aulas.</div>';
                } elseif ($resultado_matricula === 'course_unavailable') {
                    $mensagem = '<div class="alert alert-warning">Este curso não está disponível para novas matrículas no momento.</div>';
                } else {
                    $mensagem = '<div class="alert alert-danger">Erro ao realizar inscrição no curso. Tente novamente mais tarde.</div>';
                }
            } else {
                $mensagem = '<div class="alert alert-danger">Erro ao registrar os dados do aluno. Tente novamente.</div>';
            }
        } else {
            $mensagem = '<div class="alert alert-warning">Por favor, preencha todos os campos obrigatórios.</div>';
        }
    }
}

// Carrega somente os cursos ativos para exibi-los no catálogo público.
// Se a tabela de cursos ainda não existir ou o banco estiver num estado inconsistente,
// a página continua funcionando com uma lista vazia e mostra uma mensagem de erro em vez de quebrar.
$cursos = [];
if ($conexao) {
    try {
        $cursos = $conexao->query('SELECT * FROM cursos WHERE ativo = true ORDER BY id ASC')->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao listar cursos ativos: ' . $e->getMessage());
        $cursos = [];
        if ($mensagem === '') {
            $mensagem = '<div class="alert alert-danger">Não foi possível carregar os cursos no momento. Verifique a conexão com o banco e a estrutura do schema.</div>';
        }
    }
}
/* strtotime calcula uma data relativa a hoje; date a formata para o input date. Este max ajuda o
 * navegador a exigir 14 anos, mas a validação real também é feita por validarDataNascimento no
 * servidor. */
$data_max_nasc = date('Y-m-d', strtotime('-14 years'));
/* Calcula a data de 100 anos atrás para o min do campo. Esses limites da interface ajudam o
 * preenchimento e não substituem as regras verificadas pelo PHP. */
$data_min_nasc = date('Y-m-d', strtotime('-100 years'));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>HighTech School - Escola de Tecnologia</title>
    
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Página pública: catálogo de cursos e formulário de inscrição. -->
<div class="hero">
        <div class="container">
            
            <span class="badge">Início</span>
            
            <h2>Conheça os cursos da HighTech School</h2>
            
            <p>Explore os cursos disponíveis e escolha aquele ideal para você.</p>
        </div>
    </div>

<main class="container">
        
        <?php echo $mensagem; ?>

<section id="cursos">
            <div class="section-header">
                <h2>Catálogo de cursos</h2>
                <p>Veja os cursos disponíveis para matrícula.</p>
            </div>

<div class="grid">
                <?php  if (empty($cursos)): ?>
                    
                    <p>Nenhum curso cadastrado no momento.</p>
                <?php else: ?>
                    
                    <?php foreach ($cursos as $curso): ?>
                        
                        <article class="card">
                            <div>
                                
                                <span class="badge"><?php echo htmlspecialchars($curso['categoria']); ?></span>
                                
                                <h3><?php echo htmlspecialchars($curso['nome']); ?></h3>
                                
                                <p><?php echo htmlspecialchars($curso['descricao']); ?></p>
                                
                                <p><strong>Carga Horária:</strong> <?php echo htmlspecialchars($curso['carga_horaria']); ?> horas</p>
                            </div>
                            
                            <a href="#inscricao" class="btn btn-primary">Inscrever-se neste Curso</a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

<section id="inscricao" class="admin-card">
            <h2>Inscrição / matrícula</h2>
            <p style="margin-bottom: 1.5rem;">Preencha seus dados e confirme a matrícula no curso escolhido.</p>

<?php if ($conexao): ?>
<form action="aplicacao.php" method="post">
                
                <input type="hidden" name="inscrever" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_inscricao'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label for="nome">Nome Completo: *</label>
                        
                        <input type="text" name="nome" id="nome" required value="<?php echo htmlspecialchars($usuario_logado['nome'] ?? ''); ?>" placeholder="Digite seu nome">
                    </div>

<div class="form-group">
                        <label for="cpf">CPF:</label>
                        
                        <input type="text" name="cpf" id="cpf" placeholder="00000000000" maxlength="11" inputmode="numeric" pattern="\d{11}" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)">
                    </div>

<div class="form-group">
                        <label for="email">E-mail: *</label>
                        
                        <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($usuario_logado['email'] ?? ''); ?>" placeholder="seu@email.com">
                    </div>

<div class="form-group">
                        <label for="nasc">Data de Nascimento:</label>
                        
                        <input type="date" name="nasc" id="nasc" min="<?php echo $data_min_nasc; ?>" max="<?php echo $data_max_nasc; ?>" value="<?php echo htmlspecialchars($_POST['nasc'] ?? ''); ?>">
                        <small style="color: var(--text-muted); font-size: 0.8rem;">Idade mínima: 14 anos.</small>
                    </div>

<div class="form-group" style="grid-column: span 2;">
                        <label for="id_curso">Selecione o Curso desejado: *</label>
                        
                        <select name="id_curso" id="id_curso" required>
                            
                            <option value="">-- Escolha um Curso --</option>
                            
                            <?php foreach ($cursos as $curso): ?>
                                
                                <option value="<?php echo $curso['id']; ?>">
                                    
                                    <?php echo htmlspecialchars($curso['nome']); ?> (<?php echo htmlspecialchars($curso['categoria']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

<button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Fazer matrícula</button>
            </form>
            <?php else: ?>
<p class="alert alert-danger">As inscrições estão indisponíveis enquanto o banco não estiver conectado.</p>
            <?php endif; ?>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
