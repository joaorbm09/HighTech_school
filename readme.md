# HighTech School

## Visão geral

O HighTech School é um sistema acadêmico desenvolvido em PHP e PostgreSQL para demonstrar, na prática, como funciona uma aplicação web de gestão escolar. O projeto simula uma escola digital em que alunos podem se cadastrar, realizar matrículas, acessar aulas, responder provas e receber certificados.

Além disso, o sistema conta com uma área administrativa, em que o gestor pode controlar cursos, aulas, alunos e avaliações.

A proposta do projeto é simples: unir os principais conceitos de desenvolvimento web em uma aplicação funcional, mantendo a estrutura bem organizada e fácil de entender para quem está começando na área.

---

## Objetivo do projeto

Este sistema foi pensado para:

- ensinar o fluxo de uma aplicação web completa;
- mostrar como PHP trabalha com formulários, sessões e banco de dados;
- praticar o uso de PostgreSQL para armazenamento de dados;
- demonstrar a diferença entre acesso de aluno e acesso de administrador;
- facilitar o aprendizado de regras de negócio e organização de código.

---

## Funcionalidades principais

### Para alunos

- cadastro de usuário;
- login e autenticação;
- visualização de cursos disponíveis;
- matrícula em cursos;
- acesso às aulas do curso;
- acompanhamento de progresso;
- realização de provas;
- visualização de certificados.

### Para administradores

- gestão de usuários;
- cadastro de cursos;
- gestão de aulas por curso;
- configuração de provas;
- gerenciamento de questões e alternativas;
- visualização do progresso dos alunos;
- controle da publicação de conteúdos.

---

## Fluxo geral da aplicação

O sistema segue um fluxo comum de aplicações web:

1. O usuário acessa uma página no navegador.
2. O PHP recebe os dados enviados pela página.
3. O PHP valida as informações.
4. O PHP consulta ou grava informações no PostgreSQL.
5. O banco retorna os dados necessários.
6. A página é montada novamente com as informações corretas.
7. A sessão mantém o usuário logado entre as páginas.

Em outras palavras, a aplicação funciona como um ciclo de entrada, processamento e resposta.

---

## Arquitetura do projeto

A estrutura foi organizada para separar responsabilidades e facilitar a leitura do código:

```text
hightech_school/
├── app/                  # páginas internas do sistema
├── assets/               # arquivos estáticos (CSS, imagens, etc.)
├── database/             # conexão com o banco e schema SQL
├── docs/                 # documentação auxiliar
├── includes/             # funções compartilhadas e autenticação
├── login/                # autenticação e cadastro
├── index.php             # página inicial
├── aplicacao.php         # entrada principal da aplicação
├── readme.md             # documentação do projeto
└── .htaccess / outros    # configurações do ambiente
```

### Papel de cada pasta

- `app/`: páginas de uso do aluno e do admin depois do login;
- `assets/`: arquivos visuais e estilos da interface;
- `database/`: conexão com o banco e estrutura das tabelas;
- `includes/`: funções reutilizadas e regras de autenticação;
- `login/`: telas de login, cadastro e saída;
- `docs/`: materiais extras e explicações complementares.

---

## Requisitos do ambiente

Antes de rodar o projeto, você precisa ter instalado no computador:

- PHP 8.x ou superior;
- PostgreSQL;
- extensão `pdo_pgsql` do PHP;
- servidor local do PHP ou Apache/Nginx;
- navegador web.

Para verificar se o PHP está funcionando corretamente:

```bash
php -v
```

Para verificar se o PostgreSQL está disponível:

```bash
psql --version
```

---

## Configuração local

### 1. Criar o banco de dados

No terminal PostgreSQL, execute:

```bash
psql -U postgres -c "CREATE DATABASE hightech_school;"
```

Se o banco já existir, você pode pular esta etapa.

### 2. Importar o schema

A estrutura do banco fica no arquivo `database/schema.sql`.

Execute:

```bash
psql -U postgres -d hightech_school -f database/schema.sql
```

Esse script cria as tabelas principais do sistema, como:

