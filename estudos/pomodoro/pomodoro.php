<?php
require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

// ======================================
// VERIFICAR LOGIN
// ======================================

$contextoUsuario = foag_contexto_usuario('../../login/index.php');
$codigoUsuario = $contextoUsuario['codigoUsuario'];
$baseJsonDir = $contextoUsuario['baseJsonDir'];
$pastaUsuario = $contextoUsuario['pastaUsuario'];

// ======================================
// PASTA DO USUÁRIO
// ======================================


if (!is_dir($pastaUsuario)) {
    exit("Pasta do usuário não encontrada.");
}

// ======================================
// ARQUIVO DO POMODORO
// ======================================

$arquivoPomodoro = $pastaUsuario . '/pomodoro.json';

if (!file_exists($arquivoPomodoro)) {
    $estadoInicial = [
        'disciplines' => ['Geral'],
        'sessions' => [],
        'goals' => new stdClass()
    ];

    file_put_contents(
        $arquivoPomodoro,
        json_encode(
            $estadoInicial,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ),
        LOCK_EX
    );
}

// ======================================
// CARREGAR POMODORO
// ======================================

$pomodoroData = json_decode(
    file_get_contents($arquivoPomodoro),
    true
);

if (!is_array($pomodoroData)) {
    $pomodoroData = [];
}

if (
    !isset($pomodoroData['sessions']) ||
    !is_array($pomodoroData['sessions'])
) {
    $pomodoroData['sessions'] = [];
}

if (
    !isset($pomodoroData['goals']) ||
    !is_array($pomodoroData['goals'])
) {
    $pomodoroData['goals'] = [];
}

// ======================================
// CARREGAR MATÉRIAS DO USUÁRIO
// ======================================

$arquivoMaterias = $pastaUsuario . '/materias.json';

$materiasData = [
    'materias' => []
];

if (file_exists($arquivoMaterias)) {

    $conteudoMaterias = json_decode(
        file_get_contents($arquivoMaterias),
        true
    );

    if (
        is_array($conteudoMaterias) &&
        isset($conteudoMaterias['materias']) &&
        is_array($conteudoMaterias['materias'])
    ) {
        $materiasData = $conteudoMaterias;
    }
}

