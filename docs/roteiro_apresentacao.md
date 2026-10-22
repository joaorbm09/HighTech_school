# Roteiro de apresentação — HighTech School

Preparação para 23/10/2026. Duração sugerida: **10 a 12 minutos**, ajustável ao tempo concedido pelo professor. As falas são sugestões: ensaie com suas palavras e mostre no sistema o que estiver explicando.

Use também o [questionário de preparação](quest.md). Este roteiro descreve a versão School presente neste repositório, com 11 tabelas e foco educacional. Trechos do README ainda mencionam o escopo antigo de serviços empresariais, vagas e talentos; esses módulos não fazem parte desta demonstração.

## 1. Antes da apresentação

- Confira PHP, extensão `pdo_pgsql`, conexão com PostgreSQL e o endereço usado para acessar a aplicação. Teste no equipamento e na rede da apresentação.
- Prepare um curso de demonstração com duas aulas obrigatórias publicadas e uma prova publicada com duas questões, quatro alternativas por questão e uma correta. Configure nota mínima 70 e até três tentativas.
- Tenha uma conta administrativa e uma conta de aluno de teste. Para demonstrar o bloqueio inicial, o aluno precisa ter matrícula ativa e ao menos uma aula obrigatória pendente.
- Use dois navegadores ou um navegador comum e uma janela privativa para separar as sessões. Duas abas comuns compartilham a mesma sessão.
- Ensaie uma vez o percurso completo. Depois, prepare outro aluno de demonstração: uma matrícula que já recebeu certificado não permite refazer a prova no fluxo atual.
- Deixe abertos o sistema, o DER do README, `includes/functions.php`, `includes/auth.php` e `database/schema.sql`.
- Tenha capturas de tela de reserva. Se o ambiente falhar, explique o fluxo por elas e pelo código, deixando claro que são registros de um ensaio anterior.
- Não recrie o banco nem exclua cursos ou matrículas durante a apresentação: essas exclusões podem remover registros relacionados por cascata.

## 2. Sequência principal — 12 minutos

| Tempo | O que mostrar | Ideia que precisa ficar clara |
| --- | --- | --- |
| 0:00–1:00 | Catálogo público | Problema, público e objetivo. |
| 1:00–2:00 | Organização das pastas | Papel de PHP, PostgreSQL, HTML e CSS. |
| 2:00–5:30 | Jornada do aluno | Matrícula, aulas, prova e certificado. |
| 5:30–7:00 | Área administrativa | Quem prepara o conteúdo e controla o acesso. |
| 7:00–9:00 | DER | Matrícula como vínculo; prova diferente de tentativa. |
| 9:00–11:00 | Dois trechos de código | Autorização, correção e transação. |
| 11:00–12:00 | Fechamento | Resultado, limitações e próximas melhorias. |

### Abertura — problema e objetivo

> “A HighTech School é uma aplicação web para organizar a participação de alunos em cursos. Ela reúne matrícula, acesso às aulas, registro de progresso, avaliação e emissão de certificado. O objetivo é manter essas etapas conectadas: o aluno acompanha sua aprendizagem, e o administrador prepara os cursos e as avaliações.”

Mostre o catálogo e diferencie os públicos: visitante consulta e pode se inscrever; aluno autenticado acompanha sua matrícula; administrador gerencia os cadastros e conteúdos.

Transição: “Vou mostrar primeiro a experiência do aluno e depois explicar como o banco e o PHP sustentam esse fluxo.”

### Tecnologias e organização

> “O navegador apresenta HTML e CSS. O PHP recebe as requisições, verifica a sessão e aplica as regras de negócio. Para consultar e gravar dados no PostgreSQL, o projeto usa PDO. É uma aplicação com páginas PHP e funções compartilhadas, sem uma implementação completa de MVC.”

Mostre somente as pastas essenciais: `app/` contém telas, `login/` cuida do acesso, `includes/` reúne código reutilizado, `database/` contém conexão e schema, e `assets/` contém CSS. Não leia todos os nomes de arquivos.

### Demonstração do aluno — uma história completa

1. **Catálogo e cadastro:** mostre onde a pessoa consulta cursos e cria sua conta. Explique que inscrição pública pode criar aluno e matrícula antes da conta de acesso. Use uma conta preparada para economizar tempo.
2. **Painel:** entre como aluno, mostre o curso matriculado e o progresso. Explique que a matrícula identifica a participação daquele aluno naquele curso.
3. **Regra de acesso:** com uma aula obrigatória pendente, mostre que a prova ainda está bloqueada. Isso demonstra a regra de negócio antes de mostrar o resultado positivo.
4. **Aulas:** abra o material e registre a conclusão das aulas obrigatórias publicadas. Explique que o sistema registra a ação do aluno; não mede automaticamente se ele assistiu ou compreendeu todo o conteúdo.
5. **Prova:** responda às duas questões. Duas respostas corretas produzem nota 100; uma correta produz 50. Para mostrar o certificado rapidamente, use duas corretas.
6. **Certificado:** mostre a nota e o link de validação. Abra o link em uma sessão sem login para demonstrar a consulta pública pelo código.

Fala durante a prova:

> “O navegador envia os identificadores das respostas escolhidas. O PHP consulta o gabarito no banco, verifica as alternativas, calcula a nota e registra a tentativa. Se houver aprovação, emite o certificado na mesma transação.”

Se houver tempo extra, use outro aluno para demonstrar reprovação e nova tentativa. Não gaste o tempo principal preenchendo muitos formulários.

