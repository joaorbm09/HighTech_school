<?php
// Limpa os dados da sessão e encerra o acesso do usuário.
session_start();
// Esvazia os dados mantidos na sessão antes de encerrar a sessão no servidor.
$_SESSION = [];
session_destroy();

// Após sair, a pessoa volta ao catálogo público de cursos.
header('Location: ../aplicacao.php');
exit;
