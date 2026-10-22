<?php
// Este arquivo cria a ponte entre a aplicação PHP e o banco PostgreSQL.
// Em termos simples: ele diz ao PHP "use este endereço, este usuário, esta senha e este banco".
// Se tudo estiver certo, a aplicação consegue salvar e consultar dados do sistema.
// getenv() lê configurações do ambiente; o valor após ?: é usado quando a variável não está definida ou está vazia.
/* Lê a configuração no ambiente do servidor. ?: usa o valor padrão também para valores falsos, como
 * string vazia ou "0"; é diferente de ??, que só substitui null ou ausência. O ternário condição ?
 * valor_se_verdadeiro : valor_se_falso escolhe um dos dois valores. Já a forma abreviada valor ?:
 * alternativa usa a alternativa para qualquer valor considerado falso. */
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
        /* Esta é a DSN, a descrição da conexão: pgsql escolhe o driver PostgreSQL, host localiza o
         * servidor, port indica sua porta e dbname escolhe o banco. Os três argumentos seguintes
         * fornecem credenciais e opções ao construtor. */
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            /* Configura a conexão para lançar uma PDOException quando o banco recusar uma operação.
             * Assim os catch do projeto podem registrar o erro e mostrar a mensagem prevista para a
             * tela. */
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            /* Define o formato padrão de fetch/fetchAll: cada registro terá chaves como nome e email.
             * Por isso as outras páginas podem usar $aluno['nome'] sem repetir FETCH_ASSOC em toda
             * consulta. */
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

        /* A consulta anterior seleciona 1 apenas quando encontra o e-mail. Sem linha, fetchColumn
         * devolve false e o bloco cria a conta administrativa padrão; uma conta já existente não
         * recebe uma nova senha aqui. */
        if (!$stmt->fetchColumn()) {
            /* Armazena somente o hash da senha, nunca a senha em texto puro. */
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
