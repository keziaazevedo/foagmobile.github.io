<?php
session_start();

// ======================================
// LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {
    header('Location: ../login/index.php');
    exit;
}

$codigoUsuario =
    $_SESSION['codigo_usuario'];

$current =
    basename(
        $_SERVER['PHP_SELF']
    );

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
// PASTA DO USUÁRIO
// ======================================

$pastaUsuario =
    __DIR__ .
    '/../json/usuarios/' .
    $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    if (!mkdir(
        $pastaUsuario,
        0777,
        true
    )) {
        exit(
            'Não foi possível criar a pasta do usuário.'
        );
    }
}

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
            'cursor' => null
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

function tipoEquipavelPagina(
    array $produto
): ?string {

    $categoria =
        (string)(
            $produto[
                'categoria'
            ] ?? ''
        );

    return match (
        $categoria
    ) {
        'temas' =>
            'tema',

        'fundos' =>
            'fundo',

        'molduras' =>
            'moldura',

        'especiais' =>
            'cursor',

        default =>
            null
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
        href="loja.css?v=2"
    >

    <link
        rel="stylesheet"
        href="../m.escuro/dark_basee.css"
    >

    <link
        rel="stylesheet"
        href="dark_loja.css"
    >

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

    <script src="../m.escuro/dark-mode.js"></script>



    <script>

        window.LOJA_DATA =
            <?= json_encode(
                $lojaData,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;
        window.LOJA_ACTION_URL =
            "salvar_loja.php";

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

</head>

<body>

<header class="cabecalho">

    FOAG

    <div class="header-icons">

    <a href="../configuracoes/configuracoes.php" class="link-configuracoes" title="Configurações">
      <i class="fa-solid fa-gear"></i>
          </a>

        <a href="../perfil/perfil.php" class="link-perfil" title="Perfil">
            <i class="fa-regular fa-user"></i>
        </a>

        <i
            id="icon-sair"
            class="fa-solid fa-right-from-bracket"
            title="Sair">
        </i>

    </div>

</header>

<div class="container">

    <nav class="menu">
        <a href="../inicioo/inicio.php" class="<?= $current === 'inicio.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> Início
        </a>

        <a href="../estudos/estudos.php" class="<?= $current === 'estudos.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-graduation-cap"></i> Estudos
        </a>

        <a href="../bloco/agenda.php" class="<?= $current === 'agenda.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-book"></i> Agenda
        </a>

        <a href="../calend/calendario.php" class="<?= $current === 'calendario.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-days"></i> Calendário
        </a>

        <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-check-double"></i> Boletim
        </a>

        <a href="../comunidade/comunidade.php" class="<?= $current === 'comunidade.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-comments"></i> Comunidade
        </a>

        <a href="../rank/rank.php" class="<?= $current === 'rank.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-trophy"></i> Ranking
        </a>

        <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-store"></i> Loja
        </a>
    </nav>

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

            </div>

        </div>

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
                Especiais
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

            <div class="modal-content">

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

                <div class="modal-preco">

                    <i class="fa-solid fa-star"></i>

                    <span id="modalPreco">
                        0
                    </span>

                </div>

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

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <span class="footer-brand">FOAG</span>

                <nav class="footer-links">
                    <a href="../sobre/sobre.php">Sobre</a>
                    <a href="../contato/contato.php">Contato</a>
                    <a href="../privacidade/privacidade.php">Privacidade</a>
                </nav>
            </div>

            <span class="footer-copy">
                © <?= date('Y') ?> FOAG
            </span>
        </div>
    </footer>

</div>

</div>

<!-- ======================================
     MODAL LOGOUT
======================================= -->

<div
    id="logout-modal"
    class="modal"
>

    <div class="modal-content">

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

<!-- ======================================
     JAVASCRIPT DA LOJA
======================================= -->

<script src="loja.js?v=<?= time() ?>"></script>

<script src="../configuracoes/aparencia.js?v=5"></script>
<script src="../configuracoes/acessibilidade.js?v=25" defer></script>
</body>
</html>