<?php
session_start();

/*
|--------------------------------------------------------------------------
| Verificar login
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['codigo_usuario'])) {
    header('Location: ../login/index.php');
    exit;
}

$codigoUsuario = $_SESSION['codigo_usuario'];

/*
|--------------------------------------------------------------------------
| Caminhos
|--------------------------------------------------------------------------
*/

$pastaUsuario = __DIR__
    . '/../json/usuarios/'
    . $codigoUsuario;

$caminhoPerfil = $pastaUsuario . '/perfil.json';

$pastaLogin = __DIR__ . '/../json/usuario_login';

$pasta_fotos = __DIR__ . '/../img/perfil/';
$pasta_fotos_url = '../img/perfil/';
$foto_padrao = 'foto_padrao.png';

$escolas_json = __DIR__ . '/../json/escolas.json';
$series_json = __DIR__ . '/../json/series.json';
$cidades_json = __DIR__ . '/../json/cidades.json';

/*
|--------------------------------------------------------------------------
| Funções
|--------------------------------------------------------------------------
*/

function escapar($valor)
{
    return htmlspecialchars(
        $valor ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

function carregarLista($caminho)
{
    if (!file_exists($caminho)) {
        return [];
    }

    $conteudo = file_get_contents($caminho);

    if ($conteudo === false) {
        return [];
    }

    $lista = json_decode(
        $conteudo,
        true
    );

    return is_array($lista)
        ? $lista
        : [];
}

function salvarJson($caminho, $dados)
{
    $json = json_encode(
        $dados,
        JSON_PRETTY_PRINT
        | JSON_UNESCAPED_UNICODE
    );

    if ($json === false) {
        return false;
    }

    return file_put_contents(
        $caminho,
        $json,
        LOCK_EX
    ) !== false;
}

/*
|--------------------------------------------------------------------------
| Verificar se cidade/estado existem no cidades.json
|--------------------------------------------------------------------------
*/

function localidadeValida(
    $cidade,
    $estado,
    $opcoesCidades
) {
    foreach ($opcoesCidades as $opcao) {

        $cidadeOpcao =
            trim($opcao['cidade'] ?? '');

        $estadoOpcao =
            trim($opcao['estado'] ?? '');

        if (
            $cidadeOpcao === $cidade
            &&
            $estadoOpcao === $estado
        ) {
            return true;
        }
    }

    return false;
}

/*
|--------------------------------------------------------------------------
| Transformar nome em nome de arquivo
|--------------------------------------------------------------------------
*/

function criarNomeArquivoUsuario($nome)
{
    $nome = trim($nome);

    $nome = preg_replace(
        '/\s+/u',
        ' ',
        $nome
    );

    if (function_exists('mb_strtolower')) {

        $nome = mb_strtolower(
            $nome,
            'UTF-8'
        );

    } else {

        $nome = strtolower($nome);
    }

    $acentos = [
        'á' => 'a',
        'à' => 'a',
        'ã' => 'a',
        'â' => 'a',
        'ä' => 'a',

        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',

        'í' => 'i',
        'ì' => 'i',
        'î' => 'i',
        'ï' => 'i',

        'ó' => 'o',
        'ò' => 'o',
        'õ' => 'o',
        'ô' => 'o',
        'ö' => 'o',

        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',

        'ç' => 'c',
        'ñ' => 'n'
    ];

    $nome = strtr(
        $nome,
        $acentos
    );

    $nome = preg_replace(
        '/[^a-z0-9]+/',
        '_',
        $nome
    );

    $nome = trim(
        $nome,
        '_'
    );

    if ($nome === '') {
        return 'Usuario';
    }

    return ucfirst($nome);
}

/*
|--------------------------------------------------------------------------
| Atualizar nome no arquivo de login
|--------------------------------------------------------------------------
*/

function atualizarArquivoLogin(
    $pastaLogin,
    $codigoUsuario,
    $novoNome
) {
    if (!is_dir($pastaLogin)) {
        return;
    }

    $arquivos = glob(
        $pastaLogin . '/*.json'
    ) ?: [];

    foreach ($arquivos as $arquivoLogin) {

        $conteudo = file_get_contents(
            $arquivoLogin
        );

        if ($conteudo === false) {
            continue;
        }

        $dadosLogin = json_decode(
            $conteudo,
            true
        );

        if (!is_array($dadosLogin)) {
            continue;
        }

        if (
            ($dadosLogin['codigo_usuario'] ?? '')
            !== $codigoUsuario
        ) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Atualizar nome no JSON de login
        |--------------------------------------------------------------------------
        */

        $dadosLogin['nome'] =
            $novoNome;

        salvarJson(
            $arquivoLogin,
            $dadosLogin
        );

        /*
        |--------------------------------------------------------------------------
        | Renomear arquivo de login
        |--------------------------------------------------------------------------
        */

        $novoNomeArquivo =
            criarNomeArquivoUsuario(
                $novoNome
            );

        $novoCaminho =
            $pastaLogin
            . '/'
            . $novoNomeArquivo
            . '.json';

        if (
            file_exists($novoCaminho)
            &&
            realpath($novoCaminho)
            !== realpath($arquivoLogin)
        ) {

            $novoCaminho =
                $pastaLogin
                . '/'
                . $novoNomeArquivo
                . '_'
                . $codigoUsuario
                . '.json';
        }

        if (
            $arquivoLogin
            !== $novoCaminho
        ) {
            @rename(
                $arquivoLogin,
                $novoCaminho
            );
        }

        break;
    }
}

/*
|--------------------------------------------------------------------------
| Carregar listas
|--------------------------------------------------------------------------
*/

$opcoes_escolas = carregarLista(
    $escolas_json
);

$opcoes_series = carregarLista(
    $series_json
);

$opcoes_cidades = carregarLista(
    $cidades_json
);

/*
|--------------------------------------------------------------------------
| Verificar pasta e perfil
|--------------------------------------------------------------------------
*/

if (!is_dir($pastaUsuario)) {

    exit(
        'Pasta do usuário não encontrada.'
    );
}

if (!file_exists($caminhoPerfil)) {

    exit(
        'Perfil do usuário não encontrado.'
    );
}

$conteudoPerfil =
    file_get_contents(
        $caminhoPerfil
    );

if ($conteudoPerfil === false) {

    exit(
        'Não foi possível carregar o perfil.'
    );
}

$usuario = json_decode(
    $conteudoPerfil,
    true
);

if (!is_array($usuario)) {

    exit(
        'Os dados do perfil estão inválidos.'
    );
}

/*
|--------------------------------------------------------------------------
| Garantir opção atual de série
|--------------------------------------------------------------------------
*/

if (
    !empty($usuario['serie'])
    &&
    !in_array(
        $usuario['serie'],
        $opcoes_series,
        true
    )
) {

    array_unshift(
        $opcoes_series,
        $usuario['serie']
    );
}

/*
|--------------------------------------------------------------------------
| Garantir opção atual de escola
|--------------------------------------------------------------------------
*/

if (
    !empty($usuario['escola'])
    &&
    !in_array(
        $usuario['escola'],
        $opcoes_escolas,
        true
    )
) {

    array_unshift(
        $opcoes_escolas,
        $usuario['escola']
    );
}

$mensagem_erro = '';

/*
|--------------------------------------------------------------------------
| Salvar alterações
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Receber dados
    |--------------------------------------------------------------------------
    */

    $nome = trim(
        $_POST['nome'] ?? ''
    );

    $nascimento = trim(
        $_POST['nascimento'] ?? ''
    );

    $telefone = trim(
        $_POST['telefone'] ?? ''
    );

    $serie = trim(
        $_POST['serie'] ?? ''
    );

    $escola = trim(
        $_POST['escola'] ?? ''
    );

    $localidade = trim(
        $_POST['localidade'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | Separar cidade e estado
    |--------------------------------------------------------------------------
    |
    | O select envia, por exemplo:
    |
    | Cuiabá|MT
    |
    */

    $cidade = '';
    $estado = '';

    if ($localidade !== '') {

        $partesLocalidade = explode(
            '|',
            $localidade,
            2
        );

        $cidade = trim(
            $partesLocalidade[0] ?? ''
        );

        $estado = strtoupper(
            trim(
                $partesLocalidade[1] ?? ''
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validação
    |--------------------------------------------------------------------------
    */

    if ($nome === '') {

        $mensagem_erro =
            'Informe o nome.';

    } elseif ($nascimento === '') {

        $mensagem_erro =
            'Informe a data de nascimento.';

    } elseif ($serie === '') {

        $mensagem_erro =
            'Selecione a série ou curso.';

    } elseif ($escola === '') {

        $mensagem_erro =
            'Selecione a escola ou faculdade.';

    } elseif ($localidade === '') {

        $mensagem_erro =
            'Selecione sua cidade.';

    } elseif (
        $cidade === ''
        ||
        $estado === ''
    ) {

        $mensagem_erro =
            'A cidade selecionada é inválida.';

    } elseif (
        !localidadeValida(
            $cidade,
            $estado,
            $opcoes_cidades
        )
    ) {

        $mensagem_erro =
            'Selecione uma cidade disponível na lista.';
    }

    /*
    |--------------------------------------------------------------------------
    | Foto atual
    |--------------------------------------------------------------------------
    */

    $foto_salva =
        $usuario['foto']
        ?? $foto_padrao;

    /*
    |--------------------------------------------------------------------------
    | Upload da foto
    |--------------------------------------------------------------------------
    */

    if (
        $mensagem_erro === ''
        &&
        isset($_FILES['foto'])
        &&
        $_FILES['foto']['error']
            !== UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES['foto']['error']
            !== UPLOAD_ERR_OK
        ) {

            $mensagem_erro =
                'Ocorreu um erro ao enviar a foto.';

        } elseif (
            $_FILES['foto']['size']
            > 5 * 1024 * 1024
        ) {

            $mensagem_erro =
                'A foto deve ter no máximo 5 MB.';

        } else {

            $extensao = strtolower(
                pathinfo(
                    $_FILES['foto']['name'],
                    PATHINFO_EXTENSION
                )
            );

            $extensoesPermitidas = [
                'jpg',
                'jpeg',
                'png',
                'webp',
                'gif'
            ];

            if (
                !in_array(
                    $extensao,
                    $extensoesPermitidas,
                    true
                )
            ) {

                $mensagem_erro =
                    'Escolha uma imagem JPG, PNG, WEBP ou GIF.';

            } elseif (
                getimagesize(
                    $_FILES['foto']['tmp_name']
                ) === false
            ) {

                $mensagem_erro =
                    'O arquivo escolhido não é uma imagem válida.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Criar pasta de fotos se necessário
                |--------------------------------------------------------------------------
                */

                if (!is_dir($pasta_fotos)) {

                    mkdir(
                        $pasta_fotos,
                        0775,
                        true
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Nome da nova foto
                |--------------------------------------------------------------------------
                */

                $novo_nome =
                    'perfil_'
                    . $codigoUsuario
                    . '_'
                    . bin2hex(
                        random_bytes(4)
                    )
                    . '.'
                    . $extensao;

                $destino =
                    $pasta_fotos
                    . $novo_nome;

                /*
                |--------------------------------------------------------------------------
                | Salvar foto
                |--------------------------------------------------------------------------
                */

                if (
                    move_uploaded_file(
                        $_FILES['foto']['tmp_name'],
                        $destino
                    )
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Apagar foto antiga
                    |--------------------------------------------------------------------------
                    */

                    $foto_antiga =
                        basename(
                            $foto_salva
                        );

                    $caminho_antigo =
                        $pasta_fotos
                        . $foto_antiga;

                    if (
                        $foto_antiga
                        !== $foto_padrao
                        &&
                        is_file(
                            $caminho_antigo
                        )
                    ) {

                        unlink(
                            $caminho_antigo
                        );
                    }

                    $foto_salva =
                        $novo_nome;

                } else {

                    $mensagem_erro =
                        'Não foi possível salvar a nova foto.';
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Atualizar perfil.json
    |--------------------------------------------------------------------------
    */

    if ($mensagem_erro === '') {

        $usuario['nome'] =
            $nome;

        $usuario['nascimento'] =
            $nascimento;

        $usuario['telefone'] =
            $telefone;

        $usuario['serie'] =
            $serie;

        $usuario['escola'] =
            $escola;

        /*
        |--------------------------------------------------------------------------
        | Localização
        |--------------------------------------------------------------------------
        */

        $usuario['cidade'] =
            $cidade;

        $usuario['estado'] =
            $estado;

        /*
        |--------------------------------------------------------------------------
        | Foto
        |--------------------------------------------------------------------------
        */

        $usuario['foto'] =
            $foto_salva;

        /*
        |--------------------------------------------------------------------------
        | Garantir código do usuário
        |--------------------------------------------------------------------------
        */

        $usuario['codigo_usuario'] =
            $codigoUsuario;

        /*
        |--------------------------------------------------------------------------
        | Salvar JSON
        |--------------------------------------------------------------------------
        */

        if (
            !salvarJson(
                $caminhoPerfil,
                $usuario
            )
        ) {

            $mensagem_erro =
                'Não foi possível salvar as alterações.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Atualizar sessão
            |--------------------------------------------------------------------------
            */

            $_SESSION['user_nome'] =
                $nome;

            $_SESSION['usuario'] =
                $nome;

            /*
            |--------------------------------------------------------------------------
            | Atualizar arquivo de login
            |--------------------------------------------------------------------------
            */

            atualizarArquivoLogin(
                $pastaLogin,
                $codigoUsuario,
                $nome
            );

            /*
            |--------------------------------------------------------------------------
            | Voltar para perfil
            |--------------------------------------------------------------------------
            */

            header(
                'Location: perfil.php'
            );

            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Manter valores digitados em caso de erro
    |--------------------------------------------------------------------------
    */

    $usuario['nome'] =
        $nome;

    $usuario['nascimento'] =
        $nascimento;

    $usuario['telefone'] =
        $telefone;

    $usuario['serie'] =
        $serie;

    $usuario['escola'] =
        $escola;

    $usuario['cidade'] =
        $cidade;

    $usuario['estado'] =
        $estado;
}

/*
|--------------------------------------------------------------------------
| Foto exibida
|--------------------------------------------------------------------------
*/

$foto_perfil =
    $foto_padrao;

if (!empty($usuario['foto'])) {

    $arquivo_foto =
        basename(
            $usuario['foto']
        );

    if (
        is_file(
            $pasta_fotos
            . $arquivo_foto
        )
    ) {

        $foto_perfil =
            $arquivo_foto;
    }
}

$caminho_foto =
    $pasta_fotos_url
    . $foto_perfil;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOAG - Editar Perfil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="../m.escuro/dark_basee.css">
    <link rel="stylesheet" href="editr.css">
    <script src="../m.escuro/dark-mode.js"></script>
    <link rel="stylesheet" href="../global/css/cursor.css">
    <link rel="stylesheet" href="../global/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/forms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/tables.css?v=<?= time() ?>">
</head>

<body>
    <header class="cabecalho">
        FOAG

        <div class="header-icons">
            <i id="themeToggle" class="fa-solid fa-moon" title="Modo Escuro"></i>
            <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
            <i id="icon-fogi" class="fa-solid fa-robot" title="Assistente FOGi"></i>
            <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
        </div>
    </header>

    <div class="container">
        <nav class="menu">
            <a href="../inicioo/inicio.php">
                <i class="fa-solid fa-house"></i>
                Início
            </a>

            <a href="../calend/calendario.php">
                <i class="fa-solid fa-calendar-days"></i>
                Calendário
            </a>

            <a href="../bloco/agenda.php">
                <i class="fa-solid fa-book"></i>
                Agenda
            </a>

            <a href="../pomodoro/pomodoro.php">
                <i class="fa-solid fa-stopwatch"></i>
                Pomodoro
            </a>

            <a href="../notas/notas.php">
                <i class="fa-solid fa-check-double"></i>
                Boletim
            </a>

            <a href="../loja/loja.php">
                <i class="fa-solid fa-store"></i>
                Loja
            </a>

            <a href="../rank/rank.php">
                <i class="fa-solid fa-trophy"></i>
                Ranking
            </a>
        </nav>

        <main class="conteudo">
            <div class="editar-wrapper">
                <div class="titulo-pagina">
                    <div>
                        <span>Configurações da conta</span>
                        <h1>Editar perfil</h1>
                    </div>

                    <a href="perfil.php" class="btn-voltar">
                        <i class="fa-solid fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>

                <?php if ($mensagem_erro !== ""): ?>
                    <div class="mensagem-erro">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?= escapar($mensagem_erro) ?>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <section class="foto-card">
<!-- NO EDITAR.PHP - Substitua o .foto-area -->
<div class="foto-area">
    <div class="moldura-container">
        <div class="moldura-borda" id="molduraEditar">
            <img id="previewFoto" src="<?= escapar($caminho_foto) ?>" alt="Foto de perfil">
        </div>
        <label for="foto" class="btn-camera">
            <i class="fa-solid fa-camera"></i>
        </label>
    </div>
</div>

                        <div class="foto-informacoes">
                            <span>Foto de perfil</span>
                            <h2><?= escapar($usuario["nome"] ?? "Usuário FOAG") ?></h2>
                            <p>Escolha uma imagem de até 5 MB.</p>

                            <label for="foto" class="btn-escolher">
                                <i class="fa-regular fa-image"></i>
                                Escolher foto
                            </label>

                            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>

                        <div class="decoracao">
                            <div class="circulo-decoracao"></div>

                            <div class="icone-decoracao">
                                <i class="fa-solid fa-user-pen"></i>
                            </div>
                        </div>
                    </section>

                    <section class="formulario-card">
                        <div class="formulario-titulo">
                            <div class="formulario-icone">
                                <i class="fa-regular fa-address-card"></i>
                            </div>

                            <div>
                                <h3>Informações do perfil</h3>
                                <p>Atualize os dados cadastrados.</p>
                            </div>
                        </div>

                        <div class="campos-grid">
                            <div class="campo campo-nome">
                                <label for="nome">Nome completo</label>

                                <div class="input-area">
                                    <i class="fa-regular fa-user"></i>
                                    <input type="text" id="nome" name="nome" value="<?= escapar($usuario["nome"] ?? "") ?>" required>
                                </div>
                            </div>

                            <div class="campo">
                                <label for="nascimento">Data de nascimento</label>

                                <div class="input-area">
                                    <i class="fa-regular fa-calendar"></i>
                                    <input type="date" id="nascimento" name="nascimento" value="<?= escapar($usuario["nascimento"] ?? "") ?>" required>
                                </div>
                            </div>

                            <div class="campo">
                                <label for="telefone">Telefone</label>

                                <div class="input-area">
                                    <i class="fa-solid fa-phone"></i>
                                    <input type="text" id="telefone" name="telefone" value="<?= escapar($usuario["telefone"] ?? "") ?>" placeholder="(00) 00000-0000" maxlength="15">
                                </div>
                            </div>

                            <div class="campo">
                                <label for="serie">Série ou curso</label>

                                <select id="serie" name="serie" required>
                                    <option value="">Selecione</option>

                                    <?php foreach ($opcoes_series as $opcao_serie): ?>
                                        <option value="<?= escapar($opcao_serie) ?>" <?= $opcao_serie === ($usuario["serie"] ?? "") ? "selected" : "" ?>>
                                            <?= escapar($opcao_serie) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="escola">Escola ou faculdade</label>

                                <select id="escola" name="escola" required>
                                    <option value="">Selecione</option>

                                    <?php foreach ($opcoes_escolas as $opcao_escola): ?>
                                        <option value="<?= escapar($opcao_escola) ?>" <?= $opcao_escola === ($usuario["escola"] ?? "") ? "selected" : "" ?>>
                                            <?= escapar($opcao_escola) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="localidade">
                                    Cidade
                                </label>

                                <select
                                    id="localidade"
                                    name="localidade"
                                    required
                                >

                                    <option value="">
                                        Selecione sua cidade
                                    </option>

                                    <?php

                                    $localidadeAtual = '';

                                    if (
                                        !empty($usuario['cidade'])
                                        &&
                                        !empty($usuario['estado'])
                                    ) {
                                        $localidadeAtual =
                                            $usuario['cidade']
                                            . '|'
                                            . $usuario['estado'];
                                    }

                                    foreach (
                                        $opcoes_cidades
                                        as $opcaoCidade
                                    ):

                                        $cidadeOpcao =
                                            $opcaoCidade['cidade']
                                            ?? '';

                                        $estadoOpcao =
                                            $opcaoCidade['estado']
                                            ?? '';

                                        $labelOpcao =
                                            $opcaoCidade['label']
                                            ??
                                            (
                                                $cidadeOpcao
                                                . ' - '
                                                . $estadoOpcao
                                            );

                                        $valorOpcao =
                                            $cidadeOpcao
                                            . '|'
                                            . $estadoOpcao;

                                    ?>

                                        <option
                                            value="<?= escapar($valorOpcao) ?>"
                                            <?= $valorOpcao === $localidadeAtual ? 'selected' : '' ?>
                                        >
                                            <?= escapar($labelOpcao) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>

                        <div class="botoes">
                            <a href="perfil.php" class="btn-cancelar">
                                Cancelar
                            </a>

                            <button type="submit" class="btn-salvar">
                                <i class="fa-solid fa-check"></i>
                                Salvar alterações
                            </button>
                        </div>
                    </section>
                </form>
            </div>
        </main>
    </div>

    <div id="logout-modal" class="modal">
        <div class="modal-content">
            <h3>Ah... já vai?</h3>
            <p>Tem certeza que deseja sair?</p>

            <div class="modal-buttons">
                <button id="confirm-logout">Sim</button>
                <button id="cancel-logout">Cancelar</button>
            </div>
        </div>
    </div>

    <div id="fogi-modal">
        <div class="fogi-container">
            <div class="fogi-header">
                <span>FOGi — Assistente de Estudos</span>
                <button id="fogi-close">Fechar</button>
            </div>

            <iframe id="fogi-iframe" src="about:blank"></iframe>
        </div>
    </div>

    <footer>&copy; 2025 FOAG. Todos os direitos reservados.</footer>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const fotoInput = document.getElementById("foto");
            const previewFoto = document.getElementById("previewFoto");
            const telefoneInput = document.getElementById("telefone");
            const serieSelect = document.getElementById("serie");
            const escolaSelect = document.getElementById("escola");
            const localidadeSelect = document.getElementById("localidade");

            if (serieSelect) {
                new Choices(serieSelect, {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: "",
                    noResultsText: "Nenhum resultado encontrado",
                    noChoicesText: "Nenhuma opção disponível",
                    searchPlaceholderValue: "Buscar série ou curso"
                });
            }

            if (escolaSelect) {
                new Choices(escolaSelect, {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: "",
                    noResultsText: "Nenhum resultado encontrado",
                    noChoicesText: "Nenhuma opção disponível",
                    searchPlaceholderValue: "Buscar escola ou faculdade"
                });
            }

            if (localidadeSelect) {
                new Choices(localidadeSelect, {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: "",
                    noResultsText: "Cidade não encontrada",
                    noChoicesText: "Nenhuma cidade disponível",
                    searchPlaceholderValue: "Digite sua cidade"
                });
            }

            if (fotoInput && previewFoto) {
                fotoInput.addEventListener("change", () => {
                    const arquivo = fotoInput.files[0];

                    if (!arquivo) {
                        return;
                    }

                    if (arquivo.size > 5 * 1024 * 1024) {
                        alert("A imagem deve ter no máximo 5 MB.");
                        fotoInput.value = "";
                        return;
                    }

                    const leitor = new FileReader();

                    leitor.onload = evento => {
                        previewFoto.src = evento.target.result;
                    };

                    leitor.readAsDataURL(arquivo);
                });
            }

            if (telefoneInput) {
                telefoneInput.addEventListener("input", () => {
                    let valor = telefoneInput.value.replace(/\D/g, "").slice(0, 11);

                    if (valor.length > 10) {
                        valor = valor.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
                    } else if (valor.length > 6) {
                        valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4})$/, "($1) $2-$3");
                    } else if (valor.length > 2) {
                        valor = valor.replace(/^(\d{2})(\d+)/, "($1) $2");
                    } else if (valor.length > 0) {
                        valor = valor.replace(/^(\d*)/, "($1");
                    }

                    telefoneInput.value = valor;
                });
            }

            const perfilBtn = document.getElementById("icon-perfil");

            if (perfilBtn) {
                perfilBtn.addEventListener("click", () => {
                    window.location.href = "perfil.php";
                });
            }

            const fogiBtn = document.getElementById("icon-fogi");
            const fogiModal = document.getElementById("fogi-modal");
            const fogiFrame = document.getElementById("fogi-iframe");
            const fogiClose = document.getElementById("fogi-close");

            if (fogiBtn && fogiModal && fogiFrame && fogiClose) {
                fogiBtn.addEventListener("click", () => {
                    fogiFrame.src = "http://127.0.0.1:5000";
                    fogiModal.style.display = "flex";
                    document.body.style.overflow = "hidden";
                });

                fogiClose.addEventListener("click", () => {
                    fogiModal.style.display = "none";
                    fogiFrame.src = "about:blank";
                    document.body.style.overflow = "";
                });
            }

            const sairBtn = document.getElementById("icon-sair");
            const logoutModal = document.getElementById("logout-modal");
            const confirmarLogout = document.getElementById("confirm-logout");
            const cancelarLogout = document.getElementById("cancel-logout");

            if (sairBtn && logoutModal) {
                sairBtn.addEventListener("click", () => {
                    logoutModal.style.display = "flex";
                });
            }

            if (cancelarLogout && logoutModal) {
                cancelarLogout.addEventListener("click", () => {
                    logoutModal.style.display = "none";
                });
            }

            if (confirmarLogout) {
                confirmarLogout.addEventListener("click", () => {
                    window.location.href = "../login/logout.php";
                });
            }
        });
    </script>
    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>