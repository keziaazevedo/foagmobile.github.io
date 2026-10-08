<?php
require_once __DIR__ . '/../core/usuario.php';
require_once __DIR__ . '/../core/json.php';

$contextoUsuario = foag_contexto_usuario(
    '../login/index.php',
    true
);

$codigoUsuario = $contextoUsuario['codigoUsuario'];
$pastaUsuario = $contextoUsuario['pastaUsuario'];

$current = basename($_SERVER['PHP_SELF']);

// ======================================
// SISTEMA CENTRAL DE PONTOS
// ======================================

$arquivoSistemaPontos =
    __DIR__ .
    '/../estrelas/adicionar_estrelas.php';

if (!file_exists($arquivoSistemaPontos)) {
    exit(
        'Sistema central de pontos não encontrado.'
    );
}

require_once
    $arquivoSistemaPontos;

// ======================================
// FUNÇÕES AUXILIARES
// ======================================

function salvarJsonLojaPagina(
    string $arquivo,
    array $dados
): bool {

    $json =
        json_encode(
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

function estruturaLojaUsuarioPagina(): array
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

function normalizarLojaUsuarioPagina(
    $dados
): array {

    $padrao =
        estruturaLojaUsuarioPagina();

    if (!is_array($dados)) {
        return $padrao;
    }

    if (
        isset($dados['itens_comprados']) &&
        is_array($dados['itens_comprados'])
    ) {
        $padrao['itens_comprados'] =
            array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'strval',
                            $dados['itens_comprados']
                        ),
                        static fn($id) =>
                            trim($id) !== ''
                    )
                )
            );
    }

    if (
        isset($dados['itens_ativos']) &&
        is_array($dados['itens_ativos'])
    ) {
        foreach (
            $padrao['itens_ativos']
            as $tipo => $valor
        ) {
            $id =
                $dados[
                    'itens_ativos'
                ][$tipo] ?? null;

            $padrao[
                'itens_ativos'
            ][$tipo] =
                is_string($id) &&
                trim($id) !== ''
                    ? trim($id)
                    : null;
        }
    }

    return $padrao;
}

function tipoEquipavelPagina(array $produto): ?string
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

function localizarProdutoPagina(
    array $itens,
    string $itemId
): ?array {

    foreach (
        $itens
        as $item
    ) {
        if (
            is_array($item) &&
            (string)(
                $item['id'] ?? ''
            ) ===
            $itemId
        ) {
            return $item;
        }
    }

    return null;
}

// ======================================
// CATÁLOGO GLOBAL
// ======================================

$arquivoProdutos =
    __DIR__ .
    '/../json/loja/produtos.json';

if (!file_exists($arquivoProdutos)) {
    exit(
        'Catálogo não encontrado em json/loja/produtos.json.'
    );
}

$produtosData =
    json_decode(
        file_get_contents(
            $arquivoProdutos
        ),
        true
    );

if (
    !is_array($produtosData) ||
    !isset($produtosData['itens']) ||
    !is_array($produtosData['itens'])
) {
    exit(
        'O arquivo produtos.json está inválido.'
    );
}

$catalogoItens =
    $produtosData['itens'];

// ======================================
// LOJA.JSON DO USUÁRIO
// ======================================

$arquivoLoja =
    $pastaUsuario .
    '/loja.json';

$lojaUsuario =
    estruturaLojaUsuarioPagina();

$lojaAntiga = [];

if (file_exists($arquivoLoja)) {
    $conteudoLoja =
        file_get_contents(
            $arquivoLoja
        );

    if ($conteudoLoja !== false) {
        $lojaAntiga =
            json_decode(
                $conteudoLoja,
                true
            );

        $lojaUsuario =
            normalizarLojaUsuarioPagina(
                $lojaAntiga
            );
    }
}

