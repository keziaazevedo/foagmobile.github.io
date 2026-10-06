<?php

require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Acesso inválido.");
}

$nome = trim($_POST['nome'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';
$dataNascimento = trim($_POST['data_nascimento'] ?? '');
$perguntaSecreta = trim($_POST['pergunta_secreta'] ?? '');
$respostaSecreta = trim($_POST['resposta_secreta'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$serie = trim($_POST['serie'] ?? '');
$escola = trim($_POST['escola'] ?? '');
$termos = isset($_POST['termos']);
$dataCadastro = date('Y-m-d H:i:s');

if (
    empty($nome) ||
    empty($email) ||
    empty($senha) ||
    empty($dataNascimento) ||
    empty($perguntaSecreta) ||
    empty($respostaSecreta) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    exit("Por favor, preencha todos os campos corretamente.");
}

if (!$termos) {
    exit("Você precisa aceitar os termos de uso.");
}

$perguntasPermitidas = [
    'Qual o nome do seu primeiro animal?',
    'Qual era seu apelido de infância?',
    'Qual o nome da sua primeira escola?',
    'Qual o nome do seu personagem favorito?',
    'Qual era sua brincadeira favorita quando criança?'
];

if (!in_array($perguntaSecreta, $perguntasPermitidas, true)) {
    exit("Pergunta de segurança inválida.");
}

$nome = strip_tags($nome);

$regexSenha = '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/';

if (!preg_match($regexSenha, $senha)) {
    exit("A senha deve ter pelo menos 8 caracteres, uma letra maiúscula, um número e um símbolo.");
}

if ($senha !== $confirmarSenha) {
    exit("As senhas não coincidem.");
}

/*
|--------------------------------------------------------------------------
| Pastas utilizadas
|--------------------------------------------------------------------------
*/

$pastaLogin = FOAG_LOGIN_DATA_DIR;
$pastaUsuarios = FOAG_USUARIOS_DIR;

if (!is_dir($pastaLogin)) {
    if (!mkdir($pastaLogin, 0755, true)) {
        exit("Não foi possível criar a pasta usuario_login.");
    }
}

if (!is_dir($pastaUsuarios)) {
    if (!mkdir($pastaUsuarios, 0755, true)) {
        exit("Não foi possível criar a pasta usuarios.");
    }
}

/*
|--------------------------------------------------------------------------
| Bloquear cadastros simultâneos
|--------------------------------------------------------------------------
*/

$arquivoLock = fopen($pastaLogin . '/.cadastro.lock', 'c');

if (!$arquivoLock || !flock($arquivoLock, LOCK_EX)) {
    exit("Não foi possível iniciar o cadastro.");
}

/*
|--------------------------------------------------------------------------
| Verificar se o e-mail já existe
|--------------------------------------------------------------------------
*/

$arquivosLogin = glob($pastaLogin . '/*.json') ?: [];

foreach ($arquivosLogin as $arquivoLogin) {
    $conteudo = file_get_contents($arquivoLogin);
    $dadosExistentes = json_decode($conteudo, true);

    if (
        is_array($dadosExistentes) &&
        isset($dadosExistentes['email']) &&
        strtolower($dadosExistentes['email']) === $email
    ) {
        flock($arquivoLock, LOCK_UN);
        fclose($arquivoLock);

        exibirMensagem(
            "Ops... este e-mail já está cadastrado!",
            foag_url('cadastro/cadastro.php')
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Criar código aleatório de 5 caracteres
|--------------------------------------------------------------------------
*/

$codigoUsuario = gerarCodigoUsuario($pastaUsuarios);

/*
|--------------------------------------------------------------------------
| Criar nome do arquivo de login
|--------------------------------------------------------------------------
*/

$nomeArquivo = criarNomeArquivoUsuario($nome);

$caminhoArquivoLogin = $pastaLogin . '/' . $nomeArquivo . '.json';

// Impede que duas pessoas com o mesmo nome apaguem uma à outra
if (file_exists($caminhoArquivoLogin)) {
    $caminhoArquivoLogin = $pastaLogin . '/'
        . $nomeArquivo
        . '_'
        . $codigoUsuario
        . '.json';
}

/*
|--------------------------------------------------------------------------
| Criar senha protegida
|--------------------------------------------------------------------------
*/

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

if ($senhaHash === false) {
    flock($arquivoLock, LOCK_UN);
    fclose($arquivoLock);

    exit("Não foi possível proteger a senha.");
}

$respostaSecretaNormalizada = normalizarRespostaSecreta($respostaSecreta);
$respostaSecretaHash = password_hash($respostaSecretaNormalizada, PASSWORD_DEFAULT);

if ($respostaSecretaHash === false) {
    flock($arquivoLock, LOCK_UN);
    fclose($arquivoLock);

    exit("Não foi possível proteger a resposta de segurança.");
}

/*
|--------------------------------------------------------------------------
| Criar pasta individual do usuário
|--------------------------------------------------------------------------
*/

$pastaUsuario = $pastaUsuarios . '/' . $codigoUsuario;

if (!mkdir($pastaUsuario, 0755, true)) {
    flock($arquivoLock, LOCK_UN);
    fclose($arquivoLock);

    exit("Não foi possível criar a pasta individual do usuário.");
}

/*
|--------------------------------------------------------------------------
| Salvar os dados pessoais dentro da pasta do usuário
|--------------------------------------------------------------------------
*/

$dadosPerfil = [
    'codigo_usuario' => $codigoUsuario,
    'nome' => $nome,
    'email' => $email,
    'nascimento' => $dataNascimento,
    'telefone' => $telefone,
    'serie' => $serie,
    'escola' => $escola,
    'termos_aceitos' => true,
    'cadastrado_em' => $dataCadastro
];

if (!salvarJson($pastaUsuario . '/perfil.json', $dadosPerfil)) {
    removerDiretorio($pastaUsuario);

    flock($arquivoLock, LOCK_UN);
    fclose($arquivoLock);

    exit("Não foi possível salvar os dados do perfil.");
}

/*
|--------------------------------------------------------------------------
| Criar arquivos individuais
|--------------------------------------------------------------------------
*/

$arquivosIniciais = [
    'agenda.json',
    'calendario.json',
    'horario.json',
    'pomodoro.json',
    'notas.json'
];

foreach ($arquivosIniciais as $arquivoInicial) {
    $caminhoArquivo = $pastaUsuario . '/' . $arquivoInicial;

    if (!salvarJson($caminhoArquivo, [])) {
        removerDiretorio($pastaUsuario);

        flock($arquivoLock, LOCK_UN);
        fclose($arquivoLock);

        exit("Não foi possível criar o arquivo {$arquivoInicial}.");
    }
}

/*
|--------------------------------------------------------------------------
| Criar arquivo individual de login
|--------------------------------------------------------------------------
*/

$dadosLogin = [
    'nome' => $nome,
    'email' => $email,
    'senha' => $senhaHash,
    'codigo_usuario' => $codigoUsuario,
    'pergunta_secreta' => $perguntaSecreta,
    'resposta_secreta' => $respostaSecretaHash
];

if (!salvarJson($caminhoArquivoLogin, $dadosLogin)) {
    removerDiretorio($pastaUsuario);

    flock($arquivoLock, LOCK_UN);
    fclose($arquivoLock);

    exit("Não foi possível criar o arquivo de login.");
}

flock($arquivoLock, LOCK_UN);
fclose($arquivoLock);

exibirMensagem(
    "Cadastro realizado com sucesso :)",
    "../login/index.php"
);

exit;

/*
|--------------------------------------------------------------------------
| Normalizar resposta de segurança
|--------------------------------------------------------------------------
*/

function normalizarRespostaSecreta(string $resposta): string
{
    $resposta = trim($resposta);
    $resposta = preg_replace('/\s+/u', ' ', $resposta);

    if (function_exists('mb_strtolower')) {
        return mb_strtolower($resposta, 'UTF-8');
    }

    return strtolower($resposta);
}

/*
|--------------------------------------------------------------------------
| Gerar código exclusivo
|--------------------------------------------------------------------------
*/

function gerarCodigoUsuario(string $pastaUsuarios): string
{
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $quantidade = strlen($caracteres);

    do {
        $codigo = '';

        for ($i = 0; $i < 5; $i++) {
            $codigo .= $caracteres[
                random_int(0, $quantidade - 1)
            ];
        }

        $pastaExiste = is_dir(
            $pastaUsuarios . '/' . $codigo
        );

    } while ($pastaExiste);

    return $codigo;
}

/*
|--------------------------------------------------------------------------
| Transformar nome em nome de arquivo
|--------------------------------------------------------------------------
*/

function criarNomeArquivoUsuario(string $nome): string
{
    $nome = trim($nome);
    $nome = preg_replace('/\s+/u', ' ', $nome);

    if (function_exists('mb_strtolower')) {
        $nome = mb_strtolower($nome, 'UTF-8');
    } else {
        $nome = strtolower($nome);
    }

    $acentos = [
        'á' => 'a',
        'à' => 'a',
        'ã' => 'a',
        'â' => 'a',
        'ä' => 'a',
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'í' => 'i',
        'ì' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ó' => 'o',
        'ò' => 'o',
        'õ' => 'o',
        'ô' => 'o',
        'ö' => 'o',
        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ç' => 'c',
        'ñ' => 'n'
    ];

    $nome = strtr($nome, $acentos);
    $nome = preg_replace('/[^a-z0-9]+/', '_', $nome);
    $nome = trim($nome, '_');

    if ($nome === '') {
        return 'Usuario';
    }

    return ucfirst($nome);
}

/*
|--------------------------------------------------------------------------
| Salvar arquivo JSON
|--------------------------------------------------------------------------
*/

function salvarJson(string $caminho, array $dados): bool
{
    $json = json_encode(
        $dados,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    if ($json === false) {
        return false;
    }

    return file_put_contents(
        $caminho,
        $json,
        LOCK_EX
    ) !== false;
}

/*
|--------------------------------------------------------------------------
| Remover pasta incompleta em caso de erro
|--------------------------------------------------------------------------
*/

function removerDiretorio(string $diretorio): void
{
    if (!is_dir($diretorio)) {
        return;
    }

    $arquivos = array_diff(
        scandir($diretorio),
        ['.', '..']
    );

    foreach ($arquivos as $arquivo) {
        $caminho = $diretorio . '/' . $arquivo;

        if (is_dir($caminho)) {
            removerDiretorio($caminho);
        } else {
            unlink($caminho);
        }
    }

    rmdir($diretorio);
}

/*
|--------------------------------------------------------------------------
| Mensagem e redirecionamento
|--------------------------------------------------------------------------
*/

function exibirMensagem(string $mensagem, string $redirect): void
{
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3;url={$redirectSeguro}">
    <title>Mensagem</title>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #38a5ff, rgb(46, 154, 241));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 5em;
        }

        h2 {
            margin-bottom: 10px;
            text-align: center;
        }

        p, small {
            text-align: center;
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
    <h1>FOAG</h1>
    <h2>{$mensagemSegura}</h2>
    <p>Você será redirecionado em alguns segundos...</p>
    <small>Se não for redirecionado, <a href="{$redirectSeguro}">clique aqui</a>.</small>
</body>
</html>
HTML;
}
?>