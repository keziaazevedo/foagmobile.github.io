<?php
session_start();

// ======================================
// VERIFICAR LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {
    header("Location: ../login/index.php");
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

$current = basename($_SERVER['PHP_SELF']);

// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir =
    __DIR__ . '/../json/usuarios';

$pastaUsuario =
    $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exit('Pasta do usuário não encontrada.');
}


// ======================================
// CARREGAR AGENDA DO USUÁRIO
// ======================================

$arquivoAgenda =
    $pastaUsuario . '/agenda.json';

$estruturaAgendaPadrao = [
    'notas' => [],
    'tarefas' => [],
    'nao_esquecer' => []
];


// ======================================
// CRIAR AGENDA CASO NÃO EXISTA
// ======================================

if (!file_exists($arquivoAgenda)) {

    file_put_contents(
        $arquivoAgenda,
        json_encode(
            $estruturaAgendaPadrao,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ),
        LOCK_EX
    );
}


// ======================================
// LER AGENDA.JSON
// ======================================

$conteudoAgenda =
    file_get_contents(
        $arquivoAgenda
    );

$agendaData =
    json_decode(
        $conteudoAgenda ?: '',
        true
    );


// ======================================
// CORRIGIR JSON INVÁLIDO
// ======================================

if (!is_array($agendaData)) {

    $agendaData =
        $estruturaAgendaPadrao;
}


// ======================================
// GARANTIR ESTRUTURA
// ======================================

if (
    !isset($agendaData['notas']) ||
    !is_array($agendaData['notas'])
) {
    $agendaData['notas'] = [];
}


if (
    !isset($agendaData['tarefas']) ||
    !is_array($agendaData['tarefas'])
) {
    $agendaData['tarefas'] = [];
}


if (
    !isset($agendaData['nao_esquecer']) ||
    !is_array($agendaData['nao_esquecer'])
) {
    $agendaData['nao_esquecer'] = [];
}


// ======================================
// DADOS DA AGENDA
// ======================================

$tarefas =
    array_values(
        $agendaData['tarefas']
    );

$lembretes =
    array_values(
        $agendaData['nao_esquecer']
    );

$notasAgenda =
    array_values(
        $agendaData['notas']
    );


// ======================================
// RESUMO DA AGENDA
// ======================================

$totalTarefas =
    count(
        $agendaData['tarefas']
    );

$totalLembretes =
    count(
        $agendaData['nao_esquecer']
    );

$totalNotas =
    count(
        $agendaData['notas']
    );

// ======================================
// DADOS DO HORÁRIO
// ======================================

$arquivoHorario = $pastaUsuario . '/horario.json';

$estruturaHorarioPadrao = [
    'html' => ''
];

if (!file_exists($arquivoHorario)) {
    file_put_contents(
        $arquivoHorario,
        json_encode(
            $estruturaHorarioPadrao,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        )
    );
}

$horarioData = json_decode(
    file_get_contents($arquivoHorario),
    true
);

if (!is_array($horarioData)) {
    $horarioData = $estruturaHorarioPadrao;
}

if (!isset($horarioData['html'])) {
    $horarioData['html'] = '';
}

// ======================================
// MATÉRIAS DO USUÁRIO
// ======================================

$arquivoMaterias =
    $pastaUsuario . '/materias.json';

$materiasData = [];

