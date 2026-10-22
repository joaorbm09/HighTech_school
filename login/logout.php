<?php
// Limpa os dados da sessão e encerra o acesso do usuário.
/* Abre ou retoma os dados da sessão no servidor usando seu identificador, normalmente recebido por
 * cookie. Deve ocorrer antes da saída de HTML. */
session_start();
// Esvazia os dados mantidos na sessão antes de encerrar a sessão no servidor.
$_SESSION = [];
/* Destrói os dados da sessão no servidor. Não é o mesmo que apagar o cookie do navegador; neste
 * arquivo, o array da sessão já foi esvaziado na linha anterior. */
session_destroy();

// Após sair, a pessoa volta ao catálogo público de cursos.

header('Location: ../aplicacao.php');
exit;
