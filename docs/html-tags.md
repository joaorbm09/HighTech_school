# Tecnologias utilizadas — HighTech School

Este documento descreve todas as tecnologias, linguagens, tags, propriedades e recursos utilizados no projeto, explicando **o que cada um faz** e **por que foi escolhido**.

---

## 1. HTML — Tags utilizadas nas páginas

As telas são arquivos PHP que geram HTML para o navegador. As tags abaixo aparecem nos arquivos `.php` e definem a estrutura, o conteúdo e a interação das páginas.

### Estrutura do documento

| Tag | Função na página | Por que foi usada |
| --- | --- | --- |
| `<!DOCTYPE html>` | Declara que o documento segue o padrão HTML5. | Garante que o navegador interprete a página no modo padrão, evitando comportamentos imprevisíveis. |
| `<html lang="pt-br">` | Elemento raiz do documento, com o idioma definido. | Envolve todo o HTML e o atributo `lang` informa ao navegador e a leitores de tela que o conteúdo está em português. |
| `<head>` | Agrupa metadados, título e recursos externos. | Separa informações técnicas (charset, viewport, CSS) do conteúdo visível da página. |
| `<meta charset="UTF-8">` | Define a codificação de caracteres. | Permite exibir corretamente acentos e caracteres especiais do português (ç, ã, é, etc.). |
| `<meta name="viewport" ...>` | Configura a escala para telas de dispositivos móveis. | Torna a página responsiva, adaptando o layout a celulares e tablets. |
| `<title>` | Define o título exibido na aba do navegador. | Identifica a página para o usuário e para mecanismos de busca. |
| `<link rel="stylesheet">` | Carrega a folha de estilos `assets/style.css`. | Centraliza o visual em um arquivo CSS externo, evitando repetição de estilos em cada página. |
| `<style>` | Define regras CSS específicas da página. | Usado na tela de certificado para estilos de impressão que não precisam estar no CSS global. |
| `<body>` | Envolve todo o conteúdo visível da página. | É o contêiner principal onde o navegador renderiza texto, formulários, tabelas e demais elementos. |

### Regiões e organização do conteúdo

| Tag | Função na página | Por que foi usada |
| --- | --- | --- |
| `<header>` | Apresenta o nome "HighTech School" no topo. | Tag semântica que identifica o cabeçalho do site, melhorando a acessibilidade e a organização. |
| `<nav>` | Reúne os links de navegação principal. | Indica ao navegador e a leitores de tela que aquele bloco é a navegação do site. |
| `<main>` | Marca o conteúdo principal da página. | Separa semanticamente o conteúdo central da navegação e do rodapé. |
| `<section>` | Agrupa conteúdo relacionado (cursos, formulários, blocos). | Divide a página em áreas temáticas com significado próprio. |
| `<article>` | Representa um item independente, como um cartão de curso. | Indica que o conteúdo faz sentido sozinho (um curso, uma aula), útil para acessibilidade. |
| `<footer>` | Exibe o rodapé com a identificação do sistema. | Tag semântica que delimita a área final da página. |
| `<div>` | Agrupa elementos para layout e classes CSS. | Usado quando não há uma tag semântica mais específica; serve como contêiner genérico de estilo. |

### Títulos e texto

| Tag | Função na página | Por que foi usada |
| --- | --- | --- |
| `<h1>` | Título principal da página. | Hierarquia de títulos ajuda na organização visual e é importante para acessibilidade e SEO. |
| `<h2>` | Título de seção. | Segundo nível de hierarquia, usado para separar blocos de conteúdo. |
| `<h3>` | Subtítulo de curso, cartão ou bloco. | Terceiro nível, usado dentro de seções para detalhar subgrupos. |
| `<h4>` | Subtítulo inferior. | Quarto nível, usado em áreas com muitas subdivisões. |
| `<p>` | Parágrafos de descrição, instruções e boas-vindas. | Estrutura textual básica que separa blocos de texto de forma semântica. |
| `<strong>` | Destaque semântico em rótulos e avisos. | Indica importância; leitores de tela enfatizam o texto, e o navegador exibe em negrito. |
| `<small>` | Observações auxiliares (categorias, datas, requisitos). | Reduz visualmente textos secundários sem perder o significado. |
| `<span>` | Agrupa texto curto em linha (nome do usuário, badges). | Permite aplicar classes CSS a um trecho de texto sem quebrar o fluxo. |
| `<br>` | Quebra de linha pontual. | Força uma nova linha dentro de um bloco sem criar um novo parágrafo. |
| `<hr>` | Separação visual entre blocos. | Cria uma linha horizontal para dividir seções de conteúdo. |

