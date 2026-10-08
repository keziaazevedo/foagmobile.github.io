<?php

// ==========================================
// ESTRUTURA PADRÃO DOS PONTOS
// ==========================================

function estruturaPadraoPontos()
{
    return [
        'estrelas' => 0,

        'historico' => [],

        'controle' => [

            'pomodoro' => [
                'bonus_diarios' => []
            ],

            'flashcards' => [
                'ultimo_dia' => null,
                'estrelas_dia' => 0
            ],

            'frequencia' => [

                'meses_processados' => [],

                'sequencia_presenca' => [
                    'dias' => 0,
                    'ultima_data' => null,
                    'marcos_recebidos' => []
                ]
            ],

            'notas' => [
                'recompensas' => []
            ],

            'sequencia_estudo' => [
                'dias' => 0,
                'ultima_data_estudo' => null,
                'marcos_recebidos' => []
            ],

            'recompensas_recebidas' => []
        ]
    ];
}


// ==========================================
// CAMINHO DO PONTOS.JSON
// ==========================================

function caminhoArquivoPontos(
    $codigoUsuario
) {

    return
        __DIR__ .
        '/../json/usuarios/' .
        $codigoUsuario .
        '/pontos.json';
}


// ==========================================
// CARREGAR PONTOS
// ==========================================

function carregarPontos(
    $codigoUsuario
) {

    $estrutura =
        estruturaPadraoPontos();


    $arquivo =
        caminhoArquivoPontos(
            $codigoUsuario
        );


    if (
        !file_exists(
            $arquivo
        )
    ) {

        return $estrutura;
    }


    $conteudo =
        file_get_contents(
            $arquivo
        );


    if (
        $conteudo === false
    ) {

        return $estrutura;
    }


    $dados =
        json_decode(
            $conteudo,
            true
        );


    if (
        !is_array(
            $dados
        )
    ) {

        return $estrutura;
    }


    return array_replace_recursive(
        $estrutura,
        $dados
    );
}


// ==========================================
// SALVAR PONTOS
// ==========================================

function salvarPontos(
    $codigoUsuario,
    $dados
) {

    $arquivo =
        caminhoArquivoPontos(
            $codigoUsuario
        );


    $pasta =
        dirname(
            $arquivo
        );


    if (
        !is_dir(
            $pasta
        )
    ) {

        mkdir(
            $pasta,
            0777,
            true
        );
    }


    $json =
        json_encode(
            $dados,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );


    if (
        $json === false
    ) {

        return false;
    }


    return (
        file_put_contents(
            $arquivo,
            $json,
            LOCK_EX
        ) !== false
    );
}


// ==========================================
// ADICIONAR ESTRELAS
// ==========================================

function adicionarEstrelas(
    $codigoUsuario,
    $tipo,
    $descricao,
    $quantidade,
    $chave
) {

    $quantidade =
        (int)$quantidade;


    if (
        $quantidade <= 0 ||
        empty(
            $chave
        )
    ) {

        return false;
    }


    $pontos =
        carregarPontos(
            $codigoUsuario
        );


    // ======================================
    // GARANTIR ESTRUTURAS
    // ======================================

    if (
        !isset(
            $pontos[
                'historico'
            ]
        ) ||
        !is_array(
            $pontos[
                'historico'
            ]
        )
    ) {

        $pontos[
            'historico'
        ] = [];
    }


    if (
        !isset(
            $pontos[
                'controle'
            ][
                'recompensas_recebidas'
            ]
        ) ||
        !is_array(
            $pontos[
                'controle'
            ][
                'recompensas_recebidas'
            ]
        )
    ) {

        $pontos[
            'controle'
        ][
            'recompensas_recebidas'
        ] = [];
    }


    // ======================================
    // VERIFICAR DUPLICAÇÃO
    // ======================================

    $recebidas =
        $pontos[
            'controle'
        ][
            'recompensas_recebidas'
        ];


    if (
        in_array(
            $chave,
            $recebidas,
            true
        )
    ) {

        return false;
    }


    // ======================================
    // SOMAR ESTRELAS
    // ======================================

    $pontos[
        'estrelas'
    ] =
        (int)(
            $pontos[
                'estrelas'
            ] ?? 0
        );


    $pontos[
        'estrelas'
    ] +=
        $quantidade;


    // ======================================
    // HORÁRIO
    // ======================================

    $timezone =
        new DateTimeZone(
            'America/Cuiaba'
        );


    $agora =
        new DateTimeImmutable(
            'now',
            $timezone
        );


    // ======================================
    // HISTÓRICO
    // ======================================

    $pontos[
        'historico'
    ][] = [

        'id' =>
            'estrela_' .
            bin2hex(
                random_bytes(
                    6
                )
            ),

        'tipo' =>
            $tipo,

        'descricao' =>
            $descricao,

        'estrelas' =>
            $quantidade,

        'data' =>
            $agora->format(
                'Y-m-d H:i:s'
            ),

        'timestamp' =>
            $agora->getTimestamp(),

        'chave' =>
            $chave
    ];


    // ======================================
    // REGISTRAR RECOMPENSA
    // ======================================

    $pontos[
        'controle'
    ][
        'recompensas_recebidas'
    ][] =
        $chave;


    return salvarPontos(
        $codigoUsuario,
        $pontos
    );
}


