<?php 

require_once __DIR__ . '/../database/connect.php';

// Cadastra a conta com e-mail normalizado, perfil e hash da senha.
function cadastrarUsuario($conexao, $nome, $email, $senha, $perfil = 'aluno') {
    if (!$conexao) return false;
    try {
        $email_normalizado = strtolower(trim($email));
        /* Armazena somente o hash da senha, nunca a senha em texto puro. */
        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
        $sql = "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (:nome, :email, :senha, :perfil)";

        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email_normalizado);
        $stmt->bindParam(":senha", $senha_hash);
        $stmt->bindParam(":perfil", $perfil);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erro ao cadastrar usuário: " . $e->getMessage());
        return false;
    }
}

// Busca a conta pelo e-mail e verifica a senha. Retorna o usuário ou false se o login falhar.
function autenticarUsuario($conexao, $email, $senha) {
    if (!$conexao) return false;
    try {
        $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE LOWER(email) = LOWER(:email)");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $usuario = $stmt->fetch();
        /* Compara a senha informada com o hash salvo no banco. */
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erro ao autenticar usuário: " . $e->getMessage());
        return false;
    }
}

// Busca uma conta pelo e-mail, inclusive para evitar cadastros duplicados.
function buscarUsuarioPorEmail($conexao, $email) {
    if (!$conexao) return false;
    try {
        $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE LOWER(email) = LOWER(:email)");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Erro ao buscar usuário: " . $e->getMessage());
        return false;
    }
}

// Padroniza o texto do CPF antes de armazenar; esta limpeza não protege o dado por criptografia nem comprova sua validade.
// Remove pontos, traços e espaços e limita a 11 dígitos numéricos.
function normalizarCpf($cpf) {
    if ($cpf === null || $cpf === '') {
        return '';
    }

    /* Remove os caracteres que nÃ£o sÃ£o nÃºmeros; a validade Ã© verificada separadamente. */
    $cpf_limpo = preg_replace('/\D+/', '', (string) $cpf);
    /* Limita aos 11 primeiros dígitos; números excedentes são cortados. */
    return substr($cpf_limpo, 0, 11);
}

// Faz uma verificação limitada do formato do CPF; não calcula seus dígitos verificadores.
// A regra mínima aqui é: deve conter exatamente 11 números e não pode ser repetido.
function validarCpf($cpf) {
    $cpf_limpo = normalizarCpf($cpf);

    if ($cpf_limpo === '' || strlen($cpf_limpo) !== 11) {
        return false;
    }

    /* Recusa CPFs com todos os dígitos iguais, como 11111111111. */
    if (preg_match('/^(\d)\\1{10}$/', $cpf_limpo)) {
        return false;
    }

    return true;
}

// Exibe texto complementar de uma aula com URLs HTTP/HTTPS clicáveis e seguras.
function formatarConteudoAula($conteudo) {
    /* Separa o conteúdo com uma expressão regular que reconhece URLs HTTP/HTTPS; a opção
     * PREG_SPLIT_DELIM_CAPTURE também guarda os endereços encontrados para tratá-los separadamente. */
    $partes = preg_split(
        '~(https?://[^\s<>"\']+)~iu',

        (string) $conteudo,
        -1,
        /* Inclui os grupos capturados na lista de partes: os índices pares contêm texto comum e os
         * ímpares contêm as URLs que serão validadas e transformadas em links. */
        PREG_SPLIT_DELIM_CAPTURE
    );
    $html = '';

    foreach ($partes as $indice => $parte) {
        /* A divisão feita com captura alterna texto comum e URL. O resto da divisão por 2 identifica
         * os índices pares, que devem ser escapados e exibidos como texto. */
        if ($indice % 2 === 0) {
            /* Converte quebras de linha em tags br. O texto é escapado com htmlspecialchars antes,
             * para que o conteúdo da aula não injete tags HTML. */
            $html .= nl2br(htmlspecialchars($parte, ENT_QUOTES, 'UTF-8'));
            continue;
        }

        // Pontuação no fim da frase não deve virar parte do endereço clicável.
        /* Retira apenas a pontuação indicada do final da URL, para que o ponto final de uma frase não
         * seja incluído no link da aula. */
        $url = rtrim($parte, '.,;:!?)]}');
        /* Separa o trecho retirado da URL para exibi-lo depois do link. strlen fornece a posição em
         * bytes em que a pontuação começa neste mesmo texto. */
        $pontuacao = substr($parte, strlen($url));
        /* Extrai partes da URL; PHP_URL_SCHEME pede o protocolo. O código aceita apenas http/https
         * antes de montar o link da aula. */
        $esquema = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($url !== '' &&

            filter_var($url, FILTER_VALIDATE_URL) &&

            in_array($esquema, ['http', 'https'], true)) {
            $url_segura = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $html .= '<a href="' . $url_segura .
                '" target="_blank" rel="noopener noreferrer">' .
                $url_segura . '</a>';
            $html .= htmlspecialchars($pontuacao, ENT_QUOTES, 'UTF-8');
        } else {
            $html .= htmlspecialchars($parte, ENT_QUOTES, 'UTF-8');
        }
    }

    return $html;
}

