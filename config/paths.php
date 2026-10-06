<?php

require_once __DIR__ . '/app.php';

/*
|--------------------------------------------------------------------------
| FOAG — CAMINHOS FÍSICOS
|--------------------------------------------------------------------------
*/

if (!defined('FOAG_JSON_DIR')) {
    define('FOAG_JSON_DIR', FOAG_ROOT . '/json');
}

if (!defined('FOAG_USUARIOS_DIR')) {
    define('FOAG_USUARIOS_DIR', FOAG_JSON_DIR . '/usuarios');
}

if (!defined('FOAG_LOGIN_DATA_DIR')) {
    define('FOAG_LOGIN_DATA_DIR', FOAG_JSON_DIR . '/usuario_login');
}

if (!defined('FOAG_IMG_DIR')) {
    define('FOAG_IMG_DIR', FOAG_ROOT . '/img');
}

if (!defined('FOAG_PERFIL_IMG_DIR')) {
    define('FOAG_PERFIL_IMG_DIR', FOAG_IMG_DIR . '/perfil');
}

function foag_path(string $caminho = ''): string
{
    $caminho = ltrim(str_replace('\\', '/', $caminho), '/');

    return $caminho === ''
        ? FOAG_ROOT
        : FOAG_ROOT . '/' . $caminho;
}
