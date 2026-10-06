<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// ======================================
// SISTEMA DE ESTRELAS
// ======================================

require_once
    __DIR__ .
    '/../estrelas/adicionar_estrelas.php';

$recompensaBoletim = [
    'estrelas' => 0,
    'motivos' => []
];

// ======================================
// LOGIN OBRIGATÓRIO
// ======================================

if (empty($_SESSION['codigo_usuario'])) {
    header("Location: ../login/index.php");
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

// ======================================
// ARQUIVOS JSON DO USUÁRIO
// ======================================

$baseJsonDir = __DIR__ . '/../json/usuarios';
$pastaUsuario = $baseJsonDir . '/' . $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    exit("Pasta do usuário não encontrada.");
}

$arquivoBoletim = $pastaUsuario . '/notas.json';
$arquivoMaterias = $pastaUsuario . '/materias.json';

// ======================================
// FUNÇÕES AUXILIARES DE JSON / MATÉRIAS
// ======================================

function salvarJsonArquivo($arquivo, $dados)
{
    return file_put_contents(
        $arquivo,
        json_encode(
            $dados,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ),
        LOCK_EX
    );
}

function ehListaNumerica($array)
{
    if (!is_array($array)) {
        return false;
    }

    if (count($array) === 0) {
        return true;
    }

    return array_keys($array) === range(0, count($array) - 1);
}

function chaveNomeMateria($nome)
{
    $nome = trim((string)$nome);

    if (function_exists('mb_strtolower')) {
        return mb_strtolower($nome, 'UTF-8');
    }

    return strtolower($nome);
}

function gerarIdMateria()
{
    try {
        return 'mat_' . bin2hex(random_bytes(6));
    } catch (Throwable $e) {
        return 'mat_' . uniqid();
    }
}

function notasVazias()
{
    return [
        1 => null,
        2 => null,
        3 => null,
        4 => null
    ];
}

function normalizarNotasLinha($notas)
{
    $resultado = notasVazias();

    if (!is_array($notas)) {
        return $resultado;
    }

    for ($i = 1; $i <= 4; $i++) {
        if (array_key_exists($i, $notas)) {
            $resultado[$i] = $notas[$i];
        } elseif (array_key_exists((string)$i, $notas)) {
            $resultado[$i] = $notas[(string)$i];
        }
    }

    return $resultado;
}

function indiceMateriaPorId($materias, $id)
{
    $id = (string)$id;

    foreach ($materias as $indice => $materia) {
        if (
            is_array($materia) &&
            isset($materia['id']) &&
            (string)$materia['id'] === $id
        ) {
            return $indice;
        }
    }

    return -1;
}

function indiceMateriaPorNome($materias, $nome)
{
    $chave = chaveNomeMateria($nome);

    if ($chave === '') {
        return -1;
    }

    foreach ($materias as $indice => $materia) {
        if (!is_array($materia)) {
            continue;
        }

        if (
            chaveNomeMateria($materia['nome'] ?? '') ===
            $chave
        ) {
            return $indice;
        }
    }

    return -1;
}

function criarMateriaPadraoBoletim($nome)
{
    return [
        'id'    => gerarIdMateria(),
        'nome'  => trim((string)$nome),

        // Criada pelo Boletim:
        // cor neutra + ícone de interrogação.
        // Depois o usuário personaliza em Estudos.
        'cor'   => '#94a3b8',
        'icone' => 'fa-circle-question'
    ];
}

/**
 * Faz o Boletim usar a lista oficial de materias.json.
 *
 * - preserva notas existentes pelo ID;
 * - migra estrutura antiga por nome;
 * - adiciona automaticamente matérias criadas em Estudos;
 * - remove da tabela matérias que já não existem em materias.json.
 */
function sincronizarPeriodoComMaterias(&$periodo, $materiasGlobais)
{
    if (!is_array($periodo)) {
        $periodo = [];
    }

    $nomesAntigos = (
        isset($periodo['materias']) &&
        is_array($periodo['materias'])
    )
        ? $periodo['materias']
        : [];

    $idsAntigos = (
        isset($periodo['materia_ids']) &&
        is_array($periodo['materia_ids'])
    )
        ? $periodo['materia_ids']
        : [];

    $notasAntigas = (
        isset($periodo['notas']) &&
        is_array($periodo['notas'])
    )
        ? $periodo['notas']
        : [];

    $notasPorId = [];

    $totalLinhas = max(
        count($nomesAntigos),
        count($idsAntigos),
        count($notasAntigas)
    );

    for ($i = 0; $i < $totalLinhas; $i++) {
        $id = trim((string)($idsAntigos[$i] ?? ''));
        $nome = trim((string)($nomesAntigos[$i] ?? ''));

        if (
            $id === '' ||
            indiceMateriaPorId($materiasGlobais, $id) < 0
        ) {
            $indiceNome =
                indiceMateriaPorNome(
                    $materiasGlobais,
                    $nome
                );

            if ($indiceNome >= 0) {
                $id =
                    (string)$materiasGlobais[$indiceNome]['id'];
            }
        }

        if ($id !== '') {
            $notasPorId[$id] =
                normalizarNotasLinha(
                    $notasAntigas[$i] ?? []
                );
        }
    }

    $novosNomes = [];
    $novosIds   = [];
    $novasNotas = [];

    foreach ($materiasGlobais as $materia) {
        if (
            !is_array($materia) ||
            trim((string)($materia['nome'] ?? '')) === ''
        ) {
            continue;
        }

        $id =
            trim((string)($materia['id'] ?? ''));

        if ($id === '') {
            continue;
        }

        $novosNomes[] =
            trim((string)$materia['nome']);

        $novosIds[] =
            $id;

        $novasNotas[] =
            $notasPorId[$id] ??
            notasVazias();
    }

    $periodo['materias']   = $novosNomes;
    $periodo['materia_ids'] = $novosIds;
    $periodo['notas']      = $novasNotas;
}

// ======================================
// CARREGAR materias.json
// ======================================

$materiasData = [
    'materias' => []
];

if (file_exists($arquivoMaterias)) {
    $materiasLidas =
        json_decode(
            file_get_contents($arquivoMaterias),
            true
        );

    if (is_array($materiasLidas)) {
        // Compatibilidade caso o JSON antigo seja uma lista direta.
        if (
            isset($materiasLidas['materias']) &&
            is_array($materiasLidas['materias'])
        ) {
            $materiasData =
                $materiasLidas;
        } elseif (ehListaNumerica($materiasLidas)) {
            $materiasData = [
                'materias' => $materiasLidas
            ];
        }
    }
}

$materiasNormalizadas = [];
$idsUsados = [];
$nomesUsados = [];

foreach ($materiasData['materias'] as $materia) {
    if (is_string($materia)) {
        $materia = [
            'nome' => $materia
        ];
    }

    if (!is_array($materia)) {
        continue;
    }

    $nome =
        trim(
            (string)($materia['nome'] ?? '')
        );

    if ($nome === '') {
        continue;
    }

    $chaveNome =
        chaveNomeMateria($nome);

    // Evita matérias duplicadas pelo nome.
    if (isset($nomesUsados[$chaveNome])) {
        continue;
    }

    $id =
        trim(
            (string)($materia['id'] ?? '')
        );

    if (
        $id === '' ||
        isset($idsUsados[$id])
    ) {
        $id = gerarIdMateria();
    }

    $materia['id'] = $id;
    $materia['nome'] = $nome;

    if (
        !isset($materia['cor']) ||
        trim((string)$materia['cor']) === ''
    ) {
        $materia['cor'] = '#38a5ff';
    }

    if (
        !isset($materia['icone']) ||
        trim((string)$materia['icone']) === ''
    ) {
        $materia['icone'] = 'fa-book';
    }

    $materiasNormalizadas[] = $materia;
    $idsUsados[$id] = true;
    $nomesUsados[$chaveNome] = true;
}

$materiasData['materias'] =
    $materiasNormalizadas;

// ======================================
// ESTRUTURA PADRÃO DO BOLETIM
// ======================================

$defaultData = [
    'nota_maxima'     => 10,
    'media_aprovacao' => 6,
    'tipo_curso'      => 'escola',
    'pesos'           => [
        1 => 1,
        2 => 1,
        3 => 1,
        4 => 1
    ],
    'periodos'        => [
        'Padrão' => [
            'materias'    => [],
            'materia_ids' => [],
            'notas'       => []
        ]
    ],
    'periodo_atual'   => 'Padrão',
];

// ======================================
// CARREGAR notas.json
// ======================================

