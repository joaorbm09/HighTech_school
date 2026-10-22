<?php
// Mantém os dados do usuário na sessão entre as páginas.
if (session_status() === PHP_SESSION_NONE) {
    // Inicia ou retoma a sessão antes de enviar HTML.
    session_start();
}

// Verifica o login pela sessão; a senha é conferida em autenticarUsuario().
function usuarioLogado() {
    return isset($_SESSION['user_id']);
}

// Retorna os dados guardados no login, sem consultar novamente o banco.
function obterUsuarioLogado() {
    if (!usuarioLogado()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'nome' => $_SESSION['user_nome'],
        'email' => $_SESSION['user_email'],
        'perfil' => $_SESSION['user_perfil'] ?? 'aluno'
    ];
}

// Redireciona visitantes para o login e interrompe o acesso à página privada.
function exigirLogin() {
    if (!usuarioLogado()) {
        header('Location: ../login/login.php');
        exit;
    }
}

// Exige login e perfil de administrador antes de permitir o acesso.
function exigirAdmin() {
    exigirLogin();

    if (($_SESSION['user_perfil'] ?? '') !== 'admin') {
        header('Location: ../aplicacao.php?erro=acesso_negado');
        exit;
    }
}
