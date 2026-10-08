<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(false);
// ======================================
// VERIFICAR LOGIN - USANDO codigo_usuario
// ======================================

if ($codigoUsuario === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensagem' => 'Não autenticado']);
    exit;
}

// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = foag_base_usuarios();
$pastaUsuario = foag_pasta_usuario($codigoUsuario);

// ======================================
// CARREGAR DADOS DA LOJA
// ======================================

$arquivoLoja = $pastaUsuario . '/loja.json';

if (!file_exists($arquivoLoja)) {
    echo json_encode([
        'estrelas' => 0,
        'total_estudado' => 0,
        'itens_comprados' => [],
        'itens' => []
    ]);
    exit;
}

$dados = foag_ler_json($arquivoLoja);

if (empty($dados)) {
    $dados = ['estrelas' => 0, 'total_estudado' => 0, 'itens_comprados' => [], 'itens' => []];
}

echo json_encode($dados);