if (file_exists($arquivoBoletim)) {
    $data =
        json_decode(
            file_get_contents($arquivoBoletim),
            true
        );

    if (!is_array($data)) {
        $data = $defaultData;
    }
} else {
    // Primeira vez: tenta migrar dados antigos da sessão.
    $data = $defaultData;

    if (isset($_SESSION['nota_maxima'])) {
        $data['nota_maxima'] =
            (float)$_SESSION['nota_maxima'];
    }

    if (isset($_SESSION['media_aprovacao'])) {
        $data['media_aprovacao'] =
            (float)$_SESSION['media_aprovacao'];
    }

    if (
        isset($_SESSION['tipo_curso']) &&
        in_array(
            $_SESSION['tipo_curso'],
            ['escola', 'faculdade'],
            true
        )
    ) {
        $data['tipo_curso'] =
            $_SESSION['tipo_curso'];
    }

    if (
        isset($_SESSION['pesos']) &&
        is_array($_SESSION['pesos'])
    ) {
        $data['pesos'] =
            $data['pesos'] +
            $_SESSION['pesos'];
    }

    if (
        isset($_SESSION['periodos']) &&
        is_array($_SESSION['periodos'])
    ) {
        $data['periodos'] =
            $_SESSION['periodos'];
    } else {
        $materiasOld =
            isset($_SESSION['materias'])
                ? $_SESSION['materias']
                : [];

        $notasOld =
            isset($_SESSION['notas'])
                ? $_SESSION['notas']
                : [];

        $data['periodos'] = [
            'Padrão' => [
                'materias'    => $materiasOld,
                'materia_ids' => [],
                'notas'       => $notasOld
            ]
        ];
    }

    if (isset($_SESSION['periodo_atual'])) {
        $data['periodo_atual'] =
            (string)$_SESSION['periodo_atual'];
    }
}

// ======================================
// GARANTIR CAMPOS DO notas.json
// ======================================

if (!isset($data['nota_maxima'])) {
    $data['nota_maxima'] = 10;
}

if (!isset($data['media_aprovacao'])) {
    $data['media_aprovacao'] = 6;
}

if (
    !isset($data['tipo_curso']) ||
    !in_array(
        $data['tipo_curso'],
        ['escola', 'faculdade'],
        true
    )
) {
    $data['tipo_curso'] = 'escola';
}

if (
    !isset($data['pesos']) ||
    !is_array($data['pesos'])
) {
    $data['pesos'] = [
        1 => 1,
        2 => 1,
        3 => 1,
        4 => 1
    ];
}

for ($i = 1; $i <= 4; $i++) {
    if (!isset($data['pesos'][$i])) {
        $data['pesos'][$i] = 1;
    }
}

if (
    !isset($data['periodos']) ||
    !is_array($data['periodos'])
) {
    $data['periodos'] = [
        'Padrão' => [
            'materias'    => [],
            'materia_ids' => [],
            'notas'       => []
        ]
    ];
}

if (
    !isset($data['periodo_atual']) ||
    !isset(
        $data['periodos'][$data['periodo_atual']]
    )
) {
    $data['periodo_atual'] = 'Padrão';

    if (!isset($data['periodos']['Padrão'])) {
        $data['periodos']['Padrão'] = [
            'materias'    => [],
            'materia_ids' => [],
            'notas'       => []
        ];
    }
}

// ======================================
// MIGRAR MATÉRIAS ANTIGAS DO BOLETIM
// PARA materias.json
// ======================================

foreach ($data['periodos'] as &$periodoMigracao) {
    if (!is_array($periodoMigracao)) {
        $periodoMigracao = [
            'materias'    => [],
            'materia_ids' => [],
            'notas'       => []
        ];
    }

    if (
        !isset($periodoMigracao['materias']) ||
        !is_array($periodoMigracao['materias'])
    ) {
        $periodoMigracao['materias'] = [];
    }

    if (
        !isset($periodoMigracao['materia_ids']) ||
        !is_array($periodoMigracao['materia_ids'])
    ) {
        $periodoMigracao['materia_ids'] = [];
    }

    if (
        !isset($periodoMigracao['notas']) ||
        !is_array($periodoMigracao['notas'])
    ) {
        $periodoMigracao['notas'] = [];
    }

    foreach (
        $periodoMigracao['materias']
        as $indiceMateriaAntiga => $nomeMateriaAntiga
    ) {
        $nomeMateriaAntiga =
            trim(
                (string)$nomeMateriaAntiga
            );

        if ($nomeMateriaAntiga === '') {
            continue;
        }

        $idMateriaAntiga =
            trim(
                (string)(
                    $periodoMigracao['materia_ids']
                        [$indiceMateriaAntiga] ??
                    ''
                )
            );

        /*
         * Só importa para materias.json quando a linha
         * ainda NÃO tem ID. Isso identifica a estrutura
         * antiga do Boletim.
         *
         * Se já existe ID e ele sumiu de materias.json,
         * significa que a matéria foi excluída em Estudos
         * e não deve ser recriada aqui.
         */
        if ($idMateriaAntiga !== '') {
            continue;
        }

        if (
            indiceMateriaPorNome(
                $materiasData['materias'],
                $nomeMateriaAntiga
            ) < 0
        ) {
            $materiasData['materias'][] =
                criarMateriaPadraoBoletim(
                    $nomeMateriaAntiga
                );
        }
    }
}

unset($periodoMigracao);

// ======================================
// SINCRONIZAR PERÍODO ATUAL COM Estudos
// ======================================

$periodoAtual =
    $data['periodo_atual'];

if (!isset($data['periodos'][$periodoAtual])) {
    $data['periodos'][$periodoAtual] = [
        'materias'    => [],
        'materia_ids' => [],
        'notas'       => []
    ];
}

sincronizarPeriodoComMaterias(
    $data['periodos'][$periodoAtual],
    $materiasData['materias']
);

// Salva possíveis migrações antes do POST.
salvarJsonArquivo(
    $arquivoMaterias,
    $materiasData
);

salvarJsonArquivo(
    $arquivoBoletim,
    $data
);

// ======================================
// FUNÇÕES DO BOLETIM
// ======================================

function calcularMediaEStatus(
    $notas,
    $mediaAprovacao,
    $pesos
) {
    $somaNP = 0;
    $somaW  = 0;

    for ($i = 1; $i <= 4; $i++) {
        $nota =
            isset($notas[$i])
                ? $notas[$i]
                : null;

        $w =
            isset($pesos[$i])
                ? $pesos[$i]
                : 1;

        if (
            $nota !== null &&
            $nota !== '' &&
            $w > 0
        ) {
            $nota = (float)$nota;

            $somaNP +=
                $nota * $w;

            $somaW += $w;
        }
    }

    if ($somaW == 0) {
        return [
            'media'   => 0,
            'status'  => '-',
            'precisa' => null
        ];
    }

    $media =
        $somaNP / $somaW;

    if ($media >= $mediaAprovacao) {
        $status = 'Aprovado';
    } elseif (
        $media >=
        $mediaAprovacao * 0.5
    ) {
        $status = 'Recuperação';
    } else {
        $status = 'Reprovado';
    }

    return [
        'media'   => $media,
        'status'  => $status,
        'precisa' => null
    ];
}


function calcularProgressoAnualEscola(
    $notas,
    $mediaAprovacao,
    $notaMaxima
) {
    $totalPontos = 0.0;
    $preenchidas = 0;
    $totalPeriodos = 4;

    for ($i = 1; $i <= $totalPeriodos; $i++) {
        $valor = $notas[$i] ?? $notas[(string)$i] ?? null;
        if ($valor === null || $valor === '') {
            continue;
        }
        $totalPontos += (float)$valor;
        $preenchidas++;
    }

    $metaTotal = (float)$mediaAprovacao * $totalPeriodos;
    $restantes = $totalPeriodos - $preenchidas;
    $faltam = max(0, $metaTotal - $totalPontos);
    $mediaAtual = $preenchidas > 0 ? $totalPontos / $preenchidas : 0;
    $mediaNecessaria = $restantes > 0 ? $faltam / $restantes : null;
    $impossivel = $restantes > 0 && $faltam > ((float)$notaMaxima * $restantes);

    if ($preenchidas === 0) {
        $status = 'Sem notas';
    } elseif ($preenchidas < $totalPeriodos) {
        $status = $totalPontos >= $metaTotal ? 'Meta alcançada' : 'Em andamento';
    } else {
        $status = $totalPontos >= $metaTotal ? 'Aprovado' : 'Não atingiu a média';
    }

    return [
        'total' => $totalPontos,
        'meta_total' => $metaTotal,
        'preenchidas' => $preenchidas,
        'restantes' => $restantes,
        'faltam' => $faltam,
        'media_atual' => $mediaAtual,
        'media_necessaria' => $mediaNecessaria,
        'impossivel' => $impossivel,
        'completo' => $preenchidas === $totalPeriodos,
        'status' => $status
    ];
}

