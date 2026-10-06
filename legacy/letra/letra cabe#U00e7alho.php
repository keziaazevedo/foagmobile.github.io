<!DOCTYPE html>
<html>
<head>
    <script src="../../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../../global/js/utils.js?v=<?= time() ?>"></script>

    <title>Meu Site</title>
    <?php include 'font-config.php'; ?>
</head>
<body>
    <header>
        <div class="container">
            <h1>Meu Site</h1>
            
            <!-- AQUI VOCÊ COLA OS BOTÕES -->
            <div class="fonte-controles">
                <form method="POST" style="display: inline; margin: 0;">
                    <input type="hidden" name="font_action" value="decrease">
                    <button type="submit" class="fonte-btn" title="Diminuir fonte">A−</button>
                </form>
                <span class="fonte-tamanho">16px</span>
                <form method="POST" style="display: inline; margin: 0;">
                    <input type="hidden" name="font_action" value="increase">
                    <button type="submit" class="fonte-btn" title="Aumentar fonte">A+</button>
                </form>
                <form method="POST" style="display: inline; margin: 0;">
                    <input type="hidden" name="font_action" value="reset">
                    <button type="submit" class="fonte-btn reset" title="Resetar fonte">A</button>
                </form>
            </div>
        </div>
    </header>
    
    <!-- Resto do site -->
</body>
</html>