### Links e listas

| Tag | Função na página | Por que foi usada |
| --- | --- | --- |
| `<a>` | Links de navegação, ações e abertura de materiais. | Cria âncoras clicáveis; é o elemento fundamental para navegar entre páginas. |
| `<ul>` | Lista sem numeração. | Agrupa itens onde a ordem não importa (menus, habilidades, requisitos). |
| `<ol>` | Lista numerada. | Agrupa itens onde a sequência importa (passos, instruções). |
| `<li>` | Item de lista. | Cada entrada dentro de `<ul>` ou `<ol>`. |

### Formulários

| Tag | Função na página | Por que foi usada |
| --- | --- | --- |
| `<form>` | Agrupa campos e envia dados ao PHP por POST. | É o elemento que permite ao usuário enviar informações ao servidor. |
| `<label>` | Identifica cada campo do formulário. | Associa o texto ao controle (`for` + `id`), melhorando acessibilidade — clicar no rótulo ativa o campo. |
| `<input>` | Recebe valores: texto, e-mail, senha, número, data, hidden, checkbox, radio. | Elemento versátil para captura de dados; o atributo `type` adapta o comportamento (validação, teclado). |
| `<select>` | Lista de opções suspensa. | Permite ao usuário escolher entre opções pré-definidas (cursos, status). |
| `<option>` | Uma opção dentro de `<select>`. | Define cada item disponível na lista. |
| `<textarea>` | Campo de texto multilinha. | Recebe textos longos como descrições, mensagens e biografias. |
| `<button>` | Botão de envio ou ação. | Mais flexível que `<input type="submit">`; permite conteúdo rico dentro do botão. |
| `<fieldset>` | Agrupa controles relacionados. | Nas provas, delimita visualmente as alternativas de cada questão. |
| `<legend>` | Título do grupo `<fieldset>`. | Exibe o enunciado da questão como título do grupo de alternativas. |

### Tabelas

| Tag | Função na página | Por que foi usada |
| --- | --- | --- |
| `<table>` | Organiza dados em linhas e colunas. | Ideal para listagens administrativas (alunos, cursos, matrículas) onde os dados são tabulares. |
| `<thead>` | Cabeçalho da tabela. | Separa semanticamente os rótulos das colunas dos dados. |
| `<tbody>` | Corpo da tabela com os registros. | Agrupa as linhas de dados, permitindo ao navegador renderizar o cabeçalho primeiro. |
| `<tr>` | Linha da tabela. | Define cada registro ou linha de cabeçalho. |
| `<th>` | Célula de cabeçalho. | Identifica colunas com ênfase e semântica de cabeçalho. |
| `<td>` | Célula de dados. | Exibe os valores de cada campo do registro. |

---

## 2. CSS — Propriedades e recursos utilizados

O estilo visual do sistema é definido em [assets/style.css](../assets/style.css) e, pontualmente, em blocos `<style>` dentro de páginas específicas.

### Variáveis CSS (Custom Properties)

```css
:root {
    --primary: #185abc;
    --text-heading: #243447;
    --text-muted: #5c6770;
    --border-color: #d7dde3;
    --bg-main: #f4f6f8;
    --shadow-lg: 0 3px 10px rgba(0, 0, 0, 0.12);
}
```

**Por que:** variáveis CSS permitem alterar cores e valores em um único lugar, mantendo a consistência visual em todo o sistema.

### Propriedades e técnicas utilizadas