function calcularQuantoPrecisa(
    $notas,
    $mediaAlvo,
    $notaMaxima,
    $pesos
) {
    $indiceProxima = null;
    $somaNP        = 0;
    $somaWFeitas   = 0;

    for ($i = 1; $i <= 4; $i++) {
        $nota =
            isset($notas[$i])
                ? $notas[$i]
                : null;

        $w =
            isset($pesos[$i])
                ? $pesos[$i]
                : 1;

        if ($w <= 0) {
            continue;
        }

        if (
            $nota !== null &&
            $nota !== ''
        ) {
            $nota = (float)$nota;

            $somaNP +=
                $nota * $w;

            $somaWFeitas +=
                $w;
        } elseif ($indiceProxima === null) {
            $indiceProxima = $i;
        }
    }

    if (
        $indiceProxima === null ||
        $somaWFeitas == 0
    ) {
        return null;
    }

    $somaWTodas = 0;

    for ($i = 1; $i <= 4; $i++) {
        $w =
            isset($pesos[$i])
                ? $pesos[$i]
                : 1;

        if ($w > 0) {
            $somaWTodas +=
                $w;
        }
    }

    $wProx =
        isset($pesos[$indiceProxima])
            ? $pesos[$indiceProxima]
            : 1;

    if (
        $wProx <= 0 ||
        $somaWTodas == 0
    ) {
        return null;
    }

    $necessaria =
        (
            $mediaAlvo *
            $somaWTodas -
            $somaNP
        ) /
        $wProx;

    if ($necessaria < 0) {
        $necessaria = 0;
    }

    if ($necessaria > $notaMaxima) {
        return 'Impossível';
    }

    return $necessaria;
}

// ======================================
// ESTRELAS — BOLETIM
// ======================================

function calcularEstrelasNota(
    $nota,
    $notaMaxima
) {
    $nota = (float)$nota;
    $notaMaxima = (float)$notaMaxima;

    if (
        $notaMaxima <= 0 ||
        $nota < 0 ||
        $nota > $notaMaxima
    ) {
        return 0;
    }

    $percentual =
        ($nota / $notaMaxima) * 100;

    if ($percentual >= 99.999) {
        return 10;
    }

    if ($percentual >= 90) {
        return 7;
    }

    if ($percentual >= 80) {
        return 5;
    }

    if ($percentual >= 70) {
        return 3;
    }

    return 0;
}

function nomeAvaliacaoBoletim(
    $avaliacao,
    $tipoCurso
) {
    if ($tipoCurso === 'faculdade') {
        $nomes = [
            1 => 'P1',
            2 => 'P2',
            3 => 'Trabalho',
            4 => 'P3'
        ];
    } else {
        $nomes = [
            1 => '1º Bimestre',
            2 => '2º Bimestre',
            3 => '3º Bimestre',
            4 => '4º Bimestre'
        ];
    }

    return
        $nomes[$avaliacao] ??
        ('Avaliação ' . $avaliacao);
}

function hashPeriodoBoletim($periodo)
{
    return substr(
        hash(
            'sha256',
            (string)$periodo
        ),
        0,
        12
    );
}

function registrarControleRecompensaNota(
    $codigoUsuario,
    $chave
) {
    $pontos =
        carregarPontos(
            $codigoUsuario
        );

    if (
        !isset(
            $pontos['controle']['notas']
        ) ||
        !is_array(
            $pontos['controle']['notas']
        )
    ) {
        $pontos['controle']['notas'] = [
            'recompensas' => []
        ];
    }

    if (
        !isset(
            $pontos['controle']['notas']['recompensas']
        ) ||
        !is_array(
            $pontos['controle']['notas']['recompensas']
        )
    ) {
        $pontos['controle']['notas']['recompensas'] = [];
    }

    if (
        !in_array(
            $chave,
            $pontos['controle']['notas']['recompensas'],
            true
        )
    ) {
        $pontos['controle']['notas']['recompensas'][] =
            $chave;

        salvarPontos(
            $codigoUsuario,
            $pontos
        );
    }
}

function concederRecompensaBoletim(
    $codigoUsuario,
    $tipo,
    $descricao,
    $quantidade,
    $chave,
    &$recompensaBoletim
) {
    $quantidade =
        (int)$quantidade;

    if (
        $quantidade <= 0 ||
        trim((string)$chave) === ''
    ) {
        return false;
    }

    $adicionou =
        adicionarEstrelas(
            $codigoUsuario,
            $tipo,
            $descricao,
            $quantidade,
            $chave
        );

    if (!$adicionou) {
        return false;
    }

    registrarControleRecompensaNota(
        $codigoUsuario,
        $chave
    );

    $recompensaBoletim['estrelas'] +=
        $quantidade;

    $recompensaBoletim['motivos'][] =
        $descricao;

    return true;
}

function obterNotaLinhaBoletim(
    $linhaNotas,
    $avaliacao
) {
    if (!is_array($linhaNotas)) {
        return null;
    }

    $valor =
        $linhaNotas[$avaliacao] ??
        $linhaNotas[(string)$avaliacao] ??
        null;

    if (
        $valor === null ||
        $valor === ''
    ) {
        return null;
    }

    return (float)$valor;
}

function processarBonusBimestreBoletim(
    $codigoUsuario,
    $periodo,
    $avaliacao,
    $materias,
    $materiaIds,
    $notas,
    $notaMaxima,
    $mediaAprovacao,
    $tipoCurso,
    &$recompensaBoletim
) {
    $avaliacao =
        (int)$avaliacao;

    if (
        $avaliacao < 1 ||
        $avaliacao > 4
    ) {
        return;
    }

    $notasValidas = [];

    foreach (
        $materias
        as $indice => $materiaNome
    ) {
        $materiaNome =
            trim(
                (string)$materiaNome
            );

        $materiaId =
            trim(
                (string)(
                    $materiaIds[$indice] ??
                    ''
                )
            );

        if (
            $materiaNome === '' ||
            $materiaId === ''
        ) {
            continue;
        }

        $nota =
            obterNotaLinhaBoletim(
                $notas[$indice] ?? [],
                $avaliacao
            );

        if ($nota === null) {
            return;
        }

        if (
            $nota < 0 ||
            $nota > $notaMaxima
        ) {
            return;
        }

        $notasValidas[] =
            $nota;
    }

    if (count($notasValidas) === 0) {
        return;
    }

    $periodoHash =
        hashPeriodoBoletim(
            $periodo
        );

    $nomeAvaliacao =
        nomeAvaliacaoBoletim(
            $avaliacao,
            $tipoCurso
        );

    // ==================================
    // FECHOU TODAS AS NOTAS
    // +10 estrelas
    // ==================================

    concederRecompensaBoletim(
        $codigoUsuario,
        'boletim_completo',
        $nomeAvaliacao .
            ' com todas as notas preenchidas',
        10,
        'boletim_completo_' .
            $periodoHash .
            '_avaliacao_' .
            $avaliacao,
        $recompensaBoletim
    );

    // ==================================
    // TODAS AS MATÉRIAS APROVADAS
    // +15 estrelas
    // ==================================

    $todasAprovadas =
        true;

    foreach ($notasValidas as $nota) {
        if ($nota < $mediaAprovacao) {
            $todasAprovadas = false;
            break;
        }
    }

    if ($todasAprovadas) {
        concederRecompensaBoletim(
            $codigoUsuario,
            'boletim_aprovado',
            $nomeAvaliacao .
                ' aprovado em todas as matérias',
            15,
            'boletim_aprovado_' .
                $periodoHash .
                '_avaliacao_' .
                $avaliacao,
            $recompensaBoletim
        );
    }

    // ==================================
    // DESEMPENHO GERAL
    // >= 80% = +20
    // >= 90% = +30
    // NÃO ACUMULAM ENTRE SI
    // ==================================

    $limiteOito =
        $notaMaxima * 0.80;

    $limiteNove =
        $notaMaxima * 0.90;

    $menorNota =
        min($notasValidas);

    $estrelasDesempenho =
        0;

    $descricaoDesempenho =
        '';

    if ($menorNota >= $limiteNove) {
        $estrelasDesempenho = 30;
        $descricaoDesempenho =
            $nomeAvaliacao .
            ' excelente: todas as notas foram 90% ou mais';
    } elseif ($menorNota >= $limiteOito) {
        $estrelasDesempenho = 20;
        $descricaoDesempenho =
            $nomeAvaliacao .
            ' de destaque: todas as notas foram 80% ou mais';
    }

    if ($estrelasDesempenho > 0) {
        /*
         * Uma única chave para desempenho.
         * Assim o bônus de 80% e o de 90%
         * nunca acumulam no mesmo bimestre.
         */
        concederRecompensaBoletim(
            $codigoUsuario,
            'boletim_destaque',
            $descricaoDesempenho,
            $estrelasDesempenho,
            'boletim_desempenho_' .
                $periodoHash .
                '_avaliacao_' .
                $avaliacao,
            $recompensaBoletim
        );
    }
}

// ======================================
// VARIÁVEIS ATUAIS
// ======================================

