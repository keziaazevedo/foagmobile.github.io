<?php
session_start();

// ======================================
// VERIFICAR LOGIN
// ======================================

if (empty($_SESSION['codigo_usuario'])) {
    header("Location: ../login/index.php");
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];
$current = basename($_SERVER['PHP_SELF']);

// ======================================
// CAMINHOS
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';

/* ======================================
   FOTOS DE PERFIL
====================================== */

$pastaFotosUrl = '../img/perfil/';
$pastaFotosArquivo = __DIR__ . '/../img/perfil/';
$fotoPadrao = 'foto_padrao.png';

$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;
$arquivoPerfil = $pastaUsuario . '/perfil.json';

if (!is_dir($pastaUsuario)) {
    exit("Pasta do usuário não encontrada.");
}

// ======================================
// LER JSON
// ======================================

function lerJson($arquivo)
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


// ======================================
// MOLDURAS DA LOJA
// ======================================

function normalizarAjusteMolduraRanking($ajuste)
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

/*
 * Os deslocamentos do ajuste_perfil foram calibrados sobre
 * um avatar-base de 132px. No Ranking usamos porcentagens
 * equivalentes para manter o encaixe em qualquer tamanho.
 */
function deslocamentoRankingPercentual($valorPx)
{
    if (!is_numeric($valorPx)) {
        return 0;
    }

    return ((float) $valorPx / 132) * 100;
}

$arquivoProdutosLoja =
    __DIR__ . '/../json/loja/produtos.json';

$dadosProdutosLoja =
    lerJson($arquivoProdutosLoja);

$moldurasLojaPorId = [];

$itensCatalogoLoja =
    isset($dadosProdutosLoja['itens']) &&
    is_array($dadosProdutosLoja['itens'])
        ? $dadosProdutosLoja['itens']
        : [];

foreach ($itensCatalogoLoja as $produtoLoja) {
    if (
        !is_array($produtoLoja) ||
        (string)($produtoLoja['categoria'] ?? '') !== 'molduras'
    ) {
        continue;
    }

    $idMoldura =
        trim((string)($produtoLoja['id'] ?? ''));

    $imagemMoldura =
        trim((string)($produtoLoja['imagem'] ?? ''));

    if (
        $idMoldura === '' ||
        $imagemMoldura === ''
    ) {
        continue;
    }

    $moldurasLojaPorId[$idMoldura] = [
        'id' => $idMoldura,
        'nome' => (string)($produtoLoja['nome'] ?? 'Moldura'),
        'imagem' => $imagemMoldura,
        'ajuste_perfil' => normalizarAjusteMolduraRanking(
            $produtoLoja['ajuste_perfil'] ?? []
        )
    ];
}

// ======================================
// NORMALIZAR TEXTO
// ======================================

function normalizarTexto($texto)
{
    $texto = trim((string) $texto);

    if (function_exists('mb_strtolower')) {
        return mb_strtolower(
            $texto,
            'UTF-8'
        );
    }

    return strtolower($texto);
}

// ======================================
// CAMPOS DE PERFIL COMPATÍVEIS
// ======================================

function obterPrimeiroTextoPerfil($perfil, $chaves)
{
    if (!is_array($perfil)) {
        return '';
    }

    foreach ($chaves as $chave) {
        if (!array_key_exists($chave, $perfil)) {
            continue;
        }

        $valor = trim((string) $perfil[$chave]);

        if ($valor !== '') {
            return $valor;
        }
    }

    return '';
}

// ======================================
// DESCOBRIR REGIÃO PELO ESTADO
// ======================================

function obterRegiaoBrasil($estado)
{
    $estado = strtoupper(
        trim((string) $estado)
    );

    $regioes = [

        'Norte' => [
            'AC',
            'AP',
            'AM',
            'PA',
            'RO',
            'RR',
            'TO'
        ],

        'Nordeste' => [
            'AL',
            'BA',
            'CE',
            'MA',
            'PB',
            'PE',
            'PI',
            'RN',
            'SE'
        ],

        'Centro-Oeste' => [
            'DF',
            'GO',
            'MT',
            'MS'
        ],

        'Sudeste' => [
            'ES',
            'MG',
            'RJ',
            'SP'
        ],

        'Sul' => [
            'PR',
            'RS',
            'SC'
        ]
    ];

    foreach ($regioes as $regiao => $estados) {

        if (
            in_array(
                $estado,
                $estados,
                true
            )
        ) {
            return $regiao;
        }
    }

    return '';
}

// ======================================
// FORMATAR TEMPO
// ======================================

function formatarTempo($minutos)
{
    $minutos = (int) $minutos;

    $horas = intdiv(
        $minutos,
        60
    );

    $restante =
        $minutos % 60;

    if (
        $horas > 0
        &&
        $restante > 0
    ) {
        return
            $horas
            . 'h '
            . $restante
            . 'min';
    }

    if ($horas > 0) {
        return $horas . 'h';
    }

    return $restante . 'min';
}

// ======================================
// CALCULAR POMODORO
// ======================================

function calcularPomodoro($arquivo)
{
    $dados = lerJson($arquivo);

    if (
        !isset($dados['sessions'])
        ||
        !is_array($dados['sessions'])
    ) {
        return 0;
    }

    $totalMinutos = 0;

    foreach ($dados['sessions'] as $sessao) {

        if (
            ($sessao['mode'] ?? '')
            !== 'focus'
        ) {
            continue;
        }

        $minutos =
            $sessao['minutes']
            ?? 0;

        if (is_numeric($minutos)) {

            $totalMinutos +=
                (int) $minutos;
        }
    }

    return $totalMinutos;
}

// ======================================
// CALCULAR MÉDIA DAS NOTAS
// ======================================

function calcularMediaNotas($arquivo)
{
    $dados = lerJson($arquivo);

    if (
        !isset($dados['periodos'])
        ||
        !is_array($dados['periodos'])
    ) {
        return 0;
    }

    $pesos =
        $dados['pesos']
        ?? [];

    $somaNotas = 0;
    $somaPesos = 0;

    foreach (
        $dados['periodos']
        as $periodo
    ) {

        if (
            !isset($periodo['notas'])
            ||
            !is_array($periodo['notas'])
        ) {
            continue;
        }

        foreach (
            $periodo['notas']
            as $notasMateria
        ) {

            if (!is_array($notasMateria)) {
                continue;
            }

            foreach (
                $notasMateria
                as $numeroAvaliacao => $nota
            ) {

                if (!is_numeric($nota)) {
                    continue;
                }

                $peso = 1;

                if (
                    isset(
                        $pesos[
                            $numeroAvaliacao
                        ]
                    )
                    &&
                    is_numeric(
                        $pesos[
                            $numeroAvaliacao
                        ]
                    )
                ) {

                    $peso =
                        (float)
                        $pesos[
                            $numeroAvaliacao
                        ];
                }

                $somaNotas +=
                    (float) $nota
                    * $peso;

                $somaPesos +=
                    $peso;
            }
        }
    }

    if ($somaPesos <= 0) {
        return 0;
    }

    return
        $somaNotas
        /
        $somaPesos;
}

// ======================================
// CARREGAR PERFIL ATUAL
// ======================================

$dadosPerfilAtual =
    lerJson(
        $arquivoPerfil
    );

$usuarioAtual =
    $dadosPerfilAtual['nome']
    ?? $_SESSION['user_nome']
    ?? $_SESSION['usuario']
    ?? 'Usuário FOAG';

$estadoUsuarioAtual =
    strtoupper(
        trim(
            $dadosPerfilAtual['estado']
            ?? ''
        )
    );

$cidadeUsuarioAtual =
    trim(
        $dadosPerfilAtual['cidade']
        ?? ''
    );

$escolaUsuarioAtual =
    obterPrimeiroTextoPerfil(
        $dadosPerfilAtual,
        [
            'escola',
            'nome_escola',
            'escola_nome',
            'instituicao',
            'instituição'
        ]
    );

$serieUsuarioAtual =
    obterPrimeiroTextoPerfil(
        $dadosPerfilAtual,
        [
            'serie',
            'série',
            'serie_escolar',
            'ano_escolar'
        ]
    );

$_SESSION['user_nome'] =
    $usuarioAtual;

$_SESSION['usuario'] =
    $usuarioAtual;

// ======================================
// CARREGAR TODOS OS USUÁRIOS
// ======================================

$usuarios = [];

$pastasUsuarios = glob(
    $baseJsonDir . '/*',
    GLOB_ONLYDIR
);

if ($pastasUsuarios === false) {
    $pastasUsuarios = [];
}

