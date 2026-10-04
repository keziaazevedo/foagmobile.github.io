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

$dadosLojaUsuario = [];
$itensCompradosPerfilIds = [];
$itensAtivosPerfil = [];
$itensCompradosPerfil = [];

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
            : [];

    if (!is_array($dadosLojaUsuario)) {
        $dadosLojaUsuario = [];
    }

    $itensCompradosPerfilIds =
        isset($dadosLojaUsuario['itens_comprados']) &&
        is_array($dadosLojaUsuario['itens_comprados'])
            ? array_values(array_map(
                'strval',
                $dadosLojaUsuario['itens_comprados']
            ))
            : [];

    $itensAtivosPerfil =
        isset($dadosLojaUsuario['itens_ativos']) &&
        is_array($dadosLojaUsuario['itens_ativos'])
            ? $dadosLojaUsuario['itens_ativos']
            : [];

    foreach ($produtosLoja as $produtoLoja) {
        if (!is_array($produtoLoja)) {
            continue;
        }

        $idProduto = (string)($produtoLoja['id'] ?? '');

        if (
            $idProduto !== '' &&
            in_array(
                $idProduto,
                $itensCompradosPerfilIds,
                true
            )
        ) {
            $itensCompradosPerfil[] = $produtoLoja;
        }
    }

    $idMoldura =
        isset($itensAtivosPerfil['moldura'])
            ? trim((string)$itensAtivosPerfil['moldura'])
            : '';

    if (
        $idMoldura !== '' &&
        in_array(
            $idMoldura,
            $itensCompradosPerfilIds,
            true
        )
    ) {
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

/*
|--------------------------------------------------------------------------
| Organizar personalizações do Perfil
|--------------------------------------------------------------------------
| Emojis não aparecem aqui. Esta área mostra apenas:
| Molduras, Temas, Fundos e Especiais/Cursor.
*/

$categoriasColecaoPerfil = [
    'molduras' => [
        'nome' => 'Molduras',
        'icone' => 'fa-regular fa-image',
        'tipo_ativo' => 'moldura'
    ],
    'temas' => [
        'nome' => 'Temas',
        'icone' => 'fa-solid fa-palette',
        'tipo_ativo' => 'tema'
    ],
    'fundos' => [
        'nome' => 'Fundos',
        'icone' => 'fa-regular fa-images',
        'tipo_ativo' => 'fundo'
    ],
    'especiais' => [
        'nome' => 'Especiais',
        'icone' => 'fa-solid fa-wand-magic-sparkles',
        'tipo_ativo' => 'cursor'
    ]
];

$colecaoPerfil = [];
$itensEmUsoPerfil = [];
$totalColecaoPerfil = 0;

foreach ($categoriasColecaoPerfil as $categoriaKey => $configCategoria) {
    $colecaoPerfil[$categoriaKey] = [];
}

foreach ($itensCompradosPerfil as $itemPerfilColecao) {
    if (!is_array($itemPerfilColecao)) {
        continue;
    }

    $categoriaColecao =
        (string)($itemPerfilColecao['categoria'] ?? '');

    /*
     * Emojis e qualquer categoria fora da coleção
     * não entram nesta área do Perfil.
     */
    if (!isset($categoriasColecaoPerfil[$categoriaColecao])) {
        continue;
    }

    $colecaoPerfil[$categoriaColecao][] =
        $itemPerfilColecao;

    $totalColecaoPerfil++;

    $tipoAtivoColecao =
        $categoriasColecaoPerfil[
            $categoriaColecao
        ]['tipo_ativo'];

    $idAtivoColecao =
        isset($itensAtivosPerfil[$tipoAtivoColecao])
            ? (string)$itensAtivosPerfil[$tipoAtivoColecao]
            : '';

    if (
        $idAtivoColecao !== '' &&
        (string)($itemPerfilColecao['id'] ?? '')
            === $idAtivoColecao
    ) {
        $itensEmUsoPerfil[] =
            $itemPerfilColecao;
    }
}

$primeiraCategoriaColecaoPerfil = null;

foreach ($categoriasColecaoPerfil as $categoriaKey => $configCategoria) {
    if (!empty($colecaoPerfil[$categoriaKey])) {
        $primeiraCategoriaColecaoPerfil =
            $categoriaKey;
        break;
    }
}

/*
|--------------------------------------------------------------------------
| Carregar insígnias
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/insignias.php';

/*
|--------------------------------------------------------------------------
| Nomes oficiais das insígnias
|--------------------------------------------------------------------------
| O nome do arquivo da imagem é usado como referência.
| Assim os nomes exibidos no Perfil ficam iguais aos nomes
| impressos nas artes enviadas.
*/

$nomesOficiaisInsigniasPerfil = [
    'insignia_1ano.png' => 'Aniversário FOAG',
    'insignia_alem.png' => 'Além do Limite',

    'insignia_cacador1.png' => 'Caçador de Estrelas',
    'insignia_cacador2.png' => 'Caçador de Estrelas',
    'insignia_cacador3.png' => 'Caçador de Estrelas',

    'insignia_colecionador.png' => 'Colecionador Mestre',
    'insignia_colecionador1.png' => 'Colecionador',
    'insignia_colecionador2.png' => 'Colecionador',
    'insignia_colecionador3.png' => 'Colecionador',

    'insignia_cumprida1.png' => 'Missão Cumprida',
    'insignia_cumprida2.png' => 'Missão Cumprida',
    'insignia_cumprida3.png' => 'Missão Cumprida',

    'insignia_elefante1.png' => 'Memória de Elefante',
    'insignia_elefante2.png' => 'Memória de Elefante',
    'insignia_elefante3.png' => 'Memória de Elefante',

    'insignia_estudo1.png' => 'Estudioso',
    'insignia_estudo2.png' => 'Estudioso',
    'insignia_estudo3.png' => 'Estudioso',

    'insignia_evolucao1.png' => 'Evolução',
    'insignia_evolucao2.png' => 'Evolução',
    'insignia_evolucao3.png' => 'Evolução',

    'insignia_explorador.png' => 'Explorador do FOAG',

    'insignia_imparavel1.png' => 'Imparável',
    'insignia_imparavel2.png' => 'Imparável',
    'insignia_imparavel3.png' => 'Imparável',

    'insignia_lenda.png' => 'Lenda do FOAG',

    'insignia_maratona1.png' => 'Maratonista',
    'insignia_maratona2.png' => 'Maratonista',
    'insignia_maratona3.png' => 'Maratonista',

    'insignia_pomodoro1.png' => 'Mestre do Pomodoro',
    'insignia_pomodoro2.png' => 'Mestre do Pomodoro',
    'insignia_pomodoro3.png' => 'Mestre do Pomodoro',

    'insignia_pontual1.png' => 'Pontual',
    'insignia_pontual2.png' => 'Pontual',
    'insignia_pontual3.png' => 'Pontual',

    'insignia_presenca1.png' => 'Presença de Ferro',
    'insignia_presenca2.png' => 'Presença de Ferro',
    'insignia_presenca3.png' => 'Presença de Ferro',

    'insignia_primeiro.png' => 'Primeiro Passo',
    'insignia_segredos.png' => 'Caçador de Segredos',
    'insignia_supremo.png' => 'Conquistador Supremo',

    'insignia_veterano.png' => 'Veterano FOAG',
    'insignia_veterano1.png' => 'Veterano FOAG',
    'insignia_veterano2.png' => 'Veterano FOAG',
    'insignia_veterano3.png' => 'Veterano FOAG'
];

function corrigirNomeInsigniaPerfil(
    $insignia,
    $nomesOficiais
) {
    if (!is_array($insignia)) {
        return $insignia;
    }

    $imagemInsignia =
        trim(
            (string)(
                $insignia['imagem']
                ?? ''
            )
        );

    if ($imagemInsignia !== '') {
        $arquivoImagem =
            basename($imagemInsignia);

        if (isset($nomesOficiais[$arquivoImagem])) {
            $insignia['nome'] =
                $nomesOficiais[$arquivoImagem];
        }
    }

    return $insignia;
}

/*
 * Corrige o catálogo antes de montar o modal.
 */
foreach ($insignias_disponiveis as $chaveInsignia => $itemInsignia) {
    $insignias_disponiveis[$chaveInsignia] =
        corrigirNomeInsigniaPerfil(
            $itemInsignia,
            $nomesOficiaisInsigniasPerfil
        );
}

verificarDesbloquearInsignias($codigoUsuario);

$insignias_usuario =
    getInsigniasUsuario($codigoUsuario);

/*
 * Corrige também as já conquistadas, pois algumas podem
 * ter sido salvas anteriormente com nomes antigos.
 */
foreach ($insignias_usuario as $chaveInsignia => $itemInsignia) {
    $insignias_usuario[$chaveInsignia] =
        corrigirNomeInsigniaPerfil(
            $itemInsignia,
            $nomesOficiaisInsigniasPerfil
        );
}

/*
|--------------------------------------------------------------------------
| Organizar insígnias para o Perfil
|--------------------------------------------------------------------------
| A seção principal mostra uma prévia compacta.
| O modal mostra todas as insígnias disponíveis,
| diferenciando conquistadas e bloqueadas.
*/

$idsInsigniasUsuario = [];

foreach ($insignias_usuario as $chaveInsignia => $insigniaUsuarioItem) {
    if (!is_array($insigniaUsuarioItem)) {
        continue;
    }

    $idInsigniaUsuario =
        trim(
            (string)(
                $insigniaUsuarioItem['id']
                ?? (
                    is_string($chaveInsignia)
                        ? $chaveInsignia
                        : ''
                )
            )
        );

    if ($idInsigniaUsuario !== '') {
        $idsInsigniasUsuario[] =
            $idInsigniaUsuario;
    }
}

$insigniasCatalogoPerfil = [];

foreach ($insignias_disponiveis as $chaveInsignia => $insigniaCatalogoItem) {
    if (!is_array($insigniaCatalogoItem)) {
        continue;
    }

    $idInsigniaCatalogo =
        trim(
            (string)(
                $insigniaCatalogoItem['id']
                ?? (
                    is_string($chaveInsignia)
                        ? $chaveInsignia
                        : ''
                )
            )
        );

    if ($idInsigniaCatalogo === '') {
        continue;
    }

    $insigniaCatalogoItem['id'] =
        $idInsigniaCatalogo;

    $insigniaCatalogoItem['desbloqueada'] =
        in_array(
            $idInsigniaCatalogo,
            $idsInsigniasUsuario,
            true
        );

    $insigniasCatalogoPerfil[] =
        $insigniaCatalogoItem;
}

/*
 * Se por algum motivo o catálogo vier sem os detalhes
 * completos, garantimos que as conquistadas continuem
 * aparecendo no modal.
 */
$idsCatalogoPerfil =
    array_map(
        function ($item) {
            return (string)($item['id'] ?? '');
        },
        $insigniasCatalogoPerfil
    );

foreach ($insignias_usuario as $chaveInsignia => $insigniaUsuarioItem) {
    if (!is_array($insigniaUsuarioItem)) {
        continue;
    }

    $idInsigniaUsuario =
        trim(
            (string)(
                $insigniaUsuarioItem['id']
                ?? (
                    is_string($chaveInsignia)
                        ? $chaveInsignia
                        : ''
                )
            )
        );

    if (
        $idInsigniaUsuario !== '' &&
        !in_array(
            $idInsigniaUsuario,
            $idsCatalogoPerfil,
            true
        )
    ) {
        $insigniaUsuarioItem['id'] =
            $idInsigniaUsuario;

        $insigniaUsuarioItem['desbloqueada'] =
            true;

        $insigniasCatalogoPerfil[] =
            $insigniaUsuarioItem;
    }
}

/*
|--------------------------------------------------------------------------
| Separar insígnias normais e especiais
|--------------------------------------------------------------------------
| As especiais são reconhecidas pelo ID ou pelo nome.
| Isso evita depender da categoria cadastrada no config.
*/

function normalizarInsigniaEspecialPerfil($valor)
{
    $valor = trim((string)$valor);

    if (function_exists('mb_strtolower')) {
        $valor = mb_strtolower($valor, 'UTF-8');
    } else {
        $valor = strtolower($valor);
    }

    $mapa = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
        'é' => 'e', 'ê' => 'e',
        'í' => 'i',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u',
        'ç' => 'c'
    ];

    $valor = strtr($valor, $mapa);

    $valor = preg_replace(
        '/[^a-z0-9]+/',
        '_',
        $valor
    );

    return trim((string)$valor, '_');
}

