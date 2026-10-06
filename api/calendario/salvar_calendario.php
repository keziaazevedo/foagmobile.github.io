<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(true);
header(
    'Content-Type: application/json; charset=utf-8'
);


// ==========================================
// CARREGAR SISTEMA DE ESTRELAS
// ==========================================

$arquivoEstrelas =
    __DIR__ . '/../estrelas/adicionar_estrelas.php';


if (
    !file_exists(
        $arquivoEstrelas
    )
) {

    http_response_code(
        500
    );


    echo json_encode(
        [
            'sucesso' => false,

            'mensagem' =>
                'Arquivo do sistema de estrelas não encontrado.',

            'arquivo' =>
                $arquivoEstrelas
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


    exit;
}


require_once
    $arquivoEstrelas;


// ==========================================
// VERIFICAR LOGIN
// ==========================================

if (
    !$codigoUsuario
) {

    http_response_code(
        401
    );


    echo json_encode(
        [
            'sucesso' => false,

            'mensagem' =>
                'Usuário não autenticado.'
        ],
        JSON_UNESCAPED_UNICODE
    );


    exit;
}


// ==========================================
// ACEITAR APENAS POST
// ==========================================

if (
    $_SERVER[
        'REQUEST_METHOD'
    ] !==
    'POST'
) {

    http_response_code(
        405
    );


    echo json_encode(
        [
            'sucesso' => false,

            'mensagem' =>
                'Método não permitido.'
        ],
        JSON_UNESCAPED_UNICODE
    );


    exit;
}


// ==========================================
// PASTA DO USUÁRIO
// ==========================================

$pastaUsuario = foag_pasta_usuario($codigoUsuario);


if (
    !is_dir(
        $pastaUsuario
    )
) {

    http_response_code(
        404
    );


    echo json_encode(
        [
            'sucesso' => false,

            'mensagem' =>
                'Pasta do usuário não encontrada.'
        ],
        JSON_UNESCAPED_UNICODE
    );


    exit;
}


$arquivoCalendario =
    $pastaUsuario .
    '/calendario.json';


// ==========================================
// RECEBER JSON
// ==========================================

$conteudo =
    file_get_contents(
        'php://input'
    );


$dadosRecebidos =
    json_decode(
        $conteudo ?: '',
        true
    );


if (
    !is_array(
        $dadosRecebidos
    )
) {

    http_response_code(
        400
    );


    echo json_encode(
        [
            'sucesso' => false,

            'mensagem' =>
                'JSON inválido.'
        ],
        JSON_UNESCAPED_UNICODE
    );


    exit;
}


// ==========================================
// VALIDAR DATA
// ==========================================

function dataCalendarioValida(
    $data
) {

    if (
        !is_string(
            $data
        )
    ) {

        return false;
    }


    if (
        !preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $data
        )
    ) {

        return false;
    }


    $objetoData =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $data
        );


    return (
        $objetoData &&
        $objetoData->format(
            'Y-m-d'
        ) ===
        $data
    );
}


// ==========================================
// ESTRUTURA FINAL
// ==========================================

$dadosSalvar = [

    'dias' =>
        [],

    'metas' =>
        [],

    'configuracoes' =>
        []
];


// ==========================================
// STATUS DOS DIAS
// ==========================================

$statusPermitidos = [

    'vermelho',

    'amarelo',

    'sem-aula',

    'roxo'
];


$diasRecebidos =
    $dadosRecebidos[
        'dias'
    ] ?? [];


if (
    is_array(
        $diasRecebidos
    )
) {

    foreach (
        $diasRecebidos
        as $data => $status
    ) {

        if (
            !dataCalendarioValida(
                $data
            )
        ) {

            continue;
        }


        if (
            !in_array(
                $status,
                $statusPermitidos,
                true
            )
        ) {

            continue;
        }


        $dadosSalvar[
            'dias'
        ][
            $data
        ] =
            $status;
    }
}


// ==========================================
// METAS MENSAIS
// ==========================================

$metasRecebidas =
    $dadosRecebidos[
        'metas'
    ] ?? [];


if (
    is_array(
        $metasRecebidas
    )
) {

    foreach (
        $metasRecebidas
        as $chave => $meta
    ) {

        if (
            !preg_match(
                '/^\d{4}-(?:[1-9]|1[0-2])$/',
                (string)$chave
            )
        ) {

            continue;
        }


        if (
            !is_numeric(
                $meta
            )
        ) {

            continue;
        }


        $meta =
            (float)$meta;


        $meta =
            max(
                0,
                min(
                    100,
                    $meta
                )
            );


        $dadosSalvar[
            'metas'
        ][
            $chave
        ] =
            $meta;
    }
}


// ==========================================
// CONFIGURAÇÕES ANUAIS
// ==========================================

$configuracoesRecebidas =
    $dadosRecebidos[
        'configuracoes'
    ] ?? [];


