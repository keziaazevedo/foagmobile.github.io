<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$contextoUsuario = foag_contexto_usuario('../../login/index.php');
$codigoUsuario = $contextoUsuario['codigoUsuario'];
$baseJsonDir = $contextoUsuario['baseJsonDir'];
$pastaUsuario = $contextoUsuario['pastaUsuario'];


if (!is_dir($pastaUsuario)) {
    exit('Pasta do usuário não encontrada.');
}


// ======================================
// ID DO BARALHO
// ======================================

$idBaralho =
    trim($_GET['id'] ?? '');

if ($idBaralho === '') {
    header('Location: flashcards.php');
    exit;
}


// ======================================
// FLASHCARDS.JSON
// ======================================

$arquivoFlashcards =
    $pastaUsuario . '/flashcards.json';

if (!file_exists($arquivoFlashcards)) {
    header('Location: flashcards.php');
    exit;
}

$flashcardsData =
    json_decode(
        file_get_contents($arquivoFlashcards),
        true
    );

if (
    !is_array($flashcardsData) ||
    !isset($flashcardsData['baralhos']) ||
    !is_array($flashcardsData['baralhos'])
) {
    header('Location: flashcards.php');
    exit;
}


// ======================================
// ENCONTRAR BARALHO
// ======================================

$baralhoAtual = null;

foreach ($flashcardsData['baralhos'] as $baralho) {

    if (
        ($baralho['id'] ?? '') ===
        $idBaralho
    ) {

        $baralhoAtual = $baralho;
        break;
    }
}

if (!$baralhoAtual) {
    header('Location: flashcards.php');
    exit;
}


// ======================================
// CARTÕES
// ======================================

if (
    !isset($baralhoAtual['cartoes']) ||
    !is_array($baralhoAtual['cartoes'])
) {
    $baralhoAtual['cartoes'] = [];
}


// ======================================
// PEGAR MATÉRIA DO materias.json
// ======================================

$arquivoMaterias =
    $pastaUsuario . '/materias.json';

$materiaAtual = null;

if (file_exists($arquivoMaterias)) {

    $materiasData =
        json_decode(
            file_get_contents($arquivoMaterias),
            true
        );

    if (
        is_array($materiasData) &&
        isset($materiasData['materias']) &&
        is_array($materiasData['materias'])
    ) {

        foreach (
            $materiasData['materias']
            as $materia
        ) {

            $nomeMateria =
                trim(
                    $materia['nome']
                    ?? ''
                );

            $nomeBaralho =
                trim(
                    $baralhoAtual['materia']
                    ?? ''
                );

            if (
                mb_strtolower($nomeMateria) ===
                mb_strtolower($nomeBaralho)
            ) {

                $materiaAtual =
                    $materia;

                break;
            }
        }
    }
}


// ======================================
// COR E ÍCONE
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
        Estudar —
        <?= htmlspecialchars(
            $baralhoAtual['nome'] ?? 'Flashcards'
        ) ?>
    </title>

    <link rel="stylesheet" href="estudar.css">
    <link rel="stylesheet" href="dark_estudar.css">
    <link rel="stylesheet" href="../../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="../../estrelas/modal_estrelas.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../global/css/cursor.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script src="../../m.escuro/dark-mode.js"></script>

