<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="../../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">

    <!-- Responsividade -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bem-vindo</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #38a5ff, rgb(46, 154, 241));
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        .container {
            width: 100%;
            max-width: 700px;
            padding: 30px 20px;
        }
        
        h1 {
            font-size: clamp(3rem, 12vw, 5rem);
            font-family: 'Snap ITC', 'Poppins', sans-serif;
            color: white;
            margin: 0 0 20px;
            line-height: 1;
        }

        h2 {
            font-size: clamp(1.5rem, 6vw, 2.5rem);
            color: white;
            margin: 0 0 15px;
            line-height: 1.2;
            word-break: break-word;
        }

        p {
            font-size: clamp(1rem, 4vw, 1.5rem);
            color: white;
            margin: 0 0 25px;
        }

        a {
            display: inline-block;
            background-color: white;
            color: #38a5ff;
            font-size: clamp(1rem, 3vw, 1.2rem);
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease-in-out;
        }

        a:hover {
            background-color: #e6f3ff;
            transform: translateY(-3px);
        }

        a:active {
            transform: translateY(1px);
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .container {
                padding: 20px 10px;
            }

            a {
                width: 100%;
                max-width: 260px;
                padding: 14px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>FOAG</h1>  

        <h2>
            Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!
        </h2>

        <p>Você está logado com sucesso.</p>

        <a href="../inicioo/inicio.php">Entrar</a>
    </div>

    <!-- VLibras -->
    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app.js"></script>
    <script>
        new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>
</body>
</html>