<?php
session_start();

if (empty($_SESSION['codigo_usuario'])) {
    header('Location: ../login/index.php');
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];
$current = basename($_SERVER['PHP_SELF']);

$pastaUsuario =
    __DIR__ .
    '/../json/usuarios/' .
    $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exit('Pasta do usuário não encontrada.');
}

// ==========================================
// FUNÇÕES AUXILIARES
// ==========================================

function carregarJsonInicio($arquivo, $padrao = [])
{
    if (!file_exists($arquivo)) {
        return $padrao;
    }

    $dados = json_decode(
        file_get_contents($arquivo),
        true
    );

    return is_array($dados)
        ? $dados
        : $padrao;
}

function normalizarListaTarefas($dados)
{
    if (!is_array($dados)) {
        return [];
    }

    foreach (['tarefas', 'items', 'agenda', 'eventos'] as $chave) {
        if (
            isset($dados[$chave]) &&
            is_array($dados[$chave])
        ) {
            return array_values($dados[$chave]);
        }
    }

    $ehLista = array_keys($dados) === range(
        0,
        max(count($dados) - 1, 0)
    );

    return $ehLista
        ? array_values($dados)
        : [];
}

function tarefaConcluida($tarefa)
{
    return !empty(
        $tarefa['concluida'] ??
        $tarefa['completed'] ??
        $tarefa['feito'] ??
        false
    );
}

function tarefaTexto($tarefa)
{
    return trim(
        (string)(
            $tarefa['texto'] ??
            $tarefa['titulo'] ??
            $tarefa['title'] ??
            $tarefa['descricao'] ??
            ''
        )
    );
}

function tarefaData($tarefa)
{
    return trim(
        (string)(
            $tarefa['data'] ??
            $tarefa['date'] ??
            $tarefa['prazo'] ??
            ''
        )
    );
}

function tarefaHora($tarefa)
{
    return trim(
        (string)(
            $tarefa['hora'] ??
            $tarefa['time'] ??
            ''
        )
    );
}

function calcularMediaLinhaInicio($notas, $pesos)
{
    if (!is_array($notas)) {
        return null;
    }

    $soma = 0;
    $somaPesos = 0;

    for ($i = 1; $i <= 4; $i++) {
        $nota =
            $notas[$i] ??
            $notas[(string)$i] ??
            null;

        $peso =
            $pesos[$i] ??
            $pesos[(string)$i] ??
            1;

        if (
            $nota === null ||
            $nota === '' ||
            (float)$peso <= 0
        ) {
            continue;
        }

        $soma +=
            (float)$nota *
            (float)$peso;

        $somaPesos +=
            (float)$peso;
    }

    return $somaPesos > 0
        ? $soma / $somaPesos
        : null;
}

function coletarStatusFrequencia($dados, &$presencas, &$faltas)
{
    if (!is_array($dados)) {
        return;
    }

    $status =
        $dados['status'] ??
        $dados['cor'] ??
        null;

    if ($status !== null) {
        if (
            in_array(
                $status,
                ['presenca', 'presença', 'verde'],
                true
            )
        ) {
            $presencas++;
        } elseif (
            in_array(
                $status,
                ['falta', 'vermelho'],
                true
            )
        ) {
            $faltas++;
        }
    }

    foreach ($dados as $valor) {
        if (is_array($valor)) {
            coletarStatusFrequencia(
                $valor,
                $presencas,
                $faltas
            );
        }
    }
}

// ==========================================
// ARQUIVOS DO USUÁRIO
// ==========================================

$arquivoAgenda =
    $pastaUsuario .
    '/agenda.json';

$arquivoMaterias =
    $pastaUsuario .
    '/materias.json';

$arquivoPomodoro =
    $pastaUsuario .
    '/pomodoro.json';

$arquivoNotas =
    $pastaUsuario .
    '/notas.json';

$arquivoCalendario =
    $pastaUsuario .
    '/calendario.json';