// ======================================
// MIGRAÇÃO DO ITEM ATIVO ANTIGO
// ======================================
//
// A Loja antiga guardava apenas um item_ativo
// dentro do perfil.json. Se existir, migramos
// uma vez para o novo itens_ativos.
//
// ======================================

$temAlgumAtivo =
    count(
        array_filter(
            $lojaUsuario[
                'itens_ativos'
            ],
            static fn($id) =>
                is_string($id) &&
                trim($id) !== ''
        )
    ) > 0;

if (!$temAlgumAtivo) {
    $arquivoPerfil =
        $pastaUsuario .
        '/perfil.json';

    if (file_exists($arquivoPerfil)) {
        $perfilAntigo =
            json_decode(
                file_get_contents(
                    $arquivoPerfil
                ),
                true
            );

        $itemAtivoAntigo =
            is_array($perfilAntigo)
                ? trim(
                    (string)(
                        $perfilAntigo[
                            'item_ativo'
                        ] ?? ''
                    )
                )
                : '';

        if (
            $itemAtivoAntigo !== '' &&
            in_array(
                $itemAtivoAntigo,
                $lojaUsuario[
                    'itens_comprados'
                ],
                true
            )
        ) {
            $produtoAntigo =
                localizarProdutoPagina(
                    $catalogoItens,
                    $itemAtivoAntigo
                );

            if ($produtoAntigo) {
                $tipo =
                    tipoEquipavelPagina(
                        $produtoAntigo
                    );

                if ($tipo) {
                    $lojaUsuario[
                        'itens_ativos'
                    ][$tipo] =
                        $itemAtivoAntigo;
                }
            }
        }
    }
}

// Salva somente inventário + itens ativos.
// estrelas e catálogo deixam de ficar em loja.json.
salvarJsonLojaPagina(
    $arquivoLoja,
    $lojaUsuario
);

// ======================================
// SALDO REAL DO PONTOS.JSON
// ======================================

$pontosData =
    carregarPontos(
        $codigoUsuario
    );

$saldoEstrelas =
    max(
        0,
        (int)(
            $pontosData[
                'estrelas'
            ] ?? 0
        )
    );

// ======================================
// TEMPO TOTAL ESTUDADO
// ======================================

$arquivoPomodoro =
    $pastaUsuario .
    '/pomodoro.json';

$totalMinutos =
    0;

if (file_exists($arquivoPomodoro)) {
    $pomodoroData =
        json_decode(
            file_get_contents(
                $arquivoPomodoro
            ),
            true
        );

    if (
        is_array($pomodoroData) &&
        isset(
            $pomodoroData[
                'sessions'
            ]
        ) &&
        is_array(
            $pomodoroData[
                'sessions'
            ]
        )
    ) {
        foreach (
            $pomodoroData[
                'sessions'
            ]
            as $sessao
        ) {
            if (!is_array($sessao)) {
                continue;
            }

            if (
                ($sessao[
                    'mode'
                ] ?? '') !==
                'focus'
            ) {
                continue;
            }

            $minutos =
                (int)(
                    $sessao[
                        'minutes'
                    ] ??
                    $sessao[
                        'minutos'
                    ] ??
                    $sessao[
                        'duration'
                    ] ??
                    $sessao[
                        'duracao'
                    ] ??
                    0
                );

            if ($minutos > 0) {
                $totalMinutos +=
                    $minutos;
            }
        }
    }
}

// ======================================
// DADOS ENVIADOS AO JAVASCRIPT
// ======================================

