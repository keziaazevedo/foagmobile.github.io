<?php

require_once __DIR__ . '/../../../core/usuario.php';
require_once __DIR__ . '/../../../core/json.php';

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
// POST
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
// ARQUIVO DO USUÁRIO
// ======================================

$baseJsonDir = foag_base_usuarios();


$pastaUsuario = foag_pasta_usuario($codigoUsuario);


$arquivoFlashcards =
    $pastaUsuario . '/flashcards.json';


if (
    !is_dir($pastaUsuario) ||
    !file_exists($arquivoFlashcards)
) {

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados de flashcards não encontrados.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// RECEBER JSON
// ======================================

$input =
    file_get_contents('php://input');


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
        'mensagem' => 'Dados inválidos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// CAMPOS
// ======================================

$idBaralho =
    trim(
        $data['baralho_id']
        ?? ''
    );


$idCartao =
    trim(
        $data['cartao_id']
        ?? ''
    );


$pergunta =
    trim(
        $data['pergunta']
        ?? ''
    );


$resposta =
    trim(
        $data['resposta']
        ?? ''
    );


// ======================================
// VALIDAR
// ======================================

if (
    $idBaralho === '' ||
    $idCartao === ''
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Baralho ou cartão não informado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if ($pergunta === '') {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Digite a pergunta.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if ($resposta === '') {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Digite a resposta.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (mb_strlen($pergunta) > 500) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'A pergunta ultrapassa 500 caracteres.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (mb_strlen($resposta) > 1000) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'A resposta ultrapassa 1000 caracteres.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ABRIR ARQUIVO
// ======================================

$handle =
    fopen(
        $arquivoFlashcards,
        'c+'
    );


if (!$handle) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Não foi possível abrir o arquivo.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// BLOQUEAR
// ======================================

if (!flock($handle, LOCK_EX)) {

    fclose($handle);

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Não foi possível bloquear o arquivo.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


rewind($handle);


$conteudo =
    stream_get_contents(
        $handle
    );


$flashcardsData =
    json_decode(
        $conteudo,
        true
    );


if (
    !is_array($flashcardsData) ||
    !isset($flashcardsData['baralhos']) ||
    !is_array($flashcardsData['baralhos'])
) {

    flock($handle, LOCK_UN);
    fclose($handle);

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Arquivo de flashcards inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ENCONTRAR BARALHO
// ======================================

$indiceBaralho =
    null;


$indiceCartao =
    null;


foreach (
    $flashcardsData['baralhos']
    as $i => $baralho
) {

    if (
        ($baralho['id'] ?? '') !==
        $idBaralho
    ) {
        continue;
    }


    $indiceBaralho =
        $i;


    $cartoes =
        $baralho['cartoes']
        ?? [];


    if (!is_array($cartoes)) {
        break;
    }


    foreach (
        $cartoes
        as $j => $cartao
    ) {

        if (
            ($cartao['id'] ?? '') ===
            $idCartao
        ) {

            $indiceCartao =
                $j;

            break 2;

        }

    }

}


// ======================================
// NÃO ENCONTRADO
// ======================================

if (
    $indiceBaralho === null ||
    $indiceCartao === null
) {

    flock($handle, LOCK_UN);
    fclose($handle);

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Cartão não encontrado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// VERIFICAR PERGUNTA DUPLICADA
// ======================================

foreach (
    $flashcardsData[
        'baralhos'
    ][
        $indiceBaralho
    ][
        'cartoes'
    ]
    as $j => $cartao
) {

    if ($j === $indiceCartao) {
        continue;
    }


    $perguntaExistente =
        trim(
            $cartao['pergunta']
            ?? ''
        );


    if (
        mb_strtolower($perguntaExistente) ===
        mb_strtolower($pergunta)
    ) {

        flock($handle, LOCK_UN);
        fclose($handle);

        http_response_code(409);

        echo json_encode([
            'sucesso' => false,
            'mensagem' =>
                'Já existe outro cartão com essa pergunta.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

}


// ======================================
// ATUALIZAR CARTÃO
// ======================================

$cartaoAtual =
    &$flashcardsData[
        'baralhos'
    ][
        $indiceBaralho
    ][
        'cartoes'
    ][
        $indiceCartao
    ];


$cartaoAtual['pergunta'] =
    $pergunta;


$cartaoAtual['resposta'] =
    $resposta;


$cartaoAtual['editado_em'] =
    date('Y-m-d H:i:s');


// ======================================
// GERAR JSON
// ======================================

$json =
    json_encode(
        $flashcardsData,

        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


if ($json === false) {

    flock($handle, LOCK_UN);
    fclose($handle);

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Erro ao gerar os dados.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// SALVAR
// ======================================

rewind($handle);

ftruncate(
    $handle,
    0
);


$resultado =
    fwrite(
        $handle,
        $json
    );


fflush($handle);

flock(
    $handle,
    LOCK_UN
);

fclose($handle);


if ($resultado === false) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Não foi possível atualizar o cartão.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// RESPOSTA
// ======================================

echo json_encode([

    'sucesso' =>
        true,

    'mensagem' =>
        'Cartão atualizado com sucesso.',

    'cartao' =>
        $cartaoAtual

], JSON_UNESCAPED_UNICODE);