$notaMaxima     = $data['nota_maxima'];
$mediaAprovacao = $data['media_aprovacao'];
$tipoCurso      = $data['tipo_curso'];
$pesos          = $data['pesos'];
$periodos       = $data['periodos'];
$periodoAtual   = $data['periodo_atual'];

// ======================================
// TRATAMENTO POST
// ======================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==================================
    // TROCAR PERÍODO RAPIDAMENTE
    // ==================================

    if (isset($_POST['trocar_periodo'])) {

        $novoPeriodo = trim(
            (string)(
                $_POST['periodo_atual'] ??
                ''
            )
        );

        if (
            $novoPeriodo !== '' &&
            isset($data['periodos'][$novoPeriodo])
        ) {
            $data['periodo_atual'] = $novoPeriodo;

            sincronizarPeriodoComMaterias(
                $data['periodos'][$novoPeriodo],
                $materiasData['materias']
            );
        }

        salvarJsonArquivo(
            $arquivoMaterias,
            $materiasData
        );

        salvarJsonArquivo(
            $arquivoBoletim,
            $data
        );

        header(
            'Location: ' .
            $_SERVER['PHP_SELF']
        );

        exit;
    }

    $periodoAlvo =
        (
            isset($_POST['periodo_atual_form']) &&
            $_POST['periodo_atual_form'] !== ''
        )
            ? (string)$_POST['periodo_atual_form']
            : (string)$data['periodo_atual'];

    if (!isset($data['periodos'][$periodoAlvo])) {
        $data['periodos'][$periodoAlvo] = [
            'materias'    => [],
            'materia_ids' => [],
            'notas'       => []
        ];
    }

    // Antes de mexer nas linhas, garante que esse
    // período esteja alinhado com materias.json.
    sincronizarPeriodoComMaterias(
        $data['periodos'][$periodoAlvo],
        $materiasData['materias']
    );

    $materiasRef =&
        $data['periodos'][$periodoAlvo]['materias'];

    $materiaIdsRef =&
        $data['periodos'][$periodoAlvo]['materia_ids'];

    $notasRef =&
        $data['periodos'][$periodoAlvo]['notas'];

    // ==================================
    // CONTROLE DAS RECOMPENSAS DESTE POST
    // ==================================

    $notasAntes =
        $notasRef;

    $premiarNotasNestePost =
        isset(
            $_POST['salvar_edicoes']
        );

    $avaliacoesAlteradas = [];

    // ==================================
    // 0) SALVAR MATÉRIAS DA TELA
    // ==================================

    foreach ($_POST as $key => $value) {
        if (
            preg_match(
                '/^materia_(\d+)$/',
                $key,
                $matches
            )
        ) {
            $linha =
                (int)$matches[1];

            $nome =
                trim(
                    (string)$value
                );

            $id =
                trim(
                    (string)(
                        $_POST[
                            'materia_id_' .
                            $linha
                        ] ??
                        (
                            $materiaIdsRef[$linha] ??
                            ''
                        )
                    )
                );

            $indicePorId =
                $id !== ''
                    ? indiceMateriaPorId(
                        $materiasData['materias'],
                        $id
                    )
                    : -1;

            // Matéria já existente:
            // permite renomear pelo Boletim,
            // preservando cor e ícone.
            if ($indicePorId >= 0) {
                $nomeAtual =
                    trim(
                        (string)(
                            $materiasData['materias']
                                [$indicePorId]['nome'] ??
                            ''
                        )
                    );

                if ($nome === '') {
                    $nome = $nomeAtual;
                }

                $indiceMesmoNome =
                    indiceMateriaPorNome(
                        $materiasData['materias'],
                        $nome
                    );

                // Evita criar dois IDs com o mesmo nome.
                if (
                    $indiceMesmoNome >= 0 &&
                    $indiceMesmoNome !== $indicePorId
                ) {
                    $nome = $nomeAtual;
                }

                $materiasData['materias']
                    [$indicePorId]['nome'] =
                    $nome;

                $materiasRef[$linha] =
                    $nome;

                $materiaIdsRef[$linha] =
                    $id;

                continue;
            }

            // Linha nova do Boletim.
            if ($nome !== '') {
                $indicePorNome =
                    indiceMateriaPorNome(
                        $materiasData['materias'],
                        $nome
                    );

                if ($indicePorNome >= 0) {
                    $materia =
                        $materiasData['materias']
                            [$indicePorNome];
                } else {
                    $materia =
                        criarMateriaPadraoBoletim(
                            $nome
                        );

                    $materiasData['materias'][] =
                        $materia;
                }

                $materiasRef[$linha] =
                    $materia['nome'];

                $materiaIdsRef[$linha] =
                    $materia['id'];
            } else {
                $materiasRef[$linha] = '';
                $materiaIdsRef[$linha] = '';
            }
        }
    }

    // ==================================
    // SALVAR NOTAS DA TELA
    // + PROCESSAR RECOMPENSAS INDIVIDUAIS
    // ==================================

    foreach ($_POST as $key => $value) {
        if (
            !preg_match(
                '/^nota_(\d+)_(\d+)$/',
                $key,
                $matches
            )
        ) {
            continue;
        }

        $linha =
            (int)$matches[1];

        $avaliacao =
            (int)$matches[2];

        if (
            $avaliacao < 1 ||
            $avaliacao > 4
        ) {
            continue;
        }

        if (!isset($notasRef[$linha])) {
            $notasRef[$linha] =
                notasVazias();
        }

        $notaAnteriorSalva =
            obterNotaLinhaBoletim(
                $notasAntes[$linha] ?? [],
                $avaliacao
            );

        $value =
            trim(
                (string)$value
            );

        $novaNota =
            ($value === '')
                ? null
                : (float)$value;

        $notasRef[$linha][$avaliacao] =
            $novaNota;

        // Recompensas só são avaliadas ao
        // clicar em "Salvar alterações".
        if (!$premiarNotasNestePost) {
            continue;
        }

        $notaMudou =
            false;

        if (
            $notaAnteriorSalva === null &&
            $novaNota !== null
        ) {
            $notaMudou = true;
        } elseif (
            $notaAnteriorSalva !== null &&
            $novaNota === null
        ) {
            $notaMudou = true;
        } elseif (
            $notaAnteriorSalva !== null &&
            $novaNota !== null &&
            abs(
                $notaAnteriorSalva -
                $novaNota
            ) > 0.00001
        ) {
            $notaMudou = true;
        }

        if (!$notaMudou) {
            continue;
        }

        $avaliacoesAlteradas[$avaliacao] =
            true;

        // Nota vazia não gera prêmio.
        if ($novaNota === null) {
            continue;
        }

        $notaMaximaAtual =
            (float)(
                $data['nota_maxima'] ??
                10
            );

        $mediaAprovacaoAtual =
            (float)(
                $data['media_aprovacao'] ??
                6
            );

        if (
            $novaNota < 0 ||
            $novaNota > $notaMaximaAtual
        ) {
            continue;
        }

        $materiaId =
            trim(
                (string)(
                    $materiaIdsRef[$linha] ??
                    ''
                )
            );

        $materiaNome =
            trim(
                (string)(
                    $materiasRef[$linha] ??
                    'Matéria'
                )
            );

        if ($materiaId === '') {
            continue;
        }

        $periodoHash =
            hashPeriodoBoletim(
                $periodoAlvo
            );

        $nomeAvaliacao =
            nomeAvaliacaoBoletim(
                $avaliacao,
                $data['tipo_curso'] ?? 'escola'
            );

        // ==================================
        // 1) NOTA INDIVIDUAL
        // 70% = 3
        // 80% = 5
        // 90% = 7
        // 100% = 10
        // ==================================

        $estrelasNota =
            calcularEstrelasNota(
                $novaNota,
                $notaMaximaAtual
            );

        if ($estrelasNota > 0) {
            concederRecompensaBoletim(
                $codigoUsuario,
                'nota',
                'Nota ' .
                    number_format(
                        $novaNota,
                        2,
                        ',',
                        '.'
                    ) .
                    ' em ' .
                    $materiaNome .
                    ' — ' .
                    $nomeAvaliacao,
                $estrelasNota,
                'nota_' .
                    $materiaId .
                    '_' .
                    $periodoHash .
                    '_avaliacao_' .
                    $avaliacao,
                $recompensaBoletim
            );
        }

        // ==================================
        // 2) EVOLUÇÃO DA NOTA
        // +5 estrelas
        // ==================================

        if ($avaliacao >= 2) {
            $notaAnteriorBimestre =
                obterNotaLinhaBoletim(
                    $notasRef[$linha] ?? [],
                    $avaliacao - 1
                );

            if (
                $notaAnteriorBimestre !== null &&
                $novaNota > $notaAnteriorBimestre
            ) {
                concederRecompensaBoletim(
                    $codigoUsuario,
                    'evolucao_nota',
                    'Evolução em ' .
                        $materiaNome .
                        ': nota maior que na avaliação anterior',
                    5,
                    'evolucao_nota_' .
                        $materiaId .
                        '_' .
                        $periodoHash .
                        '_avaliacao_' .
                        $avaliacao,
                    $recompensaBoletim
                );
            }

            // ==================================
            // 3) RECUPEROU UMA MATÉRIA
            // +8 estrelas
            // ==================================

            if (
                $notaAnteriorBimestre !== null &&
                $notaAnteriorBimestre < $mediaAprovacaoAtual &&
                $novaNota >= $mediaAprovacaoAtual
            ) {
                concederRecompensaBoletim(
                    $codigoUsuario,
                    'recuperacao_nota',
                    'Recuperação em ' .
                        $materiaNome .
                        ': voltou para a média de aprovação',
                    8,
                    'recuperacao_nota_' .
                        $materiaId .
                        '_' .
                        $periodoHash .
                        '_avaliacao_' .
                        $avaliacao,
                    $recompensaBoletim
                );
            }
        }
    }

    // ==================================
    // 4 A 7) BÔNUS GERAIS DO BIMESTRE
    // ==================================

    if ($premiarNotasNestePost) {
        foreach (
            array_keys(
                $avaliacoesAlteradas
            )
            as $avaliacaoAlterada
        ) {
            processarBonusBimestreBoletim(
                $codigoUsuario,
                $periodoAlvo,
                $avaliacaoAlterada,
                $materiasRef,
                $materiaIdsRef,
                $notasRef,
                (float)($data['nota_maxima'] ?? 10),
                (float)($data['media_aprovacao'] ?? 6),
                $data['tipo_curso'] ?? 'escola',
                $recompensaBoletim
            );
        }
    }

    // ==================================
    // 1) CONFIGURAÇÕES
    // ==================================

    if (isset($_POST['salvar_config'])) {

        if (
            isset($_POST['tipo_curso']) &&
            in_array(
                $_POST['tipo_curso'],
                ['escola', 'faculdade'],
                true
            )
        ) {
            $data['tipo_curso'] =
                $_POST['tipo_curso'];
        }

        $notaMax =
            (
                isset($_POST['nota_maxima']) &&
                $_POST['nota_maxima'] !== ''
            )
                ? (float)$_POST['nota_maxima']
                : (float)$data['nota_maxima'];

        $mediaAp =
            (
                isset($_POST['media_aprovacao']) &&
                $_POST['media_aprovacao'] !== ''
            )
                ? (float)$_POST['media_aprovacao']
                : (float)$data['media_aprovacao'];

        if ($notaMax <= 0) {
            $notaMax = 10;
        }

        if ($mediaAp <= 0) {
            $mediaAp = 6;
        }

        $data['nota_maxima'] =
            $notaMax;

        $data['media_aprovacao'] =
            $mediaAp;

        $novosPesos = [];

        for ($i = 1; $i <= 4; $i++) {
            $campo = 'peso_' . $i;

            $w =
                (
                    isset($_POST[$campo]) &&
                    $_POST[$campo] !== ''
                )
                    ? (float)$_POST[$campo]
                    : 1;

            if ($w < 0) {
                $w = 0;
            }

            $novosPesos[$i] =
                $w;
        }

        $data['pesos'] =
            $novosPesos;

        $periodoSel =
            (
                isset($_POST['periodo_atual']) &&
                $_POST['periodo_atual'] !== ''
            )
                ? (string)$_POST['periodo_atual']
                : (string)$data['periodo_atual'];

        $novoPeriodo =
            isset($_POST['novo_periodo'])
                ? trim(
                    (string)$_POST['novo_periodo']
                )
                : '';

        if ($novoPeriodo !== '') {
            if (
                !isset(
                    $data['periodos'][$novoPeriodo]
                )
            ) {
                $data['periodos'][$novoPeriodo] = [
                    'materias'    => [],
                    'materia_ids' => [],
                    'notas'       => []
                ];
            }

            $periodoSel =
                $novoPeriodo;
        }

        if (
            !isset(
                $data['periodos'][$periodoSel]
            )
        ) {
            $data['periodos'][$periodoSel] = [
                'materias'    => [],
                'materia_ids' => [],
                'notas'       => []
            ];
        }

        $data['periodo_atual'] =
            $periodoSel;

        // Ao trocar/criar período, ele já nasce
        // com as matérias de materias.json.
        sincronizarPeriodoComMaterias(
            $data['periodos'][$periodoSel],
            $materiasData['materias']
        );
    }

    // ==================================
    // 2) ADICIONAR LINHA
    // ==================================

    if (isset($_POST['adicionar_linha'])) {
        $materiasRef[] = '';
        $materiaIdsRef[] = '';
        $notasRef[] =
            notasVazias();
    }

    // Remove somente uma linha ainda vazia.
    // Matéria real deve ser excluída em Estudos.
    if (
        isset($_POST['remover_linha']) &&
        count($materiasRef) > 0
    ) {
        $ultimo =
            count($materiasRef) - 1;

        $ultimoId =
            trim(
                (string)(
                    $materiaIdsRef[$ultimo] ??
                    ''
                )
            );

        $ultimoNome =
            trim(
                (string)(
                    $materiasRef[$ultimo] ??
                    ''
                )
            );

        if (
            $ultimoId === '' &&
            $ultimoNome === ''
        ) {
            array_pop($materiasRef);
            array_pop($materiaIdsRef);
            array_pop($notasRef);
        }
    }

    // ==================================
    // 3) LIMPAR NOTAS DA LINHA
    // ==================================

    if (
        isset($_POST['limpar_linha']) &&
        isset($_POST['linha_index'])
    ) {
        $idx =
            (int)$_POST['linha_index'];

        if (isset($materiasRef[$idx])) {
            $notasRef[$idx] =
                notasVazias();
        }
    }

    // ==================================
    // 4) LIMPAR TODAS AS NOTAS
    // ==================================

    if (isset($_POST['limpar_tudo'])) {
        foreach ($materiasRef as $idx => $materiaNome) {
            $notasRef[$idx] =
                notasVazias();
        }
    }

    // ==================================
    // SALVAR OS DOIS JSONS
    // ==================================

    salvarJsonArquivo(
        $arquivoMaterias,
        $materiasData
    );

    salvarJsonArquivo(
        $arquivoBoletim,
        $data
    );

    // Atualiza variáveis para renderizar
    // imediatamente o resultado do POST.
    $notaMaxima =
        $data['nota_maxima'];

    $mediaAprovacao =
        $data['media_aprovacao'];

    $tipoCurso =
        $data['tipo_curso'];

    $pesos =
        $data['pesos'];

    $periodos =
        $data['periodos'];

    $periodoAtual =
        $data['periodo_atual'];
}