$insigniasEspeciaisChavesPerfil = [
    'lenda_do_foag',
    'lenda_foag',

    'primeiro_passo',

    'cacador_de_segredos',
    'cacador_segredos',

    'aniversario_foag',
    'aniversario_do_foag',
    '1_ano_foag',
    '1_ano',

    'veterano_foag',
    'veterano_do_foag',
    'veterano',

    'alem_do_limite',

    'conquistador_supremo',

    'colecionador_mestre',

    'explorador_do_foag',
    'explorador_foag'
];

$arquivosInsigniasEspeciaisPerfil = [
    'insignia_lenda.png',
    'insignia_primeiro.png',
    'insignia_segredos.png',
    'insignia_1ano.png',
    'insignia_veterano.png',
    'insignia_alem.png',
    'insignia_supremo.png',
    'insignia_colecionador.png',
    'insignia_explorador.png'
];

$insigniasNormaisModal = [];
$insigniasEspeciaisModal = [];

foreach ($insigniasCatalogoPerfil as $insigniaModalItem) {
    $idNormalizado =
        normalizarInsigniaEspecialPerfil(
            $insigniaModalItem['id']
            ?? ''
        );

    $nomeNormalizado =
        normalizarInsigniaEspecialPerfil(
            $insigniaModalItem['nome']
            ?? ''
        );

    $arquivoImagemEspecial =
        basename(
            trim(
                (string)(
                    $insigniaModalItem['imagem']
                    ?? ''
                )
            )
        );

    /*
     * Se existe arquivo de imagem, ele é a fonte de verdade.
     * Isso impede que veterano1/2/3 e colecionador1/2/3
     * caiam nas especiais por causa do nome.
     */
    if ($arquivoImagemEspecial !== '') {
        $ehEspecial =
            in_array(
                $arquivoImagemEspecial,
                $arquivosInsigniasEspeciaisPerfil,
                true
            );
    } else {
        $ehEspecial =
            in_array(
                $idNormalizado,
                $insigniasEspeciaisChavesPerfil,
                true
            )
            ||
            in_array(
                $nomeNormalizado,
                $insigniasEspeciaisChavesPerfil,
                true
            );
    }

    $insigniaModalItem['especial'] =
        $ehEspecial;

    if ($ehEspecial) {
        $insigniasEspeciaisModal[] =
            $insigniaModalItem;
    } else {
        $insigniasNormaisModal[] =
            $insigniaModalItem;
    }
}

/*
|--------------------------------------------------------------------------
| Garantir as 9 Insígnias Especiais no modal
|--------------------------------------------------------------------------
| Mesmo que uma especial ainda não exista no catálogo/config,
| ela aparece bloqueada como silhueta secreta.
| Quando for conquistada e estiver nos dados do usuário,
| o estado desbloqueado é reconhecido.
*/

$catalogoEspecialFixoPerfil = [
    [
        'id' => 'lenda_do_foag',
        'nome' => 'Lenda do FOAG',
        'imagem' => 'insignia_lenda.png'
    ],
    [
        'id' => 'primeiro_passo',
        'nome' => 'Primeiro Passo',
        'imagem' => 'insignia_primeiro.png'
    ],
    [
        'id' => 'cacador_de_segredos',
        'nome' => 'Caçador de Segredos',
        'imagem' => 'insignia_segredos.png'
    ],
    [
        'id' => 'aniversario_foag',
        'nome' => 'Aniversário FOAG',
        'imagem' => 'insignia_1ano.png'
    ],
    [
        'id' => 'veterano_foag',
        'nome' => 'Veterano FOAG',
        'imagem' => 'insignia_veterano.png'
    ],
    [
        'id' => 'alem_do_limite',
        'nome' => 'Além do Limite',
        'imagem' => 'insignia_alem.png'
    ],
    [
        'id' => 'conquistador_supremo',
        'nome' => 'Conquistador Supremo',
        'imagem' => 'insignia_supremo.png'
    ],
    [
        'id' => 'colecionador_mestre',
        'nome' => 'Colecionador Mestre',
        'imagem' => 'insignia_colecionador.png'
    ],
    [
        'id' => 'explorador_do_foag',
        'nome' => 'Explorador do FOAG',
        'imagem' => 'insignia_explorador.png'
    ]
];

$especiaisJaNoModalPorImagem = [];

foreach ($insigniasEspeciaisModal as $indiceEspecial => $especialExistente) {
    $arquivoEspecialExistente =
        basename(
            trim(
                (string)(
                    $especialExistente['imagem']
                    ?? ''
                )
            )
        );

    if ($arquivoEspecialExistente !== '') {
        $especiaisJaNoModalPorImagem[
            $arquivoEspecialExistente
        ] = $indiceEspecial;
    }
}

/*
 * Mapa das conquistadas do usuário por ID, nome e imagem.
 */
$conquistadasEspeciaisPerfil = [];

foreach ($insignias_usuario as $chaveUsuarioInsignia => $insigniaUsuarioEspecial) {
    if (!is_array($insigniaUsuarioEspecial)) {
        continue;
    }

    $idUsuarioEspecial =
        normalizarInsigniaEspecialPerfil(
            $insigniaUsuarioEspecial['id']
            ?? (
                is_string($chaveUsuarioInsignia)
                    ? $chaveUsuarioInsignia
                    : ''
            )
        );

    $nomeUsuarioEspecial =
        normalizarInsigniaEspecialPerfil(
            $insigniaUsuarioEspecial['nome']
            ?? ''
        );

    $imagemUsuarioEspecial =
        basename(
            trim(
                (string)(
                    $insigniaUsuarioEspecial['imagem']
                    ?? ''
                )
            )
        );

    if ($idUsuarioEspecial !== '') {
        $conquistadasEspeciaisPerfil[
            'id:' . $idUsuarioEspecial
        ] = true;
    }

    if ($nomeUsuarioEspecial !== '') {
        $conquistadasEspeciaisPerfil[
            'nome:' . $nomeUsuarioEspecial
        ] = true;
    }

    if ($imagemUsuarioEspecial !== '') {
        $conquistadasEspeciaisPerfil[
            'imagem:' . $imagemUsuarioEspecial
        ] = true;
    }
}

