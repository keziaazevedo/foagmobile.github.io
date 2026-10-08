<?php
require_once __DIR__ . '/../config/bootstrap.php';
$foagBase = $foagBase ?? FOAG_BASE_URL;
?>

<header class="cabecalho">
    FOAG

    <div class="header-icons">
        <a
            href="<?= $foagBase ?>/configuracoes/configuracoes.php"
            class="link-configuracoes"
            title="Configurações"
            aria-label="Abrir configurações"
        >
            <i class="fa-solid fa-gear" aria-hidden="true"></i>
        </a>

        <i
            id="icon-perfil"
            class="fa-regular fa-user"
            title="Perfil"
        ></i>

        <i
            id="icon-sair"
            class="fa-solid fa-right-from-bracket"
            title="Sair"
        ></i>
    </div>
</header>