if (
    is_array(
        $configuracoesRecebidas
    )
) {

    foreach (
        $configuracoesRecebidas
        as $ano => $config
    ) {

        if (
            !preg_match(
                '/^\d{4}$/',
                (string)$ano
            )
        ) {

            continue;
        }


        if (
            !is_array(
                $config
            )
        ) {

            continue;
        }


        // ======================================
        // META ANUAL
        // ======================================

        $metaAnual =
            isset(
                $config[
                    'meta_anual'
                ]
            )
                ?
                (float)$config[
                    'meta_anual'
                ]
                :
                80;


        $metaAnual =
            max(
                0,
                min(
                    100,
                    $metaAnual
                )
            );


        // ======================================
        // DATAS
        // ======================================

        $inicioAno =
            $config[
                'inicio_ano_letivo'
            ] ?? '';


        $fimAno =
            $config[
                'fim_ano_letivo'
            ] ?? '';


        $inicioFerias =
            $config[
                'inicio_ferias_meio'
            ] ?? '';


        $fimFerias =
            $config[
                'fim_ferias_meio'
            ] ?? '';


        if (
            $inicioAno !== '' &&
            !dataCalendarioValida(
                $inicioAno
            )
        ) {

            $inicioAno =
                '';
        }


        if (
            $fimAno !== '' &&
            !dataCalendarioValida(
                $fimAno
            )
        ) {

            $fimAno =
                '';
        }


        if (
            $inicioFerias !== '' &&
            !dataCalendarioValida(
                $inicioFerias
            )
        ) {

            $inicioFerias =
                '';
        }


        if (
            $fimFerias !== '' &&
            !dataCalendarioValida(
                $fimFerias
            )
        ) {

            $fimFerias =
                '';
        }


        // ======================================
        // GARANTIR ORDEM DAS DATAS
        // ======================================

        if (
            $inicioAno !== '' &&
            $fimAno !== '' &&
            $inicioAno >
                $fimAno
        ) {

            http_response_code(
                400
            );


            echo json_encode(
                [
                    'sucesso' =>
                        false,

                    'mensagem' =>
                        'O início do ano letivo não pode ser posterior ao final.'
                ],
                JSON_UNESCAPED_UNICODE
            );


            exit;
        }


        if (
            $inicioFerias !== '' &&
            $fimFerias !== '' &&
            $inicioFerias >
                $fimFerias
        ) {

            http_response_code(
                400
            );


            echo json_encode(
                [
                    'sucesso' =>
                        false,

                    'mensagem' =>
                        'O início das férias não pode ser posterior ao final.'
                ],
                JSON_UNESCAPED_UNICODE
            );


            exit;
        }


        // ======================================
        // MONTAR CONFIGURAÇÃO
        // ======================================

        $dadosSalvar[
            'configuracoes'
        ][
            (string)$ano
        ] = [

            'meta_anual' =>
                $metaAnual,

            'inicio_ano_letivo' =>
                $inicioAno,

            'fim_ano_letivo' =>
                $fimAno,

            'inicio_ferias_meio' =>
                $inicioFerias,

            'fim_ferias_meio' =>
                $fimFerias
        ];
    }
}


// ==========================================
// GERAR JSON
// ==========================================

$json =
    json_encode(
        $dadosSalvar,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


if (
    $json === false
) {

    http_response_code(
        500
    );


    echo json_encode(
        [
            'sucesso' => false,

            'mensagem' =>
                'Erro ao gerar JSON.'
        ],
        JSON_UNESCAPED_UNICODE
    );


    exit;
}


// ==========================================
// SALVAR CALENDÁRIO
// ==========================================

$resultado =
    file_put_contents(
        $arquivoCalendario,
        $json,
        LOCK_EX
    );


if (
    $resultado === false
) {

    http_response_code(
        500
    );


    echo json_encode(
        [
            'sucesso' => false,

            'mensagem' =>
                'Não foi possível salvar o calendário.'
        ],
        JSON_UNESCAPED_UNICODE
    );


    exit;
}


// ==========================================
// PROCESSAR PONTOS DE PRESENÇA
// ==========================================

$resultadoPontos = [

    'funcao_encontrada' =>
        false,

    'processado' =>
        false,

    'dias_seguidos' =>
        0,

    'maior_sequencia_mes' =>
        0,

    'ultima_data' =>
        null,

    'mes_referencia' =>
        null,

    'recompensa_concedida' =>
        false,

    'marco' =>
        null,

    'estrelas' =>
        0
];


try {

    $resultadoPontos[
        'funcao_encontrada'
    ] =
        function_exists(
            'processarSequenciaPresencaCalendario'
        );


    if (
        $resultadoPontos[
            'funcao_encontrada'
        ]
    ) {

        $processamento =
            processarSequenciaPresencaCalendario(
                $codigoUsuario,
                $dadosSalvar
            );


        if (
            is_array(
                $processamento
            )
        ) {

            $resultadoPontos =
                array_merge(
                    $resultadoPontos,
                    $processamento
                );


            $resultadoPontos[
                'funcao_encontrada'
            ] =
                true;
        }
    }

} catch (
    Throwable $erro
) {

    $resultadoPontos[
        'erro'
    ] =
        $erro->getMessage();


    error_log(
        'Erro nos pontos do calendário: ' .
        $erro->getMessage()
    );
}


// ==========================================
// RESPOSTA
// ==========================================

echo json_encode(
    [
        'sucesso' =>
            true,

        'mensagem' =>
            'Calendário salvo com sucesso.',

        'pontos' =>
            $resultadoPontos
    ],
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);