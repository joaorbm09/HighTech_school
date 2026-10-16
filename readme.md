# HighTech — Sistema de serviços e educação em tecnologia

A **HighTech** é uma aplicação web acadêmica desenvolvida em PHP e PostgreSQL. O sistema combina um portal de serviços para empresas com a HighTech School, uma área de formação em tecnologia, e recursos para aproximar estudantes de oportunidades profissionais.

Por meio da aplicação, visitantes podem conhecer os serviços, consultar cursos e vagas, enviar solicitações corporativas e visualizar perfis profissionais públicos. Alunos podem criar uma conta, acompanhar matrículas, acessar as aulas dos cursos em que possuem matrícula ativa, registrar o progresso, fazer provas e receber certificados verificáveis após a aprovação. Administradores autenticados podem gerenciar alunos, cursos, matrículas, aulas e provas.

Para o roteiro de apresentação do projeto, consulte o [roteiro de apresentação](docs/roteiro_apresentacao.md).

> Para conferir o comportamento atual, considere o código e `database/schema.sql` como referência. Requisitos marcados como parte do escopo anterior não estão implementados nesta versão.

---

## 📌 Sumário
- [Como executar o projeto localmente](#como-executar-o-projeto-localmente)
  - [Pré-requisitos](#pré-requisitos)
  - [Passo a passo de instalação](#passo-1-clonar-o-repositório)
  - [Como criar o primeiro administrador](#primeiro-administrador)
- [Especificação de Requisitos de Software (SRS)](#especificação-de-requisitos-de-software)
  - [1. Introdução](#1-introdução)
  - [2. Descrição global](#2-descrição-global)
  - [3. Requisitos do sistema](#3-requisitos-do-sistema)
    - [Requisitos Funcionais](#31-requisitos-funcionais)
    - [Requisitos Não Funcionais](#32-requisitos-não-funcionais)
    - [Regras de Negócio](#33-regras-de-negócio)
- [4. Estruturação do banco de dados](#4-estruturação-do-banco-de-dados)
- [5. Diagramas](#5-diagramas)
  - [Diagrama de Entidade-Relacionamento (DER)](#51-diagrama-de-entidade-relacionamento-der)
  - [Diagrama de Casos de Uso](#52-diagrama-de-casos-de-uso)
  - [Diagrama de Classes Conceitual](#53-diagrama-de-classes-conceitual)
  - [Diagrama de Fluxo de Inscrição](#54-diagrama-de-fluxo-de-inscrição-em-curso)

---

## Como executar o projeto localmente

Para executar a aplicação localmente, é necessário ter PHP e PostgreSQL instalados.

### Pré-requisitos

- **PHP 7.4 ou superior**, com as extensões `PDO`, `pdo_pgsql` e `mbstring` habilitadas.
- **PostgreSQL** instalado e em execução.
- **Git** instalado para clonar o repositório.
- O cliente de linha de comando `psql`, incluído na instalação do PostgreSQL.

### Passo 1: Clonar o repositório

Abra um terminal e execute:

```bash
# Clona o repositório do projeto.
git clone https://github.com/joaorbm09/HighTech_school.git

# Entra na pasta criada pelo Git.
cd HighTech_school
```

### Passo 2: Criar o banco de dados

Crie um banco de dados vazio para o projeto. O exemplo abaixo usa o usuário PostgreSQL `postgres`:

```bash
psql -U postgres
```

No console `psql`, crie a role e defina uma senha própria:

```sql
CREATE USER admin_school WITH PASSWORD admin123;
CREATE DATABASE hightech_school OWNER admin_school;
\q
```

Se o banco `hightech_school` já existir, escolha outro nome ou remova somente o banco local de desenvolvimento antes de recriá-lo.

### Passo 3: Criar as tabelas e carregar os dados iniciais

Na raiz do repositório, execute o script SQL:

```bash
psql -U postgres -d hightech_school -f database/schema.sql
```

O arquivo `database/schema.sql` cria as 9 tabelas do sistema (usuarios, alunos, cursos, matriculas, aulas, progresso_aulas, provas, questoes_prova, alternativas_prova, tentativas_prova, certificados) e insere dois cursos iniciais de demonstração.

#### Aplicar pelo pgAdmin

1. Abra o **pgAdmin** e conecte-se ao servidor PostgreSQL.
2. No navegador à esquerda, expanda **Servers → seu servidor → Databases** e clique uma vez no banco `hightech_school`.
3. Clique com o botão direito nesse banco e escolha **Query Tool**.
4. No Query Tool, use **Open File** e selecione o arquivo `database/schema.sql` dentro da pasta do projeto.
5. Pressione **F5** (ou clique no botão **Execute**).
6. O painel de mensagens deve indicar que os comandos `CREATE TABLE` foram executados.

### Passo 4: Configurar a conexão PHP

A aplicação lê as configurações em `database/connect.php`. Você pode definir variáveis de ambiente no seu sistema ou ajustar diretamente os valores padrão no arquivo:

- `DB_HOST`: Endereço do servidor PostgreSQL (padrão: `127.0.0.1`).
- `DB_PORT`: Porta do servidor (padrão: `5432`).
- `DB_NAME`: Nome da base de dados (padrão: `hightech_school`).
- `DB_USER`: Usuário do banco (padrão: `postgres`).
- `DB_PASS`: Senha do usuário do banco (padrão: vazia).

Confirme que os módulos necessários estão habilitados:

```bash
php -m
```

Na lista apresentada, verifique a presença de `PDO`, `pdo_pgsql` e `mbstring`.

### Passo 5: Iniciar a aplicação

Na raiz do projeto, inicie o servidor embutido do PHP para testar no mesmo computador:

```bash
php -S 127.0.0.1:8000
```

No navegador desse computador, abra:

```text
http://localhost:8000
```

O `index.php` redireciona para `portal_empresa.php`.

### Passo 6: Criar uma conta e acessar

1. Acesse `http://localhost:8000/login/cadastrar.php` e use a opção **Cadastrar-se** para criar uma conta de aluno. O formulário cria a conta de usuário e o registro acadêmico correspondente.
2. Por padrão, todas as contas criadas pelo formulário público recebem o perfil `aluno`.
3. **Para promover um usuário a Administrador**, conecte ao banco com o usuário PostgreSQL `admin_school` (não com `postgres`):

```bash
psql -h 127.0.0.1 -U admin_school -d hightech_school
```

No console `psql`, atualize o e-mail da conta que você acabou de cadastrar:

```sql
UPDATE usuarios SET perfil = 'admin' WHERE email = 'seu_email@exemplo.com';
DELETE FROM usuarios WHERE email = 'admin@hightech.com';
\q
```

Confirme que o `UPDATE` alterou uma linha. O `DELETE` remove a conta administrativa de demonstração que vem no schema (`admin@hightech.com` / `admin123`); não a mantenha em uma máquina compartilhada. Com o perfil `admin`, novos menus de gestão serão liberados no cabeçalho: **Gestão Alunos**, **Gestão Cursos**, **Gestão de Aulas**, **Gestão de Provas** e **Matrículas**.

---

## Guia de instalação em uma máquina Linux (MobaXterm / SSH)

Este roteiro instala a aplicação para demonstração ou avaliação em uma máquina Linux. Ele usa um usuário PostgreSQL exclusivo para a aplicação e o servidor embutido do PHP, apropriado para testes, não para publicar um serviço de produção na internet.

### 1. Conectar ao servidor

No MobaXterm, use o terminal para entrar no servidor:

```bash
ssh usuario@ip_do_servidor
```

Exemplo:

```bash
ssh aluno@192.168.0.10
```

### 2. Verificar o ambiente

```bash
php -v
psql --version
```

Se o PHP ou PostgreSQL não estiverem instalados, execute:

```bash
sudo apt update
sudo apt install git php php-pgsql postgresql postgresql-contrib
```

### 3. Clonar o repositório

```bash
cd "$HOME"
git clone https://github.com/joaorbm09/HighTech_school.git hightech_school
cd "$HOME/hightech_school"
```

### 4. Criar o usuário PostgreSQL e o banco

Abra o console como administrador do PostgreSQL:

```bash
sudo -u postgres psql
```

Crie uma role de login exclusiva da aplicação, defina uma senha forte e torne-a proprietária do banco. A senha é solicitada de forma interativa e não fica exposta no comando:

```sql
CREATE ROLE admin_school LOGIN;
\password admin_school
CREATE DATABASE hightech_school OWNER admin_school;
\q
```

Não use `postgres` em `DB_USER`: essa conta serve para administração do servidor PostgreSQL. `admin_school` é a identidade de banco de dados da aplicação e é diferente tanto do usuário SSH/Linux quanto da conta administradora que fará login no site.

### 5. Importar o schema da aplicação

No diretório do projeto, importe o schema conectando como o proprietário do banco. O `-h 127.0.0.1` força a conexão TCP e permite autenticação pela senha criada no passo anterior:

```bash
cd "$HOME/hightech_school"
psql -h 127.0.0.1 -U admin_school -d hightech_school -f database/schema.sql
```

Digite a senha de `admin_school` quando solicitada. Esse comando cria as tabelas e também carrega os dados de demonstração. Execute-o somente em um banco novo.

### 6. Validar o usuário da aplicação

Teste a conexão do usuário empregado pela aplicação:

```bash
psql -h 127.0.0.1 -U admin_school -d hightech_school -c "SELECT 1;"
```

Se o comando retornar `1`, a conexão está correta. Se houver erro de autenticação, confira a senha e as regras de conexão do PostgreSQL; não substitua o usuário por `postgres`.

### 7. Configurar o ambiente PHP

Defina as variáveis antes de iniciar o PHP, substituindo os valores entre aspas pela configuração desta máquina. Use a mesma senha criada no passo 4. O exemplo vale para o servidor embutido iniciado no mesmo terminal:

```bash
export DB_HOST='127.0.0.1'
export DB_PORT='5432'
export DB_NAME='hightech_school'
export DB_USER='admin_school'
export DB_PASS='substitua-pela-senha-forte-do-postgres'
```

As variáveis definidas com `export` ficam disponíveis apenas para esse terminal e processos iniciados por ele. Não compartilhe o terminal nem salve as senhas em arquivos públicos. Este roteiro cria a conta administrativa pelo cadastro e promoção descritos no passo 9; por isso, não defina `APP_ADMIN_EMAIL` nem `APP_ADMIN_PASSWORD` aqui. Se optar por defini-las, a aplicação poderá criar outra conta administrativa automaticamente na primeira conexão. Em Apache/PHP-FPM, configure as variáveis de banco no ambiente do serviço; exportá-las no SSH não as disponibiliza automaticamente para esses serviços.

### 8. Iniciar a aplicação no servidor

Para demonstração ou avaliação, inicie o servidor embutido do PHP:

```bash
cd "$HOME/hightech_school"
php -S 0.0.0.0:8000
```

Em seguida, acesse no navegador:

```text
http://<IP_DO_SERVIDOR>:8000
```

O servidor embutido é destinado a desenvolvimento e avaliação, não a produção. Não o exponha diretamente à internet. Para uma publicação real, use um servidor web/PHP-FPM configurado com HTTPS, permissões e variáveis de ambiente próprias; simplesmente copiar os arquivos para `/var/www/html` não configura esses componentes.

### 9. Criar o administrador da aplicação

O usuário `admin_school` acessa o banco; ele não é uma conta de login do site. Abra `http://<IP_DO_SERVIDOR>:8000/login/cadastrar.php` e cadastre uma conta própria. Depois, promova essa conta a administradora no banco:

```bash
psql -h 127.0.0.1 -U admin_school -d hightech_school
```

No console `psql`, substitua o e-mail pelo usado no cadastro:

```sql
UPDATE usuarios SET perfil = 'admin' WHERE email = 'seu-email@exemplo.com';
```

4. Entre novamente. O menu administrativo permite gerenciar: **Usuários**, **Alunos**, **Cursos**, **Matrículas**, **Aulas** e **Provas**.

### Pastas principais

| Pasta | Conteúdo |
| --- | --- |
| `app/` | Telas de gestão administrativa e de aprendizagem do aluno. |
| `assets/` | Folha de estilos CSS compartilhada. |
| `database/` | Conexão com o banco (`connect.php`) e esquema SQL (`schema.sql`). |
| `docs/` | Documentação de tecnologias e roteiro de apresentação. |
| `includes/` | Autenticação (`auth.php`), funções (`functions.php`), cabeçalho e rodapé. |
| `login/` | Cadastro, login e logout. |

---

## Especificação de requisitos de software

Especificação de Requisitos de Software (SRS), organizada como referência à estrutura da ISO/IEC/IEEE 29148:2018.

## 1. Introdução

#### 1.1 Escopo do sistema

Este documento descreve a aplicação **HighTech School**, um sistema de gestão escolar que administra cursos, alunos, matrículas, aulas, avaliações e certificados. A aplicação disponibiliza páginas públicas, autenticação de alunos e administradores, fluxo de aprendizagem completo e gestão de registros acadêmicos.

O sistema é implementado em PHP, utiliza PostgreSQL para persistência e acessa o banco por meio de PDO.

#### 1.2 Propósito

O propósito do sistema é centralizar operações acadêmicas, oferecendo um fluxo educacional completo — da matrícula à certificação. A aplicação também serve como projeto de aprendizagem sobre desenvolvimento web, sessões, operações CRUD, relacionamento entre tabelas, segurança e integração PHP/PostgreSQL.

---

## 2. Descrição global

#### 2.1 Prototipagem

O repositório não contém um link de prototipagem ou especificação visual externa. A interface atual é implementada nas páginas PHP e estilizada pelo arquivo `assets/style.css`.

#### 2.2 Funções do sistema

O sistema oferece as seguintes funções principais:

1. Exibir cursos ativos e permitir inscrições públicas.
2. Cadastrar alunos como usuários e autenticar contas existentes.
3. Controlar o acesso às páginas administrativas por perfil.
4. Permitir que administradores gerenciem usuários (contas de acesso), alunos, cursos e matrículas.
5. Permitir que administradores cadastrem, editem, ordenem, publiquem e ocultem aulas com links externos.
6. Permitir que alunos com matrícula ativa acessem aulas e registrem conclusão.
7. Exibir no painel o progresso real das aulas de cada curso.
8. Permitir que administradores configurem provas e cadastrem questões de múltipla escolha.
9. Liberar a prova após conclusão das aulas obrigatórias e registrar cada tentativa e nota.
10. Emitir certificado automaticamente após aprovação e permitir consulta pública.
11. Disponibilizar ao aluno um painel com seus cursos, progresso e matrícula rápida.
12. Encerrar a sessão do usuário e redirecioná-lo ao catálogo de cursos.

#### 2.2 Classes de usuários

| Usuário | Descrição e permissões |
| --- | --- |
| **Visitante** | Pode consultar cursos e enviar inscrição. |
| **Aluno** | Pode autenticar, acessar o painel, estudar aulas, fazer provas, consultar certificados e solicitar matrículas. |
| **Administrador** | Pode acessar as telas de gestão de usuários, alunos, cursos, matrículas, aulas e provas, além das funções disponíveis ao aluno. |

O cadastro público cria contas com o perfil `aluno`. O perfil `admin` precisa ser atribuído por um processo administrativo; o esquema SQL não cria uma conta administrativa.

---

## 3. Requisitos do sistema

### 3.1 Requisitos funcionais

#### Módulo de acesso e autenticação

| ID | Título | Descrição | Prioridade |
| --- | --- | --- | --- |
| **RF01** | Cadastro de aluno | O sistema deve permitir que um visitante crie uma conta de aluno informando nome, e-mail e senha. | Alta |
| **RF02** | Validação de cadastro | O sistema deve verificar o preenchimento dos campos obrigatórios, a confirmação da senha, o tamanho mínimo de seis caracteres e a existência prévia do e-mail. | Alta |
| **RF03** | Autenticação | O sistema deve permitir que o usuário entre com e-mail e senha válidos. | Alta |
| **RF04** | Controle de acesso | O sistema deve exigir autenticação para o painel do aluno e autenticação com perfil `admin` para as páginas administrativas. | Alta |
| **RF05** | Encerramento da sessão | O sistema deve permitir que o usuário encerre a sessão e seja redirecionado ao portal. | Média |

#### Módulo institucional e de solicitações empresariais

| ID | Título | Descrição | Prioridade |
| --- | --- | --- | --- |
| **RF06** | Apresentação dos serviços | O sistema deve apresentar informações sobre os serviços corporativos e a HighTech School. | Média |
| **RF07** | Solicitação de diagnóstico | O sistema deve permitir que uma empresa envie seus dados de contato, serviço de interesse e uma mensagem opcional. | Alta |
| **RF08** | Retorno do envio | O sistema deve informar se a solicitação corporativa foi registrada ou se ocorreu uma falha. | Média |

#### Módulo de cursos, alunos e matrículas

| ID | Título | Descrição | Prioridade |
| --- | --- | --- | --- |
| **RF09** | Consulta de cursos | O sistema deve exibir os cursos cadastrados com categoria, descrição e carga horária. | Alta |
| **RF10** | Inscrição pública | O sistema deve permitir que uma pessoa informe seus dados e selecione um curso para criar ou reutilizar um registro de aluno e solicitar matrícula. | Alta |
| **RF11** | Gestão de alunos | O sistema deve permitir que administradores consultem, cadastrem, editem e excluam alunos. | Alta |
| **RF12** | Gestão de cursos | O sistema deve permitir que administradores consultem, cadastrem, editem e excluam cursos. | Alta |
| **RF13** | Gestão de matrículas | O sistema deve permitir que administradores criem matrículas, consultem a listagem combinada de alunos e cursos e cancelem matrículas. | Alta |
| **RF14** | Painel individual do aluno | O sistema deve permitir que um aluno autenticado consulte seus cursos e a carga horária total exibida no painel. | Média |
| **RF15** | Perfil profissional | O sistema deve permitir que o aluno crie ou atualize seu título profissional, biografia, links, habilidades e disponibilidade pública. | Média |
| **RF16** | Matrícula rápida | O painel do aluno deve permitir o envio de solicitação de matrícula em outro curso. | Média |

#### Módulo de talentos e oportunidades

| ID | Título | Descrição | Prioridade |
| --- | --- | --- | --- |
| **RF17** | Vitrine de talentos | O sistema deve exibir perfis marcados como disponíveis de alunos ativos. | Média |
| **RF18** | Busca de talentos | O sistema deve permitir filtrar a vitrine por nome, título profissional ou habilidades. | Média |
| **RF19** | Mural de vagas | O sistema deve listar vagas ativas com informações de empresa, modalidade, contrato e candidatura. | Média |
| **RF20** | Filtro de vagas | O sistema deve permitir filtrar as vagas por modalidade de trabalho. | Média |

#### Módulo de aulas, avaliações e certificação

| ID | Título | Descrição | Prioridade |
| --- | --- | --- | --- |
| **RF13** | Acesso às aulas | O sistema deve liberar aulas somente para aluno com matrícula ativa. | Alta |
| **RF14** | Registro de progresso | O sistema deve permitir que o aluno marque aulas como concluídas. | Alta |
| **RF15** | Gestão de aulas | Administradores devem poder cadastrar, editar, ordenar, publicar e ocultar aulas. | Alta |
| **RF16** | Gestão de provas | Administradores devem poder configurar nota mínima, tentativas e questões. | Alta |
| **RF17** | Realização de prova | A prova deve ser liberada após conclusão das aulas obrigatórias, corrigida no servidor. | Alta |
| **RF18** | Certificado verificável | Emissão de código UUID após aprovação, com validação pública. | Alta |

### 3.2 Requisitos não funcionais

| ID | Título | Descrição | Prioridade |
| --- | --- | --- | --- |
| **RNF01** | Tecnologias | PHP 7.4+ com PDO, `pdo_pgsql` e `mbstring`; PostgreSQL. | Alta |
| **RNF02** | Separação de responsabilidades | Conexão, autenticação, funções, páginas e estilos em arquivos próprios. | Média |
| **RNF03** | Compatibilidade web | Acessível por navegadores modernos sem cliente específico. | Média |
| **RNF04** | Acesso ao banco | Valores do usuário enviados por parâmetros PDO (prepared statements). | Alta |
| **RNF05** | Proteção de senhas | Senhas armazenadas como hash bcrypt. | Alta |
| **RNF06** | Proteção de páginas | Sessão e perfil validados antes de exibir dados. | Alta |
| **RNF07** | Saída HTML | Dados dinâmicos escapados com `htmlspecialchars`. | Alta |
| **RNF08** | Integridade referencial | Chaves estrangeiras com `ON DELETE CASCADE` no banco. | Alta |
| **RNF09** | Interface consistente | Cabeçalho, navegação e rodapé reutilizados via includes. | Média |
| **RNF10** | Layout adaptável | Grids e formulários responsivos via CSS. | Média |
| **RNF11** | Feedback de operações | Mensagens de sucesso, erro e validação nos formulários. | Média |

### 3.3 Regras de negócio

| ID | Título | Regra / condição |
| --- | --- | --- |
| **RN01** | Perfil inicial de cadastro | Contas criadas pelo formulário público recebem o perfil `aluno`. |
| **RN02** | Acesso administrativo | Somente uma sessão autenticada cujo perfil seja `admin` pode acessar as páginas administrativas de alunos, cursos, matrículas, aulas e provas. |
| **RN03** | Acesso ao painel do aluno | O painel individual exige uma sessão autenticada. |
| **RN04** | Senha de conta | O cadastro rejeita senhas com menos de seis caracteres e exige que a confirmação corresponda à senha. |

#### Módulo acadêmico

| ID | Título | Regra / condição |
| --- | --- | --- |
| **RN05** | Campos obrigatórios de aluno | Nome e e-mail são obrigatórios nas operações administrativas de cadastro e atualização de aluno. |
| **RN06** | Campos obrigatórios de curso | Nome, categoria e carga horária maior que zero são necessários para salvar um curso pela tela administrativa. |
| **RN07** | Relação de matrícula | Cada matrícula referencia um aluno e um curso existentes. |
| **RN08** | Exclusão em cascata | A exclusão de aluno ou curso remove do banco os registros de matrícula associados; a exclusão de aluno também remove seu perfil de talento. |
| **RN09** | Perfil de talento por aluno | Cada aluno pode ter no máximo um perfil de talento, conforme a restrição única de `perfil_talento.id_aluno`. |
| **RN10** | Visibilidade de talentos | A vitrine pública inclui apenas perfis com `disponivel_mercado = true` pertencentes a alunos ativos. |

#### Módulo corporativo e de oportunidades

| ID | Título | Regra / condição |
| --- | --- | --- |
| **RN11** | Solicitação empresarial | Empresa, responsável, e-mail e serviço de interesse são obrigatórios no formulário do portal; a nova solicitação recebe status `Pendente`. |
| **RN12** | Exibição de vagas | A listagem pública apresenta somente vagas ativas por padrão. |
| **RN13** | Candidatura | O contato de candidatura pode ser exibido como endereço de e-mail ou como link fornecido no cadastro da vaga. |
| **RN14** | CPF opcional e único quando informado | O CPF pode ficar vazio; quando informado, o índice parcial impede repetições entre valores não nulos. |

#### Módulo de aulas, avaliações e dados do aluno

| ID | Título | Regra / condição |
| --- | --- | --- |
| **RN15** | Liberação da prova | A prova ativa só pode ser realizada com matrícula ativa e após concluir todas as aulas obrigatórias que estejam publicadas. |
| **RN16** | Aprovação | A nota da prova é calculada no servidor e deve atingir ou superar a nota mínima configurada para o curso. |
| **RN17** | Limite de tentativas | A prova impede novas tentativas depois de atingir o limite configurado, entre 1 e 20 tentativas. |
| **RN18** | Emissão única | Cada matrícula aprovada recebe um único certificado com código aleatório para validação pública. |
| **RN19** | Faixa etária do aluno | A data de nascimento, quando informada, deve corresponder a uma idade entre 14 e 100 anos, rejeitando datas futuras ou incompatíveis com o público da escola. |

---

## 4. Estruturação do banco de dados

O banco PostgreSQL utiliza quatorze tabelas. Os campos e as regras abaixo correspondem ao arquivo `database/schema.sql`.

#### Entidade: `usuarios` (contas de acesso)

| Campo | Tipo | Restrições | Descrição |
| --- | --- | --- | --- |
| `id` | SERIAL | PRIMARY KEY | Identificador da conta. |
| `nome` | VARCHAR(100) | NOT NULL | Nome exibido nas páginas. |
| `email` | VARCHAR(100) | UNIQUE, NOT NULL | E-mail usado no login. |
| `senha` | VARCHAR(255) | NOT NULL | Hash de senha. |
| `perfil` | VARCHAR(20) | DEFAULT 'aluno', CHECK | Perfil: `aluno` ou `admin`. |
| `criado_em` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Data de criação. |

#### Entidade: `alunos` (cadastro acadêmico)

| Campo | Tipo | Restrições | Descrição |
| --- | --- | --- | --- |
| `id` | SERIAL | PRIMARY KEY | Identificador do aluno. |
| `nome` | VARCHAR(100) | NOT NULL | Nome completo. |
| `cpf` | VARCHAR(14) | Opcional; índice único parcial | Documento opcional; valores não nulos não podem se repetir. |
| `email` | VARCHAR(100) | NOT NULL | E-mail de contato e associação com a conta. |
| `turma` | VARCHAR(20) | Opcional | Turma ou grupo acadêmico. |
| `nascimento` | DATE | Opcional | Data de nascimento. |
| `ativo` | BOOLEAN | DEFAULT true | Status do aluno. |
| `criado_em` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Data de criação. |

#### Entidade: `cursos`

| Campo | Tipo | Restrições | Descrição |
| --- | --- | --- | --- |
| `id` | SERIAL | PRIMARY KEY | Identificador. |
| `nome` | VARCHAR(100) | NOT NULL, UNIQUE | Nome do curso. |
| `categoria` | VARCHAR(50) | NOT NULL | Área do curso. |
| `descricao` | TEXT | Opcional | Descrição. |
| `carga_horaria` | INT | NOT NULL, CHECK > 0 | Duração em horas. |
| `ativo` | BOOLEAN | DEFAULT true | Status. |

#### Entidade: `matriculas`

| Campo | Tipo | Restrições | Descrição |
| --- | --- | --- | --- |
| `id` | SERIAL | PRIMARY KEY | Identificador. |
| `id_aluno` | INT | FK → alunos, CASCADE | Aluno. |
| `id_curso` | INT | FK → cursos, CASCADE | Curso. |
| `data_matricula` | DATE | DEFAULT CURRENT_DATE | Data. |
| `status` | VARCHAR(20) | DEFAULT 'Ativa' | Situação. |

#### Entidades de aulas e progresso

| Tabela | Campo | Tipo | Restrições | Descrição |
| --- | --- | --- | --- | --- |
| `aulas` | `id` | SERIAL | PK | Identificador. |
| `aulas` | `id_curso` | INT | FK → cursos, CASCADE | Curso. |
| `aulas` | `titulo` | VARCHAR(150) | NOT NULL | Título. |
| `aulas` | `descricao`, `conteudo` | TEXT | Opcional | Material. |
| `aulas` | `video_url` | VARCHAR(500) | Opcional | Link do material. |
| `aulas` | `ordem` | INT | NOT NULL, UNIQUE por curso | Posição. |
| `aulas` | `obrigatoria`, `ativa` | BOOLEAN | DEFAULT true | Flags. |
| `progresso_aulas` | `id_matricula`, `id_aula` | INT | FK, UNIQUE em conjunto | Matrícula + aula. |
| `progresso_aulas` | `concluida_em` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Quando concluiu. |

#### Entidades de provas, questões e certificados

| Tabela | Campos principais | Regras |
| --- | --- | --- |
| `provas` | `id`, `id_curso`, `nota_minima`, `max_tentativas`, `ativa` | Uma prova por curso; nota de 1 a 100; 1 a 20 tentativas. |
| `questoes_prova` | `id`, `id_prova`, `enunciado`, `ordem` | Ordem única dentro da prova. |
| `alternativas_prova` | `id`, `id_questao`, `texto`, `correta` | Alternativas com uma resposta correta. |
| `tentativas_prova` | `id`, `id_matricula`, `id_prova`, `nota`, `aprovada`, `respostas`, `realizada_em` | Resultado e respostas em JSONB. |
| `certificados` | `id`, `id_matricula`, `id_prova`, `codigo`, `emitido_em` | Um por matrícula; código UUID único. |

---

## 5. Diagramas

### 5.1 Diagrama de entidade-relacionamento (DER)

O diagrama representa as tabelas e os relacionamentos definidos no esquema PostgreSQL.

```mermaid
erDiagram
    USUARIOS {
        int id PK
        varchar nome
        varchar email UK
        varchar senha
        varchar perfil
        timestamp criado_em
    }
    ALUNOS {
        int id PK
        varchar nome
        varchar cpf UK
        varchar email
        varchar turma
        date nascimento
        boolean ativo
        timestamp criado_em
    }
    CURSOS {
        int id PK
        varchar nome UK
        varchar categoria
        text descricao
        int carga_horaria
        boolean ativo
    }
    MATRICULAS {
        int id PK
        int id_aluno FK
        int id_curso FK
        date data_matricula
        varchar status
    }
    AULAS {
        int id PK
        int id_curso FK
        varchar titulo
        varchar video_url
        int ordem
        boolean obrigatoria
        boolean ativa
    }
    PROGRESSO_AULAS {
        int id PK
        int id_matricula FK
        int id_aula FK
        timestamp concluida_em
    }
    PROVAS {
        int id PK
        int id_curso FK, UK
        decimal nota_minima
        int max_tentativas
        boolean ativa
    }
    QUESTOES_PROVA {
        int id PK
        int id_prova FK
        text enunciado
        int ordem
    }
    ALTERNATIVAS_PROVA {
        int id PK
        int id_questao FK
        text texto
        boolean correta
    }
    TENTATIVAS_PROVA {
        int id PK
        int id_matricula FK
        int id_prova FK
        decimal nota
        boolean aprovada
        jsonb respostas
    }
    CERTIFICADOS {
        int id PK
        int id_matricula FK, UK
        int id_prova FK
        uuid codigo UK
        timestamp emitido_em
    }
    ALUNOS ||--o{ MATRICULAS : realiza
    CURSOS ||--o{ MATRICULAS : recebe
    CURSOS ||--o{ AULAS : possui
    MATRICULAS ||--o{ PROGRESSO_AULAS : registra
    AULAS ||--o{ PROGRESSO_AULAS : acompanha
    CURSOS ||--o| PROVAS : avalia
    PROVAS ||--o{ QUESTOES_PROVA : contem
    QUESTOES_PROVA ||--|{ ALTERNATIVAS_PROVA : oferece
    MATRICULAS ||--o{ TENTATIVAS_PROVA : realiza
    PROVAS ||--o{ TENTATIVAS_PROVA : recebe
    MATRICULAS ||--o| CERTIFICADOS : conquista
    PROVAS ||--o{ CERTIFICADOS : aprova
```

`USUARIOS` e `ALUNOS` são tabelas distintas; as páginas associam os registros pelo e-mail.

### 5.2 Diagrama de casos de uso

O diagrama resume as ações disponíveis para visitantes, alunos e administradores. A autorização das telas administrativas é verificada no servidor.

```mermaid
flowchart LR
    Visitante["Visitante"]
    Aluno["Aluno autenticado"]
    Admin["Administrador"]
    subgraph HighTechSchool["Sistema HighTech School"]
        Cursos(["Consultar cursos"])
        Inscricao(["Enviar inscrição em curso"])
        Cadastro(["Criar conta de aluno"])
        Login(["Entrar ou sair da conta"])
        Painel(["Consultar painel pessoal"])
        MatriculaRapida(["Solicitar matrícula em curso"])
        EstudarAulas(["Acessar aulas e registrar progresso"])
        FazerProva(["Fazer prova do curso"])
        ConsultarCertificado(["Consultar ou validar certificado"])
        GestaoUsuarios(["Gerenciar usuários"])
        GestaoAlunos(["Gerenciar alunos"])
        GestaoCursos(["Gerenciar cursos"])
        GestaoMatriculas(["Gerenciar matrículas"])
        GestaoAulas(["Gerenciar aulas e links"])
        GestaoProvas(["Configurar provas e questões"])
    end
    Visitante --> Cursos
    Visitante --> Inscricao
    Visitante --> Cadastro
    Visitante --> Login
    Visitante --> ConsultarCertificado
    Aluno --> Login
    Aluno --> Painel
    Aluno --> MatriculaRapida
    Aluno --> EstudarAulas
    Aluno --> FazerProva
    Aluno --> ConsultarCertificado
    Admin --> Login
    Admin --> GestaoUsuarios
    Admin --> GestaoAlunos
    Admin --> GestaoCursos
    Admin --> GestaoMatriculas
    Admin --> GestaoAulas
    Admin --> GestaoProvas
```

### 5.3 Diagrama de classes conceitual

O diagrama apresenta uma visão conceitual das entidades persistidas e das associações existentes. Ele não representa classes PHP: o projeto implementa as operações como funções e páginas.

```mermaid
classDiagram
    class Usuario {
        +int id
        +string nome
        +string email
        +string senha
        +string perfil
        +timestamp criado_em
    }
    class Aluno {
        +int id
        +string nome
        +string cpf
        +string email
        +string turma
        +date nascimento
        +bool ativo
    }
    class Curso {
        +int id
        +string nome
        +string categoria
        +string descricao
        +int carga_horaria
        +bool ativo
    }
    class Matricula {
        +int id
        +int id_aluno
        +int id_curso
        +date data_matricula
        +string status
    }
    class Aula {
        +int id
        +int id_curso
        +string titulo
        +string video_url
        +int ordem
        +bool obrigatoria
        +bool ativa
    }
    class ProgressoAula {
        +int id
        +int id_matricula
        +int id_aula
        +timestamp concluida_em
    }
    class Prova {
        +int id
        +int id_curso
        +float nota_minima
        +int max_tentativas
        +bool ativa
    }
    class QuestaoProva {
        +int id
        +int id_prova
        +string enunciado
        +int ordem
    }
    class AlternativaProva {
        +int id
        +int id_questao
        +string texto
        +bool correta
    }
    class TentativaProva {
        +int id
        +int id_matricula
        +int id_prova
        +float nota
        +bool aprovada
    }
    class Certificado {
        +int id
        +int id_matricula
        +string codigo
        +timestamp emitido_em
    }
    Aluno "1" --> "0..*" Matricula : realiza
    Curso "1" --> "0..*" Matricula : recebe
    Curso "1" --> "0..*" Aula : possui
    Matricula "1" --> "0..*" ProgressoAula : registra
    Aula "1" --> "0..*" ProgressoAula : concluida
    Curso "1" --> "0..1" Prova : avalia
    Prova "1" --> "1..*" QuestaoProva : contem
    QuestaoProva "1" --> "4" AlternativaProva : oferece
    Matricula "1" --> "0..*" TentativaProva : realiza
    Prova "1" --> "0..*" TentativaProva : recebe
    Matricula "1" --> "0..1" Certificado : conquista
```

### 5.4 Diagrama de fluxo de inscrição em curso

O fluxo abaixo representa o envio público de inscrição tratado por `aplicacao.php`.

```mermaid
flowchart TD
    Inicio(["Visitante abre a HighTech School"])
    Exibir["Sistema lista os cursos disponíveis"]
    Preencher["Pessoa informa nome, e-mail e curso"]
    Enviar["Formulário envia os dados por POST"]
    Validar{"Campos obrigatórios preenchidos?"}
    ErroCampos["Sistema exibe aviso de validação"]
    BuscarAluno["Sistema procura aluno pelo e-mail"]
    Existe{"Aluno já cadastrado?"}
    UsarAluno["Sistema reutiliza o registro encontrado"]
    CriarAluno["Sistema tenta cadastrar um novo aluno"]
    Matricular{"Foi possível identificar o aluno e criar matrícula?"}
    Sucesso["Sistema exibe confirmação da inscrição"]
    ErroMatricula["Sistema exibe mensagem de falha"]
    Fim(["Página apresenta o resultado"])
    Inicio --> Exibir --> Preencher --> Enviar --> Validar
    Validar -- Não --> ErroCampos --> Fim
    Validar -- Sim --> BuscarAluno --> Existe
    Existe -- Sim --> UsarAluno --> Matricular
    Existe -- Não --> CriarAluno --> Matricular
    Matricular -- Sim --> Sucesso --> Fim
    Matricular -- Não --> ErroMatricula --> Fim
```

### 5.5 Diagrama de fluxo de aprendizagem

```mermaid
flowchart TD
    Inicio(["Aluno faz login"])
    Painel["Acessa o painel pessoal"]
    Matricula{"Tem matrícula ativa?"}
    SolicitarMatricula["Solicita matrícula em um curso"]
    VerAulas["Acessa as aulas do curso"]
    EstudarAula["Abre o material da aula"]
    Concluir["Marca a aula como concluída"]
    TodasConcluidas{"Todas as aulas obrigatórias concluídas?"}
    ProximaAula["Avança para a próxima aula"]
    ProvaLiberada["Prova liberada"]
    FazerProva["Responde a prova"]
    Correcao["Servidor corrige as respostas"]
    Aprovado{"Nota >= nota mínima?"}
    Certificado["Certificado emitido com código UUID"]
    Reprovado{"Tentativas restantes?"}
    TentarNovamente["Pode tentar novamente"]
    LimiteTentativas["Limite de tentativas atingido"]
    Fim(["Fim do fluxo"])
    Inicio --> Painel --> Matricula
    Matricula -- Não --> SolicitarMatricula --> Painel
    Matricula -- Sim --> VerAulas --> EstudarAula --> Concluir --> TodasConcluidas
    TodasConcluidas -- Não --> ProximaAula --> EstudarAula
    TodasConcluidas -- Sim --> ProvaLiberada --> FazerProva --> Correcao --> Aprovado
    Aprovado -- Sim --> Certificado --> Fim
    Aprovado -- Não --> Reprovado
    Reprovado -- Sim --> TentarNovamente --> FazerProva
    Reprovado -- Não --> LimiteTentativas --> Fim
```
