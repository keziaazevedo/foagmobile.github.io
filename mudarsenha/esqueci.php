<?php
require_once __DIR__ . '/../config/bootstrap.php';

session_start();

$erro = '';
$etapa = 1;
$pergunta = '';
$emailExibido = '';

$caminhoCss = __DIR__ . '/mudar.css';

$versaoCss = file_exists($caminhoCss)
    ? filemtime($caminhoCss)
    : time();


/* ==========================================
   NORMALIZAR RESPOSTA
========================================== */

function normalizarResposta(string $resposta): string
{
    $resposta = trim($resposta);

    $resposta = preg_replace(
        '/\s+/u',
        ' ',
        $resposta
    );

    if (function_exists('mb_strtolower')) {
        return mb_strtolower(
            $resposta,
            'UTF-8'
        );
    }

    return strtolower($resposta);
}


/* ==========================================
   LIMPAR RECUPERAÇÃO
========================================== */

function limparRecuperacao(): void
{
    unset(
        $_SESSION['recuperacao_arquivo'],
        $_SESSION['recuperacao_email'],
        $_SESSION['recuperacao_codigo'],
        $_SESSION['recuperacao_pergunta'],
        $_SESSION['recuperacao_inicio'],
        $_SESSION['recuperacao_tentativas'],
        $_SESSION['recuperacao_autorizada']
    );
}


/* ==========================================
   COMEÇAR NOVAMENTE
========================================== */

if (
    isset($_GET['novo']) &&
    $_GET['novo'] === '1'
) {

    limparRecuperacao();

    header('Location: esqueci.php');
    exit;
}


/* ==========================================
   RESTAURAR ETAPA 2
========================================== */

if (
    !empty($_SESSION['recuperacao_arquivo']) &&
    !empty($_SESSION['recuperacao_pergunta']) &&
    empty($_SESSION['recuperacao_autorizada'])
) {

    $etapa = 2;

    $pergunta =
        $_SESSION['recuperacao_pergunta'];

    $emailExibido =
        $_SESSION['recuperacao_email'] ?? '';
}


