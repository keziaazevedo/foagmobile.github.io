<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contato | FOAG</title>
  <link rel="stylesheet" href="sobre_contato.css">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../global/css/cursor.css">
</head>

<body>

<main class="page-content">

  <section class="page-header">
    <span>
      <i class="fa-solid fa-envelope"></i>
      Contato
    </span>

    <h1>Como podemos ajudar?</h1>

    <p>
      Encontrou um problema, tem uma dúvida ou quer enviar uma sugestão?
      Fale com a equipe do FOAG.
    </p>
  </section>

  <div class="contact-container">

    <aside class="contact-info">

      <h2>Fale com a gente</h2>

      <p>
        Escolha o assunto e envie sua mensagem. Isso ajuda nossa equipe
        a entender melhor o que você precisa.
      </p>

      <div class="contact-item">
        <div class="icon">
          <i class="fa-solid fa-circle-question"></i>
        </div>

        <div>
          <h3>Dúvidas</h3>
          <p>Precisa de ajuda para utilizar alguma ferramenta?</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="icon">
          <i class="fa-solid fa-bug"></i>
        </div>

        <div>
          <h3>Problemas</h3>
          <p>Encontrou algum erro ou algo não está funcionando?</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="icon">
          <i class="fa-solid fa-lightbulb"></i>
        </div>

        <div>
          <h3>Sugestões</h3>
          <p>Tem alguma ideia que poderia melhorar o FOAG?</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="icon">
          <i class="fa-solid fa-universal-access"></i>
        </div>

        <div>
          <h3>Acessibilidade</h3>
          <p>Conte para nós sobre dificuldades ou melhorias de acessibilidade.</p>
        </div>
      </div>

    </aside>

    <section class="contact-form">

      <h2>Envie uma mensagem</h2>

      <form action="enviar_contato.php" method="POST">

        <div class="form-row">

          <div class="form-group">
            <label for="nome">Nome</label>

            <input
              type="text"
              id="nome"
              name="nome"
              placeholder="Seu nome"
              required
            >
          </div>

          <div class="form-group">
            <label for="email">E-mail</label>

            <input
              type="email"
              id="email"
              name="email"
              placeholder="seuemail@email.com"
              required
            >
          </div>

        </div>

        <div class="form-group">
          <label for="assunto">Assunto</label>

          <select id="assunto" name="assunto" required>
            <option value="">Selecione uma opção</option>
            <option value="duvida">Dúvida</option>
            <option value="problema">Problema no FOAG</option>
            <option value="sugestao">Sugestão</option>
            <option value="acessibilidade">Acessibilidade</option>
            <option value="outro">Outro</option>
          </select>
        </div>

        <div class="form-group">
          <label for="mensagem">Mensagem</label>

          <textarea
            id="mensagem"
            name="mensagem"
            placeholder="Conte para nós como podemos ajudar..."
            required
          ></textarea>
        </div>

        <button type="submit" class="send-button">
          <i class="fa-solid fa-paper-plane"></i>
          Enviar mensagem
        </button>

        <p class="help-message">
          Evite enviar senhas ou outras informações pessoais sensíveis.
        </p>

      </form>

    </section>

  </div>

</main>

    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>