$arquivoInicio =
    $pastaUsuario .
    '/inicio.json';

$arquivoFreqMes =
    $pastaUsuario .
    '/frequencia_' .
    date('Y-m') .
    '.json';

// ==========================================
// DADOS BASE
// ==========================================

$agendaData =
    carregarJsonInicio(
        $arquivoAgenda,
        []
    );

$materiasData =
    carregarJsonInicio(
        $arquivoMaterias,
        ['materias' => []]
    );

$pomodoroData =
    carregarJsonInicio(
        $arquivoPomodoro,
        ['sessions' => []]
    );

$notasData =
    carregarJsonInicio(
        $arquivoNotas,
        []
    );

$calendarioData =
    carregarJsonInicio(
        $arquivoCalendario,
        []
    );

$inicioData =
    carregarJsonInicio(
        $arquivoInicio,
        ['anotacoes_importantes' => []]
    );

if (
    !isset($inicioData['anotacoes_importantes']) ||
    !is_array($inicioData['anotacoes_importantes'])
) {
    $inicioData['anotacoes_importantes'] = [];
}

$tarefas =
    normalizarListaTarefas(
        $agendaData
    );

$materias =
    isset($materiasData['materias']) &&
    is_array($materiasData['materias'])
        ? $materiasData['materias']
        : [];

$sessoes =
    isset($pomodoroData['sessions']) &&
    is_array($pomodoroData['sessions'])
        ? $pomodoroData['sessions']
        : [];

// ==========================================
// AGENDA / PRODUTIVIDADE
// ==========================================

$hoje =
    date('Y-m-d');

$tarefasPendentes =
    0;

$diasProdutivosMapa =
    [];

$proximosLembretes =
    [];

foreach ($tarefas as $tarefa) {
    if (!is_array($tarefa)) {
        continue;
    }

    $data =
        tarefaData($tarefa);

    $hora =
        tarefaHora($tarefa);

    $texto =
        tarefaTexto($tarefa);

    $concluida =
        tarefaConcluida($tarefa);

    if (
        $data === $hoje &&
        !$concluida
    ) {
        $tarefasPendentes++;
    }

    if (
        $concluida &&
        $data !== ''
    ) {
        $diasProdutivosMapa[$data] = true;
    }

    if (
        !$concluida &&
        $texto !== '' &&
        $data !== '' &&
        $data >= $hoje
    ) {
        $proximosLembretes[] = [
            'texto' => $texto,
            'data'  => $data,
            'hora'  => $hora
        ];
    }
}

usort(
    $proximosLembretes,
    function ($a, $b) {
        $dataA =
            ($a['data'] ?? '') .
            ' ' .
            ($a['hora'] ?? '');

        $dataB =
            ($b['data'] ?? '') .
            ' ' .
            ($b['hora'] ?? '');

        return strcmp(
            $dataA,
            $dataB
        );
    }
);

$proximosLembretes =
    array_slice(
        $proximosLembretes,
        0,
        4
    );

$diasConsecutivos =
    0;

$dataCursor =
    new DateTime($hoje);

while (true) {
    $dataStr =
        $dataCursor->format(
            'Y-m-d'
        );

    if (
        !isset(
            $diasProdutivosMapa[$dataStr]
        )
    ) {
        break;
    }

    $diasConsecutivos++;

    $dataCursor->modify(
        '-1 day'
    );
}

// ==========================================
// ESTUDOS / POMODORO
// ==========================================

$totalMinutosEstudados =
    0;

$totalSessoes =
    0;

foreach ($sessoes as $sessao) {
    if (!is_array($sessao)) {
        continue;
    }

    $modo =
        $sessao['mode'] ??
        $sessao['modo'] ??
        'focus';

    if ($modo !== 'focus') {
        continue;
    }

    $minutos =
        (int)(
            $sessao['minutes'] ??
            $sessao['minutos'] ??
            0
        );

    if ($minutos > 0) {
        $totalMinutosEstudados +=
            $minutos;

        $totalSessoes++;
    }
}

