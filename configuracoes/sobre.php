<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre | FOAG</title>
  <link rel="stylesheet" href="sobre_contato.css">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../global/css/cursor.css">
    <link rel="stylesheet" href="../global/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/forms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../global/css/tables.css?v=<?= time() ?>">

    <link rel="stylesheet" href="../global/css/layout.css?v=<?= time() ?>">
</head>

<body>

<main class="page-content">

  <section class="page-header">
    <span>
      <i class="fa-solid fa-circle-info"></i>
      Sobre o FOAG
    </span>

    <h1>Feito para tornar os estudos mais organizados</h1>

    <p>
      O FOAG reúne ferramentas de organização, acompanhamento e estudo
      em um único ambiente, ajudando estudantes a cuidarem melhor da
      rotina acadêmica.
    </p>
  </section>

  <section class="about-main">
    <h2>O que é o FOAG?</h2>

    <p>
      O FOAG é uma plataforma criada para facilitar a organização da vida
      acadêmica e tornar o processo de estudar mais simples, visual e motivador.
    </p>

    <p>
      Aqui, o estudante pode organizar atividades, acompanhar notas,
      controlar prazos, utilizar ferramentas de estudo e acompanhar sua própria
      evolução ao longo do tempo.
    </p>
  </section>

  <section class="about-grid">

    <div class="about-card">
      <div class="icon">
        <i class="fa-solid fa-bullseye"></i>
      </div>

      <h3>Nossa missão</h3>

      <p>
        Ajudar estudantes a criarem uma rotina mais organizada,
        produtiva e equilibrada.
      </p>
    </div>

    <div class="about-card">
      <div class="icon">
        <i class="fa-solid fa-lightbulb"></i>
      </div>

      <h3>Nosso propósito</h3>

      <p>
        Transformar diferentes ferramentas acadêmicas em uma experiência
        simples e integrada.
      </p>
    </div>

    <div class="about-card">
      <div class="icon">
        <i class="fa-solid fa-universal-access"></i>
      </div>

      <h3>Acessibilidade</h3>

      <p>
        Buscamos tornar o FOAG cada vez mais acessível para diferentes
        estudantes e necessidades.
      </p>
    </div>

  </section>

  <section class="features">

    <h2>O que você encontra no FOAG</h2>

    <div class="features-list">

      <div class="feature">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>Ferramentas de estudo</span>
      </div>

      <div class="feature">
        <i class="fa-solid fa-book"></i>
        <span>Agenda de atividades</span>
      </div>

      <div class="feature">
        <i class="fa-solid fa-calendar-days"></i>
        <span>Calendário acadêmico</span>
      </div>

      <div class="feature">
        <i class="fa-solid fa-chart-line"></i>
        <span>Acompanhamento de desempenho</span>
      </div>

      <div class="feature">
        <i class="fa-solid fa-comments"></i>
        <span>Comunidade de estudantes</span>
      </div>

      <div class="feature">
        <i class="fa-solid fa-trophy"></i>
        <span>Ranking e conquistas</span>
      </div>

      <div class="feature">
        <i class="fa-solid fa-star"></i>
        <span>Sistema de recompensas</span>
      </div>

      <div class="feature">
        <i class="fa-solid fa-store"></i>
        <span>Itens de personalização</span>
      </div>

    </div>

  </section>

</main>

    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>