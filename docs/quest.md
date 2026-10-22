# Questionário de preparação — HighTech School

Material de estudo para a apresentação de **23/10/2026**, baseado no código deste repositório. Não é possível prever todas as perguntas do professor; esta seleção cobre o fluxo, a implementação e as decisões mais relevantes.

O formato de perguntas e respostas foi inspirado no [questionário indicado](https://github.com/joaorbm09/HighTech/blob/fa8cca5d04e5348cf215d0403c07a25268bde88d/docs/quest.md). As respostas abaixo foram redigidas a partir da versão School atual. Não transfira automaticamente diagnósticos da outra versão: aqui, o cadastro de conta já usa transação e as operações de cursos e matrículas citadas naquele material já validam CSRF.

## Como estudar

Leia a pergunta, esconda a resposta e tente responder em 30 a 60 segundos. Depois abra o arquivo indicado e encontre a função ou consulta mencionada. O campo “Atenção” destaca uma distinção que pode gerar uma pergunta de aprofundamento.

Se o tempo estiver curto, priorize **1, 8–11, 16, 20, 25–27, 30–34, 43, 46, 48–50 e 54**. Confira os títulos: a meta é dominar fluxo, modelagem, login, transação, avaliação e limitações.

## Visão geral e arquitetura

### 1. Qual problema o projeto resolve?

**Resposta sugerida:** Organiza o percurso do aluno: matrícula, acesso às aulas, progresso, prova e certificado. A administração prepara cursos e conteúdos, enquanto o aluno acompanha sua participação.

**Onde conferir:** [app/meu_painel.php](../app/meu_painel.php).

**Atenção:** Explique o fluxo completo antes de listar telas.

### 2. Quem usa o sistema?

**Resposta sugerida:** Visitantes consultam cursos e enviam inscrições. Alunos autenticados acessam seu painel e estudam. Administradores gerenciam usuários, alunos, cursos, matrículas, aulas e provas. Os perfis gravados no banco são aluno e admin; visitante não é um terceiro perfil cadastrado.

**Onde conferir:** [includes/auth.php](../includes/auth.php).

**Atenção:** Diferencie visitante de conta cadastrada.

### 3. Esta versão também tem vagas, talentos e serviços empresariais?

**Resposta sugerida:** Não. Esta versão School concentra a parte educacional e possui 11 tabelas. O README ainda contém referências ao escopo anterior; eu usaria o schema e as telas atuais para delimitar o que está implementado.

**Onde conferir:** [database/schema.sql](../database/schema.sql).

**Atenção:** Não apresente módulos do outro repositório como se estivessem aqui.

### 4. O que cada tecnologia faz?

**Resposta sugerida:** HTML estrutura as páginas, CSS define apresentação e responsividade, PHP recebe requisições e executa regras no servidor, e PostgreSQL persiste os dados. PDO é a interface usada pelo PHP para acessar o banco.

**Onde conferir:** [database/connect.php](../database/connect.php).

**Atenção:** O navegador recebe a saída da página, não executa o PHP.

### 5. O projeto usa MVC ou orientação a objetos?

**Resposta sugerida:** A organização principal usa páginas PHP e funções compartilhadas. Não é um MVC completo nem um domínio modelado com classes. Usar objetos de PDO não transforma toda a arquitetura em orientação a objetos.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Diferencie a arquitetura própria do uso de uma classe de biblioteca.

### 6. Por que separar as pastas e usar require_once?

**Resposta sugerida:** As pastas agrupam responsabilidades e os includes reaproveitam código. require_once carrega uma dependência uma única vez na requisição, evitando redefinir funções. Ainda há SQL nas páginas administrativas, então a separação não é completa.

**Onde conferir:** [app/cursos.php](../app/cursos.php).

**Atenção:** Não diga que todas as consultas estão centralizadas em functions.php.

### 7. É apenas um CRUD?

**Resposta sugerida:** Há operações de criar, consultar, editar e excluir, mas também regras que combinam várias entidades. Fazer uma prova depende da matrícula, das aulas, da publicação e do limite de tentativas; a aprovação gera certificado.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Use a prova como exemplo de uma operação além de cadastro simples.

## Modelagem e integridade do banco

### 8. Por que existe a tabela matriculas?

**Resposta sugerida:** Ela representa a relação muitos para muitos entre alunos e cursos e guarda data e status. Um aluno pode participar de vários cursos; cada curso pode receber vários alunos. UNIQUE(id_aluno, id_curso) impede repetir esse par.

**Onde conferir:** [database/schema.sql](../database/schema.sql).

**Atenção:** Um único campo id_curso em alunos limitaria o aluno a um curso.

### 9. Por que provas está ligada diretamente a cursos?

**Resposta sugerida:** Porque provas representa a avaliação preparada para aquele curso, com critérios e questões compartilhados. O resultado de cada aluno fica em tentativas_prova, ligado à matrícula. Ana e Bruno podem responder à mesma prova e obter notas diferentes.

**Onde conferir:** [database/schema.sql](../database/schema.sql).

**Atenção:** Prova é a avaliação; tentativa é uma realização individual.

### 10. Por que a tentativa aponta para matrícula e não apenas para usuário?

**Resposta sugerida:** A matrícula identifica o aluno e o curso em que ele participa. Isso permite conferir o vínculo acadêmico e sua situação. Usuário representa a conta de acesso, inclusive contas administrativas, e não substitui a matrícula.

**Onde conferir:** [app/prova.php](../app/prova.php).

**Atenção:** A página localiza o aluno da sessão e sua matrícula antes de enviar a tentativa.

### 11. Um curso pode ter várias provas?

**Resposta sugerida:** No modelo atual, não: id_curso é UNIQUE em provas, permitindo zero ou uma prova por curso. Para ter avaliações por módulo seria necessário mudar a restrição, as telas e as regras de aprovação e certificação.

**Onde conferir:** [database/schema.sql](../database/schema.sql).

**Atenção:** Não basta retirar UNIQUE sem revisar as consultas que esperam uma prova.

### 12. Por que separar usuarios e alunos?

**Resposta sugerida:** usuarios contém credenciais e perfil; alunos contém o cadastro acadêmico. Pode existir inscrição acadêmica antes da conta. Atualmente a associação ocorre pelo e-mail na aplicação, sem FK; um vínculo por ID seria mais explícito e menos dependente de um dado alterável.

**Onde conferir:** [login/cadastrar.php](../login/cadastrar.php).

**Atenção:** Um novo campo de vínculo exigiria migração e definição de como tratar alunos sem conta.

### 13. Quais relações do DER você consegue explicar?

**Resposta sugerida:** Curso tem muitas aulas; prova tem muitas questões; questão tem alternativas. Matrícula tem progressos e tentativas e no máximo um certificado. Cada progresso identifica uma matrícula e uma aula. As FKs apontam para registros existentes.

**Onde conferir:** [database/schema.sql](../database/schema.sql).

**Atenção:** Leia zero ou muitos e zero ou um; nem todo curso já tem conteúdo cadastrado.

### 14. O que são PK, FK, UNIQUE, NOT NULL e CHECK?

**Resposta sugerida:** PK identifica o registro. FK exige uma referência válida. UNIQUE impede valores ou combinações repetidas. NOT NULL exige preenchimento. CHECK limita valores, como nota entre 0 e 100 e tentativas configuradas entre 1 e 20.

**Onde conferir:** [database/schema.sql](../database/schema.sql).

**Atenção:** A restrição de nota mínima da prova é de 1 a 100; nota obtida pode ser zero.

### 15. Como o sistema impede concluir a mesma aula duas vezes?

**Resposta sugerida:** O banco tem UNIQUE(id_matricula, id_aula). A inserção usa ON CONFLICT DO NOTHING para que repetir a ação não crie outro progresso. A função também confere se a aula pertence ao curso da matrícula ativa.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Localize concluirAulaDaMatricula; uma FK sozinha não faz a checagem de curso.

### 16. O que acontece ao excluir um curso ou cancelar uma matrícula?

**Resposta sugerida:** As FKs usam ON DELETE CASCADE. Excluir o curso remove registros dependentes. A ação administrativa chamada cancelar matrícula atualmente executa DELETE, podendo apagar progresso, tentativas e certificado. Para preservar histórico, eu priorizaria cancelamento por status e revisão das exclusões.

**Onde conferir:** [app/matriculas.php](../app/matriculas.php).

**Atenção:** Não demonstre exclusão usando os dados que pretende apresentar depois.

### 17. Todas as regras estão garantidas pelo banco?

**Resposta sugerida:** Não. O banco garante referências, unicidade e faixas declaradas. A aplicação verifica, por exemplo, se prova e matrícula pertencem ao mesmo curso e se uma questão tem quatro alternativas e uma correta antes da publicação. Alterações diretas por SQL podem contornar regras que existem só no PHP.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** A existência de duas FKs não garante que elas apontem para o mesmo curso.

### 18. Por que usar JSONB nas respostas?

**Resposta sugerida:** Ele permite guardar o mapa de questão para alternativa escolhida dentro da tentativa. É prático para recuperar as respostas, mas esses IDs dentro do JSON não têm FKs próprias. Uma tabela de respostas por tentativa facilitaria certas consultas e integridade relacional.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Não afirme normalização perfeita nem integridade automática dentro do JSON.

### 19. Como funciona a unicidade de e-mail?

**Resposta sugerida:** usuarios tem UNIQUE(email); alunos tem índice único em LOWER(email). As buscas por e-mail usam comparação sem diferenciar maiúsculas, mas as restrições não são idênticas: o índice de alunos garante essa regra no banco, enquanto o UNIQUE simples de usuarios não equivale a esse índice.

**Onde conferir:** [database/schema.sql](../database/schema.sql).

**Atenção:** Normalização na aplicação e restrição no banco são coisas diferentes.

## PHP, SQL e transações

### 20. Por que PDO e consultas preparadas?

**Resposta sugerida:** PDO permite conexão, execução de consultas e transações. Com prepare e parâmetros, os valores são enviados separados da estrutura SQL, evitando tratar uma entrada como parte do comando. Isso é importante nas consultas que recebem dados externos.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Parâmetros são para valores; não substituem automaticamente nomes de tabela ou coluna.

### 21. Por que também existe query no código?

**Resposta sugerida:** Há consultas fixas, como listar cursos ordenados por ID, que não interpolam valores externos. Nesses casos query executa diretamente. A existência de query não demonstra, sozinha, uma injeção de SQL.

**Onde conferir:** [app/cursos.php](../app/cursos.php).

**Atenção:** Procure a origem dos valores antes de avaliar o risco de uma consulta.

### 22. Qual a diferença entre fetch, fetchAll e fetchColumn?

**Resposta sugerida:** fetch retorna a próxima linha; fetchAll retorna as linhas restantes; fetchColumn retorna uma coluna da próxima linha, útil para uma contagem ou um teste de existência. A conexão usa FETCH_ASSOC como formato padrão para linhas.

**Onde conferir:** [database/connect.php](../database/connect.php).

**Atenção:** Uma consulta de listagem e uma consulta de contagem pedem tratamentos diferentes.

### 23. Qual a diferença entre bindValue e bindParam?

**Resposta sugerida:** bindValue associa um valor ao parâmetro. bindParam associa uma variável por referência, cujo valor é usado na execução. PARAM_INT informa o tipo de associação, mas não substitui validar se o identificador é aceitável para a operação.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** O projeto usa as duas formas; saiba explicar sem decorar a assinatura.

### 24. Por que usar JOIN e LEFT JOIN?

**Resposta sugerida:** JOIN combina registros que correspondem à condição. LEFT JOIN preserva a linha da esquerda mesmo sem correspondência à direita. Ao procurar aulas pendentes, o sistema relaciona aulas com progresso e seleciona aquelas sem registro de conclusão.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Localize contarAulasObrigatoriasPendentes e a condição pa.id IS NULL.

### 25. O que uma transação resolve?

**Resposta sugerida:** Agrupa etapas em uma operação que pode ser confirmada com commit ou desfeita com rollback. No envio da prova, tentativa e certificado ficam na mesma transação; se a emissão falhar, a gravação da tentativa também é desfeita nesse fluxo.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Transação não substitui validação nem garante sucesso da operação.

### 26. O cadastro de conta e aluno é atômico?

**Resposta sugerida:** O cadastro em login/cadastrar.php usa uma transação para criar a conta e criar ou reutilizar o aluno. Isso é diferente da inscrição pública em aplicacao.php, onde cadastrar o aluno e matriculá-lo não está envolvido por uma única transação externa.

**Onde conferir:** [login/cadastrar.php](../login/cadastrar.php).

**Atenção:** Não repita a conclusão do questionário antigo de que o cadastro de conta não tem transação.

### 27. E se houver dois envios simultâneos da prova?

**Resposta sugerida:** enviarTentativaProva bloqueia a linha da matrícula com FOR UPDATE OF m dentro da transação. As requisições para a mesma matrícula esperam a liberação e verificam novamente certificado e contagem de tentativas. Matrículas diferentes não dependem desse mesmo bloqueio de linha.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Esse bloqueio não significa bloquear todo o banco nem impedir edição concorrente da prova.

### 28. Como a matrícula evita duplicidade?

**Resposta sugerida:** O fluxo de matrícula rápida e pública usa transação e bloqueia o curso durante a verificação. A restrição UNIQUE do par aluno e curso também impede duplicidade. A tela administrativa insere diretamente com parâmetros e trata erro de unicidade; os caminhos não são idênticos.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Localize matricularAlunoEmCursoAtivo e compare com app/matriculas.php.

### 29. Quando usar GET e POST? POST protege os dados sozinho?

**Resposta sugerida:** GET serve para consulta, como selecionar um curso por URL. POST é usado para enviar ações e formulários. POST não oferece criptografia por si só e não substitui autorização, validação ou CSRF; o transporte precisa de HTTPS em uma implantação real.

**Onde conferir:** [app/prova.php](../app/prova.php).

**Atenção:** Um ID vindo da URL ou do formulário continua sendo entrada não confiável.

## Login, autorização e segurança

### 30. Como funciona o login?

**Resposta sugerida:** O PHP recebe e-mail e senha, consulta a conta e usa password_verify para conferir o hash. Se aceitar, grava ID, nome, e-mail e perfil na sessão e redireciona conforme o perfil. As páginas seguintes usam a sessão para reconhecer a conta.

**Onde conferir:** [login/login.php](../login/login.php).

**Atenção:** Os dados de sessão ficam no servidor; o navegador normalmente mantém o identificador em um cookie.

### 31. Como a senha é armazenada? É possível descriptografá-la?

**Resposta sugerida:** cadastrarUsuario usa password_hash com PASSWORD_BCRYPT. O banco guarda o hash, e password_verify verifica uma senha candidata. Não há uma rotina de descriptografar a senha; hash não é criptografia reversível. Senhas fracas ainda podem ser adivinhadas.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Não chame hash de senha de texto criptografado que será recuperado.

### 32. Qual é a diferença entre autenticação e autorização?

**Resposta sugerida:** Autenticação verifica a identidade. Autorização decide quais ações essa identidade pode executar. exigirLogin confere a sessão, e exigirAdmin acrescenta a verificação de perfil. Esconder um menu não impede acesso direto à URL.

**Onde conferir:** [includes/auth.php](../includes/auth.php).

**Atenção:** Mostre a chamada da função no início de uma página administrativa.

### 33. Um aluno consegue se cadastrar como administrador?

**Resposta sugerida:** O cadastro público chama cadastrarUsuario com o perfil aluno fixado no servidor. A gestão de contas administrativas exige autorização. Mudar um campo no navegador não muda esse argumento fixo do cadastro público.

**Onde conferir:** [login/cadastrar.php](../login/cadastrar.php).

**Atenção:** A proteção deve estar no processamento, não só nas opções do formulário.

### 34. O que é CSRF e como os tokens ajudam?

**Resposta sugerida:** É a indução de uma requisição indesejada usando o contexto do navegador da vítima. Nas ações protegidas, o sistema guarda um token aleatório na sessão, coloca-o no formulário e compara o valor recebido com hash_equals. O token não substitui verificar o usuário e a permissão.

**Onde conferir:** [app/meu_painel.php](../app/meu_painel.php).

**Atenção:** hash_equals faz uma comparação apropriada para segredos, evitando comparação com tempo dependente do conteúdo.

### 35. Todo formulário tem proteção CSRF?

**Resposta sugerida:** Não afirmaria isso. Cadastro, inscrição, várias operações administrativas, progresso e prova têm validações. O login atual não valida um token CSRF. As operações de criar e editar cursos e criar matrículas já possuem validação nesta versão, diferente do relato antigo.

**Onde conferir:** [app/cursos.php](../app/cursos.php).

**Atenção:** Confira também login/login.php e app/matriculas.php.

### 36. O que é XSS? htmlspecialchars resolve tudo?

**Resposta sugerida:** XSS envolve conteúdo não confiável sendo interpretado como código no navegador. htmlspecialchars converte caracteres especiais ao exibir texto em HTML. É uma proteção adequada ao contexto em que é aplicada; não é um sanitizador universal de URLs, JavaScript ou todo conteúdo possível.

**Onde conferir:** [app/prova.php](../app/prova.php).

**Atenção:** Não confunda escape de HTML com proteção de consultas SQL.

### 37. Por que validar no PHP se já há required no HTML?

**Resposta sugerida:** O navegador pode ser contornado por uma requisição construída manualmente. O PHP precisa validar tipos, campos e regras de acesso. O banco acrescenta restrições estruturais, como FK, UNIQUE e CHECK, para dados inconsistentes não serem persistidos.

**Onde conferir:** [app/prova.php](../app/prova.php).

**Atenção:** Explique as três camadas: interface, servidor e banco.

### 38. Se alguém trocar id_curso na URL, verá os dados de outro aluno?

**Resposta sugerida:** O ID informado não é suficiente. A página de prova busca o aluno associado à conta da sessão e sua matrícula ativa naquele curso. A mesma lógica de pertencimento deve ser mantida em cada rota protegida; autenticar não autoriza acessar qualquer registro.

**Onde conferir:** [app/prova.php](../app/prova.php).

**Atenção:** CSRF não resolve acesso indevido a registros; a checagem de proprietário é outra regra.

### 39. O que melhoraria na sessão e no acesso?

**Resposta sugerida:** O login atual não regenera explicitamente o ID da sessão após autenticar. Eu incluiria essa regeneração, revisaria os atributos do cookie e a invalidação de sessões quando o perfil mudar. Também acrescentaria limitação de tentativas de login e verificação de e-mail.

**Onde conferir:** [login/login.php](../login/login.php).

**Atenção:** Apresente como proposta; não diga que essas medidas já estão implementadas.

### 40. Quais cuidados existem com credenciais e administrador padrão?

**Resposta sugerida:** connect.php lê variáveis de ambiente, mas possui credenciais padrão de demonstração. Também cria a conta administrativa configurada se ela não existir. Apenas apagar o administrador padrão pode fazer a aplicação recriá-lo; eu substituiria esse comportamento por uma configuração inicial controlada.

**Onde conferir:** [database/connect.php](../database/connect.php).

**Atenção:** Não exiba senhas reais na apresentação. O uso de getenv não elimina os valores padrão existentes.

## Aprendizagem, avaliação e certificado

### 41. Como o aluno acessa as aulas?

**Resposta sugerida:** A página identifica o aluno da sessão e verifica sua matrícula ativa no curso. A listagem apresenta aulas publicadas e relaciona cada uma ao progresso daquela matrícula. Cadastrar uma conta, sozinho, não equivale a estar matriculado.

**Onde conferir:** [app/aulas.php](../app/aulas.php).

**Atenção:** Diferencie conta, aluno e matrícula.

### 42. O sistema comprova que o aluno assistiu ao material?

**Resposta sugerida:** Não. Ele registra a ação de marcar a aula como concluída. A prova avalia respostas, mas não há monitoramento automático que demonstre tempo de estudo ou compreensão de todo o material.

**Onde conferir:** [app/aulas.php](../app/aulas.php).

**Atenção:** Não prometa controle de vídeo ou fiscalização que não existe.

### 43. Quando a prova fica liberada?

**Resposta sugerida:** É necessário estar autenticado, ter cadastro de aluno e matrícula ativa, e existir prova publicada. Todas as aulas obrigatórias publicadas precisam estar concluídas. No envio, ainda são verificados certificado existente, respostas e limite de tentativas.

**Onde conferir:** [app/prova.php](../app/prova.php).

**Atenção:** A checagem de aulas pendentes considera ativa=true e obrigatoria=true.

### 44. E se não houver aulas obrigatórias publicadas?

**Resposta sugerida:** A contagem de pendentes será zero. O fluxo atual não exige uma quantidade mínima de aulas obrigatórias para fazer a prova, desde que as demais condições sejam atendidas. Se isso não for desejado, é preciso acrescentar uma regra explícita.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Zero pendências não significa que o curso necessariamente possui aulas.

### 45. Quais regras existem para publicar uma prova?

**Resposta sugerida:** Ela deve conter ao menos uma questão; cada questão precisa ter quatro alternativas e exatamente uma marcada como correta. Nota mínima fica entre 1 e 100 e limite de tentativas entre 1 e 20. Nem todas essas regras são CHECKs do banco: parte é validada no PHP.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Localize definirProvaAtiva.

### 46. Como a nota é calculada e quem conhece o gabarito?

**Resposta sugerida:** O PHP compara os IDs escolhidos com as alternativas corretas e calcula acertos divididos pelo total de questões, vezes 100, com duas casas decimais. As questões têm o mesmo peso. A consulta de questões para o aluno não seleciona o campo correta; o administrador pode ver o gabarito.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Com três acertos em quatro questões, a nota é 75.

### 47. O aluno pode enviar uma alternativa de outra questão?

**Resposta sugerida:** A função confere se cada alternativa escolhida está na lista de alternativas da respectiva questão, além de validar IDs e a quantidade de respostas. A validação é feita no servidor, mesmo que o formulário seja adulterado.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Localize o teste in_array na função enviarTentativaProva.

### 48. Como o limite de tentativas funciona?

**Resposta sugerida:** O servidor conta tentativas daquela matrícula e daquela prova dentro da transação. Quando a contagem alcança max_tentativas, rejeita uma nova realização. Se já existe certificado para a matrícula, o fluxo retorna already_passed em vez de registrar outra tentativa.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Não confunda limite de tentativas da prova com limitação de tentativas de login.

### 49. O que acontece quando o aluno é aprovado?

**Resposta sugerida:** O servidor grava a tentativa aprovada e emite um certificado na mesma transação. O código usa UUID aleatório e o banco exige código único e uma emissão por matrícula. Essa função não altera o status da matrícula para Concluída.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** A fonte de aprovação é o resultado e o certificado, não uma atualização automática de status.

### 50. Como o certificado é validado? Ele tem assinatura digital?

**Resposta sugerida:** A página pública valida o formato do UUID e consulta o registro no banco, exibindo os dados associados. Isso permite conferir a existência do certificado no sistema. Não é uma assinatura digital nem um documento com reconhecimento externo garantido.

**Onde conferir:** [app/certificado.php](../app/certificado.php).

**Atenção:** Quem possui o código pode acessar a consulta pública; o código não é uma conta autenticada.

### 51. O histórico continua confiável se o administrador editar questões?

**Resposta sugerida:** As tentativas guardam nota e IDs das respostas em JSONB, mas não uma cópia completa do enunciado e das alternativas. Alterações ou exclusões posteriores podem prejudicar a reconstrução da avaliação original. Eu criaria versões de provas ou snapshots associados à tentativa.

**Onde conferir:** [database/schema.sql](../database/schema.sql).

**Atenção:** Guardar nota histórica não equivale a preservar toda a avaliação original.

## Demonstração, testes e evolução

### 52. Como executar e diagnosticar uma falha de conexão?

**Resposta sugerida:** É preciso PHP com PDO para PostgreSQL, banco acessível e schema importado. Conferiria host, porta, nome do banco, usuário, senha e extensões. connect.php registra o erro no log e define a conexão como null; não basta a página abrir para concluir que o banco funciona.

**Onde conferir:** [database/connect.php](../database/connect.php).

**Atenção:** O servidor PHP embutido serve para demonstração, não define uma implantação de produção.

### 53. Quais testes você faria?

**Resposta sugerida:** Testaria o caminho completo e os bloqueios: aluno sem matrícula, acesso administrativo negado, aula de outro curso, prova antes de concluir aulas, alternativa inválida, limite atingido, certificado único e rollback em falha. Também testaria envios concorrentes da mesma matrícula.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Não foi identificada uma suíte automatizada neste repositório. Diga quais testes você realmente executou, sem tratar a lista proposta como resultado obtido.

### 54. Qual melhoria você priorizaria e por quê?

**Resposta sugerida:** Eu priorizaria preservar o histórico ao cancelar matrículas e revisar cascatas, pois hoje a exclusão pode remover resultados e certificados. Depois, criaria vínculo por ID entre usuário e aluno, versões de provas e testes automatizados. Para publicação real, também revisaria sessão e credenciais padrão.

**Onde conferir:** [database/schema.sql](../database/schema.sql).

**Atenção:** Escolha uma melhoria e explique seu efeito, em vez de prometer várias sem justificativa.

### 55. O sistema suportaria uma escola muito maior?

**Resposta sugerida:** O modelo relacional é uma base, mas eu precisaria medir carga e consultas. Listagens sem paginação, consultas de progresso e bloqueios merecem análise. Não afirmaria uma capacidade sem testes; avaliaria paginação, índices conforme consultas e plano de execução.

**Onde conferir:** [includes/functions.php](../includes/functions.php).

**Atenção:** Escalabilidade precisa de medição; uma tecnologia isolada não garante capacidade.

### 56. Qual foi sua contribuição e o que você aprendeu?

**Resposta sugerida:** Responda com os fatos da sua participação: uma funcionalidade que desenvolveu, uma decisão que tomou e um problema que conseguiu investigar. Explique como verificou o resultado. Se usou ferramentas de apoio, descreva isso com honestidade e demonstre entendimento do código.

**Onde conferir:** [docs/roteiro_apresentacao.md](roteiro_apresentacao.md).

**Atenção:** Esta resposta é pessoal. Não atribua a si tarefas ou testes que não realizou.

## Exercícios práticos que o professor pode pedir

### A. Mostrar aluno e curso com JOIN

Esta consulta é somente de leitura:

```sql
SELECT a.nome AS aluno, c.nome AS curso, m.status
FROM matriculas m
JOIN alunos a ON a.id = m.id_aluno
JOIN cursos c ON c.id = m.id_curso
ORDER BY a.nome, c.nome;
```

Explique como a matrícula fornece as duas chaves estrangeiras. Não faça alterações diretas no banco apenas para demonstrar SQL.

### B. Explicar uma consulta parametrizada

Em `obterProvaDoCurso()`, encontre `WHERE id_curso = :id_curso`. Explique a separação entre a estrutura SQL, o valor associado e a execução. Depois mostre onde o sistema verifica se o aluno tem acesso ao curso; a consulta preparada não resolve essa autorização sozinha.

### C. Traçar o caminho de uma requisição

Use `app/prova.php`: entrada GET/POST → conta da sessão → aluno → matrícula → validações → `enviarTentativaProva()` → transação → resposta e redirecionamento. Aponte onde cada responsabilidade está implementada.

### D. Calcular a nota e contar tentativas

Uma prova tem quatro questões, nota mínima 70 e limite de três tentativas. Com dois acertos, a nota é 50 e não há aprovação. Com três acertos, é 75 e há aprovação. Depois de duas tentativas reprovadas, resta uma. Após aprovação e emissão de certificado, o fluxo não permite repetir a avaliação daquela matrícula.

### E. Explicar um erro sem inventar a causa

Se o painel não carregar dados, separe hipóteses: falha de conexão, conta sem aluno correspondente, matrícula ausente ou conteúdo não publicado. Confira mensagens e logs antes de atribuir a causa. Um erro de ambiente não demonstra automaticamente erro na modelagem.

## Simulado oral de dez minutos

1. Em um minuto, explique o sistema sem citar nomes de funções.
2. Em dois minutos, explique matrícula, prova e tentativa no DER.
3. Em dois minutos, mostre login, hash e autorização no código.
4. Em dois minutos, explique como a prova é corrigida e gravada.
5. Em um minuto, explique o efeito de uma exclusão em cascata.
6. Em dois minutos, responda uma limitação atual e como você a resolveria.

Marque cada tema como **consigo explicar**, **preciso consultar** ou **ainda não entendi**. Volte primeiro aos dois últimos grupos. Saber localizar e explicar o comportamento vale mais que decorar a frase sugerida.