/* ==========================================
   PROCESSAR FORMULÁRIO
========================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao =
        $_POST['acao'] ?? '';


    /* ======================================
       ETAPA 1 - LOCALIZAR E-MAIL
    ====================================== */

    if ($acao === 'buscar_email') {

        limparRecuperacao();

        $email =
            strtolower(
                trim(
                    $_POST['email'] ?? ''
                )
            );


        if (
            $email === '' ||
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            $erro =
                'Digite um e-mail válido.';

            $etapa = 1;

        } else {

            $pastaLogin = FOAG_LOGIN_DATA_DIR;


            if (!is_dir($pastaLogin)) {

                $erro =
                    'Não foi possível acessar os usuários cadastrados.';

                $etapa = 1;

            } else {

                $arquivos =
                    glob(
                        $pastaLogin . '/*.json'
                    ) ?: [];


                $usuarioEncontrado = null;
                $arquivoEncontrado = null;


                foreach ($arquivos as $arquivo) {

                    $conteudo =
                        file_get_contents(
                            $arquivo
                        );


                    if ($conteudo === false) {
                        continue;
                    }


                    $dados =
                        json_decode(
                            $conteudo,
                            true
                        );


                    if (!is_array($dados)) {
                        continue;
                    }


                    $emailUsuario =
                        strtolower(
                            trim(
                                $dados['email'] ?? ''
                            )
                        );


                    if ($emailUsuario === $email) {

                        $usuarioEncontrado =
                            $dados;

                        $arquivoEncontrado =
                            $arquivo;

                        break;
                    }

                }


                if (!$usuarioEncontrado) {

                    $erro =
                        'Não encontramos uma conta com esse e-mail.';

                    $etapa = 1;

                } elseif (
                    empty(
                        $usuarioEncontrado[
                            'pergunta_secreta'
                        ]
                    ) ||
                    empty(
                        $usuarioEncontrado[
                            'resposta_secreta'
                        ]
                    )
                ) {

                    $erro =
                        'Esta conta não possui pergunta de segurança cadastrada.';

                    $etapa = 1;

                } else {

                    $_SESSION[
                        'recuperacao_arquivo'
                    ] =
                        $arquivoEncontrado;


                    $_SESSION[
                        'recuperacao_email'
                    ] =
                        $usuarioEncontrado[
                            'email'
                        ];


                    $_SESSION[
                        'recuperacao_codigo'
                    ] =
                        $usuarioEncontrado[
                            'codigo_usuario'
                        ] ?? '';


                    $_SESSION[
                        'recuperacao_pergunta'
                    ] =
                        $usuarioEncontrado[
                            'pergunta_secreta'
                        ];


                    $_SESSION[
                        'recuperacao_inicio'
                    ] =
                        time();


                    $_SESSION[
                        'recuperacao_tentativas'
                    ] =
                        0;


                    $pergunta =
                        $_SESSION[
                            'recuperacao_pergunta'
                        ];


                    $emailExibido =
                        $_SESSION[
                            'recuperacao_email'
                        ];


                    $etapa = 2;

                }

            }

        }

    }


    /* ======================================
       ETAPA 2 - VERIFICAR RESPOSTA
    ====================================== */

    if ($acao === 'verificar_resposta') {

        if (
            empty(
                $_SESSION[
                    'recuperacao_arquivo'
                ]
            ) ||
            empty(
                $_SESSION[
                    'recuperacao_pergunta'
                ]
            )
        ) {

            limparRecuperacao();

            $erro =
                'A recuperação expirou. Digite seu e-mail novamente.';

            $etapa = 1;

        } else {

            $inicio =
                $_SESSION[
                    'recuperacao_inicio'
                ] ?? 0;


            if (
                !$inicio ||
                (time() - $inicio) > 600
            ) {

                limparRecuperacao();

                $erro =
                    'O tempo para recuperação expirou. Digite seu e-mail novamente.';

                $etapa = 1;

            } else {

                $tentativas =
                    $_SESSION[
                        'recuperacao_tentativas'
                    ] ?? 0;


                $arquivo =
                    $_SESSION[
                        'recuperacao_arquivo'
                    ];


                if (!file_exists($arquivo)) {

                    limparRecuperacao();

                    $erro =
                        'Não foi possível localizar sua conta.';

                    $etapa = 1;

                } else {

                    $conteudo =
                        file_get_contents(
                            $arquivo
                        );


                    $dados =
                        json_decode(
                            $conteudo,
                            true
                        );


                    if (
                        !is_array($dados) ||
                        empty(
                            $dados[
                                'resposta_secreta'
                            ]
                        )
                    ) {

                        $erro =
                            'Não foi possível verificar sua resposta.';

                        $etapa = 2;

                    } else {

                        $resposta =
                            normalizarResposta(
                                $_POST[
                                    'resposta'
                                ] ?? ''
                            );


                        if ($resposta === '') {

                            $erro =
                                'Digite sua resposta.';

                            $etapa = 2;

                        } elseif (
                            password_verify(
                                $resposta,
                                $dados[
                                    'resposta_secreta'
                                ]
                            )
                        ) {

                            $_SESSION[
                                'recuperacao_autorizada'
                            ] =
                                true;


                            $_SESSION[
                                'recuperacao_inicio'
                            ] =
                                time();


                            header(
                                'Location: mudar.php'
                            );

                            exit;

                        } else {

                            $tentativas++;

                            $_SESSION[
                                'recuperacao_tentativas'
                            ] =
                                $tentativas;


                            if ($tentativas >= 5) {

                                limparRecuperacao();

                                $erro =
                                    'Limite de tentativas atingido. Comece novamente.';

                                $etapa = 1;

                            } else {

                                $restantes =
                                    5 -
                                    $tentativas;


                                $erro =
                                    'Resposta incorreta. Restam '
                                    .
                                    $restantes
                                    .
                                    ' tentativa(s).';


                                $pergunta =
                                    $_SESSION[
                                        'recuperacao_pergunta'
                                    ];


                                $emailExibido =
                                    $_SESSION[
                                        'recuperacao_email'
                                    ];


                                $etapa = 2;

                            }

                        }

                    }

                }

            }

        }

    }

}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>


    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Recuperar Senha
    </title>

    <link
        rel="stylesheet"
        href="mudar.css?v=<?= $versaoCss ?>"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="../global/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/forms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/tables.css?v=<?= time() ?>">