// Salva os dados acadêmicos básicos de um aluno; campos opcionais vazios ficam nulos.
function cadastrarAluno($conexao, $nome, $cpf, $email, $turma, $nasc, $ativo = true) {
    if (!$conexao) return false;
    try {
        $sql = "INSERT INTO alunos (nome, cpf, email, turma, nascimento, ativo) VALUES (:nome, :cpf, :email, :turma, :nascimento, :ativo)";

        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":nome", $nome);
        $cpf_val = normalizarCpf($cpf);
        /* Converte o CPF opcional vazio em null. Uma string vazia seria um valor repetível diferente
         * de ausência; null também permite usar o tipo PDO::PARAM_NULL na próxima linha. O ternário
         * condição ? valor_se_verdadeiro : valor_se_falso escolhe um dos dois valores. Já a forma
         * abreviada valor ?: alternativa usa a alternativa para qualquer valor considerado falso. */
        $cpf_val = $cpf_val !== '' ? $cpf_val : null;

        $stmt->bindValue(":cpf", $cpf_val, $cpf_val === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":turma", $turma);
        /* Converte nascimento opcional vazio em null, que representa ausência de data no banco; caso
         * informado, retira apenas os espaços das extremidades. */
        $nasc_val = (!empty($nasc) && trim($nasc) !== '') ? trim($nasc) : null;
        $stmt->bindValue(":nascimento", $nasc_val, $nasc_val === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":ativo", ($ativo === 'true' || $ativo === true || $ativo === 1 || $ativo === '1'), PDO::PARAM_BOOL);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erro ao cadastrar aluno: " . $e->getMessage());
        return false;
    }
}

