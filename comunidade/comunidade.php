<?php
// comunidade.php — Comunidade FOAG

require_once __DIR__ . '/../core/usuario.php';
require_once __DIR__ . '/../core/json.php';

$contextoUsuario = foag_contexto_usuario(
    '../login/index.php',
    true,
    false,
    0755
);

$codigoUsuario = $contextoUsuario['codigoUsuario'];
$baseJsonDir = $contextoUsuario['baseJsonDir'];
$pastaUsuario = $contextoUsuario['pastaUsuario'];
$nomeUsuario =
    $_SESSION['user_nome']
    ?? $_SESSION['nome_usuario']
    ?? $_SESSION['nome']
    ?? 'Usuário';

$current = basename($_SERVER['PHP_SELF']);


// ======================================
// AVATARES DA COMUNIDADE
// Foto + Moldura + Emoji
// ======================================

$pastaFotosUrl = '../img/perfil/';
$pastaFotosArquivo = __DIR__ . '/../img/perfil/';
$fotoPadrao = 'foto_padrao.png';

function lerJsonComunidade($arquivo)
{
    if (!file_exists($arquivo)) {
        return [];
    }

    $conteudo = file_get_contents($arquivo);

    if ($conteudo === false) {
        return [];
    }

    $dados = json_decode($conteudo, true);

    return is_array($dados) ? $dados : [];
}

function normalizarNomeComunidade($nome)
{
    $nome = trim((string) $nome);

    if (function_exists('mb_strtolower')) {
        return mb_strtolower($nome, 'UTF-8');
    }

    return strtolower($nome);
}

function normalizarAjusteMolduraComunidade($ajuste)
{
    $padrao = [
        'moldura_escala' => 1.28,
        'moldura_x' => 0,
        'moldura_y' => 0,
        'foto_escala' => 1.00,
        'foto_x' => 0,
        'foto_y' => 0
    ];

    if (!is_array($ajuste)) {
        return $padrao;
    }

    foreach ($padrao as $chave => $valorPadrao) {
        if (
            array_key_exists($chave, $ajuste) &&
            is_numeric($ajuste[$chave])
        ) {
            $padrao[$chave] = (float) $ajuste[$chave];
        }
    }

    return $padrao;
}

// ======================================
// CATÁLOGO DA LOJA
// ======================================

$arquivoProdutosLoja = __DIR__ . '/../json/loja/produtos.json';
$dadosProdutosLoja = lerJsonComunidade($arquivoProdutosLoja);

$itensCatalogoLoja =
    isset($dadosProdutosLoja['itens']) &&
    is_array($dadosProdutosLoja['itens'])
        ? $dadosProdutosLoja['itens']
        : [];

// Molduras indexadas por ID
$moldurasLojaPorId = [];

// Emojis indexados por ID
$emojisLojaPorId = [];

foreach ($itensCatalogoLoja as $produtoLoja) {
    if (!is_array($produtoLoja)) {
        continue;
    }

    $categoriaProduto = (string)($produtoLoja['categoria'] ?? '');
    $idProduto = trim((string)($produtoLoja['id'] ?? ''));
    $imagemProduto = trim((string)($produtoLoja['imagem'] ?? ''));

    if ($idProduto === '' || $imagemProduto === '') {
        continue;
    }

    // ------------------------------
    // MOLDURAS
    // ------------------------------
    if ($categoriaProduto === 'molduras') {
        $moldurasLojaPorId[$idProduto] = [
            'id' => $idProduto,
            'nome' => (string)($produtoLoja['nome'] ?? 'Moldura'),
            'imagem' => $imagemProduto,
            'ajuste_perfil' => normalizarAjusteMolduraComunidade(
                $produtoLoja['ajuste_perfil'] ?? []
            )
        ];
        continue;
    }

    // ------------------------------
    // EMOJIS
    // ------------------------------
    if ($categoriaProduto === 'emojis') {
        $emojisLojaPorId[$idProduto] = [
            'id' => $idProduto,
            'nome' => (string)($produtoLoja['nome'] ?? 'Emoji'),
            'imagem' => $imagemProduto
        ];
    }
}

