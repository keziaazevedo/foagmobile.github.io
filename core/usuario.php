<?php

require_once __DIR__ . '/../config/bootstrap.php';

/*
|--------------------------------------------------------------------------
| FOAG — CONTEXTO DO USUÁRIO
|--------------------------------------------------------------------------
| Centraliza sessão, código do usuário e caminho da pasta individual.
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


function foag_codigo_usuario(bool $aceitarLegado = false): ?string
{
    $codigo = $_SESSION['codigo_usuario'] ?? null;

    if (($codigo === null || $codigo === '') && $aceitarLegado) {
        $codigo = $_SESSION['user_id'] ?? null;
    }

    if ($codigo === null || $codigo === '') {
        return null;
    }

    return (string) $codigo;
}

function foag_exigir_usuario(
    ?string $loginUrl = null,
    bool $aceitarLegado = false
): string {
    $codigo = foag_codigo_usuario($aceitarLegado);

    if ($codigo === null) {
        header('Location: ' . ($loginUrl ?? FOAG_LOGIN_URL));
        exit;
    }

    return $codigo;
}

function foag_base_usuarios(): string
{
    return FOAG_USUARIOS_DIR;
}

function foag_pasta_usuario(
    string $codigoUsuario,
    bool $criar = false,
    int $permissoes = 0777
): string {
    $pasta = foag_base_usuarios() . '/' . $codigoUsuario;

    if (!is_dir($pasta) && $criar) {
        mkdir($pasta, $permissoes, true);
    }

    return $pasta;
}

function foag_contexto_usuario(
    ?string $loginUrl = null,
    bool $criarPasta = false,
    bool $aceitarLegado = false,
    int $permissoes = 0777
): array {
    $codigoUsuario = foag_exigir_usuario(
        $loginUrl,
        $aceitarLegado
    );

    $baseJsonDir = foag_base_usuarios();
    $pastaUsuario = foag_pasta_usuario(
        $codigoUsuario,
        $criarPasta,
        $permissoes
    );

    return [
        'codigoUsuario' => $codigoUsuario,
        'baseJsonDir' => $baseJsonDir,
        'pastaUsuario' => $pastaUsuario,
    ];
}
