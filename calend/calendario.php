<?php
require_once __DIR__ . '/../core/usuario.php';
require_once __DIR__ . '/../core/json.php';

$contextoUsuario = foag_contexto_usuario(
    '../login/index.php',
    false,
    true
);

$codigoUsuario = $contextoUsuario['codigoUsuario'];
$pastaUsuario = $contextoUsuario['pastaUsuario'];

$current = basename($_SERVER['PHP_SELF']);

$anoSelecionado =
    isset($_GET['ano'])
        ? (int)$_GET['ano']
        : (int)date('Y');

if (
    $anoSelecionado < 2000 ||
    $anoSelecionado > 2100
) {
    $anoSelecionado =
        (int)date('Y');
}

if (!is_dir($pastaUsuario)) {
    exit('Pasta do usuário não encontrada.');
}

// ======================================
// FUNÇÃO AUXILIAR JSON
// ======================================

function carregarJsonCalendario(
    $arquivo,
    $padrao
) {
    if (!file_exists($arquivo)) {
        file_put_contents(
            $arquivo,
            json_encode(
                $padrao,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ),
            LOCK_EX
        );

        return $padrao;
    }

    $conteudo =
        file_get_contents($arquivo);

    $dados =
        json_decode(
            $conteudo ?: '',
            true
        );

    return is_array($dados)
        ? $dados
        : $padrao;
}

// ======================================
// FERIADOS
// ======================================

$arquivoFeriados =
    __DIR__ .
    '/../json/feriados.json';

$feriados = [];

if (file_exists($arquivoFeriados)) {
    $dadosFeriados =
        json_decode(
            file_get_contents(
                $arquivoFeriados
            ) ?: '',
            true
        );

    if (is_array($dadosFeriados)) {
        $feriados =
            $dadosFeriados;
    }
}

// ======================================
// AGENDA DO USUÁRIO
// ======================================

$arquivoAgenda =
    $pastaUsuario .
    '/agenda.json';

$agendaData =
    carregarJsonCalendario(
        $arquivoAgenda,
        [
            'notas' => [],
            'tarefas' => [],
            'nao_esquecer' => []
        ]
    );

foreach (
    ['notas', 'tarefas', 'nao_esquecer']
    as $chave
) {
    if (
        !isset($agendaData[$chave]) ||
        !is_array($agendaData[$chave])
    ) {
        $agendaData[$chave] = [];
    }
}

// ======================================
// HORÁRIO DO USUÁRIO
// ======================================

$arquivoHorario =
    $pastaUsuario . '/horario.json';

$horarioData = [
    'html' => ''
];

if (file_exists($arquivoHorario)) {

    $conteudoHorario =
        file_get_contents(
            $arquivoHorario
        );

    $horarioJson =
        json_decode(
            $conteudoHorario ?: '',
            true
        );

    if (is_array($horarioJson)) {

        $horarioData =
            $horarioJson;
    }
}

if (
    !isset($horarioData['html']) ||
    !is_string($horarioData['html'])
) {

    $horarioData['html'] = '';
}

// ======================================
// CALENDÁRIO DO USUÁRIO
// ======================================

$arquivoCalend =
    $pastaUsuario .
    '/calendario.json';

$calendData =
    carregarJsonCalendario(
        $arquivoCalend,
        [
            'dias' => [],
            'metas' => [],
            'configuracoes' => []
        ]
    );

foreach (
    ['dias', 'metas', 'configuracoes']
    as $chave
) {
    if (
        !isset($calendData[$chave]) ||
        !is_array($calendData[$chave])
    ) {
        $calendData[$chave] = [];
    }
}

// ======================================
// FUNÇÕES DO CALENDÁRIO
// ======================================

function obterDiasDoMes(
    $mes,
    $ano
) {
    $meses = [
        'Janeiro' => 1,
        'Fevereiro' => 2,
        'Março' => 3,
        'Abril' => 4,
        'Maio' => 5,
        'Junho' => 6,
        'Julho' => 7,
        'Agosto' => 8,
        'Setembro' => 9,
        'Outubro' => 10,
        'Novembro' => 11,
        'Dezembro' => 12
    ];

    $numeroMes =
        $meses[$mes];

    $diasNoMes =
        cal_days_in_month(
            CAL_GREGORIAN,
            $numeroMes,
            $ano
        );

    $primeiroDiaSemana =
        (int)date(
            'w',
            strtotime(
                sprintf(
                    '%04d-%02d-01',
                    $ano,
                    $numeroMes
                )
            )
        );

    $dias = [];

    for (
        $i = 0;
        $i < $primeiroDiaSemana;
        $i++
    ) {
        $dias[] = '';
    }

    for (
        $i = 1;
        $i <= $diasNoMes;
        $i++
    ) {
        $dias[] = $i;
    }

    return [
        $dias,
        $numeroMes
    ];
}

