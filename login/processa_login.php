<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Acesso inválido.");
}

$email = strtolower(trim($_POST['email'] ?? ''));
$senha = $_POST['senha'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
    exibirMensagem(
        "Preencha o e-mail e a senha corretamente.",
        "index.php"
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Pasta onde ficam os arquivos individuais de login
|--------------------------------------------------------------------------
*/

$pastaLogin = __DIR__ . '/../json/usuario_login';

if (!is_dir($pastaLogin)) {
    exibirMensagem(
        "Nenhum usuário cadastrado.",
        "../cadastro/cadastro.php"
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
        "../cadastro/cadastro.php"
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
        "index.php"
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
        "index.php"
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
        "index.php"
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Verificar se a pasta individual existe
|--------------------------------------------------------------------------
*/

$pastaUsuario = __DIR__
    . '/../json/usuarios/'
    . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exibirMensagem(
        "A pasta de dados deste usuário não foi encontrada.",
        "index.php"
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

header('Location: ../inicioo/inicio.php');
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