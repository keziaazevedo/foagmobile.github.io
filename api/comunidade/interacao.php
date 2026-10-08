<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(true);
// interacao.php — Responder/excluir respostas em perguntas de qualquer usuário.

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
$dados = json_decode($raw ?: '', true);

if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
    responderJson([
        'ok' => false,
        'erro' => 'JSON_INVALIDO',
        'mensagem' => 'JSON inválido.'
    ], 400);
}

$acao = trim((string) ($dados['acao'] ?? ''));
$perguntaId = trim((string) ($dados['pergunta_id'] ?? ''));

if ($perguntaId === '') {
    responderJson([
        'ok' => false,
        'erro' => 'PERGUNTA_ID_AUSENTE',
        'mensagem' => 'ID da pergunta não informado.'
    ], 400);
}

$baseJsonDir = foag_base_usuarios();

if (!is_dir($baseJsonDir)) {
    responderJson([
        'ok' => false,
        'erro' => 'PASTA_USUARIOS_NAO_ENCONTRADA',
        'mensagem' => 'Pasta de usuários não encontrada.'
    ], 500);
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

function salvarArquivoBloqueado($fp, $chat)
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
            'erro' => 'ERRO_JSON',
            'mensagem' => 'Não foi possível preparar os dados.'
        ], 500);
    }

    rewind($fp);
    ftruncate($fp, 0);

    if (fwrite($fp, $json) === false) {
        flock($fp, LOCK_UN);
        fclose($fp);

        responderJson([
            'ok' => false,
            'erro' => 'ERRO_SALVAR',
            'mensagem' => 'Não foi possível salvar a interação.'
        ], 500);
    }

    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

$pastas = scandir($baseJsonDir);

foreach ($pastas as $pasta) {
    if ($pasta === '.' || $pasta === '..') continue;

    $pastaCompleta = $baseJsonDir . '/' . $pasta;
    if (!is_dir($pastaCompleta)) continue;

    $arquivoChat = $pastaCompleta . '/chat.json';
    if (!file_exists($arquivoChat)) continue;

    $fp = @fopen($arquivoChat, 'c+');
    if (!$fp) continue;

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        continue;
    }

    rewind($fp);
    $conteudo = stream_get_contents($fp);
    $chat = json_decode($conteudo ?: '{"perguntas":[]}', true);

    if (
        !is_array($chat) ||
        !isset($chat['perguntas']) ||
        !is_array($chat['perguntas'])
    ) {
        flock($fp, LOCK_UN);
        fclose($fp);
        continue;
    }

    $indicePergunta = null;

    foreach ($chat['perguntas'] as $indice => $pergunta) {
        if ((string) ($pergunta['id'] ?? '') === $perguntaId) {
            $indicePergunta = $indice;
            break;
        }
    }

    if ($indicePergunta === null) {
        flock($fp, LOCK_UN);
        fclose($fp);
        continue;
    }

    if (!isset($chat['perguntas'][$indicePergunta]['respostas'])
        || !is_array($chat['perguntas'][$indicePergunta]['respostas'])) {
        $chat['perguntas'][$indicePergunta]['respostas'] = [];
    }

    // ==========================================
    // RESPONDER
    // ==========================================

    if ($acao === 'responder') {
        $texto = trim((string) ($dados['texto'] ?? ''));

        if ($texto === '') {
            flock($fp, LOCK_UN);
            fclose($fp);

            responderJson([
                'ok' => false,
                'erro' => 'RESPOSTA_VAZIA',
                'mensagem' => 'Escreva uma resposta antes de enviar.'
            ], 400);
        }

        if (mb_strlen($texto) > 5000) {
            flock($fp, LOCK_UN);
            fclose($fp);

            responderJson([
                'ok' => false,
                'erro' => 'RESPOSTA_LONGA',
                'mensagem' => 'A resposta é muito longa.'
            ], 400);
        }

        $novaResposta = [
            'id' => gerarIdSeguro(),
            'usuario_id' => $codigoUsuario,
            'autor' => (string) $nomeUsuario,
            'texto' => censurarServidor($texto, $palavrasProibidas),
            'data' => gmdate('c')
        ];

        $chat['perguntas'][$indicePergunta]['respostas'][] = $novaResposta;

        salvarArquivoBloqueado($fp, $chat);

        responderJson([
            'ok' => true,
            'mensagem' => 'Resposta salva com sucesso.',
            'resposta' => $novaResposta,
            'pergunta_id' => $perguntaId,
            'dono_pergunta_id' => (string) $pasta
        ]);
    }

    // ==========================================
    // EXCLUIR RESPOSTA
    // ==========================================

    if ($acao === 'excluir_resposta') {
        $respostaId = trim((string) ($dados['resposta_id'] ?? ''));

        if ($respostaId === '') {
            flock($fp, LOCK_UN);
            fclose($fp);

            responderJson([
                'ok' => false,
                'erro' => 'RESPOSTA_ID_AUSENTE',
                'mensagem' => 'ID da resposta não informado.'
            ], 400);
        }

        $respostas = $chat['perguntas'][$indicePergunta]['respostas'];
        $indiceResposta = null;

        foreach ($respostas as $indice => $resposta) {
            if ((string) ($resposta['id'] ?? '') === $respostaId) {
                $indiceResposta = $indice;
                break;
            }
        }

        if ($indiceResposta === null) {
            flock($fp, LOCK_UN);
            fclose($fp);

            responderJson([
                'ok' => false,
                'erro' => 'RESPOSTA_NAO_ENCONTRADA',
                'mensagem' => 'Resposta não encontrada.'
            ], 404);
        }

        $resposta = $respostas[$indiceResposta];

        $usuarioResposta = isset($resposta['usuario_id'])
            ? (string) $resposta['usuario_id']
            : '';

        $podeExcluir =
            ($usuarioResposta !== '' && $usuarioResposta === $codigoUsuario)
            ||
            (
                $usuarioResposta === ''
                && (string) ($resposta['autor'] ?? '') === (string) $nomeUsuario
            );

        if (!$podeExcluir) {
            flock($fp, LOCK_UN);
            fclose($fp);

            responderJson([
                'ok' => false,
                'erro' => 'SEM_PERMISSAO',
                'mensagem' => 'Você só pode excluir suas próprias respostas.'
            ], 403);
        }

        array_splice(
            $chat['perguntas'][$indicePergunta]['respostas'],
            $indiceResposta,
            1
        );

        salvarArquivoBloqueado($fp, $chat);

        responderJson([
            'ok' => true,
            'mensagem' => 'Resposta excluída com sucesso.'
        ]);
    }

    flock($fp, LOCK_UN);
    fclose($fp);

    responderJson([
        'ok' => false,
        'erro' => 'ACAO_INVALIDA',
        'mensagem' => 'Ação inválida.'
    ], 400);
}

responderJson([
    'ok' => false,
    'erro' => 'PERGUNTA_NAO_ENCONTRADA',
    'mensagem' => 'Pergunta não encontrada.'
], 404);
