<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(true);
// salvar_chat.php — Cria/exclui perguntas sem sobrescrever respostas de outros usuários.

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function responderJson($dados, $status = 200)
{
    http_response_code($status);
    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responderJson([
        'ok' => false,
        'erro' => 'METODO_INVALIDO',
        'mensagem' => 'Use uma requisição POST.'
    ], 405);
}

if (!$codigoUsuario) {
    responderJson([
        'ok' => false,
        'erro' => 'USUARIO_NAO_LOGADO',
        'mensagem' => 'Usuário não autenticado.'
    ], 401);
}

$codigoUsuario = (string) $codigoUsuario;

$nomeUsuario =
    $_SESSION['user_nome']
    ?? $_SESSION['nome_usuario']
    ?? $_SESSION['nome']
    ?? 'Usuário';

$raw = file_get_contents('php://input');

if ($raw === false || trim($raw) === '') {
    responderJson([
        'ok' => false,
        'erro' => 'DADOS_VAZIOS',
        'mensagem' => 'Nenhum dado foi recebido.'
    ], 400);
}

$dados = json_decode($raw, true);

if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
    responderJson([
        'ok' => false,
        'erro' => 'JSON_INVALIDO',
        'mensagem' => 'JSON inválido.'
    ], 400);
}

$baseJsonDir = foag_base_usuarios();
$pastaUsuario = foag_pasta_usuario($codigoUsuario);
$arquivoChat = $pastaUsuario . '/chat.json';

if (!is_dir($pastaUsuario)) {
    if (!@mkdir($pastaUsuario, 0755, true) && !is_dir($pastaUsuario)) {
        responderJson([
            'ok' => false,
            'erro' => 'ERRO_CRIAR_PASTA',
            'mensagem' => 'Não foi possível criar a pasta do usuário.'
        ], 500);
    }
}

$arquivoPalavras = __DIR__ . '/../../comunidade/palavram.php';
$palavrasProibidas = file_exists($arquivoPalavras)
    ? require $arquivoPalavras
    : [];

if (!is_array($palavrasProibidas)) {
    $palavrasProibidas = [];
}

function censurarServidor($texto, $palavras)
{
    $resultado = trim((string) $texto);

    usort($palavras, function ($a, $b) {
        return mb_strlen((string) $b) <=> mb_strlen((string) $a);
    });

    foreach ($palavras as $palavra) {
        $palavra = trim((string) $palavra);
        if ($palavra === '') continue;

        $padrao = '/\b' . preg_quote($palavra, '/') . '\b/iu';

        $resultado = preg_replace_callback(
            $padrao,
            function ($matches) {
                return str_repeat('*', mb_strlen($matches[0]));
            },
            $resultado
        );
    }

    return $resultado;
}

function gerarIdSeguro()
{
    try {
        $sufixo = bin2hex(random_bytes(4));
    } catch (Throwable $e) {
        $sufixo = substr(md5(uniqid('', true)), 0, 8);
    }

    return (string) round(microtime(true) * 1000) . '_' . $sufixo;
}

function abrirChatBloqueado($arquivoChat)
{
    $fp = @fopen($arquivoChat, 'c+');

    if (!$fp) {
        responderJson([
            'ok' => false,
            'erro' => 'ERRO_ABRIR_CHAT',
            'mensagem' => 'Não foi possível abrir o chat.'
        ], 500);
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);

        responderJson([
            'ok' => false,
            'erro' => 'ERRO_BLOQUEAR_CHAT',
            'mensagem' => 'Não foi possível bloquear o arquivo do chat.'
        ], 500);
    }

    rewind($fp);
    $conteudo = stream_get_contents($fp);
    $chat = json_decode($conteudo ?: '{"perguntas":[]}', true);

    if (!is_array($chat) || !isset($chat['perguntas']) || !is_array($chat['perguntas'])) {
        $chat = ['perguntas' => []];
    }

    return [$fp, $chat];
}

