<?php
// Esta página é a área administrativa para gerenciar as aulas dos cursos.
// Aqui o administrador consegue cadastrar novas aulas, editar o conteúdo de aulas existentes
// e publicar ou ocultar uma aula sem perder o histórico de progresso já registrado pelos alunos.
require_once __DIR__ . '/../includes/auth.php';
exigirAdmin();
require_once __DIR__ . '/../includes/functions.php';

// Essas variáveis guardam mensagens visuais para o usuário e o estado atual da edição.
$erro = '';
$mensagem = '';
$aula_edicao = null;

// Carrega os cursos existentes para aparecerem em um seletor na tela.
// Se o banco estiver apontando para a base errada ou ainda não tiver o schema importado,
// a consulta pode falhar. Em vez de explodir em erro 500, capturamos a exceção e mostramos
// uma mensagem amigável para o administrador.
$cursos = false;
if ($conexao) {
    try {
        $cursos = $conexao->query('SELECT id, nome, ativo FROM cursos ORDER BY nome ASC')->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao carregar cursos para gerenciamento de aulas: ' . $e->getMessage());
        $cursos = false;
        $erro = 'Não foi possível carregar os cursos. Verifique se o banco está conectado corretamente e se o schema da aplicação foi importado.';
    }
}

// Lê o curso selecionado pela URL ou pelo formulário.
$id_curso_raw = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['id_curso'] ?? '')
    : ($_GET['curso_id'] ?? '');