foreach ($catalogoEspecialFixoPerfil as $especialFixa) {
    $arquivoEspecialFixa =
        (string)$especialFixa['imagem'];

    $idEspecialFixa =
        normalizarInsigniaEspecialPerfil(
            $especialFixa['id']
        );

    $nomeEspecialFixa =
        normalizarInsigniaEspecialPerfil(
            $especialFixa['nome']
        );

    $desbloqueadaFixa =
        isset(
            $conquistadasEspeciaisPerfil[
                'imagem:' . $arquivoEspecialFixa
            ]
        )
        ||
        isset(
            $conquistadasEspeciaisPerfil[
                'id:' . $idEspecialFixa
            ]
        )
        ||
        isset(
            $conquistadasEspeciaisPerfil[
                'nome:' . $nomeEspecialFixa
            ]
        );

    if (
        isset(
            $especiaisJaNoModalPorImagem[
                $arquivoEspecialFixa
            ]
        )
    ) {
        /*
         * Mantém os dados completos do config, mas força
         * nome/imagem oficiais e atualiza desbloqueio.
         */
        $indiceEspecial =
            $especiaisJaNoModalPorImagem[
                $arquivoEspecialFixa
            ];

        $insigniasEspeciaisModal[
            $indiceEspecial
        ]['nome'] =
            $especialFixa['nome'];

        $insigniasEspeciaisModal[
            $indiceEspecial
        ]['imagem'] =
            $arquivoEspecialFixa;

        if ($desbloqueadaFixa) {
            $insigniasEspeciaisModal[
                $indiceEspecial
            ]['desbloqueada'] = true;
        }

        continue;
    }

    /*
     * Não está no config: cria uma entrada secreta/bloqueada.
     */
    $insigniasEspeciaisModal[] = [
        'id' => $especialFixa['id'],
        'nome' => $especialFixa['nome'],
        'imagem' => $arquivoEspecialFixa,
        'descricao' => '',
        'especial' => true,
        'desbloqueada' => $desbloqueadaFixa
    ];
}

/*
 * Ordena sempre na mesma sequência das 9 enviadas.
 */
$ordemArquivosEspeciaisPerfil =
    array_column(
        $catalogoEspecialFixoPerfil,
        'imagem'
    );

usort(
    $insigniasEspeciaisModal,
    function ($a, $b) use ($ordemArquivosEspeciaisPerfil) {
        $arquivoA =
            basename(
                (string)($a['imagem'] ?? '')
            );

        $arquivoB =
            basename(
                (string)($b['imagem'] ?? '')
            );

        $posA =
            array_search(
                $arquivoA,
                $ordemArquivosEspeciaisPerfil,
                true
            );

        $posB =
            array_search(
                $arquivoB,
                $ordemArquivosEspeciaisPerfil,
                true
            );

        $posA =
            $posA === false
                ? PHP_INT_MAX
                : $posA;

        $posB =
            $posB === false
                ? PHP_INT_MAX
                : $posB;

        return $posA <=> $posB;
    }
);


/*
|--------------------------------------------------------------------------
| Organizar insígnias normais por família e nível
|--------------------------------------------------------------------------
| Cada família aparece em uma linha própria com Nível 1, 2 e 3.
*/

$ordemFamiliasInsigniasPerfil = [
    'estudo' => 'Estudioso',
    'imparavel' => 'Imparável',
    'pomodoro' => 'Mestre do Pomodoro',
    'elefante' => 'Memória de Elefante',
    'colecionador' => 'Colecionador',
    'presenca' => 'Presença de Ferro',
    'cacador' => 'Caçador de Estrelas',
    'cumprida' => 'Missão Cumprida',
    'pontual' => 'Pontual',
    'evolucao' => 'Evolução',
    'maratona' => 'Maratonista',
    'veterano' => 'Veterano FOAG'
];

$textosBasicosInsigniasPerfil = [
    'estudo' => 'Estude e avance no FOAG.',
    'imparavel' => 'Mantenha uma sequência de estudos.',
    'pomodoro' => 'Complete sessões de Pomodoro.',
    'elefante' => 'Pratique com seus flashcards.',
    'colecionador' => 'Colecione recursos e conquistas.',
    'presenca' => 'Mantenha uma boa frequência.',
    'cacador' => 'Conquiste estrelas no FOAG.',
    'cumprida' => 'Conclua suas tarefas da Agenda.',
    'pontual' => 'Cumpra suas tarefas no prazo.',
    'evolucao' => 'Evolua no seu desempenho escolar.',
    'maratona' => 'Acumule progresso nos estudos.',
    'veterano' => 'Continue sua jornada no FOAG.'
];

$familiasInsigniasNormaisModal = [];

foreach ($ordemFamiliasInsigniasPerfil as $chaveFamilia => $nomeFamilia) {
    $familiasInsigniasNormaisModal[$chaveFamilia] = [
        'nome' => $nomeFamilia,
        'niveis' => []
    ];
}

foreach ($insigniasNormaisModal as $insigniaNormalAgrupar) {
    $arquivoAgrupar =
        basename(
            trim(
                (string)(
                    $insigniaNormalAgrupar['imagem']
                    ?? ''
                )
            )
        );

    $familiaEncontrada = null;
    $nivelEncontrado = null;

    if (
        preg_match(
            '/^insignia_([a-z]+)([123])\.png$/i',
            $arquivoAgrupar,
            $partesNivel
        )
    ) {
        $familiaPossivel =
            strtolower(
                (string)$partesNivel[1]
            );

        $nivelPossivel =
            (int)$partesNivel[2];

        if (
            isset(
                $familiasInsigniasNormaisModal[
                    $familiaPossivel
                ]
            )
        ) {
            $familiaEncontrada =
                $familiaPossivel;

            $nivelEncontrado =
                $nivelPossivel;
        }
    }

    /*
     * Fallback para cadastro sem imagem padronizada.
     */
    if ($familiaEncontrada === null) {
        $nomeAgrupar =
            normalizarInsigniaEspecialPerfil(
                $insigniaNormalAgrupar['nome']
                ?? ''
            );

        foreach ($ordemFamiliasInsigniasPerfil as $chaveFamilia => $nomeFamilia) {
            $nomeFamiliaNormalizado =
                normalizarInsigniaEspecialPerfil(
                    $nomeFamilia
                );

            if (
                $nomeAgrupar === $nomeFamiliaNormalizado
                ||
                strpos(
                    $nomeAgrupar,
                    $nomeFamiliaNormalizado
                ) !== false
            ) {
                $familiaEncontrada =
                    $chaveFamilia;
                break;
            }
        }

        foreach ([1, 2, 3] as $nivelTeste) {
            $idTeste =
                normalizarInsigniaEspecialPerfil(
                    $insigniaNormalAgrupar['id']
                    ?? ''
                );

            if (
                preg_match(
                    '/(?:nivel_?)?(' . $nivelTeste . ')$/',
                    $idTeste
                )
            ) {
                $nivelEncontrado =
                    $nivelTeste;
                break;
            }
        }
    }

    if ($familiaEncontrada === null) {
        /*
         * Caso exista alguma insígnia normal fora das famílias
         * conhecidas, cria um grupo próprio em vez de sumir.
         */
        $familiaEncontrada =
            'outros_' .
            normalizarInsigniaEspecialPerfil(
                $insigniaNormalAgrupar['nome']
                ?? $insigniaNormalAgrupar['id']
                ?? uniqid('insignia_', true)
            );

        if (
            !isset(
                $familiasInsigniasNormaisModal[
                    $familiaEncontrada
                ]
            )
        ) {
            $familiasInsigniasNormaisModal[
                $familiaEncontrada
            ] = [
                'nome' =>
                    (string)(
                        $insigniaNormalAgrupar['nome']
                        ?? 'Outras Insígnias'
                    ),
                'niveis' => []
            ];
        }
    }

    if ($nivelEncontrado === null) {
        $nivelEncontrado =
            count(
                $familiasInsigniasNormaisModal[
                    $familiaEncontrada
                ]['niveis']
            ) + 1;
    }

    $insigniaNormalAgrupar['nivel'] =
        $nivelEncontrado;

    $familiasInsigniasNormaisModal[
        $familiaEncontrada
    ]['niveis'][$nivelEncontrado] =
        $insigniaNormalAgrupar;
}

/*
 * Remove famílias sem nenhum item e ordena níveis 1 → 3.
 */
foreach ($familiasInsigniasNormaisModal as $chaveFamilia => &$familiaNormal) {
    if (empty($familiaNormal['niveis'])) {
        unset(
            $familiasInsigniasNormaisModal[
                $chaveFamilia
            ]
        );
        continue;
    }

    ksort(
        $familiaNormal['niveis'],
        SORT_NUMERIC
    );
}
unset($familiaNormal);

/*
|--------------------------------------------------------------------------
| Texto do requisito exato
|--------------------------------------------------------------------------
| Prioriza campos explícitos do config/insignias.php.
| Se o sistema só tiver "descricao", usa esse texto.
*/

