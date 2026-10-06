<?php

require_once __DIR__ . '/../../config/bootstrap.php';

session_start();


/* ==========================================
   VERIFICAR AUTORIZAÇÃO
========================================== */

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    header(
        'Location: ' . foag_url('mudarsenha/esqueci.php')
    );

    exit;
}


if (
    empty(
        $_SESSION[
            'recuperacao_autorizada'
        ]
    ) ||
    empty(
        $_SESSION[
            'recuperacao_arquivo'
        ]
    ) ||
    empty(
        $_SESSION[
            'recuperacao_inicio'
        ]
    )
) {

    header(
        'Location: ' . foag_url('mudarsenha/esqueci.php')
    );

    exit;
}


/* ==========================================
   VERIFICAR TEMPO
========================================== */

if (
    (
        time() -
        $_SESSION[
            'recuperacao_inicio'
        ]
    ) > 600
) {

    unset(
        $_SESSION[
            'recuperacao_autorizada'
        ],
        $_SESSION[
            'recuperacao_arquivo'
        ],
        $_SESSION[
            'recuperacao_email'
        ],
        $_SESSION[
            'recuperacao_codigo'
        ],
        $_SESSION[
            'recuperacao_pergunta'
        ],
        $_SESSION[
            'recuperacao_inicio'
        ],
        $_SESSION[
            'recuperacao_tentativas'
        ]
    );

    exit(
        'O tempo para redefinir a senha expirou. Volte e tente novamente.'
    );
}


/* ==========================================
   RECEBER NOVA SENHA
========================================== */

$novaSenha =
    $_POST['nova_senha'] ?? '';

$confirmarSenha =
    $_POST['confirmar_senha'] ?? '';


if (
    empty($novaSenha) ||
    empty($confirmarSenha)
) {

    exit(
        'Preencha os dois campos de senha.'
    );
}


/* ==========================================
   VALIDAR SENHA
========================================== */

$regexSenha =
    '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/';


if (
    !preg_match(
        $regexSenha,
        $novaSenha
    )
) {

    exit(
        'A senha precisa ter pelo menos 8 caracteres, uma letra maiúscula, um número e um símbolo.'
    );
}


if (
    $novaSenha !==
    $confirmarSenha
) {

    exit(
        'As senhas não coincidem.'
    );
}


/* ==========================================
   ABRIR JSON DO USUÁRIO
========================================== */

$arquivo =
    $_SESSION[
        'recuperacao_arquivo'
    ];


/*
 * Garante que estamos trabalhando somente
 * dentro da pasta de login.
 */

$pastaLogin = realpath(FOAG_LOGIN_DATA_DIR);


$arquivoReal =
    realpath($arquivo);


if (
    !$pastaLogin ||
    !$arquivoReal ||
    strpos(
        $arquivoReal,
        $pastaLogin .
        DIRECTORY_SEPARATOR
    ) !== 0
) {

    exit(
        'Arquivo de usuário inválido.'
    );
}


if (
    !file_exists($arquivoReal)
) {

    exit(
        'Usuário não encontrado.'
    );
}


$conteudo =
    file_get_contents(
        $arquivoReal
    );


$dados =
    json_decode(
        $conteudo,
        true
    );


if (
    !is_array($dados)
) {

    exit(
        'Não foi possível carregar os dados da conta.'
    );
}


/* ==========================================
   CRIAR NOVO HASH
========================================== */

$novoHash =
    password_hash(
        $novaSenha,
        PASSWORD_DEFAULT
    );


if (
    $novoHash === false
) {

    exit(
        'Não foi possível proteger a nova senha.'
    );
}


/* ==========================================
   ALTERAR SOMENTE A SENHA
========================================== */

$dados['senha'] =
    $novoHash;


/* ==========================================
   SALVAR JSON
========================================== */

$json =
    json_encode(
        $dados,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE
    );


if (
    $json === false
) {

    exit(
        'Não foi possível preparar os dados da conta.'
    );
}


$resultado =
    file_put_contents(
        $arquivoReal,
        $json,
        LOCK_EX
    );


if (
    $resultado === false
) {

    exit(
        'Não foi possível alterar a senha.'
    );
}


/* ==========================================
   LIMPAR RECUPERAÇÃO
========================================== */

unset(
    $_SESSION[
        'recuperacao_autorizada'
    ],
    $_SESSION[
        'recuperacao_arquivo'
    ],
    $_SESSION[
        'recuperacao_email'
    ],
    $_SESSION[
        'recuperacao_codigo'
    ],
    $_SESSION[
        'recuperacao_pergunta'
    ],
    $_SESSION[
        'recuperacao_inicio'
    ],
    $_SESSION[
        'recuperacao_tentativas'
    ]
);


/* ==========================================
   REDIRECIONAR
========================================== */

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <script src="../../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../../global/js/utils.js?v=<?= time() ?>"></script>


    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        http-equiv="refresh"
        content="3;url=../login/index.php"
    >

    <title>Senha alterada</title>

    <link
        rel="stylesheet"
        href="mudar.css"
    >
</head>

<body class="pagina-sucesso">

    <div class="sucesso-box">

        <div class="sucesso-icone">
            ✓
        </div>

        <h1>
            Senha alterada!
        </h1>

        <p>
            Sua nova senha foi salva com sucesso.
        </p>

        <a href="../login/index.php">
            Ir para o login
        </a>

    </div>

</body>

</html>