| Propriedade / Recurso | Onde é usado | Por que foi usado |
| --- | --- | --- |
| `box-sizing: border-box` | Seletor universal `*` | Faz com que `padding` e `border` sejam incluídos no tamanho do elemento, facilitando cálculos de layout. |
| `margin`, `padding` | Todo o sistema | Controlam espaçamento externo e interno dos elementos. |
| `font-family: Arial, sans-serif` | `body` | Define a fonte padrão; `sans-serif` é a fonte genérica caso Arial não esteja disponível. |
| `line-height` | `body`, títulos | Controla o espaçamento entre linhas, melhorando a leitura. |
| `color`, `background` | Diversos elementos | Definem cores de texto e fundo para criar contraste e identidade visual. |
| `border`, `border-radius` | Cards, inputs, tabelas, botões | `border` cria contornos visíveis; `border-radius` arredonda cantos para um visual moderno. |
| `text-decoration` | Links e botões | Remove ou adiciona sublinhado em links. |
| `display: flex` | Navegação (`.nav-links`) | Distribui os links de navegação horizontalmente com alinhamento flexível. |
| `flex-wrap: wrap` | Navegação | Permite que os links quebrem para a linha seguinte em telas pequenas. |
| `gap` | Grids e flex | Define o espaçamento entre itens sem usar margens individuais. |
| `display: grid` | `.grid`, `.stat-grid`, `.form-grid` | Cria layouts de grade responsivos para cartões, estatísticas e formulários. |
| `grid-template-columns` | Grids | `repeat(auto-fit, minmax(240px, 1fr))` cria colunas que se ajustam automaticamente à largura disponível. |
| `width: min(1100px, 94%)` | `.container` | Limita a largura máxima a 1100px, mas usa 94% em telas menores. Recurso CSS moderno. |
| `min-height: calc(...)` | `main.container` | Garante que o conteúdo principal ocupe pelo menos a altura da tela, empurrando o rodapé para baixo. |
| `overflow-x: auto` | `.table-wrap` | Permite rolagem horizontal em tabelas largas em telas pequenas. |
| `border-collapse: collapse` | `table` | Remove espaçamento duplo entre bordas de células da tabela. |
| `vertical-align: top` | `td`, `th` | Alinha o conteúdo ao topo da célula. |
| `cursor: pointer` | Botões | Indica ao usuário que o elemento é clicável. |
| `margin-left: auto` | `.nav-user` | Empurra o nome do usuário para a direita usando o espaço disponível do flexbox. |
| `@media (max-width: 600px)` | Responsividade | Aplica regras específicas para telas pequenas (celulares), ajustando navegação e cards. |

### Classes de componentes

| Classe | Propósito |
| --- | --- |
| `.container` | Centraliza e limita a largura do conteúdo. |
| `.site-header` | Estilo do cabeçalho escuro com o nome do sistema. |
| `.brand` | Estilo do logo/nome no cabeçalho. |
| `.site-nav`, `.nav-links` | Barra de navegação e disposição dos links. |
| `.admin-card`, `.card`, `.stat-card` | Cartões de conteúdo com fundo branco, borda e arredondamento. |
| `.grid`, `.stat-grid`, `.form-grid` | Sistemas de grade para diferentes tipos de conteúdo. |
| `.hero` | Bloco de destaque na página principal. |
| `.btn`, `.btn-outline`, `.btn-primary`, `.btn-success`, `.btn-danger` | Estilos de botão com variações de cor para cada tipo de ação. |
| `.badge`, `.badge-success` | Etiquetas visuais para status e categorias. |
| `.alert`, `.alert-success`, `.alert-warning`, `.alert-danger` | Mensagens de feedback ao usuário com cores contextuais. |
| `.styled-table`, `.table-wrap` | Tabelas administrativas com estilo e rolagem horizontal. |
| `.form-group` | Agrupamento visual de label + campo nos formulários. |
| `.site-footer` | Estilo do rodapé. |
| `.muted` | Texto com cor reduzida para informações secundárias. |

---

## 3. PHP — Recursos e funções utilizados

O sistema é inteiramente construído em PHP, que gera o HTML no servidor. O PHP foi escolhido por ser uma linguagem voltada para web, com integração nativa a bancos de dados e gerenciamento de sessões.

### Recursos da linguagem