function obterRequisitoInsigniaPerfil($insignia)
{
    if (!is_array($insignia)) {
        return 'Requisito não informado.';
    }

    $camposPreferidos = [
        'requisito',
        'requisitos',
        'como_conseguir',
        'como_desbloquear',
        'criterio',
        'critério',
        'condicao',
        'condição',
        'objetivo',
        'meta_texto',
        'descricao'
    ];

    foreach ($camposPreferidos as $campo) {
        if (!array_key_exists($campo, $insignia)) {
            continue;
        }

        $valor =
            $insignia[$campo];

        if (is_string($valor)) {
            $valor = trim($valor);

            if ($valor !== '') {
                return $valor;
            }
        }

        if (is_numeric($valor)) {
            return 'Meta: ' . $valor;
        }

        if (is_array($valor)) {
            $partes = [];

            foreach ($valor as $chave => $item) {
                if (
                    is_string($item)
                    ||
                    is_numeric($item)
                ) {
                    $textoItem =
                        trim((string)$item);

                    if ($textoItem === '') {
                        continue;
                    }

                    if (
                        !is_int($chave)
                        &&
                        !ctype_digit(
                            (string)$chave
                        )
                    ) {
                        $partes[] =
                            ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    (string)$chave
                                )
                            )
                            . ': '
                            . $textoItem;
                    } else {
                        $partes[] =
                            $textoItem;
                    }
                }
            }

            if (!empty($partes)) {
                return implode(
                    ' • ',
                    $partes
                );
            }
        }
    }

    /*
     * Alguns configs guardam o valor numérico separado.
     * Não inventamos a ação; mostramos somente o que
     * estiver realmente definido.
     */
    $partesMeta = [];

    foreach (
        [
            'meta',
            'quantidade',
            'valor',
            'total',
            'dias',
            'minutos',
            'sessoes',
            'sessões'
        ]
        as $campoMeta
    ) {
        if (
            isset($insignia[$campoMeta])
            &&
            (
                is_numeric($insignia[$campoMeta])
                ||
                is_string($insignia[$campoMeta])
            )
        ) {
            $valorMeta =
                trim(
                    (string)$insignia[$campoMeta]
                );

            if ($valorMeta !== '') {
                $partesMeta[] =
                    ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $campoMeta
                        )
                    )
                    . ': '
                    . $valorMeta;
            }
        }
    }

    if (!empty($partesMeta)) {
        return implode(
            ' • ',
            $partesMeta
        );
    }

    return 'Requisito não informado no cadastro desta insígnia.';
}

$totalInsigniasNormaisPerfil =
    count($insigniasNormaisModal);

$totalInsigniasEspeciaisPerfil =
    count($insigniasEspeciaisModal);

$conquistadasNormaisPerfil = 0;
$conquistadasEspeciaisPerfil = 0;

foreach ($insigniasNormaisModal as $itemNormalContagem) {
    if (!empty($itemNormalContagem['desbloqueada'])) {
        $conquistadasNormaisPerfil++;
    }
}

foreach ($insigniasEspeciaisModal as $itemEspecialContagem) {
    if (!empty($itemEspecialContagem['desbloqueada'])) {
        $conquistadasEspeciaisPerfil++;
    }
}

$previewInsigniasPerfil =
    array_slice(
        array_values($insignias_usuario),
        0,
        4
    );

$totalInsigniasDisponiveisPerfil =
    count($insigniasCatalogoPerfil);

if ($totalInsigniasDisponiveisPerfil === 0) {
    $totalInsigniasDisponiveisPerfil =
        count($insignias_disponiveis);
}

$totalInsigniasUsuarioPerfil =
    count($insignias_usuario);

