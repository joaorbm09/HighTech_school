# Roteiro de apresentação — HighTech School

## Abertura

"A HighTech School é um sistema de gestão escolar desenvolvido em PHP e PostgreSQL. Ele nasceu como uma versão focada exclusivamente na parte educacional do projeto HighTech, sem os módulos de portal empresarial, vagas ou talentos. O objetivo é administrar cursos, alunos, matrículas, aulas, provas e certificados em uma plataforma web completa."

## 1. O que o sistema oferece

- **Catálogo público de cursos:** visitantes podem consultar os cursos disponíveis e enviar uma inscrição.
- **Cadastro e autenticação:** alunos criam uma conta, fazem login e acessam um painel pessoal.
- **Gestão administrativa:** administradores gerenciam usuários, alunos, cursos, matrículas, aulas e provas.
- **Fluxo educacional completo:** o aluno se matricula, estuda as aulas publicadas, registra seu progresso, faz a prova e, se aprovado, recebe um certificado verificável.
- **Controle de acesso por perfil:** visitantes, alunos e administradores veem áreas diferentes; páginas administrativas exigem perfil `admin`.

## 2. Como o sistema está organizado

- O [index.php](../index.php) redireciona para [aplicacao.php](../aplicacao.php), a página pública de cursos e inscrição.
- A pasta [app/](../app/) contém as telas de gestão e de aprendizagem:
  - [usuarios.php](../app/usuarios.php) — gestão de contas de acesso.
  - [alunos.php](../app/alunos.php) — cadastro e administração de alunos.
  - [cursos.php](../app/cursos.php) — cadastro e administração de cursos.
  - [matriculas.php](../app/matriculas.php) — criação e cancelamento de matrículas.
  - [gerenciar_aulas.php](../app/gerenciar_aulas.php) — cadastro, edição, ordenação e publicação de aulas.
  - [gerenciar_provas.php](../app/gerenciar_provas.php) — configuração de provas e questões de múltipla escolha.
  - [meu_painel.php](../app/meu_painel.php) — painel pessoal do aluno com cursos, progresso e matrícula rápida.
  - [aulas.php](../app/aulas.php) — visualização das aulas e registro de conclusão.
  - [prova.php](../app/prova.php) — realização da prova pelo aluno.
  - [certificado.php](../app/certificado.php) — consulta e validação pública de certificados.
- A pasta [login/](../login/) reúne [cadastrar.php](../login/cadastrar.php), [login.php](../login/login.php) e [logout.php](../login/logout.php).
- [includes/functions.php](../includes/functions.php) centraliza consultas e regras de negócio compartilhadas.
- [includes/auth.php](../includes/auth.php) controla sessões e verificações de perfil.
- [includes/header.php](../includes/header.php) e [includes/footer.php](../includes/footer.php) compartilham o cabeçalho e o rodapé.
- [database/connect.php](../database/connect.php) cria a conexão PDO com o PostgreSQL.
- [database/schema.sql](../database/schema.sql) define todas as tabelas e insere cursos iniciais.
- [assets/style.css](../assets/style.css) contém o estilo visual compartilhado.

## 3. Fluxo de aprendizagem

1. O visitante consulta os cursos e pode enviar uma inscrição pelo formulário público.
2. O aluno cria uma conta em **Cadastrar** e faz login.
3. No **Meu painel**, o aluno vê seus cursos, solicita matrícula em novos cursos e acompanha o progresso.
4. Uma matrícula ativa dá acesso às aulas publicadas pelo administrador.
5. O aluno acessa cada aula, abre o material e marca como concluída.
6. Após concluir todas as aulas obrigatórias, a prova é liberada.
7. O servidor corrige as respostas, calcula a nota e registra a tentativa.
8. Se a nota atingir ou superar a nota mínima, um certificado com código UUID é emitido automaticamente.
9. O certificado pode ser consultado publicamente pelo código.

Administradores podem cadastrar aulas com links, configurar a nota mínima e o limite de tentativas, cadastrar questões e acompanhar as matrículas.

## 4. Principais recursos técnicos

- **PHP com PDO e PostgreSQL:** todas as operações de banco são feitas com instruções preparadas via PDO, separando os valores do SQL.
- **Autenticação segura:** senhas são armazenadas como hashes (`password_hash` com `PASSWORD_BCRYPT`) e verificadas com `password_verify`.
- **Sessões e autorização:** sessões PHP identificam o usuário; `exigirLogin()` e `exigirAdmin()` protegem páginas no servidor.
- **Tokens CSRF:** formulários de ação (cadastro, matrícula, conclusão de aula, prova, gestão) usam tokens gerados com `random_bytes` e validados com `hash_equals`.
- **Proteção contra XSS:** dados dinâmicos exibidos em HTML são escapados com `htmlspecialchars`.
- **Integridade relacional:** chaves estrangeiras com `ON DELETE CASCADE`, restrições `UNIQUE`, `CHECK` e índices protegem a consistência dos dados.
- **Transações:** operações que envolvem múltiplas tabelas, como matrícula e emissão de certificado, usam `beginTransaction`, `commit` e `rollBack`.
- **Correção no servidor:** a prova não envia o gabarito ao navegador; a correção e o cálculo da nota acontecem inteiramente no PHP.
- **Certificação verificável:** cada certificado tem um código UUID único que pode ser validado publicamente sem login.
- **Layout responsivo:** CSS com `@media`, `grid`, `min()` e `auto-fit` adapta a interface a diferentes tamanhos de tela.
- **Interface compartilhada:** cabeçalho, navegação e rodapé são reutilizados via `include`, mantendo as páginas coerentes.

