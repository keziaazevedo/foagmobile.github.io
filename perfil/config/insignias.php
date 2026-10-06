<?php

require_once __DIR__ . '/../../config/bootstrap.php';
// Configuração das insígnias disponíveis no sistema

$insignias_disponiveis = [
    [
        'id' => '1ano',
        'nome' => '1 Ano de FOAG',
        'descricao' => 'Completou 1 ano de uso do FOAG',
        'icone' => 'fa-solid fa-cake-candles',
        'imagem' => 'insignia_1ano.png',
        'cor' => '#ff6b6b',
        'categoria' => 'conquista'
    ],
    [
        'id' => 'cacador1',
        'nome' => 'Caçador de Estrelas Nv.1',
        'descricao' => 'Colecionou 10 estrelas',
        'icone' => 'fa-solid fa-star',
        'imagem' => 'insignia_cacador1.png',
        'cor' => '#ffd93d',
        'categoria' => 'estrelas'
    ],
    [
        'id' => 'cacador2',
        'nome' => 'Caçador de Estrelas Nv.2',
        'descricao' => 'Colecionou 50 estrelas',
        'icone' => 'fa-solid fa-star',
        'imagem' => 'insignia_cacador2.png',
        'cor' => '#ffd93d',
        'categoria' => 'estrelas'
    ],
    [
        'id' => 'cacador3',
        'nome' => 'Caçador de Estrelas Nv.3',
        'descricao' => 'Colecionou 100 estrelas',
        'icone' => 'fa-solid fa-star',
        'imagem' => 'insignia_cacador3.png',
        'cor' => '#ffd93d',
        'categoria' => 'estrelas'
    ],
    [
        'id' => 'colecionador',
        'nome' => 'Colecionador Mestre',
        'descricao' => 'Colecionou 10 insígnias diferentes',
        'icone' => 'fa-solid fa-trophy',
        'imagem' => 'insignia_colecionador.png',
        'cor' => '#ff6b6b',
        'categoria' => 'conquista'
    ],
    [
        'id' => 'elefante1',
        'nome' => 'Memória de Elefante Nv.1',
        'descricao' => 'Completou 5 dias de estudos',
        'icone' => 'fa-solid fa-brain',
        'imagem' => 'insignia_elefante1.png',
        'cor' => '#845ef7',
        'categoria' => 'estudo'
    ],
    [
        'id' => 'elefante2',
        'nome' => 'Memória de Elefante Nv.2',
        'descricao' => 'Completou 30 dias de estudos',
        'icone' => 'fa-solid fa-brain',
        'imagem' => 'insignia_elefante2.png',
        'cor' => '#845ef7',
        'categoria' => 'estudo'
    ],
    [
        'id' => 'elefante3',
        'nome' => 'Memória de Elefante Nv.3',
        'descricao' => 'Completou 100 dias de estudos',
        'icone' => 'fa-solid fa-brain',
        'imagem' => 'insignia_elefante3.png',
        'cor' => '#845ef7',
        'categoria' => 'estudo'
    ],
    [
        'id' => 'explorador',
        'nome' => 'Explorador do FOAG',
        'descricao' => 'Visitou todas as áreas do FOAG',
        'icone' => 'fa-solid fa-compass',
        'imagem' => 'insignia_explorador.png',
        'cor' => '#20c997',
        'categoria' => 'conquista'
    ],
    [
        'id' => 'lenda',
        'nome' => 'Lenda do FOAG',
        'descricao' => 'Alcançou o nível máximo',
        'icone' => 'fa-solid fa-crown',
        'imagem' => 'insignia_lenda.png',
        'cor' => '#ffd700',
        'categoria' => 'conquista'
    ],
    [
        'id' => 'pomodoro1',
        'nome' => 'Mestre do Pomodoro Nv.1',
        'descricao' => 'Completou 10 ciclos Pomodoro',
        'icone' => 'fa-solid fa-clock',
        'imagem' => 'insignia_pomodoro1.png',
        'cor' => '#fd7e14',
        'categoria' => 'estudo'
    ],
    [
        'id' => 'pomodoro2',
        'nome' => 'Mestre do Pomodoro Nv.2',
        'descricao' => 'Completou 50 ciclos Pomodoro',
        'icone' => 'fa-solid fa-clock',
        'imagem' => 'insignia_pomodoro2.png',
        'cor' => '#fd7e14',
        'categoria' => 'estudo'
    ],
    [
        'id' => 'pomodoro3',
        'nome' => 'Mestre do Pomodoro Nv.3',
        'descricao' => 'Completou 100 ciclos Pomodoro',
        'icone' => 'fa-solid fa-clock',
        'imagem' => 'insignia_pomodoro3.png',
        'cor' => '#fd7e14',
        'categoria' => 'estudo'
    ],
    [
        'id' => 'presenca1',
        'nome' => 'Presença de Ferro Nv.1',
        'descricao' => 'Acessou o FOAG 7 dias seguidos',
        'icone' => 'fa-solid fa-fire',
        'imagem' => 'insignia_presenca1.png',
        'cor' => '#e74c3c',
        'categoria' => 'presenca'
    ],
    [
        'id' => 'presenca2',
        'nome' => 'Presença de Ferro Nv.2',
        'descricao' => 'Acessou o FOAG 30 dias seguidos',
        'icone' => 'fa-solid fa-fire',
        'imagem' => 'insignia_presenca2.png',
        'cor' => '#e74c3c',
        'categoria' => 'presenca'
    ],
    [
        'id' => 'presenca3',
        'nome' => 'Presença de Ferro Nv.3',
        'descricao' => 'Acessou o FOAG 100 dias seguidos',
        'icone' => 'fa-solid fa-fire',
        'imagem' => 'insignia_presenca3.png',
        'cor' => '#e74c3c',
        'categoria' => 'presenca'
    ],
    [
        'id' => 'segredos',
        'nome' => 'Caçador de Segredos',
        'descricao' => 'Descobriu todos os segredos do FOAG',
        'icone' => 'fa-solid fa-mask',
        'imagem' => 'insignia_segredos.png',
        'cor' => '#6c5ce7',
        'categoria' => 'conquista'
    ]
];