</head>

<body>

    <div class="logo">
        FOAG
    </div>


    <main class="login-page">


        <section class="left-section">

            <img
                src="../img/livro.png"
                alt="Imagem FOAG"
            >

        </section>


        <section class="right-section">

            <div class="recuperacao-box">


                <!-- PROGRESSO -->

                <div class="progresso-recuperacao">

                    <div class="progresso-item <?= $etapa >= 1 ? 'ativo' : '' ?>">

                        <span>
                            <?= $etapa > 1 ? '✓' : '1' ?>
                        </span>

                        <small>
                            E-mail
                        </small>

                    </div>


                    <div class="progresso-linha <?= $etapa > 1 ? 'ativa' : '' ?>"></div>


                    <div class="progresso-item <?= $etapa >= 2 ? 'ativo' : '' ?>">

                        <span>
                            <?= $etapa > 2 ? '✓' : '2' ?>
                        </span>

                        <small>
                            Segurança
                        </small>

                    </div>


                    <div class="progresso-linha"></div>


                    <div class="progresso-item">

                        <span>
                            3
                        </span>

                        <small>
                            Nova senha
                        </small>

                    </div>

                </div>


                <!-- ==================================
                     ETAPA 1
                =================================== -->

                <?php if ($etapa === 1): ?>


                    <h1>
                        Recuperar senha
                    </h1>


                    <p class="descricao">
                        Informe o e-mail usado no cadastro da sua conta.
                    </p>


                    <?php if ($erro): ?>

                        <div class="mensagem-erro">

                            <?= htmlspecialchars(
                                $erro,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <form
                        method="POST"
                        action=""
                    >

                        <input
                            type="hidden"
                            name="acao"
                            value="buscar_email"
                        >


                        <div class="campo">

                            <label for="email">
                                E-mail
                            </label>


                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Digite seu e-mail"
                                autocomplete="off"
                                required
                            >

                        </div>


                        <button type="submit">
                            Continuar
                        </button>

                    </form>


                    <a
                        href="../login/index.php"
                        class="voltar-login"
                    >
                        ← Voltar ao login
                    </a>


                <!-- ==================================
                     ETAPA 2
                =================================== -->

                <?php else: ?>


                    <h1>
                        Pergunta de segurança
                    </h1>


                    <p class="descricao">
                        Responda à pergunta escolhida quando sua conta foi criada.
                    </p>


                    <?php if ($erro): ?>

                        <div class="mensagem-erro">

                            <?= htmlspecialchars(
                                $erro,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <div class="conta-encontrada">

                        <span>
                            Conta encontrada
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $emailExibido,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                    </div>


                    <div class="pergunta-box">

                        <span>
                            Sua pergunta
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $pergunta,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                    </div>


                    <form
                        method="POST"
                        action=""
                    >

                        <input
                            type="hidden"
                            name="acao"
                            value="verificar_resposta"
                        >


                        <div class="campo">

                            <label for="resposta">
                                Resposta secreta
                            </label>


                            <div class="password-wrapper">

                                <input
                                    type="password"
                                    id="resposta"
                                    name="resposta"
                                    placeholder="Digite sua resposta"
                                    autocomplete="off"
                                    required
                                >


                                <span
                                    class="toggle-visibility"
                                    data-target="resposta"
                                >
                                    🙈
                                </span>

                            </div>

                        </div>


                        <button type="submit">
                            Continuar
                        </button>

                    </form>


                    <a
                        href="esqueci.php?novo=1"
                        class="voltar-login"
                    >
                        ← Informar outro e-mail
                    </a>


                <?php endif; ?>


            </div>

        </section>

    </main>


    <script>

        document
            .querySelectorAll(
                '.toggle-visibility'
            )
            .forEach(icon => {

                icon.addEventListener(
                    'click',
                    function () {

                        const input =
                            document.getElementById(
                                this.dataset.target
                            );


                        if (
                            input.type ===
                            'password'
                        ) {

                            input.type =
                                'text';

                            this.textContent =
                                '🙉';

                        } else {

                            input.type =
                                'password';

                            this.textContent =
                                '🙈';

                        }

                    }
                );

            });

    </script>

</body>

</html>