- `usuarios`
- `alunos`
- `cursos`
- `matriculas`
- `aulas`
- `progresso_aulas`
- `provas`
- `questoes_prova`
- `alternativas_prova`
- `tentativas_prova`
- `certificados`

### 3. Configurar a conexão com o banco

O arquivo responsável pela conexão é:

- `database/connect.php`

Ele usa as variáveis de ambiente ou valores padrão:

```php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'hightech_school';
$user = getenv('DB_USER') ?: 'admin_school';
$password = getenv('DB_PASS') ?: 'admin123';
```

Dados padrão do projeto:

- host: `127.0.0.1`
- porta: `5432`
- banco: `hightech_school`
- usuário: `admin_school`
- senha: `admin123`

Se seu ambiente usa outra configuração, ajuste os valores antes de iniciar a aplicação.

### 4. Iniciar o servidor PHP

No diretório do projeto, rode:

```bash
php -S localhost:8000
```

Depois acesse:

```text
http://localhost:8000
```

---

## Usuário administrador padrão

O sistema já tenta criar um administrador padrão automaticamente ao conectar ao banco.

Credenciais padrão:

- e-mail: `admin@hightech.com`
- senha: `admin123`

Esses dados são usados para facilitar testes locais e o primeiro acesso ao painel administrativo.

Se quiser alterar isso, é possível ajustar as variáveis de ambiente:

```bash
APP_ADMIN_EMAIL=admin@hightech.com
APP_ADMIN_PASSWORD=admin123
```

Também é possível promover um usuário existente para admin diretamente no banco:

```sql
UPDATE usuarios SET perfil = 'admin' WHERE email = 'seu-email@exemplo.com';
```

---

## Estrutura das tabelas principais

### `usuarios`

Guarda as contas de acesso do sistema.

Campos principais:

- `id`
- `nome`
- `email`
- `senha`
- `perfil`
- `criado_em`

Essa tabela define se o usuário é `aluno` ou `admin`.

### `alunos`

Armazena detalhes acadêmicos do aluno, como:

- nome completo;
- CPF;
- e-mail;
- turma;
- data de nascimento;
- status ativo/inativo.

### `cursos`

Guarda as informações dos cursos oferecidos pela escola.

Exemplos de campos:

- nome;
- categoria;
- descrição;
- carga horária;
- status ativo/inativo.

### `matriculas`

Relaciona cada aluno ao curso em que ele está matriculado. Essa tabela controla a associação entre pessoa e disciplina/curso.

### `aulas`

Representa cada aula dentro de um curso. Guarda:

- título;
- descrição;
- conteúdo;
- URL do vídeo;
- ordem da aula;
- se é obrigatória;
- se está ativa.

### `progresso_aulas`

Registra quais aulas o aluno já concluiu.

### `provas`

Armazena a configuração da prova vinculada ao curso.

Campos importantes:

- nota mínima;
- número máximo de tentativas;
- status ativo/inativo.

### `questoes_prova`

Guarda cada questão da avaliação.

### `alternativas_prova`

Guarda as opções de resposta de cada questão.

### `tentativas_prova`

Registra as respostas e a nota obtida pelo aluno em cada tentativa.

### `certificados`

Guarda a emissão de certificados após a aprovação do aluno.

---

## Organização por módulos

### Módulo de autenticação

A parte de login e cadastro fica em `login/` e também usa funções compartilhadas em `includes/`.

Esse módulo é responsável por:

- validar e-mail e senha;
- verificar se o usuário existe;
- comparar senha digitada com o hash salvo;
- criar sessão do usuário;
- redirecionar conforme o perfil.

### Módulo de aluno

A pasta `app/` cuida do acesso do aluno após o login.

Nela geralmente ficam páginas como:

- dashboard do aluno;
- cursos matriculados;
- aulas disponíveis;
- provas;
- certificados;
- progresso da turma ou do curso.

### Módulo administrativo

Também dentro de `app/` ficam as telas administrativas, como:

