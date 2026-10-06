<?php
require_once __DIR__ . '/../config/bootstrap.php';
$foagBase = $foagBase ?? FOAG_BASE_URL;
?>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-left">
            <span class="footer-brand">FOAG</span>

            <nav class="footer-links">
                <a href="<?= $foagBase ?>/configuracoes/sobre.php">Sobre</a>
                <a href="<?= $foagBase ?>/configuracoes/contato.php">Contato</a>
                <a href="<?= $foagBase ?>/configuracoes/politica_privacidade.php">Privacidade</a>
            </nav>
        </div>

        <span class="footer-copy">
            © <?= date('Y') ?> FOAG
        </span>
    </div>
</footer>