function gerarCalendario(
    $ano
) {
    global $feriados;

    $meses = [
        'Janeiro',
        'Fevereiro',
        'Março',
        'Abril',
        'Maio',
        'Junho',
        'Julho',
        'Agosto',
        'Setembro',
        'Outubro',
        'Novembro',
        'Dezembro'
    ];

    $diasSemana = [
        'Dom',
        'Seg',
        'Ter',
        'Qua',
        'Qui',
        'Sex',
        'Sáb'
    ];

    foreach ($meses as $mes) {
        [
            $dias,
            $numeroMes
        ] =
            obterDiasDoMes(
                $mes,
                $ano
            );

        echo "<div class='mes' data-ano='{$ano}' data-mes='{$numeroMes}'>";
        echo "  <div class='calendario-mes'>";
        echo "    <div class='header-mes'>{$mes}</div>";
        echo "    <div class='dias'>";

        foreach (
            $diasSemana as $diaSemana
        ) {
            echo "<div class='dia header-dia'><strong>{$diaSemana}</strong></div>";
        }

        foreach ($dias as $dia) {
            if (!$dia) {
                echo "<div class='dia'></div>";
                continue;
            }

            $dataAtual =
                sprintf(
                    '%04d-%02d-%02d',
                    $ano,
                    $numeroMes,
                    $dia
                );

            $classeExtra = '';
            $attrExtra = '';

            if (
                isset(
                    $feriados[$dataAtual]
                )
            ) {
                $classeExtra =
                    'feriado';

                $nomeFeriado =
                    htmlspecialchars(
                        $feriados[$dataAtual],
                        ENT_QUOTES,
                        'UTF-8'
                    );

                $attrExtra =
                    " data-feriado=\"{$nomeFeriado}\"";
            }

            echo "
                <div
                    class='dia {$classeExtra}'
                    {$attrExtra}
                    data-date='{$dataAtual}'
                >
                    <span class='num-dia'>{$dia}</span>
                    <div class='dots'></div>
                </div>
            ";
        }

        echo "    </div>";
        echo "  </div>";

        echo "  <div class='info-mes'>";

        echo "    <div class='toolbar-cal'>";
        echo "      <div class='lado-a'>";
        echo "        <span class='toolbar-label'>Ano</span>";
        echo "        <select class='anoSelect'></select>";
        echo "      </div>";
        echo "      <div class='lado-b'>";
        echo "        <button class='btn-exportar-png' title='Exportar PNG'><i class='fa-regular fa-image'></i><span>PNG</span></button>";
        echo "        <button class='btn-imprimir' title='Imprimir mês'><i class='fa-solid fa-print'></i><span>Imprimir</span></button>";
        echo "      </div>";
        echo "    </div>";

        echo "    <div class='modal-mes-grid'>";

        echo "      <section class='modal-mes-card'>";
        echo "        <div class='modal-mes-card-header'>";
        echo "          <div><span>Registro</span><h3>Marcar no calendário</h3></div>";
        echo "          <i class='fa-solid fa-calendar-check'></i>";
        echo "        </div>";

        echo "        <div class='bloco-cores'>";
        echo "          <p class='texto-cores'>Escolha uma opção e depois clique no dia desejado.</p>";
        echo "          <div class='botoes-cores'>";
        echo "            <div class='cor-item'><button class='btn-cor' data-cor='vermelho' style='background:#e74c3c'></button><span>Faltou</span></div>";
        echo "            <div class='cor-item'><button class='btn-cor' data-cor='amarelo' style='background:#f1c40f'></button><span>Atestado</span></div>";
        echo "            <div class='cor-item'><button class='btn-cor' data-cor='sem-aula' style='background:#f39c12'></button><span>Sem aula</span></div>";
        echo "            <div class='cor-item'><button class='btn-cor' data-cor='roxo' style='background:#8e44ad'></button><span>Prova</span></div>";
        echo "            <div class='cor-item'><button class='btn-cor limpar' data-cor='limpar' style='background:#bdc3c7'></button><span>Limpar</span></div>";
        echo "          </div>";
        echo "        </div>";
        echo "      </section>";

        echo "      <section class='modal-mes-card'>";
        echo "        <div class='modal-mes-card-header'>";
        echo "          <div><span>Resumo</span><h3>Frequência do mês</h3></div>";
        echo "          <i class='fa-solid fa-chart-simple'></i>";
        echo "        </div>";

        echo "        <div class='painel-metas'>";
        echo "          <div class='linha linha-meta'>";
        echo "            <label>Frequência mínima exigida</label>";
        echo "            <div class='meta-input-wrap'><input class='meta-presenca' type='number' min='0' max='100' value='80'><span>%</span></div>";
        echo "          </div>";
        echo "          <div class='linha linha-progress'>";
        echo "            <div class='progress-wrap'><div class='progress-bar'></div></div>";
        echo "            <span class='label-presenca'>0%</span>";
        echo "          </div>";
        echo "          <p class='meta-status-mes'></p>";
        echo "          <p class='faltas-restantes-mes'></p>";
        echo "          <div class='resumos'>";
        echo "            <span><b>Presenças</b><em class='count-presenca'>0</em></span>";
        echo "            <span><b>Faltas</b><em class='count-falta'>0</em></span>";
        echo "            <span><b>Atestados</b><em class='count-atestado'>0</em></span>";
        echo "            <span><b>Sem aula</b><em class='count-semaula'>0</em></span>";
        echo "            <span><b>Provas</b><em class='count-prova'>0</em></span>";
        echo "          </div>";
        echo "        </div>";
        echo "      </section>";

        echo "    </div>";

        echo "    <div class='mini-agenda'>";
        echo "      <div class='agenda-header'>";
        echo "        <strong class='agenda-data'></strong>";
        echo "        <button class='agenda-fechar'>×</button>";
        echo "      </div>";

        echo "      <div class='agenda-opcoes'>";
        echo "        <button class='btn-ver-tarefas'>Ver tarefas do dia</button>";
        echo "        <button class='btn-nova-tarefa'>Agendar nova tarefa</button>";
        echo "        <button class='btn-ver-horarios'>Ver horários</button>";
        echo "      </div>";

        echo "      <div class='agenda-resumo'></div>";

        echo "      <div class='agenda-editor'>";
        echo "        <textarea class='agenda-notas' placeholder='Anote tarefas, horários, links...'></textarea>";
        echo "        <button class='agenda-salvar'>Salvar</button>";
        echo "      </div>";

        echo "    </div>";
        echo "  </div>";
        echo "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>

      <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário - FOAG</title>

    <link rel="stylesheet" href="calendario.css">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="dark_calendario.css">
    <link rel="stylesheet" href="calendario_dashboard.css">
    <link rel="stylesheet" href="../estrelas/modal_estrelas.css?v=<?= time() ?>">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="../m.escuro/dark-mode.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <!-- ======================================
         ACESSIBILIDADE + APARÊNCIA GLOBAL
    ====================================== -->


    <script>
        window.CAL_AGENDA_DATA = <?= json_encode($agendaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.CAL_AGENDA_SAVE_URL = FOAG_CONFIG.endpoints.agendaSalvar;
        window.CAL_HORARIO_HTML = <?= json_encode($horarioData['html'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.CAL_CALEND_DATA = <?= json_encode($calendData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.CAL_CALEND_SAVE_URL = FOAG_CONFIG.endpoints.calendarioSalvar;
        window.CAL_ANO = <?= (int)$anoSelecionado ?>;
    </script>
    <style>
        /* Mantém o painel anual acima dos meses sem alterar o grid original */
        .calendario-area {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .calendario-area > .frequencia-anual,
        .calendario-area > .calendario-container {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        /* Área da página: conteúdo + footer, sem invadir o menu lateral */
        .page-area {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }

        .conteudo {
            width: 100%;
            flex: 1;
            min-width: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }

        /* Footer no mesmo padrão da Comunidade */
        .footer {
            width: 100%;
            margin-top: 30px;
            padding: 0;
            background: #ffffff;
            border-top: 1px solid #e5edf5;
            color: inherit;
            text-align: initial;
        }

        .footer-content {
            width: 100%;
            max-width: 1180px;
            min-height: 50px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 38px;
        }

        .footer-brand {
            color: #38a5ff;
            font-size: 17px;
            font-weight: 700;
        }

        .footer-links {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .footer-links a {
            color: #667085;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: #38a5ff;
        }

        .footer-copy {
            color: #98a2b3;
            font-size: 10px;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .page-area {
                width: 100%;
            }

            .conteudo {
                padding: 16px 12px;
            }

            .footer-content {
                min-height: auto;
                padding: 16px 18px;
                flex-direction: column;
                justify-content: center;
                gap: 10px;
            }

            .footer-left {
                flex-direction: column;
                gap: 8px;
            }

            .footer-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 16px 20px;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../m.escuro/dark-mode.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        window.CAL_AGENDA_DATA =
            <?= json_encode(
                $agendaData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        window.CAL_AGENDA_SAVE_URL =
            FOAG_CONFIG.endpoints.agendaSalvar;

       window.CAL_HORARIO_HTML =
        <?= json_encode(
            $horarioData['html'] ?? '',
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.CAL_CALEND_DATA =
            <?= json_encode(
                $calendData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        window.CAL_CALEND_SAVE_URL =
            FOAG_CONFIG.endpoints.calendarioSalvar;

        window.CAL_ANO =
            <?= (int)$anoSelecionado ?>;
    </script>
    <link rel="stylesheet" href="../global/css/cursor.css">
    <link rel="stylesheet" href="../global/css/layout.css?v=<?= time() ?>">
</head>

<body>

    <div
        id="cal-backdrop"
        aria-hidden="true"
    ></div>


    <!-- ======================================
         CABEÇALHO
    ======================================= -->

    <?php include __DIR__ . '/../components/header.php'; ?>


    <div class="container">

        <!-- ======================================
             MENU
        ======================================= -->

        <?php include __DIR__ . '/../components/menu.php'; ?>

        <div class="page-area">

            <div class="conteudo">

            <div class="calendario-area">

            <!-- ======================================
                 PAINEL ANUAL DE FREQUÊNCIA
            ======================================= -->

            <section
                class="frequencia-anual"
                id="frequencia-anual"
                data-ano="<?= (int)$anoSelecionado ?>"
            >

                <div class="freq-anual-topo">

                    <div>

                        <span class="freq-eyebrow">
                            Acompanhamento acadêmico
                        </span>

                        <h1>
                            Frequência anual
                            <?= (int)$anoSelecionado ?>
                        </h1>

                        <p>
                            Configure seu período letivo e acompanhe
                            sua presença durante o ano.
                        </p>

                    </div>


                    <div
                        class="freq-risco"
                        id="freq-risco"
                    >
                        Aguardando configuração
                    </div>

                </div>


                <!-- CONFIGURAÇÃO DO ANO -->

                <div class="freq-config-card">

                    <div class="freq-config-header">

                        <div>

                            <h2>
                                <i class="fa-solid fa-calendar-check"></i>
                                Período letivo
                            </h2>

                            <p>
                                As alterações são salvas automaticamente.
                            </p>

                        </div>

                    </div>


                    <div class="freq-config-grid">

                        <label class="freq-field">

                            <span>
                                Frequência mínima exigida
                            </span>

                            <div class="freq-input-suffix">

                                <input
                                    type="number"
                                    id="meta-anual"
                                    min="0"
                                    max="100"
                                    value="80"
                                >

                                <b>%</b>

                            </div>

                        </label>


                        <label class="freq-field">

                            <span>
                                Início do ano letivo
                            </span>

                            <input
                                type="date"
                                id="inicio-ano-letivo"
                                min="<?= (int)$anoSelecionado ?>-01-01"
                                max="<?= (int)$anoSelecionado ?>-12-31"
                            >

                        </label>


                        <label class="freq-field">

                            <span>
                                Final do ano letivo
                            </span>

                            <input
                                type="date"
                                id="fim-ano-letivo"
                                min="<?= (int)$anoSelecionado ?>-01-01"
                                max="<?= (int)$anoSelecionado ?>-12-31"
                            >

                        </label>


                        <label class="freq-field">

                            <span>
                                Início das férias do meio do ano
                            </span>

                            <input
                                type="date"
                                id="inicio-ferias-meio"
                                min="<?= (int)$anoSelecionado ?>-01-01"
                                max="<?= (int)$anoSelecionado ?>-12-31"
                            >

                        </label>


                        <label class="freq-field">

                            <span>
                                Final das férias do meio do ano
                            </span>

                            <input
                                type="date"
                                id="fim-ferias-meio"
                                min="<?= (int)$anoSelecionado ?>-01-01"
                                max="<?= (int)$anoSelecionado ?>-12-31"
                            >

                        </label>

                    </div>


                    <p
                        class="freq-config-erro"
                        id="freq-config-erro"
                        aria-live="polite"
                    ></p>

                </div>


                <!-- RESUMO ANUAL -->

                <div class="freq-resumo-grid">

                    <article class="freq-stat">

                        <div class="freq-stat-icon">
                            <i class="fa-solid fa-percent"></i>
                        </div>

                        <div>

                            <span
                                class="freq-stat-value"
                                id="freq-anual-percentual"
                            >
                                0%
                            </span>

                            <span class="freq-stat-label">
                                Presença geral
                            </span>

                        </div>

                    </article>


                    <article class="freq-stat">

                        <div class="freq-stat-icon danger">
                            <i class="fa-solid fa-user-xmark"></i>
                        </div>

                        <div>

                            <span
                                class="freq-stat-value"
                                id="freq-anual-faltas"
                            >
                                0
                            </span>

                            <span class="freq-stat-label">
                                Total de faltas
                            </span>

                        </div>

                    </article>


                    <article class="freq-stat">

                        <div class="freq-stat-icon warning">
                            <i class="fa-solid fa-file-medical"></i>
                        </div>

                        <div>

                            <span
                                class="freq-stat-value"
                                id="freq-anual-atestados"
                            >
                                0
                            </span>

                            <span class="freq-stat-label">
                                Atestados
                            </span>

                        </div>

                    </article>


                    <article class="freq-stat">

                        <div class="freq-stat-icon success">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                        </div>

                        <div>

                            <span
                                class="freq-stat-value freq-stat-text"
                                id="freq-melhor-mes"
                            >
                                —
                            </span>

                            <span class="freq-stat-label">
                                Melhor mês
                            </span>

                        </div>

                    </article>


                    <article class="freq-stat">

                        <div class="freq-stat-icon danger-soft">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>

                        <div>

                            <span
                                class="freq-stat-value freq-stat-text"
                                id="freq-mes-mais-faltas"
                            >
                                —
                            </span>

                            <span class="freq-stat-label">
                                Mês com mais faltas
                            </span>

                        </div>

                    </article>

                </div>


                <!-- FREQUÊNCIA MÍNIMA ANUAL -->

                <div class="freq-meta-card">

                    <div class="freq-meta-title">

                        <div>

                            <strong>
                                Frequência mínima exigida no ano
                            </strong>

                            <span id="freq-meta-resumo">
                                Mínimo exigido: 80% · Frequência atual: 0%
                            </span>

                        </div>


                        <strong
                            class="freq-meta-percent"
                            id="freq-meta-percent"
                        >
                            0%
                        </strong>

                    </div>


                    <div class="freq-progress-track">

                        <div
                            class="freq-progress-fill"
                            id="freq-progress-fill"
                        ></div>

                    </div>


                    <div class="freq-meta-mensagens">

                        <p id="freq-diferenca-meta">
                            Configure o período letivo para acompanhar a frequência mínima.
                        </p>

                        <p id="freq-faltas-restantes">
                            —
                        </p>

                    </div>

                </div>


                <!-- ======================================
                     PROJEÇÃO ANUAL
                ======================================= -->

                <div class="freq-projecao-card">

                    <div class="freq-projecao-header">

                        <div>

                            <span class="freq-projecao-eyebrow">
                                Projeção
                            </span>

                            <h3>
                                Até o fim do ano letivo
                            </h3>

                        </div>

                        <i class="fa-solid fa-chart-line"></i>

                    </div>


                    <div class="freq-projecao-grid">

                        <div class="freq-projecao-item">

                            <span
                                class="freq-projecao-valor"
                                id="proj-dias-passados"
                            >
                                0
                            </span>

                            <span class="freq-projecao-label">
                                Dias letivos já contabilizados
                            </span>

                        </div>


                        <div class="freq-projecao-item">

                            <span
                                class="freq-projecao-valor"
                                id="proj-dias-restantes"
                            >
                                0
                            </span>

                            <span class="freq-projecao-label">
                                Dias letivos restantes
                            </span>

                        </div>


                        <div class="freq-projecao-item">

                            <span
                                class="freq-projecao-valor"
                                id="proj-presencas"
                            >
                                0
                            </span>

                            <span class="freq-projecao-label">
                                Presenças até agora
                            </span>

                        </div>


                        <div class="freq-projecao-item destaque">

                            <span
                                class="freq-projecao-valor"
                                id="proj-final-sem-faltas"
                            >
                                0%
                            </span>

                            <span class="freq-projecao-label">
                                Projeção sem novas faltas
                            </span>

                        </div>

                    </div>


                    <div
                        class="freq-projecao-aviso neutro"
                        id="freq-projecao-aviso"
                    >

                        <i class="fa-solid fa-circle-info"></i>

                        <span>
                            Configure o período letivo para gerar a projeção.
                        </span>

                    </div>

                </div>


                <!-- LEGENDA -->

                <div class="freq-legenda">

                    <strong>
                        Legenda:
                    </strong>

                    <span>
                        <i class="legenda-dot verde"></i>
                        Presença automática
                    </span>

                    <span>
                        <i class="legenda-dot vermelho"></i>
                        Falta
                    </span>

                    <span>
                        <i class="legenda-dot amarelo"></i>
                        Atestado
                    </span>

                    <span>
                        <i class="legenda-dot laranja"></i>
                        Sem aula
                    </span>

                    <span>
                        <i class="legenda-dot roxo"></i>
                        Prova
                    </span>

                    <span>
                        <i class="legenda-dot azul"></i>
                        Tarefa
                    </span>

                </div>

            </section>


            <!-- ======================================
                 CALENDÁRIOS
            ======================================= -->

            <div class="calendario-container">

                <div class="calendario">

                    <?php
                    gerarCalendario(
                        $anoSelecionado
                    );
                    ?>

                </div>

            </div>

            </div>
        </div>

            <?php include __DIR__ . '/../components/footer.php'; ?>

        </div>
    </div>

    <!-- ======================================
         MODAL LOGOUT
    ======================================= -->

    <div
        id="logout-modal"
        class="modal"
    >

        <div class="modal-content">

            <h3>
                Ah... já vai?
            </h3>

            <h4>
                Tem certeza que deseja sair?
            </h4>

            <div class="modal-buttons">

                <button id="confirm-logout">
                    Sim
                </button>

                <button id="cancel-logout">
                    Cancelar
                </button>

            </div>

        </div>

    </div>

    <script src="../estrelas/modal_estrelas.js?v=1"></script>

    <script src="calend.js?v=<?= time() ?>"></script>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            () => {
                const fogiBtn =
                    document.getElementById(
                        'icon-fogi'
                    );

                const fogiModal =
                    document.getElementById(
                        'fogi-modal'
                    );

                const fogiFrame =
                    document.getElementById(
                        'fogi-iframe'
                    );

                const fogiClose =
                    document.getElementById(
                        'fogi-close'
                    );

                if (
                    !fogiBtn ||
                    !fogiModal ||
                    !fogiFrame ||
                    !fogiClose
                ) {
                    return;
                }

                fogiBtn.addEventListener(
                    'click',
                    () => {
                        fogiFrame.src =
                            'http://127.0.0.1:5000';

                        fogiModal.style.display =
                            'flex';

                        document.body.style.overflow =
                            'hidden';
                    }
                );

                fogiClose.addEventListener(
                    'click',
                    () => {
                        fogiModal.style.display =
                            'none';

                        fogiFrame.src =
                            'about:blank';

                        document.body.style.overflow =
                            '';
                    }
                );

                window.addEventListener(
                    'message',
                    ev => {
                        if (
                            ev.data &&
                            ev.data.type ===
                                'FOGI_CLOSE'
                        ) {
                            fogiModal.style.display =
                                'none';

                            fogiFrame.src =
                                'about:blank';

                            document.body.style.overflow =
                                '';
                        }
                    }
                );
            }
        );
    </script>

 <script src="../configuracoes/aparencia.js?v=5"></script>
<script src="../configuracoes/acessibilidade.js?v=25" defer></script>
    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>