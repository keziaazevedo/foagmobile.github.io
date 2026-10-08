<?php

require_once __DIR__ . '/../config/bootstrap.php';

/*
|--------------------------------------------------------------------------
| FOAG — HELPERS PARA ENDPOINTS
|--------------------------------------------------------------------------
| Helpers opcionais. Não impõem um formato único de resposta para preservar
| os contratos atuais de cada módulo.
*/

function foag_responder_json(
    array $dados,
    int $status = 200,
    int $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, $flags);
    exit;
}

function foag_metodo_atual(): string
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? ''));
}

function foag_ler_corpo_json(): ?array
{
    $conteudo = file_get_contents('php://input');

    if ($conteudo === false || trim($conteudo) === '') {
        return null;
    }

    $dados = json_decode($conteudo, true);

    return is_array($dados) ? $dados : null;
}
