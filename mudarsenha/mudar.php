<?php
session_start();

if (
    empty($_SESSION['recuperacao_autorizada']) ||
    empty($_SESSION['recuperacao_arquivo']) ||
    empty($_SESSION['recuperacao_inicio'])
) {
    header('Location: esqueci.php?novo=1');
    exit;
}

if ((time() - $_SESSION['recuperacao_inicio']) > 600) {
    unset(
        $_SESSION['recuperacao_autorizada'],
        $_SESSION['recuperacao_arquivo'],
        $_SESSION['recuperacao_email'],
        $_SESSION['recuperacao_codigo'],
        $_SESSION['recuperacao_pergunta'],
        $_SESSION['recuperacao_inicio'],
        $_SESSION['recuperacao_tentativas']
    );

    header('Location: esqueci.php?novo=1');
    exit;
}

$emailExibido = $_SESSION['recuperacao_email'] ?? '';

$caminhoCss = __DIR__ . '/mudar.css';
$versaoCss = file_exists($caminhoCss) ? filemtime($caminhoCss) : time();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>Nova Senha</title>

    <link
        rel="stylesheet"
        href="mudar.css?v=<?= $versaoCss ?>"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="../global/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/forms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/tables.css?v=<?= time() ?>">
</head>
<body>

    <div class="logo">
        FOAG
    </div>

    <main class="login-page">

        <section class="left-section">
            <img
                src="../img/livro.png"
                alt="Imagem FOAG"
            >
        </section>

        <section class="right-section">

            <div class="recuperacao-box etapa3-box">

                <div class="progresso-recuperacao">

                    <div class="progresso-item ativo concluido">
                        <span>✓</span>
                        <small>E-mail</small>
                    </div>

                    <div class="progresso-linha ativa"></div>

                    <div class="progresso-item ativo concluido">
                        <span>✓</span>
                        <small>Segurança</small>
                    </div>

                    <div class="progresso-linha ativa"></div>

                    <div class="progresso-item ativo">
                        <span>3</span>
                        <small>Nova senha</small>
                    </div>

                </div>

                <h1>Nova senha</h1>

                <p class="descricao">
                    Defina sua nova senha para voltar a acessar sua conta.
                </p>

                <div class="conta-etapa3">
                    <div class="conta-etapa3-icone">✓</div>

                    <div class="conta-etapa3-info">
                        <span>Conta verificada</span>
                        <strong><?= htmlspecialchars($emailExibido, ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                </div>

                <form
                    method="POST"
                    action="../api/auth/redefinir_senha.php"
                    id="form-nova-senha"
                >

                    <div class="campo">
                        <label for="nova_senha">Nova senha</label>

                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="nova_senha"
                                name="nova_senha"
                                placeholder="Digite sua nova senha"
                                autocomplete="new-password"
                                required
                            >

                            <span
                                class="toggle-visibility"
                                data-target="nova_senha"
                            >
                                🙈
                            </span>
                        </div>
                    </div>

                    <div class="campo">
                        <label for="confirmar_senha">Confirmar nova senha</label>

                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="confirmar_senha"
                                name="confirmar_senha"
                                placeholder="Digite novamente"
                                autocomplete="new-password"
                                required
                            >

                            <span
                                class="toggle-visibility"
                                data-target="confirmar_senha"
                            >
                                🙈
                            </span>
                        </div>
                    </div>

                    <div class="requisitos-senha etapa3-requisitos">
                        <strong>Sua senha deve conter:</strong>

                        <div class="requisitos-grid">
                            <span id="req-tamanho">✓ 8 caracteres</span>
                            <span id="req-maiuscula">✓ Letra maiúscula</span>
                            <span id="req-numero">✓ Um número</span>
                            <span id="req-simbolo">✓ Um símbolo</span>
                        </div>
                    </div>

                    <div
                        id="erro-senha"
                        class="mensagem-erro erro-senha"
                    ></div>

                    <button type="submit">
                        Alterar senha
                    </button>
                </form>

                <a
                    href="esqueci.php?novo=1"
                    class="voltar-login"
                >
                    ← Voltar
                </a>

            </div>

        </section>

    </main>

    <script>
        document
            .querySelectorAll('.toggle-visibility')
            .forEach(icon => {
                icon.addEventListener('click', function () {
                    const input = document.getElementById(this.dataset.target);

                    if (input.type === 'password') {
                        input.type = 'text';
                        this.textContent = '🙉';
                    } else {
                        input.type = 'password';
                        this.textContent = '🙈';
                    }
                });
            });

        const senha = document.getElementById('nova_senha');
        const confirmar = document.getElementById('confirmar_senha');
        const form = document.getElementById('form-nova-senha');
        const erro = document.getElementById('erro-senha');

        function alterarRequisito(id, valido) {
            const elemento = document.getElementById(id);
            elemento.classList.toggle('ok', valido);
        }

        function validarRequisitos() {
            const valor = senha.value;

            alterarRequisito('req-tamanho', valor.length >= 8);
            alterarRequisito('req-maiuscula', /[A-Z]/.test(valor));
            alterarRequisito('req-numero', /\d/.test(valor));
            alterarRequisito('req-simbolo', /[!@#$%^&*()\-_=+{};:,<.>]/.test(valor));
        }

        senha.addEventListener('input', validarRequisitos);

        form.addEventListener('submit', function (event) {
            erro.classList.remove('visivel');

            const regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/;

            if (!regex.test(senha.value)) {
                event.preventDefault();
                erro.textContent = 'Sua senha ainda não atende a todos os requisitos.';
                erro.classList.add('visivel');
                return;
            }

            if (senha.value !== confirmar.value) {
                event.preventDefault();
                erro.textContent = 'As senhas digitadas não são iguais.';
                erro.classList.add('visivel');
                confirmar.focus();
            }
        });
    </script>

</body>
</html>