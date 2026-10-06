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
// SISTEMA DE ESTRELAS
// ======================================

$arquivoEstrelas =
    __DIR__ . '/../../estrelas/adicionar_estrelas.php';

if (!file_exists($arquivoEstrelas)) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Arquivo do sistema de estrelas não encontrado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once $arquivoEstrelas;


// ======================================
// SOMENTE POST
// ======================================

if (
    $_SERVER['REQUEST_METHOD']
    !== 'POST'
) {

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


if (!is_dir($pastaUsuario)) {

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Pasta do usuário não encontrada.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


// ======================================
// FLASHCARDS.JSON
// ======================================

$arquivoFlashcards =
    $pastaUsuario . '/flashcards.json';


if (!file_exists($arquivoFlashcards)) {

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Arquivo de flashcards não encontrado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


// ======================================
// RECEBER DADOS
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
        'mensagem' => 'Nenhum dado foi recebido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


$data =
    json_decode(
        $input,
        true
    );


if (
    json_last_error()
    !== JSON_ERROR_NONE
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'JSON inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Formato de dados inválido.'
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

if ($idBaralho === '') {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Baralho não informado.'
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


if (
    mb_strlen($pergunta)
    > 500
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'A pergunta ultrapassa o limite de 500 caracteres.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


if (
    mb_strlen($resposta)
    > 1000
) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'A resposta ultrapassa o limite de 1000 caracteres.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


// ======================================
// CARREGAR JSON
// ======================================

$conteudo =
    file_get_contents(
        $arquivoFlashcards
    );


$flashcardsData =
    json_decode(
        $conteudo,
        true
    );


if (
    !is_array($flashcardsData) ||
    !isset(
        $flashcardsData['baralhos']
    ) ||
    !is_array(
        $flashcardsData['baralhos']
    )
) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Arquivo de flashcards inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


// ======================================
// LOCALIZAR BARALHO
// ======================================

$indiceBaralho =
    null;


foreach (
    $flashcardsData['baralhos']
    as $indice => $baralho
) {

    if (
        ($baralho['id'] ?? '')
        === $idBaralho
    ) {

        $indiceBaralho =
            $indice;

        break;

    }

}


if ($indiceBaralho === null) {

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Baralho não encontrado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


// ======================================
// GARANTIR CARTÕES
// ======================================

if (
    !isset(
        $flashcardsData[
            'baralhos'
        ][
            $indiceBaralho
        ][
            'cartoes'
        ]
    ) ||

    !is_array(
        $flashcardsData[
            'baralhos'
        ][
            $indiceBaralho
        ][
            'cartoes'
        ]
    )
) {

    $flashcardsData[
        'baralhos'
    ][
        $indiceBaralho
    ][
        'cartoes'
    ] = [];

}


// ======================================
// EVITAR PERGUNTA DUPLICADA
// ======================================

foreach (
    $flashcardsData[
        'baralhos'
    ][
        $indiceBaralho
    ][
        'cartoes'
    ]
    as $cartao
) {

    $perguntaExistente =
        trim(
            $cartao['pergunta']
            ?? ''
        );


    if (
        mb_strtolower(
            $perguntaExistente
        )
        ===
        mb_strtolower(
            $pergunta
        )
    ) {

        http_response_code(409);

        echo json_encode([
            'sucesso' => false,
            'mensagem' =>
                'Já existe um cartão com essa pergunta neste baralho.'
        ], JSON_UNESCAPED_UNICODE);

        exit;

    }

}


// ======================================
// GERAR ID
// ======================================

try {

    $idCartao =
        'CARD_' .
        strtoupper(
            bin2hex(
                random_bytes(4)
            )
        );

} catch (Exception $e) {

    $idCartao =
        'CARD_' .
        strtoupper(
            uniqid()
        );

}


// ======================================
// NOVO CARTÃO
// ======================================

$novoCartao = [

    'id' =>
        $idCartao,

    'pergunta' =>
        $pergunta,

    'resposta' =>
        $resposta,

    'acertos' =>
        0,

    'erros' =>
        0,

    // Aqui ficam as revisões
    // realizadas na tela de estudar.
    'revisoes' =>
        [],

    'criado_em' =>
        date(
            'Y-m-d H:i:s'
        )

];


// ======================================
// ADICIONAR CARTÃO
// ======================================

$flashcardsData[
    'baralhos'
][
    $indiceBaralho
][
    'cartoes'
][] =
    $novoCartao;


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

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Erro ao preparar os dados.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


// ======================================
// SALVAR
// ======================================

$resultado =
    file_put_contents(
        $arquivoFlashcards,
        $json,
        LOCK_EX
    );


if ($resultado === false) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Não foi possível salvar o cartão.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}


// ======================================
// RECOMPENSAS DO BARALHO
// ======================================

$totalCartoes =
    count(
        $flashcardsData[
            'baralhos'
        ][
            $indiceBaralho
        ][
            'cartoes'
        ]
    );

$nomeBaralho =
    trim(
        (string)(
            $flashcardsData[
                'baralhos'
            ][
                $indiceBaralho
            ][
                'nome'
            ] ?? 'Baralho'
        )
    );

$estrelasGanhas = 0;
$recompensasConcedidas = [];


// ======================================
// MARCO: 15 CARTÕES
// ======================================

if ($totalCartoes >= 15) {

    $chave15 =
        'flashcards_baralho_15_' .
        $idBaralho;

    $adicionou15 =
        adicionarEstrelas(
            $codigoUsuario,
            'flashcards',
            'Baralho “' . $nomeBaralho . '” alcançou 15 cartões',
            5,
            $chave15
        );

    if ($adicionou15) {

        $estrelasGanhas += 5;

        $recompensasConcedidas[] = [
            'tipo' => 'baralho_15',
            'marco' => 15,
            'estrelas' => 5
        ];
    }
}


// ======================================
// MARCO: MAIS DE 30 CARTÕES
// ======================================

if ($totalCartoes >= 31) {

    $chave31 =
        'flashcards_baralho_31_' .
        $idBaralho;

    $adicionou31 =
        adicionarEstrelas(
            $codigoUsuario,
            'flashcards',
            'Baralho “' . $nomeBaralho . '” ultrapassou 30 cartões',
            5,
            $chave31
        );

    if ($adicionou31) {

        $estrelasGanhas += 5;

        $recompensasConcedidas[] = [
            'tipo' => 'baralho_31',
            'marco' => 31,
            'estrelas' => 5
        ];
    }
}


// ======================================
// RESPOSTA
// ======================================

echo json_encode([

    'sucesso' =>
        true,

    'mensagem' =>
        'Cartão criado com sucesso.',

    'cartao' =>
        $novoCartao,

    'pontos' => [
        'estrelas' =>
            $estrelasGanhas,

        'total_cartoes' =>
            $totalCartoes,

        'recompensas' =>
            $recompensasConcedidas
    ]

], JSON_UNESCAPED_UNICODE);