if (file_exists($arquivoMaterias)) {

    $conteudoMaterias =
        file_get_contents($arquivoMaterias);

    $materiasJson =
        json_decode(
            $conteudoMaterias,
            true
        );

    if (is_array($materiasJson)) {

        // Caso materias.json seja:
        // [ {...}, {...} ]

        if (
            array_keys($materiasJson) ===
            range(0, count($materiasJson) - 1)
        ) {
            $materiasData =
                $materiasJson;
        }

        // Caso materias.json seja:
        // { "materias": [ {...}, {...} ] }

        elseif (
            isset($materiasJson['materias']) &&
            is_array($materiasJson['materias'])
        ) {
            $materiasData =
                $materiasJson['materias'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Agenda e Horário - FOAG</title>

    <link rel="stylesheet" href="bloco.css">
    <link rel="stylesheet" href="agenda.css?v=20261005-excluir-visivel-final">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="dark_agend.css?v=20261004-2">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ACESSIBILIDADE GLOBAL -->


    <script src="../m.escuro/dark-mode.js"></script>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>

    <script>
        window.AGENDA_DATA = <?= json_encode(
            $agendaData,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.MATERIAS_DATA = <?= json_encode(
            $materiasData,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.AGENDA_SAVE_URL =
            "salvar_agenda.php";

        window.HORARIO_HTML = <?= json_encode(
            $horarioData['html'] ?? '',
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

        window.HORARIO_SAVE_URL =
            "salvar_agenda.php";
    </script>


<style>
    /* ======================================
       ESTRUTURA: CONTEÚDO + FOOTER
    ======================================= */

    .page-area {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        background: transparent;
    }

    .page-area > .main-content {
        flex: 1;
        width: 100%;
    }

    /* ======================================
       FOOTER FOAG
    ======================================= */

    .footer {
        width: 100%;
        margin-top: 30px;
        padding: 0;
        background: transparent;
        color: inherit;
        border-top: 1px solid #e5edf5;
        text-align: initial;
        font-size: inherit;
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

    /* ======================================
       DARK MODE — CORREÇÃO DO FUNDO
    ======================================= */

    body.dark-mode .page-area {
        background: #0f172a !important;
    }

    body.dark-mode .footer {
        background: #0f172a !important;
        border-top-color: #334155 !important;
    }

    @media (max-width: 768px) {
        .page-area {
            width: 100%;
        }

        .footer {
            margin-top: 20px;
        }

        .footer-content {
            min-height: auto;
            padding: 16px 18px;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
            text-align: center;
        }

        .footer-left {
            flex-direction: column;
            gap: 8px;
        }

        .footer-links {
            flex-wrap: wrap;
            justify-content: center;
            gap: 14px 20px;
        }
    }
</style>

    <link rel="stylesheet" href="../global/css/cursor.css">
</head>

<body>




    <!-- ======================================
         CABEÇALHO
    ======================================= -->

    <header class="cabecalho">
        FOAG

        <div class="header-icons">

  <i id="icon-configuracoes" class="fa-solid fa-gear" title="Configurações"></i>

            <i
                id="icon-perfil"
                class="fa-regular fa-user"
                title="Perfil">
            </i>

            <i
                id="icon-sair"
                class="fa-solid fa-right-from-bracket"
                title="Sair">
            </i>
        </div>
    </header>

    <div class="container">

        <!-- ======================================
             MENU
        ======================================= -->

        <nav class="menu">
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

        <!-- ======================================
             CONTEÚDO + FOOTER
        ======================================= -->

        <div class="page-area">

        <main class="main-content" id="conteudo-principal" tabindex="-1">

            <!-- ==================================
                 CABEÇALHO DA AGENDA
            =================================== -->

            <div class="agenda-page-header">
                <div class="agenda-page-title">
                    <h1>Agenda</h1>
                    <p>Veja o que precisa fazer hoje e organize seus próximos estudos.</p>
                </div>

                <div class="agenda-header-actions">
                    <button type="button" class="agenda-novo-btn" id="agenda-novo-btn">
                        <i class="fa-solid fa-plus"></i> Novo
                    </button>
                    <div class="agenda-auto-save" id="status-salvamento" data-status="salvo">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Salvo</span>
                    </div>
                </div>
            </div>

            <div class="agenda-novo-menu" id="agenda-novo-menu" hidden>
                <button type="button" data-agenda-action="tarefa"><i class="fa-solid fa-list-check"></i> Tarefa</button>
                <button type="button" data-agenda-action="lembrete"><i class="fa-solid fa-bell"></i> Lembrete</button>
                <button type="button" data-agenda-action="anotacao"><i class="fa-solid fa-note-sticky"></i> Anotação</button>
            </div>

            <section class="agenda-resumo" aria-label="Resumo da agenda">
                <article class="agenda-resumo-card">
                    <span class="agenda-resumo-icone"><i class="fa-solid fa-list-check"></i></span>
                    <div><strong id="resumo-pendentes">0</strong><span>Tarefas pendentes</span></div>
                </article>
                <article class="agenda-resumo-card">
                    <span class="agenda-resumo-icone"><i class="fa-solid fa-calendar-day"></i></span>
                    <div><strong id="resumo-hoje">0</strong><span>Para hoje</span></div>
                </article>
                <article class="agenda-resumo-card">
                    <span class="agenda-resumo-icone alerta"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    <div><strong id="resumo-atrasadas">0</strong><span>Atrasadas</span></div>
                </article>
                <article class="agenda-resumo-card">
                    <span class="agenda-resumo-icone"><i class="fa-solid fa-note-sticky"></i></span>
                    <div><strong id="resumo-notas"><?= $totalNotas ?></strong><span>Anotações</span></div>
                </article>
            </section>

            <section class="agenda-hoje-card">
                <div class="agenda-section-header">
                    <div>
                        <span class="agenda-eyebrow">Seu dia</span>
                        <h2>Hoje</h2>
                    </div>
                    <span class="agenda-hoje-data" id="agenda-hoje-data"></span>
                </div>
                <div class="agenda-hoje-lista" id="agenda-hoje-lista">
                    <div class="agenda-vazio">Nada para hoje. Aproveite para adiantar alguma coisa ✨</div>
                </div>
            </section>

            <!-- ==================================
                 HORÁRIO RECOLHÍVEL
            =================================== -->

            <!-- ==================================
                 TAREFAS + LEMBRETES
            =================================== -->

            <div class="agenda-paineis">

                <section class="agenda-painel" id="tarefas">
                    <div class="agenda-painel-header">
                        <div class="agenda-painel-titulo">
                            <i class="fa-solid fa-list-check"></i>
                            Tarefas
                        </div>

                        <div class="agenda-painel-acoes">
                            <span class="agenda-painel-contador" id="contador-tarefas">
                                <?= $totalTarefas ?>
                            </span>
                            <button type="button" class="agenda-excluir-toggle" id="excluir-tarefas-toggle">
                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                Excluir
                            </button>
                            <button type="button" class="agenda-expandir-toggle" id="expandir-tarefas" hidden aria-expanded="false">
                                <i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i>
                                Expandir
                            </button>
                        </div>
                    </div>

                    <div class="agenda-tabela-wrapper">
                        <table id="tabela-tarefas">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tarefa</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody id="lista-tarefas"></tbody>
                        </table>
                    </div>

                    <div class="agenda-selecao-acoes" id="acoes-excluir-tarefas" hidden>
                        <button type="button" class="agenda-selecao-cancelar" id="cancelar-excluir-tarefas">Cancelar</button>
                        <button type="button" class="agenda-selecao-confirmar" id="confirmar-excluir-tarefas">
                            <i class="fa-solid fa-trash-can"></i> Excluir selecionadas
                        </button>
                    </div>

                    <button type="button" id="add-tarefa">
                        <i class="fa-solid fa-plus"></i>
                        Adicionar tarefa
                    </button>
                </section>

                <section class="agenda-painel" id="lembretes">
                    <div class="agenda-painel-header">
                        <div class="agenda-painel-titulo">
                            <i class="fa-solid fa-bell"></i>
                            Lembretes
                        </div>

                        <div class="agenda-painel-acoes">
                            <span class="agenda-painel-contador" id="contador-lembretes">
                                <?= $totalLembretes ?>
                            </span>
                            <button type="button" class="agenda-excluir-toggle" id="excluir-lembretes-toggle">
                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                Excluir
                            </button>
                            <button type="button" class="agenda-expandir-toggle" id="expandir-lembretes" hidden aria-expanded="false">
                                <i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i>
                                Expandir
                            </button>
                        </div>
                    </div>

                    <div class="agenda-tabela-wrapper">
                        <table id="tabela-nao-esquecer">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Lembrete</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody id="lista-nao-esquecer"></tbody>
                        </table>
                    </div>

                    <div class="agenda-selecao-acoes" id="acoes-excluir-lembretes" hidden>
                        <button type="button" class="agenda-selecao-cancelar" id="cancelar-excluir-lembretes">Cancelar</button>
                        <button type="button" class="agenda-selecao-confirmar" id="confirmar-excluir-lembretes">
                            <i class="fa-solid fa-trash-can"></i> Excluir selecionados
                        </button>
                    </div>

                    <button type="button" id="add-nao-esquecer">
                        <i class="fa-solid fa-plus"></i>
                        Adicionar lembrete
                    </button>
                </section>
            </div>

            <section class="horario-agenda-card">

                <div class="horario-agenda-header">
                    <button
                        type="button"
                        class="horario-toggle"
                        id="toggle-horario"
                        aria-expanded="false"
                        aria-controls="horario-conteudo">

                        <span class="horario-toggle-texto">
                            <span class="horario-toggle-icone">
                                <i class="fa-solid fa-clock"></i>
                            </span>

                            <span class="horario-toggle-copy">
                                <strong>Horário semanal</strong>
                                <span>Clique para visualizar ou editar sua grade.</span>
                            </span>
                        </span>

                        <i class="fa-solid fa-chevron-down horario-toggle-seta"></i>
                    </button>
                </div>

                <div id="horario-conteudo" class="horario-conteudo" hidden>
                    <div class="horario-resumo-topo">
                        <div class="horario-proxima-aula" id="horario-proxima-aula">
                            <span class="horario-proxima-label">Próxima aula</span>
                            <strong id="horario-proxima-titulo">Nenhuma aula encontrada</strong>
                            <span id="horario-proxima-meta">Cadastre ou edite seu horário.</span>
                        </div>

                        <div class="horario-acoes-topo">
                            <button type="button" id="btn-editar-horario" class="horario-btn-secundario">
                                <i class="fa-solid fa-pen"></i>
                                Editar horário
                            </button>

                            <button type="button" id="btn-menu-horario" class="horario-btn-icone" aria-label="Mais opções" title="Mais opções">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>

                            <div class="horario-menu-opcoes" id="horario-menu-opcoes" hidden>
                                <button type="button" onclick="salvarComoPDF()">
                                    <i class="fa-solid fa-file-pdf"></i>
                                    Exportar PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="horario-visual" id="horario-visual"></div>

                    <div class="horario-editor" id="horario-editor" hidden>
                    <div class="horario-editor-bar">
                        <strong>Editar horário semanal</strong>
                        <div class="horario-editor-acoes">
                            <button type="button" id="btn-fechar-edicao-horario" class="horario-btn-secundario">Concluir edição</button>
                        </div>
                    </div>

                    <div class="horario-table-wrapper">
                        <table id="scheduleTable">
                            <thead>
                                <tr>
                                    <th>Horário</th>
                                    <th>Segunda-feira</th>
                                    <th>Terça-feira</th>
                                    <th>Quarta-feira</th>
                                    <th>Quinta-feira</th>
                                    <th>Sexta-feira</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td class="horario-hora">
                                        <div class="horario-faixa">
                                            <input type="time" class="input-horario input-horario-inicio" step="300" aria-label="Horário de início da aula">
                                            <span class="horario-separador">às</span>
                                            <input type="time" class="input-horario input-horario-fim" step="300" aria-label="Horário de término da aula">
                                        </div>
                                    </td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                </tr>

                                <tr>
                                    <td class="horario-hora">
                                        <div class="horario-faixa">
                                            <input type="time" class="input-horario input-horario-inicio" step="300" aria-label="Horário de início da aula">
                                            <span class="horario-separador">às</span>
                                            <input type="time" class="input-horario input-horario-fim" step="300" aria-label="Horário de término da aula">
                                        </div>
                                    </td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                </tr>

                                <tr>
                                    <td class="horario-hora">
                                        <div class="horario-faixa">
                                            <input type="time" class="input-horario input-horario-inicio" step="300" aria-label="Horário de início da aula">
                                            <span class="horario-separador">às</span>
                                            <input type="time" class="input-horario input-horario-fim" step="300" aria-label="Horário de término da aula">
                                        </div>
                                    </td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="horario-buttons">
                        <button type="button" onclick="adicionarLinha()">
                            <i class="fa-solid fa-plus"></i>
                            Aula
                        </button>

                        <button type="button" onclick="adicionarIntervalo()">
                            <i class="fa-solid fa-mug-hot"></i>
                            Intervalo
                        </button>

                        <button type="button" id="btn-excluir-linha-horario" class="horario-btn-perigo horario-btn-desativado" aria-disabled="true">
                            <i class="fa-solid fa-trash-can"></i>
                            Excluir linha selecionada
                        </button>

                    </div>
                    </div>
                </div>
            </section>


            <!-- ==================================
                 NOTAS
            =================================== -->

            <section id="notas" class="agenda-notas-card">
                <div class="agenda-notas-header">
                    <h2>
                        <i class="fa-solid fa-note-sticky"></i>
                        Anotações
                    </h2>

                    <div class="agenda-notas-actions">
                        <span class="agenda-painel-contador" id="contador-notas">
                            <?= $totalNotas ?>
                        </span>
                        <button type="button" class="btn-nova-anotacao" id="btn-nova-anotacao">
                            <i class="fa-solid fa-plus"></i> Nova anotação
                        </button>
                    </div>
                </div>

                <div class="nota-editor" id="nota-editor" hidden>
                <label for="nota-texto" class="sr-only">
                    Escreva sua nota
                </label>

                <textarea
                    id="nota-texto"
                    placeholder="Escreva suas notas aqui..."
                    wrap="soft"></textarea>

                <button type="button" id="btn-salvar-nota">
                    Salvar anotação
                </button>
                </div>

                <div id="saved-notes">
                    <h2>Suas anotações</h2>

                    <div class="notas-container" id="noteList">
                        <!-- Notas inseridas pelo JavaScript -->
                    </div>
                </div>
            </section>
        </main>

        <footer class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <span class="footer-brand">FOAG</span>

                    <nav class="footer-links">
                        <a href="../sobre/sobre.php">Sobre</a>
                        <a href="../contato/contato.php">Contato</a>
                        <a href="../privacidade/privacidade.php">Privacidade</a>
                    </nav>
                </div>

                <span class="footer-copy">
                    © <?= date('Y') ?> FOAG
                </span>
            </div>
        </footer>

        </div>
    </div>

    <!-- ======================================
         MODAL: NOMEAR NOTA
    ======================================= -->

    <div
        id="modal-nomear-nota"
        class="modal-nomear-nota"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-nomear">

        <div class="modal-content">
            <h3 id="titulo-modal-nomear">Dê um nome para sua nota</h3>

            <input
                type="text"
                id="nome-nota"
                placeholder="Digite um título para sua nota..."
                maxlength="50"
            >

            <div class="modal-buttons">
                <button id="confirmar-nome-nota">
                    Salvar
                </button>

                <button id="cancelar-nome-nota">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================
         MODAL: LOGOUT
    ======================================= -->

    <div
        id="logout-modal"
        class="modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-logout">

        <div class="modal-content">
            <h3 id="titulo-logout">Ah... já vai?</h3>

            <h4>
                Tem certeza de que deseja sair?
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

    <!-- ======================================
         MODAL: EXCLUSÃO
    ======================================= -->

    <div
        id="modal-excluir"
        class="modal-excluir"
        role="dialog"
        aria-modal="true"
        aria-labelledby="excluir-titulo"
        aria-describedby="excluir-mensagem">

        <div class="modal-content">

            <div class="excluir-icon">
                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
            </div>

            <h3 id="excluir-titulo">
                Excluir Item
            </h3>

            <p id="excluir-mensagem">
                Tem certeza que deseja excluir este item?
            </p>

            <div class="modal-buttons">
                <button
                    id="confirmar-exclusao"
                    class="btn-excluir-confirmar">

                    Excluir
                </button>

                <button
                    id="cancelar-exclusao"
                    class="btn-cancelar">

                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================
         MODAL: HORÁRIO SALVO
    ======================================= -->

    <div
        id="modal-sucesso"
        class="modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-sucesso">

        <div class="modal-content modal-sucesso-content">
            <h3 id="titulo-sucesso">Horário salvo!</h3>

            <p>
                Suas alterações foram registradas com sucesso.
            </p>

            <button
                id="fechar-modal"
                class="btn-modal">

                OK
            </button>
        </div>
    </div>

    <!-- ======================================
         MODAL: FOGi
    ======================================= -->

    <div id="fogi-modal" role="dialog" aria-modal="true" aria-labelledby="titulo-fogi">
        <div class="fogi-container">

            <div class="fogi-header">
                <span id="titulo-fogi">
                    FOGi — Assistente de Estudos
                </span>

                <button id="fogi-close">
                    Fechar
                </button>
            </div>

            <iframe
                id="fogi-iframe"
                src="about:blank"
                title="FOGi - Assistente de Estudos">
            </iframe>
        </div>
    </div>

    <!-- ======================================
         FUNÇÕES GERAIS
    ======================================= -->

    <script>
        // Modal de sucesso do horário
        window.abrirModalSucesso = function () {
            const modal =
                document.getElementById("modal-sucesso");

            if (modal) {
                modal.style.display = "flex";
            }
        };
    </script>

    <script src="./agendar.js?v=<?= time() ?>"></script>

    <script>
        document.addEventListener(
            "DOMContentLoaded",
            function () {

                // ==========================
                // FOGi
                // ==========================

                const fogiBtn =
                    document.getElementById("icon-fogi");

                const fogiModal =
                    document.getElementById("fogi-modal");

                const fogiFrame =
                    document.getElementById("fogi-iframe");

                const fogiClose =
                    document.getElementById("fogi-close");

                if (
                    fogiBtn &&
                    fogiModal &&
                    fogiFrame
                ) {
                    fogiBtn.addEventListener(
                        "click",
                        function () {
                            fogiFrame.src =
                                "http://127.0.0.1:5000";

                            fogiModal.style.display =
                                "flex";

                            document.body.style.overflow =
                                "hidden";
                        }
                    );
                }

                if (
                    fogiClose &&
                    fogiModal &&
                    fogiFrame
                ) {
                    fogiClose.addEventListener(
                        "click",
                        function () {
                            fogiModal.style.display =
                                "none";

                            fogiFrame.src =
                                "about:blank";

                            document.body.style.overflow =
                                "";
                        }
                    );
                }

                window.addEventListener(
                    "message",
                    function (event) {
                        if (
                            event.data &&
                            event.data.type === "FOGI_CLOSE"
                        ) {
                            fogiModal.style.display =
                                "none";

                            fogiFrame.src =
                                "about:blank";

                            document.body.style.overflow =
                                "";
                        }
                    }
                );

                // ==========================
                // MODAL DE SUCESSO
                // ==========================

                const modalSucesso =
                    document.getElementById(
                        "modal-sucesso"
                    );

                const btnFecharModal =
                    document.getElementById(
                        "fechar-modal"
                    );

                if (
                    btnFecharModal &&
                    modalSucesso
                ) {
                    btnFecharModal.addEventListener(
                        "click",
                        function () {
                            modalSucesso.style.display =
                                "none";
                        }
                    );

                    modalSucesso.addEventListener(
                        "click",
                        function (event) {
                            if (
                                event.target ===
                                modalSucesso
                            ) {
                                modalSucesso.style.display =
                                    "none";
                            }
                        }
                    );
                }
            }
        );
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleHorario = document.getElementById('toggle-horario');
            const horarioConteudo = document.getElementById('horario-conteudo');

            if (toggleHorario && horarioConteudo) {
                toggleHorario.addEventListener('click', function () {
                    const aberto = toggleHorario.getAttribute('aria-expanded') === 'true';

                    toggleHorario.setAttribute('aria-expanded', aberto ? 'false' : 'true');
                    horarioConteudo.hidden = aberto;
                });
            }

            const listaTarefas = document.getElementById('lista-tarefas');
            const listaLembretes = document.getElementById('lista-nao-esquecer');
            const noteList = document.getElementById('noteList');


            const contadorTarefas = document.getElementById('contador-tarefas');
            const contadorLembretes = document.getElementById('contador-lembretes');
            const contadorNotas = document.getElementById('contador-notas');

            function atualizarResumoAgenda() {
                const tarefas = listaTarefas ? listaTarefas.rows.length : 0;
                const lembretes = listaLembretes ? listaLembretes.rows.length : 0;
                const notas = noteList ? noteList.querySelectorAll('.nota-item').length : 0;


                if (contadorTarefas) contadorTarefas.textContent = tarefas;
                if (contadorLembretes) contadorLembretes.textContent = lembretes;
                if (contadorNotas) contadorNotas.textContent = notas;
            }

            const observer = new MutationObserver(atualizarResumoAgenda);

            if (listaTarefas) observer.observe(listaTarefas, { childList: true });
            if (listaLembretes) observer.observe(listaLembretes, { childList: true });
            if (noteList) observer.observe(noteList, { childList: true, subtree: true });

            atualizarResumoAgenda();
        });
    </script>

    <script src="../configuracoes/aparencia.js?v=5"></script>
<script src="../configuracoes/acessibilidade.js?v=25" defer></script>

    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>