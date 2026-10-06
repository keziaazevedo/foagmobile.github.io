<?php

require_once __DIR__ . '/../../core/usuario.php';
require_once __DIR__ . '/../../core/json.php';

$codigoUsuario = foag_codigo_usuario(true);
header('Content-Type: application/json; charset=utf-8');

// ==========================================
// CONFIGURAÇÃO DE ERROS
// ==========================================

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);


// ==========================================
// FUNÇÃO DE RESPOSTA
// ==========================================

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
// 1. VERIFICAR USUÁRIO
// ==========================================

// O FOAG atualmente usa codigo_usuario.
// user_id fica como compatibilidade caso alguma página antiga ainda use.

if (!$codigoUsuario) {

    responder([
        'ok' => false,
        'erro' => 'USUARIO_NAO_LOGADO',
        'mensagem' => 'Usuário não autenticado.'
    ], 401);
}


// ==========================================
// 2. VERIFICAR MÉTODO
// ==========================================

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    !== 'POST'
) {

    responder([
        'ok' => false,
        'erro' => 'METODO_INVALIDO',
        'mensagem' => 'Use uma requisição POST.'
    ], 405);
}


// ==========================================
// 3. PASTA DO USUÁRIO
// ==========================================

$baseJsonDir = foag_base_usuarios();

$pastaUsuario = foag_pasta_usuario($codigoUsuario);


// Cria a pasta caso ainda não exista

if (!is_dir($pastaUsuario)) {

    $criou = @mkdir(
        $pastaUsuario,
        0755,
        true
    );

    if (
        !$criou &&
        !is_dir($pastaUsuario)
    ) {

        responder([
            'ok' => false,
            'erro' => 'ERRO_CRIAR_PASTA',
            'mensagem' =>
                'Não foi possível criar a pasta do usuário.'
        ], 500);
    }
}


// ==========================================
// 4. LER DADOS RECEBIDOS
// ==========================================

$raw =
    file_get_contents('php://input');

$dados = null;


// Primeiro tenta JSON

if (
    $raw !== false &&
    trim($raw) !== ''
) {

    $dados =
        json_decode(
            $raw,
            true
        );


    if (
        json_last_error()
        !== JSON_ERROR_NONE
    ) {

        responder([
            'ok' => false,
            'erro' => 'JSON_INVALIDO',
            'mensagem' =>
                'Os dados enviados estão em formato JSON inválido.'
        ], 400);
    }
}


// ==========================================
// COMPATIBILIDADE COM POST NORMAL
// ==========================================

if (
    $dados === null &&
    !empty($_POST)
) {

    $dados = $_POST;
}


if (!is_array($dados)) {

    responder([
        'ok' => false,
        'erro' => 'DADOS_INVALIDOS',
        'mensagem' =>
            'Nenhum dado válido foi recebido.'
    ], 400);
}


// ==========================================
// 5. IDENTIFICAR O QUE ESTÁ SENDO SALVO
// ==========================================

/*
|--------------------------------------------------------------------------
| HORÁRIO
|--------------------------------------------------------------------------
|
| O JavaScript do horário envia:
|
| {
|     "html": "..."
| }
|
*/

$ehHorario =
    array_key_exists(
        'html',
        $dados
    )
    &&
    !array_key_exists(
        'tarefas',
        $dados
    )
    &&
    !array_key_exists(
        'notas',
        $dados
    );


// ==========================================
// SALVAR HORÁRIO
// ==========================================

if ($ehHorario) {

    $html =
        isset($dados['html'])
            ? (string) $dados['html']
            : '';


    $dadosHorario = [
        'html' => $html
    ];


    $arquivoHorario =
        $pastaUsuario
        . '/horario.json';


    $jsonHorario =
        json_encode(
            $dadosHorario,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );


    if ($jsonHorario === false) {

        responder([
            'ok' => false,
            'erro' => 'ERRO_JSON_HORARIO',
            'mensagem' =>
                'Não foi possível preparar os dados do horário.'
        ], 500);
    }


    $resultado =
        @file_put_contents(
            $arquivoHorario,
            $jsonHorario,
            LOCK_EX
        );


    if ($resultado === false) {

        responder([
            'ok' => false,
            'erro' => 'ERRO_SALVAR_HORARIO',
            'mensagem' =>
                'Não foi possível salvar o horário.'
        ], 500);
    }


    responder([
        'ok' => true,
        'tipo' => 'horario',
        'mensagem' =>
            'Horário salvo com sucesso.'
    ]);
}


// ==========================================
// 6. SALVAR AGENDA
// ==========================================

/*
|--------------------------------------------------------------------------
| Estrutura da Agenda
|--------------------------------------------------------------------------
|
| {
|     "notas": [],
|     "tarefas": [],
|     "nao_esquecer": []
| }
|
*/


// Garante as estruturas principais

if (
    !isset($dados['notas']) ||
    !is_array($dados['notas'])
) {
    $dados['notas'] = [];
}


if (
    !isset($dados['tarefas']) ||
    !is_array($dados['tarefas'])
) {
    $dados['tarefas'] = [];
}


if (
    !isset($dados['nao_esquecer']) ||
    !is_array($dados['nao_esquecer'])
) {
    $dados['nao_esquecer'] = [];
}


// ==========================================
// ARQUIVO DA AGENDA
// ==========================================

$arquivoAgenda =
    $pastaUsuario
    . '/agenda.json';


$jsonAgenda =
    json_encode(
        $dados,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


if ($jsonAgenda === false) {

    responder([
        'ok' => false,
        'erro' => 'ERRO_JSON_AGENDA',
        'mensagem' =>
            'Não foi possível preparar os dados da Agenda.'
    ], 500);
}


// ==========================================
// GRAVAR AGENDA
// ==========================================

$resultado =
    @file_put_contents(
        $arquivoAgenda,
        $jsonAgenda,
        LOCK_EX
    );


if ($resultado === false) {

    responder([
        'ok' => false,
        'erro' => 'ERRO_SALVAR_AGENDA',
        'mensagem' =>
            'Não foi possível salvar a Agenda.'
    ], 500);
}


// ==========================================
// SUCESSO
// ==========================================

responder([
    'ok' => true,
    'tipo' => 'agenda',
    'mensagem' =>
        'Agenda salva com sucesso.'
]);