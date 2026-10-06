<?php
session_start();

// ============================================
// SEM VERIFICAÇÃO DE LOGIN
// ============================================
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_nome'] = 'Usuário';
}

$current = basename($_SERVER['PHP_SELF']);

// ============================================
// CONFIGURAÇÕES PADRÃO
// ============================================
$config_padrao = [
    'formato_data' => 'dd/mm/aaaa',
    'formato_horario' => '24',
    'inicio_semana' => 'domingo',
    'pagina_inicial' => 'inicio',
    'visualizacao_calendario' => 'mes',
    'mostrar_concluidas' => 1,
    'confirmar_exclusao' => 1,
    'tema' => 'claro',
    'tamanho_fonte' => 'media',
    'cor_principal' => 'azul',
    'notificacoes_navegador' => 0,
    'lembrete_atividades' => 1,
    'lembrete_provas' => 1,
    'lembrete_metas' => 0,
    'notificacao_pomodoro' => 1,
    'som_pomodoro' => 1,
    'antecedencia_lembrete' => 15,
    // ===== ACESSIBILIDADE =====
    'libras' => 0
];

function carregarConfiguracoes() {
    global $config_padrao;

    if (isset($_SESSION['configuracoes']) && is_array($_SESSION['configuracoes'])) {
        $configuracoesSalvas = $_SESSION['configuracoes'];

        // Opções removidas da página de Aparência
        unset(
            $configuracoesSalvas['modo_compacto'],
            $configuracoesSalvas['reduzir_animacoes']
        );

        return array_merge($config_padrao, $configuracoesSalvas);
    }

    return $config_padrao;
}

$config = carregarConfiguracoes();

// ============================================
// PROCESSAR REQUISIÇÕES AJAX
// ============================================

// SALVAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'salvar') {
    header('Content-Type: application/json');
    try {
        $dados = $_POST;
        unset(
            $dados['acao'],
            $dados['modo_compacto'],
            $dados['reduzir_animacoes']
        );
        $checkboxes = ['mostrar_concluidas', 'confirmar_exclusao',
                       'notificacoes_navegador', 'lembrete_atividades',
                       'lembrete_provas', 'lembrete_metas', 'notificacao_pomodoro', 'som_pomodoro',
                       // ===== ACESSIBILIDADE =====
                       'libras'];
        foreach ($checkboxes as $campo) {
            $dados[$campo] = isset($dados[$campo]) ? 1 : 0;
        }
        $_SESSION['configuracoes'] = $dados;
        echo json_encode(['success' => true, 'message' => 'Configurações salvas com sucesso!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
    }
    exit();
}

// EXPORTAR
if (isset($_GET['exportar']) && $_GET['exportar'] === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="foag_config_' . date('Y-m-d') . '.json"');
    echo json_encode([
        'usuario' => $_SESSION['usuario_nome'] ?? 'Usuário',
        'data_exportacao' => date('Y-m-d H:i:s'),
        'configuracoes' => $config,
        'versao' => '1.0.0'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// IMPORTAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup'])) {
    header('Content-Type: application/json');
    try {
        $arquivo = $_FILES['backup'];
        if ($arquivo['error'] !== UPLOAD_ERR_OK) throw new Exception('Erro no upload');
        $conteudo = file_get_contents($arquivo['tmp_name']);
        $dados = json_decode($conteudo, true);
        if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Arquivo JSON inválido');
        if (!isset($dados['configuracoes'])) throw new Exception('Estrutura inválida');

        unset(
            $dados['configuracoes']['modo_compacto'],
            $dados['configuracoes']['reduzir_animacoes']
        );

        $_SESSION['configuracoes'] = array_merge($config_padrao, $dados['configuracoes']);
        echo json_encode(['success' => true, 'message' => 'Backup importado com sucesso!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit();
}

// AÇÕES DE PERIGO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_perigo'])) {
    header('Content-Type: application/json');
    try {
        $acao = $_POST['acao_perigo'];
        switch ($acao) {
            case 'limpar-pomodoro': break;
            case 'limpar-atividades': break;
            default: throw new Exception('Ação desconhecida');
        }
        echo json_encode(['success' => true, 'message' => 'Ação executada com sucesso!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - FOAG</title>
    <link rel="stylesheet" href="configuracoes.css?v=21">
    <link rel="stylesheet" href="dark_configuracoes.css?v=21">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../global/css/cursor.css">
    <link rel="stylesheet" href="../global/css/layout.css?v=<?= time() ?>">
</head>

<body>
    <!-- ===== SKIP LINK ===== -->
    <a href="#conteudo-principal" class="skip-link">Pular para o conteúdo principal</a>

    <!-- ===== CABEÇALHO ===== -->
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="container">
        <!-- ===== MENU ===== -->
        <?php include __DIR__ . '/../components/menu.php'; ?>

        <!-- ===== CONTEÚDO PRINCIPAL ===== -->
        <main class="conteudo configuracoes-conteudo" id="conteudo-principal" role="main">
            <div class="configuracoes-pagina">
                <div class="configuracoes-topo">
                    <div>
                        <h1>⚙️ Configurações</h1>
                        <p>Personalize o funcionamento e a aparência do FOAG.</p>
                    </div>
                </div>

                <form id="form-configuracoes" method="POST" aria-label="Formulário de configurações">
                    <input type="hidden" name="acao" value="salvar">
                    
                    <!-- ========================================== -->
                    <!-- GERAL -->
                    <!-- ========================================== -->
                    <section class="configuracao-card" id="geral" aria-labelledby="cabecalho-geral">
                        <div class="card-cabecalho" id="cabecalho-geral" role="button" tabindex="0" aria-expanded="true" aria-controls="conteudo-geral">
                            <div class="card-icone" aria-hidden="true"><i class="fa-solid fa-sliders" aria-hidden="true"></i></div>
                            <div>
                                <h2>Geral</h2>
                                <p>Configurações utilizadas em diferentes páginas do sistema.</p>
                            </div>
                            <span class="card-toggle ativo" aria-hidden="true"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
                        </div>
                        <div class="card-conteudo aberto" id="conteudo-geral" role="region" aria-labelledby="cabecalho-geral">
                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <label for="formato-data">Formato de data</label>
                                    <span>Escolha como as datas serão exibidas.</span>
                                </div>
                                <select id="formato-data" name="formato_data">
                                    <option value="dd/mm/aaaa" <?= $config['formato_data'] === 'dd/mm/aaaa' ? 'selected' : '' ?>>DD/MM/AAAA</option>
                                    <option value="mm/dd/aaaa" <?= $config['formato_data'] === 'mm/dd/aaaa' ? 'selected' : '' ?>>MM/DD/AAAA</option>
                                    <option value="aaaa-mm-dd" <?= $config['formato_data'] === 'aaaa-mm-dd' ? 'selected' : '' ?>>AAAA-MM-DD</option>
                                </select>
                            </div>

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <label for="formato-horario">Formato de horário</label>
                                    <span>Escolha entre o formato de 12 ou 24 horas.</span>
                                </div>
                                <select id="formato-horario" name="formato_horario">
                                    <option value="24" <?= $config['formato_horario'] === '24' ? 'selected' : '' ?>>24 horas — 18:30</option>
                                    <option value="12" <?= $config['formato_horario'] === '12' ? 'selected' : '' ?>>12 horas — 06:30 PM</option>
                                </select>
                            </div>

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <label for="inicio-semana">Início da semana</label>
                                    <span>Defina o primeiro dia mostrado no calendário.</span>
                                </div>
                                <select id="inicio-semana" name="inicio_semana">
                                    <option value="domingo" <?= $config['inicio_semana'] === 'domingo' ? 'selected' : '' ?>>Domingo</option>
                                    <option value="segunda" <?= $config['inicio_semana'] === 'segunda' ? 'selected' : '' ?>>Segunda-feira</option>
                                </select>
                            </div>

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <label for="pagina-inicial">Página inicial padrão</label>
                                    <span>Escolha a página aberta depois do login.</span>
                                </div>
                                <select id="pagina-inicial" name="pagina_inicial">
                                    <option value="inicio" <?= $config['pagina_inicial'] === 'inicio' ? 'selected' : '' ?>>Início</option>
                                    <option value="calendario" <?= $config['pagina_inicial'] === 'calendario' ? 'selected' : '' ?>>Calendário</option>
                                    <option value="agenda" <?= $config['pagina_inicial'] === 'agenda' ? 'selected' : '' ?>>Agenda</option>
                                    <option value="pomodoro" <?= $config['pagina_inicial'] === 'pomodoro' ? 'selected' : '' ?>>Pomodoro</option>
                                    <option value="boletim" <?= $config['pagina_inicial'] === 'boletim' ? 'selected' : '' ?>>Boletim</option>
                                </select>
                            </div>

                            <label class="configuracao-item configuracao-switch">
                                <span class="configuracao-texto">
                                    <strong>Mostrar atividades concluídas</strong>
                                    <small>Exibe atividades finalizadas na agenda.</small>
                                </span>
                                <span class="switch">
                                    <input type="checkbox" name="mostrar_concluidas" <?= $config['mostrar_concluidas'] ? 'checked' : '' ?>>
                                    <span class="slider" aria-hidden="true"></span>
                                </span>
                            </label>

                            <label class="configuracao-item configuracao-switch">
                                <span class="configuracao-texto">
                                    <strong>Confirmar antes de excluir</strong>
                                    <small>Pede confirmação antes de apagar informações.</small>
                                </span>
                                <span class="switch">
                                    <input type="checkbox" name="confirmar_exclusao" <?= $config['confirmar_exclusao'] ? 'checked' : '' ?>>
                                    <span class="slider" aria-hidden="true"></span>
                                </span>
                            </label>
                        </div>
                    </section>

                    <!-- ========================================== -->
                    <!-- APARÊNCIA -->
                    <!-- ========================================== -->
                    <section class="configuracao-card" id="aparencia" aria-labelledby="cabecalho-aparencia">
                        <div class="card-cabecalho" id="cabecalho-aparencia" role="button" tabindex="0" aria-expanded="false" aria-controls="conteudo-aparencia">
                            <div class="card-icone" aria-hidden="true"><i class="fa-solid fa-palette" aria-hidden="true"></i></div>
                            <div>
                                <h2>Aparência</h2>
                                <p>Personalize a visualização do sistema.</p>
                            </div>
                            <span class="card-toggle" aria-hidden="true"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
                        </div>
                        <div class="card-conteudo" id="conteudo-aparencia" role="region" aria-labelledby="cabecalho-aparencia">
                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <label for="tema">Tema</label>
                                    <span>Escolha a aparência principal do FOAG.</span>
                                </div>
                                <select id="tema" name="tema">
                                    <option value="claro" <?= $config['tema'] === 'claro' ? 'selected' : '' ?>>Claro</option>
                                    <option value="escuro" <?= $config['tema'] === 'escuro' ? 'selected' : '' ?>>Escuro</option>
                                    <option value="sistema" <?= $config['tema'] === 'sistema' ? 'selected' : '' ?>>Tema do dispositivo</option>
                                </select>
                            </div>

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <label for="tamanho-fonte">Tamanho da fonte</label>
                                    <span>Altere o tamanho dos textos do sistema.</span>
                                </div>
                                <select id="tamanho-fonte" name="tamanho_fonte">
                                    <option value="pequena" <?= $config['tamanho_fonte'] === 'pequena' ? 'selected' : '' ?>>Pequena</option>
                                    <option value="media" <?= $config['tamanho_fonte'] === 'media' ? 'selected' : '' ?>>Média</option>
                                    <option value="grande" <?= $config['tamanho_fonte'] === 'grande' ? 'selected' : '' ?>>Grande</option>
                                </select>
                            </div>

                        </div>
                    </section>

                    <!-- ========================================== -->
                    <!-- NOTIFICAÇÕES -->
                    <!-- ========================================== -->
                    <section class="configuracao-card" id="notificacoes" aria-labelledby="cabecalho-notificacoes">
                        <div class="card-cabecalho" id="cabecalho-notificacoes" role="button" tabindex="0" aria-expanded="false" aria-controls="conteudo-notificacoes">
                            <div class="card-icone" aria-hidden="true"><i class="fa-solid fa-bell" aria-hidden="true"></i></div>
                            <div>
                                <h2>Notificações</h2>
                                <p>Escolha os lembretes que deseja receber.</p>
                            </div>
                            <span class="card-toggle" aria-hidden="true"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
                        </div>
                        <div class="card-conteudo" id="conteudo-notificacoes" role="region" aria-labelledby="cabecalho-notificacoes">
                            <label class="configuracao-item configuracao-switch">
                                <span class="configuracao-texto">
                                    <strong>Notificações do navegador</strong>
                                    <small>Permite que o FOAG envie avisos no dispositivo.</small>
                                </span>
                                <span class="switch">
                                    <input type="checkbox" name="notificacoes_navegador" <?= $config['notificacoes_navegador'] ? 'checked' : '' ?>>
                                    <span class="slider" aria-hidden="true"></span>
                                </span>
                            </label>

                            <label class="configuracao-item configuracao-switch">
                                <span class="configuracao-texto">
                                    <strong>Lembretes de atividades</strong>
                                    <small>Receba avisos sobre atividades próximas.</small>
                                </span>
                                <span class="switch">
                                    <input type="checkbox" name="lembrete_atividades" <?= $config['lembrete_atividades'] ? 'checked' : '' ?>>
                                    <span class="slider" aria-hidden="true"></span>
                                </span>
                            </label>

                            <label class="configuracao-item configuracao-switch">
                                <span class="configuracao-texto">
                                    <strong>Lembretes de provas</strong>
                                    <small>Receba avisos antes das datas de provas.</small>
                                </span>
                                <span class="switch">
                                    <input type="checkbox" name="lembrete_provas" <?= $config['lembrete_provas'] ? 'checked' : '' ?>>
                                    <span class="slider" aria-hidden="true"></span>
                                </span>
                            </label>

                            <label class="configuracao-item configuracao-switch">
                                <span class="configuracao-texto">
                                    <strong>Lembretes de metas</strong>
                                    <small>Receba avisos sobre metas próximas do prazo.</small>
                                </span>
                                <span class="switch">
                                    <input type="checkbox" name="lembrete_metas" <?= $config['lembrete_metas'] ? 'checked' : '' ?>>
                                    <span class="slider" aria-hidden="true"></span>
                                </span>
                            </label>

                            <label class="configuracao-item configuracao-switch">
                                <span class="configuracao-texto">
                                    <strong>Finalização do Pomodoro</strong>
                                    <small>Mostra um aviso quando o período de foco terminar.</small>
                                </span>
                                <span class="switch">
                                    <input type="checkbox" name="notificacao_pomodoro" <?= $config['notificacao_pomodoro'] ? 'checked' : '' ?>>
                                    <span class="slider" aria-hidden="true"></span>
                                </span>
                            </label>

                            <label class="configuracao-item configuracao-switch">
                                <span class="configuracao-texto">
                                    <strong>Som do Pomodoro</strong>
                                    <small>Reproduz um som quando o ciclo terminar.</small>
                                </span>
                                <span class="switch">
                                    <input type="checkbox" name="som_pomodoro" <?= $config['som_pomodoro'] ? 'checked' : '' ?>>
                                    <span class="slider" aria-hidden="true"></span>
                                </span>
                            </label>

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <label for="antecedencia-lembrete">Antecedência dos lembretes</label>
                                    <span>Defina quanto tempo antes o aviso será mostrado.</span>
                                </div>
                                <select id="antecedencia-lembrete" name="antecedencia_lembrete">
                                    <option value="5" <?= $config['antecedencia_lembrete'] == 5 ? 'selected' : '' ?>>5 minutos</option>
                                    <option value="15" <?= $config['antecedencia_lembrete'] == 15 ? 'selected' : '' ?>>15 minutos</option>
                                    <option value="30" <?= $config['antecedencia_lembrete'] == 30 ? 'selected' : '' ?>>30 minutos</option>
                                    <option value="60" <?= $config['antecedencia_lembrete'] == 60 ? 'selected' : '' ?>>1 hora</option>
                                    <option value="1440" <?= $config['antecedencia_lembrete'] == 1440 ? 'selected' : '' ?>>1 dia</option>
                                </select>
                            </div>

                            <div class="linha-botao">
                                <button type="button" class="btn-secundario" id="btn-testar-notificacao" aria-label="Testar notificação">
                                    <i class="fa-regular fa-bell" aria-hidden="true"></i> Testar notificação
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- ========================================== -->
                    <!-- PRIVACIDADE -->
                    <!-- ========================================== -->
                    <section class="configuracao-card" id="privacidade" aria-labelledby="cabecalho-privacidade">
                        <div class="card-cabecalho" id="cabecalho-privacidade" role="button" tabindex="0" aria-expanded="false" aria-controls="conteudo-privacidade">
                            <div class="card-icone" aria-hidden="true"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
                            <div>
                                <h2>Privacidade e dados</h2>
                                <p>Gerencie as informações armazenadas pelo FOAG.</p>
                            </div>
                            <span class="card-toggle" aria-hidden="true"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
                        </div>
                        <div class="card-conteudo" id="conteudo-privacidade" role="region" aria-labelledby="cabecalho-privacidade">
                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <strong>Exportar meus dados</strong>
                                    <small>Baixe uma cópia das suas informações em JSON.</small>
                                </div>
                                <a href="?exportar=json" class="btn-secundario" style="text-decoration:none;" aria-label="Exportar dados em JSON">
                                    <i class="fa-solid fa-download" aria-hidden="true"></i> Exportar
                                </a>
                            </div>

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <strong>Importar backup</strong>
                                    <small>Restaure informações usando um arquivo JSON.</small>
                                </div>
                                <div id="form-importar" style="display:flex;gap:10px;align-items:center;">
                                    <input type="file" name="backup" accept=".json,application/json" style="display:none;" id="arquivo-backup" aria-label="Selecionar arquivo de backup JSON">
                                    <button type="button" class="btn-secundario" id="btn-importar" aria-label="Importar backup">
                                        <i class="fa-solid fa-upload" aria-hidden="true"></i> Importar
                                    </button>
                                </div>
                            </div>

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <strong>Limpar histórico do Pomodoro</strong>
                                    <small>Exclui todos os ciclos registrados anteriormente.</small>
                                </div>
                                <button type="button" class="btn-perigo-outline" data-acao="limpar-pomodoro" aria-label="Limpar histórico do Pomodoro">Limpar</button>
                            </div>

                            <div class="configuracao-item">
                                <div class="configuracao-texto">
                                    <strong>Limpar atividades concluídas</strong>
                                    <small>Remove todas as atividades finalizadas.</small>
                                </div>
                                <button type="button" class="btn-perigo-outline" data-acao="limpar-atividades" aria-label="Limpar atividades concluídas">Limpar</button>
                            </div>

                            <div class="zona-perigo">
                                <div>
                                    <h3>⚠️ Excluir conta</h3>
                                    <p>Exclui permanentemente sua conta e todos os dados associados a ela.</p>
                                </div>
                                <button
                                    type="button"
                                    class="btn-perigo"
                                    id="btn-excluir-conta"
                                    aria-label="Excluir minha conta"
                                >
                                    <i class="fa-solid fa-user-xmark" aria-hidden="true"></i>
                                    Excluir minha conta
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- ========================================== -->
                    <!-- ACESSIBILIDADE — SOMENTE LIBRAS -->
                    <!-- ========================================== -->
                    <section class="configuracao-card" id="acessibilidade" aria-labelledby="cabecalho-acessibilidade">
                        <div class="card-cabecalho" id="cabecalho-acessibilidade" role="button" tabindex="0" aria-expanded="false" aria-controls="conteudo-acessibilidade">
                            <div class="card-icone" aria-hidden="true">
                                <i class="fa-solid fa-hands-asl-interpreting" aria-hidden="true"></i>
                            </div>

                            <div>
                                <h2>Acessibilidade</h2>
                                <p>Ative a tradução do conteúdo do FOAG para Libras.</p>
                            </div>

                            <span class="card-toggle" aria-hidden="true">
                                <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div class="card-conteudo" id="conteudo-acessibilidade" role="region" aria-labelledby="cabecalho-acessibilidade">
                            <label class="configuracao-item configuracao-switch">
                                <span class="configuracao-texto">
                                    <strong>Libras</strong>
                                    <small>Ativa o tradutor VLibras em todas as páginas do FOAG e mantém a opção ativa ao navegar pelo site.</small>
                                </span>

                                <span class="switch">
                                    <input
                                        type="checkbox"
                                        name="libras"
                                        id="config-libras"
                                    >
                                    <span class="slider" aria-hidden="true"></span>
                                </span>
                            </label>
                        </div>
                    </section>

                    <!-- ========================================== -->
                    <!-- SOBRE -->
                    <!-- ========================================== -->
                    <section class="configuracao-card" id="sobre" aria-labelledby="cabecalho-sobre">
                        <div class="card-cabecalho" id="cabecalho-sobre" role="button" tabindex="0" aria-expanded="false" aria-controls="conteudo-sobre">
                            <div class="card-icone" aria-hidden="true"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></div>
                            <div>
                                <h2>Sobre</h2>
                                <p>Informações sobre o projeto.</p>
                            </div>
                            <span class="card-toggle" aria-hidden="true"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
                        </div>
                        <div class="card-conteudo" id="conteudo-sobre" role="region" aria-labelledby="cabecalho-sobre">
                            <div class="sobre-foag">
                                <div class="logo-foag" aria-hidden="true">F</div>
                                <div>
                                    <h3>FOAG</h3>
                                    <p>Ferramenta de Organização Acadêmica Geral</p>
                                    <span>Versão 1.0.0 (Sem banco de dados)</span>
                                </div>
                            </div>
                            <div class="sobre-texto">
                                <p>
                                    O FOAG foi desenvolvido para ajudar estudantes a organizar
                                    atividades, horários, metas, notas e períodos de estudo.
                                    <br><br>
                                    <strong>💡 Modo offline:</strong> As configurações são salvas na sessão do navegador.
                                </p>
                            </div>
                            <div class="sobre-links">
                                <a href="termos2.php"><i class="fa-solid fa-file-contract" aria-hidden="true"></i> Termos de uso</a>
                                <a href="politica_privacidade.php"><i class="fa-solid fa-lock" aria-hidden="true"></i> Política de privacidade</a>
                                <a href="central_ajuda.php"><i class="fa-solid fa-circle-question" aria-hidden="true"></i> Central de ajuda</a>
                            </div>
                        </div>
                    </section>

                    <!-- ===== BARRA SALVAR ===== -->
                    <div class="barra-salvar">
                        <p id="status-configuracoes" aria-live="polite" aria-atomic="true">
                            <i class="fa-solid fa-circle" style="color:#22c55e;font-size:0.6rem;" aria-hidden="true"></i>
                            <?= date('H:i') ?> - Configurações carregadas
                        </p>
                        <div>
                            <button type="reset" class="btn-secundario" aria-label="Cancelar alterações"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Cancelar</button>
                            <button type="submit" class="btn-principal" aria-label="Salvar configurações"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Salvar</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- ===== MODAIS ===== -->
    <!-- Modal Logout -->
    <div id="logout-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="logout-titulo">
        <div class="modal-content">
            <h3 id="logout-titulo">Ah... já vai?</h3>
            <h4>Tem certeza que deseja sair?</h4>
            <div class="modal-buttons">
                <button id="confirm-logout" aria-label="Confirmar saída">Sim</button>
                <button id="cancel-logout" aria-label="Cancelar saída">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Confirmação -->
    <div id="confirmacao-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="confirmacao-titulo">
        <div class="modal-content">
            <div class="modal-alerta" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></div>
            <h3 id="confirmacao-titulo">Confirmar ação</h3>
            <p id="confirmacao-texto">Esta ação não poderá ser desfeita.</p>
            <div class="modal-buttons">
                <button id="confirmar-acao" aria-label="Confirmar ação">Confirmar</button>
                <button id="cancelar-acao" aria-label="Cancelar ação">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Excluir Conta -->
    <div
        id="excluir-conta-modal"
        class="modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="excluir-conta-titulo"
        aria-describedby="excluir-conta-descricao"
    >
        <div class="modal-content modal-excluir-conta">
            <div class="modal-excluir-icone" aria-hidden="true">
                <i class="fa-solid fa-user-xmark"></i>
            </div>

            <h3 id="excluir-conta-titulo">Excluir minha conta?</h3>

            <p id="excluir-conta-descricao">
                Essa ação é permanente. Todos os seus dados do FOAG serão excluídos e não poderão ser recuperados.
            </p>

            <form id="form-excluir-conta" autocomplete="off">
                <label for="senha-excluir-conta">Digite sua senha para confirmar</label>

                <div class="campo-senha-excluir">
                    <input
                        type="password"
                        id="senha-excluir-conta"
                        name="senha"
                        placeholder="Sua senha atual"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        id="toggle-senha-excluir"
                        class="btn-toggle-senha-excluir"
                        aria-label="Mostrar senha"
                    >
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>
                </div>

                <div
                    id="erro-excluir-conta"
                    class="erro-excluir-conta"
                    role="alert"
                    aria-live="assertive"
                ></div>

                <div class="modal-buttons modal-buttons-excluir">
                    <button
                        type="button"
                        id="cancelar-excluir-conta"
                        class="btn-cancelar-exclusao"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        id="confirmar-excluir-conta"
                        class="btn-confirmar-exclusao"
                    >
                        Excluir permanentemente
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal FOGi -->
    <div id="fogi-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="fogi-titulo">
        <div class="fogi-container">
            <div class="fogi-header">
                <span id="fogi-titulo">FOGi — Assistente de Estudos</span>
                <button id="fogi-close" aria-label="Fechar assistente FOGi">Fechar</button>
            </div>
            <iframe id="fogi-iframe" src="about:blank" title="Assistente FOGi - conteúdo externo"></iframe>
        </div>
    </div>

    <!-- ===== TOAST ===== -->
    <div id="toast-configuracoes" role="alert" aria-live="assertive">
        <i class="fa-solid fa-check" aria-hidden="true"></i>
        <span>Configurações salvas com sucesso!</span>
    </div>

    <!-- ===== RODAPÉ ===== -->
    <footer role="contentinfo">&copy; 2026 FOAG. Todos os direitos reservados.</footer>

    <!-- ========================================== -->
    <!-- JAVASCRIPT -->
    <!-- ========================================== -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // ============================================
        // 1. ACORDEÃO COM ACESSIBILIDADE
        // ============================================
        const cabecalhos = document.querySelectorAll('.card-cabecalho');

        function toggleSecao(cabecalho) {
            const card = cabecalho.closest('.configuracao-card');
            if (!card) return;
            const conteudo = card.querySelector('.card-conteudo');
            const toggle = cabecalho.querySelector('.card-toggle');
            if (!conteudo) return;
            
            const isOpen = conteudo.classList.contains('aberto');
            
            if (isOpen) {
                conteudo.classList.remove('aberto');
                if (toggle) toggle.classList.remove('ativo');
                cabecalho.setAttribute('aria-expanded', 'false');
            } else {
                conteudo.classList.add('aberto');
                if (toggle) toggle.classList.add('ativo');
                cabecalho.setAttribute('aria-expanded', 'true');
            }
        }

        cabecalhos.forEach(cabecalho => {
            // Já tem role e tabindex no HTML, mas garantir
            if (!cabecalho.hasAttribute('role')) {
                cabecalho.setAttribute('role', 'button');
            }
            if (!cabecalho.hasAttribute('tabindex')) {
                cabecalho.setAttribute('tabindex', '0');
            }

            // Evento de clique
            cabecalho.addEventListener('click', function(e) {
                if (e.target.closest('button') || e.target.closest('.switch') || 
                    e.target.closest('select') || e.target.closest('.cor') ||
                    e.target.closest('a') || e.target.closest('input')) {
                    return;
                }
                toggleSecao(this);
            });

            // Evento de teclado (Enter e Espaço)
            cabecalho.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleSecao(this);
                }
            });
        });

        // ============================================
        // 2. TOAST
        // ============================================
        function mostrarToast(mensagem, tipo) {
            const toast = document.getElementById('toast-configuracoes');
            if (!toast) return;
            const icone = toast.querySelector('i');
            const span = toast.querySelector('span');
            
            // Atualizar para role="alert" quando for erro
            if (tipo === 'erro') {
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
            } else {
                toast.setAttribute('role', 'status');
                toast.setAttribute('aria-live', 'polite');
            }
            
            if (tipo === 'sucesso') {
                icone.className = 'fa-solid fa-check-circle';
                icone.style.color = '#22c55e';
            } else if (tipo === 'erro') {
                icone.className = 'fa-solid fa-exclamation-circle';
                icone.style.color = '#ef4444';
            } else {
                icone.className = 'fa-solid fa-circle-info';
                icone.style.color = '#3b82f6';
            }
            
            span.textContent = mensagem;
            toast.style.display = 'flex';
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.style.transition = 'all 0.3s ease';
                toast.style.opacity = '1';
            }, 50);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => { toast.style.display = 'none'; }, 300);
            }, 3500);
        }

        // ============================================
        // 3. MODAL - TRAP FOCUS
        // ============================================
        function trapFocus(modal) {
            const focusable = modal.querySelectorAll(
                'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            );
            if (!focusable.length) return;
            
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            
            function handleTrapFocus(e) {
                if (e.key !== 'Tab') return;
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
            
            modal.addEventListener('keydown', handleTrapFocus);
            // Armazenar para remover depois
            modal._trapFocusHandler = handleTrapFocus;
        }

        function abrirModal(titulo, texto, callback) {
            const modal = document.getElementById('confirmacao-modal');
            const tituloEl = document.getElementById('confirmacao-titulo');
            const textoEl = document.getElementById('confirmacao-texto');
            const confirmarBtn = document.getElementById('confirmar-acao');
            const cancelarBtn = document.getElementById('cancelar-acao');
            if (!modal) return;
            
            tituloEl.textContent = titulo || 'Confirmar ação';
            textoEl.textContent = texto || 'Esta ação não poderá ser desfeita.';
            modal.style.display = 'flex';
            
            // Focar no primeiro botão
            setTimeout(() => confirmarBtn.focus(), 100);
            trapFocus(modal);
            
            const handleConfirm = () => {
                modal.style.display = 'none';
                confirmarBtn.removeEventListener('click', handleConfirm);
                cancelarBtn.removeEventListener('click', handleCancel);
                if (modal._trapFocusHandler) {
                    modal.removeEventListener('keydown', modal._trapFocusHandler);
                }
                if (callback) callback(true);
            };
            
            const handleCancel = () => {
                modal.style.display = 'none';
                confirmarBtn.removeEventListener('click', handleConfirm);
                cancelarBtn.removeEventListener('click', handleCancel);
                if (modal._trapFocusHandler) {
                    modal.removeEventListener('keydown', modal._trapFocusHandler);
                }
                if (callback) callback(false);
            };
            
            confirmarBtn.addEventListener('click', handleConfirm);
            cancelarBtn.addEventListener('click', handleCancel);
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    confirmarBtn.removeEventListener('click', handleConfirm);
                    cancelarBtn.removeEventListener('click', handleCancel);
                    if (modal._trapFocusHandler) {
                        modal.removeEventListener('keydown', modal._trapFocusHandler);
                    }
                    if (callback) callback(false);
                }
            });
        }

        // Configurar trap focus para modais existentes
        document.querySelectorAll('.modal, #fogi-modal').forEach(modal => {
            const observer = new MutationObserver(() => {
                if (modal.style.display === 'flex') {
                    const focusable = modal.querySelector('button:not([disabled]), [href], input:not([disabled])');
                    if (focusable) setTimeout(() => focusable.focus(), 100);
                    trapFocus(modal);
                } else if (modal._trapFocusHandler) {
                    modal.removeEventListener('keydown', modal._trapFocusHandler);
                }
            });
            observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
        });

        // ============================================
        // 4. SALVAR
        // ============================================
        const form = document.getElementById('form-configuracoes');

        function salvarConfiguracoes(formData) {
            const dados = {};
            for (let [key, value] of formData.entries()) {
                dados[key] = value;
            }
            dados.acao = 'salvar';
            
            fetch('configuracoes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {

                    // ===== GARANTIR TEMA SALVO =====
                    const temaEscolhido =
                        document.getElementById('tema')?.value || 'claro';

                    aplicarTema(temaEscolhido);

                    // ===== SALVAR APARÊNCIA =====
                    const tamanhoFonte =
                        document.querySelector('[name="tamanho_fonte"]')?.value || 'media';

                    if (
                        window.FOAGAparencia &&
                        typeof window.FOAGAparencia.salvarTamanhoFonte === 'function'
                    ) {
                        window.FOAGAparencia.salvarTamanhoFonte(tamanhoFonte);
                    } else {
                        localStorage.setItem(
                            'foag_aparencia',
                            JSON.stringify({
                                tamanho_fonte: tamanhoFonte
                            })
                        );
                    }

                    // =============================================
                    // LIBRAS — SÓ APLICA DEPOIS DE CLICAR EM SALVAR
                    // =============================================
                    const campoLibras =
                        document.getElementById('config-libras');

                    const librasAtiva =
                        campoLibras ? campoLibras.checked : false;

                    /*
                     * Chave NOVA para não herdar testes das versões
                     * anteriores que estavam causando o comportamento
                     * mostrado no vídeo.
                     */
                    localStorage.setItem(
                        'foag_libras_v19',
                        librasAtiva ? '1' : '0'
                    );

                    mostrarToast(
                        librasAtiva
                            ? '✅ Configurações salvas. Libras ativada!'
                            : '✅ Configurações salvas. Libras desativada!',
                        'sucesso'
                    );

                    /*
                     * O VLibras NÃO é ativado pelo clique no switch.
                     * Ele só entra em ação depois do SALVAR, quando
                     * recarregamos a página e acessibilidade.js lê
                     * a preferência recém-salva.
                     */
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);

                    return;
                } else {
                    mostrarToast('❌ ' + data.message, 'erro');
                }
            })
            .catch(() => mostrarToast('❌ Erro ao comunicar com o servidor', 'erro'));
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                salvarConfiguracoes(new FormData(this));
            });
        }

        document.querySelectorAll('.btn-salvar-topo, .btn-principal').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.type === 'submit') return;
                e.preventDefault();
                if (form) salvarConfiguracoes(new FormData(form));
            });
        });

        // ============================================
        // 5. IMPORTAR
        // ============================================
        const btnImportar = document.getElementById('btn-importar');
        const inputBackup = document.getElementById('arquivo-backup');
        if (btnImportar && inputBackup) {
            btnImportar.addEventListener('click', () => inputBackup.click());
            inputBackup.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append('backup', file);
                mostrarToast('⏳ Importando...', 'info');
                fetch('configuracoes.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarToast('✅ ' + data.message, 'sucesso');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mostrarToast('❌ ' + data.message, 'erro');
                    }
                })
                .catch(() => mostrarToast('❌ Erro ao importar', 'erro'));
                this.value = '';
            });
        }

        // ============================================
        // 6. AÇÕES DE PERIGO
        // ============================================
        document.querySelectorAll('[data-acao]').forEach(btn => {
            btn.addEventListener('click', function() {
                const acao = this.dataset.acao;
                const titulos = {
                    'limpar-pomodoro': '🧹 Limpar histórico do Pomodoro',
                    'limpar-atividades': '🧹 Limpar atividades concluídas'
                };
                const textos = {
                    'limpar-pomodoro': 'Todos os ciclos do Pomodoro serão removidos.',
                    'limpar-atividades': 'Todas as atividades finalizadas serão removidas.'
                };
                abrirModal(titulos[acao] || 'Confirmar', textos[acao] || 'Irreversível!', function(confirmado) {
                    if (confirmado) {
                        fetch('configuracoes.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ acao_perigo: acao })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                mostrarToast('✅ ' + data.message, 'sucesso');
                            } else {
                                mostrarToast('❌ ' + data.message, 'erro');
                            }
                        })
                        .catch(() => mostrarToast('❌ Erro ao executar', 'erro'));
                    }
                });
            });
        });

        // ============================================
        // EXCLUIR CONTA
        // ============================================
        const btnExcluirConta = document.getElementById('btn-excluir-conta');
        const modalExcluirConta = document.getElementById('excluir-conta-modal');
        const formExcluirConta = document.getElementById('form-excluir-conta');
        const senhaExcluirConta = document.getElementById('senha-excluir-conta');
        const cancelarExcluirConta = document.getElementById('cancelar-excluir-conta');
        const confirmarExcluirConta = document.getElementById('confirmar-excluir-conta');
        const erroExcluirConta = document.getElementById('erro-excluir-conta');
        const toggleSenhaExcluir = document.getElementById('toggle-senha-excluir');

        function abrirModalExcluirConta() {
            if (!modalExcluirConta) return;

            if (erroExcluirConta) {
                erroExcluirConta.textContent = '';
                erroExcluirConta.classList.remove('visivel');
            }

            if (senhaExcluirConta) {
                senhaExcluirConta.value = '';
            }

            modalExcluirConta.style.display = 'flex';

            setTimeout(() => {
                senhaExcluirConta?.focus();
            }, 100);
        }

        function fecharModalExcluirConta() {
            if (!modalExcluirConta) return;

            modalExcluirConta.style.display = 'none';

            if (senhaExcluirConta) {
                senhaExcluirConta.value = '';
                senhaExcluirConta.type = 'password';
            }

            if (toggleSenhaExcluir) {
                const icone = toggleSenhaExcluir.querySelector('i');
                if (icone) {
                    icone.className = 'fa-regular fa-eye';
                }
                toggleSenhaExcluir.setAttribute('aria-label', 'Mostrar senha');
            }

            if (erroExcluirConta) {
                erroExcluirConta.textContent = '';
                erroExcluirConta.classList.remove('visivel');
            }
        }

        btnExcluirConta?.addEventListener('click', abrirModalExcluirConta);
        cancelarExcluirConta?.addEventListener('click', fecharModalExcluirConta);

        modalExcluirConta?.addEventListener('click', function(e) {
            if (e.target === modalExcluirConta) {
                fecharModalExcluirConta();
            }
        });

        toggleSenhaExcluir?.addEventListener('click', function() {
            if (!senhaExcluirConta) return;

            const mostrando = senhaExcluirConta.type === 'text';
            senhaExcluirConta.type = mostrando ? 'password' : 'text';

            const icone = this.querySelector('i');

            if (icone) {
                icone.className = mostrando
                    ? 'fa-regular fa-eye'
                    : 'fa-regular fa-eye-slash';
            }

            this.setAttribute(
                'aria-label',
                mostrando ? 'Mostrar senha' : 'Ocultar senha'
            );
        });

        formExcluirConta?.addEventListener('submit', function(e) {
            e.preventDefault();

            const senha = senhaExcluirConta?.value || '';

            if (!senha.trim()) {
                if (erroExcluirConta) {
                    erroExcluirConta.textContent = 'Digite sua senha para confirmar.';
                    erroExcluirConta.classList.add('visivel');
                }

                senhaExcluirConta?.focus();
                return;
            }

            if (erroExcluirConta) {
                erroExcluirConta.textContent = '';
                erroExcluirConta.classList.remove('visivel');
            }

            if (confirmarExcluirConta) {
                confirmarExcluirConta.disabled = true;
                confirmarExcluirConta.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Excluindo...';
            }

            fetch('../api/configuracoes/excluir.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams({
                    senha: senha
                })
            })
            .then(async response => {
                let data;

                try {
                    data = await response.json();
                } catch (erro) {
                    throw new Error('Resposta inválida do servidor.');
                }

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message || 'Não foi possível excluir a conta.'
                    );
                }

                return data;
            })
            .then(data => {
                /*
                 * Limpa preferências locais para que outro usuário
                 * no mesmo navegador não herde as configurações.
                 */
                localStorage.removeItem('foag_aparencia');
                localStorage.removeItem('foag_acessibilidade');
                localStorage.removeItem('foag_libras_v19');
                localStorage.removeItem('darkMode');
                localStorage.removeItem('foagTema');

                mostrarToast(
                    '✅ ' + (data.message || 'Conta excluída com sucesso.'),
                    'sucesso'
                );

                setTimeout(() => {
                    window.location.href =
                        data.redirect || '../login/index.php';
                }, 900);
            })
            .catch(erro => {
                if (erroExcluirConta) {
                    erroExcluirConta.textContent =
                        erro.message || 'Erro ao excluir a conta.';
                    erroExcluirConta.classList.add('visivel');
                }

                senhaExcluirConta?.focus();
            })
            .finally(() => {
                if (confirmarExcluirConta) {
                    confirmarExcluirConta.disabled = false;
                    confirmarExcluirConta.innerHTML =
                        'Excluir permanentemente';
                }
            });
        });

        // ============================================
        // 7. TESTAR NOTIFICAÇÃO
        // ============================================
        document.getElementById('btn-testar-notificacao')?.addEventListener('click', function() {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🔔 Teste do FOAG', { body: 'Notificação de teste!' });
                mostrarToast('✅ Notificação enviada!', 'sucesso');
            } else if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification('🔔 Teste do FOAG', { body: 'Permissão concedida!' });
                        mostrarToast('✅ Notificação enviada!', 'sucesso');
                    } else {
                        mostrarToast('❌ Permissão negada.', 'erro');
                    }
                });
            } else {
                mostrarToast('❌ Notificações não suportadas.', 'erro');
            }
        });

        // ============================================
        // 8. LOGOUT
        // ============================================
        document.getElementById('icon-sair')?.addEventListener('click', function(e) {
            e.stopPropagation();
            const modal = document.getElementById('logout-modal');
            modal.style.display = 'flex';
            setTimeout(() => document.getElementById('confirm-logout').focus(), 100);
            trapFocus(modal);
        });
        
        document.getElementById('confirm-logout')?.addEventListener('click', function() {
            const modal = document.getElementById('logout-modal');
            modal.style.display = 'none';
            if (modal._trapFocusHandler) {
                modal.removeEventListener('keydown', modal._trapFocusHandler);
            }
            mostrarToast('👋 Saindo...', 'sucesso');
            setTimeout(() => window.location.href = '../login/login.php', 800);
        });
        
        document.getElementById('cancel-logout')?.addEventListener('click', function() {
            const modal = document.getElementById('logout-modal');
            modal.style.display = 'none';
            if (modal._trapFocusHandler) {
                modal.removeEventListener('keydown', modal._trapFocusHandler);
            }
        });

        // ============================================
        // 9. PERFIL
        // ============================================
        document.getElementById('icon-perfil')?.addEventListener('click', function(e) {
            e.stopPropagation();
            mostrarToast('👤 Redirecionando...', 'info');
            setTimeout(() => window.location.href = '../perfil/perfil.php', 500);
        });

        // ============================================
        // 10. FOGI
        // ============================================
        const iconFogi = document.getElementById('icon-fogi');
        const fogiModal = document.getElementById('fogi-modal');
        const fogiClose = document.getElementById('fogi-close');
        const fogiIframe = document.getElementById('fogi-iframe');
        
        if (iconFogi && fogiModal) {
            iconFogi.addEventListener('click', function(e) {
                e.stopPropagation();
                if (fogiModal.style.display === 'flex') {
                    fogiModal.style.display = 'none';
                    if (fogiIframe) fogiIframe.src = 'about:blank';
                    if (fogiModal._trapFocusHandler) {
                        fogiModal.removeEventListener('keydown', fogiModal._trapFocusHandler);
                    }
                } else {
                    fogiModal.style.display = 'flex';
                    if (fogiIframe) fogiIframe.src = 'https://www.youtube.com/embed/dQw4w9WgXcQ';
                    setTimeout(() => document.getElementById('fogi-close').focus(), 100);
                    trapFocus(fogiModal);
                }
            });
        }
        
        if (fogiClose && fogiModal) {
            fogiClose.addEventListener('click', function() {
                fogiModal.style.display = 'none';
                if (fogiIframe) fogiIframe.src = 'about:blank';
                if (fogiModal._trapFocusHandler) {
                    fogiModal.removeEventListener('keydown', fogiModal._trapFocusHandler);
                }
            });
        }
        
        if (fogiModal) {
            fogiModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    if (fogiIframe) fogiIframe.src = 'about:blank';
                    if (this._trapFocusHandler) {
                        this.removeEventListener('keydown', this._trapFocusHandler);
                    }
                }
            });
        }

        // ============================================
        // 11. ESC
        // ============================================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (
                    modalExcluirConta &&
                    modalExcluirConta.style.display === 'flex'
                ) {
                    fecharModalExcluirConta();
                }

                document.querySelectorAll('.modal, #fogi-modal').forEach(modal => {
                    if (modal.style.display === 'flex') {
                        modal.style.display = 'none';
                        if (modal.id === 'fogi-modal' && fogiIframe) fogiIframe.src = 'about:blank';
                        if (modal._trapFocusHandler) {
                            modal.removeEventListener('keydown', modal._trapFocusHandler);
                        }
                    }
                });
            }
        });

       // ============================================
        // 12. MODO ESCURO
        // ============================================
        const themeToggle = document.getElementById('themeToggle');
        const temaSelect = document.getElementById('tema');

        // Aplica o tema sem ficar invertendo valores
        function aplicarTema(tema) {
            let usarEscuro = false;

            if (tema === 'escuro') {
                usarEscuro = true;
            }

            if (tema === 'claro') {
                usarEscuro = false;
            }

            if (tema === 'sistema') {
                usarEscuro = window.matchMedia(
                    '(prefers-color-scheme: dark)'
                ).matches;
            }

            // Aplica ou REMOVE corretamente o modo escuro
            document.body.classList.toggle('dark-mode', usarEscuro);

            // Mantém compatibilidade com as outras páginas do FOAG
            localStorage.setItem(
                'darkMode',
                usarEscuro ? 'true' : 'false'
            );

            // Guarda também qual opção foi escolhida
            localStorage.setItem('foagTema', tema);

            // Troca o ícone
            if (themeToggle) {
                if (usarEscuro) {
                    themeToggle.className = 'fa-solid fa-sun';
                    themeToggle.setAttribute(
                        'aria-label',
                        'Alternar para tema claro'
                    );
                } else {
                    themeToggle.className = 'fa-solid fa-moon';
                    themeToggle.setAttribute(
                        'aria-label',
                        'Alternar para tema escuro'
                    );
                }
            }
        }


        // ============================================
        // CARREGAR TEMA SALVO
        // ============================================

        // Usa primeiro a preferência salva no navegador.
        // Se ainda não existir, usa o valor vindo do PHP.
        const temaSalvoLocal =
            localStorage.getItem('foagTema');

        let temaInicial =
            temaSalvoLocal ||
            (temaSelect ? temaSelect.value : 'claro');

        if (temaSelect) {
            temaSelect.value = temaInicial;
        }

        aplicarTema(temaInicial);


        // ============================================
        // BOTÃO LUA / SOL
        // ============================================
        if (themeToggle) {
            themeToggle.addEventListener('click', function(e) {
                e.stopPropagation();

                const estaEscuro =
                    document.body.classList.contains('dark-mode');

                const novoTema = estaEscuro
                    ? 'claro'
                    : 'escuro';

                if (temaSelect) {
                    temaSelect.value = novoTema;
                }

                aplicarTema(novoTema);
            });
        }


        // ============================================
        // SELECT CLARO / ESCURO / SISTEMA
        // ============================================
        if (temaSelect) {
            temaSelect.addEventListener('change', function() {
                aplicarTema(this.value);
            });
        }


        // ============================================
        // ALTERAÇÃO DO TEMA DO DISPOSITIVO
        // ============================================
        const mediaTema = window.matchMedia(
            '(prefers-color-scheme: dark)'
        );

        mediaTema.addEventListener('change', function() {
            if (temaSelect && temaSelect.value === 'sistema') {
                aplicarTema('sistema');
            }
        });

        // ============================================
        // 13. LIBRAS — SINCRONIZAR O SWITCH COM O QUE
        //     FOI SALVO ANTERIORMENTE
        //
        // IMPORTANTE:
        // - sem configuração salva: começa DESLIGADO;
        // - marcou, mas não salvou: não muda o site;
        // - marcou e salvou: no próximo carregamento
        //   aparece LIGADO;
        // - desmarcou e salvou: aparece DESLIGADO.
        // ============================================
        const campoLibrasConfig =
            document.getElementById('config-libras');

        if (campoLibrasConfig) {
            campoLibrasConfig.checked =
                localStorage.getItem('foag_libras_v19') === '1';
        }

        console.log(
            '⚙️ Configurações FOAG: estado de Libras sincronizado.'
        );
    });
    </script>

    <style>
        /* ============================================
           CSS COMPLETO - CONFIGURAÇÕES FOAG
           ============================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; flex-direction: column; min-height: 100vh; background: #ffffff; color: #1f2937; }
        
        /* ===== SKIP LINK ===== */
        .skip-link {
            position: absolute;
            top: -100%;
            left: 50%;
            transform: translateX(-50%);
            background: #38a5ff;
            color: #fff !important;
            padding: 12px 24px;
            z-index: 99999;
            border-radius: 0 0 8px 8px;
            font-weight: 600;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            transition: top 0.3s ease;
        }
        .skip-link:focus {
            top: 0;
        }
        
        /* ===== INDICADOR DE FOCO ===== */
        *:focus-visible {
            outline: 3px solid #38a5ff;
            outline-offset: 2px;
            border-radius: 4px;
        }
        *:focus:not(:focus-visible) {
            outline: none;
        }
        
        header.cabecalho {
            font-family: 'Snap ITC', sans-serif;
            width: 100%; background: #38a5ff; color: #fff;
            text-align: left; padding: 15px; font-size: 24px; font-weight: bold;
            position: relative; box-shadow: 5px 5px 15px rgba(0,0,0,0.2); z-index: 10;
        }
        .header-icons {
            position: absolute; top: 50%; right: 20px;
            transform: translateY(-50%);
            display: flex; gap: 15px; align-items: center;
            font-size: 1.3rem; color: #fff; user-select: none;
        }
        .header-icons i { color: #fff; cursor: pointer; transition: transform 0.2s; }
        .header-icons i:hover { transform: translateY(-13%) scale(1.1); color: #ddd; }
        .header-icons i:focus-visible { outline: 2px solid #fff; outline-offset: 2px; border-radius: 4px; }
        
        .container { display: flex; flex: 1; min-height: 0; }
        
        nav.menu {
            width: 260px; flex-shrink: 0; background: #38a5ff;
            padding: 18px; display: flex; flex-direction: column; gap: 14px;
            box-shadow: 5px 5px 15px rgba(0,0,0,0.15);
        }
        nav.menu a {
            text-decoration: none; color: #fff; font-size: 16px;
            padding: 10px 12px; border-radius: 8px;
            transition: background 0.2s; display: flex; align-items: center; gap: 8px;
        }
        nav.menu a:hover, nav.menu a.active { background: rgba(255,255,255,0.18); }
        nav.menu a:focus-visible { outline: 2px solid #fff; outline-offset: -2px; border-radius: 8px; }
        
        .conteudo { flex: 1; min-width: 0; padding: 25px; }
        .configuracoes-conteudo { background: #f6f8fb; overflow-y: auto; }
        .configuracoes-pagina { width: 100%; max-width: 1050px; margin: 0 auto; }
        
        .configuracoes-topo {
            display: flex; align-items: center; justify-content: space-between;
            gap: 20px; margin-bottom: 22px;
        }
        .configuracoes-topo h1 { color: #1f2937; font-size: 1.8rem; margin-bottom: 4px; }
        .configuracoes-topo p { color: #6b7280; font-size: 0.94rem; }
        
        /* ACORDEON */
        .configuracao-card {
            background: #fff; border: 1px solid #e5eaf0; border-radius: 14px;
            margin-bottom: 12px; box-shadow: 0 4px 14px rgba(15,23,42,0.06);
            overflow: hidden;
        }
        .card-cabecalho {
            display: flex; align-items: center; gap: 13px;
            padding: 18px 22px; cursor: pointer; user-select: none;
            transition: background 0.2s; border-bottom: 1px solid transparent;
        }
        .card-cabecalho:hover { background: rgba(56,165,255,0.05); }
        .card-cabecalho:focus-visible { outline: 3px solid #38a5ff; outline-offset: -3px; border-radius: 14px; }
        .card-cabecalho .card-toggle {
            margin-left: auto; font-size: 0.9rem; color: #94a3b8;
            transition: transform 0.3s; display: inline-block;
        }
        .card-cabecalho .card-toggle.ativo { transform: rotate(180deg); }
        
        .card-icone {
            width: 43px; height: 43px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            background: #dff3ff; color: #168ce8; font-size: 1.1rem; flex-shrink: 0;
        }
        .card-cabecalho h2 { color: #1f2937; font-size: 1.15rem; margin-bottom: 2px; }
        .card-cabecalho p { color: #6b7280; font-size: 0.86rem; }
        
        .card-conteudo {
            max-height: 0; overflow: hidden;
            transition: max-height 0.4s ease-in-out, padding 0.3s ease-in-out;
            padding: 0 22px;
        }
        .card-conteudo.aberto {
            max-height: 3000px;
            padding: 8px 22px 8px 22px;
        }
        
        .configuracao-item {
            min-height: 76px; padding: 15px 4px;
            border-bottom: 1px solid #edf0f4;
            display: flex; align-items: center; justify-content: space-between; gap: 25px;
        }
        .configuracao-item:last-child { border-bottom: none; }
        
        .configuracao-texto { display: flex; flex-direction: column; gap: 4px; }
        .configuracao-texto label, .configuracao-texto strong, .titulo-opcao {
            color: #273142; font-size: 0.94rem; font-weight: 600;
        }
        .configuracao-texto span, .configuracao-texto small {
            color: #6b7280; font-size: 0.82rem; font-weight: 400;
        }
        
        .configuracao-item select {
            width: 240px; padding: 10px 12px;
            border: 1px solid #d8dee7; border-radius: 9px;
            background: #fff; color: #374151; outline: none; cursor: pointer;
        }
        .configuracao-item select:focus-visible {
            border-color: #38a5ff; box-shadow: 0 0 0 3px rgba(56,165,255,0.14);
        }
        
        .configuracao-switch { cursor: pointer; }
        .switch {
            position: relative; width: 48px; height: 26px; flex-shrink: 0;
        }
        .switch input { display: none; }
        .slider {
            position: absolute; inset: 0; background: #cbd5e1;
            border-radius: 999px; transition: 0.25s ease;
        }
        .slider::before {
            content: ""; position: absolute; width: 20px; height: 20px;
            left: 3px; top: 3px; background: #fff; border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.18); transition: 0.25s ease;
        }
        .switch input:checked + .slider { background: #38a5ff; }
        .switch input:checked + .slider::before { transform: translateX(22px); }
        .switch input:focus-visible + .slider { outline: 3px solid #38a5ff; outline-offset: 2px; }
        
        .opcoes-cores { display: flex; align-items: center; gap: 12px; }
        .cor { cursor: pointer; }
        .cor input { display: none; }
        .cor span {
            width: 28px; height: 28px; display: block; border-radius: 50%;
            border: 3px solid transparent; box-shadow: 0 0 0 1px #d1d5db;
            transition: transform 0.2s;
        }
        .cor:hover span { transform: scale(1.1); }
        .cor input:checked + span {
            border-color: #fff; box-shadow: 0 0 0 3px #273142;
        }
        .cor-azul span { background: #38a5ff; }
        .cor-roxa span { background: #8b5cf6; }
        .cor-verde span { background: #22c55e; }
        .cor-rosa span { background: #ec4899; }
        
        .btn-principal, .btn-salvar-topo, .btn-secundario, .btn-perigo, .btn-perigo-outline {
            border: none; border-radius: 9px; padding: 10px 15px;
            font-size: 0.87rem; font-weight: 600; cursor: pointer;
            transition: 0.2s; display: inline-flex; align-items: center;
            justify-content: center; gap: 7px;
        }
        .btn-principal, .btn-salvar-topo { background: #38a5ff; color: #fff; }
        .btn-principal:hover, .btn-salvar-topo:hover { background: #168ce8; }
        .btn-secundario { background: #eef2f6; color: #374151; }
        .btn-secundario:hover { background: #dfe5eb; }
        .btn-perigo { background: #dc3545; color: #fff; }
        .btn-perigo:hover { background: #bd2130; }
        .btn-perigo-outline { background: transparent; color: #dc3545; border: 1px solid #dc3545; }
        .btn-perigo-outline:hover { background: #dc3545; color: #fff; }
        .btn-principal:focus-visible, .btn-secundario:focus-visible, 
        .btn-perigo:focus-visible, .btn-perigo-outline:focus-visible {
            outline: 3px solid #38a5ff; outline-offset: 2px;
        }
        
        .linha-botao { padding-top: 18px; display: flex; justify-content: flex-end; }
        
        .zona-perigo {
            margin-top: 20px; border: 1px solid #fecaca; background: #fff7f7;
            border-radius: 11px; padding: 16px;
            display: flex; align-items: center; justify-content: space-between; gap: 20px;
        }
        .zona-perigo h3 { color: #b91c1c; font-size: 0.95rem; margin-bottom: 3px; }
        .zona-perigo p { color: #7f1d1d; font-size: 0.8rem; }
        
        .sobre-foag {
            display: flex; align-items: center; gap: 15px;
            padding: 22px 4px; border-bottom: 1px solid #edf0f4;
        }
        .logo-foag {
            width: 58px; height: 58px; border-radius: 15px;
            background: #38a5ff; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Snap ITC', sans-serif; font-size: 1.7rem;
        }
        .sobre-foag h3 { color: #1f2937; font-size: 1.2rem; }
        .sobre-foag p { color: #4b5563; font-size: 0.87rem; }
        .sobre-foag span { color: #9ca3af; font-size: 0.76rem; }
        .sobre-texto {
            padding: 18px 4px; color: #4b5563; font-size: 0.87rem;
            line-height: 1.65; border-bottom: 1px solid #edf0f4;
        }
        .sobre-links {
            display: flex; flex-wrap: wrap; gap: 10px; padding-top: 18px;
        }
        .sobre-links a {
            text-decoration: none; color: #168ce8; background: #eef8ff;
            border: 1px solid #cfeaff; padding: 9px 12px; border-radius: 8px;
            font-size: 0.84rem; display: flex; align-items: center; gap: 7px;
        }
        .sobre-links a:hover { background: #dff3ff; }
        .sobre-links a:focus-visible { outline: 3px solid #38a5ff; outline-offset: 2px; }
        
        .barra-salvar {
            position: sticky; bottom: 0;
            background: rgba(255,255,255,0.96);
            border: 1px solid #e5eaf0; border-radius: 12px;
            padding: 13px 16px; box-shadow: 0 -5px 20px rgba(15,23,42,0.08);
            display: flex; justify-content: space-between; align-items: center;
            gap: 15px; z-index: 5;
        }
        .barra-salvar p { color: #6b7280; font-size: 0.82rem; }
        .barra-salvar > div { display: flex; gap: 10px; }
        
        /* MODAIS */
        .modal {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 2000;
            justify-content: center; align-items: center;
        }
        .modal-content {
            background: #fff; color: #333; padding: 25px;
            border-radius: 10px; text-align: center;
            width: min(90%, 350px); box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            animation: aparecer 0.3s ease;
        }
        .modal-content h3 { margin-bottom: 5px; }
        .modal-content h4, .modal-content p { font-weight: 400; color: #6b7280; }
        .modal-alerta { font-size: 2rem; color: #f59e0b; margin-bottom: 10px; }
        .modal-buttons { margin-top: 20px; display: flex; justify-content: center; gap: 15px; }
        .modal-buttons button { padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: bold; }
        .modal-buttons button:focus-visible { outline: 3px solid #38a5ff; outline-offset: 2px; }
        #confirm-logout, #confirmar-acao { background: #38a5ff; color: #fff; }
        #cancel-logout, #cancelar-acao { background: #e0e0e0; color: #333; }
        
        @keyframes aparecer {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        #fogi-modal {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
            align-items: center; justify-content: center;
        }
        #fogi-modal .fogi-container {
            background: #fff; width: 90%; max-width: 1100px; height: 80vh;
            border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;
            box-shadow: 0 10px 35px rgba(0,0,0,0.2);
        }
        #fogi-modal .fogi-header {
            display: flex; align-items: center; justify-content: space-between;
            background: #38a5ff; color: #fff; padding: 8px 14px;
            font-weight: 600; font-size: 0.95rem;
        }
        #fogi-close {
            border: none; background: #fff; color: #333;
            padding: 4px 10px; border-radius: 6px; cursor: pointer;
        }
        #fogi-close:focus-visible { outline: 3px solid #fff; outline-offset: 2px; }
        #fogi-iframe { flex: 1; border: none; width: 100%; height: 100%; }
        
        #toast-configuracoes {
            position: fixed; right: 25px; bottom: 25px;
            background: #1f2937; color: #fff; padding: 12px 17px;
            border-radius: 9px; display: none; align-items: center; gap: 8px;
            z-index: 5000; box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        footer {
            background: #232323; color: #fff; text-align: center;
            padding: 5px; width: 100%; font-size: 1em;
        }
        
        @media (max-width: 768px) {
            .container { flex-direction: column; }
            nav.menu { width: 100%; }
            .conteudo { padding: 15px; }
            .configuracoes-topo { align-items: flex-start; }
            .configuracao-item { align-items: flex-start; flex-direction: column; gap: 12px; }
            .configuracao-item select { width: 100%; }
            .zona-perigo { align-items: flex-start; flex-direction: column; }
            .barra-salvar { align-items: flex-start; flex-direction: column; }
            .barra-salvar > div { width: 100%; }
            .barra-salvar button { flex: 1; }
            .card-cabecalho { padding: 14px 16px; }
            .card-conteudo { padding: 0 16px; }
            .card-conteudo.aberto { padding: 8px 16px 8px 16px; }
        }
        @media (max-width: 480px) {
            header.cabecalho { font-size: 20px; padding: 12px; }
            .header-icons { right: 12px; gap: 11px; font-size: 1.05rem; }
            .configuracoes-topo h1 { font-size: 1.45rem; }
            .btn-salvar-topo { display: none; }
            .card-cabecalho { padding: 12px 14px; }
            .card-cabecalho h2 { font-size: 1rem; }
        }

        


        /* ============================================
           ACESSIBILIDADE
           ============================================ */
        body .texto-lendo {
            background: rgba(59, 130, 246, 0.15) !important;
            transition: background 0.3s ease;
        }
        .sr-only {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }
    
        /* ===== EXCLUIR CONTA ===== */
        .modal-excluir-conta {
            width: min(92%, 430px);
            padding: 28px;
            border-radius: 16px;
        }

        .modal-excluir-icone {
            width: 54px;
            height: 54px;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #fff1f2;
            color: #dc3545;
            font-size: 1.35rem;
        }

        .modal-excluir-conta h3 {
            margin-bottom: 8px;
            color: #1f2937;
            font-size: 1.15rem;
        }

        .modal-excluir-conta > p {
            margin: 0 auto 20px;
            color: #6b7280;
            font-size: 0.82rem;
            line-height: 1.6;
            max-width: 340px;
        }

        #form-excluir-conta {
            text-align: left;
        }

        #form-excluir-conta > label {
            display: block;
            margin-bottom: 7px;
            color: #374151;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .campo-senha-excluir {
            position: relative;
        }

        #senha-excluir-conta {
            width: 100%;
            height: 44px;
            padding: 0 46px 0 12px;
            border: 1px solid #d8dee7;
            border-radius: 9px;
            background: #fff;
            color: #1f2937;
            outline: none;
            font-size: 0.85rem;
        }

        #senha-excluir-conta:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.10);
        }

        .btn-toggle-senha-excluir {
            position: absolute;
            top: 50%;
            right: 7px;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: #6b7280;
            cursor: pointer;
        }

        .btn-toggle-senha-excluir:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .erro-excluir-conta {
            display: none;
            margin-top: 9px;
            padding: 9px 11px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fff1f2;
            color: #b91c1c;
            font-size: 0.75rem;
            line-height: 1.4;
        }

        .erro-excluir-conta.visivel {
            display: block;
        }

        .modal-buttons-excluir {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 10px;
        }

        .btn-cancelar-exclusao,
        .btn-confirmar-exclusao {
            min-height: 42px;
            padding: 9px 12px;
            border: 0;
            border-radius: 9px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancelar-exclusao {
            background: #eef2f6;
            color: #374151;
        }

        .btn-cancelar-exclusao:hover {
            background: #dfe5eb;
        }

        .btn-confirmar-exclusao {
            background: #dc3545;
            color: #fff;
        }

        .btn-confirmar-exclusao:hover {
            background: #bd2130;
        }

        .btn-confirmar-exclusao:disabled {
            opacity: 0.65;
            cursor: wait;
        }
@media (max-width: 480px) {
            .modal-excluir-conta {
                padding: 22px 18px;
            }

            .modal-buttons-excluir {
                grid-template-columns: 1fr;
            }
        }

</style>

    <!-- ======================================
         ACESSIBILIDADE FOAG
    ======================================= -->

    <script
        src="acessibilidade.js?v=22">
    </script>

    <!-- ======================================
         APARÊNCIA GLOBAL FOAG
    ======================================= -->
    <script src="aparencia.js?v=2"></script>

    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>