$id_curso = filter_var(
    is_string($id_curso_raw) || is_int($id_curso_raw) ? $id_curso_raw : '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

// Antes de qualquer ação, o sistema valida a conexão e a segurança da requisição.
// Isso ajuda a evitar acesso indevido e dados inválidos.
if (!$conexao) {
    http_response_code(503);
    $erro = 'Não foi possível conectar ao banco de dados. Tente novamente mais tarde.';
} elseif ($cursos === false && $erro === '') {
    http_response_code(500);
    $erro = 'Não foi possível carregar os cursos.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_aulas_admin']) ||
        !hash_equals($_SESSION['csrf_aulas_admin'], $token)) {
        http_response_code(400);
        $erro = 'A solicitação expirou ou não pôde ser validada. Atualize a página e tente novamente.';
    } elseif ($id_curso === false) {
        http_response_code(400);
        $erro = 'Selecione um curso válido.';
    } else {
        $curso_selecionado = false;
        foreach ($cursos as $curso) {
            if ((int) $curso['id'] === $id_curso) {
                $curso_selecionado = $curso;
                break;
            }
        }

        if (!$curso_selecionado) {
            http_response_code(400);
            $erro = 'O curso selecionado não existe.';
        } else {
            $acao = $_POST['acao'] ?? '';
            if ($acao === 'alternar_publicacao') {
                // Quando o admin publica ou oculta uma aula, o sistema não apaga o progresso já feito.
                // Isso permite que a aula volte a aparecer depois sem perder o histórico do aluno.
                $id_aula = filter_var(
                    is_string($_POST['id_aula'] ?? null) || is_int($_POST['id_aula'] ?? null)
                        ? $_POST['id_aula']
                        : '',
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                $aula = false;
                if ($id_aula) {
                    try {
                        $stmt = $conexao->prepare(
                            'SELECT id, id_curso, ativa FROM aulas WHERE id = :id'
                        );
                        $stmt->execute(['id' => $id_aula]);
                        $aula = $stmt->fetch();
                    } catch (PDOException $e) {
                        error_log('Erro ao buscar aula para alteração de publicação: ' . $e->getMessage());
                        http_response_code(500);
                        $erro = 'Não foi possível consultar a aula. Verifique a conexão com o banco e se a tabela de aulas existe.';
                    }
                }

                if (!$aula || (int) $aula['id_curso'] !== $id_curso) {
                    http_response_code(404);
                    $erro = 'A aula não existe neste curso.';
                } else {
                    $aula_ativa = in_array(
                        strtolower((string) $aula['ativa']),
                        ['1', 't', 'true', 'yes'],
                        true
                    );
                    try {
                        $stmt = $conexao->prepare(
                            'UPDATE aulas SET ativa = :ativa WHERE id = :id AND id_curso = :id_curso'
                        );
                        $stmt->bindValue('ativa', !$aula_ativa, PDO::PARAM_BOOL);
                        $stmt->bindValue('id', $id_aula, PDO::PARAM_INT);
                        $stmt->bindValue('id_curso', $id_curso, PDO::PARAM_INT);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            $_SESSION['flash_aulas_admin'] = $aula_ativa
                                ? 'Aula ocultada. O progresso já registrado pelos alunos foi preservado.'
                                : 'Aula publicada para os alunos matriculados.';
                            header('Location: gerenciar_aulas.php?curso_id=' . $id_curso);
                            exit;
                        }
                        $erro = 'Não foi possível alterar a publicação da aula.';
                    } catch (PDOException $e) {
                        error_log('Erro ao alterar publicação da aula: ' . $e->getMessage());
                        http_response_code(500);
                        $erro = 'Não foi possível alterar a publicação da aula.';
                    }
                }
            } elseif ($acao === 'salvar_aula') {
                // Aqui o sistema recebe o formulário de cadastro/edição da aula.
                // Primeiro ele valida os dados e depois decide se vai inserir uma nova aula ou atualizar uma já existente.
                $id_aula_value = $_POST['id_aula'] ?? '';
                $id_aula_raw = is_string($id_aula_value) || is_int($id_aula_value)
                    ? trim((string) $id_aula_value)
                    : 'invalid';
                $id_aula = $id_aula_raw === ''
                    ? null
                    : filter_var($id_aula_raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $titulo = is_string($_POST['titulo'] ?? null) ? trim($_POST['titulo']) : '';
                $descricao = is_string($_POST['descricao'] ?? null) ? trim($_POST['descricao']) : '';
                $conteudo = is_string($_POST['conteudo'] ?? null) ? trim($_POST['conteudo']) : '';
                $video_url = is_string($_POST['video_url'] ?? null) ? trim($_POST['video_url']) : '';
                $titulo_caracteres = preg_match_all('/./us', $titulo);
                $ordem = filter_var(
                    is_string($_POST['ordem'] ?? null) || is_int($_POST['ordem'] ?? null)
                        ? $_POST['ordem']
                        : '',
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                $obrigatoria = isset($_POST['obrigatoria']);

                if ($id_aula_raw !== '' && $id_aula === false) {
                    http_response_code(400);
                    $erro = 'O identificador da aula é inválido.';
                } elseif ($titulo === '' || $titulo_caracteres === false || $titulo_caracteres > 150) {
                    $erro = 'Informe um título de até 150 caracteres.';
                } elseif ($ordem === false) {
                    $erro = 'A ordem deve ser um número inteiro maior que zero.';
                } elseif (strlen($video_url) > 500 ||
                    !filter_var($video_url, FILTER_VALIDATE_URL) ||
                    !in_array(strtolower((string) parse_url($video_url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                    $erro = 'Informe um link válido começando com http:// ou https://.';
                } else {
                    $aula_atual = null;
                    if ($id_aula !== null) {
                        try {
                            $stmt = $conexao->prepare(
                                'SELECT id FROM aulas WHERE id = :id AND id_curso = :id_curso'
                            );
                            $stmt->execute(['id' => $id_aula, 'id_curso' => $id_curso]);
                            $aula_atual = $stmt->fetch();
                        } catch (PDOException $e) {
                            error_log('Erro ao localizar aula para edição: ' . $e->getMessage());
                            $aula_atual = null;
                            $erro = 'Não foi possível localizar a aula no banco. Verifique a conexão e a estrutura do banco de dados.';
                        }
                        if ($aula_atual === null && $erro === '') {
                            http_response_code(404);
                            $erro = 'A aula que você tentou editar não foi encontrada.';
                        }
                    }

                    if ($erro === '') {
                        try {
                            if ($id_aula === null) {
                                $stmt = $conexao->prepare(
                                    'INSERT INTO aulas
                                     (id_curso, titulo, descricao, conteudo, video_url, ordem, obrigatoria)
                                     VALUES (:id_curso, :titulo, :descricao, :conteudo, :video_url, :ordem, :obrigatoria)'
                                );
                                $stmt->bindValue('id_curso', $id_curso, PDO::PARAM_INT);
                                $stmt->bindValue('titulo', $titulo);
                                $stmt->bindValue('descricao', $descricao);
                                $stmt->bindValue('conteudo', $conteudo);
                                $stmt->bindValue('video_url', $video_url);
                                $stmt->bindValue('ordem', $ordem, PDO::PARAM_INT);
                                $stmt->bindValue('obrigatoria', $obrigatoria, PDO::PARAM_BOOL);
                                $stmt->execute();
                                $resultado = 'created';
                            } else {
                                $stmt = $conexao->prepare(
                                    'UPDATE aulas
                                     SET titulo = :titulo, descricao = :descricao,
                                         conteudo = :conteudo, video_url = :video_url,
                                         ordem = :ordem, obrigatoria = :obrigatoria
                                     WHERE id = :id AND id_curso = :id_curso'
                                );
                                $stmt->bindValue('titulo', $titulo);
                                $stmt->bindValue('descricao', $descricao);
                                $stmt->bindValue('conteudo', $conteudo);
                                $stmt->bindValue('video_url', $video_url);
                                $stmt->bindValue('ordem', $ordem, PDO::PARAM_INT);
                                $stmt->bindValue('obrigatoria', $obrigatoria, PDO::PARAM_BOOL);
                                $stmt->bindValue('id', $id_aula, PDO::PARAM_INT);
                                $stmt->bindValue('id_curso', $id_curso, PDO::PARAM_INT);
                                $stmt->execute();
                                $resultado = $stmt->rowCount() > 0 ? 'updated' : 'unchanged';
                            }
                        } catch (PDOException $e) {
                            if ($e->getCode() === '23505') {
                                $resultado = 'order_conflict';
                            } else {
                                error_log('Erro ao salvar aula: ' . $e->getMessage());
                                $resultado = 'error';
                            }
                        }

                        if ($resultado === 'created' || $resultado === 'updated' || $resultado === 'unchanged') {
                            $_SESSION['flash_aulas_admin'] = $resultado === 'created'
                                ? 'Aula cadastrada com sucesso.'
                                : 'Aula atualizada com sucesso.';
                            header('Location: gerenciar_aulas.php?curso_id=' . $id_curso);
                            exit;
                        }
                        $erro = $resultado === 'order_conflict'
                            ? 'Já existe uma aula nesta posição do curso. Escolha outro número para a ordem.'
                            : 'Não foi possível salvar a aula. Verifique os dados e tente novamente.';
                    }
                }

                if ($erro !== '') {
                    $aula_edicao = [
                        'id' => $id_aula ?: '',
                        'id_curso' => $id_curso,
                        'titulo' => $titulo,
                        'descricao' => $descricao,
                        'conteudo' => $conteudo,
                        'video_url' => $video_url,
                        'ordem' => $_POST['ordem'] ?? '',
                        'obrigatoria' => $obrigatoria
                    ];
                }
            } else {
                http_response_code(400);
                $erro = 'A ação solicitada não é válida.';
            }
        }
    }
}
// Se o admin acabou de publicar ou ocultar uma aula, a mensagem fica salva na sessão
// e é exibida na próxima renderização da página.
if (!empty($_SESSION['flash_aulas_admin'])) {
    $mensagem = $_SESSION['flash_aulas_admin'];
    unset($_SESSION['flash_aulas_admin']);
}

// Caso nenhum curso tenha sido escolhido, a página tenta abrir automaticamente o primeiro curso ativo.
if ($id_curso === false && $cursos) {
    foreach ($cursos as $curso) {
        if (in_array(strtolower((string) $curso['ativo']), ['1', 't', 'true', 'yes'], true)) {
            $id_curso = (int) $curso['id'];
            break;
        }
    }
    if ($id_curso === false) {
        $id_curso = (int) $cursos[0]['id'];
    }
}

$curso_selecionado = null;
$aulas = [];
$proxima_ordem = 1;
if ($conexao && $cursos && $id_curso !== false && $id_curso !== null) {
    foreach ($cursos as $curso) {
        if ((int) $curso['id'] === $id_curso) {
            $curso_selecionado = $curso;
            break;
        }
    }
    if (!$curso_selecionado) {
        http_response_code(404);
        $erro = 'O curso selecionado não existe.';
    }
    if ($curso_selecionado) {
        try {
            $stmt = $conexao->prepare(
                'SELECT id, id_curso, titulo, descricao, conteudo, video_url,
                        ordem, obrigatoria, ativa
                 FROM aulas
                 WHERE id_curso = :id_curso
                 ORDER BY ordem ASC, id ASC'
            );
            $stmt->execute(['id_curso' => $id_curso]);
            $aulas = $stmt->fetchAll();
            foreach ($aulas as $aula) {
                $proxima_ordem = max($proxima_ordem, (int) $aula['ordem'] + 1);
            }
        } catch (PDOException $e) {
            error_log('Erro ao carregar aulas do curso: ' . $e->getMessage());
            $aulas = false;
            $erro = 'Não foi possível listar as aulas deste curso. Verifique a conexão com o banco e se a tabela de aulas foi criada corretamente.';
        }
    }
}

// Quando o admin clica em editar uma aula, o sistema busca os dados daquela aula
// para preencher o formulário com os valores existentes.
if (!$aula_edicao && isset($_GET['editar'])) {
    $id_edicao = filter_var(
        is_string($_GET['editar']) || is_int($_GET['editar']) ? $_GET['editar'] : '',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    if ($id_edicao === false) {
        http_response_code(400);
        $erro = 'A aula informada para edição é inválida.';
    } elseif ($conexao) {
        try {
            $stmt = $conexao->prepare(
                'SELECT * FROM aulas WHERE id = :id AND id_curso = :id_curso'
            );
            $stmt->execute(['id' => $id_edicao, 'id_curso' => $id_curso]);
            $aula_edicao = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Erro ao buscar aula para edição: ' . $e->getMessage());
            $aula_edicao = null;
            $erro = 'Não foi possível abrir a aula para edição. Verifique se o banco está acessível e se a tabela de aulas existe.';
        }
        if (!$aula_edicao && $erro === '') {
            http_response_code(404);
            $aula_edicao = null;
            $erro = 'A aula solicitada não pertence ao curso selecionado.';
        }
    }
}
// Este token protege a página administrativa contra envio externo ou manipulado do formulário.
// Ele é usado para garantir que o POST veio da própria página e não de um site estranho.
if (empty($_SESSION['csrf_aulas_admin'])) {
    $_SESSION['csrf_aulas_admin'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_aulas_admin'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Aulas - HighTech School</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <!-- Seleção de curso, formulário de aula e lista de aulas para administração. -->
    <main class="container">
        <section class="admin-card">
            <div class="section-header">
                <h2>Gestão de aulas</h2>
                <p>Cadastre o título e o link do material. Os alunos com matrícula ativa verão as aulas publicadas no painel.</p>
            </div>

            <?php if ($erro !== ''): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($mensagem !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if ($cursos === false): ?>
            <?php elseif (empty($cursos)): ?>
                <div class="alert alert-warning">Cadastre um curso antes de adicionar aulas.</div>
            <?php else: ?>
                <form method="get" action="gerenciar_aulas.php" class="form-group" style="max-width: 600px; margin-bottom: 2rem;">
                    <label for="curso_id">Curso:</label>
                    <select name="curso_id" id="curso_id" required onchange="this.form.submit()">
                        <option value="">Selecione um curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?php echo (int) $curso['id']; ?>" <?php echo $id_curso === (int) $curso['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($curso['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (!in_array(strtolower((string) $curso['ativo']), ['1', 't', 'true', 'yes'], true)): ?> (inativo)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if ($curso_selecionado): ?>
                    <?php
                        $formulario = $aula_edicao ?: [];
                        $aula_obrigatoria = !array_key_exists('obrigatoria', $formulario) ||
                            in_array(strtolower((string) $formulario['obrigatoria']), ['1', 't', 'true', 'yes'], true);
                    ?>
                    <form action="gerenciar_aulas.php?curso_id=<?php echo $id_curso; ?>" method="post" class="admin-card" style="background: var(--bg-main);">
                        <h3><?php echo $aula_edicao ? 'Editar aula' : 'Adicionar aula'; ?></h3>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="acao" value="salvar_aula">
                        <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                        <input type="hidden" name="id_aula" value="<?php echo htmlspecialchars((string) ($formulario['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="titulo">Título da aula *</label>
                                <input type="text" id="titulo" name="titulo" maxlength="150" required value="<?php echo htmlspecialchars((string) ($formulario['titulo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="ordem">Ordem da aula *</label>
                                <input type="number" id="ordem" name="ordem" min="1" required value="<?php echo htmlspecialchars((string) ($formulario['ordem'] ?? $proxima_ordem), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="video_url">Link da aula (YouTube, Drive ou outro endereço HTTPS) *</label>
                                <input type="url" id="video_url" name="video_url" maxlength="500" required placeholder="https://..." value="<?php echo htmlspecialchars((string) ($formulario['video_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <small>O link será aberto em uma nova aba para o aluno.</small>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="descricao">Descrição (opcional)</label>
                                <textarea id="descricao" name="descricao" rows="2"><?php echo htmlspecialchars((string) ($formulario['descricao'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="conteudo">Orientações ou material complementar (opcional)</label>
                                <textarea id="conteudo" name="conteudo" rows="4"><?php echo htmlspecialchars((string) ($formulario['conteudo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="checkbox" name="obrigatoria" value="1" <?php echo $aula_obrigatoria ? 'checked' : ''; ?>>
                                    <span>Esta aula é obrigatória para concluir o curso</span>
                                </label>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary"><?php echo $aula_edicao ? 'Salvar alterações' : 'Adicionar aula'; ?></button>
                            <?php if ($aula_edicao): ?>
                                <a href="gerenciar_aulas.php?curso_id=<?php echo $id_curso; ?>" class="btn btn-outline">Cancelar edição</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <h3 style="margin: 2rem 0 1rem;">Aulas do curso</h3>
                    <?php if ($aulas === false): ?>
                    <?php elseif (empty($aulas)): ?>
                        <div class="alert alert-warning">Ainda não há aulas cadastradas neste curso.</div>
                    <?php else: ?>
                        <div class="grid">
                            <?php foreach ($aulas as $aula): ?>
                                <?php
                                    $aula_ativa = in_array(
                                        strtolower((string) $aula['ativa']),
                                        ['1', 't', 'true', 'yes'],
                                        true
                                    );
                                    $video_url = filter_var($aula['video_url'] ?? '', FILTER_VALIDATE_URL);
                                    $video_scheme = $video_url
                                        ? strtolower((string) parse_url($video_url, PHP_URL_SCHEME))
                                        : '';
                                    $aula_obrigatoria = in_array(
                                        strtolower((string) $aula['obrigatoria']),
                                        ['1', 't', 'true', 'yes'],
                                        true
                                    );
                                ?>
                                <article class="card">
                                    <div>
                                        <span class="badge">Aula <?php echo (int) $aula['ordem']; ?></span>
                                        <span class="badge <?php echo $aula_ativa ? 'badge-success' : ''; ?>">
                                            <?php echo $aula_ativa ? 'Publicada' : 'Oculta'; ?>
                                        </span>
                                        <?php if (!$aula_obrigatoria): ?>
                                            <span class="badge">Opcional</span>
                                        <?php endif; ?>
                                        <h4><?php echo htmlspecialchars($aula['titulo'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                        <?php if (!empty($aula['descricao'])): ?>
                                            <p><?php echo htmlspecialchars($aula['descricao'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php endif; ?>
                                        <p style="margin-top: 0.5rem; overflow-wrap: anywhere;">
                                            <?php if ($video_url && in_array($video_scheme, ['http', 'https'], true)): ?>
                                                <a href="<?php echo htmlspecialchars($video_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Abrir link da aula</a>
                                            <?php else: ?>
                                                Link inválido — edite a aula para corrigir.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1rem;">
                                        <a class="btn btn-outline" href="gerenciar_aulas.php?curso_id=<?php echo $id_curso; ?>&editar=<?php echo (int) $aula['id']; ?>">Editar</a>
                                        <form method="post" action="gerenciar_aulas.php?curso_id=<?php echo $id_curso; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="acao" value="alternar_publicacao">
                                            <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                                            <input type="hidden" name="id_aula" value="<?php echo (int) $aula['id']; ?>">
                                            <button type="submit" class="btn <?php echo $aula_ativa ? 'btn-outline' : 'btn-primary'; ?>">
                                                <?php echo $aula_ativa ? 'Ocultar aula' : 'Publicar aula'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
