<?php
session_start();

$current = basename($_SERVER['PHP_SELF']);

/*
|--------------------------------------------------------------------------
| Verificar login
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['codigo_usuario'])) {
    header('Location: ../login/index.php');
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

/*
|--------------------------------------------------------------------------
| Localizar pasta e perfil do usuário
|--------------------------------------------------------------------------
*/

$pastaUsuario = __DIR__ . '/../json/usuarios/' . $codigoUsuario;
$caminhoPerfil = $pastaUsuario . '/perfil.json';

$pasta_fotos_url = "../img/perfil/";
$pasta_fotos_arquivo = __DIR__ . "/../img/perfil/";
$foto_padrao = "foto_padrao.png";

/*
|--------------------------------------------------------------------------
| Funções
|--------------------------------------------------------------------------
*/

function escapar($valor)
{
    return htmlspecialchars(
        $valor ?? "Não informado",
        ENT_QUOTES,
        "UTF-8"
    );
}

function formatarData($data)
{
    if (empty($data)) {
        return "Não informado";
    }

    $dataFormatada = DateTime::createFromFormat("Y-m-d", $data);
    return $dataFormatada ? $dataFormatada->format("d/m/Y") : $data;
}

/*
|--------------------------------------------------------------------------
| Verificar pasta individual
|--------------------------------------------------------------------------
*/

if (!is_dir($pastaUsuario)) {
    exit("Pasta do usuário não encontrada.");
}

if (!file_exists($caminhoPerfil)) {
    exit("Perfil do usuário não encontrado.");
}

/*
|--------------------------------------------------------------------------
| Carregar perfil.json
|--------------------------------------------------------------------------
*/

$conteudoPerfil = file_get_contents($caminhoPerfil);

if ($conteudoPerfil === false) {
    exit("Não foi possível carregar o perfil.");
}

$usuario_logado = json_decode($conteudoPerfil, true);

if (!is_array($usuario_logado)) {
    exit("Os dados do perfil estão inválidos.");
}

/*
|--------------------------------------------------------------------------
| Dados exibidos
|--------------------------------------------------------------------------
*/

$nome = $usuario_logado["nome"] ?? "Usuário FOAG";
$email = $usuario_logado["email"] ?? $_SESSION["user_email"] ?? "Não informado";
$nascimento = formatarData($usuario_logado["nascimento"] ?? "");
$telefone = $usuario_logado["telefone"] ?? "Não informado";
$serie = $usuario_logado["serie"] ?? "Não informado";
$escola = $usuario_logado["escola"] ?? "Não informado";

$cidade = $usuario_logado["cidade"] ?? "";
$estado = $usuario_logado["estado"] ?? "";

if ($cidade !== '' && $estado !== '') {
    $localidade = $cidade . ' - ' . $estado;
} elseif ($cidade !== '') {
    $localidade = $cidade;
} elseif ($estado !== '') {
    $localidade = $estado;
} else {
    $localidade = "Não informado";
}

/*
|--------------------------------------------------------------------------
| Foto
|--------------------------------------------------------------------------
*/

$foto_perfil = $foto_padrao;

if (!empty($usuario_logado["foto"])) {
    $foto_usuario = basename($usuario_logado["foto"]);
    if (file_exists($pasta_fotos_arquivo . $foto_usuario)) {
        $foto_perfil = $foto_usuario;
    }
}

$caminho_foto = $pasta_fotos_url . $foto_perfil;


/*
|--------------------------------------------------------------------------
| Moldura ativa da Loja + ajustes individuais
|--------------------------------------------------------------------------
|
| Cada moldura pode ter uma área interna diferente. O produtos.json guarda
| escala/posição da moldura e escala/posição da foto separadamente.
| Modo de calibração: perfil.php?calibrar=1
|
*/

$caminhoLojaUsuario = $pastaUsuario . '/loja.json';
$caminhoProdutosLoja = __DIR__ . '/../json/loja/produtos.json';

$ajuste_moldura_padrao = [
    'moldura_escala' => 1.28,
    'moldura_x' => 0,
    'moldura_y' => 0,
    'foto_escala' => 1.00,
    'foto_x' => 0,
    'foto_y' => 0
];

