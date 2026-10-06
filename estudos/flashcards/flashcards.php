<?php

session_start();

if (empty($_SESSION['codigo_usuario'])) {
    header('Location: ../../login/index.php');
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];


// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir =
    __DIR__ . '/../../json/usuarios';

$pastaUsuario =
    $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exit('Pasta do usuário não encontrada.');
}


// ======================================
// CARREGAR materias.json
// ======================================

$arquivoMaterias =
    $pastaUsuario . '/materias.json';

$materiasData = [
    'materias' => []
];

if (file_exists($arquivoMaterias)) {

    $conteudoMaterias =
        file_get_contents(
            $arquivoMaterias
        );

    $dadosMaterias =
        json_decode(
            $conteudoMaterias,
            true
        );


    if (
        is_array($dadosMaterias) &&
        isset($dadosMaterias['materias']) &&
        is_array($dadosMaterias['materias'])
    ) {

        $materiasData =
            $dadosMaterias;

    }

}


// ======================================
// CARREGAR flashcards.json
// ======================================

$arquivoFlashcards =
    $pastaUsuario . '/flashcards.json';


if (!file_exists($arquivoFlashcards)) {

    $estadoInicial = [
        'baralhos' => []
    ];


    file_put_contents(
        $arquivoFlashcards,

        json_encode(
            $estadoInicial,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE
        ),

        LOCK_EX
    );

}


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


if (
    !isset($flashcardsData['baralhos']) ||
    !is_array($flashcardsData['baralhos'])
) {

    $flashcardsData['baralhos'] =
        [];

}


$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG – Flashcards</title>

    <link rel="stylesheet" href="flashcards.css">
    <link rel="stylesheet" href="dark_flash.css">
    <link rel="stylesheet" href="../../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="../../global/css/cursor.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script src="../../m.escuro/dark-mode.js"></script>