$horasEstudadas =
    intdiv(
        $totalMinutosEstudados,
        60
    );

$minutosRestantes =
    $totalMinutosEstudados % 60;

$tempoEstudadoFormatado =
    $horasEstudadas > 0
        ? $horasEstudadas .
          'h ' .
          str_pad(
              (string)$minutosRestantes,
              2,
              '0',
              STR_PAD_LEFT
          ) .
          'min'
        : $minutosRestantes .
          'min';

// ==========================================
// BOLETIM
// ==========================================

$notaMaxima =
    (float)(
        $notasData['nota_maxima'] ??
        10
    );

$pesos =
    isset($notasData['pesos']) &&
    is_array($notasData['pesos'])
        ? $notasData['pesos']
        : [
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 1
        ];

$periodos =
    isset($notasData['periodos']) &&
    is_array($notasData['periodos'])
        ? $notasData['periodos']
        : [];

$periodoAtual =
    (string)(
        $notasData['periodo_atual'] ??
        ''
    );

$dadosPeriodo =
    isset($periodos[$periodoAtual]) &&
    is_array($periodos[$periodoAtual])
        ? $periodos[$periodoAtual]
        : [];

$nomesPeriodo =
    isset($dadosPeriodo['materias']) &&
    is_array($dadosPeriodo['materias'])
        ? $dadosPeriodo['materias']
        : [];

$notasPeriodo =
    isset($dadosPeriodo['notas']) &&
    is_array($dadosPeriodo['notas'])
        ? $dadosPeriodo['notas']
        : [];

$somaMedias =
    0;

$totalMedias =
    0;

$materiaAtencao =
    null;

foreach ($nomesPeriodo as $indice => $nomeMateria) {
    $media =
        calcularMediaLinhaInicio(
            $notasPeriodo[$indice] ?? [],
            $pesos
        );

    if ($media === null) {
        continue;
    }

    $somaMedias +=
        $media;

    $totalMedias++;

    if (
        $materiaAtencao === null ||
        $media <
        $materiaAtencao['media']
    ) {
        $materiaAtencao = [
            'nome'  => (string)$nomeMateria,
            'media' => $media
        ];
    }
}

$mediaGeral =
    $totalMedias > 0
        ? $somaMedias /
          $totalMedias
        : 0;

$percentualMedia =
    $notaMaxima > 0
        ? (int)round(
            min(
                100,
                max(
                    0,
                    ($mediaGeral /
                    $notaMaxima) *
                    100
                )
            )
        )
        : 0;

// ==========================================
// FREQUÊNCIA
// ==========================================

$totalPresencas =
    0;

$totalFaltas =
    0;

if (!empty($calendarioData)) {
    coletarStatusFrequencia(
        $calendarioData,
        $totalPresencas,
        $totalFaltas
    );
} elseif (file_exists($arquivoFreqMes)) {
    $dadosFreqMes =
        carregarJsonInicio(
            $arquivoFreqMes,
            []
        );

    coletarStatusFrequencia(
        $dadosFreqMes,
        $totalPresencas,
        $totalFaltas
    );
}

$totalAulas =
    $totalPresencas +
    $totalFaltas;

$percentualPresenca =
    $totalAulas > 0
        ? (int)round(
            ($totalPresencas /
            $totalAulas) *
            100
        )
        : 0;

// ==========================================
// ANOTAÇÕES IMPORTANTES
// ==========================================

$anotacoesImportantes =
    $inicioData[
        'anotacoes_importantes'
    ];

usort(
    $anotacoesImportantes,
    function ($a, $b) {
        return (
            (int)(
                $b['timestamp'] ??
                0
            )
        ) <=> (
            (int)(
                $a['timestamp'] ??
                0
            )
        );
    }
);

$anotacoesImportantes =
    array_slice(
        $anotacoesImportantes,
        0,
        5
    );

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG - Início</title>
    <link rel="stylesheet" href="inicioo.css?v=10">
    <link rel="stylesheet" href="dark_ini.css?v=11">
    <link rel="stylesheet" href="../global/css/cursor.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<script>