// Lista cursos ativos em que o aluno ainda não possui matrícula ativa.
function listarCursosDisponiveisParaMatricula($conexao, $id_aluno) {
    if (!$conexao) return false;
    try {
        $stmt = $conexao->prepare(

            "SELECT c.id, c.nome, c.categoria, c.carga_horaria
             FROM cursos c
             WHERE c.ativo = true
               AND NOT EXISTS (
                   SELECT 1
                   FROM matriculas m
                   WHERE m.id_curso = c.id
                     AND m.id_aluno = :id_aluno
                     AND LOWER(m.status) = 'ativa'
               )
             ORDER BY c.nome ASC"
        );

        $stmt->bindValue(":id_aluno", $id_aluno, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erro ao listar cursos disponíveis para matrícula: " . $e->getMessage());
        return false;
    }
}

// Confere se o curso aceita matrícula e cria ou reativa a matrícula em uma transação.
/* Objetivo: conferir o curso e criar ou reativar a matrícula de forma indivisível. A transação e o
 * bloqueio abaixo coordenam requisições simultâneas que seguem este mesmo caminho. */
function matricularAlunoEmCursoAtivo($conexao, $id_aluno, $id_curso) {
    if (!$conexao) return 'error';
    try {
        /* Inicia a transação para salvar todas as alterações juntas ou desfazê-las em caso de erro. */
        $conexao->beginTransaction();
        /* Bloqueia o curso durante a transaÃ§Ã£o para coordenar matrÃ­culas simultÃ¢neas. */
        $curso = $conexao->prepare("SELECT ativo FROM cursos WHERE id = :id FOR UPDATE");

        $curso->bindValue(":id", $id_curso, PDO::PARAM_INT);
        $curso->execute();

        $ativo = $curso->fetchColumn();

        if ($ativo === false ||

            !in_array(strtolower((string) $ativo), ['1', 't', 'true', 'yes'], true)) {
            /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
            $conexao->rollBack();
            return 'course_unavailable';
        }

        $existente = $conexao->prepare(
            "SELECT id, status FROM matriculas
             WHERE id_aluno = :id_aluno
               AND id_curso = :id_curso
             LIMIT 1"
        );
        $existente->bindValue(":id_aluno", $id_aluno, PDO::PARAM_INT);
        $existente->bindValue(":id_curso", $id_curso, PDO::PARAM_INT);
        $existente->execute();
        $matricula = $existente->fetch();
        if ($matricula) {
            if (strtolower($matricula['status']) === 'ativa') {
                /* Confirma as alterações e libera os bloqueios da transação. */
                $conexao->commit();
                return 'already_enrolled';
            }

            $reativar = $conexao->prepare(
                "UPDATE matriculas
                 SET status = 'Ativa', data_matricula = CURRENT_DATE
                 WHERE id = :id"
            );
            $reativar->execute(['id' => $matricula['id']]);
            /* Confirma as alterações e libera os bloqueios da transação. */
            $conexao->commit();
            return 'created';
        }

        $insert = $conexao->prepare(
            "INSERT INTO matriculas (id_aluno, id_curso, status)
             VALUES (:id_aluno, :id_curso, 'Ativa')"
        );
        $insert->bindValue(":id_aluno", $id_aluno, PDO::PARAM_INT);
        $insert->bindValue(":id_curso", $id_curso, PDO::PARAM_INT);
        $insert->execute();
        /* Confirma as alterações e libera os bloqueios da transação. */
        $conexao->commit();
        return 'created';
    } catch (PDOException $e) {
        /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
        if ($conexao->inTransaction()) $conexao->rollBack();
        error_log("Erro ao matricular aluno em curso ativo: " . $e->getMessage());
        return 'error';
    }
}

// Busca as configurações da prova de um curso, opcionalmente exigindo que esteja ativa.
function obterProvaDoCurso($conexao, $id_curso, $somente_ativa = false) {
    if (!$conexao) return null;
    try {
        $sql = "SELECT id, id_curso, nota_minima, max_tentativas, ativa
                FROM provas
                WHERE id_curso = :id_curso";
        if ($somente_ativa) $sql .= " AND ativa = true";
        $sql .= " LIMIT 1";

        $stmt = $conexao->prepare($sql);

        $stmt->bindValue(":id_curso", $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $prova = $stmt->fetch();
        /* O ternário condição ? valor_se_verdadeiro : valor_se_falso escolhe um dos dois valores. Já a
         * forma abreviada valor ?: alternativa usa a alternativa para qualquer valor considerado
         * falso. */
        return $prova ?: false;
    } catch (PDOException $e) {
        error_log("Erro ao buscar prova do curso: " . $e->getMessage());
        return null;
    }
}

// Cria a prova do curso ou atualiza sua nota mínima e limite de tentativas.
function salvarProvaDoCurso($conexao, $id_curso, $nota_minima, $max_tentativas) {
    if (!$conexao) return false;
    try {
        $stmt = $conexao->prepare(

            "INSERT INTO provas (id_curso, nota_minima, max_tentativas)
             VALUES (:id_curso, :nota_minima, :max_tentativas)
             ON CONFLICT (id_curso) DO UPDATE
             SET nota_minima = EXCLUDED.nota_minima,
                 max_tentativas = EXCLUDED.max_tentativas
             RETURNING id"
        );

        $stmt->bindValue(":id_curso", $id_curso, PDO::PARAM_INT);
        $stmt->bindValue(":nota_minima", $nota_minima);
        $stmt->bindValue(":max_tentativas", $max_tentativas, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erro ao salvar configuração da prova: " . $e->getMessage());
        return false;
    }
}

// Publica ou despublica uma prova depois de conferir suas questões e alternativas.
/* Objetivo: só publicar uma prova com pelo menos uma questão, quatro alternativas por questão e
 * exatamente uma correta. Despublicar mantém os registros existentes. */
function definirProvaAtiva($conexao, $id_prova, $ativa) {
    if (!$conexao) return 'error';
    try {
        if ($ativa) {
            $validacao = $conexao->prepare(

                "SELECT COUNT(*) AS total_questoes,
                        COUNT(*) FILTER (
                            WHERE total_alternativas <> 4
                               OR total_corretas <> 1
                        ) AS questoes_invalidas
                 FROM (
                    SELECT q.id,
                           COUNT(a.id) AS total_alternativas,
                           COUNT(a.id) FILTER (WHERE a.correta = true) AS total_corretas
                    FROM questoes_prova q
                    LEFT JOIN alternativas_prova a ON a.id_questao = q.id
                    WHERE q.id_prova = :id_prova
                    GROUP BY q.id
                 ) questoes"
            );

            $validacao->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
            $validacao->execute();
            $resultado_validacao = $validacao->fetch();

            if (!$resultado_validacao || (int) $resultado_validacao['total_questoes'] === 0 ||
                (int) $resultado_validacao['questoes_invalidas'] > 0) {
                return 'invalid_questions';
            }
        }
        $stmt = $conexao->prepare("UPDATE provas SET ativa = :ativa WHERE id = :id");
        $stmt->bindValue(":ativa", $ativa, PDO::PARAM_BOOL);
        $stmt->bindValue(":id", $id_prova, PDO::PARAM_INT);
        $stmt->execute();
        /* Lê quantas linhas a última alteração afetou. Neste teste, sucesso de execute() sozinho não
         * prova que o ID procurado realmente encontrou um registro. O ternário condição ?
         * valor_se_verdadeiro : valor_se_falso escolhe um dos dois valores. Já a forma abreviada valor
         * ?: alternativa usa a alternativa para qualquer valor considerado falso. */
        return $stmt->rowCount() > 0 ? ($ativa ? 'published' : 'unpublished') : 'unchanged';
    } catch (PDOException $e) {
        error_log("Erro ao alterar publicação da prova: " . $e->getMessage());
        return 'error';
    }
}

// Lista questões e alternativas para a tela de administração, incluindo a correta.
function listarQuestoesProvaAdmin($conexao, $id_prova) {
    if (!$conexao) return false;
    try {
        $stmt = $conexao->prepare(

            "SELECT q.id, q.enunciado, q.ordem,
                    a.id AS id_alternativa, a.texto, a.correta
             FROM questoes_prova q
             LEFT JOIN alternativas_prova a ON a.id_questao = q.id
             WHERE q.id_prova = :id_prova
             ORDER BY q.ordem, q.id, a.id"
        );

        $stmt->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
        $stmt->execute();
        $questoes = [];
        foreach ($stmt->fetchAll() as $linha) {
            $id = (int) $linha['id'];
            if (!isset($questoes[$id])) {
                /* Usa o ID da questão como chave para agrupá-la. O JOIN repete os dados do enunciado
                 * em cada alternativa; este array mantém um enunciado com uma lista de alternativas. */
                $questoes[$id] = [
                    'id' => $id,
                    'enunciado' => $linha['enunciado'],
                    'ordem' => (int) $linha['ordem'],
                    'alternativas' => []
                ];
            }
            if ($linha['id_alternativa'] !== null) {
                $questoes[$id]['alternativas'][] = [
                    'id' => (int) $linha['id_alternativa'],
                    'texto' => $linha['texto'],

                    'correta' => in_array(
                        strtolower((string) $linha['correta']),
                        ['1', 't', 'true', 'yes'],
                        true
                    )
                ];
            }
        }
        /* Remove as chaves que eram IDs e devolve uma lista numerada a partir de zero, preservando os
         * grupos de questão e alternativas montados acima. */
        return array_values($questoes);
    } catch (PDOException $e) {
        error_log("Erro ao listar questões administrativas: " . $e->getMessage());
        return false;
    }
}

// Cria ou atualiza uma questão e suas quatro alternativas dentro de uma transação.
/* Objetivo: gravar o enunciado junto das quatro alternativas. Na edição, as alternativas antigas são
 * removidas e recriadas; se uma gravação falhar, a transação pode desfazer o conjunto. */
function salvarQuestaoProva($conexao, $id_prova, $id_questao, $enunciado, $ordem, $alternativas, $indice_correto) {
    if (!$conexao) return 'error';
    try {
        /* Inicia a transação para salvar todas as alterações juntas ou desfazê-las em caso de erro. */
        $conexao->beginTransaction();

        if ($id_questao === null) {
            $stmt = $conexao->prepare(

                "INSERT INTO questoes_prova (id_prova, enunciado, ordem)
                 VALUES (:id_prova, :enunciado, :ordem)
                 RETURNING id"
            );

            $stmt->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
            $stmt->bindValue(":enunciado", $enunciado);
            $stmt->bindValue(":ordem", $ordem, PDO::PARAM_INT);
            $stmt->execute();
            /* RETURNING id, na consulta anterior, devolve o ID da questão recém-criada. fetchColumn()
             * lê a primeira coluna da próxima linha e (int) a transforma em inteiro para vincular as
             * alternativas. */
            $id_questao = (int) $stmt->fetchColumn();
            $resultado = 'created';
        } else {
            $stmt = $conexao->prepare(
                "UPDATE questoes_prova
                 SET enunciado = :enunciado, ordem = :ordem
                 WHERE id = :id_questao AND id_prova = :id_prova"
            );
            $stmt->bindValue(":enunciado", $enunciado);
            $stmt->bindValue(":ordem", $ordem, PDO::PARAM_INT);
            $stmt->bindValue(":id_questao", $id_questao, PDO::PARAM_INT);
            $stmt->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
            $stmt->execute();
            /* Lê quantas linhas a última alteração afetou. Neste teste, sucesso de execute() sozinho
             * não prova que o ID procurado realmente encontrou um registro. */
            if ($stmt->rowCount() === 0) {
                $check = $conexao->prepare(
                    "SELECT 1 FROM questoes_prova WHERE id = :id AND id_prova = :id_prova"
                );
                $check->bindValue(":id", $id_questao, PDO::PARAM_INT);
                $check->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
                $check->execute();

                if (!$check->fetchColumn()) {
                    /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
                    $conexao->rollBack();
                    return 'not_found';
                }
            }
            $delete = $conexao->prepare("DELETE FROM alternativas_prova WHERE id_questao = :id_questao");
            $delete->bindValue(":id_questao", $id_questao, PDO::PARAM_INT);
            $delete->execute();
            $resultado = 'updated';
        }

        $insert = $conexao->prepare(
            "INSERT INTO alternativas_prova (id_questao, texto, correta)
             VALUES (:id_questao, :texto, :correta)"
        );
        foreach ($alternativas as $indice => $texto) {
            $insert->bindValue(":id_questao", $id_questao, PDO::PARAM_INT);
            $insert->bindValue(":texto", $texto);

            $insert->bindValue(":correta", ((int) $indice === (int) $indice_correto), PDO::PARAM_BOOL);
            $insert->execute();
        }
        /* Confirma as alterações e libera os bloqueios da transação. */
        $conexao->commit();
        return $resultado;
    } catch (PDOException $e) {
        /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
        if ($conexao->inTransaction()) $conexao->rollBack();
        /* O código 23505 indica violação de unicidade, como e-mail ou ordem de questão duplicados. */
        if ($e->getCode() === '23505') return 'order_conflict';
        error_log("Erro ao salvar questão da prova: " . $e->getMessage());
        return 'error';
    }
}

// Exclui uma questão que pertence à prova indicada.
function excluirQuestaoProva($conexao, $id_prova, $id_questao) {
    if (!$conexao) return false;
    try {
        $stmt = $conexao->prepare(
            "DELETE FROM questoes_prova WHERE id = :id AND id_prova = :id_prova"
        );

        $stmt->bindValue(":id", $id_questao, PDO::PARAM_INT);
        $stmt->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
        $stmt->execute();
        /* Lê quantas linhas a última alteração afetou. Neste teste, sucesso de execute() sozinho não
         * prova que o ID procurado realmente encontrou um registro. */
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Erro ao excluir questão da prova: " . $e->getMessage());
        return false;
    }
}

// Lista questões para o aluno sem revelar qual alternativa está marcada como correta.
/* A consulta do aluno não seleciona a coluna correta. O gabarito fica no servidor e só é consultado
 * durante a correção; não deve ser enviado escondido no HTML. */
function listarQuestoesProvaAluno($conexao, $id_prova) {
    if (!$conexao) return false;
    try {
        $stmt = $conexao->prepare(

            "SELECT q.id, q.enunciado, q.ordem,
                    a.id AS id_alternativa, a.texto
             FROM questoes_prova q
             JOIN alternativas_prova a ON a.id_questao = q.id
             WHERE q.id_prova = :id_prova
             ORDER BY q.ordem, q.id, a.id"
        );

        $stmt->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
        $stmt->execute();
        $questoes = [];
        foreach ($stmt->fetchAll() as $linha) {
            $id = (int) $linha['id'];
            if (!isset($questoes[$id])) {
                /* Usa o ID da questão como chave para agrupá-la. O JOIN repete os dados do enunciado
                 * em cada alternativa; este array mantém um enunciado com uma lista de alternativas. */
                $questoes[$id] = [
                    'id' => $id,
                    'enunciado' => $linha['enunciado'],
                    'ordem' => (int) $linha['ordem'],
                    'alternativas' => []
                ];
            }
            $questoes[$id]['alternativas'][] = [
                'id' => (int) $linha['id_alternativa'],
                'texto' => $linha['texto']
            ];
        }
        /* Remove as chaves que eram IDs e devolve uma lista numerada a partir de zero, preservando os
         * grupos de questão e alternativas montados acima. */
        return array_values($questoes);
    } catch (PDOException $e) {
        error_log("Erro ao listar questões para o aluno: " . $e->getMessage());
        return false;
    }
}

// Valida respostas e regras da prova, calcula a nota e registra a tentativa.
/* Objetivo: conferir matrícula, publicação, aulas obrigatórias, respostas e limite de tentativas;
 * depois gravar a nota e, se houver aprovação, o certificado na mesma transação. A página chamadora é
 * responsável por obter a matrícula do aluno autenticado. Na apresentação, acompanhe nesta ordem:
 * bloqueio da matrícula → requisitos → gabarito → respostas → limite → nota → tentativa → certificado
 * → confirmação. */
function enviarTentativaProva($conexao, $id_matricula, $id_prova, $respostas) {
    if (!$conexao) return ['status' => 'error'];
    if (!is_array($respostas)) return ['status' => 'invalid_answers'];
    try {
        /* Inicia a transação para salvar todas as alterações juntas ou desfazê-las em caso de erro. */
        $conexao->beginTransaction();
        $stmt = $conexao->prepare(
            /* Bloqueia a matrÃ­cula durante a transaÃ§Ã£o para coordenar tentativas simultÃ¢neas. */
            "SELECT m.id, m.id_curso, p.nota_minima, p.max_tentativas
             FROM matriculas m
             JOIN provas p ON p.id = :id_prova AND p.id_curso = m.id_curso
             WHERE m.id = :id_matricula AND m.status = 'Ativa' AND p.ativa = true
             FOR UPDATE OF m"
        );

        $stmt->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $stmt->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
        $stmt->execute();
        $contexto = $stmt->fetch();
        if (!$contexto) {
            /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
            $conexao->rollBack();
            return ['status' => 'unavailable'];
        }

        $certificado = $conexao->prepare(
            "SELECT codigo FROM certificados WHERE id_matricula = :id_matricula"
        );
        $certificado->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $certificado->execute();

        if ($certificado->fetchColumn()) {
            /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
            $conexao->rollBack();
            return ['status' => 'already_passed'];
        }

        $pendentes = $conexao->prepare(

            "SELECT COUNT(*)
             FROM aulas a
             LEFT JOIN progresso_aulas pa
                    ON pa.id_aula = a.id AND pa.id_matricula = :id_matricula
             WHERE a.id_curso = :id_curso
               AND a.ativa = true
               AND a.obrigatoria = true
               AND pa.id IS NULL"
        );
        $pendentes->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $pendentes->bindValue(":id_curso", $contexto['id_curso'], PDO::PARAM_INT);
        $pendentes->execute();

        if ((int) $pendentes->fetchColumn() > 0) {
            /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
            $conexao->rollBack();
            return ['status' => 'lessons_pending'];
        }

        $questoes = $conexao->prepare(

            "SELECT q.id AS id_questao, a.id AS id_alternativa, a.correta
             FROM questoes_prova q
             JOIN alternativas_prova a ON a.id_questao = q.id
             WHERE q.id_prova = :id_prova
             ORDER BY q.ordem, q.id, a.id"
        );
        $questoes->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
        $questoes->execute();
        $gabarito = [];
        $corretas_por_questao = [];
        foreach ($questoes->fetchAll() as $linha) {
            $id_questao = (int) $linha['id_questao'];
            if (!isset($gabarito[$id_questao])) $gabarito[$id_questao] = [];

            if (in_array(strtolower((string) $linha['correta']), ['1', 't', 'true', 'yes'], true)) {
                /* Guarda no servidor o ID da alternativa correta desta questão. A resposta enviada
                 * pelo aluno será comparada a esse ID, e não a uma nota informada pelo navegador. */
                $gabarito[$id_questao]['correta'] = (int) $linha['id_alternativa'];
                /* Conta quantas alternativas estão marcadas como corretas para esta questão. ?? 0
                 * inicia a contagem; mais adiante a correção exige exatamente uma. */
                $corretas_por_questao[$id_questao] = ($corretas_por_questao[$id_questao] ?? 0) + 1;
            }
            /* Acrescenta o ID da alternativa à lista da questão atual; os colchetes vazios criam a
             * próxima posição. Esse vínculo permite recusar uma alternativa de outra questão. */
            $gabarito[$id_questao]['alternativas'][] = (int) $linha['id_alternativa'];
        }
        /* Recusa uma prova sem questões e formulários com quantidade diferente da esperada. Depois,
         * cada chave e alternativa ainda será conferida; só contar as respostas não bastaria. */
        if (!$gabarito || count($respostas) !== count($gabarito)) {
            /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
            $conexao->rollBack();
            return ['status' => 'invalid_answers'];
        }
        /* A lista começa vazia para receber apenas as respostas que passaram pelas validações de tipo,
         * pertencimento e integridade da questão. */
        $respostas_normalizadas = [];
        foreach ($gabarito as $id_questao => $dados) {
            /* Exige uma resposta para a chave da questão e um valor simples, recusando arrays enviados
             * no lugar de um ID. O ternário nas próximas linhas valida esse valor como inteiro
             * positivo ou produz false. */
            $resposta = isset($respostas[$id_questao]) && is_scalar($respostas[$id_questao])
                /* O ternário condição ? valor_se_verdadeiro : valor_se_falso escolhe um dos dois
                 * valores. Já a forma abreviada valor ?: alternativa usa a alternativa para qualquer
                 * valor considerado falso. */
                ? filter_var($respostas[$id_questao], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;
            /* Antes de corrigir, confere a integridade da questão: quatro alternativas, uma correta e
             * uma resposta válida pertencente à questão. A transação é desfeita se qualquer regra
             * falhar. */
            if (count($dados['alternativas']) !== 4 ||
                ($corretas_por_questao[$id_questao] ?? 0) !== 1 ||
                !isset($dados['correta']) ||

                $resposta === false ||
                !in_array($resposta, $dados['alternativas'], true)) {
                /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
                $conexao->rollBack();
                return ['status' => 'invalid_answers'];
            }
            /* Guarda somente o ID inteiro de uma alternativa que realmente pertence a esta questão. A
             * correção posterior usa esta lista validada, sem confiar diretamente no formulário. */
            $respostas_normalizadas[$id_questao] = $resposta;
        }

        $count = $conexao->prepare(
            "SELECT COUNT(*) FROM tentativas_prova
             WHERE id_matricula = :id_matricula AND id_prova = :id_prova"
        );
        $count->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $count->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
        $count->execute();

        $tentativas = (int) $count->fetchColumn();
        if ($tentativas >= (int) $contexto['max_tentativas']) {
            /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
            $conexao->rollBack();
            return ['status' => 'attempts_exhausted'];
        }

        $acertos = 0;
        $respostas_registradas = [];
        foreach ($gabarito as $id_questao => $dados) {
            $resposta = $respostas_normalizadas[$id_questao];
            if ($resposta === $dados['correta']) $acertos++;
            $respostas_registradas[$id_questao] = $resposta;
        }
        /* Calcula acertos/total de questões em porcentagem e arredonda para duas casas. Exemplo: 3
         * acertos em 4 questões resultam em 75,00; todas as questões têm o mesmo peso. */
        $nota = round(($acertos / count($gabarito)) * 100, 2);
        /* Aprova quando a nota alcança a configuração da prova. (float) converte a nota mínima lida do
         * banco para um número que aceita casas decimais. */
        $aprovada = $nota >= (float) $contexto['nota_minima'];
        /* Converte o array de respostas para texto JSON. A consulta fará CAST para JSONB; a
         * verificação seguinte recusa false se a conversão falhar. */
        $respostas_json = json_encode($respostas_registradas);
        if ($respostas_json === false) {
            /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
            $conexao->rollBack();
            return ['status' => 'error'];
        }

        $insert = $conexao->prepare(

            "INSERT INTO tentativas_prova
                (id_matricula, id_prova, nota, aprovada, respostas)
             VALUES (:id_matricula, :id_prova, :nota, :aprovada, CAST(:respostas AS JSONB))
             RETURNING id"
        );
        $insert->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $insert->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
        $insert->bindValue(":nota", $nota);
        $insert->bindValue(":aprovada", $aprovada, PDO::PARAM_BOOL);
        $insert->bindValue(":respostas", $respostas_json);
        $insert->execute();

        $codigo = null;
        if ($aprovada) {
            /* Gera um UUID v4 para identificar o certificado. Sua validade ainda depende da consulta
             * ao banco. */
            $codigo = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                /* Escolhe um inteiro aleatório dentro dos limites inclusivos. 0x indica hexadecimal;
                 * as máscaras com | fixam bits da versão/variante do UUID sem substituir os demais
                 * bits aleatórios. */
                random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0, 0xffff),
                /* Para este grupo, limita os bits aleatórios aos três últimos dígitos hexadecimais e
                 * usa o OU bit a bit | para fixar o primeiro dígito em 4: a versão do UUID. */
                random_int(0, 0x0fff) | 0x4000,
                /* Para este grupo, | fixa os bits superiores da variante do UUID. O primeiro dígito
                 * hexadecimal resultante será 8, 9, a ou b, mantendo os bits restantes aleatórios. */
                random_int(0, 0x3fff) | 0x8000,
                random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0, 0xffff)
            );
            $emitir = $conexao->prepare(
                "INSERT INTO certificados (id_matricula, id_prova, codigo)
                 VALUES (:id_matricula, :id_prova, :codigo)"
            );
            $emitir->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
            $emitir->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
            $emitir->bindValue(":codigo", $codigo);
            $emitir->execute();
        }
        /* Confirma as alterações e libera os bloqueios da transação. */
        $conexao->commit();
        return [
            /* O ternário condição ? valor_se_sim : valor_se_não devolve passed se aprovado e failed
             * caso contrário. A página traduz esse resultado em uma mensagem ao aluno. */
            'status' => $aprovada ? 'passed' : 'failed',
            'nota' => $nota,
            'nota_minima' => (float) $contexto['nota_minima'],
            /* Calcula o limite menos as tentativas anteriores e menos esta tentativa recém-gravada.
             * max(0, ...) impede mostrar um número negativo. */
            'tentativas_restantes' => max(
                0,
                (int) $contexto['max_tentativas'] - $tentativas - 1
            ),
            'codigo_certificado' => $codigo
        ];
    } catch (PDOException $e) {
        /* Desfaz as alterações não confirmadas, se houver uma transação ativa. */
        if ($conexao->inTransaction()) $conexao->rollBack();
        error_log("Erro ao registrar tentativa de prova: " . $e->getMessage());
        return ['status' => 'error'];
    }
}

// Mostra ao aluno o histórico de notas e datas das tentativas feitas.
function listarTentativasProvaAluno($conexao, $id_matricula, $id_prova) {
    if (!$conexao) return false;
    try {
        $stmt = $conexao->prepare(
            "SELECT nota, aprovada, realizada_em
             FROM tentativas_prova
             WHERE id_matricula = :id_matricula AND id_prova = :id_prova
             ORDER BY realizada_em DESC, id DESC"
        );

        $stmt->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $stmt->bindValue(":id_prova", $id_prova, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erro ao listar tentativas de prova: " . $e->getMessage());
        return false;
    }
}

// Conta aulas obrigatórias ativas que ainda não foram concluídas pela matrícula.
function contarAulasObrigatoriasPendentes($conexao, $id_matricula, $id_curso) {
    if (!$conexao) return null;
    try {
        $stmt = $conexao->prepare(

            "SELECT COUNT(*)
             FROM aulas a
             LEFT JOIN progresso_aulas p
                    ON p.id_aula = a.id AND p.id_matricula = :id_matricula
             WHERE a.id_curso = :id_curso
               AND a.ativa = true
               AND a.obrigatoria = true
               AND p.id IS NULL"
        );

        $stmt->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $stmt->bindValue(":id_curso", $id_curso, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erro ao contar aulas obrigatórias pendentes: " . $e->getMessage());
        return null;
    }
}

// Busca os dados do certificado emitido para uma matrícula específica.
function obterCertificadoDaMatricula($conexao, $id_matricula) {
    if (!$conexao) return null;
    try {
        $stmt = $conexao->prepare(

            "SELECT ce.id, ce.codigo, ce.emitido_em, c.nome AS curso_nome,
                    c.carga_horaria, a.nome AS aluno_nome
             FROM certificados ce
             JOIN matriculas m ON m.id = ce.id_matricula
             JOIN cursos c ON c.id = m.id_curso
             JOIN alunos a ON a.id = m.id_aluno
             WHERE ce.id_matricula = :id_matricula"
        );

        $stmt->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $stmt->execute();
        /* O ternário condição ? valor_se_verdadeiro : valor_se_falso escolhe um dos dois valores. Já a
         * forma abreviada valor ?: alternativa usa a alternativa para qualquer valor considerado
         * falso. */
        return $stmt->fetch() ?: false;
    } catch (PDOException $e) {
        error_log("Erro ao buscar certificado da matrícula: " . $e->getMessage());
        return null;
    }
}

// Confere publicamente um código e devolve os dados do certificado correspondente.
function validarCertificadoPorCodigo($conexao, $codigo) {
    if (!$conexao) return null;
    try {
        $stmt = $conexao->prepare(

            "SELECT ce.codigo, ce.emitido_em, c.nome AS curso_nome,
                    c.carga_horaria, a.nome AS aluno_nome
             FROM certificados ce
             JOIN matriculas m ON m.id = ce.id_matricula
             JOIN cursos c ON c.id = m.id_curso
             JOIN alunos a ON a.id = m.id_aluno
             WHERE ce.codigo = :codigo"
        );
        $stmt->bindValue(":codigo", $codigo);
        $stmt->execute();
        /* O ternário condição ? valor_se_verdadeiro : valor_se_falso escolhe um dos dois valores. Já a
         * forma abreviada valor ?: alternativa usa a alternativa para qualquer valor considerado
         * falso. */
        return $stmt->fetch() ?: false;
    } catch (PDOException $e) {
        error_log("Erro ao validar certificado: " . $e->getMessage());
        return null;
    }
}

// Busca a matrícula ativa de um aluno em um curso.
function obterMatriculaAtivaAlunoCurso($conexao, $id_aluno, $id_curso) {
    if (!$conexao) return false;
    try {
        $sql = "SELECT m.id AS matricula_id, c.id AS curso_id, c.nome AS curso_nome,
                       c.categoria, c.descricao, c.carga_horaria
                FROM matriculas m
                JOIN cursos c ON c.id = m.id_curso
                WHERE m.id_aluno = :id_aluno
                  AND m.id_curso = :id_curso
                  AND m.status = 'Ativa'
                ORDER BY m.id DESC
                LIMIT 1";

        $stmt = $conexao->prepare($sql);

        $stmt->bindValue(":id_aluno", $id_aluno, PDO::PARAM_INT);
        $stmt->bindValue(":id_curso", $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Erro ao buscar matrícula ativa: " . $e->getMessage());
        return null;
    }
}

// Lista as aulas publicadas do curso e indica quais já foram concluídas.
function listarAulasDaMatricula($conexao, $id_matricula) {
    if (!$conexao) return false;
    try {
        $sql = "SELECT a.id, a.id_curso, a.titulo, a.descricao, a.conteudo,
                       a.video_url, a.ordem, a.obrigatoria,
                       CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END AS concluida
                FROM aulas a
                JOIN matriculas m ON m.id_curso = a.id_curso
                LEFT JOIN progresso_aulas p
                    ON p.id_aula = a.id AND p.id_matricula = m.id
                WHERE m.id = :id_matricula
                  AND m.status = 'Ativa'
                  AND a.ativa = true
                ORDER BY a.ordem ASC, a.id ASC";

        $stmt = $conexao->prepare($sql);

        $stmt->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erro ao listar aulas da matrícula: " . $e->getMessage());
        return false;
    }
}

// Registra a conclusão somente se a aula pertencer ao curso da matrícula ativa.
function concluirAulaDaMatricula($conexao, $id_matricula, $id_aula) {
    if (!$conexao) return false;
    try {
        $sql = "INSERT INTO progresso_aulas (id_matricula, id_aula)
                SELECT m.id, a.id
                FROM matriculas m
                JOIN aulas a ON a.id = :id_aula
                            AND a.id_curso = m.id_curso
                            AND a.ativa = true
                WHERE m.id = :id_matricula
                  AND m.status = 'Ativa'
                ON CONFLICT (id_matricula, id_aula) DO NOTHING";

        $stmt = $conexao->prepare($sql);

        $stmt->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $stmt->bindValue(":id_aula", $id_aula, PDO::PARAM_INT);
        $stmt->execute();

        $check = $conexao->prepare(

            "SELECT 1
             FROM progresso_aulas p
             JOIN matriculas m ON m.id = p.id_matricula
             JOIN aulas a ON a.id = p.id_aula AND a.id_curso = m.id_curso
             WHERE p.id_matricula = :id_matricula
               AND p.id_aula = :id_aula
               AND m.status = 'Ativa'
               AND a.ativa = true"
        );
        $check->bindValue(":id_matricula", $id_matricula, PDO::PARAM_INT);
        $check->bindValue(":id_aula", $id_aula, PDO::PARAM_INT);
        $check->execute();

        return (bool) $check->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erro ao concluir aula: " . $e->getMessage());
        return null;
    }
}

// Procura o cadastro acadêmico associado ao e-mail da conta.
function buscarAlunoPorEmail($conexao, $email) {
    if (!$conexao) return false;
    try {
        $stmt = $conexao->prepare("SELECT * FROM alunos WHERE LOWER(email) = LOWER(:email)");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Erro ao buscar aluno por email: " . $e->getMessage());
        return false;
    }
}

// Monta o painel do aluno com cursos, progresso, tentativas e certificados.
/* Objetivo: montar uma linha por matrícula com contagens de aulas, dados da prova e código do
 * certificado. As subconsultas evitam multiplicar as linhas da matrícula ao combinar várias listas. */
function listarCursosDoAluno($conexao, $id_aluno) {
    if (!$conexao) return [];
    try {
        $sql = "SELECT m.id AS matricula_id, m.data_matricula, m.status AS matricula_status,
                       c.id AS curso_id, c.nome AS curso_nome, c.categoria, c.descricao, c.carga_horaria,
                       (SELECT COUNT(*) FROM aulas a
                        WHERE a.id_curso = c.id AND a.ativa = true) AS aulas_total,
                       (SELECT COUNT(*) FROM progresso_aulas p
                        JOIN aulas a ON a.id = p.id_aula AND a.ativa = true
                        WHERE p.id_matricula = m.id AND a.id_curso = c.id) AS aulas_concluidas,
                       (SELECT COUNT(*) FROM aulas a
                        WHERE a.id_curso = c.id AND a.ativa = true AND a.obrigatoria = true) AS aulas_obrigatorias_total,
                       (SELECT COUNT(*) FROM progresso_aulas p
                        JOIN aulas a ON a.id = p.id_aula AND a.ativa = true AND a.obrigatoria = true
                        WHERE p.id_matricula = m.id AND a.id_curso = c.id) AS aulas_obrigatorias_concluidas,
                       (SELECT p.id FROM provas p WHERE p.id_curso = c.id AND p.ativa = true LIMIT 1) AS prova_id,
                       (SELECT p.nota_minima FROM provas p WHERE p.id_curso = c.id AND p.ativa = true LIMIT 1) AS prova_nota_minima,
                       (SELECT p.max_tentativas FROM provas p WHERE p.id_curso = c.id AND p.ativa = true LIMIT 1) AS prova_max_tentativas,
                       (SELECT COUNT(*) FROM tentativas_prova tp
                        WHERE tp.id_matricula = m.id
                          AND tp.id_prova = (SELECT p.id FROM provas p WHERE p.id_curso = c.id AND p.ativa = true LIMIT 1)) AS prova_tentativas_usadas,
                       (SELECT ce.codigo FROM certificados ce WHERE ce.id_matricula = m.id LIMIT 1) AS certificado_codigo
                FROM matriculas m
                JOIN cursos c ON m.id_curso = c.id
                WHERE m.id_aluno = :id_aluno
                ORDER BY m.id DESC";

        $stmt = $conexao->prepare($sql);

        $stmt->bindParam(":id_aluno", $id_aluno, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erro ao listar cursos do aluno: " . $e->getMessage());
        return [];
    }
}

// Valida a data de nascimento e a faixa etária aceita para o cadastro escolar.
/* Retorna a data, null se vazia ou false se invÃ¡lida. O parÃ¢metro por referÃªncia recebe a mensagem
 * de erro. */
function validarDataNascimento($data_string, &$mensagem_erro = null) {
    $data_limpa = trim((string)$data_string);

    if ($data_limpa === '') {
        return null;
    }

    /* DateTime:: chama um método da classe, sem usar um objeto prévio. Y-m-d espera ano-mês-dia,
     * formato enviado pelo input date, mesmo quando o navegador mostra dia/mês/ano. */
    $d = DateTime::createFromFormat('Y-m-d', $data_limpa);
    /* Cria um objeto com a data de hoje à meia-noite, no fuso configurado no PHP, para comparar o
     * nascimento sem depender do horário atual. */
    $hoje = new DateTime('today');

    /* A comparação com a data formatada detecta valores que o DateTime normalizou, como um dia
     * inexistente no mês. !== exige diferença de valor ou de tipo. */
    if (!$d || $d->format('Y-m-d') !== $data_limpa) {
        $mensagem_erro = 'Por favor, informe uma data de nascimento válida no formato dd/mm/aaaa.';
        return false;
    }

    if ($d > $hoje) {
        $mensagem_erro = 'A data de nascimento não pode estar no futuro.';
        return false;
    }

    /* diff() calcula um intervalo entre as datas; ->y lê seus anos completos. Isso considera se o
     * aniversário já ocorreu, em vez de apenas subtrair os anos do calendário. */
    $idade = $hoje->diff($d)->y;

    if ($idade < 14) {
        $mensagem_erro = 'O aluno deve ter no mínimo 14 anos para se matricular (idade calculada: ' . $idade . ' anos).';
        return false;
    }

    if ($idade > 100) {
        $mensagem_erro = 'Por favor, informe uma data de nascimento válida (idade máxima permitida: 100 anos).';
        return false;
    }

    return $data_limpa;
}

?>
