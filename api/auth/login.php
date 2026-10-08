<?php

require_once __DIR__ . '/../../config/bootstrap.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Acesso inválido.");
}

$email = strtolower(trim($_POST['email'] ?? ''));
$senha = $_POST['senha'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
    exibirMensagem(
        "Preencha o e-mail e a senha corretamente.",
        FOAG_LOGIN_URL
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Pasta onde ficam os arquivos individuais de login
|--------------------------------------------------------------------------
*/

$pastaLogin = FOAG_LOGIN_DATA_DIR;

if (!is_dir($pastaLogin)) {
    exibirMensagem(
        "Nenhum usuário cadastrado.",
        foag_url('cadastro/cadastro.php')
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Pegar todos os arquivos JSON
|--------------------------------------------------------------------------
*/

$arquivosLogin = glob($pastaLogin . '/*.json') ?: [];

if (empty($arquivosLogin)) {
    exibirMensagem(
        "Nenhum usuário cadastrado.",
        foag_url('cadastro/cadastro.php')
    );
    exit;
}

$usuarioEncontrado = null;

/*
|--------------------------------------------------------------------------
| Procurar usuário pelo e-mail
|--------------------------------------------------------------------------
*/

foreach ($arquivosLogin as $arquivoLogin) {

    if (!is_file($arquivoLogin)) {
        continue;
    }

    $conteudo = file_get_contents($arquivoLogin);

    if ($conteudo === false) {
        continue;
    }

    $usuario = json_decode($conteudo, true);

    if (!is_array($usuario)) {
        continue;
    }

    $emailUsuario = strtolower(
        trim($usuario['email'] ?? '')
    );

    if ($emailUsuario === $email) {
        $usuarioEncontrado = $usuario;
        break;
    }
}

/*
|--------------------------------------------------------------------------
| Verificar se encontrou
|--------------------------------------------------------------------------
*/

if ($usuarioEncontrado === null) {
    exibirMensagem(
        "E-mail ou senha incorretos.",
        FOAG_LOGIN_URL
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Verificar senha
|--------------------------------------------------------------------------
*/

$senhaHash = $usuarioEncontrado['senha'] ?? '';

if (
    $senhaHash === '' ||
    !password_verify($senha, $senhaHash)
) {
    exibirMensagem(
        "E-mail ou senha incorretos.",
        FOAG_LOGIN_URL
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Pegar o código da pasta do usuário
|--------------------------------------------------------------------------
*/

$codigoUsuario = $usuarioEncontrado['codigo_usuario'] ?? '';

if ($codigoUsuario === '') {
    exibirMensagem(
        "Não foi possível localizar os dados deste usuário.",
        FOAG_LOGIN_URL
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Verificar se a pasta individual existe
|--------------------------------------------------------------------------
*/

$pastaUsuario = FOAG_USUARIOS_DIR . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exibirMensagem(
        "A pasta de dados deste usuário não foi encontrada.",
        FOAG_LOGIN_URL
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Criar sessão
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);

$_SESSION['codigo_usuario'] = $codigoUsuario;

$_SESSION['user_nome'] =
    $usuarioEncontrado['nome'] ?? '';

$_SESSION['user_email'] =
    $usuarioEncontrado['email'] ?? '';

$_SESSION['usuario'] =
    $usuarioEncontrado['nome']
    ?? $usuarioEncontrado['email']
    ?? '';

/*
|--------------------------------------------------------------------------
| Login realizado
|--------------------------------------------------------------------------
*/

header('Location: ' . FOAG_HOME_URL);
exit;


/*
|--------------------------------------------------------------------------
| Mensagem
|--------------------------------------------------------------------------
*/

function exibirMensagem(
    string $mensagem,
    string $redirect
): void {

    $mensagemSegura = htmlspecialchars(
        $mensagem,
        ENT_QUOTES,
        'UTF-8'
    );

    $redirectSeguro = htmlspecialchars(
        $redirect,
        ENT_QUOTES,
        'UTF-8'
    );

    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="../../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <meta
        http-equiv="refresh"
        content="2;url={$redirectSeguro}"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap"
        rel="stylesheet"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;

            background: linear-gradient(
                to right,
                #38a5ff,
                rgb(46, 154, 241)
            );

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;

            min-height: 100vh;
            margin: 0;
            padding: 20px;

            text-align: center;
            color: white;
        }

        h2 {
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
        }

        a {
            color: yellow;
        }
    </style>
    <link rel="stylesheet" href="../../global/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../global/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../global/css/forms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../global/css/tables.css?v=<?= time() ?>">
</head>

<body>

    <h2>{$mensagemSegura}</h2>

    <p>Redirecionando...</p>

    <small>
        Se não for redirecionado,
        <a href="{$redirectSeguro}">
            clique aqui
        </a>.
    </small>

</body>
</html>
HTML;
}
?>