| Recurso | Onde é usado | Por que foi usado |
| --- | --- | --- |
| `<?php ... ?>` | Todos os arquivos `.php` | Delimitam blocos de código PHP dentro do HTML. |
| `require_once` | Headers, includes, conexão | Inclui arquivos uma única vez, evitando duplicação e erros de redefinição de funções. |
| `session_start()` | `auth.php` | Inicia a sessão PHP para manter a identificação do usuário entre páginas. |
| `$_SESSION` | Auth, formulários, CSRF | Armazena dados da sessão do usuário (id, nome, email, perfil, tokens). |
| `$_POST` | Formulários | Acessa dados enviados pelo formulário via método POST. |
| `$_GET` | Parâmetros de URL | Acessa parâmetros da URL, como códigos de certificado e mensagens. |
| `$_SERVER` | Detecção de método, caminhos | `$_SERVER['REQUEST_METHOD']` verifica se o acesso é GET ou POST. |
| `getenv()` | `database/connect.php` | Lê uma variável de ambiente para configurar a conexão e a conta administrativa sem depender apenas de valores fixos no código. |
| `header('Location: ...')` | Redirecionamentos | Redireciona o navegador para outra página após uma ação (login, cadastro, etc.). |
| `exit` | Após redirecionamentos | Interrompe a execução para garantir que nada mais seja processado após o redirecionamento. |
| `echo`, `<?= ... ?>` | Todo o sistema | Envia conteúdo (HTML, variáveis) para o navegador. |
| `htmlspecialchars()` | Exibição de dados | Escapa caracteres especiais (`<`, `>`, `"`, `'`, `&`) para prevenir ataques XSS. |
| `password_hash()` | Cadastro de usuário | Gera um hash seguro da senha usando bcrypt, nunca armazena a senha em texto simples. |
| `password_verify()` | Login | Compara a senha digitada com o hash salvo sem expor o valor original. |
| `random_bytes()` | Tokens CSRF | Gera bytes aleatórios criptograficamente seguros para proteger formulários. |
| `bin2hex()` | Tokens CSRF | Converte os bytes em uma string hexadecimal legível para uso em campos `hidden`. |
| `hash_equals()` | Validação CSRF | Compara tokens de forma segura contra timing attacks (ataques de temporização). |
| `filter_var()` | Validação de inputs | Valida e sanitiza dados de entrada (ex.: `FILTER_VALIDATE_INT`, `FILTER_VALIDATE_EMAIL`). |
| `trim()`, `strtolower()` | Normalização de dados | Remove espaços e normaliza capitalização em e-mails e textos. |
| `preg_match()` | Validação de UUID | Verifica o formato do código do certificado com expressão regular. |
| `http_response_code()` | Erros HTTP | Define códigos HTTP (400, 404, 500, 503) para erros contextuais. |
| `error_log()` | Tratamento de exceções | Registra erros técnicos no log do servidor sem expor informações ao usuário. |
| `json_encode()` / `json_decode()` | Provas | Serializa e desserializa respostas da prova em formato JSON. |
| `date()` / `strtotime()` | Datas | Formata e manipula datas para exibição e validação de idade. |
| `number_format()` | Notas | Formata notas da prova com casas decimais. |
| `try / catch (PDOException)` | Operações de banco | Captura erros de banco de dados sem interromper abruptamente a página. |

### Configuração com `getenv()`

No arquivo [`database/connect.php`](../database/connect.php), `getenv()` lê uma variável de ambiente definida no sistema ou no processo que iniciou o PHP. Assim, as configurações podem ser alteradas sem editar o código:

```php
$host = getenv('DB_HOST') ?: '192.168.10.52';
$user = getenv('DB_USER') ?: 'admin_school';
$password = getenv('DB_PASS') ?: 'admin123';
```

O operador `?:` usa o valor à direita quando `getenv()` não encontra a variável ou quando ela contém um valor vazio. Por exemplo, se `DB_HOST` estiver configurada como `localhost`, a aplicação usará `localhost`; se não estiver configurada, usará `192.168.10.52`. O mesmo padrão é usado para a porta, o nome do banco e os dados da conta administrativa.

As variáveis `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS` definem os dados da conexão PostgreSQL. `APP_ADMIN_EMAIL` e `APP_ADMIN_PASSWORD` definem o e-mail e a senha usados para criar a conta administrativa padrão quando ela ainda não existe. Esses valores alternativos no código permitem que o projeto funcione sem variáveis de ambiente, desde que correspondam à configuração local do banco.

`getenv()` não carrega automaticamente um arquivo `.env`: as variáveis precisam ser definidas no ambiente antes de iniciar o PHP. Por exemplo, no PowerShell do Windows, defina-as no mesmo terminal em que iniciará o servidor:

```powershell
$env:DB_HOST = "localhost"
$env:DB_NAME = "hightech_school"
$env:DB_USER = "admin_school"
$env:DB_PASS = "sua_senha_local"
php -S localhost:8000
```

