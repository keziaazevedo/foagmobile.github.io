<?php

require_once __DIR__ . '/../../config/bootstrap.php';

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);


// ==========================================
// FUNÇÃO DE RESPOSTA
// ==========================================

function responderAnotacao(
    $dados,
    $status = 200
) {
    http_response_code(
        $status
    );

    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


// ==========================================
// LOGIN
// ==========================================

if (
    empty(
        $_SESSION[
            'codigo_usuario'
        ]
    )
) {

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'Usuário não autenticado.'
        ],
        401
    );
}


// ==========================================
// MÉTODO
// ==========================================

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'Método não permitido.'
        ],
        405
    );
}


// ==========================================
// RECEBER JSON
// ==========================================

$raw =
    file_get_contents(
        'php://input'
    );


$entrada =
    json_decode(
        $raw,
        true
    );


if (
    !is_array(
        $entrada
    )
) {

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'Dados inválidos.'
        ],
        400
    );
}


// ==========================================
// TEXTO
// ==========================================

$texto =
    trim(
        (string)(
            $entrada['text'] ??
            ''
        )
    );


if (
    $texto === ''
) {

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'Digite uma anotação.'
        ],
        400
    );
}


if (
    mb_strlen(
        $texto
    ) > 200
) {

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'A anotação deve ter no máximo 200 caracteres.'
        ],
        400
    );
}


// ==========================================
// USUÁRIO
// ==========================================

$codigoUsuario =
    $_SESSION[
        'codigo_usuario'
    ];


$pastaUsuario =
    FOAG_USUARIOS_DIR . '/' . $codigoUsuario;


if (
    !is_dir(
        $pastaUsuario
    )
) {

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'Pasta do usuário não encontrada.'
        ],
        404
    );
}


// ==========================================
// ARQUIVO
// ==========================================

$arquivoAgenda =
    $pastaUsuario .
    '/agenda.json';


$estruturaPadrao = [
    'notas' => [],
    'tarefas' => [],
    'nao_esquecer' => []
];


// ==========================================
// ABRIR COM BLOQUEIO
// ==========================================

$arquivo =
    fopen(
        $arquivoAgenda,
        'c+'
    );


if (
    $arquivo === false
) {

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'Não foi possível abrir a Agenda.'
        ],
        500
    );
}


if (
    !flock(
        $arquivo,
        LOCK_EX
    )
) {

    fclose(
        $arquivo
    );

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'Não foi possível bloquear o arquivo.'
        ],
        500
    );
}


// ==========================================
// LER O agenda.json
// ==========================================

rewind(
    $arquivo
);


$conteudo =
    stream_get_contents(
        $arquivo
    );


$agendaData =
    $estruturaPadrao;


if (
    is_string(
        $conteudo
    ) &&
    trim(
        $conteudo
    ) !== ''
) {

    $lidos =
        json_decode(
            $conteudo,
            true
        );


    if (
        is_array(
            $lidos
        )
    ) {

        $agendaData =
            $lidos;
    }
}


// ==========================================
// GARANTIR ESTRUTURA
// ==========================================

if (
    !isset(
        $agendaData[
            'notas'
        ]
    ) ||
    !is_array(
        $agendaData[
            'notas'
        ]
    )
) {

    $agendaData[
        'notas'
    ] = [];
}


if (
    !isset(
        $agendaData[
            'tarefas'
        ]
    ) ||
    !is_array(
        $agendaData[
            'tarefas'
        ]
    )
) {

    $agendaData[
        'tarefas'
    ] = [];
}


if (
    !isset(
        $agendaData[
            'nao_esquecer'
        ]
    ) ||
    !is_array(
        $agendaData[
            'nao_esquecer'
        ]
    )
) {

    $agendaData[
        'nao_esquecer'
    ] = [];
}


// ==========================================
// CRIAR NOTA
// ==========================================

$id =
    (int)round(
        microtime(true) *
        1000
    );


$dataCompleta =
    date(
        'd/m/Y H:i'
    );


$novaNota = [
    'id' =>
        $id,

    'titulo' =>
        'Anotação rápida',

    'texto' =>
        $texto,

    'data' =>
        $dataCompleta
];


// Mais nova primeiro

array_unshift(
    $agendaData[
        'notas'
    ],
    $novaNota
);


// ==========================================
// GERAR JSON
// ==========================================

$json =
    json_encode(
        $agendaData,

        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


if (
    $json === false
) {

    flock(
        $arquivo,
        LOCK_UN
    );

    fclose(
        $arquivo
    );

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'Erro ao preparar os dados.'
        ],
        500
    );
}


// ==========================================
// GRAVAR
// ==========================================

rewind(
    $arquivo
);

ftruncate(
    $arquivo,
    0
);


$resultado =
    fwrite(
        $arquivo,
        $json
    );


fflush(
    $arquivo
);


flock(
    $arquivo,
    LOCK_UN
);


fclose(
    $arquivo
);


if (
    $resultado === false
) {

    responderAnotacao(
        [
            'sucesso' =>
                false,

            'mensagem' =>
                'Não foi possível salvar a anotação.'
        ],
        500
    );
}


// ==========================================
// RESPOSTA
// ==========================================

responderAnotacao(
    [
        'sucesso' =>
            true,

        'anotacao' => [
            'id' =>
                $id,

            'titulo' =>
                'Anotação rápida',

            'text' =>
                $texto,

            'date' =>
                $dataCompleta
        ]
    ]
);