### Demonstração administrativa

Na sessão administrativa, abra a gestão de aulas e de provas do mesmo curso.

> “O administrador define a ordem e a publicação das aulas. Na prova, configura a nota mínima e o limite de tentativas. Para publicar a avaliação, ela precisa ter pelo menos uma questão, e cada questão precisa de quatro alternativas com exatamente uma correta.”

Mostre as configurações existentes; não é necessário editar o conteúdo durante a realização de uma prova. Se perguntarem sobre proteção da área, mostre `exigirAdmin()` em `includes/auth.php` e explique que a verificação acontece no servidor.

### Banco de dados — como explicar o DER

Comece pelo centro do fluxo, sem ler os atributos de todas as tabelas:

> “Alunos e cursos têm uma relação de muitos para muitos: um aluno pode fazer vários cursos e um curso pode receber vários alunos. A tabela `matriculas` resolve essa relação e guarda a data e a situação da participação. O par aluno e curso é único.”

Depois explique aulas e progresso:

> “Uma aula pertence ao curso. A conclusão pertence à matrícula: dois alunos podem acessar a mesma aula e ter progressos diferentes.”

Finalize com a questão da prova:

> “A prova pertence ao curso porque representa a avaliação preparada pelo administrador. A tentativa pertence à matrícula porque representa uma realização individual. Ana e Bruno fazem a mesma prova, mas cada um tem suas respostas e sua nota. Por isso mantenho a configuração em `provas` e os resultados em `tentativas_prova`.”

Aponte também o certificado: no máximo um por matrícula. Se perguntarem sobre `usuarios`, explique que a conta de login e o cadastro acadêmico são distintos e atualmente se associam pelo e-mail na aplicação, sem FK entre eles.

### Código — escolha dois exemplos

**Exemplo 1: autorização.** Em [auth.php](../includes/auth.php), localize `exigirAdmin()`. Mostre que ela exige login, verifica `user_perfil` e interrompe o acesso indevido com redirecionamento e `exit`.

**Exemplo 2: avaliação consistente.** Em [functions.php](../includes/functions.php), localize `enviarTentativaProva()`. Explique esta sequência:

1. Inicia a transação e bloqueia a matrícula com `FOR UPDATE OF m`.
2. Verifica matrícula ativa, prova do mesmo curso, certificado existente e aulas pendentes.
3. Valida as respostas e o limite de tentativas.
4. Calcula `round((acertos / total_de_questoes) * 100, 2)`.
5. Grava a tentativa e, se aprovado, o certificado; confirma com `commit()`.

> “Se a operação falhar antes da confirmação, o rollback desfaz as gravações da transação. O bloqueio da matrícula coordena envios simultâneos para o mesmo vínculo.”

Como exemplos de reserva, saiba localizar `cadastrarUsuario()` para hash de senha e `concluirAulaDaMatricula()` para a checagem do curso da aula e prevenção de progresso duplicado.

### Fechamento

> “O resultado é um fluxo que conecta cadastro, matrícula, conteúdo, avaliação e certificação. O banco protege relações e valores, enquanto o PHP verifica as regras de acesso e aprendizagem. Para evoluir o projeto, eu priorizaria um vínculo por ID entre conta e aluno, preservação do histórico acadêmico e testes automatizados para os principais fluxos.”

Depois pare e abra espaço para perguntas. Não apresente o sistema como pronto para produção nem como livre de falhas.

## 3. Se o professor der apenas cinco minutos

- **1 minuto:** objetivo, tecnologias e públicos.
- **2 minutos:** painel, conclusão da última aula, prova e certificado com dados preparados.
- **1 minuto:** DER, destacando matrícula e diferença entre prova e tentativa.
- **1 minuto:** autorização no servidor, transação e uma melhoria concreta.

## 4. Pontos que você deve explicar com precisão

| Evite afirmar | Explique desta forma |
| --- | --- |
| “A senha é descriptografada no login.” | O sistema verifica a senha contra o hash com `password_verify`. |
| “Todo SQL usa `prepare`.” | Consultas com parâmetros usam preparação; também existem consultas fixas com `query`. |
| “Todas as telas têm CSRF.” | Várias ações têm tokens; o login atual não faz essa validação. |
| “Usuário e aluno têm uma FK.” | A aplicação associa os registros pelo e-mail. |
| “Concluir aula prova que assistiu ao vídeo.” | O sistema registra a marcação de conclusão. |
| “Cancelar matrícula só muda o status.” | A ação administrativa atual executa `DELETE`, com efeitos de cascata. |
| “A aprovação muda a matrícula para Concluída.” | A função de envio grava aprovação e certificado, sem atualizar o status da matrícula. |
| “O cadastro público de conta não usa transação.” | Nesta versão, conta e criação/reutilização do aluno estão dentro de uma transação. |
| “O UUID é uma assinatura digital.” | É um identificador aleatório usado para consultar o certificado no banco. |

## 5. Ensaio na véspera

1. Faça uma passagem de 12 minutos com cronômetro, navegando de verdade.
2. Responda sem consultar as perguntas prioritárias do [questionário](quest.md).
3. Abra o código para confirmar cada resposta em que hesitar.
4. Faça uma segunda passagem reduzida a cinco minutos.
5. Confira as contas, o estado do aluno de demonstração e o link do certificado de reserva.

Se não souber uma resposta, diga: “Não confirmei esse comportamento. Pelo fluxo atual eu esperaria isto, mas vou localizar a função para verificar.” Diferencie o que existe hoje de uma proposta de melhoria.