No Linux, o equivalente é usar `export DB_HOST='localhost'` (e definir as demais variáveis) antes de executar `php -S localhost:8000`. Use os dados reais da sua instalação do PostgreSQL. Para evitar expor credenciais, não compartilhe senhas nem as publique no repositório.

### Funções próprias do sistema

As funções abaixo são definidas em [`includes/functions.php`](../includes/functions.php) e [`includes/auth.php`](../includes/auth.php). As funções de banco de dados recebem a conexão PDO como primeiro parâmetro. Os retornos indicados descrevem os resultados mais importantes usados pelas páginas.

#### Autenticação e usuários

| Função | O que faz |
| --- | --- |
| `usuarioLogado()` (`auth.php`) | Verifica se existe um ID de usuário na sessão. Retorna `true` ou `false`. |
| `obterUsuarioLogado()` (`auth.php`) | Retorna os dados básicos do usuário da sessão (`id`, `nome`, `email` e `perfil`) ou `null` se não houver usuário autenticado. |
| `exigirLogin()` (`auth.php`) | Protege uma página para que só usuários autenticados possam acessá-la; redireciona ao login e encerra a execução se ninguém estiver logado. |
| `exigirAdmin()` (`auth.php`) | Exige login e verifica se o perfil é `admin`; caso contrário, redireciona para a aplicação com uma mensagem de acesso negado. |
| `cadastrarUsuario()` | Normaliza o e-mail, gera um hash bcrypt para a senha e insere a conta com o perfil informado (por padrão, `aluno`). Retorna `true` ou `false`. |
| `autenticarUsuario()` | Procura a conta pelo e-mail e compara a senha informada com o hash armazenado. Retorna os dados do usuário se as credenciais forem válidas ou `false`. |
| `buscarUsuarioPorEmail()` | Procura e retorna uma conta pelo e-mail, sem diferenciar maiúsculas de minúsculas; retorna `false` se não encontrar ou ocorrer uma falha. |
| `buscarAlunoPorEmail()` | Procura o cadastro acadêmico de aluno associado ao e-mail, sem diferenciar maiúsculas de minúsculas. |

#### Cadastro e validação de alunos

| Função | O que faz |
| --- | --- |
| `normalizarCpf()` | Remove caracteres que não sejam dígitos e limita o CPF a 11 números. Se o valor estiver vazio, retorna uma string vazia. |
| `validarCpf()` | Verifica se o CPF normalizado tem 11 dígitos e não é formado pelo mesmo dígito repetido. Não calcula os dígitos verificadores do CPF. Retorna `true` ou `false`. |
| `cadastrarAluno()` | Insere os dados acadêmicos do aluno; normaliza o CPF e grava campos opcionais vazios como nulos. Retorna `true` ou `false`. |
| `validarDataNascimento()` | Valida uma data no formato `AAAA-MM-DD` e aceita idades de 14 a 100 anos. Retorna a data normalizada, `null` se estiver vazia ou `false` se for inválida; em caso de erro, preenche a mensagem recebida por referência. |

#### Matrículas, cursos e aulas

| Função | O que faz |
| --- | --- |
| `listarCursosDisponiveisParaMatricula()` | Lista cursos ativos nos quais o aluno ainda não tem matrícula ativa. Retorna uma lista de cursos ou `false` em caso de falha. |
| `matricularAlunoEmCursoAtivo()` | Confere se o curso está ativo e, em uma transação, cria uma matrícula ou reativa uma existente. Retorna `created`, `already_enrolled`, `course_unavailable` ou `error`. |
| `obterMatriculaAtivaAlunoCurso()` | Busca os dados de uma matrícula ativa do aluno no curso indicado. Retorna o registro encontrado, `false` se não houver resultado ou `null` em caso de falha. |
| `listarCursosDoAluno()` | Monta os dados dos cursos matriculados pelo aluno, incluindo situação da matrícula, progresso nas aulas, prova, tentativas e certificado. Retorna uma lista; em caso de falha, retorna uma lista vazia. |
| `listarAulasDaMatricula()` | Lista as aulas ativas do curso da matrícula e informa se cada uma foi concluída. Retorna uma lista ou `false` em caso de falha. |
| `formatarConteudoAula()` | Escapa o conteúdo para exibição segura, preserva quebras de linha e transforma URLs HTTP/HTTPS válidas em links que abrem em outra aba. |
| `concluirAulaDaMatricula()` | Registra a conclusão de uma aula ativa que pertence ao curso da matrícula ativa. Retorna `true` se a conclusão existir, inclusive se já estava registrada; retorna `false` ou `null` se não puder confirmá-la ou ocorrer uma falha. |
| `contarAulasObrigatoriasPendentes()` | Conta as aulas obrigatórias e ativas ainda não concluídas na matrícula. Retorna a quantidade ou `null` em caso de falha. |