## 5. Telas do sistema

### Telas públicas (visitante)

| Tela | Arquivo | Descrição |
| --- | --- | --- |
| Catálogo de cursos | `aplicacao.php` | Lista os cursos disponíveis e exibe o formulário de inscrição. |
| Login | `login/login.php` | Formulário de autenticação por e-mail e senha. |
| Cadastro | `login/cadastrar.php` | Formulário de criação de conta de aluno. |
| Certificado | `app/certificado.php` | Consulta e validação pública de certificados pelo código UUID. |

### Telas do aluno

| Tela | Arquivo | Descrição |
| --- | --- | --- |
| Meu painel | `app/meu_painel.php` | Painel com cursos matriculados, progresso e matrícula rápida. |
| Aulas do curso | `app/aulas.php` | Lista as aulas publicadas e permite registrar conclusão. |
| Prova | `app/prova.php` | Realização da prova de múltipla escolha do curso. |

### Telas do administrador

| Tela | Arquivo | Descrição |
| --- | --- | --- |
| Gestão de usuários | `app/usuarios.php` | Cadastro, edição e exclusão de contas de acesso. |
| Gestão de alunos | `app/alunos.php` | Cadastro, edição e exclusão de registros acadêmicos. |
| Gestão de cursos | `app/cursos.php` | Cadastro, edição e exclusão de cursos. |
| Gestão de matrículas | `app/matriculas.php` | Criação e cancelamento de matrículas. |
| Gestão de aulas | `app/gerenciar_aulas.php` | Cadastro, edição, publicação e ordenação de aulas. |
| Gestão de provas | `app/gerenciar_provas.php` | Configuração de provas, questões e alternativas. |

## 6. Exemplos de código que se destacam

### Senhas armazenadas como hash

Em [cadastrarUsuario](../includes/functions.php#L5), a senha nunca é gravada em texto:

```php
$senha_hash = password_hash($senha, PASSWORD_BCRYPT);
$sql = "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (:nome, :email, :senha, :perfil)";
$stmt = $conexao->prepare($sql);
```

`password_hash` gera um hash seguro; `password_verify` compara a senha informada com o hash sem expor o valor original.

### Controle de acesso no servidor

Em [auth.php](../includes/auth.php#L35), `exigirAdmin()` não esconde apenas links — redireciona no PHP:

```php
function exigirAdmin() {
    exigirLogin();
    if (($_SESSION['user_perfil'] ?? '') !== 'admin') {
        header('Location: ../aplicacao.php?erro=acesso_negado');
        exit;
    }
}
```

### Tokens CSRF nos formulários

O painel do aluno gera um token e confere no envio:

```php
$_SESSION['csrf_matricula'] = bin2hex(random_bytes(32));
// No recebimento:
if (!hash_equals($_SESSION['csrf_matricula'], $token)) {
    $mensagem = 'A sessão expirou.';
}
```

### Matrícula com transação e bloqueio

A função `matricularAlunoEmCursoAtivo` usa `beginTransaction`, `FOR UPDATE` e `commit`/`rollBack` para garantir que o curso esteja ativo e que não exista uma matrícula duplicada antes de inserir.

### Prova corrigida no servidor

A função `enviarTentativaProva` valida a matrícula, verifica o progresso das aulas obrigatórias, consulta o gabarito no banco, calcula a nota e emite o certificado — tudo em uma transação. O navegador nunca recebe as respostas corretas.

### Conclusão de aula sem duplicação

A cláusula `ON CONFLICT (id_matricula, id_aula) DO NOTHING` evita registrar a mesma conclusão duas vezes.

## 7. Diferenças em relação ao projeto HighTech completo

| Aspecto | HighTech (completo) | HighTech School |
| --- | --- | --- |
| Portal empresarial | Sim | Não |
| Vagas e talentos | Sim | Não |
| Gestão de usuários | Não (admin via SQL) | Sim (tela dedicada) |
| Foco | Empresas + escola | Escola |
| Número de tabelas | 14 | 9 |

A HighTech School concentra as funcionalidades essenciais de aprendizagem e gestão escolar, sem os módulos corporativos e de empregabilidade.

## 8. Fechamento sugerido

"A HighTech School é uma plataforma educacional completa que vai além de um CRUD básico. O sistema controla o acesso por perfil, protege formulários com tokens CSRF, armazena senhas como hash, corrige provas no servidor e emite certificados verificáveis. O banco de dados relacional sustenta o fluxo de matrícula, progresso, avaliação e certificação, e o PHP aplica as regras de negócio que garantem a integridade de cada etapa."
