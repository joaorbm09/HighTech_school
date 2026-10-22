<?php 
// Cadastro público: usuários já autenticados seguem direto para o painel.

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
if (usuarioLogado()) {
    header("Location: ../app/meu_painel.php");
    exit;
}

$mensagem = '';
$nome = '';
$email = '';

// A proteção CSRF ajuda a recusar envios forjados por outro site usando a sessão do visitante; não substitui autenticação, autorização ou validação dos dados.
// Ele cria um token secreto e exige que o navegador envie o mesmo valor na hora do cadastro.
if (empty($_SESSION['csrf_cadastro'])) {
    /* Gera um token aleatório para proteger o formulário contra CSRF. */
    $_SESSION['csrf_cadastro'] = bin2hex(random_bytes(32));
}

if (!$conexao) {
    // Se não houver conexão com o banco, o sistema não consegue salvar o cadastro.
    $mensagem = '<div class="alert alert-danger">Sem conexão com o banco de dados PostgreSQL.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aqui o PHP recebe os dados digitados pelo usuário no formulário.
    // O trim() remove espaços extras no início e no fim do texto.

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Confere se o token recebido do formulário é igual ao token salvo na sessão.
    $csrf_valido = is_string($csrf_token) &&
        /* Confere o token CSRF antes de processar o formulário. */
        hash_equals($_SESSION['csrf_cadastro'], $csrf_token);
    if (!$csrf_valido) {
        /* Define o status HTTP 400 (pedido inválido). A mensagem de erro definida no fluxo será
         * exibida no HTML; mudar o status sozinho não interrompe o script. */
        http_response_code(400);
        $mensagem = '<div class="alert alert-danger">Falha na validação de segurança. Atualize a página e tente novamente.</div>';
    } elseif (empty($nome) || empty($email) || empty($senha)) {
        // Usuário não preencheu todos os campos obrigatórios.
        $mensagem = '<div class="alert alert-warning">Por favor, preencha todos os campos obrigatórios.</div>';
    } else if (!$conexao) {
        // Sem conexão com o banco, não há como registrar a conta.
        $mensagem = '<div class="alert alert-danger">Não foi possível processar o cadastro: banco de dados offline.</div>';
    } else if ($senha !== $confirmar_senha) {
        // A confirmação da senha precisa ser igual à senha digitada.
        $mensagem = '<div class="alert alert-warning">As senhas digitadas não coincidem!</div>';
    } else if (strlen($senha) < 6) {
        // O cadastro público exige pelo menos 6 bytes de senha neste código; essa regra de tamanho, isolada, não garante uma senha forte.
        $mensagem = '<div class="alert alert-warning">A senha deve conter no mínimo 6 caracteres.</div>';
    } else if (buscarUsuarioPorEmail($conexao, $email)) {
        // Se o e-mail já existe, não deixamos cadastrar a mesma pessoa duas vezes.
        $mensagem = '<div class="alert alert-danger">Este e-mail já está cadastrado no sistema.</div>';
    } else {
        // Conta e cadastro acadêmico formam uma única operação: ou ambos são salvos,
        // ou a transação desfaz o que já tiver sido gravado.
        try {
            /* Inicia a transação para salvar todas as alterações juntas ou desfazê-las em caso de
             * erro. */
            $conexao->beginTransaction();
            $usuario_criado = cadastrarUsuario($conexao, $nome, $email, $senha, 'aluno');

            // Se o aluno já possui cadastro acadêmico prévio (ex: inscrição na página inicial),
            // aproveita o cadastro existente; caso contrário, registra o novo aluno.
            $aluno_existente = buscarAlunoPorEmail($conexao, $email);
            if ($aluno_existente) {
                $aluno_criado = true;
            } else {
                $aluno_criado = cadastrarAluno($conexao, $nome, null, strtolower(trim($email)), 'HT-2026', null, true);
            }

            if (!$usuario_criado || !$aluno_criado) {
                /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
                $conexao->rollBack();
                $mensagem = '<div class="alert alert-danger">Não foi possível concluir o cadastro. Confira se os dados já estão registrados e tente novamente.</div>';
            } else {
                /* Confirma as alterações e libera os bloqueios da transação. */
                $conexao->commit();

                header("Location: login.php?sucesso=cadastrado");
                exit;
            }
        /* Captura tanto exceções quanto erros que implementam Throwable, permitindo desfazer o
         * cadastro conjunto e registrar o problema no log. */
        } catch (Throwable $e) {
            /* Só desfaz a operação se a transação ainda estiver ativa. */
            if ($conexao->inTransaction()) {
                /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
                $conexao->rollBack();
            }
            error_log('Erro ao criar conta e cadastro acadêmico: ' . $e->getMessage());
            $mensagem = '<div class="alert alert-danger">Erro ao criar conta. Tente novamente.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | HighTech School</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Formulário HTML para criar uma conta de aluno. -->
<main class="container">
        <div class="admin-card" style="max-width: 500px; margin: 2rem auto;">
            <h2 style="text-align: center; margin-bottom: 0.5rem;">Criar conta</h2>
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 1.5rem;">Cadastre-se para acessar a HighTech School</p>

<?php echo $mensagem; ?>

<form action="cadastrar.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php  echo htmlspecialchars($_SESSION['csrf_cadastro'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="nome">Nome Completo: *</label>
                    <input type="text" name="nome" id="nome" required placeholder="Seu nome completo" value="<?php echo htmlspecialchars($nome); ?>">
                </div>

<div class="form-group" style="margin-bottom: 1rem;">
                    <label for="email">E-mail: *</label>
                    <input type="email" name="email" id="email" required placeholder="seu@email.com" value="<?php echo htmlspecialchars($email); ?>">
                </div>

<div class="form-group" style="margin-bottom: 1rem;">
                    <label for="senha">Senha: *</label>
                    <input type="password" name="senha" id="senha" required placeholder="Mínimo 6 caracteres">
                </div>

<div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="confirmar_senha">Confirmar Senha: *</label>
                    <input type="password" name="confirmar_senha" id="confirmar_senha" required placeholder="Digite a senha novamente">
                </div>

<button type="submit" class="btn btn-primary" style="width: 100%;">Finalizar Cadastro</button>
            </form>

<hr style="display: block; margin: 1.5rem 0; border: none; border-top: 1px solid var(--border-color);">

            <p style="text-align: center; font-size: 0.95rem;">
                Já tem uma conta? <a href="login.php" style="color: var(--primary); font-weight: 600;">Faça Login aqui</a>
            </p>
        </div>
    </main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