function salvarChatBloqueado($fp, $chat)
{
    $json = json_encode(
        $chat,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        flock($fp, LOCK_UN);
        fclose($fp);

        responderJson([
            'ok' => false,
            'erro' => 'ERRO_JSON_CHAT',
            'mensagem' => 'Não foi possível preparar o chat.'
        ], 500);
    }

    rewind($fp);
    ftruncate($fp, 0);

    if (fwrite($fp, $json) === false) {
        flock($fp, LOCK_UN);
        fclose($fp);

        responderJson([
            'ok' => false,
            'erro' => 'ERRO_SALVAR_CHAT',
            'mensagem' => 'Não foi possível salvar o chat.'
        ], 500);
    }

    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

$acao = trim((string) ($dados['acao'] ?? ''));

// =================================================
// NOVA PERGUNTA
// =================================================

if ($acao === 'criar_pergunta') {
    $texto = trim((string) ($dados['texto'] ?? ''));
    $materia = trim((string) ($dados['materia'] ?? 'Geral'));

    if (mb_strlen($texto) < 5) {
        responderJson([
            'ok' => false,
            'erro' => 'PERGUNTA_CURTA',
            'mensagem' => 'A pergunta precisa ter pelo menos 5 caracteres.'
        ], 400);
    }

    if (mb_strlen($texto) > 5000) {
        responderJson([
            'ok' => false,
            'erro' => 'PERGUNTA_LONGA',
            'mensagem' => 'A pergunta é muito longa.'
        ], 400);
    }

    if ($materia === '') $materia = 'Geral';
    $materia = mb_substr($materia, 0, 80);

    $novaPergunta = [
        'id' => gerarIdSeguro(),
        'usuario_id' => $codigoUsuario,
        'autor' => (string) $nomeUsuario,
        'texto' => censurarServidor($texto, $palavrasProibidas),
        'materia' => $materia,
        'data' => gmdate('c'),
        'respostas' => []
    ];

    [$fp, $chat] = abrirChatBloqueado($arquivoChat);
    $chat['perguntas'][] = $novaPergunta;
    salvarChatBloqueado($fp, $chat);

    responderJson([
        'ok' => true,
        'mensagem' => 'Pergunta publicada com sucesso.',
        'pergunta' => $novaPergunta
    ]);
}

// =================================================
// EXCLUIR PERGUNTA
// =================================================

if ($acao === 'excluir_pergunta') {
    $perguntaId = trim((string) ($dados['pergunta_id'] ?? ''));

    if ($perguntaId === '') {
        responderJson([
            'ok' => false,
            'erro' => 'ID_AUSENTE',
            'mensagem' => 'ID da pergunta não informado.'
        ], 400);
    }

    [$fp, $chat] = abrirChatBloqueado($arquivoChat);

    $encontrou = false;

    $chat['perguntas'] = array_values(array_filter(
        $chat['perguntas'],
        function ($pergunta) use ($perguntaId, &$encontrou) {
            if ((string) ($pergunta['id'] ?? '') === $perguntaId) {
                $encontrou = true;
                return false;
            }
            return true;
        }
    ));

    if (!$encontrou) {
        flock($fp, LOCK_UN);
        fclose($fp);

        responderJson([
            'ok' => false,
            'erro' => 'PERGUNTA_NAO_ENCONTRADA',
            'mensagem' => 'Pergunta não encontrada.'
        ], 404);
    }

    salvarChatBloqueado($fp, $chat);

    responderJson([
        'ok' => true,
        'mensagem' => 'Pergunta excluída com sucesso.'
    ]);
}

// =================================================
// COMPATIBILIDADE COM O JS ANTIGO
// =================================================

if (isset($dados['perguntas']) && is_array($dados['perguntas'])) {
    $chat = ['perguntas' => $dados['perguntas']];

    // O modo antigo continua funcionando, mas o novo JS não usa esta rota
    // para respostas, evitando sobrescrever respostas gravadas por outros usuários.
    $json = json_encode(
        $chat,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    if ($json === false || @file_put_contents($arquivoChat, $json, LOCK_EX) === false) {
        responderJson([
            'ok' => false,
            'erro' => 'ERRO_SALVAR_CHAT',
            'mensagem' => 'Não foi possível salvar o chat.'
        ], 500);
    }

    responderJson([
        'ok' => true,
        'mensagem' => 'Chat salvo com sucesso.'
    ]);
}

responderJson([
    'ok' => false,
    'erro' => 'ACAO_INVALIDA',
    'mensagem' => 'Ação inválida.'
], 400);
