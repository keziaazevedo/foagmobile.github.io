<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';


// ======================================
// LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {

    header(
        'Location: ../../login/index.php'
    );

    exit;

}


$codigoUsuario =
    $_SESSION['codigo_usuario'];


// ======================================
// PASTA DO USUÁRIO
// ======================================



if (!is_dir($pastaUsuario)) {

    exit(
        'Pasta do usuário não encontrada.'
    );

}


// ======================================
// ID DO BARALHO
// ======================================

$idBaralho =
    trim(
        $_GET['id'] ?? ''
    );


if ($idBaralho === '') {

    header(
        'Location: flashcards.php'
    );

    exit;

}


// ======================================
// FLASHCARDS.JSON
// ======================================

$arquivoFlashcards =
    $pastaUsuario . '/flashcards.json';


if (!file_exists($arquivoFlashcards)) {

    header(
        'Location: flashcards.php'
    );

    exit;

}


$flashcardsData =
    json_decode(
        file_get_contents(
            $arquivoFlashcards
        ),
        true
    );


if (
    !is_array($flashcardsData) ||
    !isset(
        $flashcardsData['baralhos']
    ) ||
    !is_array(
        $flashcardsData['baralhos']
    )
) {

    header(
        'Location: flashcards.php'
    );

    exit;

}


// ======================================
// ENCONTRAR BARALHO
// ======================================

$baralhoAtual =
    null;


foreach (
    $flashcardsData['baralhos']
    as $baralho
) {

    if (
        ($baralho['id'] ?? '') ===
        $idBaralho
    ) {

        $baralhoAtual =
            $baralho;

        break;

    }

}


if (!$baralhoAtual) {

    header(
        'Location: flashcards.php'
    );

    exit;

}


// ======================================
// GARANTIR CARTÕES
// ======================================

if (
    !isset(
        $baralhoAtual['cartoes']
    ) ||
    !is_array(
        $baralhoAtual['cartoes']
    )
) {

    $baralhoAtual['cartoes'] =
        [];

}


// ======================================
// PEGAR MATÉRIA ATUAL DO materias.json
// ======================================

$arquivoMaterias =
    $pastaUsuario . '/materias.json';

$materiaAtual =
    null;


if (file_exists($arquivoMaterias)) {

    $materiasData =
        json_decode(
            file_get_contents(
                $arquivoMaterias
            ),
            true
        );


    if (
        is_array($materiasData) &&
        isset(
            $materiasData['materias']
        ) &&
        is_array(
            $materiasData['materias']
        )
    ) {

        foreach (
            $materiasData['materias']
            as $materia
        ) {

            if (
                mb_strtolower(
                    trim(
                        $materia['nome']
                        ?? ''
                    )
                ) ===
                mb_strtolower(
                    trim(
                        $baralhoAtual['materia']
                        ?? ''
                    )
                )
            ) {

                $materiaAtual =
                    $materia;

                break;

            }

        }

    }

}


// ======================================
// COR / ÍCONE
// ======================================

$cor =
    $materiaAtual['cor']
    ??
    $baralhoAtual['cor']
    ??
    '#38a5ff';


$icone =
    $materiaAtual['icone']
    ??
    $baralhoAtual['icone']
    ??
    'fa-book';


$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="../../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars(
            $baralhoAtual['nome']
            ?? 'Baralho'
        ) ?> – FOAG
    </title>

    <link rel="stylesheet" href="baralho.css">
    <link rel="stylesheet" href="dark_flash.css">
    <link rel="stylesheet" href="../../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="../../estrelas/modal_estrelas.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../global/css/cursor.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script src="../../m.escuro/dark-mode.js"></script>