$lojaData = [
    'estrelas' =>
        $saldoEstrelas,

    'total_estudado' =>
        $totalMinutos,

    'itens' =>
        $catalogoItens,

    'itens_comprados' =>
        $lojaUsuario[
            'itens_comprados'
        ],

    'itens_ativos' =>
        $lojaUsuario[
            'itens_ativos'
        ]
];

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>


    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Loja de Estrelas — FOAG
    </title>

    <link
        rel="stylesheet"
        href="loja.css?v=9"
    >

    <link
        rel="stylesheet"
        href="../m.escuro/dark_basee.css"
    >

    <link rel="stylesheet" href="dark_loja.css?v=9">

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <link rel="stylesheet" href="../global/css/cursor.css">

    <script src="../m.escuro/dark-mode.js"></script>



    <script>

        window.LOJA_DATA =
            <?= json_encode(
                $lojaData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;
        window.LOJA_ACTION_URL =
            FOAG_CONFIG.endpoints.lojaSalvar;

        window.USER_ID =
            "<?= htmlspecialchars(
                $codigoUsuario,
                ENT_QUOTES,
                'UTF-8'
            ) ?>";

    </script>


<style>
/* ==========================================
   ÁREA PRINCIPAL + FOOTER
========================================== */
.page-area {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.footer {
    width: 100%;
    margin-top: 30px;
    background: #ffffff;
    border-top: 1px solid #e5edf5;
}

.footer-content {
    width: 100%;
    max-width: 1180px;
    min-height: 50px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}

.footer-left {
    display: flex;
    align-items: center;
    gap: 38px;
}

.footer-brand {
    color: #38a5ff;
    font-size: 17px;
    font-weight: 700;
}

.footer-links {
    display: flex;
    align-items: center;
    gap: 25px;
}

.footer-links a {
    color: #667085;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: color .2s ease;
}

.footer-links a:hover {
    color: #38a5ff;
}

.footer-copy {
    color: #98a2b3;
    font-size: 10px;
    white-space: nowrap;
}

@media (max-width: 768px) {
    .page-area {
        width: 100%;
    }

    .footer {
        margin-top: 20px;
    }

    .footer-content {
        min-height: auto;
        padding: 18px 20px;
        flex-direction: column;
        justify-content: center;
        gap: 12px;
    }

    .footer-left {
        flex-direction: column;
        gap: 10px;
    }

    .footer-links {
        flex-wrap: wrap;
        justify-content: center;
        gap: 16px 22px;
    }
}
</style>
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

    <main class="main-content">

        <div class="loja-header">

            <div class="loja-titulo">

                <h1>
                    <i class="fa-solid fa-store"></i>
                    Loja de Estrelas
                </h1>

                <p>
                    Ganhe estrelas estudando e personalize seu perfil!
                </p>

            </div>

            <div class="loja-saldo">

                <div class="saldo-estrelas">

                    <i class="fa-solid fa-star"></i>

                    <span id="saldoEstrelas">
                        <?= (int) $lojaData['estrelas'] ?>
                    </span>

                    <span class="label">
                        Estrelas
                    </span>

                </div>

                <div class="tempo-estudo">

                    <i class="fa-regular fa-clock"></i>

                    <span>
                        <?= floor(
                            $lojaData['total_estudado'] / 60
                        ) ?>h

                        <?= $lojaData['total_estudado'] % 60 ?>min
                    </span>

                    <span class="label">
                        Estudados
                    </span>

                </div>

                <div class="colecao-header-resumo" id="colecaoHeaderResumo">
                    <div class="colecao-header-topo">
                        <span>
                            <i class="fa-solid fa-box-open"></i>
                            Coleção
                        </span>

                        <strong id="colecaoHeaderContador">
                            0 / 0
                        </strong>
                    </div>

                    <div class="colecao-header-barra">
                        <span id="colecaoHeaderProgresso"></span>
                    </div>
                </div>

            </div>

        </div>

        <!-- VITRINES OPCIONAIS: aparecem apenas se houver
             item.novo / item.destaque no catálogo -->
        <section class="loja-vitrines" id="lojaVitrines" hidden>
            <div class="vitrine-bloco" id="vitrineNovidades" hidden>
                <div class="vitrine-cabecalho">
                    <div>
                        <span class="vitrine-selo">Novidades</span>
                        <h2>Novos na Loja</h2>
                    </div>
                    <button type="button" class="vitrine-ver" data-vitrine-filtro="todos">
                        Ver na loja
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
                <div class="vitrine-itens" id="vitrineNovidadesItens"></div>
            </div>

            <div class="vitrine-bloco" id="vitrineDestaques" hidden>
                <div class="vitrine-cabecalho">
                    <div>
                        <span class="vitrine-selo destaque">Destaques</span>
                        <h2>Itens em destaque</h2>
                    </div>
                    <button type="button" class="vitrine-ver" data-vitrine-filtro="todos">
                        Ver na loja
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
                <div class="vitrine-itens" id="vitrineDestaquesItens"></div>
            </div>
        </section>

        <!-- ==================================
             FILTROS
        =================================== -->

        <div class="loja-filtros">

            <button
                class="filtro-btn active"
                data-filtro="todos"
            >
                <i class="fa-solid fa-th-list"></i>
                Todos
            </button>

            <button
                class="filtro-btn"
                data-filtro="temas"
            >
                <i class="fa-solid fa-palette"></i>
                Temas
            </button>

            <button
                class="filtro-btn"
                data-filtro="emojis"
            >
                <i class="fa-regular fa-face-smile"></i>
                Emojis
            </button>

            <button
                class="filtro-btn"
                data-filtro="fundos"
            >
                <i class="fa-solid fa-image"></i>
                Fundos
            </button>

            <button
                class="filtro-btn"
                data-filtro="molduras"
            >
                <i class="fa-regular fa-image"></i>
                Molduras
            </button>

            <button
                class="filtro-btn"
                data-filtro="especiais"
            >
                <i class="fa-solid fa-mouse-pointer"></i>
                Cursores
            </button>

            <button
                class="filtro-btn"
                data-filtro="comprados"
            >
                <i class="fa-solid fa-check-circle"></i>
                Meus Itens
            </button>

        </div>

        <!-- ==================================
             PAINEL DA COLEÇÃO
        =================================== -->

        <section
            class="colecao-painel"
            id="colecaoPainel"
            hidden
        >
            <div class="colecao-painel-info">
                <span class="colecao-painel-icone">
                    <i class="fa-solid fa-box-open"></i>
                </span>

                <div>
                    <span class="colecao-painel-sobretitulo">
                        Minha coleção
                    </span>
                    <h2>
                        Seus itens do FOAG
                    </h2>
                    <p id="colecaoPainelTexto">
                        Veja tudo que você já conquistou na Loja.
                    </p>
                </div>
            </div>

            <div class="colecao-painel-status">
                <div class="colecao-painel-numeros">
                    <strong id="colecaoPainelContador">0 / 0</strong>
                    <span id="colecaoPainelPercentual">0%</span>
                </div>
                <div class="colecao-painel-barra">
                    <span id="colecaoPainelProgresso"></span>
                </div>
            </div>

            <div class="colecao-em-uso" id="colecaoEmUso"></div>
        </section>

        <!-- ==================================
             ITENS DA LOJA
        =================================== -->

        <div
            class="loja-grid"
            id="lojaGrid"
        >
        </div>

        <!-- ==================================
             MODAL DE COMPRA
        =================================== -->

        <div
            id="modal-compra"
            class="modal-compra"
        >

            <div class="modal-content modal-loja-padronizado">

                <button
                    type="button"
                    class="modal-fechar-x"
                    id="fechar-compra-x"
                    aria-label="Fechar"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <span
                    class="modal-categoria"
                    id="modalCategoria"
                >
                    Item
                </span>

                <div
                    class="modal-icon"
                    id="modalIcone"
                >
                    <i class="fa-solid fa-gift"></i>
                </div>

                <h3 id="modalTitulo">
                    Comprar Item
                </h3>

                <p id="modalDescricao">
                    Tem certeza que deseja comprar este item?
                </p>

                <div class="modal-compra-resumo">
                    <div class="modal-preco">
                        <span>Preço</span>
                        <strong>
                            <i class="fa-solid fa-star"></i>
                            <span id="modalPreco">0</span>
                        </strong>
                    </div>

                    <div class="modal-saldo">
                        <span>Seu saldo</span>
                        <strong>
                            <i class="fa-solid fa-star"></i>
                            <span id="modalSaldoAtual">0</span>
                        </strong>
                    </div>

                    <div class="modal-saldo restante">
                        <span>Após a compra</span>
                        <strong>
                            <i class="fa-solid fa-star"></i>
                            <span id="modalSaldoRestante">0</span>
                        </strong>
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-experimentar-modal"
                    id="experimentar-item-modal"
                    hidden
                >
                    <i class="fa-regular fa-eye"></i>
                    Visualizar item
                </button>

                <div class="modal-buttons">

                    <button
                        id="confirmar-compra"
                        class="btn-comprar"
                    >
                        <i class="fa-solid fa-cart-shopping"></i>
                        Comprar
                    </button>

                    <button
                        id="cancelar-compra"
                        class="btn-cancelar"
                    >
                        Cancelar
                    </button>

                </div>

            </div>

        </div>

        <!-- ==================================
             MODAL DE PREVIEW
        =================================== -->

        <div
            id="modal-preview-item"
            class="modal-preview-item"
        >
            <div class="modal-content modal-loja-padronizado">
                <button
                    type="button"
                    class="modal-fechar-x"
                    id="fechar-preview-item"
                    aria-label="Fechar"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <span class="modal-categoria" id="previewCategoria">
                    Preview
                </span>

                <div class="preview-palco" id="previewPalco">
                    <div class="preview-item-visual" id="previewItemVisual"></div>
                </div>

                <h3 id="previewTitulo">Visualizar item</h3>

                <p id="previewDescricao">
                    Veja como o item aparece antes de comprar.
                </p>

            </div>
        </div>

        <div
            class="loja-toast"
            id="lojaToast"
            role="status"
            aria-live="polite"
        >
            <div class="loja-toast-icone" id="lojaToastIcone">
                <i class="fa-solid fa-check"></i>
            </div>

            <div class="loja-toast-conteudo">
                <strong id="lojaToastTitulo">
                    Tudo certo!
                </strong>
                <span id="lojaToastTexto"></span>
            </div>

            <button
                type="button"
                class="loja-toast-fechar"
                id="lojaToastFechar"
                aria-label="Fechar"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- ==================================
             MODAL DE SUCESSO
        =================================== -->

        <div
            id="modal-sucesso"
            class="modal-sucesso"
        >

            <div class="modal-content">

                <div class="sucesso-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>

                <h3>
                    Compra realizada!
                </h3>

                <p id="mensagemSucesso">
                    Item adicionado ao seu perfil!
                </p>

                <button
                    id="fechar-sucesso"
                    class="btn-modal"
                >
                    OK
                </button>

            </div>

        </div>

    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>

</div>

</div>

<!-- ======================================
     MODAL LOGOUT
======================================= -->

<div
    id="logout-modal"
    class="modal"
>

    <div class="modal-content modal-loja-padronizado">

        <button
            type="button"
            class="modal-fechar-x"
            id="fechar-logout-x"
            aria-label="Fechar"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h3>
            Ah... já vai?
        </h3>

        <h4>
            Tem certeza de que deseja sair?
        </h4>

        <div class="modal-buttons">

            <button id="confirm-logout">
                Sim
            </button>

            <button id="cancel-logout">
                Cancelar
            </button>

        </div>

    </div>

</div>


<script src="../global/js/cursor.js?v=<?= time() ?>"></script>

<script src="loja.js?v=<?= time() ?>"></script>

<script src="../configuracoes/aparencia.js?v=5"></script>
<script src="../configuracoes/acessibilidade.js?v=25" defer></script>
</body>
</html>