/* =========================================================
   FOAG — TEMA GLOBAL NA PÁGINA INICIAL
   Lê exatamente as mesmas chaves salvas em Configurações.
========================================================= */
(function () {
    'use strict';

    function temaEscuroAtivo() {
        const temaSalvo = localStorage.getItem('foagTema');

        if (temaSalvo === 'escuro') {
            return true;
        }

        if (temaSalvo === 'claro') {
            return false;
        }

        if (temaSalvo === 'sistema') {
            return window.matchMedia &&
                window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        // Compatibilidade com o sistema antigo do FOAG
        return localStorage.getItem('darkMode') === 'true';
    }

    document.body.classList.toggle(
        'dark-mode',
        temaEscuroAtivo()
    );

    // Se o usuário escolheu "Tema do dispositivo",
    // acompanha alterações do sistema operacional.
    if (window.matchMedia) {
        const mediaTema = window.matchMedia(
            '(prefers-color-scheme: dark)'
        );

        mediaTema.addEventListener('change', function () {
            if (localStorage.getItem('foagTema') === 'sistema') {
                document.body.classList.toggle(
                    'dark-mode',
                    mediaTema.matches
                );
            }
        });
    }
})();
</script>



<?php
?>


<!-- ===========================
     CABEÇALHO
=========================== -->

<header class="cabecalho">

    <div class="header-left">

        <div class="logo">
            FOAG
        </div>

        <!-- ======================================
             MENU
        ======================================= -->

        <nav class="top-menu">
            <a href="../inicioo/inicio.php" class="<?= $current === 'inicio.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> Início
            </a>

            <a href="../estudos/estudos.php" class="<?= $current === 'estudos.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-graduation-cap"></i> Estudos
            </a>

            <a href="../bloco/agenda.php" class="<?= $current === 'agenda.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-book"></i> Agenda
            </a>

            <a href="../calend/calendario.php" class="<?= $current === 'calendario.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i> Calendário
            </a>

            <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-check-double"></i> Boletim
            </a>

            <a href="../comunidade/comunidade.php" class="<?= $current === 'comunidade.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments"></i> Comunidade
            </a>

            <a href="../rank/rank.php" class="<?= $current === 'rank.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-trophy"></i> Ranking
            </a>

            <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-store"></i> Loja
            </a>
        </nav>

    </div>

    <!-- ÍCONES DO CABEÇALHO -->

    <div class="header-icons">

        <a
            href="../configuracoes/configuracoes.php"
            class="link-configuracoes"
            title="Configurações"
            aria-label="Abrir configurações"
        >
            <i
                class="fa-solid fa-gear"
                aria-hidden="true"
            ></i>
        </a>

        <button
            type="button"
            id="icon-perfil"
            class="header-icon-btn"
            title="Perfil"
            aria-label="Abrir perfil"
        >
            <i
                class="fa-regular fa-user"
                aria-hidden="true"
            ></i>
        </button>

        <button
            type="button"
            id="icon-sair"
            class="header-icon-btn"
            title="Sair"
            aria-label="Sair da conta"
        >
            <i
                class="fa-solid fa-right-from-bracket"
                aria-hidden="true"
            ></i>
        </button>

    </div>

</header>


<!-- ===========================
     CONTEÚDO PRINCIPAL
=========================== -->

<main
    class="main-content"
    id="conteudo-principal"
    tabindex="-1"
>

    <div class="welcome-container">


        <!-- LADO ESQUERDO -->

        <div class="left-panel">


            <div class="welcome-header">

                <h1>
                    Bem-vindo de volta! 👋
                </h1>

                <p class="subtitle">
                    Seu organizador pessoal FOAG está aqui
                    para te ajudar a ser mais produtivo
                </p>

            </div>



            <!-- ESTATÍSTICAS -->

            <div class="stats-grid-large">


                <div class="stat-card-large">

                    <div
                        class="stat-icon-large"
                        aria-hidden="true"
                    >

                        <i
                            class="fa-solid fa-calendar-check"
                        ></i>

                    </div>


                    <div class="stat-info-large">

                        <span
                            class="stat-number-large"
                            id="dias-consecutivos"
                        >
                            <?= $diasConsecutivos ?>
                        </span>

                        <span
                            class="stat-label-large"
                        >
                            Dias produtivos consecutivos
                        </span>

                    </div>

                </div>



                <div class="stat-card-large">

                    <div
                        class="stat-icon-large"
                        aria-hidden="true"
                    >

                        <i
                            class="fa-solid fa-tasks"
                        ></i>

                    </div>


                    <div class="stat-info-large">

                        <span
                            class="stat-number-large"
                            id="tarefas-pendentes"
                        >
                            <?= $tarefasPendentes ?>
                        </span>

                        <span
                            class="stat-label-large"
                        >
                            Tarefas para hoje
                        </span>

                    </div>

                </div>



                <div class="stat-card-large">

                    <div
                        class="stat-icon-large"
                        aria-hidden="true"
                    >

                        <i
                            class="fa-solid fa-stopwatch"
                        ></i>

                    </div>


                    <div class="stat-info-large">

                        <span
                            class="stat-number-large"
                            id="tempo-estudado"
                        >
                            <?= htmlspecialchars($tempoEstudadoFormatado) ?>
                        </span>

                        <span
                            class="stat-label-large"
                        >
                            Tempo estudado
                        </span>

                    </div>

                </div>

            </div>



            <!-- MOTIVAÇÃO -->

            <div class="motivational-section">


                <div class="motivational-quote-large">

                    <i
                        class="fa-solid fa-quote-left"
                        aria-hidden="true"
                    ></i>


                    <p id="quote-text">

                        Organizar é o primeiro passo
                        para o sucesso! Comece seu dia
                        planejando suas atividades.

                    </p>


                    <i
                        class="fa-solid fa-quote-right"
                        aria-hidden="true"
                    ></i>

                </div>



                <div class="quick-tips">

                    <h3>
                        💡 Dicas Rápidas
                    </h3>

                    <ul>

                        <li>
                            Revise suas tarefas pela manhã
                        </li>

                        <li>
                            Estabeleça metas realistas
                            para o dia
                        </li>

                        <li>
                            Faça pausas regulares
                        </li>

                        <li>
                            Celebre suas conquistas
                        </li>

                    </ul>

                </div>

            </div>

        </div>



        <!-- LADO DIREITO -->

        <div class="right-panel">


            <!-- RESUMO DO MÊS -->

            <section
                class="info-card"
                aria-labelledby="titulo-resumo-mes"
            >

                <div class="card-header">

                    <h2 id="titulo-resumo-mes">

                        <i
                            class="fa-solid fa-chart-line"
                            aria-hidden="true"
                        ></i>

                        Resumo dos Estudos

                    </h2>

                </div>


                <div class="card-content">


                    <div class="metric-row">


                        <div class="metric">

                            <span
                                class="metric-value"
                                id="total-presencas"
                            >
                                <?= count($materias) ?>
                            </span>

                            <span class="metric-label">
                                Matérias
                            </span>

                        </div>



                        <div class="metric">

                            <span
                                class="metric-value"
                                id="total-faltas"
                            >
                                <?= $totalSessoes ?>
                            </span>

                            <span class="metric-label">
                                Sessões
                            </span>

                        </div>



                        <div class="metric">

                            <span
                                class="metric-value"
                                id="percentual-presenca"
                            >
                                <?= number_format($mediaGeral, 2, ',', '.') ?>
                            </span>

                            <span class="metric-label">
                                Média geral
                            </span>

                        </div>

                    </div>



                    <!-- BARRA DE FREQUÊNCIA -->

                    <div class="progress-container">


                        <div
                            class="progress-bar"
                            role="progressbar"
                            aria-label="Desempenho médio no boletim"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="<?= $percentualMedia ?>"
                        >

                            <div
                                class="progress-fill"
                                style="width: <?= $percentualMedia ?>%"
                            ></div>

                        </div>


                        <span class="progress-text">
                            <?php if ($totalMedias > 0): ?>
                                Desempenho atual: <?= number_format($percentualMedia, 0, ',', '.') ?>%
                            <?php else: ?>
                                Adicione notas no Boletim para acompanhar sua média.
                            <?php endif; ?>
                        </span>

                        <?php if ($materiaAtencao): ?>
                            <p class="progress-text" style="margin-top:8px;">
                                <strong>Precisa de atenção:</strong>
                                <?= htmlspecialchars($materiaAtencao['nome']) ?>
                                (<?= number_format($materiaAtencao['media'], 2, ',', '.') ?>)
                            </p>
                        <?php endif; ?>

                    </div>

                </div>

            </section>



            <!-- ANOTAÇÕES IMPORTANTES -->

            <section
                class="info-card"
                aria-labelledby="titulo-anotacoes"
            >

                <div class="card-header">


                    <h2 id="titulo-anotacoes">

                        <i
                            class="fa-solid fa-star"
                            aria-hidden="true"
                        ></i>

                        Anotações Importantes

                    </h2>


                    <button
                        type="button"
                        class="btn-add"
                        id="add-note"
                        aria-label="Criar nova anotação importante"
                        title="Criar nova anotação"
                    >

                        <i
                            class="fa-solid fa-plus"
                            aria-hidden="true"
                        ></i>

                    </button>

                </div>


                <div class="card-content">


                    <div
                        class="notes-list"
                        id="notes-list"
                    >


                        <?php if (
                            !empty($anotacoesImportantes)
                        ): ?>


                            <?php foreach (
                                $anotacoesImportantes
                                as $nota
                            ): ?>


                                <div class="note-item">


                                    <p class="note-text">

                                        <?= htmlspecialchars(
                                            $nota['text']
                                        ) ?>

                                    </p>


                                    <?php if (
                                        !empty($nota['date'])
                                    ): ?>


                                        <span class="note-date">

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $nota['date']
                                                )
                                            ) ?>

                                        </span>


                                    <?php endif; ?>


                                </div>


                            <?php endforeach; ?>


                        <?php endif; ?>


                    </div>



                    <div
                        class="empty-notes"
                        id="empty-notes"
                        style="<?= !empty($anotacoesImportantes)
                            ? 'display:none;'
                            : '' ?>"
                    >

                        <i
                            class="fa-solid fa-clipboard"
                            aria-hidden="true"
                        ></i>


                        <p>
                            Nenhuma anotação importante
                        </p>


                        <button
                            type="button"
                            class="btn-primary"
                            id="create-first-note"
                        >

                            <i
                                class="fa-solid fa-plus"
                                aria-hidden="true"
                            ></i>

                            Criar primeira anotação

                        </button>

                    </div>

                </div>

            </section>



            <!-- LEMBRETES -->

            <section
                class="info-card"
                aria-labelledby="titulo-lembretes"
            >

                <div class="card-header">

                    <h2 id="titulo-lembretes">

                        <i
                            class="fa-solid fa-bell"
                            aria-hidden="true"
                        ></i>

                        Próximos Lembretes

                    </h2>

                </div>


                <div class="card-content">


                    <div
                        class="reminders-list"
                        id="reminders-list"
                    >
                        <?php foreach ($proximosLembretes as $lembrete): ?>
                            <div class="reminder-item">
                                <div class="reminder-icon" aria-hidden="true">
                                    <i class="fa-solid fa-clock"></i>
                                </div>

                                <div class="reminder-text">
                                    <?= htmlspecialchars($lembrete['texto']) ?>
                                </div>

                                <div class="reminder-time">
                                    <?php
                                    $dataLembrete = $lembrete['data'];

                                    if ($dataLembrete === $hoje) {
                                        echo $lembrete['hora'] !== ''
                                            ? htmlspecialchars($lembrete['hora'])
                                            : 'Hoje';
                                    } else {
                                        echo date(
                                            'd/m',
                                            strtotime($dataLembrete)
                                        );

                                        if ($lembrete['hora'] !== '') {
                                            echo ' · ' .
                                                htmlspecialchars(
                                                    $lembrete['hora']
                                                );
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>


                    <div
                        class="empty-reminders"
                        id="empty-reminders"
                        style="<?= !empty($proximosLembretes) ? 'display:none;' : '' ?>"
                    >

                        <i
                            class="fa-solid fa-bell-slash"
                            aria-hidden="true"
                        ></i>


                        <p>
                            Nenhum lembrete próximo
                        </p>

                    </div>

                </div>

            </section>

        </div>

    </div>

</main>



<!-- ===========================
     MODAL NOVA ANOTAÇÃO
=========================== -->

<div
    id="note-modal"
    class="modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo-note-modal"
>

    <div class="modal-content">


        <div class="modal-header">


            <h3 id="titulo-note-modal">
                Nova Anotação Importante
            </h3>


            <button
                type="button"
                class="modal-close"
                id="close-note-modal"
                aria-label="Fechar janela de nova anotação"
            >

                <i
                    class="fa-solid fa-times"
                    aria-hidden="true"
                ></i>

            </button>

        </div>



        <div class="modal-body">


            <label
                for="note-text"
                class="sr-only"
            >
                Anotação importante
            </label>


            <textarea
                id="note-text"
                placeholder="Digite sua anotação importante aqui..."
                maxlength="200"
                aria-describedby="note-limite"
            ></textarea>


            <span
                id="note-limite"
                class="sr-only"
            >
                Máximo de 200 caracteres
            </span>


            <div class="modal-footer">


                <button
                    type="button"
                    class="btn-secondary"
                    id="cancel-note"
                >
                    Cancelar
                </button>


                <button
                    type="button"
                    class="btn-primary"
                    id="save-note"
                >
                    Salvar
                </button>


            </div>

        </div>

    </div>

</div>



<!-- ===========================
     MODAL LOGOUT
=========================== -->

<div
    id="logout-modal"
    class="modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo-logout"
>

    <div class="modal-content">


        <h3 id="titulo-logout">
            Ah... já vai?
        </h3>


        <h4>
            Tem certeza de que deseja sair?
        </h4>


        <div class="modal-buttons">


            <button
                type="button"
                id="confirm-logout"
            >
                Sim
            </button>


            <button
                type="button"
                id="cancel-logout"
            >
                Cancelar
            </button>


        </div>

    </div>

</div>



<!-- ===========================
     MODAL FOGi
=========================== -->

<div
    id="fogi-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo-fogi"
>

    <div class="fogi-container">


        <div class="fogi-header">


            <span id="titulo-fogi">
                FOGi — Assistente de Estudos
            </span>


            <button
                type="button"
                id="fogi-close"
            >
                Fechar
            </button>


        </div>


        <iframe
            id="fogi-iframe"
            src="about:blank"
            title="FOGi - Assistente de Estudos"
        ></iframe>


    </div>

</div>



<!-- ===========================
     RODAPÉ
=========================== -->

<footer>

    <div class="footer-content">

        <p>
            &copy; 2025 FOAG.
            Todos os direitos reservados.
        </p>

        <p>
            Organize seu tempo,
            conquiste seus objetivos
        </p>

    </div>

</footer>



<script>
window.INICIO_NOTE_SAVE_URL = 'salvar_anotacao.php';
</script>
<script src="inicio.js"></script>

<script src="../global/js/cursor.js"></script>

<!-- LIBRAS GLOBAL FOAG -->
<script src="../configuracoes/acessibilidade.js?v=22"></script>

</body>

</html>