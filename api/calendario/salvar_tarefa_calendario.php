<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(true);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function responder($dados, $status = 200)
{
    http_response_code($status);

    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


// ==========================================
// USUÁRIO
// ==========================================

if (!$codigoUsuario) {

    responder(
        [
            'ok' => false,
            'mensagem' => 'Usuário não autenticado.'
        ],
        401
    );
}


// ==========================================
// MÉTODO
// ==========================================

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {

    responder(
        [
            'ok' => false,
            'mensagem' => 'Método inválido.'
        ],
        405
    );
}


// ==========================================
// RECEBER DADOS
// ==========================================

$conteudo =
    file_get_contents(
        'php://input'
    );

$dados =
    json_decode(
        $conteudo ?: '',
        true
    );

if (!is_array($dados)) {

    responder(
        [
            'ok' => false,
            'mensagem' => 'JSON inválido.'
        ],
        400
    );
}


$data =
    trim(
        (string)(
            $dados['data']
            ?? ''
        )
    );

$texto =
    trim(
        (string)(
            $dados['texto']
            ?? ''
        )
    );


// ==========================================
// VALIDAR DATA
// ==========================================

if (
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $data
    )
) {

    responder(
        [
            'ok' => false,
            'mensagem' => 'Data inválida.'
        ],
        400
    );
}


// ==========================================
// ARQUIVO DO USUÁRIO
// ==========================================

$pastaUsuario = foag_pasta_usuario($codigoUsuario);

if (!is_dir($pastaUsuario)) {

    responder(
        [
            'ok' => false,
            'mensagem' =>
                'Pasta do usuário não encontrada.'
        ],
        404
    );
}


$arquivoAgenda =
    $pastaUsuario
    . '/agenda.json';


// ==========================================
// ABRIR ARQUIVO COM BLOQUEIO
// ==========================================

$arquivo =
    fopen(
        $arquivoAgenda,
        'c+'
    );

if (!$arquivo) {

    responder(
        [
            'ok' => false,
            'mensagem' =>
                'Não foi possível abrir agenda.json.'
        ],
        500
    );
}


if (
    !flock(
        $arquivo,
        LOCK_EX
    )
) {

    fclose(
        $arquivo
    );

    responder(
        [
            'ok' => false,
            'mensagem' =>
                'Não foi possível bloquear agenda.json.'
        ],
        500
    );
}


// ==========================================
// LER VERSÃO MAIS RECENTE DA AGENDA
// ==========================================

rewind(
    $arquivo
);

$conteudoAtual =
    stream_get_contents(
        $arquivo
    );

$agenda =
    json_decode(
        $conteudoAtual ?: '',
        true
    );


if (
    !is_array($agenda)
) {

    $agenda = [
        'notas' => [],
        'tarefas' => [],
        'nao_esquecer' => []
    ];
}


// ==========================================
// GARANTIR ESTRUTURA
// ==========================================

if (
    !isset($agenda['notas']) ||
    !is_array($agenda['notas'])
) {

    $agenda['notas'] = [];
}


if (
    !isset($agenda['tarefas']) ||
    !is_array($agenda['tarefas'])
) {

    $agenda['tarefas'] = [];
}


if (
    !isset($agenda['nao_esquecer']) ||
    !is_array($agenda['nao_esquecer'])
) {

    $agenda['nao_esquecer'] = [];
}


// ==========================================
// REMOVER SOMENTE A TAREFA DO CALENDÁRIO
// DA DATA QUE ESTÁ SENDO EDITADA
// ==========================================

$agenda['tarefas'] =
    array_values(
        array_filter(
            $agenda['tarefas'],

            function ($tarefa) use ($data) {

                if (!is_array($tarefa)) {
                    return true;
                }

                $dataTarefa =
                    $tarefa['data']
                    ?? $tarefa['date']
                    ?? '';

                $origem =
                    $tarefa['origem']
                    ?? '';

                /*
                 * Remove SOMENTE:
                 *
                 * tarefa criada pelo calendário
                 * +
                 * nesta mesma data.
                 *
                 * Tarefas criadas pela Agenda
                 * nunca são apagadas aqui.
                 */
                if (
                    $dataTarefa === $data &&
                    $origem === 'calendario'
                ) {
                    return false;
                }

                return true;
            }
        )
    );


// ==========================================
// ADICIONAR NOVA TAREFA
// ==========================================

if ($texto !== '') {

    $agenda['tarefas'][] = [

        'id' =>
            'cal_'
            . str_replace(
                '-',
                '',
                $data
            ),

        'texto' =>
            $texto,

        'data' =>
            $data,

        'materia_id' =>
            '',

        'materia_nome' =>
            '',

        'concluida' =>
            false,

        'origem' =>
            'calendario'
    ];
}


// ==========================================
// GERAR JSON
// ==========================================

$json =
    json_encode(
        $agenda,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

if ($json === false) {

    flock(
        $arquivo,
        LOCK_UN
    );

    fclose(
        $arquivo
    );

    responder(
        [
            'ok' => false,
            'mensagem' =>
                'Erro ao gerar agenda.json.'
        ],
        500
    );
}


// ==========================================
// SOBRESCREVER COM A VERSÃO ATUALIZADA
// ==========================================

rewind(
    $arquivo
);

ftruncate(
    $arquivo,
    0
);

$salvou =
    fwrite(
        $arquivo,
        $json
    );

fflush(
    $arquivo
);

flock(
    $arquivo,
    LOCK_UN
);

fclose(
    $arquivo
);


if ($salvou === false) {

    responder(
        [
            'ok' => false,
            'mensagem' =>
                'Não foi possível salvar a tarefa.'
        ],
        500
    );
}


// ==========================================
// SUCESSO
// ==========================================

responder([
    'ok' => true,

    'mensagem' =>
        $texto !== ''
            ? 'Tarefa salva com sucesso.'
            : 'Tarefa removida com sucesso.',

    'tarefas' =>
        $agenda['tarefas']
]);