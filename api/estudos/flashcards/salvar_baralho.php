<?php

require_once __DIR__ . '/../../../core/usuario.php';
require_once __DIR__ . '/../../../core/json.php';

$codigoUsuario = foag_codigo_usuario(false);
header('Content-Type: application/json; charset=utf-8');


// ======================================
// VERIFICAR LOGIN
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

if (!is_dir($pastaUsuario)) {

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Pasta do usuário não encontrada.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ARQUIVOS
// ======================================

$arquivoMaterias =
    $pastaUsuario . '/materias.json';

$arquivoFlashcards =
    $pastaUsuario . '/flashcards.json';


// ======================================
// RECEBER JSON
// ======================================

$input =
    file_get_contents('php://input');

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
    json_decode($input, true);


if (
    json_last_error() !==
    JSON_ERROR_NONE
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
// DADOS DO BARALHO
// ======================================

$nome =
    trim(
        $data['nome'] ?? ''
    );

$materiaNome =
    trim(
        $data['materia'] ?? ''
    );

$descricao =
    trim(
        $data['descricao'] ?? ''
    );


// ======================================
// VALIDAR CAMPOS
// ======================================

if ($nome === '') {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Digite o nome do baralho.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if ($materiaNome === '') {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Escolha uma matéria.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (mb_strlen($nome) > 60) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'O nome do baralho é muito grande.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (mb_strlen($descricao) > 180) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'A descrição é muito grande.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// CARREGAR MATÉRIAS
// ======================================

if (!file_exists($arquivoMaterias)) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Nenhuma matéria foi cadastrada.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$materiasData =
    json_decode(
        file_get_contents(
            $arquivoMaterias
        ),
        true
    );


if (
    !is_array($materiasData) ||
    !isset($materiasData['materias']) ||
    !is_array($materiasData['materias'])
) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Arquivo de matérias inválido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// ENCONTRAR MATÉRIA
// ======================================

$materiaEncontrada = null;


foreach (
    $materiasData['materias']
    as $materia
) {

    $nomeAtual =
        trim(
            $materia['nome'] ?? ''
        );


    if (
        mb_strtolower($nomeAtual) ===
        mb_strtolower($materiaNome)
    ) {

        $materiaEncontrada =
            $materia;

        break;
    }

}


// ======================================
// VALIDAR MATÉRIA
// ======================================

if (!$materiaEncontrada) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'A matéria selecionada não existe mais.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================
// PEGAR COR E ÍCONE DA MATÉRIA
// ======================================

$cor =
    $materiaEncontrada['cor']
    ?? '#38a5ff';

$icone =
    $materiaEncontrada['icone']
    ?? 'fa-book';


// Validar cor
if (
    !preg_match(
        '/^#[0-9A-Fa-f]{6}$/',
        $cor
    )
) {

    $cor = '#38a5ff';
}


// ======================================
// CARREGAR FLASHCARDS
// ======================================

if (!file_exists($arquivoFlashcards)) {

    $flashcardsData = [
        'baralhos' => []
    ];

} else {

    $flashcardsData =
        json_decode(
            file_get_contents(
                $arquivoFlashcards
            ),
            true
        );


    if (!is_array($flashcardsData)) {

        $flashcardsData = [
            'baralhos' => []
        ];

    }

}


if (
    !isset(
        $flashcardsData['baralhos']
    ) ||
    !is_array(
        $flashcardsData['baralhos']
    )
) {

    $flashcardsData['baralhos'] = [];

}


// ======================================
// VERIFICAR BARALHO DUPLICADO
// ======================================

foreach (
    $flashcardsData['baralhos']
    as $baralho
) {

    $nomeExistente =
        trim(
            $baralho['nome']
            ?? ''
        );

    $materiaExistente =
        trim(
            $baralho['materia']
            ?? ''
        );


    if (
        mb_strtolower(
            $nomeExistente
        ) ===
        mb_strtolower($nome)

        &&

        mb_strtolower(
            $materiaExistente
        ) ===
        mb_strtolower($materiaNome)
    ) {

        http_response_code(409);

        echo json_encode([
            'sucesso' => false,
            'mensagem' =>
                'Já existe um baralho com esse nome nessa matéria.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

}


// ======================================
// GERAR ID
// ======================================

try {

    $id =
        'BAR_' .
        strtoupper(
            bin2hex(
                random_bytes(4)
            )
        );

} catch (Exception $e) {

    $id =
        'BAR_' .
        strtoupper(
            uniqid()
        );

}


// ======================================
// NOVO BARALHO
// ======================================

$novoBaralho = [

    'id' =>
        $id,

    'nome' =>
        $nome,

    'materia' =>
        $materiaNome,

    'descricao' =>
        $descricao,

    'cor' =>
        $cor,

    'icone' =>
        $icone,

    'cartoes' =>
        [],

    'estatisticas' => [

        'acertos' =>
            0,

        'erros' =>
            0,

        'revisoes' =>
            0

    ],

    'criado_em' =>
        date(
            'Y-m-d H:i:s'
        )

];


// ======================================
// ADICIONAR
// ======================================

$flashcardsData['baralhos'][] =
    $novoBaralho;


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
        'mensagem' => 'Erro ao preparar os dados.'
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
        'mensagem' => 'Não foi possível salvar o baralho.'
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
        'Baralho criado com sucesso.',

    'baralho' =>
        $novoBaralho

], JSON_UNESCAPED_UNICODE);