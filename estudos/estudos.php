<?php
require_once __DIR__ . '/../core/usuario.php';
require_once __DIR__ . '/../core/json.php';

$contextoUsuario = foag_contexto_usuario('../login/index.php');
$codigoUsuario = $contextoUsuario['codigoUsuario'];
$baseJsonDir = $contextoUsuario['baseJsonDir'];
$pastaUsuario = $contextoUsuario['pastaUsuario'];

$current = basename($_SERVER['PHP_SELF']);

if (!is_dir($pastaUsuario)) {
    exit('Pasta do usuário não encontrada.');
}

// ==============================
// ARQUIVO DE MATÉRIAS
// ==============================

$arquivoMaterias = $pastaUsuario . '/materias.json';

// Cria o arquivo caso ainda não exista
if (!file_exists($arquivoMaterias)) {
    $estadoInicial = [
        'materias' => []
    ];

    file_put_contents(
        $arquivoMaterias,
        json_encode(
            $estadoInicial,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ),
        LOCK_EX
    );
}

// ==============================
// CARREGAR MATÉRIAS
// ==============================

$materiasData = json_decode(
    file_get_contents($arquivoMaterias),
    true
);

if (!is_array($materiasData)) {
    $materiasData = [
        'materias' => []
    ];
}

if (
    !isset($materiasData['materias']) ||
    !is_array($materiasData['materias'])
) {
    $materiasData['materias'] = [];
}

// ======================================
// POMODORO
// ======================================

$arquivoPomodoro =
    $pastaUsuario . '/pomodoro.json';

$pomodoroData = [
    'sessions' => []
];

if (file_exists($arquivoPomodoro)) {

    $dadosPomodoro =
        json_decode(
            file_get_contents($arquivoPomodoro),
            true
        );

    if (is_array($dadosPomodoro)) {

        $pomodoroData =
            $dadosPomodoro;

    }

}

if (
    !isset($pomodoroData['sessions']) ||
    !is_array($pomodoroData['sessions'])
) {

    $pomodoroData['sessions'] =
        [];

}


// ======================================
// FLASHCARDS
// ======================================

$arquivoFlashcards =
    $pastaUsuario . '/flashcards.json';

$flashcardsData = [
    'baralhos' => []
];

if (file_exists($arquivoFlashcards)) {

    $dadosFlashcards =
        json_decode(
            file_get_contents($arquivoFlashcards),
            true
        );

    if (is_array($dadosFlashcards)) {

        $flashcardsData =
            $dadosFlashcards;

    }

}

if (
    !isset($flashcardsData['baralhos']) ||
    !is_array($flashcardsData['baralhos'])
) {

    $flashcardsData['baralhos'] =
        [];

}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG – Estudos</title>

    <link rel="stylesheet" href="estudos.css">
    <link rel="stylesheet" href="dark_estudos.css">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="../global/css/cursor.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script src="../m.escuro/dark-mode.js"></script>

