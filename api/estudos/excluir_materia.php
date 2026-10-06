<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(false);
header(
    'Content-Type: application/json; charset=utf-8'
);


// ======================================
// LOGIN
// ======================================

if ($codigoUsuario === null) {

    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// SOMENTE POST
// ======================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = foag_base_usuarios();


$pastaUsuario = foag_pasta_usuario($codigoUsuario);


$arquivoMaterias =
    $pastaUsuario . '/materias.json';


$arquivoFlashcards =
    $pastaUsuario . '/flashcards.json';


if (
    !is_dir($pastaUsuario) ||
    !file_exists($arquivoMaterias)
) {

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Arquivo de matérias não encontrado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// RECEBER JSON
// ======================================

$input =
    file_get_contents(
        'php://input'
    );


if (
    $input === false ||
    trim($input) === ''
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Nenhum dado recebido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$data =
    json_decode(
        $input,
        true
    );


if (
    json_last_error() !== JSON_ERROR_NONE ||
    !is_array($data)
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Dados inválidos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ID DA MATÉRIA
// ======================================

$idMateria =
    trim(
        $data['id']
        ?? ''
    );


if ($idMateria === '') {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Matéria não informada.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ABRIR materias.json
// ======================================

$handleMaterias =
    fopen(
        $arquivoMaterias,
        'c+'
    );


if (!$handleMaterias) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Não foi possível abrir o arquivo de matérias.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// BLOQUEAR materias.json
// ======================================

if (
    !flock(
        $handleMaterias,
        LOCK_EX
    )
) {

    fclose(
        $handleMaterias
    );

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Não foi possível bloquear o arquivo de matérias.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// LER materias.json
// ======================================

rewind(
    $handleMaterias
);


$conteudoMaterias =
    stream_get_contents(
        $handleMaterias
    );


$materiasData =
    json_decode(
        $conteudoMaterias,
        true
    );


if (
    !is_array($materiasData) ||
    !isset($materiasData['materias']) ||
    !is_array($materiasData['materias'])
) {

    flock(
        $handleMaterias,
        LOCK_UN
    );

    fclose(
        $handleMaterias
    );

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Arquivo de matérias inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ENCONTRAR MATÉRIA
// ======================================

$indiceMateria =
    null;


$materiaExcluida =
    null;


foreach (
    $materiasData['materias']
    as $indice => $materia
) {

    if (
        ($materia['id'] ?? '') ===
        $idMateria
    ) {

        $indiceMateria =
            $indice;


        $materiaExcluida =
            $materia;


        break;

    }

}


// ======================================
// MATÉRIA NÃO ENCONTRADA
// ======================================

if ($indiceMateria === null) {

    flock(
        $handleMaterias,
        LOCK_UN
    );

    fclose(
        $handleMaterias
    );

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Matéria não encontrada.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// NOME DA MATÉRIA
// ======================================

$nomeMateria =
    trim(
        $materiaExcluida['nome']
        ?? ''
    );


$nomeMateriaNormalizado =
    mb_strtolower(
        $nomeMateria,
        'UTF-8'
    );


// ======================================
// EXCLUIR FLASHCARDS DA MATÉRIA
// ======================================

$handleFlashcards =
    null;


$conteudoFlashcardsOriginal =
    null;


$baralhosExcluidos =
    0;


if (file_exists($arquivoFlashcards)) {

    $handleFlashcards =
        fopen(
            $arquivoFlashcards,
            'c+'
        );


    if (!$handleFlashcards) {

        flock(
            $handleMaterias,
            LOCK_UN
        );

        fclose(
            $handleMaterias
        );

        http_response_code(500);

        echo json_encode([
            'sucesso' => false,
            'mensagem' =>
                'Não foi possível abrir os flashcards.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    if (
        !flock(
            $handleFlashcards,
            LOCK_EX
        )
    ) {

        fclose(
            $handleFlashcards
        );

        flock(
            $handleMaterias,
            LOCK_UN
        );

        fclose(
            $handleMaterias
        );

        http_response_code(500);

        echo json_encode([
            'sucesso' => false,
            'mensagem' =>
                'Não foi possível bloquear os flashcards.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // ==================================
    // LER flashcards.json
    // ==================================

    rewind(
        $handleFlashcards
    );


    $conteudoFlashcardsOriginal =
        stream_get_contents(
            $handleFlashcards
        );


    $flashcardsData =
        json_decode(
            $conteudoFlashcardsOriginal,
            true
        );


    if (
        !is_array($flashcardsData) ||
        !isset($flashcardsData['baralhos']) ||
        !is_array($flashcardsData['baralhos'])
    ) {

        flock(
            $handleFlashcards,
            LOCK_UN
        );

        fclose(
            $handleFlashcards
        );

        flock(
            $handleMaterias,
            LOCK_UN
        );

        fclose(
            $handleMaterias
        );

        http_response_code(500);

        echo json_encode([
            'sucesso' => false,
            'mensagem' =>
                'Arquivo de flashcards inválido.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // ==================================
    // FILTRAR BARALHOS
    // ==================================

    $novosBaralhos =
        [];


    foreach (
        $flashcardsData['baralhos']
        as $baralho
    ) {

        $materiaBaralho =
            trim(
                $baralho['materia']
                ?? ''
            );


        $materiaBaralhoNormalizada =
            mb_strtolower(
                $materiaBaralho,
                'UTF-8'
            );


        // Se pertence à matéria excluída,
        // não adiciona novamente.
        if (
            $materiaBaralhoNormalizada ===
            $nomeMateriaNormalizado
        ) {

            $baralhosExcluidos++;

            continue;

        }


        $novosBaralhos[] =
            $baralho;

    }


    $flashcardsData['baralhos'] =
        $novosBaralhos;


    // ==================================
    // GERAR NOVO flashcards.json
    // ==================================

    $jsonFlashcards =
        json_encode(
            $flashcardsData,

            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );


    if ($jsonFlashcards === false) {

        flock(
            $handleFlashcards,
            LOCK_UN
        );

        fclose(
            $handleFlashcards
        );

        flock(
            $handleMaterias,
            LOCK_UN
        );

        fclose(
            $handleMaterias
        );

        http_response_code(500);

        echo json_encode([
            'sucesso' => false,
            'mensagem' =>
                'Erro ao preparar os flashcards.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // ==================================
    // SALVAR flashcards.json
    // ==================================

    rewind(
        $handleFlashcards
    );


    ftruncate(
        $handleFlashcards,
        0
    );


    $resultadoFlashcards =
        fwrite(
            $handleFlashcards,
            $jsonFlashcards
        );


    fflush(
        $handleFlashcards
    );


    if ($resultadoFlashcards === false) {

        flock(
            $handleFlashcards,
            LOCK_UN
        );

        fclose(
            $handleFlashcards
        );

        flock(
            $handleMaterias,
            LOCK_UN
        );

        fclose(
            $handleMaterias
        );

        http_response_code(500);

        echo json_encode([
            'sucesso' => false,
            'mensagem' =>
                'Não foi possível excluir os flashcards da matéria.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

}


// ======================================
// REMOVER MATÉRIA
// ======================================

array_splice(
    $materiasData['materias'],
    $indiceMateria,
    1
);


// ======================================
// GERAR materias.json
// ======================================

$jsonMaterias =
    json_encode(
        $materiasData,

        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


if ($jsonMaterias === false) {

    // Tenta restaurar flashcards
    if (
        $handleFlashcards &&
        $conteudoFlashcardsOriginal !== null
    ) {

        rewind(
            $handleFlashcards
        );

        ftruncate(
            $handleFlashcards,
            0
        );

        fwrite(
            $handleFlashcards,
            $conteudoFlashcardsOriginal
        );

        fflush(
            $handleFlashcards
        );

    }


    if ($handleFlashcards) {

        flock(
            $handleFlashcards,
            LOCK_UN
        );

        fclose(
            $handleFlashcards
        );

    }


    flock(
        $handleMaterias,
        LOCK_UN
    );

    fclose(
        $handleMaterias
    );


    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Erro ao preparar as matérias.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// SALVAR materias.json
// ======================================

rewind(
    $handleMaterias
);


ftruncate(
    $handleMaterias,
    0
);


$resultadoMaterias =
    fwrite(
        $handleMaterias,
        $jsonMaterias
    );


fflush(
    $handleMaterias
);


// ======================================
// ERRO AO SALVAR MATÉRIAS
// ======================================

if ($resultadoMaterias === false) {

    // Restaura flashcards
    if (
        $handleFlashcards &&
        $conteudoFlashcardsOriginal !== null
    ) {

        rewind(
            $handleFlashcards
        );

        ftruncate(
            $handleFlashcards,
            0
        );

        fwrite(
            $handleFlashcards,
            $conteudoFlashcardsOriginal
        );

        fflush(
            $handleFlashcards
        );

    }


    if ($handleFlashcards) {

        flock(
            $handleFlashcards,
            LOCK_UN
        );

        fclose(
            $handleFlashcards
        );

    }


    flock(
        $handleMaterias,
        LOCK_UN
    );

    fclose(
        $handleMaterias
    );


    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Não foi possível excluir a matéria.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// LIBERAR ARQUIVOS
// ======================================

if ($handleFlashcards) {

    flock(
        $handleFlashcards,
        LOCK_UN
    );

    fclose(
        $handleFlashcards
    );

}


flock(
    $handleMaterias,
    LOCK_UN
);

fclose(
    $handleMaterias
);


// ======================================
// RESPOSTA
// ======================================

echo json_encode([

    'sucesso' =>
        true,

    'mensagem' =>
        'Matéria e flashcards excluídos com sucesso.',

    'materia' =>
        $materiaExcluida,

    'baralhos_excluidos' =>
        $baralhosExcluidos

], JSON_UNESCAPED_UNICODE);