$progressoInsigniasPerfil =
    $totalInsigniasDisponiveisPerfil > 0
        ? min(
            100,
            round(
                (
                    $totalInsigniasUsuarioPerfil
                    /
                    $totalInsigniasDisponiveisPerfil
                )
                * 100
            )
        )
        : 0;
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
    <link rel="stylesheet" href="perfilfil.css?v=25">
    
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
                         DIVISOR + INSÍGNIAS
                    ============================================ -->
                    <div class="insignias-divider"></div>

                    <div class="insignias-section">
                        <div class="insignias-cabecalho">
                            <div class="insignias-cabecalho-principal">
                                <div class="insignias-icone">
                                    <i class="fa-solid fa-award"></i>
                                </div>

                                <div>
                                    <h3>Minhas Insígnias</h3>
                                    <p>Conquistas desbloqueadas durante sua jornada</p>
                                </div>
                            </div>

                            <div class="insignias-cabecalho-acoes">
                                <span class="contador-insignias">
                                    <?= $totalInsigniasUsuarioPerfil ?>
                                    /
                                    <?= $totalInsigniasDisponiveisPerfil ?>
                                </span>

                                <button
                                    type="button"
                                    class="btn-ver-todas-insignias"
                                    id="btnVerTodasInsignias"
                                >
                                    <span>Ver todas</span>
                                    <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                                </button>
                            </div>
                        </div>

                        <div class="insignias-progresso-resumo">
                            <div class="insignias-progresso-texto">
                                <span>Progresso da coleção</span>
                                <strong><?= $progressoInsigniasPerfil ?>%</strong>
                            </div>

                            <div
                                class="insignias-progresso-barra"
                                aria-label="Progresso das insígnias"
                            >
                                <span
                                    style="width: <?= $progressoInsigniasPerfil ?>%;"
                                ></span>
                            </div>
                        </div>

                        <?php if (empty($insignias_usuario)): ?>
                            <div class="sem-insignias sem-insignias-preview">
                                <i class="fa-solid fa-trophy"></i>
                                <div>
                                    <p>Você ainda não desbloqueou nenhuma insígnia</p>
                                    <small>
                                        Abra a coleção para descobrir o que você pode conquistar.
                                    </small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="insignias-preview">
                                <?php foreach ($previewInsigniasPerfil as $insignia): ?>
                                    <?php
                                    if (!is_array($insignia)) {
                                        continue;
                                    }

                                    $imagemInsigniaPreview =
                                        trim(
                                            (string)(
                                                $insignia['imagem']
                                                ?? ''
                                            )
                                        );

                                    $iconeInsigniaPreview =
                                        (string)(
                                            $insignia['icone']
                                            ?? 'fa-solid fa-trophy'
                                        );

                                    $corInsigniaPreview =
                                        (string)(
                                            $insignia['cor']
                                            ?? '#38a5ff'
                                        );
                                    ?>

                                    <div
                                        class="insignia-preview-item"
                                        title="<?= escapar(
                                            (string)(
                                                $insignia['descricao']
                                                ?? $insignia['nome']
                                                ?? 'Insígnia'
                                            )
                                        ) ?>"
                                    >
                                        <div class="insignia-preview-imagem">
                                            <?php if (
                                                $imagemInsigniaPreview !== '' &&
                                                file_exists(
                                                    __DIR__
                                                    . '/../img/insignias/'
                                                    . $imagemInsigniaPreview
                                                )
                                            ): ?>
                                                <img
                                                    src="../img/insignias/<?= escapar($imagemInsigniaPreview) ?>"
                                                    alt="<?= escapar(
                                                        (string)(
                                                            $insignia['nome']
                                                            ?? 'Insígnia'
                                                        )
                                                    ) ?>"
                                                >
                                            <?php else: ?>
                                                <i
                                                    class="<?= escapar($iconeInsigniaPreview) ?>"
                                                    style="color: <?= escapar($corInsigniaPreview) ?>;"
                                                ></i>
                                            <?php endif; ?>
                                        </div>

                                        <span>
                                            <?= escapar(
                                                (string)(
                                                    $insignia['nome']
                                                    ?? 'Insígnia'
                                                )
                                            ) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>

                                <?php if ($totalInsigniasUsuarioPerfil > 4): ?>
                                    <button
                                        type="button"
                                        class="insignia-preview-mais"
                                        id="btnVerMaisInsignias"
                                    >
                                        <strong>
                                            +<?= $totalInsigniasUsuarioPerfil - 4 ?>
                                        </strong>
                                        <span>ver mais</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </section>

                <!-- ===========================================
                     MINHAS PERSONALIZAÇÕES
                ============================================ -->
                <section class="loja-itens-card personalizacoes-card">
                    <div class="loja-itens-cabecalho">
                        <div class="loja-itens-icone">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>

                        <div>
                            <h3>Minhas Personalizações</h3>
                            <p>
                                <?= $totalColecaoPerfil ?>
                                item<?= $totalColecaoPerfil === 1 ? '' : 's' ?>
                                desbloqueado<?= $totalColecaoPerfil === 1 ? '' : 's' ?>
                            </p>
                        </div>

                        <a href="../loja/loja.php" class="btn-ir-loja">
                            <i class="fa-solid fa-cart-shopping"></i>
                            Ir à Loja
                        </a>
                    </div>

                    <div
                        class="mensagem-equipar-perfil"
                        id="mensagemEquiparPerfil"
                        role="status"
                        aria-live="polite"
                    ></div>

                    <!-- ======================================
                         EM USO
                    ======================================= -->
                    <div class="personalizacoes-bloco em-uso-bloco">
                        <div class="personalizacoes-bloco-titulo">
                            <div>
                                <span class="titulo-selo">
                                    <i class="fa-solid fa-check"></i>
                                </span>

                                <div>
                                    <h4>Em uso</h4>
                                    <p>Suas personalizações equipadas agora</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="personalizacoes-em-uso"
                            id="itensAtivosPerfil"
                        >
                            <?php if (empty($itensEmUsoPerfil)): ?>

                                <div class="colecao-vazia colecao-vazia-compacta">
                                    <i class="fa-regular fa-circle-check"></i>
                                    <span>Nenhuma personalização equipada.</span>
                                </div>

                            <?php else: ?>

                                <?php foreach ($itensEmUsoPerfil as $itemEmUso): ?>
                                    <?php
                                    $categoriaEmUso =
                                        (string)($itemEmUso['categoria'] ?? '');

                                    $nomeEmUso =
                                        (string)($itemEmUso['nome'] ?? 'Item');

                                    $imagemEmUso =
                                        trim((string)($itemEmUso['imagem'] ?? ''));

                                    $iconeEmUso =
                                        (string)($itemEmUso['icone'] ?? 'fa-solid fa-gift');

                                    $configEmUso =
                                        $categoriasColecaoPerfil[$categoriaEmUso]
                                        ?? [
                                            'nome' => ucfirst($categoriaEmUso),
                                            'icone' => 'fa-solid fa-gift'
                                        ];

                                    $temImagemEmUso =
                                        $imagemEmUso !== '' &&
                                        (bool)preg_match(
                                            '/\.(png|jpe?g|webp|gif|svg)$/i',
                                            $imagemEmUso
                                        );
                                    ?>

                                    <div class="personalizacao-ativa-card">
                                        <div class="personalizacao-ativa-visual">
                                            <?php if ($temImagemEmUso): ?>
                                                <img
                                                    src="<?= escapar($imagemEmUso) ?>"
                                                    alt="<?= escapar($nomeEmUso) ?>"
                                                >
                                            <?php else: ?>
                                                <i class="<?= escapar($iconeEmUso) ?>"></i>
                                            <?php endif; ?>
                                        </div>

                                        <div class="personalizacao-ativa-info">
                                            <span class="personalizacao-ativa-tipo">
                                                <?= escapar($configEmUso['nome']) ?>
                                            </span>

                                            <strong>
                                                <?= escapar($nomeEmUso) ?>
                                            </strong>
                                        </div>

                                        <span class="personalizacao-ativa-check">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                    </div>
                                <?php endforeach; ?>

                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ======================================
                         MINHA COLEÇÃO
                    ======================================= -->
                    <div class="personalizacoes-bloco colecao-bloco">
                        <div class="personalizacoes-bloco-titulo colecao-titulo">
                            <div>
                                <span class="titulo-selo titulo-selo-colecao">
                                    <i class="fa-solid fa-box-open"></i>
                                </span>

                                <div>
                                    <h4>Minha coleção</h4>
                                    <p>Veja os itens que você já desbloqueou</p>
                                </div>
                            </div>
                        </div>

                        <?php if ($totalColecaoPerfil === 0): ?>

                            <div class="colecao-vazia">
                                <i class="fa-solid fa-box-open"></i>
                                <h5>Sua coleção ainda está vazia</h5>
                                <p>
                                    Os itens comprados na Loja de Estrelas
                                    aparecerão aqui.
                                </p>

                                <a href="../loja/loja.php">
                                    Explorar a Loja
                                </a>
                            </div>

                        <?php else: ?>

                            <div class="colecao-abas" id="colecaoAbasPerfil">
                                <?php foreach ($categoriasColecaoPerfil as $categoriaKey => $configCategoria): ?>
                                    <?php
                                    $quantidadeCategoria =
                                        count($colecaoPerfil[$categoriaKey]);

                                    $abaAtiva =
                                        $categoriaKey ===
                                        $primeiraCategoriaColecaoPerfil;

                                    $abaDesabilitada =
                                        $quantidadeCategoria === 0;
                                    ?>

                                    <button
                                        type="button"
                                        class="colecao-aba<?= $abaAtiva ? ' ativa' : '' ?>"
                                        data-categoria="<?= escapar($categoriaKey) ?>"
                                        <?= $abaDesabilitada ? 'disabled' : '' ?>
                                    >
                                        <i class="<?= escapar($configCategoria['icone']) ?>"></i>

                                        <span>
                                            <?= escapar($configCategoria['nome']) ?>
                                        </span>

                                        <b><?= $quantidadeCategoria ?></b>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div
                                class="colecao-paineis"
                                id="itensLojaPerfil"
                            >
                                <?php foreach ($categoriasColecaoPerfil as $categoriaKey => $configCategoria): ?>
                                    <?php
                                    $itensCategoria =
                                        $colecaoPerfil[$categoriaKey];

                                    $painelAtivo =
                                        $categoriaKey ===
                                        $primeiraCategoriaColecaoPerfil;

                                    $tipoAtivoCategoria =
                                        $configCategoria['tipo_ativo'];

                                    $idAtivoCategoria =
                                        isset($itensAtivosPerfil[$tipoAtivoCategoria])
                                            ? (string)$itensAtivosPerfil[$tipoAtivoCategoria]
                                            : '';
                                    ?>

                                    <div
                                        class="colecao-painel<?= $painelAtivo ? ' ativo' : '' ?>"
                                        data-categoria="<?= escapar($categoriaKey) ?>"
                                    >
                                        <?php if (empty($itensCategoria)): ?>

                                            <div class="colecao-vazia colecao-vazia-painel">
                                                <i class="<?= escapar($configCategoria['icone']) ?>"></i>
                                                <span>
                                                    Você ainda não possui itens desta categoria.
                                                </span>
                                            </div>

                                        <?php else: ?>

                                            <div class="loja-itens-grid colecao-grid">
                                                <?php foreach ($itensCategoria as $indiceItem => $itemPerfil): ?>
                                                    <?php
                                                    $idItemPerfil =
                                                        (string)($itemPerfil['id'] ?? '');

                                                    $nomeItemPerfil =
                                                        (string)($itemPerfil['nome'] ?? 'Item');

                                                    $imagemItemPerfil =
                                                        trim((string)($itemPerfil['imagem'] ?? ''));

                                                    $iconeItemPerfil =
                                                        (string)($itemPerfil['icone'] ?? 'fa-solid fa-gift');

                                                    $estaAtivoPerfil =
                                                        $idAtivoCategoria !== '' &&
                                                        $idAtivoCategoria ===
                                                        $idItemPerfil;

                                                    $temImagemPerfil =
                                                        $imagemItemPerfil !== '' &&
                                                        (bool)preg_match(
                                                            '/\.(png|jpe?g|webp|gif|svg)$/i',
                                                            $imagemItemPerfil
                                                        );

                                                    $itemExtra =
                                                        $indiceItem >= 8;
                                                    ?>

                                                    <div
                                                        class="
                                                            item-loja-perfil
                                                            <?= $estaAtivoPerfil ? ' ativo' : '' ?>
                                                            <?= $itemExtra ? ' colecao-item-extra' : '' ?>
                                                        "
                                                    >
                                                        <div class="icone-item">
                                                            <?php if ($temImagemPerfil): ?>
                                                                <img
                                                                    src="<?= escapar($imagemItemPerfil) ?>"
                                                                    alt="<?= escapar($nomeItemPerfil) ?>"
                                                                    loading="lazy"
                                                                >
                                                            <?php else: ?>
                                                                <i class="<?= escapar($iconeItemPerfil) ?>"></i>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="nome-item">
                                                            <?= escapar($nomeItemPerfil) ?>
                                                        </div>

                                                        <div class="categoria-item">
                                                            <?= $estaAtivoPerfil
                                                                ? 'ATIVO'
                                                                : 'DESBLOQUEADO'
                                                            ?>
                                                        </div>

                                                        <?php if ($estaAtivoPerfil): ?>
                                                            <span class="btn-equipar-item em-uso" aria-label="Item em uso">
                                                                <i class="fa-solid fa-check"></i>
                                                                Em uso
                                                            </span>
                                                        <?php else: ?>
                                                            <button
                                                                type="button"
                                                                class="btn-equipar-item"
                                                                data-item-id="<?= escapar($idItemPerfil) ?>"
                                                                data-item-nome="<?= escapar($nomeItemPerfil) ?>"
                                                                data-categoria="<?= escapar($categoriaKey) ?>"
                                                            >
                                                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                                                Usar
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <?php if (count($itensCategoria) > 8): ?>
                                                <button
                                                    type="button"
                                                    class="btn-ver-colecao"
                                                    data-expandido="false"
                                                >
                                                    <span>
                                                        Ver todos (<?= count($itensCategoria) ?>)
                                                    </span>

                                                    <i class="fa-solid fa-chevron-down"></i>
                                                </button>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php endif; ?>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <!-- ===========================================
         MODAL — TODAS AS INSÍGNIAS
    ============================================ -->
    <div
        class="insignias-modal-overlay"
        id="modalInsignias"
        aria-hidden="true"
    >
        <div
            class="insignias-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tituloModalInsignias"
        >
            <div class="insignias-modal-topo">
                <div class="insignias-modal-titulo-area">
                    <div class="insignias-modal-icone">
                        <i class="fa-solid fa-trophy"></i>
                    </div>

                    <div>
                        <span>Minha coleção</span>
                        <h2 id="tituloModalInsignias">
                            Todas as Insígnias
                        </h2>
                        <p>
                            Acompanhe suas conquistas e descubra
                            quais ainda faltam desbloquear.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="insignias-modal-fechar"
                    id="fecharModalInsignias"
                    aria-label="Fechar"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="insignias-modal-resumo">
                <div class="insignias-modal-stat">
                    <span class="stat-icone stat-conquistadas">
                        <i class="fa-solid fa-check"></i>
                    </span>
                    <div>
                        <small>Conquistadas</small>
                        <strong><?= $totalInsigniasUsuarioPerfil ?></strong>
                    </div>
                </div>

                <div class="insignias-modal-stat">
                    <span class="stat-icone stat-restantes">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <div>
                        <small>Restantes</small>
                        <strong>
                            <?= max(
                                0,
                                $totalInsigniasDisponiveisPerfil
                                - $totalInsigniasUsuarioPerfil
                            ) ?>
                        </strong>
                    </div>
                </div>

                <div class="insignias-modal-progresso">
                    <div>
                        <span>Progresso total</span>
                        <strong><?= $progressoInsigniasPerfil ?>%</strong>
                    </div>

                    <div class="insignias-modal-progresso-barra">
                        <span
                            style="width: <?= $progressoInsigniasPerfil ?>%;"
                        ></span>
                    </div>
                </div>
            </div>

            <div class="insignias-modal-conteudo">

                <?php
                function renderizarCardInsigniaModalPerfil(
                    $insigniaModalItem,
                    $especial = false
                ) {
                    $desbloqueadaModal =
                        !empty(
                            $insigniaModalItem['desbloqueada']
                        );

                    $nomeModal =
                        (string)(
                            $insigniaModalItem['nome']
                            ?? 'Insígnia'
                        );

                    $descricaoModal =
                        (string)(
                            $insigniaModalItem['descricao']
                            ?? (
                                $desbloqueadaModal
                                    ? 'Insígnia conquistada.'
                                    : 'Continue avançando para desbloquear esta insígnia.'
                            )
                        );

                    $nivelModal =
                        isset($insigniaModalItem['nivel'])
                            ? (int)$insigniaModalItem['nivel']
                            : null;

                    $textoBasicoModal =
                        trim(
                            (string)(
                                $insigniaModalItem['texto_basico']
                                ?? ''
                            )
                        );

                    $imagemModal =
                        trim(
                            (string)(
                                $insigniaModalItem['imagem']
                                ?? ''
                            )
                        );

                    $iconeModal =
                        (string)(
                            $insigniaModalItem['icone']
                            ?? 'fa-solid fa-trophy'
                        );

                    $corModal =
                        (string)(
                            $insigniaModalItem['cor']
                            ?? '#38a5ff'
                        );
                    ?>

                    <article
                        class="
                            insignia-modal-card
                            <?= $desbloqueadaModal ? ' conquistada' : ' bloqueada' ?>
                            <?= $especial ? ' especial' : ' normal' ?>
                        "
                    >
                        <div class="insignia-modal-card-visual">
                            <?php if (!$desbloqueadaModal): ?>

                                <?php if (
                                    $imagemModal !== '' &&
                                    file_exists(
                                        __DIR__
                                        . '/../img/insignias/'
                                        . $imagemModal
                                    )
                                ): ?>

                                    <div
                                        class="insignia-bloqueada-silhueta"
                                        aria-label="Insígnia bloqueada"
                                        style="
                                            --insignia-silhueta:
                                            url('../img/insignias/<?= escapar($imagemModal) ?>');
                                        "
                                    ></div>

                                <?php else: ?>

                                    <div
                                        class="insignia-bloqueada-silhueta sem-imagem"
                                        aria-label="Insígnia bloqueada"
                                    ></div>

                                <?php endif; ?>

                            <?php elseif (
                                $imagemModal !== '' &&
                                file_exists(
                                    __DIR__
                                    . '/../img/insignias/'
                                    . $imagemModal
                                )
                            ): ?>

                                <img
                                    src="../img/insignias/<?= escapar($imagemModal) ?>"
                                    alt="<?= escapar($nomeModal) ?>"
                                >

                            <?php else: ?>

                                <i
                                    class="<?= escapar($iconeModal) ?>"
                                    style="color: <?= escapar($corModal) ?>;"
                                ></i>

                            <?php endif; ?>

                            <?php if ($desbloqueadaModal): ?>
                                <span class="insignia-modal-status">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="insignia-modal-card-info">

                            <?php if ($especial): ?>

                                <?php if ($desbloqueadaModal): ?>
                                    <h4 class="nome-especial-visivel">
                                        <?= escapar($nomeModal) ?>
                                    </h4>
                                <?php else: ?>
                                    <h4
                                        class="nome-especial-bloqueado"
                                        aria-label="Insígnia especial secreta"
                                    >
                                        <span>
                                            <?= escapar($nomeModal) ?>
                                        </span>
                                    </h4>
                                <?php endif; ?>

                            <?php else: ?>

                                <?php if ($nivelModal !== null): ?>
                                    <span class="insignia-nivel-chip">
                                        Nível <?= $nivelModal ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($textoBasicoModal !== ''): ?>
                                    <p class="insignia-texto-basico">
                                        <?= escapar($textoBasicoModal) ?>
                                    </p>
                                <?php endif; ?>

                            <?php endif; ?>

                        </div>
                    </article>

                    <?php
                }
                ?>

                <!-- ======================================
                     INSÍGNIAS NORMAIS
                ======================================= -->
                <section class="insignias-modal-grupo grupo-normal">
                    <div class="insignias-modal-grupo-topo">
                        <div class="insignias-modal-grupo-titulo">
                            <span class="grupo-insignia-icone normal">
                                <i class="fa-solid fa-award"></i>
                            </span>

                            <div>
                                <span class="grupo-insignia-sobretitulo">
                                    Conquistas
                                </span>

                                <h3>Insígnias</h3>

                                <p>
                                    Conquistas obtidas pelas suas atividades
                                    e evolução no FOAG.
                                </p>
                            </div>
                        </div>

                        <div class="grupo-insignia-contador">
                            <strong>
                                <?= $conquistadasNormaisPerfil ?>
                            </strong>

                            <span>
                                de <?= $totalInsigniasNormaisPerfil ?>
                            </span>
                        </div>
                    </div>

                    <?php if (empty($familiasInsigniasNormaisModal)): ?>
                        <div class="insignias-grupo-vazio">
                            Nenhuma insígnia normal cadastrada.
                        </div>
                    <?php else: ?>

                        <div class="insignias-familias-lista">
                            <?php foreach (
                                $familiasInsigniasNormaisModal
                                as $chaveFamilia => $familiaInsignia
                            ): ?>

                                <section class="insignia-familia">