#### Provas e tentativas

| Função | O que faz |
| --- | --- |
| `obterProvaDoCurso()` | Busca as configurações da prova de um curso; o parâmetro `somente_ativa` restringe a busca a provas publicadas. Retorna os dados, `false` se não encontrar ou `null` em caso de falha. |
| `salvarProvaDoCurso()` | Cria a configuração da prova do curso ou atualiza a nota mínima e o limite de tentativas existentes. Retorna `true` ou `false`. |
| `definirProvaAtiva()` | Publica ou despublica a prova. Antes de publicar, exige ao menos uma questão e exatamente quatro alternativas, com uma correta por questão. Retorna `published`, `unpublished`, `invalid_questions`, `unchanged` ou `error`. |
| `listarQuestoesProvaAdmin()` | Lista questões e alternativas para administração, incluindo qual alternativa é correta. |
| `salvarQuestaoProva()` | Cria ou atualiza uma questão e suas alternativas em uma transação. Retorna `created`, `updated`, `not_found`, `order_conflict` ou `error`. |
| `excluirQuestaoProva()` | Exclui uma questão somente se ela pertencer à prova indicada. Retorna `true` se uma questão foi excluída ou `false` caso contrário. |
| `listarQuestoesProvaAluno()` | Lista questões e alternativas para o aluno sem incluir a informação de qual alternativa é correta. |
| `enviarTentativaProva()` | Valida as respostas e as regras de acesso à prova, calcula a nota, registra a tentativa e emite certificado se o aluno for aprovado. Retorna um resultado com `status` (como `passed`, `failed`, `invalid_answers` ou `attempts_exhausted`), nota e tentativas restantes; uma aprovação também inclui o código do certificado. |
| `listarTentativasProvaAluno()` | Lista o histórico de notas, aprovação e datas das tentativas do aluno para uma prova, da mais recente para a mais antiga. |

#### Certificados

| Função | O que faz |
| --- | --- |
| `obterCertificadoDaMatricula()` | Busca os dados do certificado da matrícula, incluindo código, data de emissão, aluno, curso e carga horária. Retorna o registro, `false` se não houver certificado ou `null` em caso de falha. |
| `validarCertificadoPorCodigo()` | Confere um código de certificado e retorna os dados públicos do certificado correspondente. Retorna `false` se não encontrar ou `null` em caso de falha. |

### Padrões de arquitetura

| Padrão | Descrição | Por que foi adotado |
| --- | --- | --- |
| Separação de responsabilidades | Conexão (`connect.php`), autenticação (`auth.php`), funções (`functions.php`), páginas e estilo separados. | Facilita a manutenção, testes e reutilização de código. |
| Includes compartilhados | Header e footer são incluídos em todas as páginas. | Mantém a identidade visual e a navegação consistentes. |
| Funções centralizadas | `functions.php` concentra consultas e regras de negócio. | Evita duplicação de código SQL e lógica em múltiplas páginas. |
| Validação no servidor | Toda validação acontece no PHP, nunca apenas no JavaScript. | Impede que dados inválidos cheguem ao banco, mesmo que o navegador seja manipulado. |

---

## 4. SQL / PostgreSQL — Estrutura e recursos utilizados

O banco de dados é PostgreSQL, escolhido por suportar chaves estrangeiras, restrições avançadas, transações ACID, tipos como `UUID` e `JSONB`, e por ser um banco relacional robusto e gratuito.

### Comandos e cláusulas SQL