<script>

        window.ESTUDO_DATA =
            <?= json_encode(
                [
                    'baralho' => $baralhoAtual,
                    'cor' => $cor,
                    'icone' => $icone
                ],
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        window.REVISAO_SAVE_URL =
            FOAG_CONFIG.endpoints.flashcardsRevisaoSalvar;

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


    <?php include __DIR__ . '/../../components/menu.php'; ?>

    <div class="page-area">


    <main class="conteudo">

        <div
            class="study-wrapper"
            style="
                --deck-color:
                <?= htmlspecialchars($cor) ?>;
            "
        >


            <!-- VOLTAR -->

            <div class="study-nav">

                <a
                    href="baralho.php?id=<?= urlencode($idBaralho) ?>"
                    class="back-button"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Voltar ao baralho

                </a>


                <?php if (
                    count($baralhoAtual['cartoes']) > 1
                ): ?>

                    <button
                        type="button"
                        id="shuffle-btn"
                        class="shuffle-button"
                    >

                        <i class="fa-solid fa-shuffle"></i>

                        Embaralhar

                    </button>

                <?php endif; ?>

            </div>


            <!-- CABEÇALHO -->

            <section class="study-header">


                <div class="study-header-main">

                    <div class="study-icon">

                        <i
                            class="fa-solid <?= htmlspecialchars($icone) ?>"
                        ></i>

                    </div>


                    <div>

                        <span class="study-subject">

                            <?= htmlspecialchars(
                                $baralhoAtual['materia']
                                ?? ''
                            ) ?>

                        </span>


                        <h1>

                            <?= htmlspecialchars(
                                $baralhoAtual['nome']
                                ?? 'Flashcards'
                            ) ?>

                        </h1>


                        <p>
                            Revise os cartões e marque como você se saiu.
                        </p>

                    </div>

                </div>


                <div class="session-stats">

                    <div>

                        <strong id="session-hits">
                            0
                        </strong>

                        <span>
                            acertos
                        </span>

                    </div>


                    <div>

                        <strong id="session-errors">
                            0
                        </strong>

                        <span>
                            erros
                        </span>

                    </div>

                </div>


            </section>


            <?php if (
                count($baralhoAtual['cartoes']) === 0
            ): ?>


                <!-- SEM CARTÕES -->

                <section class="empty-study">

                    <div class="empty-study-icon">

                        <i class="fa-regular fa-clone"></i>

                    </div>


                    <h2>
                        Este baralho ainda está vazio
                    </h2>


                    <p>
                        Crie alguns cartões antes de começar a estudar.
                    </p>


                    <a
                        href="baralho.php?id=<?= urlencode($idBaralho) ?>"
                        class="btn"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Adicionar cartões

                    </a>

                </section>


            <?php else: ?>


                <!-- ÁREA DE ESTUDO -->

                <section
                    id="study-area"
                    class="study-area"
                >


                    <!-- PROGRESSO -->

                    <div class="study-progress-header">

                        <div>

                            <span>
                                Progresso
                            </span>

                            <strong id="progress-text">
                                1 de
                                <?= count(
                                    $baralhoAtual['cartoes']
                                ) ?>
                            </strong>

                        </div>

                    </div>


                    <div class="study-progress">

                        <span
                            id="study-progress-bar"
                        ></span>

                    </div>


                    <!-- FLASHCARD -->

                    <div
                        id="study-card"
                        class="study-card"
                    >


                        <div
                            id="study-card-inner"
                            class="study-card-inner"
                        >


                            <!-- FRENTE -->

                            <div
                                class="study-card-face study-card-front"
                            >


                                <div class="face-label">

                                    <i class="fa-regular fa-circle-question"></i>

                                    Pergunta

                                </div>


                                <div
                                    id="question-text"
                                    class="study-card-text"
                                ></div>


                                <div class="flip-hint">

                                    <i class="fa-solid fa-rotate"></i>

                                    Clique para ver a resposta

                                </div>


                            </div>


                            <!-- VERSO -->

                            <div
                                class="study-card-face study-card-back"
                            >


                                <div class="face-label">

                                    <i class="fa-regular fa-lightbulb"></i>

                                    Resposta

                                </div>


                                <div
                                    id="answer-text"
                                    class="study-card-text"
                                ></div>


                                <div class="flip-hint">

                                    <i class="fa-solid fa-rotate"></i>

                                    Clique para voltar

                                </div>


                            </div>


                        </div>


                    </div>


                    <!-- BOTÃO MOSTRAR -->

                    <div
                        id="reveal-actions"
                        class="reveal-actions"
                    >

                        <button
                            type="button"
                            id="reveal-btn"
                            class="btn reveal-btn"
                        >

                            <i class="fa-regular fa-eye"></i>

                            Mostrar resposta

                        </button>

                    </div>


                    <!-- ACERTO / ERRO -->

                    <div
                        id="answer-actions"
                        class="answer-actions"
                        hidden
                    >


                        <p>
                            Como você se saiu?
                        </p>


                        <div class="answer-buttons">


                            <button
                                type="button"
                                id="wrong-btn"
                                class="answer-btn wrong"
                            >

                                <i class="fa-solid fa-xmark"></i>

                                Errei

                            </button>


                            <button
                                type="button"
                                id="correct-btn"
                                class="answer-btn correct"
                            >

                                <i class="fa-solid fa-check"></i>

                                Acertei

                            </button>


                        </div>


                    </div>


                </section>


                <!-- FINAL -->

                <section
                    id="finish-screen"
                    class="finish-screen"
                    hidden
                >


                    <div class="finish-icon">

                        <i class="fa-solid fa-trophy"></i>

                    </div>


                    <h2>
                        Revisão concluída!
                    </h2>


                    <p>
                        Você terminou todos os cartões deste baralho.
                    </p>


                    <div class="finish-stats">


                        <div class="finish-stat">

                            <strong
                                id="finish-hits"
                            >
                                0
                            </strong>

                            <span>
                                Acertos
                            </span>

                        </div>


                        <div class="finish-stat">

                            <strong
                                id="finish-errors"
                            >
                                0
                            </strong>

                            <span>
                                Erros
                            </span>

                        </div>


                        <div class="finish-stat">

                            <strong
                                id="finish-percent"
                            >
                                0%
                            </strong>

                            <span>
                                Aproveitamento
                            </span>

                        </div>


                    </div>


                    <div class="finish-actions">


                        <button
                            type="button"
                            id="restart-btn"
                            class="btn secondary"
                        >

                            <i class="fa-solid fa-rotate-right"></i>

                            Estudar novamente

                        </button>


                        <a
                            href="baralho.php?id=<?= urlencode($idBaralho) ?>"
                            class="btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>

                            Voltar ao baralho

                        </a>


                    </div>


                </section>


            <?php endif; ?>


        </div>

    </main>
    <?php include __DIR__ . '/../../components/footer.php'; ?>

    </div>
  </div>
<!-- LOGOUT -->

<div
    id="logout-modal"
    class="modal"
>

    <div class="modal-content">

        <h3>
            Ah... já vai?
        </h3>

        <p>
            Tem certeza que deseja sair?
        </p>

        <div class="modal-actions">

            <button
                type="button"
                id="cancel-logout"
                class="btn secondary"
            >
                Cancelar
            </button>

            <button
                type="button"
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
<script defer src="estudar.js?v=<?= time() ?>"></script>

<script src="../../configuracoes/aparencia.js?v=6"></script>
<script src="../../configuracoes/acessibilidade.js?v=26" defer></script>


</body>

</html>