<?php

require_once __DIR__ . '/app.php';

/*
|--------------------------------------------------------------------------
| FOAG — URLs
|--------------------------------------------------------------------------
*/

function foag_url(string $caminho = ''): string
{
    $caminho = ltrim($caminho, '/');

    return $caminho === ''
        ? FOAG_BASE_URL
        : FOAG_BASE_URL . '/' . $caminho;
}

if (!defined('FOAG_LOGIN_URL')) {
    define('FOAG_LOGIN_URL', foag_url('login/index.php'));
}

if (!defined('FOAG_HOME_URL')) {
    define('FOAG_HOME_URL', foag_url('inicioo/inicio.php'));
}

if (!defined('FOAG_LOGOUT_URL')) {
    define('FOAG_LOGOUT_URL', foag_url('api/auth/logout.php'));
}
