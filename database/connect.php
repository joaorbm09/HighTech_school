<?php
// Este arquivo cria a ponte entre a aplicação PHP e o banco PostgreSQL.
// Em termos simples: ele diz ao PHP "use este endereço, este usuário, esta senha e este banco".
// Se tudo estiver certo, a aplicação consegue salvar e consultar dados do sistema.
$host = getenv('DB_HOST') ?: '192.168.10.52';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'hightech_school';
$user = getenv('DB_USER') ?: 'admin_school';
$password = getenv('DB_PASS') ?: 'admin123';
$admin_email = getenv('APP_ADMIN_EMAIL') ?: 'admin@hightech.com';
$admin_password = getenv('APP_ADMIN_PASSWORD') ?: 'admin123';

try {
    // PDO é uma classe do PHP que ajuda a conversar com bancos de dados.
    // Aqui estamos criando uma conexão com PostgreSQL e dizendo para o PHP lançar erros
    // quando algo der errado, em vez de continuar silenciosamente.
    $conexao = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Depois que a conexão funciona, garantimos que exista um usuário administrador padrão.
    // Isso facilita muito a vida em ambiente local de desenvolvimento e testes.
    // A senha é transformada em hash antes de ser salva, para não deixar a senha em texto puro.
    $admin_hash = password_hash($admin_password, PASSWORD_BCRYPT);
    $stmt = $conexao->prepare(
        "INSERT INTO usuarios (nome, email, senha, perfil)
         VALUES ('Administrador', LOWER(:email), :senha, 'admin')
         ON CONFLICT (email) DO UPDATE
         SET nome = EXCLUDED.nome,
             senha = EXCLUDED.senha,
             perfil = 'admin'"
    );
    $stmt->bindValue(':email', strtolower(trim($admin_email)));
    $stmt->bindValue(':senha', $admin_hash);
    $stmt->execute();
} catch (PDOException $e) {
    // Se a conexão falhar, o PHP grava a mensagem no log do servidor e não expõe dados sensíveis para o usuário.
    // Isso é importante porque não queremos mostrar o endereço real do banco ou a senha no navegador.
    error_log('Erro de conexão com o PostgreSQL: ' . $e->getMessage());
    $conexao = null;
}
