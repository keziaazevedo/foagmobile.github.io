<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(false);
header('Content-Type: application/json; charset=utf-8');

if ($codigoUsuario === null) {
    http_response_code(401);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$pastaUsuario = foag_pasta_usuario($codigoUsuario);

$arquivoMaterias =
    $pastaUsuario .
    '/materias.json';

if (!is_dir($pastaUsuario)) {
    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Pasta do usuário não encontrada.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$entrada =
    json_decode(
        file_get_contents('php://input'),
        true
    );

if (!is_array($entrada)) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados inválidos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$id =
    trim(
        (string)(
            $entrada['id'] ??
            ''
        )
    );

$nome =
    trim(
        (string)(
            $entrada['nome'] ??
            ''
        )
    );

$cor =
    trim(
        (string)(
            $entrada['cor'] ??
            '#94a3b8'
        )
    );

$icone =
    trim(
        (string)(
            $entrada['icone'] ??
            'fa-circle-question'
        )
    );

if ($id === '') {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID da matéria não informado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ($nome === '') {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Informe o nome da matéria.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (mb_strlen($nome) > 50) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'O nome da matéria deve ter no máximo 50 caracteres.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (
    !preg_match(
        '/^#[0-9A-Fa-f]{6}$/',
        $cor
    )
) {
    $cor = '#94a3b8';
}

if (
    !preg_match(
        '/^fa-[a-z0-9-]+$/',
        $icone
    )
) {
    $icone =
        'fa-circle-question';
}

$materiasData = [
    'materias' => []
];

if (file_exists($arquivoMaterias)) {
    $dados =
        json_decode(
            file_get_contents($arquivoMaterias),
            true
        );

    if (is_array($dados)) {
        $materiasData =
            $dados;
    }
}

if (
    !isset($materiasData['materias']) ||
    !is_array($materiasData['materias'])
) {
    $materiasData['materias'] = [];
}

foreach (
    $materiasData['materias']
    as $materia
) {
    $materiaId =
        (string)(
            $materia['id'] ??
            ''
        );

    $materiaNome =
        trim(
            (string)(
                $materia['nome'] ??
                ''
            )
        );

    if (
        $materiaId !== $id &&
        mb_strtolower(
            $materiaNome,
            'UTF-8'
        ) ===
        mb_strtolower(
            $nome,
            'UTF-8'
        )
    ) {
        http_response_code(409);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Essa matéria já foi cadastrada.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

$indiceEncontrado = null;
$nomeAnterior = '';

foreach (
    $materiasData['materias']
    as $indice => $materia
) {
    if (
        (string)(
            $materia['id'] ??
            ''
        ) ===
        $id
    ) {
        $indiceEncontrado =
            $indice;

        $nomeAnterior =
            trim(
                (string)(
                    $materia['nome'] ??
                    ''
                )
            );

        break;
    }
}

if ($indiceEncontrado === null) {
    http_response_code(404);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Matéria não encontrada.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$materiaAtual =
    $materiasData['materias']
    [$indiceEncontrado];

$materiaAtual['id'] =
    $id;

$materiaAtual['nome'] =
    $nome;

$materiaAtual['cor'] =
    $cor;

$materiaAtual['icone'] =
    $icone;

$materiasData['materias']
[$indiceEncontrado] =
    $materiaAtual;

$salvou =
    file_put_contents(
        $arquivoMaterias,
        json_encode(
            $materiasData,
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
        'mensagem' => 'Não foi possível atualizar a matéria.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ==========================================
// SE TROCOU O NOME, ATUALIZA REFERÊNCIAS
// ==========================================

if (
    $nomeAnterior !== '' &&
    $nomeAnterior !== $nome
) {

    // ======================================
    // POMODORO
    // ======================================

    $arquivoPomodoro =
        $pastaUsuario .
        '/pomodoro.json';

    if (file_exists($arquivoPomodoro)) {

        $pomodoro =
            json_decode(
                file_get_contents(
                    $arquivoPomodoro
                ),
                true
            );

        if (
            is_array($pomodoro) &&
            isset($pomodoro['sessions']) &&
            is_array($pomodoro['sessions'])
        ) {

            foreach (
                $pomodoro['sessions']
                as &$sessao
            ) {

                foreach (
                    [
                        'discipline',
                        'disciplina',
                        'materia'
                    ]
                    as $campo
                ) {

                    if (
                        isset(
                            $sessao[$campo]
                        ) &&
                        trim(
                            (string)
                            $sessao[$campo]
                        ) ===
                        $nomeAnterior
                    ) {

                        $sessao[$campo] =
                            $nome;

                    }

                }

            }

            unset($sessao);

            file_put_contents(
                $arquivoPomodoro,
                json_encode(
                    $pomodoro,
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                ),
                LOCK_EX
            );

        }

    }

    // ======================================
    // FLASHCARDS
    // ======================================

    $arquivoFlashcards =
        $pastaUsuario .
        '/flashcards.json';

    if (file_exists($arquivoFlashcards)) {

        $flashcards =
            json_decode(
                file_get_contents(
                    $arquivoFlashcards
                ),
                true
            );

        if (
            is_array($flashcards) &&
            isset($flashcards['baralhos']) &&
            is_array($flashcards['baralhos'])
        ) {

            foreach (
                $flashcards['baralhos']
                as &$baralho
            ) {

                if (
                    isset(
                        $baralho['materia']
                    ) &&
                    trim(
                        (string)
                        $baralho['materia']
                    ) ===
                    $nomeAnterior
                ) {

                    $baralho['materia'] =
                        $nome;

                }

            }

            unset($baralho);

            file_put_contents(
                $arquivoFlashcards,
                json_encode(
                    $flashcards,
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                ),
                LOCK_EX
            );

        }

    }

}

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Matéria atualizada com sucesso.',
    'materia' => $materiaAtual
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);