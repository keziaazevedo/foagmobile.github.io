<?php

require_once __DIR__ . '/../../../core/usuario.php';
require_once __DIR__ . '/../../../core/json.php';

$codigoUsuario = foag_codigo_usuario(false);
header(
    'Content-Type: application/json; charset=utf-8'
);


// ==========================================
// VERIFICAR LOGIN
// ==========================================

if (
    $codigoUsuario === null
) {

    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Usuário não autenticado.'
    ]);

    exit;
}


// ==========================================
// RECEBER DADOS
// ==========================================

$entrada =
    json_decode(
        file_get_contents(
            'php://input'
        ),
        true
    );


if (!is_array($entrada)) {

    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Dados inválidos.'
    ]);

    exit;
}


// ==========================================
// PASTA DO USUÁRIO
// ==========================================

$pastaUsuario = foag_pasta_usuario($codigoUsuario);


if (!is_dir($pastaUsuario)) {

    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Pasta do usuário não encontrada.'
    ]);

    exit;
}


$arquivoPomodoro =
    $pastaUsuario .
    '/pomodoro.json';


// ==========================================
// DADOS ANTERIORES
// ==========================================

$dadosAnteriores = [
    'disciplines' => ['Geral'],
    'sessions' => [],
    'goals' => []
];


if (
    file_exists(
        $arquivoPomodoro
    )
) {

    $conteudoAnterior =
        file_get_contents(
            $arquivoPomodoro
        );


    if (
        $conteudoAnterior !== false
    ) {

        $jsonAnterior =
            json_decode(
                $conteudoAnterior,
                true
            );


        if (
            is_array(
                $jsonAnterior
            )
        ) {

            $dadosAnteriores =
                $jsonAnterior;
        }
    }
}


// ==========================================
// NORMALIZAR DADOS
// ==========================================

if (
    !isset(
        $entrada['sessions']
    ) ||
    !is_array(
        $entrada['sessions']
    )
) {

    $entrada['sessions'] = [];
}


if (
    !isset(
        $entrada['disciplines']
    ) ||
    !is_array(
        $entrada['disciplines']
    )
) {

    $entrada['disciplines'] = [
        'Geral'
    ];
}


if (
    !isset(
        $entrada['goals']
    ) ||
    !is_array(
        $entrada['goals']
    )
) {

    $entrada['goals'] = [];
}


// ==========================================
// SALVAR POMODORO
// ==========================================

$salvou =
    file_put_contents(
        $arquivoPomodoro,

        json_encode(
            $entrada,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ),

        LOCK_EX
    );


if ($salvou === false) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' =>
            'Não foi possível salvar o Pomodoro.'
    ]);

    exit;
}


// ==========================================
// CARREGAR SISTEMA DE ESTRELAS
// ==========================================

require_once(
    __DIR__ . '/../../estrelas/adicionar_estrelas.php'
);


// ==========================================
// PROCESSAR ESTRELAS
// ==========================================

$resultadoEstrelas =
    processarEstrelasPomodoro(
        $codigoUsuario,
        $dadosAnteriores,
        $entrada
    );


// ==========================================
// PEGAR TOTAL DE ESTRELAS
// ==========================================

$pontos =
    carregarPontos(
        $codigoUsuario
    );


// ==========================================
// RESPOSTA
// ==========================================

echo json_encode(
    [
        'sucesso' => true,

        'mensagem' =>
            'Pomodoro salvo com sucesso.',

        'estrelas' =>
            $pontos['estrelas']
            ?? 0,

        'estrelas_sessoes' =>
            $resultadoEstrelas[
                'estrelas_sessoes'
            ]
            ?? 0,

        'estrelas_bonus' =>
            $resultadoEstrelas[
                'estrelas_bonus'
            ]
            ?? 0,

        'limite_diario_atingido' =>
            $resultadoEstrelas[
                'limite_diario_atingido'
            ]
            ?? false,

        'sessoes_premiadas_hoje' =>
            $resultadoEstrelas[
                'sessoes_premiadas_hoje'
            ]
            ?? 0
    ],

    JSON_UNESCAPED_UNICODE
);