// ==========================================
// IDENTIFICAR SESSÃO POMODORO
// ==========================================

function identificarSessaoPomodoro(
    $sessao,
    $indice = 0
) {

    if (
        !empty(
            $sessao[
                'id'
            ]
        )
    ) {

        return
            (string)
            $sessao[
                'id'
            ];
    }


    $dados = [

        $sessao[
            'ts'
        ] ?? '',

        $sessao[
            'minutes'
        ] ?? '',

        $sessao[
            'mode'
        ] ?? '',

        $sessao[
            'source'
        ] ?? '',

        $sessao[
            'discipline'
        ] ?? '',

        $indice
    ];


    return hash(
        'sha256',
        implode(
            '|',
            $dados
        )
    );
}


// ==========================================
// CONTAR SESSÕES PREMIADAS NO DIA
// ==========================================

function contarSessoesPremiadasNoDia(
    $codigoUsuario,
    $data
) {

    $pontos =
        carregarPontos(
            $codigoUsuario
        );


    $historico =
        $pontos[
            'historico'
        ] ?? [];


    $total =
        0;


    foreach (
        $historico
        as $item
    ) {

        if (
            !is_array(
                $item
            )
        ) {

            continue;
        }


        $tipo =
            $item[
                'tipo'
            ] ?? '';


        $dataHistorico =
            $item[
                'data'
            ] ?? '';


        $chave =
            $item[
                'chave'
            ] ?? '';


        if (
            $tipo !==
                'pomodoro' &&
            $tipo !==
                'cronometro'
        ) {

            continue;
        }


        if (
            substr(
                $dataHistorico,
                0,
                10
            ) !==
            $data
        ) {

            continue;
        }


        $ehSessaoPomodoro =
            strpos(
                $chave,
                'pomodoro_'
            ) === 0;


        $ehSessaoCronometro =
            strpos(
                $chave,
                'cronometro_'
            ) === 0;


        if (
            $ehSessaoPomodoro ||
            $ehSessaoCronometro
        ) {

            $total++;
        }
    }


    return $total;
}


// ==========================================
// PROCESSAR ESTRELAS DO POMODORO
// ==========================================