foreach ($pastasUsuarios as $pasta) {

    $codigo =
        basename($pasta);

    $perfil =
        lerJson(
            $pasta
            . '/perfil.json'
        );

    if (empty($perfil['nome'])) {
        continue;
    }

    // ==================================
    // FOTO DE PERFIL
    // ==================================

    $fotoPerfil = $fotoPadrao;

    if (!empty($perfil['foto'])) {

        $fotoUsuario =
            basename(
                (string) $perfil['foto']
            );

        if (
            $fotoUsuario !== ''
            &&
            file_exists(
                $pastaFotosArquivo .
                $fotoUsuario
            )
        ) {
            $fotoPerfil =
                $fotoUsuario;
        }
    }

    /*
     * Caso a foto padrão também não exista,
     * o HTML abaixo usará um ícone como fallback.
     */
    $caminhoFoto =
        $pastaFotosUrl .
        rawurlencode(
            $fotoPerfil
        );


    // ==================================
    // MOLDURA ATIVA DO USUÁRIO
    // ==================================

    $molduraPerfil = null;

    $dadosLojaUsuario =
        lerJson(
            $pasta . '/loja.json'
        );

    $itensAtivosUsuario =
        isset($dadosLojaUsuario['itens_ativos']) &&
        is_array($dadosLojaUsuario['itens_ativos'])
            ? $dadosLojaUsuario['itens_ativos']
            : [];

    $itensCompradosUsuario =
        isset($dadosLojaUsuario['itens_comprados']) &&
        is_array($dadosLojaUsuario['itens_comprados'])
            ? $dadosLojaUsuario['itens_comprados']
            : [];

    $idMolduraAtiva =
        isset($itensAtivosUsuario['moldura'])
            ? trim((string)$itensAtivosUsuario['moldura'])
            : '';

    if (
        $idMolduraAtiva !== '' &&
        in_array(
            $idMolduraAtiva,
            $itensCompradosUsuario,
            true
        ) &&
        isset($moldurasLojaPorId[$idMolduraAtiva])
    ) {
        $molduraPerfil =
            $moldurasLojaPorId[$idMolduraAtiva];
    }

    // ==================================
    // LOCALIZAÇÃO
    // ==================================

    $estado =
        strtoupper(
            trim(
                $perfil['estado']
                ?? ''
            )
        );

    $cidade =
        trim(
            $perfil['cidade']
            ?? ''
        );

    $escola =
        obterPrimeiroTextoPerfil(
            $perfil,
            [
                'escola',
                'nome_escola',
                'escola_nome',
                'instituicao',
                'instituição'
            ]
        );

    $serie =
        obterPrimeiroTextoPerfil(
            $perfil,
            [
                'serie',
                'série',
                'serie_escolar',
                'ano_escolar'
            ]
        );

    // ==================================
    // ESTRELAS
    // ==================================

    $estrelas = 0;

    $arquivoPontos =
        $pasta
        . '/pontos.json';

    if (file_exists($arquivoPontos)) {

        $dadosPontos =
            lerJson(
                $arquivoPontos
            );

        if (
            isset(
                $dadosPontos['estrelas']
            )
            &&
            is_numeric(
                $dadosPontos['estrelas']
            )
        ) {

            $estrelas =
                (int)
                $dadosPontos['estrelas'];
        }
    }

    // ==================================
    // POMODORO
    // ==================================

    $minutosPomodoro =
        calcularPomodoro(
            $pasta
            . '/pomodoro.json'
        );

    // ==================================
    // NOTAS
    // ==================================

    $mediaNotas =
        calcularMediaNotas(
            $pasta
            . '/notas.json'
        );

    // ==================================
    // USUÁRIO
    // ==================================

    $usuarios[] = [

        'codigo_usuario' =>
            $codigo,

        'nome' =>
            $perfil['nome'],

        'foto' =>
            $caminhoFoto,

        'moldura' =>
            $molduraPerfil,

        'estado' =>
            $estado,

        'cidade' =>
            $cidade,

        'escola' =>
            $escola,

        'serie' =>
            $serie,

        'estrelas' =>
            $estrelas,

        'pomodoro' =>
            $minutosPomodoro,

        'notas' =>
            $mediaNotas
    ];
}

// ======================================
// ESTRELAS DO USUÁRIO ATUAL
// ======================================

$estrelasUsuarioAtual = 0;

foreach ($usuarios as $usuarioCarregado) {
    if (
        (string)($usuarioCarregado['codigo_usuario'] ?? '')
        === (string)$codigoUsuario
    ) {
        $estrelasUsuarioAtual =
            (int)($usuarioCarregado['estrelas'] ?? 0);
        break;
    }
}

// ======================================
// FILTRAR POR NÍVEL
// ======================================

function filtrarUsuariosPorNivel(
    $usuarios,
    $nivel,
    $estadoAtual,
    $cidadeAtual,
    $escolaAtual,
    $serieAtual
) {
    if ($nivel === 'nacional') {
        return $usuarios;
    }

    $resultado = [];

    foreach ($usuarios as $usuario) {
        if ($nivel === 'estadual') {
            if ($estadoAtual === '') {
                continue;
            }

            if (($usuario['estado'] ?? '') === $estadoAtual) {
                $resultado[] = $usuario;
            }
        } elseif ($nivel === 'municipal') {
            if ($estadoAtual === '' || $cidadeAtual === '') {
                continue;
            }

            if (
                ($usuario['estado'] ?? '') === $estadoAtual
                && normalizarTexto($usuario['cidade'] ?? '')
                    === normalizarTexto($cidadeAtual)
            ) {
                $resultado[] = $usuario;
            }
        } elseif ($nivel === 'escola') {
            if ($escolaAtual === '') {
                continue;
            }

            if (
                normalizarTexto($usuario['escola'] ?? '')
                === normalizarTexto($escolaAtual)
            ) {
                $resultado[] = $usuario;
            }
        } elseif ($nivel === 'serie') {
            if ($escolaAtual === '' || $serieAtual === '') {
                continue;
            }

            if (
                normalizarTexto($usuario['escola'] ?? '')
                === normalizarTexto($escolaAtual)
                && normalizarTexto($usuario['serie'] ?? '')
                    === normalizarTexto($serieAtual)
            ) {
                $resultado[] = $usuario;
            }
        }
    }

    return $resultado;
}

// ======================================
// USUÁRIOS POR NÍVEL
// ======================================

$usuariosPorNivel = [
    'nacional' =>
        filtrarUsuariosPorNivel(
            $usuarios,
            'nacional',
            $estadoUsuarioAtual,
            $cidadeUsuarioAtual,
            $escolaUsuarioAtual,
            $serieUsuarioAtual
        ),

    'estadual' =>
        filtrarUsuariosPorNivel(
            $usuarios,
            'estadual',
            $estadoUsuarioAtual,
            $cidadeUsuarioAtual,
            $escolaUsuarioAtual,
            $serieUsuarioAtual
        ),

    'municipal' =>
        filtrarUsuariosPorNivel(
            $usuarios,
            'municipal',
            $estadoUsuarioAtual,
            $cidadeUsuarioAtual,
            $escolaUsuarioAtual,
            $serieUsuarioAtual
        ),

    'escola' =>
        filtrarUsuariosPorNivel(
            $usuarios,
            'escola',
            $estadoUsuarioAtual,
            $cidadeUsuarioAtual,
            $escolaUsuarioAtual,
            $serieUsuarioAtual
        ),

    'serie' =>
        filtrarUsuariosPorNivel(
            $usuarios,
            'serie',
            $estadoUsuarioAtual,
            $cidadeUsuarioAtual,
            $escolaUsuarioAtual,
            $serieUsuarioAtual
        )
];

// ======================================
// CRIAR RANKING
// ======================================

function criarRanking(
    $usuarios,
    $campo,
    $formatador = null
) {

    $jogadores = [];

    foreach ($usuarios as $usuario) {

        $valorBruto =
            $usuario[$campo]
            ?? 0;

        $valorExibicao =
            $valorBruto;

        if (is_callable($formatador)) {

            $valorExibicao =
                $formatador(
                    $valorBruto
                );
        }

        $jogadores[] = [

            'codigo_usuario' =>
                $usuario[
                    'codigo_usuario'
                ],

            'nome' =>
                $usuario['nome'],

            'foto' =>
                $usuario['foto'],

            'moldura' =>
                $usuario['moldura'] ?? null,

            'estado' =>
                $usuario['estado'],

            'cidade' =>
                $usuario['cidade'],

            'escola' =>
                $usuario['escola'] ?? '',

            'serie' =>
                $usuario['serie'] ?? '',

            'valor_bruto' =>
                $valorBruto,

            'valor' =>
                $valorExibicao,

            'nivel' => 0
        ];
    }

    // ==================================
    // ORDENAR
    // ==================================

    usort(
        $jogadores,
        function ($a, $b) {

            $comparacao =
                $b['valor_bruto']
                <=>
                $a['valor_bruto'];

            if ($comparacao !== 0) {
                return $comparacao;
            }

            return strcasecmp(
                $a['nome'],
                $b['nome']
            );
        }
    );

    // ==================================
    // DEFINIR POSIÇÃO
    // ==================================

    foreach (
        $jogadores
        as $indice => &$jogador
    ) {

        $jogador['nivel'] =
            $indice + 1;
    }

    unset($jogador);

    return $jogadores;
}