<script>

        window.BARALHO_DATA =
            <?= json_encode(
                $baralhoAtual,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;


        window.BARALHO_ID =
            <?= json_encode(
                $idBaralho,
                JSON_UNESCAPED_UNICODE
            ); ?>;


        window.BARALHO_COR =
            <?= json_encode(
                $cor
            ); ?>;


        window.BARALHO_ICONE =
            <?= json_encode(
                $icone
            ); ?>;


        window.CARTAO_SAVE_URL =
            FOAG_CONFIG.endpoints.flashcardsCartaoSalvar;

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
    <link rel="stylesheet" href="../../global/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../global/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../global/css/forms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../global/css/tables.css?v=<?= time() ?>">


    <link rel="stylesheet" href="../../global/css/layout.css?v=<?= time() ?>">
</head>


<body>


<?php include __DIR__ . '/../../components/header.php'; ?>


<div class="container">


    <!-- MENU -->

    <?php include __DIR__ . '/../../components/menu.php'; ?>

    <div class="page-area">


    <!-- CONTEÚDO -->

    <main class="conteudo">


        <div class="baralho-wrapper">


            <!-- VOLTAR -->

            <div class="baralho-nav">

                <a
                    href="flashcards.php"
                    class="back-button"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Voltar para Flashcards

                </a>

            </div>


            <!-- CABEÇALHO BARALHO -->

            <section
                class="deck-header"
                style="
                    --deck-color:
                    <?= htmlspecialchars($cor) ?>;
                "
            >


                <div class="deck-header-main">


                    <div class="deck-big-icon">

                        <i
                            class="fa-solid <?= htmlspecialchars($icone) ?>"
                        ></i>

                    </div>


                    <div class="deck-header-info">


                        <span class="deck-subject">

                            <?= htmlspecialchars(
                                $baralhoAtual['materia']
                                ?? ''
                            ) ?>

                        </span>


                        <h1>

                            <?= htmlspecialchars(
                                $baralhoAtual['nome']
                                ?? 'Baralho'
                            ) ?>

                        </h1>


                        <?php if (
                            !empty(
                                $baralhoAtual['descricao']
                            )
                        ): ?>

                            <p>

                                <?= htmlspecialchars(
                                    $baralhoAtual['descricao']
                                ) ?>

                            </p>

                        <?php endif; ?>


                    </div>


                </div>


                <div class="deck-header-actions">


                    <div class="cards-total">

                        <strong
                            id="cards-total"
                        >

                            <?= count(
                                $baralhoAtual['cartoes']
                            ) ?>

                        </strong>

                        <span>
                            cartões
                        </span>

                    </div>


                    <button
                        type="button"
                        id="open-card-modal"
                        class="btn"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Novo cartão

                    </button>


                </div>


            </section>


            <!-- LISTA DE CARTÕES -->

            <section class="cards-section">


                <div class="section-header">


                    <div>

                        <h2>
                            Cartões
                        </h2>

                        <p>
                            Perguntas e respostas deste baralho.
                        </p>

                    </div>


                </div>


                <!-- GRID -->

                <div
                    id="cards-grid"
                    class="cards-grid"
                    hidden
                ></div>


                <!-- VAZIO -->

                <div
                    id="cards-empty"
                    class="cards-empty"
                >


                    <div class="empty-icon">

                        <i class="fa-regular fa-clone"></i>

                    </div>


                    <h3>
                        Nenhum cartão ainda
                    </h3>


                    <p>
                        Adicione a primeira pergunta deste baralho.
                    </p>


                    <button
                        type="button"
                        id="open-card-modal-empty"
                        class="btn"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Criar primeiro cartão

                    </button>


                </div>


            </section>


        </div>


    </main>
    <?php include __DIR__ . '/../../components/footer.php'; ?>

    </div>
  </div>
<!-- ===================================
     MODAL NOVO CARTÃO
==================================== -->

<div
    id="card-modal"
    class="modal"
    aria-hidden="true"
>


    <div class="modal-content">


        <button
            type="button"
            id="close-card-modal"
            class="modal-close"
        >

            <i class="fa-solid fa-xmark"></i>

        </button>


        <div class="modal-title">


            <div class="modal-title-icon">

                <i class="fa-solid fa-clone"></i>

            </div>


            <div>

               <h3 id="card-modal-title">
                    Novo cartão
                </h3>

                <p id="card-modal-subtitle">
                    Crie uma pergunta e sua resposta.
                </p>

            </div>


        </div>


        <form id="card-form">


            <div class="field-group">


                <label
                    for="card-question"
                    class="lbl"
                >
                    Pergunta
                </label>


                <textarea
                    id="card-question"
                    class="input"
                    maxlength="500"
                    placeholder="Ex.: O que é mitose?"
                    required
                ></textarea>


            </div>


            <div class="field-group">


                <label
                    for="card-answer"
                    class="lbl"
                >
                    Resposta
                </label>


                <textarea
                    id="card-answer"
                    class="input"
                    maxlength="1000"
                    placeholder="Digite a resposta..."
                    required
                ></textarea>


            </div>


            <div class="modal-actions">


                <button
                    type="button"
                    id="cancel-card-modal"
                    class="btn secondary"
                >
                    Cancelar
                </button>


                <button
                    type="submit"
                    class="btn"
                    id="card-submit-btn"
                >
                    <i class="fa-solid fa-plus"></i>

                    Adicionar cartão
                </button>


            </div>


        </form>


    </div>


</div>


<!-- ===================================
     LOGOUT
==================================== -->

<div
    id="logout-modal"
    class="modal"
>


    <div class="modal-content logout-content">


        <h3>
            Ah... já vai?
        </h3>

        <p>
            Tem certeza que deseja sair?
        </p>


        <div class="modal-actions">


            <button
                id="cancel-logout"
                class="btn secondary"
            >
                Cancelar
            </button>


            <button
                id="confirm-logout"
                class="btn"
            >
                Sim
            </button>


        </div>


    </div>


</div>


<div
    id="toast"
    class="toast"
></div>
<script src="../../estrelas/modal_estrelas.js?v=<?= time() ?>"></script>


<script src="../../global/js/cursor.js?v=<?= time() ?>"></script>
<script defer src="baralho.js?v=<?= time() ?>"></script>

<script src="../../configuracoes/aparencia.js?v=6"></script>
<script src="../../configuracoes/acessibilidade.js?v=26" defer></script>

</body>

</html>