$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="../../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG – Pomodoro</title>

    <link rel="stylesheet" href="pomodoro.css">
    <link rel="stylesheet" href="dark_pomo.css">
    <link rel="stylesheet" href="../../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="../../estrelas/modal_estrelas.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../global/css/cursor.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script src="../../m.escuro/dark-mode.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>

        window.POMODORO_DATA =
            <?= json_encode(
                $pomodoroData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;


        window.MATERIAS_DATA =
            <?= json_encode(
                $materiasData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;


        window.POMODORO_SAVE_URL =
            FOAG_CONFIG.endpoints.pomodoroSalvar;

    </script>


<style>
  /* ==========================================
     ÁREA PRINCIPAL + FOOTER PADRÃO FOAG
  ========================================== */
  .page-area {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
  }

  .page-area > .conteudo {
    flex: 1;
    width: 100%;
  }

  .footer {
    width: 100% !important;
    margin: 30px 0 0 !important;
    padding: 0 !important;
    background: #ffffff !important;
    color: inherit !important;
    border-top: 1px solid #e5edf5 !important;
    box-shadow: none !important;
    text-align: left !important;
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
    gap: 24px;
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
    transition: color .2s ease;
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
      gap: 16px 22px;
    }
  }
</style>

    <link rel="stylesheet" href="../../global/css/layout.css?v=<?= time() ?>">
</head>


<body>


    <!-- ==========================================
         CABEÇALHO
    =========================================== -->

    <?php include __DIR__ . '/../../components/header.php'; ?>


    <div class="container">


        <!-- ==========================================
             MENU LATERAL
        =========================================== -->

        <?php include __DIR__ . '/../../components/menu.php'; ?>

    <div class="page-area">

        <!-- ==========================================
             CONTEÚDO
        =========================================== -->

        <main class="conteudo">


            <section class="estudos-wrapper">


                <!-- ==========================================
                     VOLTAR
                =========================================== -->

                <div class="pomodoro-nav">

                    <a
                        href="../estudos.php"
                        class="back-studies"
                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Voltar para Estudos

                    </a>

                </div>


                <!-- ==========================================
                     TIMER + CRONÔMETRO
                =========================================== -->

                <section class="card half">


                    <h2>
                        ⏱️ Tempo de Estudo
                    </h2>


                    <p class="sub">

                        Use o

                        <strong>
                            Timer (Pomodoro)
                        </strong>

                        para sessões cronometradas ou o

                        <strong>
                            Cronômetro
                        </strong>

                        para contar livre.

                    </p>


                    <!-- ======================================
                         ABAS
                    ======================================= -->

                    <div class="tabs">


                        <button
                            class="tab-btn active"
                            data-tab="pomodoro"
                        >

                            Timer (Pomodoro)

                        </button>


                        <button
                            class="tab-btn"
                            data-tab="stopwatch"
                        >

                            Cronômetro

                        </button>


                    </div>


                    <!-- ======================================
                         PAINEL POMODORO
                    ======================================= -->

                    <div
                        id="tab-pomodoro"
                        class="tab-panel active"
                    >


                        <div class="grid-2">


                            <label class="row">

                                <span class="lbl">
                                    Foco (min)
                                </span>

                                <input
                                    id="focusM"
                                    class="input"
                                    type="number"
                                    min="5"
                                    max="120"
                                    value="25"
                                />

                            </label>


                            <label class="row">

                                <span class="lbl">
                                    Pausa curta (min)
                                </span>

                                <input
                                    id="shortM"
                                    class="input"
                                    type="number"
                                    min="3"
                                    max="30"
                                    value="5"
                                />

                            </label>


                            <label class="row">

                                <span class="lbl">
                                    Pausa longa (min)
                                </span>

                                <input
                                    id="longM"
                                    class="input"
                                    type="number"
                                    min="5"
                                    max="60"
                                    value="15"
                                />

                            </label>


                            <label class="row">

                                <span class="lbl">
                                    A cada (ciclos)
                                </span>

                                <input
                                    id="everyCycles"
                                    class="input"
                                    type="number"
                                    min="2"
                                    max="8"
                                    value="4"
                                />

                            </label>


                        </div>


                        <!-- ======================================
                             MATÉRIA
                        ======================================= -->

                        <div class="field-group mt">

                            <label
                                class="lbl"
                                for="discipline"
                            >
                                Matéria
                            </label>


                            <select
                                id="discipline"
                                class="select"
                            >

                                <option value="Geral">
                                    Geral
                                </option>

                            </select>

                        </div>


                        <!-- ======================================
                             TIMER
                        ======================================= -->

                        <div
                            class="timer"
                            id="timer"
                        >
                            25:00
                        </div>


                        <div class="row center gap">


                            <button
                                class="btn"
                                id="startBtn"
                            >

                                <i class="fa-solid fa-play"></i>

                                Iniciar

                            </button>


                            <button
                                class="btn secondary"
                                id="pauseBtn"
                            >

                                <i class="fa-solid fa-pause"></i>

                                Pausar

                            </button>


                            <button
                                class="btn ghost"
                                id="resetBtn"
                            >

                                <i class="fa-solid fa-rotate-left"></i>

                                Reset

                            </button>


                        </div>


                        <div class="row center gap mt">


                            <span
                                class="pill"
                                id="modePill"
                            >

                                <i class="fa-solid fa-hourglass-half"></i>

                                Foco

                            </span>


                            <span
                                class="pill"
                                id="cyclePill"
                            >

                                <i class="fa-solid fa-repeat"></i>

                                Ciclo 1

                            </span>


                        </div>


                        <div class="progress mt">

                            <span id="timerProgress"></span>

                        </div>


                        <audio
                            id="ding"
                            preload="auto"
                        >

                            <source
                                src="https://cdn.pixabay.com/download/audio/2022/03/15/audio_6f4caa1a68.mp3?filename=ui-interface-sfx-confirmation-95384.mp3"
                                type="audio/mpeg"
                            />

                        </audio>


                    </div>


                    <!-- ======================================
                         CRONÔMETRO
                    ======================================= -->

                    <div
                        id="tab-stopwatch"
                        class="tab-panel"
                    >


                        <div class="row mt">


                            <select
                                id="stopwatchDiscipline"
                                class="select"
                            ></select>


                            <button
                                class="btn secondary"
                                id="swSaveSession"
                                title="Salvar a sessão atual como estudo"
                            >

                                <i class="fa-solid fa-floppy-disk"></i>

                                Salvar sessão

                            </button>


                        </div>


                        <div
                            class="timer"
                            id="stopwatchDisplay"
                        >
                            00:00:00
                        </div>


                        <div class="row center gap">


                            <button
                                class="btn"
                                id="swStart"
                            >

                                <i class="fa-solid fa-play"></i>

                                Iniciar

                            </button>


                            <button
                                class="btn secondary"
                                id="swPause"
                            >

                                <i class="fa-solid fa-pause"></i>

                                Pausar

                            </button>


                            <button
                                class="btn ghost"
                                id="swReset"
                            >

                                <i class="fa-solid fa-rotate-left"></i>

                                Zerar

                            </button>


                            <button
                                class="btn"
                                id="swLap"
                            >

                                <i class="fa-solid fa-flag-checkered"></i>

                                Volta

                            </button>


                        </div>


                        <div
                            class="list mt"
                            id="lapsList"
                        ></div>


                    </div>


                </section>


                <!-- ==========================================
                     METAS SEMANAIS
                =========================================== -->

                <section class="card half">


                    <h2>
                        🎯 Metas Semanais
                    </h2>


                    <p class="sub">

                        Defina horas por matéria e acompanhe o progresso semanal.

                    </p>


                    <div class="row">


                        <select
                            id="goalDiscipline"
                            class="select"
                        ></select>


                        <input
                            id="goalHours"
                            class="input"
                            type="number"
                            min="1"
                            max="60"
                            placeholder="Horas/semana"
                        />


                        <button
                            class="btn"
                            id="saveGoal"
                        >

                            Salvar meta

                        </button>


                    </div>


                    <div
                        id="goalsList"
                        class="list mt"
                    ></div>


                    <!-- ======================================
                         ESTUDADAS RECENTEMENTE
                    ======================================= -->

                    <div class="recent-studies">


                        <div class="recent-studies-header">


                            <div>


                                <h3>

                                    <i class="fa-solid fa-clock-rotate-left"></i>

                                    Estudadas recentemente

                                </h3>


                                <p>
                                    Suas últimas matérias estudadas.
                                </p>


                            </div>


                        </div>


                        <div
                            id="recentStudiesList"
                            class="recent-studies-list"
                        >


                            <div class="recent-empty">


                                <i class="fa-regular fa-clock"></i>


                                <span>

                                    Suas matérias estudadas recentemente aparecerão aqui.

                                </span>


                            </div>


                        </div>


                    </div>


                </section>


                <!-- ==========================================
                     ESTATÍSTICAS
                =========================================== -->

                <section class="card full">


                    <h2>
                        📊 Estatísticas
                    </h2>


                    <p class="sub">

                        Horas estudadas nos últimos 14 dias e distribuição por matéria.

                    </p>


                    <div class="grid-2">


                        <div>

                            <canvas id="lineChart"></canvas>

                        </div>


                        <div>

                            <canvas id="pieChart"></canvas>

                        </div>


                    </div>


                </section>


                <!-- ==========================================
                     HISTÓRICO
                =========================================== -->

                <section class="card full">


                    <div class="row between">


                        <h2>
                            🗂️ Histórico de Sessões
                        </h2>


                        <div class="row gap">


                            <button
                                class="btn secondary"
                                id="clearHistory"
                            >

                                <i class="fa-solid fa-trash"></i>

                                Limpar histórico

                            </button>


                            <button
                                class="btn"
                                id="exportCsv"
                            >

                                <i class="fa-solid fa-file-arrow-down"></i>

                                Exportar CSV

                            </button>


                        </div>


                    </div>


                    <table
                        class="table"
                        id="historyTable"
                    >


                        <thead>

                            <tr>

                                <th>Data</th>

                                <th>Matéria</th>

                                <th>Modo</th>

                                <th>Duração (min)</th>

                            </tr>

                        </thead>


                        <tbody></tbody>


                    </table>


                </section>


            </section>


        </main>
    <?php include __DIR__ . '/../../components/footer.php'; ?>

    </div>
  </div>
<!-- ==========================================
         MODAL DE SAIR
    =========================================== -->

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


                <button
                    id="confirm-logout"
                    class="btn"
                >

                    Sim

                </button>


                <button
                    id="cancel-logout"
                    class="btn secondary"
                >

                    Cancelar

                </button>


            </div>


        </div>


    </div>


    <!-- ==========================================
         MODAL ANTIGO DE FEEDBACK DE ESTRELAS
         MANTIDO PARA NÃO QUEBRAR O POMODORO
    =========================================== -->

    <div
        class="stars-feedback-modal"
        id="stars-feedback-modal"
    >


        <div class="stars-feedback-box">


            <div class="stars-feedback-icon">

                <i
                    class="fa-solid fa-star"
                    id="stars-feedback-icon"
                ></i>

            </div>


            <h3 id="stars-feedback-title">
                Estrelas
            </h3>


            <p id="stars-feedback-text"></p>


            <label class="stars-feedback-hide">


                <input
                    type="checkbox"
                    id="stars-feedback-dont-show"
                >


                <span>
                    Não mostrar novamente
                </span>


            </label>


            <button
                type="button"
                id="stars-feedback-close"
                class="stars-feedback-close"
            >

                Entendi

            </button>


        </div>


    </div>
<!-- ==========================================
         MODAL GLOBAL DE ESTRELAS
    =========================================== -->

    <script src="../../estrelas/modal_estrelas.js?v=<?= time() ?>"></script>


    <!-- ==========================================
         LÓGICA DO POMODORO
    =========================================== -->

    <script defer src="pomodoro.js?v=<?= time() ?>"></script>

    <script src="../../global/js/cursor.js?v=<?= time() ?>"></script>

    <script src="../../configuracoes/aparencia.js?v=6"></script>
    <script src="../../configuracoes/acessibilidade.js?v=26" defer></script>

</body>

</html>