// ======================================
// CRIAR NÍVEIS DE UMA CATEGORIA
// ======================================

function criarNiveisRanking(
    $usuariosPorNivel,
    $campo,
    $formatador = null
) {

    $resultado = [];

    foreach (
        $usuariosPorNivel
        as $nivel => $usuariosNivel
    ) {

        $resultado[$nivel] = [
            'jogadores' =>
                criarRanking(
                    $usuariosNivel,
                    $campo,
                    $formatador
                )
        ];
    }

    return $resultado;
}

// ======================================
// NOMES DOS NÍVEIS
// ======================================

$nomesNiveis = [
    'nacional' => '🌎 Nacional',
    'estadual' =>
        $estadoUsuarioAtual !== ''
            ? '🏛️ Estadual (' . $estadoUsuarioAtual . ')'
            : '🏛️ Estadual',
    'municipal' =>
        $cidadeUsuarioAtual !== ''
            ? '🏙️ Municipal (' . $cidadeUsuarioAtual . ')'
            : '🏙️ Municipal',
    'escola' =>
        $escolaUsuarioAtual !== ''
            ? '🏫 Escola (' . $escolaUsuarioAtual . ')'
            : '🏫 Escola',
    'serie' =>
        $serieUsuarioAtual !== ''
            ? '🎓 Minha série (' . $serieUsuarioAtual . ')'
            : '🎓 Minha série'
];

// ======================================
// RANKINGS
// ======================================

$rankings = [

    'estrelas' => [

        'titulo' =>
            'Estrelas',

        'icone' =>
            '⭐',

        'cor' =>
            '#ffd700',

        'descricao' =>
            'Quem conquistou mais estrelas no FOAG',

        'niveis' =>
            criarNiveisRanking(
                $usuariosPorNivel,
                'estrelas',
                function ($valor) {
                    return (int) $valor;
                }
            )
    ],

    'pomodoro' => [

        'titulo' =>
            'Foco',

        'icone' =>
            '⏱️',

        'cor' =>
            '#4caf50',

        'descricao' =>
            'Quem acumulou mais tempo de estudo focado',

        'niveis' =>
            criarNiveisRanking(
                $usuariosPorNivel,
                'pomodoro',
                function ($valor) {
                    return (int)$valor > 0
                        ? formatarTempo($valor)
                        : '—';
                }
            )
    ],

    'notas' => [

        'titulo' =>
            'Desempenho',

        'icone' =>
            '📚',

        'cor' =>
            '#9c27b0',

        'descricao' =>
            'As maiores médias escolares cadastradas',

        'niveis' =>
            criarNiveisRanking(
                $usuariosPorNivel,
                'notas',
                function ($valor) {
                    return (float)$valor > 0
                        ? number_format(
                            (float)$valor,
                            1,
                            ',',
                            ''
                        )
                        : '—';
                }
            )
    ]
];

// ======================================
// COLOCAR NOME NOS NÍVEIS
// ======================================

foreach ($rankings as &$ranking) {

    foreach ($ranking['niveis'] as $nivelKey => &$nivel) {

        $nivel['nome'] =
            $nomesNiveis[$nivelKey]
            ?? ucfirst($nivelKey);
    }

    unset($nivel);
}

unset($ranking);

// ======================================
// ORDEM DAS CATEGORIAS
// ======================================

$categoriasOrdenadas = [
    'estrelas',
    'pomodoro',
    'notas'
];

$niveisDisponiveis = [
    'nacional',
    'estadual',
    'municipal',
    'escola',
    'serie'
];

$niveisPrincipais = [
    'nacional',
    'estadual',
    'municipal',
    'escola'
];

// ======================================
// QUANTIDADE POR NÍVEL
// ======================================

$quantidadesNivel = [];

foreach (
    $niveisDisponiveis
    as $nivelKey
) {

    $quantidadesNivel[$nivelKey] =
        count(
            $usuariosPorNivel[
                $nivelKey
            ] ?? []
        );
}

function encontrarJogadorAtual($jogadores, $codigoUsuario)
{
    foreach ($jogadores as $indice => $jogador) {
        if (($jogador['codigo_usuario'] ?? '') === $codigoUsuario) {
            return [
                'indice' => $indice,
                'posicao' => $indice + 1,
                'jogador' => $jogador
            ];
        }
    }

    return null;
}

function calcularDistanciaProximaPosicao($jogadores, $codigoUsuario)
{
    $atual = encontrarJogadorAtual($jogadores, $codigoUsuario);

    if (!$atual || $atual['indice'] <= 0) {
        return null;
    }

    $acima = $jogadores[$atual['indice'] - 1] ?? null;

    if (!$acima) {
        return null;
    }

    $diferenca =
        (float)($acima['valor_bruto'] ?? 0)
        - (float)($atual['jogador']['valor_bruto'] ?? 0);

    return max(0, $diferenca);
}