// Categorias para exibição
$categorias_insignias = [
    'estrelas' => ['nome' => '⭐ Estrelas', 'icone' => 'fa-solid fa-star'],
    'estudo' => ['nome' => '📚 Estudo', 'icone' => 'fa-solid fa-book-open'],
    'conquista' => ['nome' => '🏆 Conquistas', 'icone' => 'fa-solid fa-trophy'],
    'presenca' => ['nome' => '🔥 Presença', 'icone' => 'fa-solid fa-fire']
];

// Funções
function usuarioTemInsignia($codigoUsuario, $insigniaId) {
    $caminho = FOAG_USUARIOS_DIR . '/' . $codigoUsuario . '/insignias.json';
    if (!file_exists($caminho)) return false;
    $dados = json_decode(file_get_contents($caminho), true);
    return is_array($dados) && in_array($insigniaId, $dados);
}

function desbloquearInsignia($codigoUsuario, $insigniaId) {
    $caminho = FOAG_USUARIOS_DIR . '/' . $codigoUsuario . '/insignias.json';
    $pasta = dirname($caminho);
    if (!is_dir($pasta)) return false;
    
    $insignias = [];
    if (file_exists($caminho)) {
        $insignias = json_decode(file_get_contents($caminho), true) ?: [];
    }
    
    if (!in_array($insigniaId, $insignias)) {
        $insignias[] = $insigniaId;
        return file_put_contents($caminho, json_encode($insignias, JSON_PRETTY_PRINT)) !== false;
    }
    return true;
}

function getInsigniasUsuario($codigoUsuario) {
    global $insignias_disponiveis;
    
    $caminho = FOAG_USUARIOS_DIR . '/' . $codigoUsuario . '/insignias.json';
    if (!file_exists($caminho)) return [];
    
    $ids = json_decode(file_get_contents($caminho), true) ?: [];
    $idsValidos = array_filter($ids, function($id) use ($insignias_disponiveis) {
        return in_array($id, array_column($insignias_disponiveis, 'id'));
    });
    
    return array_filter($insignias_disponiveis, function($insignia) use ($idsValidos) {
        return in_array($insignia['id'], $idsValidos);
    });
}

function verificarDesbloquearInsignias($codigoUsuario) {
    global $insignias_disponiveis;
    
    $caminhoPerfil = FOAG_USUARIOS_DIR . '/' . $codigoUsuario . '/perfil.json';
    if (!file_exists($caminhoPerfil)) return;
    
    $perfil = json_decode(file_get_contents($caminhoPerfil), true);
    
    // Desbloquear por estrelas
    if (isset($perfil['estrelas'])) {
        $estrelas = $perfil['estrelas'];
        if ($estrelas >= 100) desbloquearInsignia($codigoUsuario, 'cacador3');
        elseif ($estrelas >= 50) desbloquearInsignia($codigoUsuario, 'cacador2');
        elseif ($estrelas >= 10) desbloquearInsignia($codigoUsuario, 'cacador1');
    }
    
    // Desbloquear por presença
    if (isset($perfil['dias_consecutivos'])) {
        $dias = $perfil['dias_consecutivos'];
        if ($dias >= 100) desbloquearInsignia($codigoUsuario, 'presenca3');
        elseif ($dias >= 30) desbloquearInsignia($codigoUsuario, 'presenca2');
        elseif ($dias >= 7) desbloquearInsignia($codigoUsuario, 'presenca1');
    }
    
    // Desbloquear por pomodoro
    if (isset($perfil['pomodoros'])) {
        $pomodoros = $perfil['pomodoros'];
        if ($pomodoros >= 100) desbloquearInsignia($codigoUsuario, 'pomodoro3');
        elseif ($pomodoros >= 50) desbloquearInsignia($codigoUsuario, 'pomodoro2');
        elseif ($pomodoros >= 10) desbloquearInsignia($codigoUsuario, 'pomodoro1');
    }
    
    // Contar quantas insígnias o usuário tem
    $insignias = getInsigniasUsuario($codigoUsuario);
    if (count($insignias) >= 10) {
        desbloquearInsignia($codigoUsuario, 'colecionador');
    }
}

function garantirArquivoInsignias($codigoUsuario) {
    $caminho = FOAG_USUARIOS_DIR . '/' . $codigoUsuario . '/insignias.json';
    $pasta = dirname($caminho);
    
    if (!is_dir($pasta)) {
        mkdir($pasta, 0775, true);
    }
    
    if (!file_exists($caminho)) {
        file_put_contents($caminho, json_encode([], JSON_PRETTY_PRINT));
    }
    
    return $caminho;
}
?>