$moldura_ativa_id = null;
$moldura_ativa_imagem = null;
$moldura_ativa_nome = null;
$moldura_ativa_ajuste = $ajuste_moldura_padrao;
$molduras_catalogo = [];
$dadosProdutosLoja = [];

if (file_exists($caminhoProdutosLoja)) {
    $conteudoProdutos = file_get_contents($caminhoProdutosLoja);

    if ($conteudoProdutos !== false) {
        $dadosProdutosLoja = json_decode($conteudoProdutos, true);

        if (!is_array($dadosProdutosLoja)) {
            $dadosProdutosLoja = [];
        }
    }
}

$produtosLoja =
    isset($dadosProdutosLoja['itens']) &&
    is_array($dadosProdutosLoja['itens'])
        ? $dadosProdutosLoja['itens']
        : [];

foreach ($produtosLoja as $produtoLoja) {
    if (
        !is_array($produtoLoja) ||
        (string)($produtoLoja['categoria'] ?? '') !== 'molduras'
    ) {
        continue;
    }

    $ajusteProduto = $ajuste_moldura_padrao;

    if (
        isset($produtoLoja['ajuste_perfil']) &&
        is_array($produtoLoja['ajuste_perfil'])
    ) {
        foreach ($ajuste_moldura_padrao as $chave => $valorPadrao) {
            if (
                array_key_exists($chave, $produtoLoja['ajuste_perfil']) &&
                is_numeric($produtoLoja['ajuste_perfil'][$chave])
            ) {
                $ajusteProduto[$chave] =
                    (float)$produtoLoja['ajuste_perfil'][$chave];
            }
        }
    }

    $molduras_catalogo[] = [
        'id' => (string)($produtoLoja['id'] ?? ''),
        'nome' => (string)($produtoLoja['nome'] ?? 'Moldura'),
        'imagem' => (string)($produtoLoja['imagem'] ?? ''),
        'ajuste_perfil' => $ajusteProduto
    ];
}

