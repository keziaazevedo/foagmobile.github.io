<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(false);
header('Content-Type: application/json; charset=utf-8');

// ======================================
// LOGIN
// ======================================

if ($codigoUsuario === null) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ======================================
// SOMENTE POST
// ======================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ======================================
// SISTEMA CENTRAL DE PONTOS
// ======================================

$arquivoSistemaPontos =
    __DIR__ . '/../estrelas/adicionar_estrelas.php';

if (!file_exists($arquivoSistemaPontos)) {
    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Sistema de pontos não encontrado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once $arquivoSistemaPontos;

// ======================================
// FUNÇÕES AUXILIARES
// ======================================

function responderLoja(
    bool $sucesso,
    string $mensagem,
    array $extra = [],
    int $status = 200
): void {
    http_response_code($status);

    echo json_encode(
        array_merge([
            'sucesso' => $sucesso,
            'mensagem' => $mensagem
        ], $extra),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}

function salvarJsonLoja(
    string $arquivo,
    array $dados
): bool {
    $json = json_encode(
        $dados,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        return false;
    }

    return file_put_contents(
        $arquivo,
        $json,
        LOCK_EX
    ) !== false;
}

function estruturaLojaUsuario(): array
{
    return [
        'itens_comprados' => [],
        'itens_ativos' => [
            'tema' => null,
            'fundo' => null,
            'moldura' => null,
            'cursor' => null,
            'emoji' => null   // 👈 ADICIONADO
        ]
    ];
}

function normalizarLojaUsuario($dados): array
{
    $padrao = estruturaLojaUsuario();

    if (!is_array($dados)) {
        return $padrao;
    }

    $comprados =
        isset($dados['itens_comprados']) &&
        is_array($dados['itens_comprados'])
            ? array_values(array_unique(
                array_filter(
                    array_map('strval', $dados['itens_comprados']),
                    static fn($id) => trim($id) !== ''
                )
            ))
            : [];

    $ativos =
        isset($dados['itens_ativos']) &&
        is_array($dados['itens_ativos'])
            ? $dados['itens_ativos']
            : [];

    foreach ($padrao['itens_ativos'] as $tipo => $valor) {
        $idAtivo = $ativos[$tipo] ?? null;

        $padrao['itens_ativos'][$tipo] =
            is_string($idAtivo) && trim($idAtivo) !== ''
                ? trim($idAtivo)
                : null;
    }

    $padrao['itens_comprados'] = $comprados;

    return $padrao;
}

function tipoEquipavelProduto(array $produto): ?string
{
    $categoria = (string)($produto['categoria'] ?? '');

    return match ($categoria) {
        'temas' => 'tema',
        'fundos' => 'fundo',
        'molduras' => 'moldura',
        'especiais' => 'cursor',
        'emojis' => 'emoji',   // 👈 ADICIONADO
        default => null
    };
}

function encontrarProdutoPorId(
    array $produtos,
    string $itemId
): ?array {
    foreach ($produtos as $produto) {
        if (!is_array($produto)) {
            continue;
        }

        if ((string)($produto['id'] ?? '') === $itemId) {
            return $produto;
        }
    }

    return null;
}

function gerarIdGastoLoja(): string
{
    try {
        return 'gasto_' . bin2hex(random_bytes(6));
    } catch (Throwable $e) {
        return 'gasto_' . uniqid('', true);
    }
}

// ======================================
// RECEBER JSON
// ======================================

$input = file_get_contents('php://input');

if ($input === false || trim($input) === '') {
    responderLoja(
        false,
        'Nenhum dado foi recebido.',
        [],
        400
    );
}

$dados = json_decode($input, true);

if (
    json_last_error() !== JSON_ERROR_NONE ||
    !is_array($dados)
) {
    responderLoja(
        false,
        'JSON inválido.',
        [],
        400
    );
}

$acao = trim((string)($dados['acao'] ?? ''));
$itemId = trim((string)($dados['item_id'] ?? ''));

if ($acao === '') {
    responderLoja(
        false,
        'Ação não informada.',
        [],
        400
    );
}

if ($itemId === '') {
    responderLoja(
        false,
        'Item não informado.',
        [],
        400
    );
}

// ======================================
// CAMINHOS
// ======================================

$pastaUsuario = foag_pasta_usuario($codigoUsuario);

if (!is_dir($pastaUsuario)) {
    if (!mkdir($pastaUsuario, 0777, true)) {
        responderLoja(
            false,
            'Não foi possível criar a pasta do usuário.',
            [],
            500
        );
    }
}

$arquivoLoja =
    $pastaUsuario . '/loja.json';

$arquivoProdutos =
    FOAG_JSON_DIR . '/loja/produtos.json';

// ======================================
// CATÁLOGO OFICIAL
// ======================================

if (!file_exists($arquivoProdutos)) {
    responderLoja(
        false,
        'Catálogo de produtos não encontrado.',
        [],
        500
    );
}

$produtosData = json_decode(
    file_get_contents($arquivoProdutos),
    true
);

if (
    !is_array($produtosData) ||
    !isset($produtosData['itens']) ||
    !is_array($produtosData['itens'])
) {
    responderLoja(
        false,
        'Catálogo de produtos inválido.',
        [],
        500
    );
}

$produto = encontrarProdutoPorId(
    $produtosData['itens'],
    $itemId
);

if ($produto === null) {
    responderLoja(
        false,
        'Produto não encontrado.',
        [],
        404
    );
}

$preco = (int)($produto['preco'] ?? 0);

if ($preco < 0) {
    responderLoja(
        false,
        'Preço do produto inválido.',
        [],
        500
    );
}

// ======================================
// DADOS DO USUÁRIO NA LOJA
// ======================================

$lojaUsuario = estruturaLojaUsuario();

if (file_exists($arquivoLoja)) {
    $conteudoLoja = file_get_contents($arquivoLoja);

    if ($conteudoLoja !== false) {
        $lojaUsuario = normalizarLojaUsuario(
            json_decode($conteudoLoja, true)
        );
    }
}

// ======================================
// COMPRAR ITEM
// ======================================

if ($acao === 'comprar') {
    if (in_array(
        $itemId,
        $lojaUsuario['itens_comprados'],
        true
    )) {
        $pontos = carregarPontos($codigoUsuario);

        responderLoja(
            false,
            'Este item já foi comprado.',
            [
                'dados' => [
                    'estrelas' => (int)($pontos['estrelas'] ?? 0),
                    'itens_comprados' => $lojaUsuario['itens_comprados'],
                    'itens_ativos' => $lojaUsuario['itens_ativos']
                ]
            ],
            409
        );
    }

    $pontos = carregarPontos($codigoUsuario);
    $pontosAntes = $pontos;

    $saldoAtual = (int)($pontos['estrelas'] ?? 0);

    if ($saldoAtual < $preco) {
        responderLoja(
            false,
            'Você não tem estrelas suficientes para comprar este item.',
            [
                'dados' => [
                    'estrelas' => $saldoAtual,
                    'preco' => $preco,
                    'faltam' => $preco - $saldoAtual,
                    'itens_comprados' => $lojaUsuario['itens_comprados'],
                    'itens_ativos' => $lojaUsuario['itens_ativos']
                ]
            ],
            422
        );
    }

    // O preço é decidido SOMENTE pelo produtos.json.
    $pontos['estrelas'] =
        $saldoAtual - $preco;

    if (!isset($pontos['historico']) || !is_array($pontos['historico'])) {
        $pontos['historico'] = [];
    }

    $timezone = new DateTimeZone('America/Cuiaba');
    $agora = new DateTimeImmutable('now', $timezone);

    $pontos['historico'][] = [
        'id' => gerarIdGastoLoja(),
        'tipo' => 'compra_loja',
        'descricao' => 'Compra na loja: ' . (string)($produto['nome'] ?? $itemId),
        'estrelas' => -$preco,
        'data' => $agora->format('Y-m-d H:i:s'),
        'timestamp' => $agora->getTimestamp(),
        'chave' => 'compra_loja_' . $itemId
    ];

    $lojaUsuario['itens_comprados'][] = $itemId;
    $lojaUsuario['itens_comprados'] = array_values(
        array_unique($lojaUsuario['itens_comprados'])
    );

    // Primeiro salva o saldo.
    if (!salvarPontos($codigoUsuario, $pontos)) {
        responderLoja(
            false,
            'Não foi possível atualizar o saldo de estrelas.',
            [],
            500
        );
    }

    // Depois salva a compra. Se falhar, tenta devolver o saldo.
    if (!salvarJsonLoja($arquivoLoja, $lojaUsuario)) {
        salvarPontos($codigoUsuario, $pontosAntes);

        responderLoja(
            false,
            'Não foi possível concluir a compra. Nenhuma estrela foi descontada.',
            [],
            500
        );
    }

    responderLoja(
        true,
        'Compra realizada com sucesso.',
        [
            'item' => $produto,
            'dados' => [
                'estrelas' => (int)$pontos['estrelas'],
                'itens_comprados' => $lojaUsuario['itens_comprados'],
                'itens_ativos' => $lojaUsuario['itens_ativos']
            ]
        ]
    );
}

// ======================================
// ATIVAR ITEM
// ======================================

if ($acao === 'ativar') {
    if (!in_array(
        $itemId,
        $lojaUsuario['itens_comprados'],
        true
    )) {
        responderLoja(
            false,
            'Você precisa comprar este item antes de ativá-lo.',
            [],
            403
        );
    }

    $tipo = tipoEquipavelProduto($produto);

    if ($tipo === null) {
        responderLoja(
            false,
            'Este item não precisa ser ativado. Ele já está disponível na sua coleção.',
            [
                'dados' => [
                    'itens_comprados' => $lojaUsuario['itens_comprados'],
                    'itens_ativos' => $lojaUsuario['itens_ativos']
                ]
            ],
            422
        );
    }

    $lojaUsuario['itens_ativos'][$tipo] = $itemId;

    if (!salvarJsonLoja($arquivoLoja, $lojaUsuario)) {
        responderLoja(
            false,
            'Não foi possível ativar o item.',
            [],
            500
        );
    }

    $pontos = carregarPontos($codigoUsuario);

    responderLoja(
        true,
        'Item ativado com sucesso.',
        [
            'item' => $produto,
            'tipo_ativo' => $tipo,
            'dados' => [
                'estrelas' => (int)($pontos['estrelas'] ?? 0),
                'itens_comprados' => $lojaUsuario['itens_comprados'],
                'itens_ativos' => $lojaUsuario['itens_ativos']
            ]
        ]
    );
}

responderLoja(
    false,
    'Ação inválida.',
    [],
    400
);
