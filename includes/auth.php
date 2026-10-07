<?php
// Este arquivo cuida da autenticação do sistema.
// Em termos simples: ele lembra quem está logado enquanto a pessoa navega entre as páginas.
// Sem isso, cada página teria que pedir usuário e senha de novo toda vez.
if (session_status() === PHP_SESSION_NONE) {
    // Inicia a sessão apenas quando ela ainda não existe.
    // A sessão funciona como memória temporária do navegador para o usuário atual.
    session_start();
}

// Verifica se o usuário já está autenticado.
// Se a sessão tem 'user_id', significa que alguém entrou com login válido.
function usuarioLogado() {
    return isset($_SESSION['user_id']);
}

// Busca os dados básicos do usuário logado para usar em outras páginas.
// Isso evita que o código precise acessar a sessão em vários lugares diferentes.
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

// Protege páginas que só podem ser acessadas por quem está logado.
// Se a pessoa não está autenticada, ela é enviada para a página de login.
function exigirLogin() {
    if (!usuarioLogado()) {
        header('Location: ../login/login.php');
        exit;
    }
}

// Protege páginas exclusivas de administradores.
// Primeiro exige login, depois verifica se o perfil é 'admin'.
function exigirAdmin() {
    exigirLogin();

    if (($_SESSION['user_perfil'] ?? '') !== 'admin') {
        // Se a pessoa não for admin, ela não tem permissão para acessar a área administrativa.
        header('Location: ../aplicacao.php?erro=acesso_negado');
        exit;
    }
}
