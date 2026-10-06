<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script src="../../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../../global/js/utils.js?v=<?= time() ?>"></script>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Horário Escolar - Sobre</title>
  <link rel="stylesheet" href="sobre.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

  <!-- Cabeçalho -->
  <header class="cabecalho">
    FOAG
    <div class="header-icons">
      <i id="icon-perfil" class="fa-regular fa-user" title="Perfil"></i>
      <i id="icon-sair" class="fa-solid fa-right-from-bracket" title="Sair"></i>
    </div>
  </header>

  <!-- Container geral -->
  <div class="container">
    <!-- Menu lateral -->
    <nav class="menu">
      <a href="../inicio/inicio.html">Início</a>
      <a href="../agenda/agenda.php">Agenda</a>
      <a href="../calendario/calendario.php">Calendário</a>
      <a href="../HORARIO/horario.php">Horario</a>
      <a href="#">Contato</a>
    </nav>

    <!-- Conteúdo principal -->
    <main class="conteudo-principal">
  <div class="container-sobre">
    <h2>Origem</h2>
    <p>
    O site surgiu da necessidade de tornar a rotina acadêmica mais simples e organizada para os estudantes. Com a correria do dia a dia, é comum que alunos enfrentem dificuldades para controlar faltas, acompanhar prazos e manter um planejamento eficiente para tarefas e horários.
    </p>
    <p>
    Pensando nisso, nasceu a ideia de criar uma plataforma que reunisse, em um só lugar, todas as ferramentas necessárias para facilitar o acompanhamento das atividades escolares. O objetivo é oferecer um recurso prático que ajude os usuários a evitar esquecimentos, melhorar a gestão do tempo e reduzir a sobrecarga causada pela desorganização.
    </p>
    <p>
    Mais do que um simples organizador, o site busca ser um apoio para que os estudantes mantenham uma rotina mais equilibrada e produtiva, garantindo que consigam cumprir suas responsabilidades com tranquilidade e foco.
</p>

    <h3>Missão</h3>
    <p>
      Oferecer uma plataforma eficiente e acessível que auxilie estudantes na gestão de suas faltas, horários e tarefas, promovendo a organização e o sucesso acadêmico.
    </p>

    <h3>Visão</h3>
    <p>
      Ser reconhecido como um recurso confiável e indispensável para estudantes que buscam otimizar sua rotina acadêmica, contribuindo para o desenvolvimento de hábitos organizacionais saudáveis.
    </p>

    <h3>Valores</h3>
    <ul>
      <li><strong>Comprometimento:</strong> Dedicação em fornecer uma ferramenta funcional e de qualidade.</li>
      <li><strong>Simplicidade:</strong> Facilitar o uso para que todos os estudantes possam aproveitar os benefícios sem dificuldades.</li>
      <li><strong>Inovação:</strong> Buscar constantemente melhorias para atender às necessidades dos usuários.</li>
      <li><strong>Colaboração:</strong> Incentivar a união entre estudantes para compartilhar experiências e soluções.</li>
      <li><strong>Responsabilidade:</strong> Respeitar os dados e a privacidade dos usuários, garantindo um ambiente seguro.</li>
    </ul>
  </div>
</section>


    </main>
  </div>

  <!-- Modal de logout -->
  <div id="logout-modal" class="modal">
    <div class="modal-content">
      <h3>Ah... já vai?</h3>
      <h4>Tem certeza de que deseja sair?</h4>
      <div class="modal-buttons">
        <button id="confirm-logout">Sim</button>
        <button id="cancel-logout">Cancelar</button>
      </div>
    </div>
  </div>

  <!-- Rodapé -->
  <footer>
    &copy; 2025 FOAG. Todos os direitos reservados.
  </footer>

  <script>
    const logoutModal = document.getElementById('logout-modal');
    const confirmLogout = document.getElementById('confirm-logout');
    const cancelLogout = document.getElementById('cancel-logout');

    document.getElementById('icon-sair').addEventListener('click', () => {
      logoutModal.style.display = 'flex';
    });

    confirmLogout.addEventListener('click', () => {
      window.location.href = '../index/index.php';
    });

    cancelLogout.addEventListener('click', () => {
      logoutModal.style.display = 'none';
    });

    logoutModal.addEventListener('click', e => {
      if (e.target === logoutModal) {
        logoutModal.style.display = 'none';
      }
    });
  </script>

</body>
</html>