| Recurso | Onde é usado | Por que foi usado |
| --- | --- | --- |
| `CREATE TABLE` | `schema.sql` | Define a estrutura das 11 tabelas do sistema. |
| `SERIAL PRIMARY KEY` | Todas as tabelas | Cria uma chave primária com incremento automático, garantindo um identificador único por registro. |
| `VARCHAR(n)` | Nomes, e-mails, títulos | Limita o tamanho de campos de texto, protegendo contra dados excessivos. |
| `TEXT` | Descrições, enunciados, conteúdo | Permite textos sem limite fixo para campos que precisam de mais espaço. |
| `INT` | IDs, carga horária, ordem | Tipo numérico inteiro para contadores e referências. |
| `BOOLEAN` | Ativo, obrigatória, correta, aprovada | Armazena verdadeiro/falso para flags de estado. |
| `DATE` | Nascimento, data de matrícula | Armazena apenas a data, sem horário. |
| `TIMESTAMP` | Criação, conclusão, emissão | Armazena data e hora para registros cronológicos. |
| `NUMERIC(5,2)` | Notas | Armazena valores decimais com precisão exata (ex.: 85.50). |
| `SMALLINT` | Máximo de tentativas | Inteiro menor, adequado para valores pequenos (1 a 20). |
| `UUID` | Código do certificado | Tipo nativo do PostgreSQL para identificadores universais únicos. |
| `JSONB` | Respostas da prova | Armazena dados JSON em formato binário para consultas eficientes. |
| `NOT NULL` | Campos obrigatórios | Impede que um campo fique vazio, garantindo dados mínimos. |
| `DEFAULT` | Status, datas, flags | Define um valor automático quando o campo não é informado. |
| `UNIQUE` | E-mail, CPF, matrícula, código | Impede duplicações, protegendo a integridade dos dados. |
| `CHECK` | Nota, tentativas, carga horária, perfil | Valida valores no nível do banco (ex.: nota entre 0 e 100, perfil só pode ser 'aluno' ou 'admin'). |
| `REFERENCES ... ON DELETE CASCADE` | Chaves estrangeiras | Conecta tabelas e remove automaticamente dados dependentes quando o registro pai é excluído. |
| `CREATE UNIQUE INDEX` | `alunos_email_unique` | Índice único com `LOWER(email)` para impedir e-mails duplicados independentemente de maiúsculas/minúsculas. |
| `ON CONFLICT DO NOTHING` | Inserts iniciais, progresso | Evita erro quando o registro já existe, tornando a operação idempotente. |
| `INSERT INTO ... VALUES` | Cadastros e inscrições | Adiciona novos registros às tabelas. |
| `SELECT ... FROM ... WHERE` | Consultas | Busca registros com filtros. |
| `UPDATE ... SET ... WHERE` | Edições | Atualiza dados existentes. |
| `DELETE FROM ... WHERE` | Exclusões | Remove registros. |
| `JOIN` | Listagens combinadas | Combina dados de múltiplas tabelas (alunos + cursos em matrículas, por exemplo). |
| `COUNT`, `SUM`, `AVG` | Estatísticas | Funções de agregação para contagens, totais e médias. |
| `ORDER BY` | Listagens | Ordena resultados por campo (nome, ordem, data). |
| `LIMIT` | Consultas | Limita o número de resultados retornados. |
| `FOR UPDATE` | Transações de matrícula | Bloqueia a linha durante a verificação, prevenindo condições de corrida. |
| `BEGIN` / `COMMIT` / `ROLLBACK` | Transações (via PDO) | Garante que operações compostas sejam atômicas — ou tudo é salvo, ou nada. |

### Tabelas do sistema

| Tabela | Registros armazenados |
| --- | --- |
| `usuarios` | Contas de acesso com nome, e-mail, hash de senha e perfil. |
| `alunos` | Dados acadêmicos separados da conta de autenticação. |
| `cursos` | Catálogo com nome, categoria, descrição e carga horária. |
| `matriculas` | Relação aluno × curso com data e status. |
| `aulas` | Materiais publicados por curso, com título, link e ordem. |
| `progresso_aulas` | Registro de conclusão de cada aula por matrícula. |
| `provas` | Configuração da avaliação por curso (nota mínima, tentativas). |
| `questoes_prova` | Enunciados das questões de múltipla escolha. |
| `alternativas_prova` | As 4 alternativas de cada questão, com a correta marcada. |
| `tentativas_prova` | Histórico de tentativas com nota, aprovação e respostas (JSONB). |
| `certificados` | Certificados emitidos com código UUID único. |

---

## 5. PDO — Acesso ao banco de dados

O PHP acessa o PostgreSQL por meio da extensão **PDO** (PHP Data Objects).