<div class="insignia-familia-grid">
                                        <?php foreach (
                                            [1, 2, 3]
                                            as $nivelFamilia
                                        ): ?>

                                            <?php if (
                                                isset(
                                                    $familiaInsignia[
                                                        'niveis'
                                                    ][
                                                        $nivelFamilia
                                                    ]
                                                )
                                            ): ?>
                                                <?php
                                                $itemNivel =
                                                    $familiaInsignia[
                                                        'niveis'
                                                    ][
                                                        $nivelFamilia
                                                    ];

                                                $itemNivel['nivel'] =
                                                    $nivelFamilia;

                                                $itemNivel['texto_basico'] =
                                                    $textosBasicosInsigniasPerfil[
                                                        $chaveFamilia
                                                    ]
                                                    ?? 'Continue avançando nesta conquista.';

                                                renderizarCardInsigniaModalPerfil(
                                                    $itemNivel,
                                                    false
                                                );
                                                ?>
                                            <?php else: ?>
                                                <div class="insignia-nivel-ausente">
                                                    <span>
                                                        Nível <?= $nivelFamilia ?>
                                                    </span>

                                                    <i class="fa-regular fa-circle-question"></i>

                                                    <p>
                                                        Este nível ainda não está
                                                        cadastrado no sistema.
                                                    </p>
                                                </div>
                                            <?php endif; ?>

                                        <?php endforeach; ?>
                                    </div>
                                </section>

                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>
                </section>

                <!-- ======================================
                     INSÍGNIAS ESPECIAIS
                ======================================= -->
                <section class="insignias-modal-grupo grupo-especial">
                    <div class="insignias-modal-grupo-topo especial">
                        <div class="insignias-modal-grupo-titulo">
                            <span class="grupo-insignia-icone especial">
                                <i class="fa-solid fa-crown"></i>
                            </span>

                            <div>
                                <span class="grupo-insignia-sobretitulo especial">
                                    Raras
                                </span>

                                <h3>Insígnias Especiais</h3>

                                <p>
                                    Conquistas únicas, marcos especiais e
                                    desafios raros da sua jornada.
                                </p>
                            </div>
                        </div>

                        <div class="grupo-insignia-contador especial">
                            <strong>
                                <?= $conquistadasEspeciaisPerfil ?>
                            </strong>

                            <span>
                                de <?= $totalInsigniasEspeciaisPerfil ?>
                            </span>
                        </div>
                    </div>

                    <?php if (empty($insigniasEspeciaisModal)): ?>
                        <div class="insignias-grupo-vazio especial">
                            Nenhuma insígnia especial cadastrada.
                        </div>
                    <?php else: ?>
                        <div class="insignias-modal-grid especiais-grid">
                            <?php foreach (
                                $insigniasEspeciaisModal
                                as $insigniaEspecialModal
                            ): ?>
                                <?php
                                renderizarCardInsigniaModalPerfil(
                                    $insigniaEspecialModal,
                                    true
                                );
                                ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            </div>
            </div>
        </div>
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
        // MODAL DE INSÍGNIAS
        // ===========================================

        const modalInsignias =
            document.getElementById(
                'modalInsignias'
            );

        const btnVerTodasInsignias =
            document.getElementById(
                'btnVerTodasInsignias'
            );

        const btnVerMaisInsignias =
            document.getElementById(
                'btnVerMaisInsignias'
            );

        const fecharModalInsigniasBtn =
            document.getElementById(
                'fecharModalInsignias'
            );

        let scrollPerfilAntesModal = 0;

        function abrirModalInsignias() {
            if (!modalInsignias) {
                return;
            }

            scrollPerfilAntesModal =
                window.scrollY || 0;

            modalInsignias.classList.add(
                'aberto'
            );

            modalInsignias.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.classList.add(
                'modal-insignias-aberto'
            );

            setTimeout(
                function() {
                    fecharModalInsigniasBtn
                        ?.focus();
                },
                40
            );
        }

        function fecharModalInsignias() {
            if (!modalInsignias) {
                return;
            }

            modalInsignias.classList.remove(
                'aberto'
            );

            modalInsignias.setAttribute(
                'aria-hidden',
                'true'
            );

            document.body.classList.remove(
                'modal-insignias-aberto'
            );
        }

        btnVerTodasInsignias
            ?.addEventListener(
                'click',
                abrirModalInsignias
            );

        btnVerMaisInsignias
            ?.addEventListener(
                'click',
                abrirModalInsignias
            );

        fecharModalInsigniasBtn
            ?.addEventListener(
                'click',
                fecharModalInsignias
            );

        modalInsignias
            ?.addEventListener(
                'click',
                function(evento) {
                    if (
                        evento.target ===
                        modalInsignias
                    ) {
                        fecharModalInsignias();
                    }
                }
            );

        document.addEventListener(
            'keydown',
            function(evento) {
                if (
                    evento.key === 'Escape' &&
                    modalInsignias
                        ?.classList
                        .contains('aberto')
                ) {
                    fecharModalInsignias();
                }
            }
        );

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

            // Os itens comprados já foram renderizados pelo PHP.
            // Não apagamos os cards enquanto atualizamos os dados da Loja.
            console.log('🔄 Atualizando dados da loja sem esconder os itens...');

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
                        console.warn(
                            '⚠️ Dados da Loja incompletos. Mantendo os itens renderizados pelo PHP.'
                        );
                    }
                })
                .catch(function(erro) {
                    console.error('❌ Erro ao buscar itens da loja:', erro);

                    /*
                     * Os itens comprados já vieram do PHP.
                     * Tentamos cache apenas para atualizar dados,
                     * mas nunca apagamos os cards já visíveis.
                     */
                    carregarDoSessionStorage(container);
                });
        }

        function carregarDoSessionStorage(container) {
            try {
                const itensSalvos =
                    sessionStorage.getItem('itens_loja');

                const estrelasSalvas =
                    sessionStorage.getItem('estrelas_total');

                const itensAtivosSalvos =
                    sessionStorage.getItem('itens_ativos_loja');

                if (itensSalvos) {
                    const itens =
                        JSON.parse(itensSalvos);

                    const estrelas =
                        parseInt(estrelasSalvas) || 0;

                    const itensAtivos =
                        itensAtivosSalvos
                            ? JSON.parse(itensAtivosSalvos)
                            : {};

                    renderizarItensPerfil(
                        container,
                        itens,
                        estrelas,
                        itensAtivos
                    );

                    return;
                }
            } catch (e) {
                console.warn(
                    '⚠️ Não foi possível ler o cache da Loja.',
                    e
                );
            }

            console.warn(
                '⚠️ Sem cache da Loja. Mantendo os itens carregados pelo PHP.'
            );
        }

        function atualizarEstrelasPerfil(estrelas) {
            const semItens = document.querySelector('.sem-itens-loja small');
            if (semItens) {
                semItens.textContent = `⭐ ${estrelas} estrelas disponíveis`;
            }
        }

        function escaparHtmlPerfil(valor) {
            return String(valor ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function obterConfigColecaoPerfil() {
            return {
                molduras: {
                    nome: 'Molduras',
                    icone: 'fa-regular fa-image',
                    tipoAtivo: 'moldura'
                },
                temas: {
                    nome: 'Temas',
                    icone: 'fa-solid fa-palette',
                    tipoAtivo: 'tema'
                },
                fundos: {
                    nome: 'Fundos',
                    icone: 'fa-regular fa-images',
                    tipoAtivo: 'fundo'
                },
                especiais: {
                    nome: 'Especiais',
                    icone: 'fa-solid fa-wand-magic-sparkles',
                    tipoAtivo: 'cursor'
                }
            };
        }

        function itemVisualPerfil(item) {
            const imagemItem =
                String(item?.imagem || '').trim();

            const temImagemValida =
                /\.(png|jpe?g|webp|gif|svg)$/i.test(
                    imagemItem
                );

            if (temImagemValida) {
                return `
                    <img
                        src="${escaparHtmlPerfil(imagemItem)}"
                        alt="${escaparHtmlPerfil(item?.nome || 'Item')}"
                        loading="lazy"
                    >
                `;
            }

            const icone =
                escaparHtmlPerfil(
                    item?.icone ||
                    'fa-solid fa-gift'
                );

            return `<i class="${icone}"></i>`;
        }


        const PERFIL_LOJA_ACTION_URL =
            '../loja/salvar_loja.php';

        let ativandoItemPerfil = false;

        function mostrarMensagemEquiparPerfil(
            mensagem,
            tipo = 'sucesso'
        ) {
            const elemento =
                document.getElementById(
                    'mensagemEquiparPerfil'
                );

            if (!elemento) {
                return;
            }

            elemento.textContent =
                String(mensagem || '');

            elemento.className =
                'mensagem-equipar-perfil ' +
                (tipo === 'erro'
                    ? ' erro'
                    : ' sucesso');

            elemento.style.display =
                mensagem
                    ? 'flex'
                    : 'none';
        }

        function notificarLojaAtualizadaPerfil(dadosAtivos) {
            try {
                sessionStorage.setItem(
                    'itens_ativos_loja',
                    JSON.stringify(
                        dadosAtivos || {}
                    )
                );

                sessionStorage.setItem(
                    'loja_atualizada',
                    Date.now().toString()
                );
            } catch (erro) {
                console.warn(
                    'Não foi possível atualizar o cache da Loja.',
                    erro
                );
            }

            try {
                const channel =
                    new BroadcastChannel(
                        'foag_loja'
                    );

                channel.postMessage({
                    type: 'LOJA_ATUALIZADA',
                    itens_ativos:
                        dadosAtivos || {}
                });

                setTimeout(
                    function() {
                        channel.close();
                    },
                    100
                );
            } catch (erro) {
                // BroadcastChannel é opcional.
            }
        }

        async function ativarItemPeloPerfil(
            itemId,
            itemNome,
            botao
        ) {
            if (
                ativandoItemPerfil ||
                !itemId
            ) {
                return;
            }

            ativandoItemPerfil = true;

            const htmlOriginal =
                botao?.innerHTML || '';

            if (botao) {
                botao.disabled = true;
                botao.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    Aplicando...
                `;
            }

            mostrarMensagemEquiparPerfil(
                `Aplicando ${itemNome || 'item'}...`
            );

            try {
                const resposta =
                    await fetch(
                        PERFIL_LOJA_ACTION_URL,
                        {
                            method: 'POST',
                            credentials: 'same-origin',
                            cache: 'no-store',
                            headers: {
                                'Content-Type':
                                    'application/json',
                                'Accept':
                                    'application/json'
                            },
                            body: JSON.stringify({
                                acao: 'ativar',
                                item_id: itemId
                            })
                        }
                    );

                let dados = null;

                try {
                    dados =
                        await resposta.json();
                } catch (erro) {
                    throw new Error(
                        'O servidor retornou uma resposta inválida.'
                    );
                }

                if (
                    !resposta.ok ||
                    !dados ||
                    dados.sucesso !== true
                ) {
                    throw new Error(
                        dados?.mensagem ||
                        'Não foi possível ativar este item.'
                    );
                }

                const ativos =
                    dados?.dados?.itens_ativos ||
                    {};

                notificarLojaAtualizadaPerfil(
                    ativos
                );

                mostrarMensagemEquiparPerfil(
                    `✅ ${itemNome || 'Item'} está em uso!`
                );

                /*
                 * Recarrega o Perfil para aplicar também
                 * tema, fundo, cursor e moldura no restante
                 * da página usando os dados persistidos.
                 */
                window.location.reload();

            } catch (erro) {
                console.error(
                    'Erro ao ativar item pelo Perfil:',
                    erro
                );

                mostrarMensagemEquiparPerfil(
                    erro.message ||
                    'Não foi possível ativar este item.',
                    'erro'
                );

                if (botao) {
                    botao.disabled = false;
                    botao.innerHTML =
                        htmlOriginal;
                }

                ativandoItemPerfil = false;
            }
        }

        function inicializarColecaoPerfil() {
            const abas =
                document.querySelectorAll(
                    '.colecao-aba:not(:disabled)'
                );

            const paineis =
                document.querySelectorAll(
                    '.colecao-painel'
                );

            abas.forEach(function(aba) {
                aba.onclick = function() {
                    const categoria =
                        this.dataset.categoria;

                    abas.forEach(function(item) {
                        item.classList.toggle(
                            'ativa',
                            item === aba
                        );
                    });

                    paineis.forEach(function(painel) {
                        painel.classList.toggle(
                            'ativo',
                            painel.dataset.categoria ===
                                categoria
                        );
                    });
                };
            });

            document.querySelectorAll(
                '.btn-equipar-item:not(.em-uso)'
            ).forEach(function(botao) {
                botao.onclick = function(evento) {
                    evento.preventDefault();
                    evento.stopPropagation();

                    ativarItemPeloPerfil(
                        String(
                            this.dataset.itemId || ''
                        ),
                        String(
                            this.dataset.itemNome || 'Item'
                        ),
                        this
                    );
                };
            });

            document.querySelectorAll(
                '.btn-ver-colecao'
            ).forEach(function(botao) {
                botao.onclick = function() {
                    const painel =
                        this.closest(
                            '.colecao-painel'
                        );

                    if (!painel) {
                        return;
                    }

                    const extras =
                        painel.querySelectorAll(
                            '.colecao-item-extra'
                        );

                    const expandido =
                        this.dataset.expandido ===
                        'true';

                    extras.forEach(function(item) {
                        item.classList.toggle(
                            'visivel',
                            !expandido
                        );
                    });

                    this.dataset.expandido =
                        expandido
                            ? 'false'
                            : 'true';

                    const texto =
                        this.querySelector('span');

                    const total =
                        painel.querySelectorAll(
                            '.item-loja-perfil'
                        ).length;

                    if (texto) {
                        texto.textContent =
                            expandido
                                ? `Ver todos (${total})`
                                : 'Mostrar menos';
                    }

                    const icone =
                        this.querySelector('i');

                    if (icone) {
                        icone.className =
                            expandido
                                ? 'fa-solid fa-chevron-down'
                                : 'fa-solid fa-chevron-up';
                    }
                };
            });
        }

        function renderizarItensPerfil(
            container,
            itens,
            estrelas,
            itensAtivos = {}
        ) {
            const card =
                container?.closest(
                    '.personalizacoes-card'
                );

            if (!card) {
                return;
            }

            const config =
                obterConfigColecaoPerfil();

            /*
             * Emojis ficam propositalmente fora do Perfil.
             */
            const itensColecao =
                Array.isArray(itens)
                    ? itens.filter(function(item) {
                        return Boolean(
                            config[
                                String(
                                    item?.categoria || ''
                                )
                            ]
                        );
                    })
                    : [];

            const porCategoria = {
                molduras: [],
                temas: [],
                fundos: [],
                especiais: []
            };

            itensColecao.forEach(function(item) {
                porCategoria[
                    item.categoria
                ].push(item);
            });

            const total =
                itensColecao.length;

            const subtitulo =
                card.querySelector(
                    '.loja-itens-cabecalho p'
                );

            if (subtitulo) {
                subtitulo.textContent =
                    `${total} item${total === 1 ? '' : 's'} ` +
                    `desbloqueado${total === 1 ? '' : 's'}`;
            }

            // ---------------------------------------
            // Em uso
            // ---------------------------------------

            const ativosContainer =
                document.getElementById(
                    'itensAtivosPerfil'
                );

            const itensAtivosLista =
                itensColecao.filter(function(item) {
                    const categoria =
                        String(
                            item.categoria || ''
                        );

                    const tipoAtivo =
                        config[categoria]
                            ?.tipoAtivo;

                    if (!tipoAtivo) {
                        return false;
                    }

                    return String(
                        itensAtivos?.[tipoAtivo] || ''
                    ) === String(item.id || '');
                });

            if (ativosContainer) {
                if (!itensAtivosLista.length) {
                    ativosContainer.innerHTML = `
                        <div class="colecao-vazia colecao-vazia-compacta">
                            <i class="fa-regular fa-circle-check"></i>
                            <span>
                                Nenhuma personalização equipada.
                            </span>
                        </div>
                    `;
                } else {
                    ativosContainer.innerHTML =
                        itensAtivosLista.map(
                            function(item) {
                                const cat =
                                    config[
                                        item.categoria
                                    ];

                                return `
                                    <div class="personalizacao-ativa-card">
                                        <div class="personalizacao-ativa-visual">
                                            ${itemVisualPerfil(item)}
                                        </div>

                                        <div class="personalizacao-ativa-info">
                                            <span class="personalizacao-ativa-tipo">
                                                ${escaparHtmlPerfil(cat.nome)}
                                            </span>

                                            <strong>
                                                ${escaparHtmlPerfil(item.nome || 'Item')}
                                            </strong>
                                        </div>

                                        <span class="personalizacao-ativa-check">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                    </div>
                                `;
                            }
                        ).join('');
                }
            }

            // ---------------------------------------
            // Minha coleção
            // ---------------------------------------

            const blocoColecao =
                card.querySelector(
                    '.colecao-bloco'
                );

            if (!blocoColecao) {
                return;
            }

            if (!total) {
                blocoColecao.innerHTML = `
                    <div class="personalizacoes-bloco-titulo colecao-titulo">
                        <div>
                            <span class="titulo-selo titulo-selo-colecao">
                                <i class="fa-solid fa-box-open"></i>
                            </span>

                            <div>
                                <h4>Minha coleção</h4>
                                <p>Veja os itens que você já desbloqueou</p>
                            </div>
                        </div>
                    </div>

                    <div class="colecao-vazia">
                        <i class="fa-solid fa-box-open"></i>
                        <h5>Sua coleção ainda está vazia</h5>
                        <p>
                            Os itens comprados na Loja de Estrelas
                            aparecerão aqui.
                        </p>
                        <a href="../loja/loja.php">
                            Explorar a Loja
                        </a>
                    </div>
                `;
                return;
            }

            const ordem =
                ['molduras', 'temas', 'fundos', 'especiais'];

            const primeira =
                ordem.find(
                    categoria =>
                        porCategoria[categoria].length > 0
                );

            const abasHtml =
                ordem.map(function(categoria) {
                    const cat =
                        config[categoria];

                    const quantidade =
                        porCategoria[categoria].length;

                    return `
                        <button
                            type="button"
                            class="colecao-aba ${
                                categoria === primeira
                                    ? 'ativa'
                                    : ''
                            }"
                            data-categoria="${categoria}"
                            ${quantidade === 0 ? 'disabled' : ''}
                        >
                            <i class="${cat.icone}"></i>
                            <span>${cat.nome}</span>
                            <b>${quantidade}</b>
                        </button>
                    `;
                }).join('');

            const paineisHtml =
                ordem.map(function(categoria) {
                    const cat =
                        config[categoria];

                    const lista =
                        porCategoria[categoria];

                    const idAtivo =
                        String(
                            itensAtivos?.[
                                cat.tipoAtivo
                            ] || ''
                        );

                    let conteudo = '';

                    if (!lista.length) {
                        conteudo = `
                            <div class="colecao-vazia colecao-vazia-painel">
                                <i class="${cat.icone}"></i>
                                <span>
                                    Você ainda não possui itens desta categoria.
                                </span>
                            </div>
                        `;
                    } else {
                        const cards =
                            lista.map(
                                function(item, indice) {
                                    const ativo =
                                        idAtivo !== '' &&
                                        idAtivo ===
                                        String(item.id || '');

                                    return `
                                        <div class="
                                            item-loja-perfil
                                            ${ativo ? 'ativo' : ''}
                                            ${indice >= 8 ? 'colecao-item-extra' : ''}
                                        ">
                                            <div class="icone-item">
                                                ${itemVisualPerfil(item)}
                                            </div>

                                            <div class="nome-item">
                                                ${escaparHtmlPerfil(item.nome || 'Item')}
                                            </div>

                                            <div class="categoria-item">
                                                ${ativo ? 'ATIVO' : 'DESBLOQUEADO'}
                                            </div>

                                            ${
                                                ativo
                                                    ? `
                                                        <span class="btn-equipar-item em-uso">
                                                            <i class="fa-solid fa-check"></i>
                                                            Em uso
                                                        </span>
                                                    `
                                                    : `
                                                        <button
                                                            type="button"
                                                            class="btn-equipar-item"
                                                            data-item-id="${escaparHtmlPerfil(item.id || '')}"
                                                            data-item-nome="${escaparHtmlPerfil(item.nome || 'Item')}"
                                                            data-categoria="${escaparHtmlPerfil(categoria)}"
                                                        >
                                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                                            Usar
                                                        </button>
                                                    `
                                            }
                                        </div>
                                    `;
                                }
                            ).join('');

                        const botao =
                            lista.length > 8
                                ? `
                                    <button
                                        type="button"
                                        class="btn-ver-colecao"
                                        data-expandido="false"
                                    >
                                        <span>
                                            Ver todos (${lista.length})
                                        </span>

                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                `
                                : '';

                        conteudo = `
                            <div class="loja-itens-grid colecao-grid">
                                ${cards}
                            </div>
                            ${botao}
                        `;
                    }

                    return `
                        <div
                            class="colecao-painel ${
                                categoria === primeira
                                    ? 'ativo'
                                    : ''
                            }"
                            data-categoria="${categoria}"
                        >
                            ${conteudo}
                        </div>
                    `;
                }).join('');

            blocoColecao.innerHTML = `
                <div class="personalizacoes-bloco-titulo colecao-titulo">
                    <div>
                        <span class="titulo-selo titulo-selo-colecao">
                            <i class="fa-solid fa-box-open"></i>
                        </span>

                        <div>
                            <h4>Minha coleção</h4>
                            <p>Veja os itens que você já desbloqueou</p>
                        </div>
                    </div>
                </div>

                <div class="colecao-abas" id="colecaoAbasPerfil">
                    ${abasHtml}
                </div>

                <div class="colecao-paineis" id="itensLojaPerfil">
                    ${paineisHtml}
                </div>
            `;

            inicializarColecaoPerfil();

            console.log(
                '✅ Coleção renderizada:',
                total,
                'itens (emojis ocultos)'
            );
        }


        // ===========================================
        // INICIALIZAR
        // ===========================================

        

        inicializarColecaoPerfil();

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