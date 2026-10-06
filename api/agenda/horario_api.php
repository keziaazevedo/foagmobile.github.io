<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(true);
// horario_api.php — devolve o HTML do horário salvo para o usuário logado

header('Content-Type: application/json; charset=utf-8');

// precisa estar logado
if ($codigoUsuario === null) {
    echo json_encode([
        'ok'    => false,
        'error' => 'NOT_LOGGED'
    ]);
    exit;
}

$userId = $codigoUsuario;

// caminho do JSON: ../json/usuarios/{id}/horario.json
$baseJsonDir = foag_base_usuarios();
$pastaUsuario = foag_pasta_usuario($userId);
$arquivoHorario = $pastaUsuario . '/horario.json';

// se não existir ainda, devolve vazio
if (!file_exists($arquivoHorario)) {
    echo json_encode([
        'ok'   => true,
        'html' => ''   // sem horário cadastrado ainda
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// lê o arquivo
$dados = json_decode(file_get_contents($arquivoHorario), true);

// garantia de estrutura
if (!is_array($dados)) {
    $dados = ['html' => ''];
}

// aqui o importante é ter a chave 'html'
// que é exatamente o que o horario.php usa:
$html = $dados['html'] ?? '';

echo json_encode([
    'ok'   => true,
    'html' => $html
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>