<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(true);
// salvar_interacao.php — Salva curtidas e perguntas salvas do usuário.

header('Content-Type: application/json; charset=utf-8');

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

$raw = file_get_contents('php://input');
$dados = json_decode($raw ?: '', true);

if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
    responderJson([
        'ok' => false,
        'erro' => 'JSON_INVALIDO',
        'mensagem' => 'JSON inválido.'
    ], 400);
}

$curtidas = isset($dados['curtidas']) && is_array($dados['curtidas'])
    ? $dados['curtidas']
    : [];

$salvos = isset($dados['salvos']) && is_array($dados['salvos'])
    ? $dados['salvos']
    : [];

$normalizarIds = function ($lista) {
    $resultado = [];

    foreach ($lista as $id) {
        if (!is_scalar($id)) continue;

        $id = trim((string) $id);
        if ($id === '') continue;

        if (!in_array($id, $resultado, true)) {
            $resultado[] = $id;
        }
    }

    return $resultado;
};

$interacoes = [
    'curtidas' => $normalizarIds($curtidas),
    'salvos' => $normalizarIds($salvos)
];

$baseJsonDir = foag_base_usuarios();
$pastaUsuario = $baseJsonDir . '/' . (string) $codigoUsuario;
$arquivoInteracoes = $pastaUsuario . '/interacoes.json';

if (!is_dir($pastaUsuario)) {
    if (!@mkdir($pastaUsuario, 0755, true) && !is_dir($pastaUsuario)) {
        responderJson([
            'ok' => false,
            'erro' => 'ERRO_CRIAR_PASTA',
            'mensagem' => 'Não foi possível criar a pasta do usuário.'
        ], 500);
    }
}

$json = json_encode(
    $interacoes,
    JSON_PRETTY_PRINT |
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

if ($json === false || @file_put_contents($arquivoInteracoes, $json, LOCK_EX) === false) {
    responderJson([
        'ok' => false,
        'erro' => 'ERRO_SALVAR_INTERACOES',
        'mensagem' => 'Não foi possível salvar as interações.'
    ], 500);
}

responderJson([
    'ok' => true,
    'mensagem' => 'Interações salvas com sucesso.',
    'interacoes' => $interacoes
]);