- gestão de cursos;
- gestão de aulas;
- gestão de provas;
- controle de alunos;
- controle de usuários.

---

## Como funciona a autenticação

A aplicação usa sessões para lembrar quem está logado.

Em termos simples:

- o usuário envia e-mail e senha;
- o sistema consulta o cadastro em `usuarios`;
- a senha é comparada com o hash armazenado;
- se estiver correta, o sistema salva informações do usuário na sessão;
- nas próximas páginas, o PHP lê essa sessão para confirmar a identidade.

Isso é essencial para que o sistema saiba se o usuário é aluno ou administrador.

---

## O que são funções compartilhadas?

O arquivo `includes/functions.php` concentra chamadas frequentes do sistema, como:

- criação de usuários;
- autenticação de usuário;
- busca por e-mail;
- cadastro de aluno;
- matrícula em curso;
- carregamento de aulas;
- controle de progresso;
- registro de provas e notas.

Essa organização é importante porque evita repetir a mesma lógica em vários arquivos.

---

## Principais conceitos de desenvolvimento usados no projeto

### PHP

PHP é a linguagem usada para processar lógica do servidor, como receber dados, consultar banco e montar a página final.

### PostgreSQL

PostgreSQL é o banco de dados responsável por guardar os registros do sistema.

### Sessões

Sessões permitem que o sistema “lembre” quem está logado durante a navegação.

### Hash de senha

O sistema usa `password_hash()` para salvar senhas de forma mais segura, e `password_verify()` para validar a senha no login.

### PDO

A conexão com o PostgreSQL usa PDO, que é uma forma segura e organizada de interagir com banco de dados no PHP.

---

## Solução de problemas comuns

### 1. Erro de conexão com o banco

Se a tela mostrar “Não foi possível conectar ao banco de dados”, verifique:

- se o PostgreSQL está rodando;
- se o banco `hightech_school` existe;
- se o usuário e a senha informados em `database/connect.php` estão corretos;
- se o host está correto (`127.0.0.1`);
- se a extensão `pdo_pgsql` está habilitada no PHP.

### 2. Tabela inexistente

Se aparecer mensagem como:

```text
relation "aulas" does not exist
```

isso geralmente significa que o banco conectado não tem as tabelas do projeto. Nesse caso, execute:

```bash
psql -U postgres -d hightech_school -f database/schema.sql
```

### 3. Arquivo SQL não encontrado

Se o terminal mostrar algo como:

```text
No such file or directory
```

isso indica que o caminho do arquivo está errado. Verifique se você está na pasta correta do projeto e use o caminho correto:

```bash
cd /caminho/do/projeto/hightech_school
psql -U postgres -d hightech_school -f database/schema.sql
```

### 4. Página não abre

Verifique se o servidor está rodando:

```bash
php -S localhost:8000
```

Depois teste a URL:

```text
http://localhost:8000
```

---

## Recomendação de fluxo para estudos

Se você quiser aprender a lógica da aplicação em ordem, siga este caminho:

1. abrir `database/connect.php` para entender a conexão;
2. abrir `database/schema.sql` para ver as tabelas;
3. abrir `login/login.php` para ver o processo de autenticação;
4. abrir `includes/functions.php` para entender as regras principais;
5. abrir `app/` para ver como os dados aparecem na interface;
6. testar cada ação com um aluno e com admin.

Esse processo ajuda a visualizar a aplicação como um conjunto de partes que se conectam entre si.

---

## Observações finais

- O projeto foi criado com foco em aprendizado prático.
- A estrutura é simples, mas representa bem a lógica de um sistema real.
- Ele pode ser usado como base para um projeto maior, com melhorias em interface, validações e mais módulos.
- O principal objetivo não é apenas funcionar, mas ensinar como uma aplicação web em PHP e PostgreSQL funciona por dentro.

---

## Resumo em uma frase

O HighTech School é um sistema escolar didático em PHP e PostgreSQL, pensado para ensinar na prática como funcionam autenticação, banco de dados, aulas, provas, matrículas e gestão acadêmica em uma aplicação web real.
