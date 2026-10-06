<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$codigoUsuario = $_SESSION['codigo_usuario'] ?? null;

if (!$codigoUsuario) {
    echo json_encode([
        'ok' => false,
        'codigo_usuario' => null,
        'cursor' => null
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$arquivoLoja = __DIR__ . '/../json/usuarios/' . $codigoUsuario . '/loja.json';
$cursorAtivo = null;

if (is_file($arquivoLoja)) {
    $dados = json_decode(file_get_contents($arquivoLoja), true);

    if (
        is_array($dados) &&
        isset($dados['itens_ativos']) &&
        is_array($dados['itens_ativos'])
    ) {
        $cursorAtivo = $dados['itens_ativos']['cursor'] ?? null;
    }
}

echo json_encode([
    'ok' => true,
    'codigo_usuario' => $codigoUsuario,
    'cursor' => $cursorAtivo
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