// ======================================
// LABELS DAS AVALIAÇÕES
// ======================================

if ($tipoCurso === 'escola') {
    $labelsAval = [
        '1º Bimestre',
        '2º Bimestre',
        '3º Bimestre',
        '4º Bimestre'
    ];
} else {
    $labelsAval = [
        'P1',
        'P2',
        'Trabalho',
        'P3'
    ];
}

// ======================================
// DADOS DO PERÍODO ATUAL
// ======================================

if (!isset($data['periodos'][$periodoAtual])) {
    $data['periodos'][$periodoAtual] = [
        'materias'    => [],
        'materia_ids' => [],
        'notas'       => []
    ];

    sincronizarPeriodoComMaterias(
        $data['periodos'][$periodoAtual],
        $materiasData['materias']
    );
}

$materias =
    $data['periodos']
        [$periodoAtual]['materias'];

$materiaIds =
    $data['periodos']
        [$periodoAtual]['materia_ids'] ??
    [];

$notasAll =
    $data['periodos']
        [$periodoAtual]['notas'];

$current =
    basename(
        $_SERVER['PHP_SELF']
    );

// ======================================
// RESUMO E METADADOS PARA A NOVA UI
// ======================================

$totalMaterias = 0;
$aprovadas = 0;
$recuperacao = 0;
$reprovadas = 0;
$somaMedias = 0;
$contMedias = 0;
$melhorMateria = null;
$piorMateria = null;
$materiasAtencao = [];

$metaMaterias = [];
foreach (($materiasData['materias'] ?? []) as $m) {
    if (!is_array($m)) continue;
    $mid = (string)($m['id'] ?? '');
    if ($mid === '') continue;
    $metaMaterias[$mid] = [
        'cor' => (string)($m['cor'] ?? '#94a3b8'),
        'icone' => (string)($m['icone'] ?? 'fa-book')
    ];
}