// ======================================
// VISUAL DOS USUÁRIOS
// ======================================

$usuariosVisuais = [];
$usuariosPorNome = [];

$pastasUsuariosVisual = glob(
    $baseJsonDir . '/*',
    GLOB_ONLYDIR
);

if ($pastasUsuariosVisual === false) {
    $pastasUsuariosVisual = [];
}

foreach ($pastasUsuariosVisual as $pastaVisual) {
    $codigoVisual = (string) basename($pastaVisual);

    // ------------------------------
    // PERFIL
    // ------------------------------
    $perfilVisual = lerJsonComunidade(
        $pastaVisual . '/perfil.json'
    );

    $nomeVisual = trim((string)($perfilVisual['nome'] ?? ''));

    if ($nomeVisual === '') {
        $nomeVisual = 'Usuário FOAG';
    }

    // ------------------------------
    // FOTO
    // ------------------------------
    $fotoVisual = $fotoPadrao;

    if (!empty($perfilVisual['foto'])) {
        $fotoArquivo = basename((string)$perfilVisual['foto']);

        if (
            $fotoArquivo !== '' &&
            file_exists($pastaFotosArquivo . $fotoArquivo)
        ) {
            $fotoVisual = $fotoArquivo;
        }
    }

    $caminhoFotoVisual =
        $pastaFotosUrl .
        rawurlencode($fotoVisual);

    // ------------------------------
    // LOJA (MOLDURA + EMOJI)
    // ------------------------------
    $lojaVisual = lerJsonComunidade(
        $pastaVisual . '/loja.json'
    );

    $itensAtivosVisual =
        isset($lojaVisual['itens_ativos']) &&
        is_array($lojaVisual['itens_ativos'])
            ? $lojaVisual['itens_ativos']
            : [];

    $itensCompradosVisual =
        isset($lojaVisual['itens_comprados']) &&
        is_array($lojaVisual['itens_comprados'])
            ? $lojaVisual['itens_comprados']
            : [];

    // ------------------------------
    // MOLDURA ATIVA
    // ------------------------------
    $molduraVisual = null;

    $idMolduraVisual =
        isset($itensAtivosVisual['moldura'])
            ? trim((string)$itensAtivosVisual['moldura'])
            : '';

    if (
        $idMolduraVisual !== '' &&
        in_array($idMolduraVisual, $itensCompradosVisual, true) &&
        isset($moldurasLojaPorId[$idMolduraVisual])
    ) {
        $molduraVisual = $moldurasLojaPorId[$idMolduraVisual];
    }

    // ------------------------------
    // EMOJI ATIVO
    // ------------------------------
    $emojiVisual = null;

    $idEmojiVisual =
        isset($itensAtivosVisual['emoji'])
            ? trim((string)$itensAtivosVisual['emoji'])
            : '';

    if (
        $idEmojiVisual !== '' &&
        in_array($idEmojiVisual, $itensCompradosVisual, true) &&
        isset($emojisLojaPorId[$idEmojiVisual])
    ) {
        $emojiVisual = $emojisLojaPorId[$idEmojiVisual];
    }

    // ------------------------------
// EMOJI ATIVO
// ------------------------------
$emojiVisual = null;

$idEmojiVisual =
    isset($itensAtivosVisual['emoji'])
        ? trim((string)$itensAtivosVisual['emoji'])
        : '';

if (
    $idEmojiVisual !== '' &&
    in_array($idEmojiVisual, $itensCompradosVisual, true) &&
    isset($emojisLojaPorId[$idEmojiVisual])
) {
    $emojiVisual = $emojisLojaPorId[$idEmojiVisual];
}

    // ------------------------------
    // VISUAL FINAL
    // ------------------------------
    $visual = [
        'codigo_usuario' => $codigoVisual,
        'nome' => $nomeVisual,
        'foto' => $caminhoFotoVisual,
        'moldura' => $molduraVisual,
        'emoji' => $emojiVisual
    ];

    $usuariosVisuais[$codigoVisual] = $visual;

    $chaveNome = normalizarNomeComunidade($nomeVisual);

    /*
     * Compatibilidade com posts/respostas antigos que
     * ainda não possuem usuario_id.
     * Em caso de nomes repetidos, mantemos o primeiro.
     */
    if (
        $chaveNome !== '' &&
        !isset($usuariosPorNome[$chaveNome])
    ) {
        $usuariosPorNome[$chaveNome] = $codigoVisual;
    }
}

