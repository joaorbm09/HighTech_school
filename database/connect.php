<?php
// Este arquivo cria a ponte entre a aplicação PHP e o banco PostgreSQL.
// Em termos simples: ele diz ao PHP "use este endereço, este usuário, esta senha e este banco".
// Se tudo estiver certo, a aplicação consegue salvar e consultar dados do sistema.
// getenv() lê configurações do ambiente; o valor após ?: é usado quando a variável não está definida ou está vazia.
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
} catch (PDOException $e) {
    // Se a conexão falhar, o PHP grava a mensagem no log do servidor e não expõe dados sensíveis para o usuário.
    // Isso é importante porque não queremos mostrar o endereço real do banco ou a senha no navegador.
    error_log('Erro de conexão com o PostgreSQL: ' . $e->getMessage());
    $conexao = null;
}

// Cria o administrador padrão somente se a conta ainda não existir.
// Assim, a aplicação não redefine a senha de um administrador a cada página visitada.
if ($conexao) {
    try {
        $email_admin_normalizado = strtolower(trim($admin_email));
        $stmt = $conexao->prepare(
            'SELECT 1 FROM usuarios WHERE LOWER(email) = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email_admin_normalizado]);

        if (!$stmt->fetchColumn()) {
            $admin_hash = password_hash($admin_password, PASSWORD_BCRYPT);
            $stmt = $conexao->prepare(
                "INSERT INTO usuarios (nome, email, senha, perfil)
                 VALUES ('Administrador', :email, :senha, 'admin')
                 ON CONFLICT (email) DO NOTHING"
            );
            $stmt->execute([
                'email' => $email_admin_normalizado,
                'senha' => $admin_hash
            ]);
        }
    } catch (PDOException $e) {
        // Uma falha ao criar a conta padrão não deve apagar uma conexão válida com o banco.
        error_log('Erro ao verificar/criar administrador padrão: ' . $e->getMessage());
    }
}
