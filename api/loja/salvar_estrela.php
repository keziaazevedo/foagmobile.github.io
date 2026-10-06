<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(false);
header('Content-Type: application/json; charset=utf-8');

// ======================================
// VERIFICAR LOGIN
// ======================================

if ($codigoUsuario === null) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado']);
    exit;
}

// ======================================
// PASTA DO USUÁRIO
// ======================================

$baseJsonDir = foag_base_usuarios();
$pastaUsuario = foag_pasta_usuario($codigoUsuario);

if (!is_dir($pastaUsuario)) {
    mkdir($pastaUsuario, 0755, true);
}

// ======================================
// RECEBER DADOS
// ======================================

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos']);
    exit;
}

$minutos = intval($input['minutos'] ?? 0);
$disciplina = trim($input['disciplina'] ?? 'Geral');

if ($minutos <= 0) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Minutos inválidos']);
    exit;
}

// ======================================
// CALCULAR ESTRELAS (1 a cada 15 min)
// ======================================

$estrelasGanhas = floor($minutos / 15);

if ($estrelasGanhas <= 0) {
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Estude mais para ganhar estrelas! (Mínimo 15 min)',
        'estrelas' => 0,
        'minutos' => $minutos
    ]);
    exit;
}

// ======================================
// CARREGAR LOJA.JSON
// ======================================

$arquivoLoja = $pastaUsuario . '/loja.json';
$lojaData = foag_ler_json($arquivoLoja);

if (empty($lojaData)) {
    $lojaData = [
        'estrelas' => 0,
        'total_estudado' => 0,
        'itens_comprados' => [],
        'itens' => []
    ];
}

// ======================================
// ATUALIZAR ESTRELAS
// ======================================

$lojaData['estrelas'] = ($lojaData['estrelas'] ?? 0) + $estrelasGanhas;
$lojaData['total_estudado'] = ($lojaData['total_estudado'] ?? 0) + $minutos;

// Salvar
if (foag_salvar_json($arquivoLoja, $lojaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) {
    echo json_encode([
        'sucesso' => true,
        'mensagem' => "🎉 Você ganhou {$estrelasGanhas} estrelas!",
        'estrelas' => $estrelasGanhas,
        'total_estrelas' => $lojaData['estrelas'],
        'minutos' => $minutos,
        'disciplina' => $disciplina
    ]);
} else {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar estrelas']);
}