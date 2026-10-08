<?php
require_once __DIR__ . '/../config/bootstrap.php';
$foagBase = $foagBase ?? FOAG_BASE_URL;
$currentPath = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');

$menuItems = [
    [
        'label' => 'Início',
        'icon'  => 'fa-solid fa-house',
        'href'  => $foagBase . '/inicioo/inicio.php',
        'match' => '/inicioo/'
    ],
    [
        'label' => 'Estudos',
        'icon'  => 'fa-solid fa-graduation-cap',
        'href'  => $foagBase . '/estudos/estudos.php',
        'match' => '/estudos/'
    ],
    [
        'label' => 'Agenda',
        'icon'  => 'fa-solid fa-book',
        'href'  => $foagBase . '/bloco/agenda.php',
        'match' => '/bloco/'
    ],
    [
        'label' => 'Calendário',
        'icon'  => 'fa-solid fa-calendar-days',
        'href'  => $foagBase . '/calend/calendario.php',
        'match' => '/calend/'
    ],
    [
        'label' => 'Boletim',
        'icon'  => 'fa-solid fa-check-double',
        'href'  => $foagBase . '/notas/notas.php',
        'match' => '/notas/'
    ],
    [
        'label' => 'Comunidade',
        'icon'  => 'fa-solid fa-comments',
        'href'  => $foagBase . '/comunidade/comunidade.php',
        'match' => '/comunidade/'
    ],
    [
        'label' => 'Ranking',
        'icon'  => 'fa-solid fa-trophy',
        'href'  => $foagBase . '/rank/rank.php',
        'match' => '/rank/'
    ],
    [
        'label' => 'Loja',
        'icon'  => 'fa-solid fa-store',
        'href'  => $foagBase . '/loja/loja.php',
        'match' => '/loja/'
    ]
];
?>

<nav class="menu">
    <?php foreach ($menuItems as $item): ?>
        <?php $active = str_contains($currentPath, $item['match']) ? 'active' : ''; ?>

        <a href="<?= $item['href'] ?>" class="<?= $active ?>">
            <i class="<?= $item['icon'] ?>"></i>
            <?= $item['label'] ?>
        </a>
    <?php endforeach; ?>
</nav>