<script>

        window.MATERIAS_DATA =
            <?= json_encode(
                $materiasData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        window.FLASHCARDS_DATA =
            <?= json_encode(
                $flashcardsData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

            window.CARTAO_SAVE_URL =
            'salvar_cartao.php';

        window.CARTAO_EDIT_URL =
            'editar_cartao.php';
        
        window.CARTAO_DELETE_URL =
            'excluir_cartao.php';

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

</head>


<body>

<header class="cabecalho">

    FOAG

    <div class="header-icons">

        <i
            id="icon-configuracoes"
            class="fa-solid fa-gear"
            title="Configurações"
        ></i>

        <i
            id="icon-perfil"
            class="fa-regular fa-user"
            title="Perfil"
        ></i>

        <i
            id="icon-sair"
            class="fa-solid fa-right-from-bracket"
            title="Sair"
        ></i>

    </div>

</header>


<div class="container">

    <!-- MENU -->

    <nav class="menu">
        <a href="../../inicioo/inicio.php" class="<?= $current === 'inicio.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> Início
        </a>

        <a href="../../estudos/estudos.php" class="active">
            <i class="fa-solid fa-graduation-cap"></i> Estudos
        </a>

        <a href="../../bloco/agenda.php" class="<?= $current === 'agenda.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-book"></i> Agenda
        </a>

        <a href="../../calend/calendario.php" class="<?= $current === 'calendario.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-days"></i> Calendário
        </a>

        <a href="../../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-check-double"></i> Boletim
        </a>

        <a href="../../comunidade/comunidade.php" class="<?= $current === 'comunidade.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-comments"></i> Comunidade
        </a>

        <a href="../../rank/rank.php" class="<?= $current === 'rank.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-trophy"></i> Ranking
        </a>

        <a href="../../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-store"></i> Loja
        </a>
        </nav>

    <div class="page-area">


    <!-- CONTEÚDO -->

    <main class="conteudo">

        <div class="flashcards-wrapper">


            <!-- VOLTAR -->

            <div class="flashcards-nav">

                <a
                    href="../estudos.php"
                    class="back-studies"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Voltar para Estudos

                </a>

            </div>


            <!-- TÍTULO -->

            <section class="flashcards-header">

                <div>

                    <span class="page-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>

                    <div>

                        <h1>Flashcards</h1>

                        <p>
                            Crie seus cartões e revise seus conteúdos.
                        </p>

                    </div>

                </div>

            </section>


            <!-- ESTATÍSTICAS -->

            <section class="stats-grid">

                <article class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-box"></i>
                    </div>

                    <div>

                        <span class="stat-label">
                            Baralhos
                        </span>

                        <strong id="stat-decks">
                            0
                        </strong>

                    </div>

                </article>


                <article class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-clone"></i>
                    </div>

                    <div>

                        <span class="stat-label">
                            Cartões
                        </span>

                        <strong id="stat-cards">
                            0
                        </strong>

                    </div>

                </article>


                <article class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <div>

                        <span class="stat-label">
                            Revisados hoje
                        </span>

                        <strong id="stat-reviewed">
                            0
                        </strong>

                    </div>

                </article>

            </section>


            <!-- BARALHOS -->

            <section class="card decks-section">

                <div class="section-header">

                    <div>

                        <h2>
                            Meus baralhos
                        </h2>

                        <p>
                            Organize seus flashcards por matéria.
                        </p>

                    </div>


                    <button
                        class="btn"
                        id="open-deck-modal"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Novo baralho

                    </button>

                </div>


                <!-- FILTRO -->

                <div class="filter-area">

                    <div class="field-group">

                        <label
                            class="lbl"
                            for="filter-subject"
                        >
                            Filtrar por matéria
                        </label>

                        <select
                            id="filter-subject"
                            class="select"
                        >

                            <option value="todos">
                                Todas as matérias
                            </option>

                        </select>

                    </div>

                </div>


                <!-- GRID -->

                <div
                    id="decks-grid"
                    class="decks-grid"
                    hidden
                ></div>


                <!-- ESTADO VAZIO -->

                <div
                    id="decks-empty"
                    class="empty-state"
                >

                    <div class="empty-icon">

                        <i class="fa-solid fa-layer-group"></i>

                    </div>

                    <h3>
                        Nenhum baralho criado
                    </h3>

                    <p>
                        Crie seu primeiro baralho e comece a estudar com flashcards.
                    </p>

                    <button
                        class="btn"
                        id="open-deck-modal-empty"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Criar primeiro baralho

                    </button>

                </div>

            </section>

        </div>

    </main>
    <footer class="footer">
      <div class="footer-content">
        <div class="footer-left">
          <span class="footer-brand">FOAG</span>
          <nav class="footer-links">
            <a href="../../sobre/sobre.php">Sobre</a>
            <a href="../../contato/contato.php">Contato</a>
            <a href="../../privacidade/privacidade.php">Privacidade</a>
          </nav>
        </div>
        <span class="footer-copy">© <?= date('Y') ?> FOAG</span>
      </div>
    </footer>

    </div>
  </div>
<!-- =====================================
     MODAL NOVO BARALHO
====================================== -->

<div
    id="deck-modal"
    class="modal"
    aria-hidden="true"
>

    <div class="modal-content deck-modal-content">

        <button
            type="button"
            id="close-deck-modal"
            class="modal-close"
            aria-label="Fechar"
        >

            <i class="fa-solid fa-xmark"></i>

        </button>


        <div class="modal-title">

            <div class="modal-title-icon">

                <i class="fa-solid fa-layer-group"></i>

            </div>

            <div>

                <h3>
                    Novo baralho
                </h3>

                <p>
                    Escolha uma matéria e dê um nome ao seu baralho.
                </p>

            </div>

        </div>


        <form id="deck-form">


            <!-- MATÉRIA -->

            <div class="field-group">

                <label
                    class="lbl"
                    for="deck-subject"
                >
                    Matéria
                </label>

                <select
                    id="deck-subject"
                    class="select"
                    required
                ></select>

            </div>


            <!-- NOME -->

            <div class="field-group">

                <label
                    class="lbl"
                    for="deck-name"
                >
                    Nome do baralho
                </label>

                <input
                    type="text"
                    id="deck-name"
                    class="input"
                    maxlength="60"
                    placeholder="Ex.: Química Orgânica"
                    required
                >

            </div>


            <!-- DESCRIÇÃO -->

            <div class="field-group">

                <label
                    class="lbl"
                    for="deck-description"
                >
                    Descrição
                    <span class="optional">
                        opcional
                    </span>
                </label>

                <textarea
                    id="deck-description"
                    class="input"
                    maxlength="180"
                    placeholder="Ex.: Revisão para a prova do 3º bimestre"
                ></textarea>

            </div>


            <div class="modal-actions">

                <button
                    type="button"
                    class="btn secondary"
                    id="cancel-deck-modal"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn"
                >

                    <i class="fa-solid fa-plus"></i>

                    Criar baralho

                </button>

            </div>

        </form>

    </div>

</div>


<!-- =====================================
     MODAL LOGOUT
====================================== -->

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


<!-- TOAST -->

<div
    id="toast"
    class="toast"
></div>
<script src="../../global/js/cursor.js?v=<?= time() ?>"></script>

<script defer src="flashcards.js?v=<?= time() ?>"></script>

<script src="../../configuracoes/aparencia.js?v=6"></script>
<script src="../../configuracoes/acessibilidade.js?v=26" defer></script>

</body>

</html>