foreach ($materias as $i => $materiaResumo) {
    $nomeResumo = trim((string)$materiaResumo);
    if ($nomeResumo === '') continue;

    $totalMaterias++;
    $notasResumo = $notasAll[$i] ?? notasVazias();
    if ($tipoCurso === 'escola') {
        $progressoResumo = calcularProgressoAnualEscola($notasResumo, $mediaAprovacao, $notaMaxima);
        $mediaResumo = (float)$progressoResumo['media_atual'];
        $statusResumo = (string)$progressoResumo['status'];
        $precisaResumo = $progressoResumo['media_necessaria'];

        if ($statusResumo === 'Aprovado') $aprovadas++;
        if ($statusResumo === 'Meta alcançada') $recuperacao++; // reaproveitado abaixo como contador de meta atingida em andamento
        if (in_array($statusResumo, ['Em andamento', 'Não atingiu a média'], true) && $progressoResumo['preenchidas'] > 0) $reprovadas++;
    } else {
        $dadosResumo = calcularMediaEStatus($notasResumo, $mediaAprovacao, $pesos);
        $mediaResumo = (float)$dadosResumo['media'];
        $statusResumo = (string)$dadosResumo['status'];
        $precisaResumo = calcularQuantoPrecisa($notasResumo, $mediaAprovacao, $notaMaxima, $pesos);

        if ($statusResumo === 'Aprovado') $aprovadas++;
        if ($statusResumo === 'Recuperação') $recuperacao++;
        if ($statusResumo === 'Reprovado') $reprovadas++;
    }

    $temNotasResumo = $tipoCurso === 'escola'
        ? (($progressoResumo['preenchidas'] ?? 0) > 0)
        : ($statusResumo !== '-');

    if ($temNotasResumo) {
        $somaMedias += $mediaResumo;
        $contMedias++;

        if ($melhorMateria === null || $mediaResumo > $melhorMateria['media']) {
            $melhorMateria = ['nome' => $nomeResumo, 'media' => $mediaResumo];
        }
        if ($piorMateria === null || $mediaResumo < $piorMateria['media']) {
            $piorMateria = ['nome' => $nomeResumo, 'media' => $mediaResumo];
        }
    }

    $deveEntrarAtencao = $tipoCurso === 'escola'
        ? (isset($progressoResumo) && $progressoResumo['preenchidas'] > 0 && $progressoResumo['faltam'] > 0)
        : ($statusResumo !== 'Aprovado');

    if ($deveEntrarAtencao) {
        $materiasAtencao[] = [
            'indice' => $i,
            'nome' => $nomeResumo,
            'media' => $mediaResumo,
            'status' => $statusResumo,
            'precisa' => $precisaResumo,
            'id' => (string)($materiaIds[$i] ?? ''),
            'progresso' => $tipoCurso === 'escola' ? $progressoResumo : null
        ];
    }
}

usort($materiasAtencao, function ($a, $b) {
    return $a['media'] <=> $b['media'];
});