<script>

    window.MATERIAS_DATA =
        <?= json_encode(
            $materiasData,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;


    window.POMODORO_DATA =
        <?= json_encode(
            $pomodoroData,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;


    window.FLASHCARDS_DATA =
        <?= json_encode(
            $flashcardsData,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;


    window.MATERIAS_SAVE_URL =
        FOAG_CONFIG.endpoints.estudosMateriaSalvar;


    window.MATERIAS_UPDATE_URL =
        FOAG_CONFIG.endpoints.estudosMateriaEditar;


    window.MATERIAS_DELETE_URL =
        FOAG_CONFIG.endpoints.estudosMateriaExcluir;

    </script>

  <style>
    .subject-card-actions {
      position: absolute;
      top: 12px;
      right: 12px;
      display: flex;
      gap: 8px;
      z-index: 2;
    }

    .subject-edit-btn,
    .subject-delete-btn {
      width: 34px;
      height: 34px;
      border: 0;
      border-radius: 9px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .subject-edit-btn {
      background: rgba(56, 165, 255, 0.12);
      color: #238ddf;
    }

    .subject-edit-btn:hover {
      background: rgba(56, 165, 255, 0.22);
    }
  </style>


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

    <link rel="stylesheet" href="../global/css/layout.css?v=<?= time() ?>">
</head>
<body>
  <?php include __DIR__ . '/../components/header.php'; ?>

  <div class="container">
    <?php include __DIR__ . '/../components/menu.php'; ?>

    <div class="page-area">

    <main class="conteudo">
      <section class="page-header">
        <div>
          <span class="page-eyebrow">Área de estudos</span>
          <h1>Estudos</h1>
          <p>Organize seus estudos, retome de onde parou e acompanhe sua evolução.</p>
        </div>
        <button class="btn" id="open-subject-modal">
          <i class="fa-solid fa-plus"></i> Nova matéria
        </button>
      </section>

      <section class="study-overview" aria-label="Resumo de estudos">
        <article class="continue-card" id="continue-card">
          <div class="continue-main">
            <span class="overview-kicker"><i class="fa-solid fa-bolt"></i> Continue estudando</span>
            <div class="continue-subject-row">
              <div class="continue-icon" id="continue-icon"><i class="fa-solid fa-book-open"></i></div>
              <div>
                <h2 id="continue-subject">Comece sua primeira sessão</h2>
                <p id="continue-detail">Escolha uma matéria e use o Pomodoro para registrar seu progresso.</p>
              </div>
            </div>
            <a class="btn continue-btn" id="continue-action" href="pomodoro/pomodoro.php">
              <i class="fa-solid fa-play"></i> Estudar agora
            </a>
          </div>

          <div class="weekly-goal-card">
            <div class="goal-header">
              <div>
                <span>Meta semanal</span>
                <strong id="weekly-goal-label">0h de 5h</strong>
              </div>
              <button type="button" class="goal-edit-btn" id="edit-weekly-goal" title="Editar meta semanal" aria-label="Editar meta semanal">
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
            </div>
            <div class="goal-progress" aria-hidden="true"><span id="weekly-goal-progress"></span></div>
            <div class="goal-footer">
              <span id="weekly-goal-percent">0% concluído</span>
              <span id="weekly-study-days"><i class="fa-solid fa-fire"></i> 0 dias ativos</span>
            </div>
          </div>
        </article>
      </section>

      <section class="stats-grid" aria-label="Estatísticas da semana">
        <article class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
          <div><span class="stat-label">Tempo estudado</span><strong id="stat-study-time">0h 00min</strong><small>Esta semana</small></div>
        </article>
        <article class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
          <div><span class="stat-label">Sessões</span><strong id="stat-sessions">0</strong><small>Esta semana</small></div>
        </article>
        <article class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-book-open"></i></div>
          <div><span class="stat-label">Matérias</span><strong id="stat-subjects">0</strong><small>Matérias cadastradas</small></div>
        </article>
        <article class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-fire"></i></div>
          <div><span class="stat-label">Sequência</span><strong id="stat-streak">0 dias</strong><small>Dias seguidos estudando</small></div>
        </article>
      </section>

      <section class="content-section methods-section">
        <div class="section-heading">
          <div><h2>Métodos de estudo</h2><p>Escolha uma ferramenta e comece sem perder tempo.</p></div>
        </div>
        <div class="methods-grid">
          <a class="method-card" href="flashcards/flashcards.php">
            <div class="method-icon"><i class="fa-solid fa-layer-group"></i></div>
            <div class="method-info"><h3>Flashcards</h3><p id="method-flashcards-info">Revise conteúdos com cartões.</p><span class="method-link">Abrir <i class="fa-solid fa-arrow-right"></i></span></div>
          </a>
          <a class="method-card" href="pomodoro/pomodoro.php">
            <div class="method-icon"><i class="fa-solid fa-stopwatch"></i></div>
            <div class="method-info"><h3>Pomodoro</h3><p id="method-pomodoro-info">Organize seus períodos de foco.</p><span class="method-link">Abrir <i class="fa-solid fa-arrow-right"></i></span></div>
          </a>
          <a class="method-card coming-soon" href="#" data-coming-soon="Quiz">
            <span class="soon-badge">Em breve</span><div class="method-icon"><i class="fa-solid fa-circle-question"></i></div>
            <div class="method-info"><h3>Quiz</h3><p>Teste seus conhecimentos.</p><span class="method-link">Conhecer <i class="fa-solid fa-arrow-right"></i></span></div>
          </a>
          <a class="method-card coming-soon" href="#" data-coming-soon="Revisão">
            <span class="soon-badge">Em breve</span><div class="method-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
            <div class="method-info"><h3>Revisão</h3><p>Retome conteúdos importantes.</p><span class="method-link">Conhecer <i class="fa-solid fa-arrow-right"></i></span></div>
          </a>
        </div>
      </section>

      <section class="content-section subjects-section">
        <div class="section-heading subjects-heading">
          <div><h2>Minhas matérias</h2><p>Veja seu progresso e acesse rapidamente o que quer estudar.</p></div>
          <div class="subjects-tools">
            <label class="subject-search"><i class="fa-solid fa-magnifying-glass"></i><input id="subject-search" type="search" placeholder="Buscar matéria" aria-label="Buscar matéria"></label>
            <select id="subject-sort" class="subject-sort" aria-label="Ordenar matérias">
              <option value="recent">Recentes</option><option value="studied">Mais estudadas</option><option value="az">A–Z</option>
            </select>
            <button class="btn secondary-outline" id="open-subject-modal-secondary"><i class="fa-solid fa-plus"></i> Adicionar</button>
          </div>
        </div>
        <div id="subjects-empty" class="empty-state">
          <div class="empty-icon"><i class="fa-solid fa-book-open-reader"></i></div><h3>Nenhuma matéria cadastrada ainda</h3>
          <p>Adicione sua primeira matéria para começar a organizar seus estudos.</p>
          <button class="btn" id="open-subject-modal-empty"><i class="fa-solid fa-plus"></i> Adicionar primeira matéria</button>
        </div>
        <div id="subjects-grid" class="subjects-grid" hidden></div>
        <div id="subjects-no-results" class="subjects-no-results" hidden><i class="fa-solid fa-magnifying-glass"></i><span>Nenhuma matéria encontrada.</span></div>
      </section>

      <section class="study-bottom-grid">
        <section class="content-section review-section">
          <div class="section-heading"><div><h2>Para revisar</h2><p>Baralhos que você pode retomar agora.</p></div><a href="flashcards/flashcards.php" class="text-link">Ver flashcards <i class="fa-solid fa-arrow-right"></i></a></div>
          <div id="review-list" class="review-list"></div>
        </section>
        <section class="content-section activity-section">
          <div class="section-heading"><div><h2>Atividade recente</h2><p>Seus últimos registros de estudo.</p></div></div>
          <div id="activity-list" class="activity-list"></div>
        </section>
      </section>
    </main>
    <?php include __DIR__ . '/../components/footer.php'; ?>

    </div>
  </div>
<div id="subject-modal" class="modal" aria-hidden="true">
    <div class="modal-content subject-modal-content">
      <button class="modal-close" id="close-subject-modal" aria-label="Fechar">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <div class="modal-title">
        <div class="modal-title-icon"><i class="fa-solid fa-book"></i></div>
        <div>
          <h3 id="subject-modal-title">Nova matéria</h3>
          <p id="subject-modal-description">Escolha um nome, uma cor e um ícone para identificar a matéria.</p>
        </div>
      </div>

      <form id="subject-form">
        <label class="field-group">
          <span class="lbl">Nome da matéria</span>
          <input class="input" id="subject-name" type="text" maxlength="50" placeholder="Ex.: Matemática" required>
        </label>

        <div class="form-group">
  <label for="subject-color">Cor da matéria</label>

  <div class="color-picker-area">
        <input
        type="color"
        id="subject-color"
        name="subject-color"
        value="#38a5ff"
        class="color-picker"
        >

        <span>Escolha uma cor</span>
    </div>
    </div>

        <label class="field-group">
          <span class="lbl">Ícone</span>
          <select class="select" id="subject-icon">
            <option value="fa-circle-question">Não definido (?)</option>
            <option value="fa-book">Livro</option>
            <option value="fa-calculator">Matemática</option>
            <option value="fa-flask">Ciências / Química</option>
            <option value="fa-dna">Biologia</option>
            <option value="fa-globe">Geografia</option>
            <option value="fa-landmark">História</option>
            <option value="fa-language">Idiomas</option>
            <option value="fa-laptop-code">Tecnologia</option>
          </select>
        </label>

        <div class="modal-buttons subject-modal-buttons">
          <button type="button" class="btn secondary" id="cancel-subject-modal">Cancelar</button>
          <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Adicionar</button>
        </div>
      </form>
    </div>
  </div>

  <div id="logout-modal" class="modal">
    <div class="modal-content">
      <h3>Ah... já vai?</h3>
      <h4>Tem certeza que deseja sair?</h4>
      <div class="modal-buttons">
        <button id="confirm-logout" class="btn">Sim</button>
        <button id="cancel-logout" class="btn secondary">Cancelar</button>
      </div>
    </div>
  </div>

  <!-- ==========================================
     MODAL EXCLUIR MATÉRIA
========================================== -->

<div
  id="delete-subject-modal"
  class="modal"
  aria-hidden="true"
>

  <div class="modal-content delete-subject-content">
    <div class="delete-subject-icon"> <i class="fa-regular fa-trash-can"></i> </div>
    <h3> Excluir matéria?</h3>
    <p>Você está prestes a excluir <strong id="delete-subject-name"></strong>.</p>
    <div class="delete-warning"> <i class="fa-solid fa-triangle-exclamation"></i>
      <span>
        Todos os baralhos, cartões e revisões dessa matéria
        também serão excluídos permanentemente.
      </span>
    </div>

    <div class="delete-subject-actions">

      <button
        type="button"
        id="cancel-delete-subject"
        class="btn secondary"
      >
        Cancelar
      </button>

      <button
        type="button"
        id="confirm-delete-subject"
        class="btn delete-confirm-btn"
      >
        <i class="fa-regular fa-trash-can"></i>
        Excluir matéria
      </button>

    </div>

  </div>

</div>

  <div id="toast" class="toast" role="status" aria-live="polite"></div>
<script src="../global/js/cursor.js?v=<?= time() ?>"></script>
<script defer src="estudos.js?v=<?= time() ?>"></script>
<script src="../configuracoes/aparencia.js?v=5"></script>
<script src="../configuracoes/acessibilidade.js?v=25" defer></script>
</body>
</html>