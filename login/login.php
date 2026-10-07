<?php 

// Carrega a sessão, a conexão e as funções usadas para validar a senha.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (usuarioLogado()) {
    // Se a pessoa já está logada, ela não precisa acessar a página de login novamente.
    // Por isso redirecionamos para o painel correspondente conforme o perfil dela.
    $user = obterUsuarioLogado();
    if ($user && $user['perfil'] === 'admin') {
        header("Location: ../app/usuarios.php");
    } else {
        header("Location: ../app/meu_painel.php");
    }
    exit;
}

// Essa variável guarda a mensagem exibida ao usuário: erro, sucesso ou aviso.
$mensagem = '';
// Esse campo guarda o e-mail digitado para não perder a informação se a validação falhar.
$email = '';

if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastrado') {
    // Quando a pessoa acabou de se cadastrar, mostramos uma mensagem amigável e a convidamos a entrar.
    $mensagem = '<div class="alert alert-success"> Cadastro realizado com sucesso! Faça seu login abaixo.</div>';
}

if (!$conexao) {
    // Se não houver conexão com o PostgreSQL, a pessoa não consegue entrar.
    $mensagem = '<div class="alert alert-danger"> Erro de conexão com o banco de dados PostgreSQL. Verifique as configurações em database/connect.php.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Quando o formulário é enviado, o PHP lê os dados enviados pelo navegador.
    // Em seguida valida se os campos estão preenchidos e se as credenciais são válidas.
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        // Usuário deixou um dos campos vazios.
        $mensagem = '<div class="alert alert-warning">Por favor, preencha o E-mail e a Senha.</div>';

    } else if (!$conexao) {
        // Mesmo com os dados digitados corretamente, sem conexão não é possível validar o login.
        $mensagem = '<div class="alert alert-danger">Não foi possível processar o login: sem conexão com o banco de dados.</div>';

    } else {
        // Chama a função responsável por verificar se o usuário existe e se a senha está correta.
        $usuario = autenticarUsuario($conexao, $email, $senha);

        if ($usuario) {
            // Se a autenticação deu certo, salvamos os dados do usuário em sessão.
            // A sessão funciona como uma memória temporária para a navegação no sistema.
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nome'] = $usuario['nome'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_perfil'] = $usuario['perfil'];

            if ($usuario['perfil'] === 'admin') {
                header("Location: ../app/usuarios.php");
            } else {
                header("Location: ../app/meu_painel.php");
            }
            exit;

        } else {
            // Se a senha ou e-mail estiver errado, avisamos o usuário.
            $mensagem = '<div class="alert alert-danger">E-mail ou senha incorretos! Verifique suas credenciais.</div>';
        }
    }
}
?>

<!DOCTYPE html>

<html lang="pt-br">
<head>
    
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Entrar | HighTech School</title>
    
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Formulário de entrada; as mensagens informam erros ou sucesso do cadastro. -->
<main class="container">

<div class="admin-card" style="max-width: 450px; margin: 2rem auto;">

<h2 style="text-align: center; margin-bottom: 0.5rem;">Entrar</h2>

<p style="text-align: center; color: var(--text-muted); margin-bottom: 1.5rem;">Entre com seu e-mail e senha cadastrados</p>

<?php echo $mensagem; ?>

<form action="login.php" method="post">

<div class="form-group" style="margin-bottom: 1rem;">
                    <label for="email">E-mail: *</label>

<input type="email" name="email" id="email" required placeholder="seuemail@hightech.com" value="<?php echo htmlspecialchars($email); ?>">
                </div>

<div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="senha">Senha: *</label>
                    
                    <input type="password" name="senha" id="senha" required placeholder="Digite sua senha">
                </div>

<button type="submit" class="btn btn-primary" style="width: 100%;">Entrar no Sistema</button>
            </form>

<hr style="display: block; margin: 1.5rem 0; border: none; border-top: 1px solid var(--border-color);">

<p style="text-align: center; font-size: 0.95rem;">
                Ainda não possui uma conta? <a href="cadastrar.php" style="color: var(--primary); font-weight: 600;">Cadastre-se aqui</a>
            </p>
        </div>
    </main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