function processarEstrelasPomodoro(
    $codigoUsuario,
    $dadosAnteriores,
    $dadosNovos
) {

    $resultado = [

        'estrelas_sessoes' =>
            0,

        'estrelas_bonus' =>
            0,

        'limite_diario_atingido' =>
            false,

        'sessoes_premiadas_hoje' =>
            0
    ];


    $sessoesAntigas =
        $dadosAnteriores[
            'sessions'
        ] ?? [];


    $sessoesNovas =
        $dadosNovos[
            'sessions'
        ] ?? [];


    if (
        !is_array(
            $sessoesAntigas
        ) ||
        !is_array(
            $sessoesNovas
        )
    ) {

        return $resultado;
    }


    $limiteDiario =
        3;


    $timezone =
        new DateTimeZone(
            'America/Cuiaba'
        );


    $agora =
        new DateTimeImmutable(
            'now',
            $timezone
        );


    $hoje =
        $agora->format(
            'Y-m-d'
        );


    $premiadasHoje =
        contarSessoesPremiadasNoDia(
            $codigoUsuario,
            $hoje
        );


    // ======================================
    // IDENTIFICAR SESSÕES ANTIGAS
    // ======================================

    $idsAntigos =
        [];


    foreach (
        $sessoesAntigas
        as $indice => $sessao
    ) {

        if (
            !is_array(
                $sessao
            )
        ) {

            continue;
        }


        $id =
            identificarSessaoPomodoro(
                $sessao,
                $indice
            );


        $idsAntigos[
            $id
        ] =
            true;
    }


    // ======================================
    // VERIFICAR NOVAS SESSÕES
    // ======================================

    foreach (
        $sessoesNovas
        as $indice => $sessao
    ) {

        if (
            !is_array(
                $sessao
            )
        ) {

            continue;
        }


        $idSessao =
            identificarSessaoPomodoro(
                $sessao,
                $indice
            );


        if (
            isset(
                $idsAntigos[
                    $idSessao
                ]
            )
        ) {

            continue;
        }


        $modo =
            $sessao[
                'mode'
            ] ?? '';


        $origem =
            $sessao[
                'source'
            ] ?? '';


        $minutos =
            (int)(
                $sessao[
                    'minutes'
                ] ?? 0
            );


        $sessaoValida =
            false;


        $tipo =
            '';


        $descricao =
            '';


        $chave =
            '';


        // ==================================
        // POMODORO
        // ==================================

        if (
            $origem ===
                'pomodoro' &&
            $modo ===
                'focus'
        ) {

            $sessaoValida =
                true;


            $tipo =
                'pomodoro';


            $descricao =
                'Sessão de Pomodoro concluída';


            $chave =
                'pomodoro_' .
                $idSessao;
        }


        // ==================================
        // CRONÔMETRO
        // ==================================

        elseif (
            $origem ===
                'cronometro' &&
            $modo ===
                'focus' &&
            $minutos >=
                30
        ) {

            $sessaoValida =
                true;


            $tipo =
                'cronometro';


            $descricao =
                'Sessão de 30 minutos ou mais no cronômetro';


            $chave =
                'cronometro_' .
                $idSessao;
        }


        if (
            !$sessaoValida
        ) {

            continue;
        }


        if (
            $premiadasHoje >=
            $limiteDiario
        ) {

            $resultado[
                'limite_diario_atingido'
            ] =
                true;


            continue;
        }


        $adicionou =
            adicionarEstrelas(
                $codigoUsuario,
                $tipo,
                $descricao,
                2,
                $chave
            );


        if (
            $adicionou
        ) {

            $premiadasHoje++;


            $resultado[
                'estrelas_sessoes'
            ] +=
                2;
        }
    }


    // ======================================
    // BÔNUS DE TEMPO
    // ======================================

    $bonus =
        processarBonusTempoDia(
            $codigoUsuario,
            $sessoesNovas
        );


    $resultado[
        'estrelas_bonus'
    ] =
        $bonus;


    $resultado[
        'sessoes_premiadas_hoje'
    ] =
        $premiadasHoje;


    return $resultado;
}


// ==========================================
// PROCESSAR BÔNUS DO DIA
// ==========================================

function processarBonusTempoDia(
    $codigoUsuario,
    $sessoes
) {

    $timezone =
        new DateTimeZone(
            'America/Cuiaba'
        );


    $agora =
        new DateTimeImmutable(
            'now',
            $timezone
        );


    $hoje =
        $agora->format(
            'Y-m-d'
        );


    $inicioHoje =
        strtotime(
            $hoje .
            ' 00:00:00'
        ) * 1000;


    $fimHoje =
        strtotime(
            $hoje .
            ' 23:59:59'
        ) * 1000;


    $totalMinutos =
        0;


    $estrelasBonus =
        0;


    foreach (
        $sessoes
        as $sessao
    ) {

        if (
            !is_array(
                $sessao
            )
        ) {

            continue;
        }


        $timestamp =
            (int)(
                $sessao[
                    'ts'
                ] ?? 0
            );


        if (
            $timestamp <
                $inicioHoje ||
            $timestamp >
                $fimHoje
        ) {

            continue;
        }


        $modo =
            $sessao[
                'mode'
            ] ?? '';


        $origem =
            $sessao[
                'source'
            ] ?? '';


        $minutos =
            (int)(
                $sessao[
                    'minutes'
                ] ?? 0
            );


        if (
            $modo !==
            'focus'
        ) {

            continue;
        }


        if (
            $origem ===
            'pomodoro'
        ) {

            $totalMinutos +=
                $minutos;
        }


        elseif (
            $origem ===
                'cronometro' &&
            $minutos >=
                30
        ) {

            $totalMinutos +=
                $minutos;
        }
    }


    // ======================================
    // BÔNUS 2 HORAS
    // ======================================

    if (
        $totalMinutos >=
        120
    ) {

        $adicionou =
            adicionarEstrelas(
                $codigoUsuario,
                'pomodoro',
                '2 horas estudadas no dia',
                3,
                'bonus_estudo_2h_' .
                $hoje
            );


        if (
            $adicionou
        ) {

            $estrelasBonus +=
                3;
        }
    }


    // ======================================
    // BÔNUS 4 HORAS
    // ======================================

    if (
        $totalMinutos >=
        240
    ) {

        $adicionou =
            adicionarEstrelas(
                $codigoUsuario,
                'pomodoro',
                '4 horas estudadas no dia',
                5,
                'bonus_estudo_4h_' .
                $hoje
            );


        if (
            $adicionou
        ) {

            $estrelasBonus +=
                5;
        }
    }


    return $estrelasBonus;
}


