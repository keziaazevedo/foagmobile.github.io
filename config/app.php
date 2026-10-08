<?php

/*
|--------------------------------------------------------------------------
| FOAG — CONFIGURAÇÃO PRINCIPAL
|--------------------------------------------------------------------------
| Centraliza a raiz física e a URL base do projeto.
*/

if (!defined('FOAG_ROOT')) {
    define('FOAG_ROOT', dirname(__DIR__));
}

if (!defined('FOAG_BASE_URL')) {
    $baseUrl = getenv('FOAG_BASE_URL');

    if ($baseUrl === false || trim($baseUrl) === '') {
        $baseUrl = '/foagmobile.github.io';
    }

    define('FOAG_BASE_URL', rtrim($baseUrl, '/'));
}
