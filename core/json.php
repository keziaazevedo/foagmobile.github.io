<?php

require_once __DIR__ . '/../config/bootstrap.php';

/*
|--------------------------------------------------------------------------
| FOAG — FUNÇÕES JSON
|--------------------------------------------------------------------------
| Utilitários genéricos. Cada módulo pode continuar mantendo suas funções
| específicas quando possuir validações próprias.
*/

function foag_ler_json(
    string $arquivo,
    array $padrao = [],
    bool $criarSeNaoExistir = false
): array {
    if (!file_exists($arquivo)) {
        if ($criarSeNaoExistir) {
            foag_salvar_json($arquivo, $padrao);
        }

        return $padrao;
    }

    $conteudo = file_get_contents($arquivo);

    if ($conteudo === false || trim($conteudo) === '') {
        return $padrao;
    }

    $dados = json_decode($conteudo, true);

    return is_array($dados) ? $dados : $padrao;
}

function foag_salvar_json(
    string $arquivo,
    array $dados,
    int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
): bool {
    $pasta = dirname($arquivo);

    if (!is_dir($pasta) && !mkdir($pasta, 0777, true)) {
        return false;
    }

    $json = json_encode($dados, $flags);

    if ($json === false) {
        return false;
    }

    return file_put_contents($arquivo, $json, LOCK_EX) !== false;
}

function foag_arquivo_usuario(
    string $pastaUsuario,
    string $nomeArquivo
): string {
    return rtrim($pastaUsuario, '/\\') . '/' . ltrim($nomeArquivo, '/\\');
}