/*
 * Usa o nome real do perfil na Comunidade quando disponível.
 */
if (
    isset($usuariosVisuais[$codigoUsuario]) &&
    !empty($usuariosVisuais[$codigoUsuario]['nome'])
) {
    $nomeUsuario = $usuariosVisuais[$codigoUsuario]['nome'];
}

// ======================================
// PALAVRAS PROIBIDAS
// ======================================

$arquivoPalavras = __DIR__ . '/palavram.php';
$palavrasProibidas = file_exists($arquivoPalavras)
    ? require $arquivoPalavras
    : [];

if (!is_array($palavrasProibidas)) {
    $palavrasProibidas = [];
}

function censurarTexto($texto, $palavrasProibidas)
{
    if ($texto === null || $texto === '') return '';

    $resultado = (string) $texto;

    usort($palavrasProibidas, function ($a, $b) {
        return mb_strlen((string) $b) <=> mb_strlen((string) $a);
    });

    foreach ($palavrasProibidas as $palavra) {
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

function limparPerguntaParaExibicao($pergunta, $palavrasProibidas)
{
    if (!is_array($pergunta)) return null;

    $pergunta['texto'] = censurarTexto(
        $pergunta['texto'] ?? $pergunta['texto_original'] ?? '',
        $palavrasProibidas
    );

    unset($pergunta['texto_original']);

    if (!isset($pergunta['respostas']) || !is_array($pergunta['respostas'])) {
        $pergunta['respostas'] = [];
    }

    foreach ($pergunta['respostas'] as &$resposta) {
        if (!is_array($resposta)) continue;

        $resposta['texto'] = censurarTexto(
            $resposta['texto'] ?? $resposta['texto_original'] ?? '',
            $palavrasProibidas
        );

        unset($resposta['texto_original']);
    }
    unset($resposta);

    return $pergunta;
}

// ======================================
// CHAT DO USUÁRIO
// ======================================

$arquivoChatUsuario = $pastaUsuario . '/chat.json';
$estruturaChatPadrao = ['perguntas' => []];

if (!file_exists($arquivoChatUsuario)) {
    file_put_contents(
        $arquivoChatUsuario,
        json_encode(
            $estruturaChatPadrao,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ),
        LOCK_EX
    );
}

$chatData = json_decode(
    (string) @file_get_contents($arquivoChatUsuario),
    true
);

if (!is_array($chatData) || !isset($chatData['perguntas']) || !is_array($chatData['perguntas'])) {
    $chatData = $estruturaChatPadrao;
}

$chatDataExibicao = ['perguntas' => []];

foreach ($chatData['perguntas'] as $pergunta) {
    $limpa = limparPerguntaParaExibicao($pergunta, $palavrasProibidas);
    if ($limpa) {
        $limpa['usuario_id'] = $codigoUsuario;
        $chatDataExibicao['perguntas'][] = $limpa;
    }
}

// ======================================
// INTERAÇÕES
// ======================================

$arquivoInteracoes = $pastaUsuario . '/interacoes.json';
$interacoesPadrao = [
    'curtidas' => [],
    'salvos' => []
];

if (!file_exists($arquivoInteracoes)) {
    file_put_contents(
        $arquivoInteracoes,
        json_encode(
            $interacoesPadrao,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ),
        LOCK_EX
    );
}

$interacoes = json_decode(
    (string) @file_get_contents($arquivoInteracoes),
    true
);

if (!is_array($interacoes)) {
    $interacoes = $interacoesPadrao;
}

if (!isset($interacoes['curtidas']) || !is_array($interacoes['curtidas'])) {
    $interacoes['curtidas'] = [];
}

if (!isset($interacoes['salvos']) || !is_array($interacoes['salvos'])) {
    $interacoes['salvos'] = [];
}

// ======================================
// TODAS AS PERGUNTAS
// ======================================

$todasPerguntas = [];

if (is_dir($baseJsonDir)) {
    $pastas = scandir($baseJsonDir);

    foreach ($pastas as $pasta) {
        if ($pasta === '.' || $pasta === '..') continue;

        $pastaCompleta = $baseJsonDir . '/' . $pasta;
        if (!is_dir($pastaCompleta)) continue;

        $arquivoChat = $pastaCompleta . '/chat.json';
        if (!file_exists($arquivoChat)) continue;

        $dados = json_decode(
            (string) @file_get_contents($arquivoChat),
            true
        );

        if (
            !is_array($dados) ||
            !isset($dados['perguntas']) ||
            !is_array($dados['perguntas'])
        ) {
            continue;
        }

        foreach ($dados['perguntas'] as $pergunta) {
            $limpa = limparPerguntaParaExibicao(
                $pergunta,
                $palavrasProibidas
            );

            if (!$limpa) continue;

            $limpa['usuario_id'] = (string) $pasta;
            $todasPerguntas[] = $limpa;
        }
    }
}

usort($todasPerguntas, function ($a, $b) {
    return strtotime($b['data'] ?? '1970-01-01')
        <=> strtotime($a['data'] ?? '1970-01-01');
});

// ======================================
// MATÉRIAS
// ======================================

$materias = ['Geral'];

foreach ($todasPerguntas as $pergunta) {
    $materia = trim((string) ($pergunta['materia'] ?? 'Geral'));
    if ($materia !== '' && !in_array($materia, $materias, true)) {
        $materias[] = $materia;
    }
}

sort($materias, SORT_NATURAL | SORT_FLAG_CASE);

// ======================================
// FILTROS
// ======================================

$filtroMateria = trim((string) ($_GET['materia'] ?? 'todas'));
$filtroBusca = trim((string) ($_GET['busca'] ?? ''));
$abaAtiva = ($_GET['aba'] ?? 'minhas') === 'explorar'
    ? 'explorar'
    : 'minhas';

if ($filtroMateria !== 'todas') {
    $todasPerguntas = array_values(array_filter(
        $todasPerguntas,
        function ($pergunta) use ($filtroMateria) {
            return (string) ($pergunta['materia'] ?? 'Geral') === $filtroMateria;
        }
    ));
}

if ($filtroBusca !== '') {
    $buscaLower = mb_strtolower($filtroBusca);

    $todasPerguntas = array_values(array_filter(
        $todasPerguntas,
        function ($pergunta) use ($buscaLower) {
            $texto = mb_strtolower((string) ($pergunta['texto'] ?? ''));
            $autor = mb_strtolower((string) ($pergunta['autor'] ?? ''));
            $materia = mb_strtolower((string) ($pergunta['materia'] ?? ''));

            return mb_strpos($texto, $buscaLower) !== false
                || mb_strpos($autor, $buscaLower) !== false
                || mb_strpos($materia, $buscaLower) !== false;
        }
    ));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comunidade - FOAG</title>

    <link rel="stylesheet" href="comunidade.css?v=5">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="dark_comu.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <script src="../m.escuro/dark-mode.js"></script>
    <script src="../configuracoes/aparencia.js?v=1"></script>

    <link rel="stylesheet" href="../acessibilidade/acessibilidade.css">
    <script src="../acessibilidade/acessibilidade.js?v=13" defer></script>

    <script>
        window.CHAT_DATA = <?= json_encode(
            $chatDataExibicao,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;

        window.TODAS_PERGUNTAS = <?= json_encode(
            $todasPerguntas,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;

        window.CHAT_SAVE_URL = FOAG_CONFIG.endpoints.comunidadeChatSalvar;
        window.INTERACAO_URL = FOAG_CONFIG.endpoints.comunidadeInteracao;
        window.INTERACOES_SAVE_URL = FOAG_CONFIG.endpoints.comunidadeInteracoesSalvar;

        window.USUARIO_NOME = <?= json_encode(
            $nomeUsuario,
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;

        window.USUARIO_CODIGO = <?= json_encode(
            $codigoUsuario,
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;

        window.USUARIOS_VISUAIS = <?= json_encode(
            $usuariosVisuais,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;

        window.USUARIOS_POR_NOME = <?= json_encode(
            $usuariosPorNome,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;

        window.INTERACOES = <?= json_encode(
            $interacoes,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;

        window.ABA_ATIVA = <?= json_encode($abaAtiva); ?>;
        window.FILTRO_MATERIA = <?= json_encode($filtroMateria); ?>;
        window.FILTRO_BUSCA = <?= json_encode($filtroBusca); ?>;

        window.PALAVRAS_PROIBIDAS = <?= json_encode(
            $palavrasProibidas,
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;
    </script>
    <link rel="stylesheet" href="../global/css/cursor.css">
    <link rel="stylesheet" href="../global/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/forms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/tables.css?v=<?= time() ?>">

    <link rel="stylesheet" href="../global/css/layout.css?v=<?= time() ?>">
</head>

<body>

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="container">

   <?php include __DIR__ . '/../components/menu.php'; ?>

    <div class="page-area">
        <main class="main-content" id="conteudo-principal" tabindex="-1">

        <section class="chat-card">

            <div class="chat-header">
                <div>
                    <h2>
                        <i class="fa-solid fa-comments"></i>
                        Comunidade FOAG
                    </h2>
                    <p>Tire dúvidas, ajude outros alunos e compartilhe conhecimento.</p>
                </div>

                <div class="chat-stats">
                    <span>
                        <i class="fa-regular fa-message"></i>
                        <span id="total-perguntas">
                            <?= count($chatDataExibicao['perguntas']) ?>
                        </span>
                        minhas
                    </span>

                    <span>
                        <i class="fa-regular fa-compass"></i>
                        <span id="total-explorar">
                            <?= count($todasPerguntas) ?>
                        </span>
                        comunidade
                    </span>
                </div>
            </div>

            <div class="censure-notice">
                <i class="fa-solid fa-shield-halved"></i>
                <span>
                    Este espaço é para aprendizado. Palavras ofensivas serão
                    automaticamente censuradas com <strong>*</strong>.
                </span>
            </div>

            <div class="abas-container">
                <button
                    class="aba-btn <?= $abaAtiva === 'minhas' ? 'ativo' : '' ?>"
                    data-aba="minhas"
                >
                    <i class="fa-regular fa-user"></i>
                    Minhas Perguntas
                    <span class="badge">
                        <?= count($chatDataExibicao['perguntas']) ?>
                    </span>
                </button>

                <button
                    class="aba-btn <?= $abaAtiva === 'explorar' ? 'ativo' : '' ?>"
                    data-aba="explorar"
                >
                    <i class="fa-solid fa-compass"></i>
                    Explorar
                    <span class="badge"><?= count($todasPerguntas) ?></span>
                </button>
            </div>

            <div
                class="aba-conteudo <?= $abaAtiva === 'minhas' ? 'ativo' : '' ?>"
                id="aba-minhas"
            >

                <div class="pergunta-form">
                    <textarea
                        id="pergunta-texto"
                        placeholder="Faça sua pergunta para a comunidade..."
                        rows="3"
                    ></textarea>

                    <div class="form-actions">
                        <div class="left">
                            <select id="pergunta-materia">
                                <option value="Geral">Geral</option>
                                <option value="Matemática">Matemática</option>
                                <option value="Português">Português</option>
                                <option value="Ciências">Ciências</option>
                                <option value="História">História</option>
                                <option value="Geografia">Geografia</option>
                                <option value="Inglês">Inglês</option>
                                <option value="Artes">Artes</option>
                                <option value="Educação Física">Educação Física</option>
                                <option value="Química">Química</option>
                                <option value="Física">Física</option>
                                <option value="Biologia">Biologia</option>
                                <option value="Filosofia">Filosofia</option>
                                <option value="Sociologia">Sociologia</option>
                                <option value="Programação">Programação</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>

                        <button class="btn-postar" id="btn-postar-pergunta">
                            <i class="fa-regular fa-paper-plane"></i>
                            Publicar pergunta
                        </button>
                    </div>

                    <div
                        id="censure-preview"
                        style="
                            display:none;
                            margin-top:10px;
                            padding:10px;
                            background:#fef2f2;
                            border-radius:8px;
                            font-size:14px;
                            color:#ef4444;
                        "
                    >
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span id="censure-preview-text"></span>
                    </div>
                </div>

                <div
                    class="perguntas-lista"
                    id="minhas-perguntas-lista"
                ></div>
            </div>

            <div
                class="aba-conteudo <?= $abaAtiva === 'explorar' ? 'ativo' : '' ?>"
                id="aba-explorar"
            >

                <div class="filtros-container">
                    <div class="busca-wrapper">
                        <input
                            type="text"
                            id="busca-input"
                            placeholder="Buscar perguntas, autores ou matérias..."
                            value="<?= htmlspecialchars($filtroBusca, ENT_QUOTES, 'UTF-8') ?>"
                        >

                        <button id="btn-buscar">
                            <i class="fa-solid fa-search"></i>
                            Buscar
                        </button>
                    </div>

                    <div class="filtro-materia">
                        <label for="filtro-materia">
                            <i class="fa-solid fa-tag"></i>
                            Matéria:
                        </label>

                        <select id="filtro-materia">
                            <option
                                value="todas"
                                <?= $filtroMateria === 'todas' ? 'selected' : '' ?>
                            >
                                Todas
                            </option>

                            <?php foreach ($materias as $materia): ?>
                                <option
                                    value="<?= htmlspecialchars($materia, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $filtroMateria === $materia ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($materia, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button
                        class="btn-limpar-filtros"
                        id="btn-limpar-filtros"
                    >
                        <i class="fa-solid fa-rotate-left"></i>
                        Limpar
                    </button>
                </div>

                <div
                    class="perguntas-lista"
                    id="explorar-perguntas-lista"
                ></div>
            </div>

        </section>
        </main>

        <?php include __DIR__ . '/../components/footer.php'; ?>
    </div>
</div>

<div
    id="logout-modal"
    class="modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo-logout"
>
    <div class="modal-content">
        <h3 id="titulo-logout">Ah... já vai?</h3>
        <h4>Tem certeza de que deseja sair?</h4>

        <div class="modal-buttons">
            <button id="confirm-logout">Sim</button>
            <button id="cancel-logout">Cancelar</button>
        </div>
    </div>
</div>

<div
    id="modal-excluir"
    class="modal-excluir"
    role="dialog"
    aria-modal="true"
    aria-labelledby="excluir-titulo"
>
    <div class="modal-content">
        <div class="excluir-icon">
            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
        </div>

        <h3 id="excluir-titulo">Excluir Pergunta</h3>

        <p id="excluir-mensagem">
            Tem certeza que deseja excluir esta pergunta?
            Todas as respostas também serão removidas.
        </p>

        <div class="modal-buttons">
            <button
                id="confirmar-exclusao"
                class="btn-excluir-confirmar"
            >
                Excluir
            </button>

            <button
                id="cancelar-exclusao"
                class="btn-cancelar"
            >
                Cancelar
            </button>
        </div>
    </div>
</div>


<script src="comunidade.js?v=5"></script>

<script src="../configuracoes/aparencia.js?v=5"></script>
<script src="../configuracoes/acessibilidade.js?v=25" defer></script>

    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>