| Recurso PDO | Como é usado | Por que foi escolhido |
| --- | --- | --- |
| `new PDO(...)` | `connect.php` | Cria a conexão com o banco de forma orientada a objetos. |
| `PDO::ERRMODE_EXCEPTION` | Configuração | Erros de banco se tornam exceções, permitindo tratamento com `try/catch`. |
| `PDO::FETCH_ASSOC` | Configuração | Resultados são retornados como arrays associativos (`$row['nome']`), mais legíveis. |
| `$conexao->prepare()` | Todas as consultas | Prepara a instrução SQL antes de executar, separando o código SQL dos dados. |
| `$stmt->bindParam()` | Inserções e atualizações | Vincula variáveis PHP aos placeholders do SQL, prevenindo SQL injection. |
| `$stmt->execute()` | Todas as consultas | Executa a instrução preparada com os parâmetros vinculados. |
| `$stmt->fetch()` | Consultas de registro único | Retorna uma linha do resultado. |
| `$stmt->fetchAll()` | Listagens | Retorna todas as linhas do resultado. |
| `$stmt->rowCount()` | Verificações | Conta as linhas afetadas por INSERT, UPDATE ou DELETE. |
| `$conexao->beginTransaction()` | Operações compostas | Inicia uma transação para garantir atomicidade. |
| `$conexao->commit()` | Após sucesso | Confirma todas as operações da transação. |
| `$conexao->rollBack()` | Após falha | Desfaz todas as operações da transação. |

**Por que PDO e não `pg_*`:** PDO oferece uma interface unificada, suporte a instruções preparadas de forma nativa e facilita a troca de banco se necessário.

---

## 6. Sessões PHP — Gerenciamento de estado

| Recurso | Como é usado | Por que é necessário |
| --- | --- | --- |
| `session_start()` | Início de cada requisição | HTTP é stateless; a sessão mantém o estado do usuário entre páginas. |
| `$_SESSION['user_id']` | Identificação | Armazena o ID do usuário logado para verificar autenticação. |
| `$_SESSION['user_nome']` | Exibição | Mostra o nome do usuário no cabeçalho. |
| `$_SESSION['user_perfil']` | Autorização | Determina se o usuário pode acessar páginas administrativas. |
| `$_SESSION['csrf_*']` | Proteção CSRF | Armazena tokens aleatórios para validar formulários legítimos. |
| `session_destroy()` | Logout | Encerra a sessão e remove os dados armazenados. |

---

## 7. Segurança — Técnicas aplicadas

| Técnica | Implementação | Finalidade |
| --- | --- | --- |
| Hash de senhas | `password_hash(PASSWORD_BCRYPT)` | Protege senhas mesmo em caso de vazamento do banco. |
| Instruções preparadas | `$conexao->prepare()` + `bindParam()` | Previne SQL injection. |
| Escape de saída | `htmlspecialchars(ENT_QUOTES, 'UTF-8')` | Previne XSS (Cross-Site Scripting). |
| Tokens CSRF | `random_bytes()` + `hash_equals()` | Previne ataques CSRF (Cross-Site Request Forgery). |
| Verificação de perfil no servidor | `exigirAdmin()` em cada página admin | Impede acesso não autorizado mesmo manipulando URLs. |
| Validação de entrada | `filter_var()`, `trim()`, `preg_match()` | Rejeita dados malformados antes de processar. |
| Erros no log | `error_log()` em vez de `echo` | Evita expor detalhes do banco ao usuário. |

---

## 8. Resumo das tecnologias

| Tecnologia | Versão / Detalhes | Papel no projeto |
| --- | --- | --- |
| **HTML5** | Gerado pelos arquivos PHP | Estrutura e conteúdo das páginas. |
| **CSS3** | `assets/style.css` | Estilo visual, layout responsivo e identidade do sistema. |
| **PHP** | 7.4+ | Lógica do servidor, autenticação, regras de negócio e geração de HTML. |
| **PostgreSQL** | Banco relacional | Persistência de dados com integridade referencial e recursos avançados. |
| **PDO** | Extensão PHP | Interface de acesso ao banco com instruções preparadas. |
| **Sessões PHP** | Nativo | Gerenciamento de estado e autenticação entre páginas. |
| **bcrypt** | Via `password_hash` | Algoritmo de hash para proteção de senhas. |
| **UUID** | Tipo nativo PostgreSQL | Códigos únicos de certificados para validação pública. |
| **JSONB** | Tipo nativo PostgreSQL | Armazenamento eficiente das respostas de prova. |