function dadosAvatarRanking($jogador)
{
    $moldura =
        isset($jogador['moldura']) && is_array($jogador['moldura'])
            ? $jogador['moldura']
            : null;

    $temMoldura =
        $moldura && !empty($moldura['imagem']);

    $ajuste = normalizarAjusteMolduraRanking(
        $temMoldura
            ? ($moldura['ajuste_perfil'] ?? [])
            : []
    );

    return [
        'moldura' => $moldura,
        'tem_moldura' => $temMoldura,
        'ajuste' => $ajuste,
        'moldura_x_pct' => deslocamentoRankingPercentual($ajuste['moldura_x']),
        'moldura_y_pct' => deslocamentoRankingPercentual($ajuste['moldura_y']),
        'foto_x_pct' => deslocamentoRankingPercentual($ajuste['foto_x']),
        'foto_y_pct' => deslocamentoRankingPercentual($ajuste['foto_y'])
    ];
}
function renderAvatarRankingHtml($jogador, $classeExtra = '')
{
    $dados = dadosAvatarRanking($jogador);
    $ajuste = $dados['ajuste'];
    $moldura = $dados['moldura'];
    $temMoldura = $dados['tem_moldura'];

    $classe = 'avatar avatar-foto'
        . ($temMoldura ? ' com-moldura' : '')
        . ($classeExtra !== '' ? ' ' . $classeExtra : '');

    ob_start();
    ?>
    <div class="<?= htmlspecialchars($classe, ENT_QUOTES, 'UTF-8') ?>">
        <div
            class="avatar-stage<?= $temMoldura ? ' tem-moldura' : '' ?>"
            style="
                --rank-moldura-escala: <?= htmlspecialchars((string)$ajuste['moldura_escala'], ENT_QUOTES, 'UTF-8') ?>;
                --rank-moldura-x: <?= htmlspecialchars((string)$dados['moldura_x_pct'], ENT_QUOTES, 'UTF-8') ?>%;
                --rank-moldura-y: <?= htmlspecialchars((string)$dados['moldura_y_pct'], ENT_QUOTES, 'UTF-8') ?>%;
                --rank-foto-escala: <?= htmlspecialchars((string)$ajuste['foto_escala'], ENT_QUOTES, 'UTF-8') ?>;
                --rank-foto-x: <?= htmlspecialchars((string)$dados['foto_x_pct'], ENT_QUOTES, 'UTF-8') ?>%;
                --rank-foto-y: <?= htmlspecialchars((string)$dados['foto_y_pct'], ENT_QUOTES, 'UTF-8') ?>%;
            "
        >
            <div class="avatar-foto-recorte">
                <img
                    class="avatar-foto-img"
                    src="<?= htmlspecialchars($jogador['foto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    alt="Foto de perfil de <?= htmlspecialchars($jogador['nome'] ?? 'Estudante', ENT_QUOTES, 'UTF-8') ?>"
                    loading="lazy"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                >
                <span class="avatar-fallback" aria-hidden="true">
                    <i class="fa-solid fa-user"></i>
                </span>
            </div>

            <?php if ($temMoldura): ?>
                <img
                    class="avatar-moldura-img"
                    src="<?= htmlspecialchars($moldura['imagem'], ENT_QUOTES, 'UTF-8') ?>"
                    alt=""
                    aria-hidden="true"
                    loading="lazy"
                >
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ranking — FOAG</title>

    <link
        rel="stylesheet"
        href="rank.css?v=5"
    >

    <link
        rel="stylesheet"
        href="../m.escuro/dark_basee.css"
    >

    <link
        rel="stylesheet"
        href="dark_rank.css?v=5"
    >

    <!-- ACESSIBILIDADE GLOBAL -->


    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <script src="../m.escuro/dark-mode.js"></script>

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

        <a
            href="../configuracoes/configuracoes.php"
            class="link-configuracoes"
            title="Configurações"
        >
            <i class="fa-solid fa-gear"></i>
        </a>

        <i
            id="icon-perfil"
            class="fa-regular fa-user"
            title="Perfil"
        ></i>

        <i
            id="icon-sair"
            class="fa-solid fa-right-from-bracket"
            title="Sair"
        ></i>

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

    <div class="ranking-header">
        <div class="ranking-titulo">
            <span class="ranking-kicker">Competição saudável • progresso real</span>

            <h1>
                <i class="fa-solid fa-trophy"></i>
                Ranking FOAG
            </h1>

            <p>
                Estude, evolua e acompanhe sua posição entre outros estudantes.
            </p>

            <div class="ranking-titulo-pill" id="rankingTituloPill">
                <i class="fa-solid fa-star"></i>
                <span>Categoria ativa: Estrelas</span>
            </div>
        </div>

        <div class="ranking-header-estrelas" aria-live="polite" aria-label="Resumo da sua pontuação no ranking">
            <span class="header-estrelas-label" id="headerResumoLabel">Suas estrelas</span>

            <strong class="header-estrelas-valor">
                <i class="fa-solid fa-star" id="headerResumoIcone"></i>
                <span id="headerResumoValor"><?= number_format($estrelasUsuarioAtual, 0, ',', '.') ?></span>
            </strong>

            <small id="headerResumoTexto">Categoria Estrelas • Nacional</small>
        </div>
    </div>

    <div class="abas-niveis" id="abasNiveis">
        <button class="aba-nivel active" data-nivel="nacional" type="button">
            <i class="fa-solid fa-earth-americas"></i>
            Nacional
            <span class="badge-nivel"><?= $quantidadesNivel['nacional'] ?></span>
        </button>

        <button class="aba-nivel" data-nivel="estadual" type="button">
            <i class="fa-solid fa-landmark"></i>
            Estadual
            <span class="badge-nivel"><?= $quantidadesNivel['estadual'] ?></span>
        </button>

        <button class="aba-nivel" data-nivel="municipal" type="button">
            <i class="fa-solid fa-city"></i>
            Municipal
            <span class="badge-nivel"><?= $quantidadesNivel['municipal'] ?></span>
        </button>

        <button class="aba-nivel" data-nivel="escola" type="button" title="<?= htmlspecialchars($escolaUsuarioAtual ?: 'Escola não cadastrada', ENT_QUOTES, 'UTF-8') ?>">
            <i class="fa-solid fa-school"></i>
            Escola
            <span class="badge-nivel"><?= $quantidadesNivel['escola'] ?></span>
        </button>
    </div>

    <div class="escola-subfiltros" id="escolaSubfiltros" hidden>
        <div class="escola-identificacao">
            <i class="fa-solid fa-school"></i>
            <span>
                <?= htmlspecialchars($escolaUsuarioAtual !== '' ? $escolaUsuarioAtual : 'Escola não cadastrada') ?>
            </span>
        </div>

        <div class="escola-scope-botoes">
            <button type="button" class="escola-scope active" data-school-scope="escola">
                Toda a escola
            </button>

            <?php if ($escolaUsuarioAtual !== '' && $serieUsuarioAtual !== ''): ?>
                <button type="button" class="escola-scope" data-school-scope="serie">
                    Minha série · <?= htmlspecialchars($serieUsuarioAtual) ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="rank-layout">
        <aside class="rank-menu-lateral">
            <div class="menu-titulo">
                <i class="fa-solid fa-layer-group"></i>
                Categorias
            </div>

            <?php foreach ($categoriasOrdenadas as $index => $key): ?>
                <?php
                $ranking = $rankings[$key];
                $ativo = $index === 0 ? 'active' : '';
                $totalEstudantes = count($ranking['niveis']['nacional']['jogadores']);

                $descricaoCategoria = [
                    'estrelas' => 'Conquistas e progresso',
                    'pomodoro' => 'Tempo de estudo focado',
                    'notas' => 'Média escolar'
                ][$key] ?? '';
                ?>

                <button
                    type="button"
                    class="menu-item <?= $ativo ?>"
                    data-categoria="<?= htmlspecialchars($key) ?>"
                >
                    <span class="item-icone"><?= htmlspecialchars($ranking['icone']) ?></span>

                    <span class="item-textos">
                        <strong class="item-nome"><?= htmlspecialchars($ranking['titulo']) ?></strong>
                        <small class="item-descricao"><?= htmlspecialchars($descricaoCategoria) ?></small>
                    </span>

                    <span class="item-badge" title="Estudantes neste ranking">
                        <?= $totalEstudantes ?>
                    </span>

                    <span class="indicador-ativo"></span>
                </button>
            <?php endforeach; ?>
        </aside>

        <div class="rank-conteudo">
            <?php $primeiro = true; ?>
            <?php foreach ($categoriasOrdenadas as $key): ?>
                <?php
                $ranking = $rankings[$key];
                $hidden = $primeiro ? '' : 'hidden';
                $primeiro = false;
                $jogadoresNacional = $ranking['niveis']['nacional']['jogadores'];

                $rotuloValor = $key === 'estrelas'
                    ? 'estrelas'
                    : ($key === 'pomodoro' ? 'tempo focado' : 'média');
                ?>

                <section
                    class="rank-full <?= $hidden ?>"
                    data-categoria="<?= htmlspecialchars($key) ?>"
                    id="rank-<?= htmlspecialchars($key) ?>"
                >
                    <div class="rank-full-header" style="border-bottom-color: <?= htmlspecialchars($ranking['cor']) ?>;">
                        <div class="rank-info">
                            <div
                                class="icone-grande"
                                style="background: <?= htmlspecialchars($ranking['cor']) ?>22; color: <?= htmlspecialchars($ranking['cor']) ?>;"
                            >
                                <?= htmlspecialchars($ranking['icone']) ?>
                            </div>

                            <div class="titulo">
                                <h2><?= htmlspecialchars($ranking['titulo']) ?></h2>
                                <p><?= htmlspecialchars($ranking['descricao']) ?></p>
                            </div>
                        </div>

                        <div class="rank-stats">
                            <span class="stat">
                                <i class="fa-solid fa-graduation-cap"></i>
                                <span class="total-jogadores"><?= count($jogadoresNacional) ?></span>
                                estudantes
                            </span>

                            <span class="stat">
                                <i class="fa-solid fa-crown" style="color: <?= htmlspecialchars($ranking['cor']) ?>;"></i>
                                Líder:
                                <span class="top1-nome">
                                    <?= !empty($jogadoresNacional) ? htmlspecialchars($jogadoresNacional[0]['nome']) : '—' ?>
                                </span>
                            </span>
                        </div>
                    </div>

                    <div class="minha-posicao-card">
                        <div class="minha-posicao-intro">
                            <div class="minha-posicao-icone">
                                <i class="fa-solid fa-location-crosshairs"></i>
                            </div>
                            <div>
                                <span class="mini-label">Sua posição</span>
                                <h3><?= htmlspecialchars($ranking['titulo']) ?></h3>
                                <p>Veja sua posição nos principais grupos.</p>
                            </div>
                        </div>

                        <div class="posicoes-resumo">
                            <?php foreach ($niveisPrincipais as $nivelResumo): ?>
                                <?php
                                $listaResumo = $ranking['niveis'][$nivelResumo]['jogadores'] ?? [];
                                $atualResumo = encontrarJogadorAtual($listaResumo, $codigoUsuario);
                                $iconeResumo = [
                                    'nacional' => 'fa-earth-americas',
                                    'estadual' => 'fa-landmark',
                                    'municipal' => 'fa-city',
                                    'escola' => 'fa-school'
                                ][$nivelResumo] ?? 'fa-ranking-star';
                                $nomeResumo = [
                                    'nacional' => 'Nacional',
                                    'estadual' => 'Estado',
                                    'municipal' => 'Cidade',
                                    'escola' => 'Escola'
                                ][$nivelResumo] ?? ucfirst($nivelResumo);
                                ?>

                                <div class="posicao-resumo-item">
                                    <i class="fa-solid <?= $iconeResumo ?>"></i>
                                    <span><?= $nomeResumo ?></span>
                                    <strong><?= $atualResumo ? '#' . $atualResumo['posicao'] : '—' ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="minha-posicao-acoes">
                            <span class="progresso-proxima" data-progresso-proxima>
                                Continue estudando para subir no ranking.
                            </span>

                            <button
                                type="button"
                                class="btn-compartilhar-posicao"
                                data-share-ranking
                                data-categoria="<?= htmlspecialchars($key) ?>"
                            >
                                <i class="fa-solid fa-share-nodes"></i>
                                Compartilhar posição
                            </button>
                        </div>
                    </div>

                    <div class="rank-full-body">
                        <?php foreach ($niveisDisponiveis as $nivelKey): ?>
                            <?php
                            $jogadores = $ranking['niveis'][$nivelKey]['jogadores'] ?? [];
                            $mostrar = $nivelKey === 'nacional' ? 'block' : 'none';
                            $atual = encontrarJogadorAtual($jogadores, $codigoUsuario);
                            ?>

                            <div
                                class="nivel-conteudo"
                                data-nivel="<?= htmlspecialchars($nivelKey) ?>"
                                style="display: <?= $mostrar ?>;"
                            >
                                <div class="nivel-contexto">
                                    <span><?= htmlspecialchars($ranking['niveis'][$nivelKey]['nome'] ?? ucfirst($nivelKey)) ?></span>
                                    <strong><?= count($jogadores) ?> estudantes</strong>
                                </div>

                                <?php if (empty($jogadores)): ?>
                                    <div class="rank-vazio">
                                        <div class="rank-vazio-icone">
                                            <i class="fa-solid <?= in_array($nivelKey, ['escola', 'serie'], true) ? 'fa-school' : 'fa-ranking-star' ?>"></i>
                                        </div>

                                        <?php if ($nivelKey === 'escola' && $escolaUsuarioAtual === ''): ?>
                                            <h3>Cadastre sua escola para liberar este ranking</h3>
                                            <p>Adicione sua escola no Perfil para comparar sua posição com outros estudantes dela.</p>
                                        <?php elseif ($nivelKey === 'escola'): ?>
                                            <h3>Ainda não há outros estudantes da sua escola no FOAG</h3>
                                            <p>Convide seus colegas e comece o ranking da sua escola.</p>
                                        <?php elseif ($nivelKey === 'serie' && $serieUsuarioAtual === ''): ?>
                                            <h3>Cadastre sua série no Perfil</h3>
                                            <p>Com a série cadastrada, o FOAG poderá montar uma classificação ainda mais próxima de você.</p>
                                        <?php elseif ($nivelKey === 'serie'): ?>
                                            <h3>Sua série ainda não tem um ranking ativo</h3>
                                            <p>Quando colegas da mesma série entrarem no FOAG, eles aparecerão aqui.</p>
                                        <?php else: ?>
                                            <h3>Nenhum estudante encontrado</h3>
                                            <p>Este ranking ainda não tem participantes suficientes.</p>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <?php $podio = array_slice($jogadores, 0, 3); ?>

                                    <div class="podio-ranking <?= count($podio) < 3 ? 'podio-incompleto' : '' ?>">
                                        <?php foreach ($podio as $podioIndex => $jogadorPodio): ?>
                                            <?php
                                            $posicaoPodio = $podioIndex + 1;
                                            $isUsuarioPodio = ($jogadorPodio['codigo_usuario'] ?? '') === $codigoUsuario;
                                            $medalhaPodio = $posicaoPodio === 1 ? '🥇' : ($posicaoPodio === 2 ? '🥈' : '🥉');
                                            ?>
                                            <article class="podio-card lugar-<?= $posicaoPodio ?> <?= $isUsuarioPodio ? 'usuario-destaque' : '' ?>">
                                                <div class="podio-medalha"><?= $medalhaPodio ?></div>
                                                <?= renderAvatarRankingHtml($jogadorPodio, 'podio-avatar') ?>
                                                <div class="podio-posicao">#<?= $posicaoPodio ?></div>
                                                <h3>
                                                    <?= htmlspecialchars($jogadorPodio['nome']) ?>
                                                    <?php if ($isUsuarioPodio): ?><span class="badge-eu">Você</span><?php endif; ?>
                                                </h3>
                                                <p class="podio-escola">
                                                    <?php
                                                    $linhaPodio = trim((string)($jogadorPodio['escola'] ?? ''));
                                                    if (!empty($jogadorPodio['serie'])) {
                                                        $linhaPodio .= ($linhaPodio !== '' ? ' • ' : '') . $jogadorPodio['serie'];
                                                    }
                                                    echo htmlspecialchars($linhaPodio !== '' ? $linhaPodio : (($jogadorPodio['cidade'] ?? '') ?: 'FOAG'));
                                                    ?>
                                                </p>
                                                <strong class="podio-valor" style="color: <?= htmlspecialchars($ranking['cor']) ?>;">
                                                    <?= htmlspecialchars((string)$jogadorPodio['valor']) ?>
                                                </strong>
                                                <small><?= htmlspecialchars($rotuloValor) ?></small>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if ($atual && $atual['posicao'] > 5): ?>
                                        <?php
                                        $inicioPerto = max(0, $atual['indice'] - 2);
                                        $perto = array_slice($jogadores, $inicioPerto, 5, true);
                                        ?>
                                        <section class="perto-de-voce">
                                            <div class="secao-ranking-titulo">
                                                <div>
                                                    <span>Perto de você</span>
                                                    <h3>Sua disputa mais próxima</h3>
                                                </div>
                                                <i class="fa-solid fa-bullseye"></i>
                                            </div>

                                            <div class="perto-lista">
                                                <?php foreach ($perto as $indicePerto => $jogadorPerto): ?>
                                                    <?php
                                                    $posicaoPerto = $indicePerto + 1;
                                                    $isUsuarioPerto = ($jogadorPerto['codigo_usuario'] ?? '') === $codigoUsuario;
                                                    ?>
                                                    <div class="perto-item <?= $isUsuarioPerto ? 'usuario-destaque' : '' ?>">
                                                        <span class="perto-posicao">#<?= $posicaoPerto ?></span>
                                                        <?= renderAvatarRankingHtml($jogadorPerto, 'avatar-compacto') ?>
                                                        <div class="perto-info">
                                                            <strong>
                                                                <?= htmlspecialchars($jogadorPerto['nome']) ?>
                                                                <?php if ($isUsuarioPerto): ?><span class="badge-eu">Você</span><?php endif; ?>
                                                            </strong>
                                                            <small><?= htmlspecialchars(($jogadorPerto['serie'] ?? '') ?: (($jogadorPerto['cidade'] ?? '') ?: 'Estudante FOAG')) ?></small>
                                                        </div>
                                                        <span class="perto-valor" style="color: <?= htmlspecialchars($ranking['cor']) ?>;">
                                                            <?= htmlspecialchars((string)$jogadorPerto['valor']) ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </section>
                                    <?php endif; ?>

                                    <?php if (count($jogadores) > 3): ?>
                                        <section class="ranking-lista-secao">
                                            <div class="secao-ranking-titulo lista-titulo">
                                                <div>
                                                    <span>Classificação</span>
                                                    <h3>Ranking completo</h3>
                                                </div>
                                            </div>

                                            <div class="ranking-lista">
                                                <?php foreach (array_slice($jogadores, 3, null, true) as $index => $jogador): ?>
                                                    <?php
                                                    $posicao = $index + 1;
                                                    $isUsuario = ($jogador['codigo_usuario'] ?? '') === $codigoUsuario;

                                                    $localizacao = '';
                                                    if (!empty($jogador['cidade']) && !empty($jogador['estado'])) {
                                                        $localizacao = $jogador['cidade'] . ' - ' . $jogador['estado'];
                                                    } elseif (!empty($jogador['estado'])) {
                                                        $localizacao = $jogador['estado'];
                                                    }

                                                    $linhaEscolar = trim((string)($jogador['escola'] ?? ''));
                                                    if (!empty($jogador['serie'])) {
                                                        $linhaEscolar .= ($linhaEscolar !== '' ? ' • ' : '') . $jogador['serie'];
                                                    }
                                                    ?>

                                                    <article class="rank-full-item <?= $isUsuario ? 'usuario-destaque' : '' ?> <?= $posicao > 10 ? 'ranking-extra' : '' ?>">
                                                        <div class="posicao">
                                                            <span class="numero">#<?= $posicao ?></span>
                                                        </div>

                                                        <?= renderAvatarRankingHtml($jogador) ?>

                                                        <div class="info">
                                                            <div class="nome">
                                                                <?= htmlspecialchars($jogador['nome']) ?>
                                                                <?php if ($isUsuario): ?><span class="badge-eu">Você</span><?php endif; ?>
                                                            </div>

                                                            <div class="detalhes">
                                                                <?php if ($linhaEscolar !== ''): ?>
                                                                    <span class="detalhe-escola">
                                                                        <i class="fa-solid fa-school"></i>
                                                                        <?= htmlspecialchars($linhaEscolar) ?>
                                                                    </span>
                                                                <?php endif; ?>

                                                                <?php if ($localizacao !== ''): ?>
                                                                    <span class="tag-local">
                                                                        <i class="fa-solid fa-location-dot"></i>
                                                                        <?= htmlspecialchars($localizacao) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <div class="valor-bloco">
                                                            <strong class="valor" style="color: <?= htmlspecialchars($ranking['cor']) ?>;">
                                                                <?= htmlspecialchars((string)$jogador['valor']) ?>
                                                            </strong>
                                                            <span><?= htmlspecialchars($rotuloValor) ?></span>
                                                        </div>
                                                    </article>
                                                <?php endforeach; ?>
                                            </div>

                                            <?php if (count($jogadores) > 10): ?>
                                                <button type="button" class="btn-ver-ranking-completo" data-toggle-ranking>
                                                    <i class="fa-solid fa-chevron-down"></i>
                                                    Ver ranking completo
                                                </button>
                                            <?php endif; ?>
                                        </section>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
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
     LOGOUT
======================================= -->

<div
    id="share-ranking-modal"
    class="modal share-modal"
>
    <div class="modal-content share-modal-content">
        <button type="button" class="share-modal-close" id="share-modal-close" aria-label="Fechar modal de compartilhamento">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="share-modal-topo">
            <span class="share-modal-kicker">Compartilhar posição</span>
            <h3>Seu destaque no Ranking FOAG</h3>
            <p>Visualize seu card e compartilhe sua conquista.</p>
        </div>

        <div class="share-card-preview share-card--estrelas" id="share-card-preview">
            <div class="share-card-fundo-decor"></div>

            <div class="share-card-brand-row">
                <div class="share-card-brand">
                    <span class="share-card-logo">FOAG</span>
                    <span class="share-card-brand-sub">RANKING</span>
                </div>

                <div class="share-card-posicao-badge" id="share-card-posicao">#1</div>
            </div>

            <div class="share-card-categoria-row">
                <span class="share-card-label">Categoria</span>
                <strong id="share-card-categoria">Estrelas • Nacional</strong>
            </div>

            <div class="share-card-corpo">
                <div class="share-card-avatar" id="share-card-avatar"></div>

                <div class="share-card-info">
                    <strong id="share-card-nome">Seu nome</strong>
                    <span id="share-card-escola">Sua escola</span>
                </div>
            </div>

            <div class="share-card-valor-box">
                <span id="share-card-valor-label">Suas estrelas</span>
                <strong id="share-card-valor">0</strong>
            </div>

            <div class="share-card-rodape">
                <span>Estude • evolua • suba no ranking</span>
                <i class="fa-solid fa-trophy"></i>
            </div>
        </div>

        <div class="share-modal-texto">
            <label for="share-texto-preview">Texto para compartilhar</label>
            <textarea id="share-texto-preview" readonly></textarea>
        </div>

        <div class="share-modal-actions">
            <button type="button" class="btn-share-copy" id="share-copy-btn">
                <i class="fa-solid fa-copy"></i>
                Copiar texto
            </button>

            <button type="button" class="btn-share-native" id="share-native-btn">
                <i class="fa-solid fa-paper-plane"></i>
                Compartilhar imagem
            </button>

            <button type="button" class="btn-share-download" id="share-download-btn">
                <i class="fa-solid fa-download"></i>
                Baixar imagem
            </button>

            <button type="button" class="btn-share-cancel" id="share-cancel-btn">
                Fechar
            </button>
        </div>
    </div>
</div>

<div
    id="logout-modal"
    class="modal"
>

    <div class="modal-content">

        <h3>Ah... já vai?</h3>

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

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>

const rankingsData =
    <?= json_encode(
        $rankings,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    ) ?>;

const codigoUsuarioAtual =
    <?= json_encode((string)$codigoUsuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

const nomesNivelCurto = {
    nacional: 'Nacional',
    estadual: 'Estado',
    municipal: 'Cidade',
    escola: 'Escola',
    serie: 'Minha série'
};

const nomesCategoriaCurto = {
    estrelas: 'Estrelas',
    pomodoro: 'Foco',
    notas: 'Desempenho'
};

document.addEventListener('DOMContentLoaded', function () {
    const abasNivel = document.querySelectorAll('.aba-nivel');
    const menuItems = document.querySelectorAll('.rank-menu-lateral .menu-item');
    const categorias = document.querySelectorAll('.rank-full');
    const escolaSubfiltros = document.getElementById('escolaSubfiltros');
    const escolaScopes = document.querySelectorAll('.escola-scope');
    const headerMinhaPosicao = document.getElementById('headerMinhaPosicao');
    const headerMinhaPosicaoLegenda = document.getElementById('headerMinhaPosicaoLegenda');
    const headerResumoLabel = document.getElementById('headerResumoLabel');
    const headerResumoValor = document.getElementById('headerResumoValor');
    const headerResumoTexto = document.getElementById('headerResumoTexto');
    const headerResumoIcone = document.getElementById('headerResumoIcone');
    const rankingTituloPill = document.getElementById('rankingTituloPill');
    const shareModal = document.getElementById('share-ranking-modal');
    const shareModalClose = document.getElementById('share-modal-close');
    const shareCancelBtn = document.getElementById('share-cancel-btn');
    const shareCopyBtn = document.getElementById('share-copy-btn');
    const shareNativeBtn = document.getElementById('share-native-btn');
    const shareDownloadBtn = document.getElementById('share-download-btn');
    const shareTextoPreview = document.getElementById('share-texto-preview');
    const shareCardPreview = document.getElementById('share-card-preview');
    const shareCardCategoria = document.getElementById('share-card-categoria');
    const shareCardPosicao = document.getElementById('share-card-posicao');
    const shareCardAvatar = document.getElementById('share-card-avatar');
    const shareCardNome = document.getElementById('share-card-nome');
    const shareCardEscola = document.getElementById('share-card-escola');
    const shareCardValorLabel = document.getElementById('share-card-valor-label');
    const shareCardValor = document.getElementById('share-card-valor');

    const resumoCategoria = {
        estrelas: {
            label: 'Suas estrelas',
            icon: 'fa-star',
            texto: 'Total acumulado nesta categoria',
            shareClass: 'share-card--estrelas'
        },
        pomodoro: {
            label: 'Seu tempo de foco',
            icon: 'fa-clock',
            texto: 'Tempo acumulado em sessões focadas',
            shareClass: 'share-card--pomodoro'
        },
        notas: {
            label: 'Sua média',
            icon: 'fa-book-open',
            texto: 'Desempenho escolar cadastrado',
            shareClass: 'share-card--notas'
        }
    };

    let nivelAtual = 'nacional';
    let escolaScopeAtual = 'escola';
    let categoriaAtual = 'estrelas';
    let currentShareText = '';
    let currentShareImageName = 'ranking-foag.png';

    function nivelEfetivo() {
        return nivelAtual === 'escola'
            ? escolaScopeAtual
            : nivelAtual;
    }

    function jogadoresDa(categoria, nivel) {
        return rankingsData?.[categoria]?.niveis?.[nivel]?.jogadores || [];
    }

    function encontrarUsuario(jogadores) {
        const indice = jogadores.findIndex(
            jogador => String(jogador.codigo_usuario) === String(codigoUsuarioAtual)
        );

        return indice >= 0
            ? { indice, jogador: jogadores[indice], posicao: indice + 1 }
            : null;
    }

    function obterResumoUsuario(categoria, nivel) {
        const listaAtual = jogadoresDa(categoria, nivel);
        const atualNoNivel = encontrarUsuario(listaAtual);

        if (atualNoNivel) {
            return atualNoNivel;
        }

        if (nivel !== 'nacional') {
            const listaNacional = jogadoresDa(categoria, 'nacional');
            return encontrarUsuario(listaNacional);
        }

        return null;
    }

    function normalizarAjusteMolduraRankingJs(ajuste) {
        const padrao = {
            moldura_escala: 1.28,
            moldura_x: 0,
            moldura_y: 0,
            foto_escala: 1,
            foto_x: 0,
            foto_y: 0
        };

        if (!ajuste || typeof ajuste !== 'object') {
            return padrao;
        }

        Object.keys(padrao).forEach(chave => {
            const valor = Number(ajuste[chave]);
            if (!Number.isNaN(valor)) {
                padrao[chave] = valor;
            }
        });

        return padrao;
    }

    function deslocamentoRankingPercentualJs(valorPx) {
        const numero = Number(valorPx);
        if (Number.isNaN(numero)) {
            return 0;
        }
        return (numero / 132) * 100;
    }

    function renderAvatarCompartilhar(jogador) {
        if (!jogador) {
            return '<div class="avatar avatar-foto share-avatar"><div class="avatar-stage"><div class="avatar-foto-recorte"><span class="avatar-fallback" style="display:flex"><i class="fa-solid fa-user"></i></span></div></div></div>';
        }

        const moldura = jogador.moldura && typeof jogador.moldura === 'object'
            ? jogador.moldura
            : null;

        const ajuste = normalizarAjusteMolduraRankingJs(moldura?.ajuste_perfil || {});
        const temMoldura = !!(moldura && moldura.imagem);
        const style = `
            --rank-moldura-escala: ${ajuste.moldura_escala};
            --rank-moldura-x: ${deslocamentoRankingPercentualJs(ajuste.moldura_x)}%;
            --rank-moldura-y: ${deslocamentoRankingPercentualJs(ajuste.moldura_y)}%;
            --rank-foto-escala: ${ajuste.foto_escala};
            --rank-foto-x: ${deslocamentoRankingPercentualJs(ajuste.foto_x)}%;
            --rank-foto-y: ${deslocamentoRankingPercentualJs(ajuste.foto_y)}%;
        `;

        const foto = jogador.foto || '';
        const nome = jogador.nome || 'Estudante';

        return `
            <div class="avatar avatar-foto ${temMoldura ? 'com-moldura' : ''} share-avatar">
                <div class="avatar-stage ${temMoldura ? 'tem-moldura' : ''}" style="${style}">
                    <div class="avatar-foto-recorte">
                        <img class="avatar-foto-img" src="${foto}" alt="Foto de perfil de ${nome}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <span class="avatar-fallback" aria-hidden="true">
                            <i class="fa-solid fa-user"></i>
                        </span>
                    </div>
                    ${temMoldura ? `<img class="avatar-moldura-img" src="${moldura.imagem}" alt="" aria-hidden="true">` : ''}
                </div>
            </div>
        `;
    }

    function montarTextoCompartilhamento(categoria, nivel, atual) {
        if (!atual || !atual.jogador) {
            return '';
        }

        const nomeCategoria = nomesCategoriaCurto[categoria] || categoria;
        const nomeNivel = nomesNivelCurto[nivel] || nivel;
        return `🏆 Estou em ${atual.posicao}º lugar no Ranking FOAG — ${nomeCategoria} / ${nomeNivel}! Minha marca atual é ${atual.jogador.valor}.`;
    }

    function abrirModalCompartilhar(categoria, nivel, atual) {
        if (!shareModal || !atual || !atual.jogador) return;

        const configResumo = resumoCategoria[categoria] || resumoCategoria.estrelas;
        const nomeCategoria = nomesCategoriaCurto[categoria] || categoria;
        const nomeNivel = nomesNivelCurto[nivel] || nivel;
        const jogador = atual.jogador;
        currentShareText = montarTextoCompartilhamento(categoria, nivel, atual);
        currentShareImageName = `ranking-foag-${categoria}-${nivel}.png`;

        if (shareCardPreview) {
            shareCardPreview.classList.remove('share-card--estrelas', 'share-card--pomodoro', 'share-card--notas');
            shareCardPreview.classList.add(configResumo.shareClass || 'share-card--estrelas');
        }

        if (shareCardCategoria) {
            shareCardCategoria.textContent = `${nomeCategoria} • ${nomeNivel}`;
        }

        if (shareCardPosicao) {
            shareCardPosicao.textContent = `#${atual.posicao}`;
        }

        if (shareCardAvatar) {
            shareCardAvatar.innerHTML = renderAvatarCompartilhar(jogador);
        }

        if (shareCardNome) {
            shareCardNome.textContent = jogador.nome || 'Estudante';
        }

        if (shareCardEscola) {
            const detalhes = [jogador.escola || '', jogador.serie || ''].filter(Boolean).join(' • ');
            shareCardEscola.textContent = detalhes || 'Ranking FOAG';
        }

        if (shareCardValorLabel) {
            shareCardValorLabel.textContent = configResumo.label;
        }

        if (shareCardValor) {
            shareCardValor.innerHTML = `<i class="fa-solid ${configResumo.icon}"></i><span>${jogador.valor || '—'}</span>`;
        }

        if (shareTextoPreview) {
            shareTextoPreview.value = currentShareText;
        }

        if (shareNativeBtn) {
            shareNativeBtn.style.display = 'inline-flex';
            shareNativeBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Compartilhar imagem';
        }

        if (shareDownloadBtn) {
            shareDownloadBtn.style.display = 'inline-flex';
        }

        shareModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    async function gerarImagemCompartilhamento() {
        if (!shareCardPreview || typeof html2canvas === 'undefined') {
            throw new Error('html2canvas não disponível');
        }

        if (document.fonts?.ready) {
            await document.fonts.ready;
        }

        const clone = shareCardPreview.cloneNode(true);
        clone.removeAttribute('id');
        clone.classList.add('share-card-render-target');
        clone.style.position = 'fixed';
        clone.style.left = '-9999px';
        clone.style.top = '0';
        clone.style.width = '432px';
        clone.style.height = '432px';
        clone.style.maxWidth = 'none';
        clone.style.margin = '0';
        clone.style.transform = 'none';

        document.body.appendChild(clone);

        const imagens = Array.from(clone.querySelectorAll('img'));
        await Promise.all(
            imagens.map(img => {
                if (img.complete) return Promise.resolve();
                return new Promise(resolve => {
                    img.addEventListener('load', resolve, { once: true });
                    img.addEventListener('error', resolve, { once: true });
                });
            })
        );

        try {
            const canvas = await html2canvas(clone, {
                backgroundColor: null,
                scale: 2.5,
                width: 432,
                height: 432,
                useCORS: true,
                logging: false
            });

            return await new Promise((resolve, reject) => {
                canvas.toBlob(blob => {
                    if (!blob) {
                        reject(new Error('Não foi possível gerar a imagem.'));
                        return;
                    }
                    resolve(blob);
                }, 'image/png');
            });
        } finally {
            clone.remove();
        }
    }

    function baixarBlobImagem(blob, nomeArquivo) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = nomeArquivo || 'ranking-foag.png';
        document.body.appendChild(link);
        link.click();
        link.remove();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }

    function fecharModalCompartilhar() {
        if (!shareModal) return;
        shareModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function formatarDiferenca(categoria, diferenca) {
        if (categoria === 'estrelas') {
            return `${Math.max(1, Math.ceil(diferenca))} ⭐ para subir uma posição`;
        }

        if (categoria === 'pomodoro') {
            const minutos = Math.max(1, Math.ceil(diferenca));
            const horas = Math.floor(minutos / 60);
            const resto = minutos % 60;
            const texto = horas > 0
                ? `${horas}h${resto ? ` ${resto}min` : ''}`
                : `${resto}min`;
            return `${texto} de foco para subir uma posição`;
        }

        const pontos = Math.max(0.1, diferenca).toFixed(1).replace('.', ',');
        return `${pontos} ponto(s) de média para subir uma posição`;
    }

    function atualizarProgresso(categoriaEl, categoriaKey, nivel) {
        const progresso = categoriaEl.querySelector('[data-progresso-proxima]');
        if (!progresso) return;

        const jogadores = jogadoresDa(categoriaKey, nivel);
        const atual = encontrarUsuario(jogadores);

        if (!atual) {
            progresso.textContent = 'Participe das atividades do FOAG para entrar neste ranking.';
            return;
        }

        if (atual.posicao === 1) {
            progresso.textContent = 'Você está em 1º lugar. Continue mantendo sua posição!';
            return;
        }

        const acima = jogadores[atual.indice - 1];
        const diferenca = Math.max(
            0,
            Number(acima?.valor_bruto || 0) - Number(atual.jogador?.valor_bruto || 0)
        );

        progresso.textContent = diferenca <= 0
            ? 'Você está muito perto de subir uma posição.'
            : formatarDiferenca(categoriaKey, diferenca);
    }

    function atualizarCabecalho() {
        const nivel = nivelEfetivo();
        const jogadores = jogadoresDa(categoriaAtual, nivel);
        const atual = encontrarUsuario(jogadores);
        const resumoAtual = obterResumoUsuario(categoriaAtual, nivel);
        const configResumo = resumoCategoria[categoriaAtual] || resumoCategoria.estrelas;
        const nomeNivel = nomesNivelCurto[nivel] || nivel;
        const nomeCategoria = nomesCategoriaCurto[categoriaAtual] || categoriaAtual;

        if (headerMinhaPosicao) {
            headerMinhaPosicao.textContent = atual ? `#${atual.posicao}` : '—';
        }

        if (headerMinhaPosicaoLegenda) {
            headerMinhaPosicaoLegenda.textContent =
                `${nomeNivel} • ${nomeCategoria}`;
        }

        if (headerResumoLabel) {
            headerResumoLabel.textContent = configResumo.label;
        }

        if (headerResumoValor) {
            headerResumoValor.textContent = resumoAtual?.jogador?.valor ?? '—';
        }

        if (headerResumoTexto) {
            headerResumoTexto.textContent = `${nomeCategoria} • ${nomeNivel}`;
        }

        if (headerResumoIcone) {
            headerResumoIcone.className = `fa-solid ${configResumo.icon}`;
        }

        if (rankingTituloPill) {
            rankingTituloPill.innerHTML = `
                <i class="fa-solid ${configResumo.icon}"></i>
                <span>Categoria ativa: ${nomeCategoria}</span>
            `;
        }
    }

    function atualizarNivel(nivel) {
        nivelAtual = nivel;
        const efetivo = nivelEfetivo();

        abasNivel.forEach(aba => {
            aba.classList.toggle('active', aba.dataset.nivel === nivelAtual);
        });

        if (escolaSubfiltros) {
            escolaSubfiltros.hidden = nivelAtual !== 'escola';
        }

        categorias.forEach(categoriaEl => {
            const categoriaKey = categoriaEl.dataset.categoria;

            categoriaEl.querySelectorAll('.nivel-conteudo').forEach(conteudo => {
                conteudo.style.display = conteudo.dataset.nivel === efetivo ? 'block' : 'none';
            });

            const jogadores = jogadoresDa(categoriaKey, efetivo);
            const total = categoriaEl.querySelector('.total-jogadores');
            const top1 = categoriaEl.querySelector('.top1-nome');

            if (total) total.textContent = jogadores.length;
            if (top1) top1.textContent = jogadores.length ? jogadores[0].nome : '—';

            atualizarProgresso(categoriaEl, categoriaKey, efetivo);
        });

        menuItems.forEach(item => {
            const categoriaKey = item.dataset.categoria;
            const badge = item.querySelector('.item-badge');
            if (badge) badge.textContent = jogadoresDa(categoriaKey, efetivo).length;
        });

        atualizarCabecalho();
    }

    abasNivel.forEach(aba => {
        aba.addEventListener('click', function () {
            atualizarNivel(aba.dataset.nivel || 'nacional');
        });
    });

    escolaScopes.forEach(botao => {
        botao.addEventListener('click', function () {
            escolaScopes.forEach(item => item.classList.remove('active'));
            botao.classList.add('active');
            escolaScopeAtual = botao.dataset.schoolScope || 'escola';
            atualizarNivel('escola');
        });
    });

    menuItems.forEach(item => {
        item.addEventListener('click', function () {
            menuItems.forEach(menu => menu.classList.remove('active'));
            item.classList.add('active');

            categoriaAtual = item.dataset.categoria || 'estrelas';

            categorias.forEach(rank => rank.classList.add('hidden'));
            const selecionado = document.getElementById(`rank-${categoriaAtual}`);

            if (selecionado) {
                selecionado.classList.remove('hidden');
                selecionado.style.animation = 'none';
                void selecionado.offsetHeight;
                selecionado.style.animation = 'slideIn 0.35s ease forwards';
            }

            atualizarNivel(nivelAtual);
        });
    });

    document.querySelectorAll('[data-share-ranking]').forEach(botao => {
        botao.addEventListener('click', function () {
            const categoria = botao.dataset.categoria || categoriaAtual;
            const nivel = nivelEfetivo();
            const jogadores = jogadoresDa(categoria, nivel);
            const atual = encontrarUsuario(jogadores);

            if (!atual) return;

            abrirModalCompartilhar(categoria, nivel, atual);
        });
    });

    shareModalClose?.addEventListener('click', fecharModalCompartilhar);
    shareCancelBtn?.addEventListener('click', fecharModalCompartilhar);

    shareModal?.addEventListener('click', function (event) {
        if (event.target === shareModal) {
            fecharModalCompartilhar();
        }
    });

    shareCopyBtn?.addEventListener('click', async function () {
        if (!currentShareText) return;

        try {
            if (navigator.clipboard) {
                await navigator.clipboard.writeText(currentShareText);
            } else if (shareTextoPreview) {
                shareTextoPreview.focus();
                shareTextoPreview.select();
                document.execCommand('copy');
            }

            const original = shareCopyBtn.innerHTML;
            shareCopyBtn.innerHTML = '<i class="fa-solid fa-check"></i> Copiado!';
            setTimeout(() => { shareCopyBtn.innerHTML = original; }, 1800);
        } catch (erro) {
            console.log('Não foi possível copiar o texto.', erro);
        }
    });

    shareDownloadBtn?.addEventListener('click', async function () {
        if (!shareCardPreview) return;

        const original = shareDownloadBtn.innerHTML;

        try {
            shareDownloadBtn.disabled = true;
            shareDownloadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando...';

            const blob = await gerarImagemCompartilhamento();
            baixarBlobImagem(blob, currentShareImageName);

            shareDownloadBtn.innerHTML = '<i class="fa-solid fa-check"></i> Imagem baixada';
            setTimeout(() => {
                shareDownloadBtn.innerHTML = original;
            }, 1800);
        } catch (erro) {
            console.log('Não foi possível baixar a imagem.', erro);
            shareDownloadBtn.innerHTML = original;
        } finally {
            shareDownloadBtn.disabled = false;
        }
    });

    shareNativeBtn?.addEventListener('click', async function () {
        if (!shareCardPreview) return;

        const original = shareNativeBtn.innerHTML;

        try {
            shareNativeBtn.disabled = true;
            shareNativeBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando imagem...';

            const blob = await gerarImagemCompartilhamento();
            const arquivo = new File([blob], currentShareImageName, { type: 'image/png' });

            if (navigator.share && navigator.canShare && navigator.canShare({ files: [arquivo] })) {
                await navigator.share({
                    title: 'Ranking FOAG',
                    text: currentShareText,
                    files: [arquivo]
                });
            } else {
                baixarBlobImagem(blob, currentShareImageName);
            }
        } catch (erro) {
            console.log('Não foi possível compartilhar a imagem.', erro);
        } finally {
            shareNativeBtn.disabled = false;
            shareNativeBtn.innerHTML = original;
        }
    });

    document.querySelectorAll('[data-toggle-ranking]').forEach(botao => {
        botao.addEventListener('click', function () {
            const secao = botao.closest('.ranking-lista-secao');
            const aberta = secao?.classList.toggle('ranking-aberto');

            botao.innerHTML = aberta
                ? '<i class="fa-solid fa-chevron-up"></i> Mostrar apenas Top 10'
                : '<i class="fa-solid fa-chevron-down"></i> Ver ranking completo';
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && shareModal?.style.display === 'flex') {
            fecharModalCompartilhar();
        }
    });

    const perfilIcon = document.getElementById('icon-perfil');
    perfilIcon?.addEventListener('click', function () {
        window.location.href = '../perfil/perfil.php';
    });

    const logoutModal = document.getElementById('logout-modal');
    const iconSair = document.getElementById('icon-sair');
    const confirmarLogout = document.getElementById('confirm-logout');
    const cancelarLogout = document.getElementById('cancel-logout');

    iconSair?.addEventListener('click', function () {
        if (logoutModal) logoutModal.style.display = 'flex';
    });

    confirmarLogout?.addEventListener('click', function () {
        window.location.href = '../login/logout.php';
    });

    cancelarLogout?.addEventListener('click', function () {
        if (logoutModal) logoutModal.style.display = 'none';
    });

    logoutModal?.addEventListener('click', function (evento) {
        if (evento.target === logoutModal) logoutModal.style.display = 'none';
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && logoutModal?.style.display === 'flex') {
            logoutModal.style.display = 'none';
        }
    });

    atualizarNivel('nacional');
});

</script>


<script src="../configuracoes/aparencia.js?v=5"></script>
<script src="../configuracoes/acessibilidade.js?v=25" defer></script>

</body>
</html>