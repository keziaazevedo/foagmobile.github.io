<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(false);
header('Content-Type: application/json; charset=utf-8');

// ==============================
// VERIFICAR LOGIN
// ==============================

if ($codigoUsuario === null) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado.'
    ]);

    exit;
}

// ==============================
// PASTA DO USUÁRIO
// ==============================

$baseJsonDir = foag_base_usuarios();
$pastaUsuario = foag_pasta_usuario($codigoUsuario);

if (!is_dir($pastaUsuario)) {
    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Pasta do usuário não encontrada.'
    ]);

    exit;
}

$arquivoMaterias = $pastaUsuario . '/materias.json';

// ==============================
// RECEBER JSON
// ==============================

$entrada = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($entrada)) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos.'
    ]);

    exit;
}

$nome = trim($entrada['nome'] ?? '');
$cor = trim($entrada['cor'] ?? '#38a5ff');
$icone = trim($entrada['icone'] ?? 'fa-book');

// ==============================
// VALIDAR NOME
// ==============================

if ($nome === '') {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Digite o nome da matéria.'
    ]);

    exit;
}

if (mb_strlen($nome) > 50) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'O nome da matéria é muito grande.'
    ]);

    exit;
}

// ==============================
// VALIDAR COR
// ==============================

if (!preg_match('/^#[0-9a-fA-F]{6}$/', $cor)) {
    $cor = '#38a5ff';
}

// ==============================
// ÍCONES PERMITIDOS
// ==============================

$iconesPermitidos = [
    'fa-book',
    'fa-calculator',
    'fa-flask',
    'fa-dna',
    'fa-globe',
    'fa-landmark',
    'fa-language',
    'fa-laptop-code'
];

if (!in_array($icone, $iconesPermitidos, true)) {
    $icone = 'fa-book';
}

// ==============================
// CARREGAR ARQUIVO
// ==============================

if (!file_exists($arquivoMaterias)) {
    $dados = [
        'materias' => []
    ];
} else {
    $dados = json_decode(
        file_get_contents($arquivoMaterias),
        true
    );

    if (!is_array($dados)) {
        $dados = [
            'materias' => []
        ];
    }
}

if (
    !isset($dados['materias']) ||
    !is_array($dados['materias'])
) {
    $dados['materias'] = [];
}

// ==============================
// VERIFICAR MATÉRIA REPETIDA
// ==============================

foreach ($dados['materias'] as $materia) {
    if (
        mb_strtolower(trim($materia['nome'] ?? '')) ===
        mb_strtolower($nome)
    ) {
        http_response_code(409);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Essa matéria já foi cadastrada.'
        ]);

        exit;
    }
}

// ==============================
// CRIAR MATÉRIA
// ==============================

try {
    $id = 'MAT_' . strtoupper(
        bin2hex(random_bytes(4))
    );
} catch (Exception $e) {
    $id = 'MAT_' . uniqid();
}

$novaMateria = [
    'id' => $id,
    'nome' => $nome,
    'cor' => $cor,
    'icone' => $icone,
    'criado_em' => date('Y-m-d H:i:s')
];

$dados['materias'][] = $novaMateria;

// ==============================
// SALVAR
// ==============================

$salvou = file_put_contents(
    $arquivoMaterias,
    json_encode(
        $dados,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ),
    LOCK_EX
);

if ($salvou === false) {
    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível salvar a matéria.'
    ]);

    exit;
}

// ==============================
// RESPOSTA
// ==============================

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Matéria adicionada com sucesso.',
    'materia' => $novaMateria
], JSON_UNESCAPED_UNICODE);