// =====================================================
// CALENDÁRIO — VALIDAR DATA
// =====================================================

function dataPresencaValida(
    $data
) {

    if (
        !is_string(
            $data
        ) ||
        !preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $data
        )
    ) {

        return false;
    }


    $objeto =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $data
        );


    return (
        $objeto &&
        $objeto->format(
            'Y-m-d'
        ) ===
        $data
    );
}


// =====================================================
// CALENDÁRIO — CARREGAR FERIADOS
// =====================================================

function carregarFeriadosParaPontos()
{
    $arquivo =
        __DIR__ .
        '/../json/feriados.json';


    if (
        !file_exists(
            $arquivo
        )
    ) {

        return [];
    }


    $conteudo =
        file_get_contents(
            $arquivo
        );


    if (
        $conteudo === false
    ) {

        return [];
    }


    $dados =
        json_decode(
            $conteudo,
            true
        );


    return is_array(
        $dados
    )
        ? $dados
        : [];
}


// =====================================================
// CALENDÁRIO — PROCESSAR SEQUÊNCIA DE PRESENÇA
// =====================================================

function processarSequenciaPresencaCalendario(
    $codigoUsuario,
    $calendario
) {

    $resultado = [

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


    if (
        !is_array(
            $calendario
        )
    ) {

        return $resultado;
    }


    // ======================================
    // DATA ATUAL
    // ======================================

    $timezone =
        new DateTimeZone(
            'America/Cuiaba'
        );


    $agora =
        new DateTimeImmutable(
            'now',
            $timezone
        );


    $hoje =
        $agora->format(
            'Y-m-d'
        );


    $anoAtual =
        $agora->format(
            'Y'
        );


    $mesAtual =
        $agora->format(
            'Y-m'
        );


    $resultado[
        'mes_referencia'
    ] =
        $mesAtual;


    // ======================================
    // CONFIGURAÇÃO DO ANO
    // ======================================

    $config =
        $calendario[
            'configuracoes'
        ][
            $anoAtual
        ] ?? null;


    if (
        !is_array(
            $config
        )
    ) {

        return $resultado;
    }


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
        !dataPresencaValida(
            $inicioAno
        ) ||
        !dataPresencaValida(
            $fimAno
        )
    ) {

        return $resultado;
    }


    if (
        $inicioAno >
        $fimAno
    ) {

        return $resultado;
    }


    if (
        $hoje <
        $inicioAno
    ) {

        return $resultado;
    }


    // ======================================
    // DATA FINAL PARA CÁLCULO
    // ======================================

    $limiteFinal =
        $hoje;


    if (
        $fimAno <
        $limiteFinal
    ) {

        $limiteFinal =
            $fimAno;
    }


    // ======================================
    // STATUS DOS DIAS
    // ======================================

    $diasMarcados =
        $calendario[
            'dias'
        ] ?? [];


    if (
        !is_array(
            $diasMarcados
        )
    ) {

        $diasMarcados =
            [];
    }


    // ======================================
    // FERIADOS
    // ======================================

    $feriados =
        carregarFeriadosParaPontos();


    // ======================================
    // CONTADORES
    // ======================================

    $sequenciaAtual =
        0;


    $maiorSequenciaMes =
        0;


    $ultimaDataLetiva =
        null;


    // ======================================
    // PERCORRER PERÍODO LETIVO
    // ======================================

    $dataAtual =
        new DateTimeImmutable(
            $inicioAno,
            $timezone
        );


    $dataFinal =
        new DateTimeImmutable(
            $limiteFinal,
            $timezone
        );


    while (
        $dataAtual <=
        $dataFinal
    ) {

        $iso =
            $dataAtual->format(
                'Y-m-d'
            );


        $mesData =
            $dataAtual->format(
                'Y-m'
            );


        // 1 segunda ... 7 domingo
        $diaSemana =
            (int)$dataAtual->format(
                'N'
            );


        $ehFimDeSemana =
            $diaSemana >=
            6;


        $ehFeriado =
            array_key_exists(
                $iso,
                $feriados
            );


        $ehFerias =
            false;


        if (
            dataPresencaValida(
                $inicioFerias
            ) &&
            dataPresencaValida(
                $fimFerias
            )
        ) {

            $ehFerias =
                (
                    $iso >=
                        $inicioFerias &&
                    $iso <=
                        $fimFerias
                );
        }


        $status =
            $diasMarcados[
                $iso
            ] ?? '';


        $ehSemAula =
            $status ===
            'sem-aula';


        // ==================================
        // DIAS NEUTROS
        // ==================================
        //
        // Não contam e não quebram sequência.
        //
        // ==================================

        if (
            $ehFimDeSemana ||
            $ehFeriado ||
            $ehFerias ||
            $ehSemAula
        ) {

            $dataAtual =
                $dataAtual->modify(
                    '+1 day'
                );

            continue;
        }


        $ultimaDataLetiva =
            $iso;


        // ==================================
        // FALTA / ATESTADO
        // ==================================

        if (
            $status ===
                'vermelho' ||
            $status ===
                'amarelo'
        ) {

            $sequenciaAtual =
                0;


            $dataAtual =
                $dataAtual->modify(
                    '+1 day'
                );


            continue;
        }


        // ==================================
        // PRESENÇA
        // ==================================
        //
        // Qualquer dia letivo sem falta ou
        // atestado é presença automática.
        //
        // ==================================

        $sequenciaAtual++;


        // ==================================
        // MAIOR SEQUÊNCIA VISTA NESTE MÊS
        // ==================================

        if (
            $mesData ===
            $mesAtual
        ) {

            $maiorSequenciaMes =
                max(
                    $maiorSequenciaMes,
                    $sequenciaAtual
                );
        }


        $dataAtual =
            $dataAtual->modify(
                '+1 day'
            );
    }


    // ======================================
    // RESULTADO DO CÁLCULO
    // ======================================

    $resultado[
        'processado'
    ] =
        true;


    $resultado[
        'dias_seguidos'
    ] =
        $sequenciaAtual;


    $resultado[
        'maior_sequencia_mes'
    ] =
        $maiorSequenciaMes;


    $resultado[
        'ultima_data'
    ] =
        $ultimaDataLetiva;


    // ======================================
    // CARREGAR PONTOS
    // ======================================

    $pontos =
        carregarPontos(
            $codigoUsuario
        );


    // ======================================
    // GARANTIR ESTRUTURA FREQUÊNCIA
    // ======================================

    if (
        !isset(
            $pontos[
                'controle'
            ][
                'frequencia'
            ]
        ) ||
        !is_array(
            $pontos[
                'controle'
            ][
                'frequencia'
            ]
        )
    ) {

        $pontos[
            'controle'
        ][
            'frequencia'
        ] = [

            'meses_processados' =>
                [],

            'sequencia_presenca' => [

                'dias' =>
                    0,

                'ultima_data' =>
                    null,

                'marcos_recebidos' =>
                    []
            ]
        ];
    }


    if (
        !isset(
            $pontos[
                'controle'
            ][
                'frequencia'
            ][
                'sequencia_presenca'
            ]
        ) ||
        !is_array(
            $pontos[
                'controle'
            ][
                'frequencia'
            ][
                'sequencia_presenca'
            ]
        )
    ) {

        $pontos[
            'controle'
        ][
            'frequencia'
        ][
            'sequencia_presenca'
        ] = [

            'dias' =>
                0,

            'ultima_data' =>
                null,

            'marcos_recebidos' =>
                []
        ];
    }


    $controle =&
        $pontos[
            'controle'
        ][
            'frequencia'
        ][
            'sequencia_presenca'
        ];


    // ======================================
    // ATUALIZAR CONTAGEM
    // ======================================

    $controle[
        'dias'
    ] =
        $sequenciaAtual;


    $controle[
        'ultima_data'
    ] =
        $ultimaDataLetiva;


    if (
        !isset(
            $controle[
                'marcos_recebidos'
            ]
        ) ||
        !is_array(
            $controle[
                'marcos_recebidos'
            ]
        )
    ) {

        $controle[
            'marcos_recebidos'
        ] =
            [];
    }


    // ======================================
    // SALVAR CONTAGEM MESMO SEM RECOMPENSA
    // ======================================

    salvarPontos(
        $codigoUsuario,
        $pontos
    );


    // ======================================
    // JÁ RECEBEU RECOMPENSA NESTE MÊS?
    // ======================================

    if (
        array_key_exists(
            $mesAtual,
            $controle[
                'marcos_recebidos'
            ]
        )
    ) {

        return $resultado;
    }


    // ======================================
    // DEFINIR RECOMPENSA
    // ======================================

    $marcoAtingido =
        null;


    $quantidadeEstrelas =
        0;


    /*
     * Apenas UMA recompensa de sequência
     * pode ser recebida por mês.
     *
     * Se atingir mais de um marco antes
     * de receber, ganha o maior alcançado.
     */

    if (
        $maiorSequenciaMes >=
        25
    ) {

        $marcoAtingido =
            25;


        $quantidadeEstrelas =
            40;

    } elseif (
        $maiorSequenciaMes >=
        10
    ) {

        $marcoAtingido =
            10;


        $quantidadeEstrelas =
            12;

    } elseif (
        $maiorSequenciaMes >=
        5
    ) {

        $marcoAtingido =
            5;


        $quantidadeEstrelas =
            7;
    }


    // ======================================
    // NÃO ATINGIU MARCO
    // ======================================

    if (
        $marcoAtingido ===
        null
    ) {

        return $resultado;
    }


    // ======================================
    // CHAVE ÚNICA DO MÊS
    // ======================================

    $chave =
        'sequencia_presenca_' .
        $mesAtual;


    // ======================================
    // ADICIONAR ESTRELAS
    // ======================================

    $adicionou =
        adicionarEstrelas(
            $codigoUsuario,
            'sequencia_presenca',
            $marcoAtingido .
            ' dias seguidos de presença',
            $quantidadeEstrelas,
            $chave
        );


    // ======================================
    // RECARREGAR PONTOS
    // ======================================

    $pontos =
        carregarPontos(
            $codigoUsuario
        );


    if (
        !isset(
            $pontos[
                'controle'
            ][
                'frequencia'
            ][
                'sequencia_presenca'
            ][
                'marcos_recebidos'
            ]
        ) ||
        !is_array(
            $pontos[
                'controle'
            ][
                'frequencia'
            ][
                'sequencia_presenca'
            ][
                'marcos_recebidos'
            ]
        )
    ) {

        $pontos[
            'controle'
        ][
            'frequencia'
        ][
            'sequencia_presenca'
        ][
            'marcos_recebidos'
        ] =
            [];
    }


    // ======================================
    // MARCAR MÊS COMO PREMIADO
    // ======================================

    $pontos[
        'controle'
    ][
        'frequencia'
    ][
        'sequencia_presenca'
    ][
        'marcos_recebidos'
    ][
        $mesAtual
    ] =
        $marcoAtingido;


    $pontos[
        'controle'
    ][
        'frequencia'
    ][
        'sequencia_presenca'
    ][
        'dias'
    ] =
        $sequenciaAtual;


    $pontos[
        'controle'
    ][
        'frequencia'
    ][
        'sequencia_presenca'
    ][
        'ultima_data'
    ] =
        $ultimaDataLetiva;


    salvarPontos(
        $codigoUsuario,
        $pontos
    );


    // ======================================
    // RESULTADO
    // ======================================

    if (
        $adicionou
    ) {

        $resultado[
            'recompensa_concedida'
        ] =
            true;


        $resultado[
            'marco'
        ] =
            $marcoAtingido;


        $resultado[
            'estrelas'
        ] =
            $quantidadeEstrelas;
    }


    return $resultado;
}