$mediaGeral = $contMedias > 0 ? $somaMedias / $contMedias : 0;
$emAtencao = count($materiasAtencao);
$metaAnual = $tipoCurso === 'escola' ? ((float)$mediaAprovacao * 4) : (float)$mediaAprovacao;
$metasAlcancadas = $tipoCurso === 'escola' ? $recuperacao : $aprovadas;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FOAG — Notas e Médias</title>
  <link rel="stylesheet" href="boletim.css?v=20261005-1">
  <link rel="stylesheet" href="../m.escuro/dark_basee.css">
  <link rel="stylesheet" href="dark_notas.css?v=20261005-1">
  <link rel="stylesheet" href="../estrelas/modal_estrelas.css?v=<?= time() ?>">

  <!-- ACESSIBILIDADE GLOBAL -->

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="../m.escuro/dark-mode.js"></script>


  <style>
      #icon-fogi {
        cursor: pointer;
        transition: 0.2s;
      }
      #icon-fogi:hover {
        color: #38a5ff;
        transform: scale(1.1);
      }
      #fogi-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
      }
      #fogi-modal .fogi-container {
        background: #ffffff;
        width: 90%;
        max-width: 1100px;
        height: 80vh;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 35px rgba(0,0,0,0.2);
      }
      #fogi-modal .fogi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #38a5ff;
        color: #fff;
        padding: 8px 14px;
        font-weight: 600;
        font-size: 0.95rem;
      }
      #fogi-close {
        border: none;
        background: #ffffff;
        color: #333;
        padding: 4px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
      }
      #fogi-close:hover {
        background: #f1f1f1;
      }
      #fogi-iframe {
        flex: 1;
        border: none;
        width: 100%;
        height: 100%;
      }

      


      /* ==========================================
         ÁREA PRINCIPAL + FOOTER
      ========================================== */
      .page-area {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
      }

      .page-area .main-content {
        flex: 1;
        min-width: 0;
        width: 100%;
      }

      .footer {
        width: 100%;
        margin: 30px 0 0;
        padding: 0;
        background: #ffffff;
        color: #232323;
        border-top: 1px solid #e5edf5;
        box-shadow: none;
        text-align: left;
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
        gap: 25px;
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
        transition: color 0.2s ease;
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

        .footer-content {
          min-height: auto;
          padding: 18px;
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
    <link rel="stylesheet" href="../global/css/cursor.css">
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

      <!-- ==========================================
           CABEÇALHO DO BOLETIM
      =========================================== -->
      <section class="boletim-topo">
        <div class="boletim-titulo">
          <span class="boletim-eyebrow">Desempenho acadêmico</span>
          <h1>Boletim</h1>
          <p>Acompanhe suas notas, médias e desempenho em cada matéria.</p>
        </div>

        <form method="POST" class="periodo-rapido">
          <input type="hidden" name="trocar_periodo" value="1">

          <label for="filtro-periodo">
            <i class="fa-regular fa-calendar"></i>
            Período
          </label>

          <select
            id="filtro-periodo"
            name="periodo_atual"
            onchange="this.form.submit()"
          >
            <?php
            foreach ($data['periodos'] as $nomePeriodo => $dadosPeriodo) {
                $selected = (
                    $nomePeriodo === $periodoAtual
                )
                    ? 'selected'
                    : '';

                echo '<option value="' .
                    htmlspecialchars($nomePeriodo) .
                    '" ' .
                    $selected .
                    '>' .
                    htmlspecialchars($nomePeriodo) .
                    '</option>';
            }
            ?>
          </select>
        </form>
      </section>

      <!-- RESUMO RÁPIDO -->
      <section class="resumo-hero" aria-label="Resumo do boletim">
        <article class="resumo-kpi destaque">
          <span class="kpi-icone"><i class="fa-solid fa-chart-line"></i></span>
          <div>
            <span class="resumo-label">Média geral</span>
            <strong class="resumo-valor" id="kpi-media-geral"><?= number_format($mediaGeral, 2, ',', '.'); ?></strong>
          </div>
        </article>
        <article class="resumo-kpi aprovado">
          <span class="kpi-icone"><i class="fa-solid fa-circle-check"></i></span>
          <div>
            <span class="resumo-label"><?= $tipoCurso === 'escola' ? 'Meta anual' : 'Aprovadas'; ?></span>
            <strong class="resumo-valor" id="kpi-aprovadas"><?= $tipoCurso === 'escola' ? number_format($metaAnual, 0, ',', '.') : $aprovadas; ?></strong>
            <small><?= $tipoCurso === 'escola' ? 'pontos por matéria' : 'de ' . $totalMaterias . ' matérias'; ?></small>
          </div>
        </article>
        <article class="resumo-kpi atencao">
          <span class="kpi-icone"><i class="fa-solid fa-bolt"></i></span>
          <div>
            <span class="resumo-label"><?= $tipoCurso === 'escola' ? 'Meta já alcançada' : 'Precisam de atenção'; ?></span>
            <strong class="resumo-valor" id="kpi-atencao"><?= $tipoCurso === 'escola' ? $metasAlcancadas : $emAtencao; ?></strong>
            <small><?= $tipoCurso === 'escola' ? 'antes do fechamento' : 'priorize primeiro'; ?></small>
          </div>
        </article>
        <article class="resumo-kpi materias">
          <span class="kpi-icone"><i class="fa-solid fa-book-open"></i></span>
          <div>
            <span class="resumo-label">Matérias</span>
            <strong class="resumo-valor"><?= $totalMaterias; ?></strong>
            <small>neste período</small>
          </div>
        </article>
      </section>

      <!-- CARD PRINCIPAL DE NOTAS -->
      <section class="card-notas card-boletim-principal">
        <div class="card-section-header">
          <div>
            <span class="section-eyebrow">Seu desempenho</span>
            <h2 class="titulo-tabela">Suas notas</h2>
            <p class="sub-notas">Digite suas notas. O FOAG acompanha os pontos acumulados e só define aprovação após o fechamento de todos os bimestres.</p>
          </div>
          <div class="acoes-topo-notas">
            <span class="autosave-status" id="autosave-status"><i class="fa-solid fa-cloud"></i> Tudo salvo</span>
            <button type="button" class="btn-view-toggle" id="btn-view-toggle" aria-pressed="false">
              <i class="fa-solid fa-table-list"></i>
              <span class="view-toggle-label">Ver modo resumido</span>
            </button>
            <button type="button" class="btn-config-toggle" id="btn-config-toggle" aria-expanded="false" aria-controls="config-panel">
              <i class="fa-solid fa-sliders"></i> Configurar boletim
            </button>
          </div>
        </div>

        <div id="config-panel" class="config-panel" hidden>
          <form method="POST" class="config-form config-form-nova">
            <div class="tipo-curso-group">
              <span>Tipo:</span>
              <label><input type="radio" name="tipo_curso" value="escola" <?= ($tipoCurso === 'escola' ? 'checked' : ''); ?>> Escola</label>
              <label><input type="radio" name="tipo_curso" value="faculdade" <?= ($tipoCurso === 'faculdade' ? 'checked' : ''); ?>> Faculdade</label>
            </div>
            <div class="config-field"><label for="nota_maxima">Nota máxima</label><input type="number" step="0.01" id="nota_maxima" name="nota_maxima" value="<?= htmlspecialchars($notaMaxima); ?>" min="1"></div>
            <div class="config-field"><label for="media_aprovacao">Média para aprovação</label><input type="number" step="0.01" id="media_aprovacao" name="media_aprovacao" value="<?= htmlspecialchars($mediaAprovacao); ?>" min="0"></div>
            <?php for ($pi = 1; $pi <= 4; $pi++): ?>
              <div class="config-field"><label for="peso_<?= $pi; ?>">Peso <?= htmlspecialchars($labelsAval[$pi - 1]); ?></label><input type="number" step="0.1" id="peso_<?= $pi; ?>" name="peso_<?= $pi; ?>" value="<?= htmlspecialchars($pesos[$pi] ?? 1); ?>" min="0"></div>
            <?php endfor; ?>
            <div class="config-field"><label for="novo_periodo">Novo período</label><input type="text" id="novo_periodo" name="novo_periodo" placeholder="Ex: 2027/1"></div>
            <input type="hidden" name="periodo_atual" value="<?= htmlspecialchars($periodoAtual); ?>">
            <input type="hidden" name="periodo_atual_form" value="<?= htmlspecialchars($periodoAtual); ?>">
            <button type="submit" name="salvar_config" class="btn-config">Salvar configurações</button>
          </form>
        </div>

        <form method="POST" id="notas-form" data-media-aprovacao="<?= htmlspecialchars($mediaAprovacao); ?>" data-nota-maxima="<?= htmlspecialchars($notaMaxima); ?>" data-pesos="<?= htmlspecialchars(json_encode(array_values($pesos))); ?>" data-tipo-curso="<?= htmlspecialchars($tipoCurso); ?>" data-meta-anual="<?= htmlspecialchars($metaAnual); ?>">
          <input type="hidden" name="periodo_atual_form" value="<?= htmlspecialchars($periodoAtual); ?>">
          <div class="table-scroll">
            <table class="tabela-notas tabela-notas-nova" id="tabela-boletim">
              <thead>
                <tr>
                  <th>Matéria</th>
                  <th><?= htmlspecialchars($labelsAval[0]); ?></th>
                  <th><?= htmlspecialchars($labelsAval[1]); ?></th>
                  <th><?= htmlspecialchars($labelsAval[2]); ?></th>
                  <th><?= htmlspecialchars($labelsAval[3]); ?></th>
                  <th class="col-media">
                    <?php if ($tipoCurso === 'escola'): ?>
                      <span class="view-detalhado">Pontos</span><span class="view-resumido">Média atual</span>
                    <?php else: ?>Média<?php endif; ?>
                  </th>
                  <th class="col-status">Situação</th>
                  <th class="col-falta">
                    <?php if ($tipoCurso === 'escola'): ?>
                      <span class="view-detalhado">O que falta</span><span class="view-resumido">Falta</span>
                    <?php else: ?>Próxima meta<?php endif; ?>
                  </th>
                  <th><span class="sr-only">Ações</span></th>
                </tr>
              </thead>
              <tbody>
              <?php if (count($materias) === 0): ?>
                <tr class="linha-vazia"><td colspan="9">Nenhuma matéria cadastrada ainda. Adicione uma matéria para começar.</td></tr>
              <?php else: foreach ($materias as $i => $materia):
                  $materiaNomeRaw = (string)$materia;
                  $notas = $notasAll[$i] ?? notasVazias();
                  if ($tipoCurso === 'escola') {
                      $progresso = calcularProgressoAnualEscola($notas, $mediaAprovacao, $notaMaxima);
                      $media = (float)$progresso['media_atual'];
                      $status = (string)$progresso['status'];
                      $precisa = $progresso['media_necessaria'];
                  } else {
                      $dados = calcularMediaEStatus($notas, $mediaAprovacao, $pesos);
                      $media = (float)$dados['media'];
                      $status = (string)$dados['status'];
                      $precisa = calcularQuantoPrecisa($notas, $mediaAprovacao, $notaMaxima, $pesos);
                      $progresso = null;
                  }
                  $materiaIdRaw = (string)($materiaIds[$i] ?? '');
                  $meta = $metaMaterias[$materiaIdRaw] ?? ['cor' => '#94a3b8', 'icone' => 'fa-book'];
                  $statusClass = in_array($status, ['Aprovado', 'Meta alcançada'], true) ? 'status-aprovado' : (in_array($status, ['Em andamento', 'Recuperação', 'Sem notas'], true) ? 'status-recuperacao' : 'status-reprovado');
              ?>
                <tr class="nota-row" data-row="<?= (int)$i; ?>">
                  <td class="materia-cell">
                    <input type="hidden" name="materia_id_<?= (int)$i; ?>" value="<?= htmlspecialchars($materiaIdRaw); ?>">
                    <input type="hidden" name="materia_<?= (int)$i; ?>" value="<?= htmlspecialchars($materiaNomeRaw); ?>">
                    <span class="materia-dot" style="--materia-cor: <?= htmlspecialchars($meta['cor']); ?>"><i class="fa-solid <?= htmlspecialchars($meta['icone']); ?>"></i></span>
                    <span class="materia-nome"><?= htmlspecialchars($materiaNomeRaw); ?></span>
                  </td>
                  <?php for ($a = 1; $a <= 4; $a++):
                    $notaVal = $notas[$a] ?? null;
                    $notaStr = ($notaVal !== null && $notaVal !== '') ? (string)$notaVal : '';
                  ?>
                    <td><input type="number" step="0.01" min="0" max="<?= htmlspecialchars($notaMaxima); ?>" name="nota_<?= (int)$i; ?>_<?= $a; ?>" value="<?= htmlspecialchars($notaStr); ?>" placeholder="—" class="input-nota" data-avaliacao="<?= $a; ?>"></td>
                  <?php endfor; ?>
                  <td class="celula-media">
                    <?php if ($tipoCurso === 'escola'): ?>
                      <span class="view-detalhado media-detalhada">
                        <strong class="media-valor pontos-valor"><?= number_format((float)$progresso['total'], 1, ',', '.'); ?></strong><span class="media-referencia">/ <?= number_format((float)$progresso['meta_total'], 0, ',', '.'); ?> pts</span>
                        <small class="media-parcial">média atual <?= number_format($media, 1, ',', '.'); ?></small>
                      </span>
                      <span class="view-resumido media-resumida">
                        <strong class="media-atual-resumida"><?= number_format($media, 1, ',', '.'); ?></strong><span>/ <?= number_format((float)$notaMaxima, 0, ',', '.'); ?></span>
                      </span>
                    <?php else: ?>
                      <strong class="media-valor"><?= number_format($media, 2, ',', '.'); ?></strong><span class="media-referencia">/ <?= number_format((float)$notaMaxima, 0, ',', '.'); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="celula-status col-status"><span class="badge-status <?= $statusClass; ?>"><?= htmlspecialchars($status); ?></span></td>
                  <td class="celula-precisa col-falta">
                    <?php if ($tipoCurso === 'escola'): ?>
                      <span class="view-detalhado falta-detalhada">
                      <?php if ($progresso['preenchidas'] === 0): ?>
                        <span class="proxima-meta"><small>Meta anual</small><strong><?= number_format($progresso['meta_total'], 0, ',', '.'); ?> pts</strong></span>
                      <?php elseif (!$progresso['completo'] && $progresso['faltam'] <= 0): ?>
                        <span class="meta-ok"><i class="fa-solid fa-check"></i> Meta anual alcançada</span>
                      <?php elseif ($progresso['impossivel']): ?>
                        <span class="badge-precisa impossivel">Meta não alcançável só com os bimestres restantes</span>
                      <?php elseif (!$progresso['completo']): ?>
                        <span class="proxima-meta"><small>Faltam <?= number_format($progresso['faltam'], 1, ',', '.'); ?> pts</small><strong><?= $progresso['restantes'] === 1 ? 'precisa de ' . number_format($progresso['faltam'], 1, ',', '.') : 'média ' . number_format($progresso['media_necessaria'], 1, ',', '.') . ' nos ' . $progresso['restantes'] . ' restantes'; ?></strong></span>
                      <?php elseif ($status === 'Aprovado'): ?>
                        <span class="meta-ok"><i class="fa-solid fa-check"></i> Aprovado no ano</span>
                      <?php else: ?>
                        <span class="badge-precisa impossivel">Faltaram <?= number_format($progresso['faltam'], 1, ',', '.'); ?> pts</span>
                      <?php endif; ?>
                      </span>
                      <span class="view-resumido falta-resumida" title="Pontos que ainda faltam para a meta anual">
                        <strong class="falta-numero"><?= number_format((float)$progresso['faltam'], 1, ',', '.'); ?></strong><span> pts</span>
                      </span>
                    <?php elseif ($status === 'Aprovado'): ?>
                      <span class="meta-ok"><i class="fa-solid fa-check"></i> Meta atingida</span>
                    <?php elseif ($precisa === 'Impossível'): ?>
                      <span class="badge-precisa impossivel">Requer recuperação</span>
                    <?php elseif ($precisa !== null): ?>
                      <span class="proxima-meta"><small>Você precisa de</small><strong><?= number_format((float)$precisa, 1, ',', '.'); ?></strong></span>
                    <?php else: ?>—<?php endif; ?>
                  </td>
                  <td class="acoes-linha"><button type="submit" name="limpar_linha" value="1" class="btn-menu-linha" title="Limpar notas" onclick="document.getElementById('linha_index').value=<?= (int)$i; ?>"><i class="fa-solid fa-ellipsis"></i></button></td>
                </tr>
              <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
          <input type="hidden" id="linha_index" name="linha_index" value="">
          <div class="buttons-notas buttons-notas-nova">
            <button type="submit" name="adicionar_linha" class="btn-add-materia"><i class="fa-solid fa-plus"></i> Adicionar matéria</button>
            <button type="submit" name="limpar_tudo" class="btn-secundario"><i class="fa-regular fa-trash-can"></i> Limpar notas</button>
          </div>
        </form>
      </section>

      <!-- ATENÇÃO -->
      <section class="card-notas card-atencao">
        <div class="card-section-header compacta">
          <div><span class="section-eyebrow">Prioridades</span><h2 class="titulo-tabela">Matérias que precisam de atenção</h2></div>
        </div>
        <?php if (count($materiasAtencao) > 0): ?>
          <div class="atencao-lista">
            <?php foreach (array_slice($materiasAtencao, 0, 3) as $item):
              $meta = $metaMaterias[$item['id']] ?? ['cor' => '#94a3b8', 'icone' => 'fa-book'];
            ?>
              <article class="atencao-item">
                <div class="atencao-materia">
                  <span class="materia-dot" style="--materia-cor: <?= htmlspecialchars($meta['cor']); ?>"><i class="fa-solid <?= htmlspecialchars($meta['icone']); ?>"></i></span>
                  <div><strong><?= htmlspecialchars($item['nome']); ?></strong><span><?php if ($tipoCurso === 'escola' && !empty($item['progresso'])): ?><?= number_format($item['progresso']['total'], 1, ',', '.'); ?> de <?= number_format($item['progresso']['meta_total'], 0, ',', '.'); ?> pontos acumulados<?php else: ?>Média atual <?= number_format($item['media'], 1, ',', '.'); ?> · meta <?= number_format((float)$mediaAprovacao, 1, ',', '.'); ?><?php endif; ?></span></div>
                </div>
                <div class="atencao-meta">
                  <?php if ($tipoCurso === 'escola' && !empty($item['progresso'])): ?>
                    <span>Para a meta anual</span><strong>faltam <?= number_format($item['progresso']['faltam'], 1, ',', '.'); ?> pontos</strong>
                    <?php if ($item['progresso']['restantes'] > 1 && !$item['progresso']['impossivel']): ?><small>média <?= number_format($item['progresso']['media_necessaria'], 1, ',', '.'); ?> nos <?= $item['progresso']['restantes']; ?> bimestres restantes</small><?php elseif ($item['progresso']['restantes'] === 1 && !$item['progresso']['impossivel']): ?><small>precisa de <?= number_format($item['progresso']['faltam'], 1, ',', '.'); ?> no último bimestre</small><?php endif; ?>
                  <?php elseif ($item['precisa'] === 'Impossível'): ?><strong>Recuperação necessária</strong>
                  <?php elseif ($item['precisa'] !== null): ?><span>Próxima avaliação</span><strong>precisa de <?= number_format((float)$item['precisa'], 1, ',', '.'); ?></strong>
                  <?php else: ?><strong>Continue acompanhando</strong><?php endif; ?>
                </div>
                <div class="atencao-acoes">
                  <a href="../tarefas/tarefas.php" class="btn-acao-estudo"><i class="fa-solid fa-list-check"></i> Criar tarefa</a>
                  <a href="../timer/timer.php" class="btn-acao-estudo secundario"><i class="fa-regular fa-clock"></i> Estudar com Timer</a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-sucesso"><i class="fa-solid fa-circle-check"></i><div><strong>Está tudo em dia.</strong><span>Quando alguma matéria ainda precisar de pontos para atingir a meta anual, ela aparecerá aqui.</span></div></div>
        <?php endif; ?>
      </section>

      <!-- DESEMPENHO -->
      <section class="card-notas card-desempenho">
        <div class="card-section-header compacta"><div><span class="section-eyebrow">Visão rápida</span><h2 class="titulo-tabela">Desempenho por matéria</h2></div></div>
        <div class="desempenho-lista">
          <?php foreach ($materias as $i => $materiaGrafico):
            $nomeGrafico = trim((string)$materiaGrafico); if ($nomeGrafico === '') continue;
            $dadosGrafico = calcularMediaEStatus($notasAll[$i] ?? notasVazias(), $mediaAprovacao, $pesos);
            $mediaGrafico = (float)$dadosGrafico['media'];
            $pctGrafico = $notaMaxima > 0 ? max(0, min(100, ($mediaGrafico / $notaMaxima) * 100)) : 0;
            $midGrafico = (string)($materiaIds[$i] ?? '');
            $metaGrafico = $metaMaterias[$midGrafico] ?? ['cor' => '#94a3b8'];
          ?>
            <div class="desempenho-linha">
              <span class="desempenho-nome"><?= htmlspecialchars($nomeGrafico); ?></span>
              <div class="desempenho-barra"><span style="width: <?= number_format($pctGrafico, 2, '.', ''); ?>%; --materia-cor: <?= htmlspecialchars($metaGrafico['cor']); ?>"></span></div>
              <strong><?= number_format($mediaGrafico, 1, ',', '.'); ?></strong>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

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

  <!-- Modal da FOGi -->
  <div id="fogi-modal">
    <div class="fogi-container">
      <div class="fogi-header">
        <span>FOGi — Assistente de Estudos</span>
        <button id="fogi-close">Fechar</button>
      </div>
      <iframe id="fogi-iframe" src="about:blank"></iframe>
    </div>
  </div>

  <!-- Modal de Sair -->
  <div id="logout-modal" class="modal">
    <div class="modal-content">
      <h3>Ah... já vai?</h3>
      <h4>Tem certeza que deseja sair?</h4>
      <div class="modal-buttons">
        <button id="confirm-logout" class="btn">Sim</button>
        <button id="cancel-logout" class="btn secondary">Cancelar</button>
      </div>
    </div>
  </div>

  <script src="../estrelas/modal_estrelas.js?v=<?= time() ?>"></script>
  <script src="notas.js?v=<?= time() ?>"></script>

  <?php if (($recompensaBoletim['estrelas'] ?? 0) > 0): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
        const estrelas =
            <?= json_encode((int)$recompensaBoletim['estrelas']); ?>;

        const motivos =
            <?= json_encode(
                $recompensaBoletim['motivos'] ?? [],
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        let mensagem =
            'Mandou bem no Boletim! Continue assim! :)';

        if (Array.isArray(motivos) && motivos.length === 1) {
            mensagem = motivos[0] + '! :)';
        } else if (Array.isArray(motivos) && motivos.length > 1) {
            mensagem =
                `Você conquistou ${motivos.length} recompensas no Boletim! :)`;
        }

        if (
            estrelas > 0 &&
            typeof window.mostrarModalEstrelas === 'function'
        ) {
            window.mostrarModalEstrelas(
                estrelas,
                mensagem
            );
        }
    });
  </script>
  <?php endif; ?>
  
  <script src="../configuracoes/aparencia.js?v=5"></script>
<script src="../configuracoes/acessibilidade.js?v=25" defer></script>
    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>