if (file_exists($caminhoLojaUsuario)) {
    $conteudoLoja = file_get_contents($caminhoLojaUsuario);
    $dadosLojaUsuario =
        $conteudoLoja !== false
            ? json_decode($conteudoLoja, true)
            : null;

    if (
        is_array($dadosLojaUsuario) &&
        isset($dadosLojaUsuario['itens_ativos']) &&
        is_array($dadosLojaUsuario['itens_ativos'])
    ) {
        $idMoldura =
            $dadosLojaUsuario['itens_ativos']['moldura'] ?? null;

        $itensComprados =
            isset($dadosLojaUsuario['itens_comprados']) &&
            is_array($dadosLojaUsuario['itens_comprados'])
                ? $dadosLojaUsuario['itens_comprados']
                : [];

        if (
            is_string($idMoldura) &&
            trim($idMoldura) !== '' &&
            in_array(trim($idMoldura), $itensComprados, true)
        ) {
            $idMoldura = trim($idMoldura);

            foreach ($molduras_catalogo as $molduraCatalogo) {
                if ((string)$molduraCatalogo['id'] !== $idMoldura) {
                    continue;
                }

                if (trim((string)$molduraCatalogo['imagem']) !== '') {
                    $moldura_ativa_id = $idMoldura;
                    $moldura_ativa_imagem = (string)$molduraCatalogo['imagem'];
                    $moldura_ativa_nome = (string)$molduraCatalogo['nome'];
                    $moldura_ativa_ajuste = $molduraCatalogo['ajuste_perfil'];
                }

                break;
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Carregar insígnias
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/insignias.php';
verificarDesbloquearInsignias($codigoUsuario);
$insignias_usuario = getInsigniasUsuario($codigoUsuario);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG - Perfil</title>
    
    <!-- FONTES -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS PRINCIPAL DO PERFIL -->
    <link rel="stylesheet" href="perfilfil.css?v=15">
    
    <!-- DARK MODE BASE -->
    <link rel="stylesheet" href="../m.escuro/dark_basee.css?v=12">
    
    <!-- ACESSIBILIDADE -->
    <link rel="stylesheet" href="../acessibilidade/acessibilidade.css?v=12">
    
    <!-- DARK MODE ESPECÍFICO DO PERFIL (DEIXAR POR ÚLTIMO) -->
    <link rel="stylesheet" href="dark-per.css?v=12">
    
    <!-- SCRIPTS (DEFER PARA CARREGAR DEPOIS) -->
    <script src="../acessibilidade/acessibilidade.js?v=4" defer></script>
    <script src="../m.escuro/dark-mode.js"></script>
</head>

<body>
    <header class="cabecalho">
        FOAG
        <div class="header-icons">
            <a href="../configuracoes/configuracoes.php" class="link-configuracoes" title="Configurações">
                <i class="fa-solid fa-gear"></i>
            </a>
            <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
            <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
        </div>
    </header>

    <div class="container">
        <!-- Menu lateral -->
        <nav class="menu">
            <a href="../inicioo/inicio.php" class="<?= $current === 'inicio.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> Início
            </a>

            <a href="../calend/calendario.php" class="<?= $current === 'calendario.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i> Calendário
            </a>

            <a href="../bloco/agenda.php" class="<?= $current === 'agenda.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-book"></i> Agenda
            </a>

            <a href="../estudos/estudos.php" class="<?= $current === 'estudos.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-graduation-cap"></i> Estudos
            </a>

            <a href="../notas/notas.php" class="<?= $current === 'notas.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-check-double"></i> Boletim 
            </a>

            <a href="../loja/loja.php" class="<?= $current === 'loja.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-store"></i> Loja 
            </a>

            <a href="../rank/rank.php" class="<?= $current === 'rank.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-trophy"></i> Ranking
            </a>
        </nav>

        <main class="conteudo perfil-conteudo">
            <div class="perfil-wrapper">
                <div class="titulo-pagina">
                    <div>
                        <span>Área do usuário</span>
                        <h1>Meu perfil</h1>
                    </div>
                    <a href="editar.php" class="btn-editar-topo">
                        <i class="fa-solid fa-pen"></i> Editar perfil
                    </a>
                </div>

                <!-- ===========================================
                     PERFIL DESTAQUE
                ============================================ -->
                <section class="perfil-destaque">
                    <div class="perfil-identidade">
                        <div
                            class="foto-container<?= $moldura_ativa_imagem ? ' tem-moldura' : '' ?>"
                            id="fotoContainerPerfil"
                            style="
                                --moldura-escala: <?= escapar((string)$moldura_ativa_ajuste['moldura_escala']) ?>;
                                --moldura-x: <?= escapar((string)$moldura_ativa_ajuste['moldura_x']) ?>px;
                                --moldura-y: <?= escapar((string)$moldura_ativa_ajuste['moldura_y']) ?>px;
                                --foto-escala: <?= escapar((string)$moldura_ativa_ajuste['foto_escala']) ?>;
                                --foto-x: <?= escapar((string)$moldura_ativa_ajuste['foto_x']) ?>px;
                                --foto-y: <?= escapar((string)$moldura_ativa_ajuste['foto_y']) ?>px;
                            "
                        >
                            <div class="moldura-container">
                                <div class="moldura-borda" id="molduraPerfil">
                                    <img
                                        class="foto-perfil-img"
                                        src="<?= escapar($caminho_foto) ?>"
                                        alt="Foto de perfil de <?= escapar($nome) ?>"
                                    >
                                </div>

                                <img
                                    id="molduraImagemPerfil"
                                    class="moldura-imagem-perfil<?= $moldura_ativa_imagem ? ' ativa' : '' ?>"
                                    src="<?= $moldura_ativa_imagem ? escapar($moldura_ativa_imagem) : '' ?>"
                                    alt="<?= $moldura_ativa_nome ? escapar($moldura_ativa_nome) : '' ?>"
                                    data-moldura-id="<?= $moldura_ativa_id ? escapar($moldura_ativa_id) : '' ?>"
                                    aria-hidden="true"
                                >

                                <span class="foto-status"></span>
                            </div>
                        </div>
                        <div class="perfil-texto">
                            <span class="etiqueta-perfil">Perfil do estudante</span>
                            <h2><?= escapar($nome) ?></h2>
                            <p class="email-perfil">
                                <i class="fa-regular fa-envelope"></i>
                                <?= escapar($email) ?>
                            </p>
                            <div class="perfil-resumo">
                                <span><i class="fa-solid fa-book-open"></i> <?= escapar($serie) ?></span>
                                <span><i class="fa-solid fa-school"></i> <?= escapar($escola) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="perfil-ilustracao">
                        <span class="circulo circulo-1"></span>
                        <span class="circulo circulo-2"></span>
                        <span class="circulo circulo-3"></span>
                        <div class="icone-estudante">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                    </div>
                </section>

                

                <!-- ===========================================
                     CARD PRINCIPAL - INFORMAÇÕES + INSÍGNIAS
                ============================================ -->
                <section class="dados-card">

                    <!-- CABEÇALHO INFORMAÇÕES -->
                    <div class="dados-cabecalho">
                        <div class="dados-icone">
                            <i class="fa-regular fa-address-card"></i>
                        </div>
                        <div>
                            <h3>Informações cadastradas</h3>
                            <p>Confira os dados salvos na sua conta.</p>
                        </div>
                    </div>

                    <!-- GRID DE DADOS -->
                    <div class="dados-grid">
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-regular fa-user"></i></div>
                            <div>
                                <span>Nome completo</span>
                                <strong><?= escapar($nome) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-regular fa-envelope"></i></div>
                            <div>
                                <span>E-mail</span>
                                <strong><?= escapar($email) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-regular fa-calendar"></i></div>
                            <div>
                                <span>Data de nascimento</span>
                                <strong><?= escapar($nascimento) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <span>Telefone</span>
                                <strong><?= escapar($telefone) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-solid fa-book-open-reader"></i></div>
                            <div>
                                <span>Série ou ano</span>
                                <strong><?= escapar($serie) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-solid fa-school"></i></div>
                            <div>
                                <span>Escola ou faculdade</span>
                                <strong><?= escapar($escola) ?></strong>
                            </div>
                        </div>
                        <div class="dado-item">
                            <div class="dado-item-icone"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <span>Cidade</span>
                                <strong><?= escapar($localidade) ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- ===========================================
                         DIVISOR + INSÍGNIAS (DENTRO DO MESMO CARD)
                    ============================================ -->
                    <div class="insignias-divider"></div>

                    <div class="insignias-section">
                        <div class="insignias-cabecalho">
                            <div class="insignias-icone">
                                <i class="fa-solid fa-award"></i>
                            </div>
                            <div>
                                <h3>Minhas Insígnias</h3>
                                <p>Conquistas desbloqueadas durante sua jornada</p>
                            </div>
                            <span class="contador-insignias">
                                <?= count($insignias_usuario) ?> / <?= count($insignias_disponiveis) ?>
                            </span>
                        </div>

                        <?php if (empty($insignias_usuario)): ?>
                            <div class="sem-insignias">
                                <i class="fa-solid fa-trophy"></i>
                                <p>Você ainda não desbloqueou nenhuma insígnia</p>
                                <small>Continue estudando e conquistando!</small>
                            </div>
                        <?php else: ?>
                            <div class="insignias-grid">
                                <?php 
                                $categorias = [];
                                foreach ($insignias_usuario as $insignia) {
                                    $cat = $insignia['categoria'] ?? 'conquista';
                                    if (!isset($categorias[$cat])) {
                                        $categorias[$cat] = [];
                                    }
                                    $categorias[$cat][] = $insignia;
                                }
                                
                                foreach ($categorias as $categoria => $itens):
                                ?>
                                    <div class="insignia-categoria-grupo">
                                        <div class="insignia-categoria-titulo">
                                            <?php 
                                            $iconeCat = $categorias_insignias[$categoria]['icone'] ?? 'fa-solid fa-trophy';
                                            $nomeCat = $categorias_insignias[$categoria]['nome'] ?? ucfirst($categoria);
                                            ?>
                                            <i class="<?= $iconeCat ?>"></i>
                                            <?= $nomeCat ?>
                                            <span class="categoria-contador"><?= count($itens) ?></span>
                                        </div>
                                        <div class="insignias-grid-sub">
                                            <?php foreach ($itens as $insignia): ?>
                                                <div class="insignia-item" data-id="<?= escapar($insignia['id']) ?>" title="<?= escapar($insignia['descricao']) ?>">
                                                    <div class="insignia-imagem">
                                                        <?php if (file_exists(__DIR__ . '/../img/insignias/' . $insignia['imagem'])): ?>
                                                            <img src="../img/insignias/<?= escapar($insignia['imagem']) ?>" alt="<?= escapar($insignia['nome']) ?>">
                                                        <?php else: ?>
                                                            <i class="<?= escapar($insignia['icone']) ?>" style="color: <?= escapar($insignia['cor']) ?>;"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="insignia-info">
                                                        <span class="insignia-nome"><?= escapar($insignia['nome']) ?></span>
                                                        <span class="insignia-categoria"><?= escapar(ucfirst($insignia['categoria'])) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </section>

                <!-- ===========================================
                     LOJA - ITENS COMPRADOS
                ============================================ -->
                <section class="loja-itens-card">
                    <div class="loja-itens-cabecalho">
                        <div class="loja-itens-icone">
                            <i class="fa-solid fa-store"></i>
                        </div>
                        <div>
                            <h3>Minhas Personalizações</h3>
                            <p>Itens comprados na Loja de Estrelas</p>
                        </div>
                        <a href="../loja/loja.php" class="btn-ir-loja">
                            <i class="fa-solid fa-cart-shopping"></i> Ir à Loja
                        </a>
                    </div>
                    <div class="loja-itens-grid" id="itensLojaPerfil">
                        <div class="carregando-itens">
                            <i class="fa-solid fa-spinner fa-spin"></i> Carregando itens...
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <!-- ===========================================
         MODAL LOGOUT
    ============================================ -->
    <div id="logout-modal" class="modal">
        <div class="modal-content">
            <h3>Ah... já vai?</h3>
            <h4>Tem certeza que deseja sair?</h4>
            <div class="modal-buttons">
                <button id="confirm-logout">Sim</button>
                <button id="cancel-logout">Cancelar</button>
            </div>
        </div>
    </div>

    <footer>&copy; 2025 FOAG. Todos os direitos reservados.</footer>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log('Perfil carregado ✅');


        const AJUSTE_MOLDURA_PADRAO = {
            moldura_escala: 1.28,
            moldura_x: 0,
            moldura_y: 0,
            foto_escala: 1,
            foto_x: 0,
            foto_y: 0
        };

        // Catálogo carregado pelo próprio perfil.php.
        // Serve de fonte segura caso loja_data.php não devolva
        // todos os dados da moldura (principalmente ajuste_perfil).
        const MOLDURAS_CATALOGO = <?= json_encode(
            $molduras_catalogo,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ) ?>;

        // ===========================================
        // REDIRECIONAMENTO - PERFIL
        // ===========================================
        const perfilBtn = document.getElementById("icon-perfil");
        if (perfilBtn) {
            perfilBtn.addEventListener("click", function() {
                window.location.href = "perfil.php";
            });
        }

        // ===========================================
        // LOGOUT
        // ===========================================
        const sairBtn = document.getElementById("icon-sair");
        const logoutModal = document.getElementById("logout-modal");
        const confirmarLogout = document.getElementById("confirm-logout");
        const cancelarLogout = document.getElementById("cancel-logout");

        if (sairBtn && logoutModal && confirmarLogout && cancelarLogout) {
            sairBtn.addEventListener("click", function() {
                logoutModal.style.display = "flex";
            });

            cancelarLogout.addEventListener("click", function() {
                logoutModal.style.display = "none";
            });

            confirmarLogout.addEventListener("click", function() {
                window.location.href = "../login/logout.php";
            });

            logoutModal.addEventListener("click", function(evento) {
                if (evento.target === logoutModal) {
                    logoutModal.style.display = "none";
                }
            });
        }

        // ===========================================
        // CARREGAR ITENS DA LOJA NO PERFIL
        // ===========================================

        function normalizarAjusteMoldura(item) {
            const ajuste = item && item.ajuste_perfil ? item.ajuste_perfil : {};
            const numero = (valor, padrao) => {
                const convertido = Number(valor);
                return Number.isFinite(convertido) ? convertido : padrao;
            };

            return {
                moldura_escala: numero(ajuste.moldura_escala, AJUSTE_MOLDURA_PADRAO.moldura_escala),
                moldura_x: numero(ajuste.moldura_x, AJUSTE_MOLDURA_PADRAO.moldura_x),
                moldura_y: numero(ajuste.moldura_y, AJUSTE_MOLDURA_PADRAO.moldura_y),
                foto_escala: numero(ajuste.foto_escala, AJUSTE_MOLDURA_PADRAO.foto_escala),
                foto_x: numero(ajuste.foto_x, AJUSTE_MOLDURA_PADRAO.foto_x),
                foto_y: numero(ajuste.foto_y, AJUSTE_MOLDURA_PADRAO.foto_y)
            };
        }

        function aplicarAjusteVisual(ajuste) {
            const fotoContainer = document.getElementById('fotoContainerPerfil');
            if (!fotoContainer) return;

            const a = { ...AJUSTE_MOLDURA_PADRAO, ...(ajuste || {}) };
            fotoContainer.style.setProperty('--moldura-escala', String(a.moldura_escala));
            fotoContainer.style.setProperty('--moldura-x', `${a.moldura_x}px`);
            fotoContainer.style.setProperty('--moldura-y', `${a.moldura_y}px`);
            fotoContainer.style.setProperty('--foto-escala', String(a.foto_escala));
            fotoContainer.style.setProperty('--foto-x', `${a.foto_x}px`);
            fotoContainer.style.setProperty('--foto-y', `${a.foto_y}px`);
        }

        function limparMolduraVisual() {
            const fotoContainer = document.getElementById('fotoContainerPerfil');
            const molduraImagem = document.getElementById('molduraImagemPerfil');

            if (fotoContainer) {
                fotoContainer.classList.remove('tem-moldura');
                aplicarAjusteVisual(AJUSTE_MOLDURA_PADRAO);
            }

            if (molduraImagem) {
                molduraImagem.removeAttribute('src');
                molduraImagem.alt = '';
                molduraImagem.dataset.molduraId = '';
                molduraImagem.classList.remove('ativa');
            }
        }

        function aplicarItemMoldura(itemMoldura, ajusteOverride = null) {
            const fotoContainer = document.getElementById('fotoContainerPerfil');
            const molduraImagem = document.getElementById('molduraImagemPerfil');

            if (!fotoContainer || !molduraImagem || !itemMoldura || !itemMoldura.imagem) {
                limparMolduraVisual();
                return;
            }

            const ajuste = ajusteOverride || normalizarAjusteMoldura(itemMoldura);
            fotoContainer.classList.add('tem-moldura');
            molduraImagem.src = itemMoldura.imagem;
            molduraImagem.alt = itemMoldura.nome || 'Moldura do perfil';
            molduraImagem.dataset.molduraId = String(itemMoldura.id || '');
            molduraImagem.classList.add('ativa');
            aplicarAjusteVisual(ajuste);
        }

        function aplicarMolduraAtiva(dados) {
            if (!dados) {
                return;
            }

            const molduraAtivaId =
                dados.itens_ativos &&
                dados.itens_ativos.moldura
                    ? String(dados.itens_ativos.moldura)
                    : '';

            /*
             * IMPORTANTE:
             * O PHP já renderiza a moldura correta antes do JS carregar.
             * Se a resposta de loja_data.php vier incompleta, NÃO limpamos
             * a moldura que já está funcionando.
             */
            if (!molduraAtivaId) {
                console.warn(
                    '⚠️ loja_data.php não informou uma moldura ativa. Mantendo a moldura renderizada pelo PHP.'
                );
                return;
            }

            const itensServidor =
                Array.isArray(dados.itens)
                    ? dados.itens
                    : [];

            const itemServidor =
                itensServidor.find(function(item) {
                    return (
                        String(item.id) === molduraAtivaId &&
                        item.categoria === 'molduras'
                    );
                }) || null;

            const itemCatalogo =
                Array.isArray(MOLDURAS_CATALOGO)
                    ? MOLDURAS_CATALOGO.find(function(item) {
                        return String(item.id) === molduraAtivaId;
                    }) || null
                    : null;

            /*
             * Preferimos os dados atuais da Loja, mas completamos com
             * ajuste_perfil do catálogo carregado pelo PHP.
             */
            let itemMoldura = null;

            if (itemServidor || itemCatalogo) {
                itemMoldura = {
                    ...(itemCatalogo || {}),
                    ...(itemServidor || {}),
                    ajuste_perfil:
                        (itemServidor && itemServidor.ajuste_perfil)
                            ? itemServidor.ajuste_perfil
                            : (
                                itemCatalogo &&
                                itemCatalogo.ajuste_perfil
                                    ? itemCatalogo.ajuste_perfil
                                    : {}
                            )
                };
            }

            if (!itemMoldura || !itemMoldura.imagem) {
                console.warn(
                    '⚠️ Não foi possível localizar os dados da moldura ativa:',
                    molduraAtivaId,
                    '- mantendo a moldura atual.'
                );
                return;
            }

            aplicarItemMoldura(itemMoldura);

            console.log(
                '🖼️ Moldura aplicada no perfil:',
                itemMoldura.nome
            );
        }

        function carregarItensLojaPerfil() {
            const container = document.getElementById('itensLojaPerfil');
            if (!container) {
                console.log('❌ Container itensLojaPerfil não encontrado');
                return;
            }

            console.log('🔄 Carregando itens da loja...');

            container.innerHTML = `
                <div class="carregando-itens">
                    <i class="fa-solid fa-spinner fa-spin"></i> Carregando itens...
                </div>
            `;

            fetch('../loja/loja_data.php')
                .then(function(resposta) {
                    if (!resposta.ok) {
                        throw new Error('Erro ao buscar dados');
                    }
                    return resposta.json();
                })
                .then(function(dados) {
                    console.log('📦 Dados da loja:', dados);

                    aplicarMolduraAtiva(dados);
                    
                    if (dados && dados.estrelas !== undefined) {
                        atualizarEstrelasPerfil(dados.estrelas);
                    }
                    
                    if (dados && dados.itens_comprados && dados.itens) {
                        const itensComprados = dados.itens_comprados || [];
                        const todosItens = dados.itens || [];
                        const itensAtivos = todosItens.filter(function(item) {
                            return itensComprados.includes(item.id);
                        });
                        
                        try {
                            sessionStorage.setItem('itens_loja', JSON.stringify(itensAtivos));
                            sessionStorage.setItem('itens_ativos_loja', JSON.stringify(dados.itens_ativos || {}));
                            sessionStorage.setItem('estrelas_total', dados.estrelas || 0);
                        } catch (e) {}
                        
                        renderizarItensPerfil(container, itensAtivos, dados.estrelas || 0, dados.itens_ativos || {});
                    } else {
                        container.innerHTML = `
                            <div class="sem-itens-loja">
                                <i class="fa-solid fa-store"></i>
                                <p>Você ainda não comprou nada na loja</p>
                                <small>⭐ ${dados?.estrelas || 0} estrelas disponíveis</small>
                            </div>
                        `;
                    }
                })
                .catch(function(erro) {
                    console.error('❌ Erro ao buscar itens da loja:', erro);
                    carregarDoSessionStorage(container);
                });
        }

        function carregarDoSessionStorage(container) {
            try {
                const itensSalvos = sessionStorage.getItem('itens_loja');
                const estrelasSalvas = sessionStorage.getItem('estrelas_total');
                
                if (itensSalvos) {
                    const itens = JSON.parse(itensSalvos);
                    const estrelas = parseInt(estrelasSalvas) || 0;
                    console.log('📦 Carregando do sessionStorage:', itens.length, 'itens');
                    renderizarItensPerfil(container, itens, estrelas);
                    return;
                }
            } catch (e) {}
            
            container.innerHTML = `
                <div class="sem-itens-loja">
                    <i class="fa-solid fa-store"></i>
                    <p>Erro ao carregar itens</p>
                    <small>Tente recarregar a página</small>
                </div>
            `;
        }

        function atualizarEstrelasPerfil(estrelas) {
            const semItens = document.querySelector('.sem-itens-loja small');
            if (semItens) {
                semItens.textContent = `⭐ ${estrelas} estrelas disponíveis`;
            }
        }

        function renderizarItensPerfil(container, itens, estrelas, itensAtivos = {}) {
            if (!itens || itens.length === 0) {
                container.innerHTML = `
                    <div class="sem-itens-loja">
                        <i class="fa-solid fa-store"></i>
                        <p>Você ainda não comprou nada na loja</p>
                        <small>⭐ ${estrelas || 0} estrelas disponíveis</small>
                    </div>
                `;
                return;
            }
            
            let html = '';
            const categoriasTraduzidas = {
                'temas': 'Tema',
                'insignias': 'Insígnia',
                'emojis': 'Emoji',
                'fundos': 'Fundo',
                'molduras': 'Moldura',
                'efeitos': 'Efeito'
            };
            
            itens.forEach(function(item) {
                const categoria = item.categoria || 'geral';
                const icone = item.icone || 'fa-solid fa-gift';
                const categoriaTraduzida = categoriasTraduzidas[categoria] || categoria;

                const tiposPorCategoria = {
                    'temas': 'tema',
                    'fundos': 'fundo',
                    'molduras': 'moldura',
                    'especiais': 'cursor'
                };

                const tipoEquipavel = tiposPorCategoria[categoria] || null;
                const estaAtivo =
                    tipoEquipavel &&
                    itensAtivos &&
                    String(itensAtivos[tipoEquipavel] || '') === String(item.id);

                const visualItem =
                    item.imagem
                        ? `<img src="${item.imagem}" alt="${item.nome || 'Item'}">`
                        : `<i class="${icone}"></i>`;

                html += `
                    <div class="item-loja-perfil${estaAtivo ? ' ativo' : ''}">
                        <div class="icone-item">${visualItem}</div>
                        <div class="nome-item">${item.nome || 'Item'}</div>
                        <div class="categoria-item">
                            ${estaAtivo ? 'ATIVO' : categoriaTraduzida}
                        </div>
                    </div>
                `;
            });
            
            html += `
                <div class="item-loja-perfil total-estrelas">
                    <div class="icone-item"><i class="fa-solid fa-star" style="color: #ffd700;"></i></div>
                    <div class="nome-item">${estrelas || 0}</div>
                    <div class="categoria-item">Estrelas</div>
                </div>
            `;
            
            container.innerHTML = html;
            console.log('✅ Itens renderizados:', itens.length, 'itens + estrelas');
        }

        

        // ===========================================
        // INICIALIZAR
        // ===========================================

        

        setTimeout(carregarItensLojaPerfil, 500);

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                console.log('👁️ Página visível novamente, recarregando...');
                setTimeout(carregarItensLojaPerfil, 300);
            }
        });

        try {
            const channel = new BroadcastChannel('foag_loja');
            channel.onmessage = function(evento) {
                if (evento.data && evento.data.type === 'LOJA_ATUALIZADA') {
                    console.log('📢 Loja atualizada! Recarregando perfil...');
                    setTimeout(carregarItensLojaPerfil, 150);
                }
            };
            console.log('📡 BroadcastChannel configurado');
        } catch (e) {
            console.log('⚠️ BroadcastChannel não suportado');
        }

        window.addEventListener('storage', function(evento) {
            if (evento.key === 'itens_loja' || evento.key === 'estrelas_total' || evento.key === 'loja_atualizada') {
                console.log('📦 Storage atualizado:', evento.key);
                setTimeout(carregarItensLojaPerfil, 300);
            }
        });

        console.log('✅ Perfil pronto!');
    });
    </script>
</body>
</html>