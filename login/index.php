<!DOCTYPE html>
<html lang="pt-br">

<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Login</title>

  <link rel="stylesheet" href="./estilo.css?v=50">

  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap"
    rel="stylesheet"
  >
    <link rel="stylesheet" href="../global/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/forms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/tables.css?v=<?= time() ?>">
</head>

<body>

  <!-- CABEÇALHO -->
  <div class="logo">
    FOAG
  </div>


  <!-- PÁGINA DE LOGIN -->
  <div class="login-page">


    <!-- LADO ESQUERDO -->
    <div class="left-section">

      <img
        src="../img/login.jpeg"
        alt="Imagem de login"
      >

    </div>


    <!-- LADO DIREITO -->
    <div class="right-section">

      <h1>Login</h1>


      <form
        id="form-login"
        method="POST"
        action="../api/auth/login.php"
        autocomplete="off"
      >


        <!-- CAMPOS ISCA -->
        <div
          aria-hidden="true"
          style="
            position: absolute;
            left: -9999px;
            top: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
          "
        >

          <input
            type="text"
            name="usuario_ignorar"
            autocomplete="username"
            tabindex="-1"
          >

          <input
            type="password"
            name="senha_ignorar"
            autocomplete="current-password"
            tabindex="-1"
          >

        </div>


        <!-- E-MAIL -->
        <label for="email">
          E-mail:
        </label>

        <input
          type="email"
          id="email"
          name="email"
          required
          autocomplete="off"
          autocorrect="off"
          autocapitalize="none"
          spellcheck="false"
          data-lpignore="true"
          data-1p-ignore="true"
          data-bwignore="true"
        >


        <!-- SENHA -->
        <label for="senha">
          Senha:
        </label>


        <div class="password-wrapper">

          <input
            type="password"
            id="senha"
            name="senha"
            required
            autocomplete="off"
            autocorrect="off"
            autocapitalize="none"
            spellcheck="false"
            data-lpignore="true"
            data-1p-ignore="true"
            data-bwignore="true"
          >


          <span
            class="toggle-visibility"
            data-target="senha"
            role="button"
            tabindex="0"
            aria-label="Mostrar ou esconder senha"
          >
            🙈
          </span>

        </div>


        <!-- LINKS -->
        <div class="login-links">

          <a
            href="../cadastro/cadastro.php"
            class="link-cadastro"
          >
            CADASTRE-SE
          </a>


          <a
            href="../mudarsenha/esqueci.php"
            class="link-recuperar"
          >
            ESQUECI MINHA SENHA
          </a>

        </div>


        <!-- BOTÃO -->
        <button type="submit">
          Entrar
        </button>

      </form>

    </div>

  </div>


  <script>

    const emailInput =
      document.getElementById('email');

    const senhaInput =
      document.getElementById('senha');

    const formLogin =
      document.getElementById('form-login');


    /* ==================================================
       LIMPAR PREENCHIMENTO AUTOMÁTICO
    =================================================== */

    function limparAutofill() {

      if (emailInput) {
        emailInput.value = '';
        emailInput.setAttribute(
          'autocomplete',
          'off'
        );
      }


      if (senhaInput) {
        senhaInput.value = '';
        senhaInput.type = 'password';

        senhaInput.setAttribute(
          'autocomplete',
          'off'
        );
      }

    }


    /* ==================================================
       CARREGAMENTO
    =================================================== */

    document.addEventListener(
      'DOMContentLoaded',
      function () {

        limparAutofill();

        setTimeout(
          limparAutofill,
          100
        );

        setTimeout(
          limparAutofill,
          500
        );

        setTimeout(
          limparAutofill,
          1000
        );

      }
    );


    /* ==================================================
       VOLTAR PARA A PÁGINA
    =================================================== */

    window.addEventListener(
      'pageshow',
      function () {

        setTimeout(
          limparAutofill,
          50
        );

      }
    );


    /* ==================================================
       RESETAR FORMULÁRIO
    =================================================== */

    window.addEventListener(
      'load',
      function () {

        if (formLogin) {
          formLogin.reset();
        }

        limparAutofill();

      }
    );


    /* ==================================================
       MOSTRAR / ESCONDER SENHA
    =================================================== */

    document
      .querySelectorAll(
        '.toggle-visibility'
      )
      .forEach(icon => {


        function alternarSenha() {

          const targetId =
            icon.getAttribute(
              'data-target'
            );


          const input =
            document.getElementById(
              targetId
            );


          if (!input) {
            return;
          }


          const isPassword =
            input.type === 'password';


          input.type =
            isPassword
              ? 'text'
              : 'password';


          icon.textContent =
            isPassword
              ? '🙉'
              : '🙈';

        }


        icon.addEventListener(
          'click',
          alternarSenha
        );


        icon.addEventListener(
          'keydown',
          function (event) {

            if (
              event.key === 'Enter' ||
              event.key === ' '
            ) {

              event.preventDefault();

              alternarSenha();

            }

          }
        );

      });

  </script>

</body>

</html>