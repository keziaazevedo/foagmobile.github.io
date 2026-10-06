<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(false);
header('Content-Type: application/json; charset=utf-8');


/* =========================================================
   APENAS POST
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);

    exit;
}


/* =========================================================
   VERIFICAR LOGIN
========================================================= */

if ($codigoUsuario === null) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Sua sessão expirou. Faça login novamente.'
    ]);

    exit;
}


$codigoUsuario =
    trim(
        (string) $_SESSION['codigo_usuario']
    );


if ($codigoUsuario === '') {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Usuário não identificado.'
    ]);

    exit;
}


/* =========================================================
   RECEBER SENHA
========================================================= */

$senha =
    $_POST['senha'] ?? '';


if ($senha === '') {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Digite sua senha para confirmar.'
    ]);

    exit;
}


/* =========================================================
   CAMINHOS
========================================================= */

$pastaLogins = FOAG_LOGIN_DATA_DIR;


$pastaUsuarios = FOAG_USUARIOS_DIR;


$pastaUsuario =
    $pastaUsuarios .
    '/' .
    $codigoUsuario;


/* =========================================================
   LOCALIZAR ARQUIVO DE LOGIN
========================================================= */

if (!is_dir($pastaLogins)) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível acessar os dados de login.'
    ]);

    exit;
}


$arquivosLogin =
    glob(
        $pastaLogins . '/*.json'
    ) ?: [];


$arquivoLogin = null;
$dadosLogin = null;


foreach ($arquivosLogin as $arquivo) {

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


    $codigoArquivo =
        trim(
            (string) (
                $dados['codigo_usuario']
                ?? ''
            )
        );


    if (
        hash_equals(
            $codigoUsuario,
            $codigoArquivo
        )
    ) {

        $arquivoLogin =
            $arquivo;

        $dadosLogin =
            $dados;

        break;
    }
}


/* =========================================================
   USUÁRIO NÃO ENCONTRADO
========================================================= */

if (
    !$arquivoLogin ||
    !is_array($dadosLogin)
) {

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível localizar sua conta.'
    ]);

    exit;
}


/* =========================================================
   VERIFICAR SENHA
========================================================= */

$senhaHash =
    $dadosLogin['senha']
    ?? '';


if (
    $senhaHash === '' ||
    !password_verify(
        $senha,
        $senhaHash
    )
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Senha incorreta.'
    ]);

    exit;
}


/* =========================================================
   FUNÇÃO PARA APAGAR PASTA
========================================================= */

function excluirPastaRecursivamente(
    string $pasta
): bool {

    if (!file_exists($pasta)) {
        return true;
    }


    if (!is_dir($pasta)) {
        return unlink($pasta);
    }


    $itens =
        scandir(
            $pasta
        );


    if ($itens === false) {
        return false;
    }


    foreach ($itens as $item) {

        if (
            $item === '.' ||
            $item === '..'
        ) {
            continue;
        }


        $caminho =
            $pasta .
            DIRECTORY_SEPARATOR .
            $item;


        if (is_dir($caminho)) {

            if (
                !excluirPastaRecursivamente(
                    $caminho
                )
            ) {
                return false;
            }

        } else {

            if (!unlink($caminho)) {
                return false;
            }

        }
    }


    return rmdir($pasta);
}


/* =========================================================
   SEGURANÇA DO CAMINHO DA PASTA
========================================================= */

$baseUsuariosReal =
    realpath(
        $pastaUsuarios
    );


$pastaUsuarioReal =
    realpath(
        $pastaUsuario
    );


if (
    $pastaUsuarioReal !== false &&
    $baseUsuariosReal !== false
) {

    $prefixoPermitido =
        rtrim(
            $baseUsuariosReal,
            DIRECTORY_SEPARATOR
        ) .
        DIRECTORY_SEPARATOR;


    if (
        strpos(
            $pastaUsuarioReal,
            $prefixoPermitido
        ) !== 0
    ) {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Caminho de usuário inválido.'
        ]);

        exit;
    }
}


/* =========================================================
   EXCLUIR DADOS DO USUÁRIO
========================================================= */

if (
    file_exists($pastaUsuario) &&
    !excluirPastaRecursivamente(
        $pastaUsuario
    )
) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível excluir todos os dados da conta.'
    ]);

    exit;
}


/* =========================================================
   EXCLUIR LOGIN
========================================================= */

if (
    file_exists($arquivoLogin) &&
    !unlink($arquivoLogin)
) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Os dados foram removidos, mas ocorreu um erro ao excluir o login.'
    ]);

    exit;
}


/* =========================================================
   ENCERRAR SESSÃO
========================================================= */

$_SESSION = [];


if (
    ini_get(
        'session.use_cookies'
    )
) {

    $params =
        session_get_cookie_params();


    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}


session_destroy();


/* =========================================================
   SUCESSO
========================================================= */

echo json_encode([
    'success' => true,
    'message' => 'Sua conta foi excluída permanentemente.',
    'redirect' => '../login/index.php'
]);

exit;