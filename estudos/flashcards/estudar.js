document.addEventListener(
  'DOMContentLoaded',
  () => {

    const data =
      window.ESTUDO_DATA &&
      typeof window.ESTUDO_DATA === 'object'
        ? window.ESTUDO_DATA
        : {};

    const baralho =
      data.baralho || {};

    const SAVE_URL =
      window.REVISAO_SAVE_URL ||
      FOAG_CONFIG.endpoints.flashcardsRevisaoSalvar;

    let cartoes =
      Array.isArray(baralho.cartoes)
        ? [...baralho.cartoes]
        : [];

    let indiceAtual = 0;

    let virado = false;

    let acertosSessao = 0;

    let errosSessao = 0;


    const studyArea =
      document.getElementById(
        'study-area'
      );

    const finishScreen =
      document.getElementById(
        'finish-screen'
      );

    const studyCard =
      document.getElementById(
        'study-card'
      );

    const questionText =
      document.getElementById(
        'question-text'
      );

    const answerText =
      document.getElementById(
        'answer-text'
      );

    const progressText =
      document.getElementById(
        'progress-text'
      );

    const progressBar =
      document.getElementById(
        'study-progress-bar'
      );

    const revealBtn =
      document.getElementById(
        'reveal-btn'
      );

    const revealActions =
      document.getElementById(
        'reveal-actions'
      );

    const answerActions =
      document.getElementById(
        'answer-actions'
      );

    const correctBtn =
      document.getElementById(
        'correct-btn'
      );

    const wrongBtn =
      document.getElementById(
        'wrong-btn'
      );

    const shuffleBtn =
      document.getElementById(
        'shuffle-btn'
      );

    const restartBtn =
      document.getElementById(
        'restart-btn'
      );

    const sessionHits =
      document.getElementById(
        'session-hits'
      );

    const sessionErrors =
      document.getElementById(
        'session-errors'
      );

    const finishHits =
      document.getElementById(
        'finish-hits'
      );

    const finishErrors =
      document.getElementById(
        'finish-errors'
      );

    const finishPercent =
      document.getElementById(
        'finish-percent'
      );

    const toast =
      document.getElementById(
        'toast'
      );

    const logoutModal =
      document.getElementById(
        'logout-modal'
      );

    let toastTimer = null;


    // ==========================================
    // TOAST
    // ==========================================

    function showToast(message) {
      if (!toast) {
        return;
      }

      clearTimeout(toastTimer);

      if (window.FOAG?.ui?.showSimpleToast) {
        toastTimer = FOAG.ui.showSimpleToast(
          toast,
          message,
          { duracao: 2500, classe: 'show' }
        );
        return;
      }

      toast.textContent = message;
      toast.classList.add('show');
      toastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
    }


    // ==========================================
    // ATUALIZAR ESTATÍSTICAS
    // ==========================================

    function updateSessionStats() {

      if (sessionHits) {
        sessionHits.textContent =
          acertosSessao;
      }

      if (sessionErrors) {
        sessionErrors.textContent =
          errosSessao;
      }

    }


    // ==========================================
    // MOSTRAR CARTÃO
    // ==========================================

    function renderCard() {

      if (
        cartoes.length === 0 ||
        indiceAtual >= cartoes.length
      ) {

        finishStudy();

        return;
      }


      const cartao =
        cartoes[indiceAtual];


      virado = false;


      studyCard?.classList.remove(
        'flipped'
      );


      if (questionText) {

        questionText.textContent =
          cartao.pergunta || '';

      }


      if (answerText) {

        answerText.textContent =
          cartao.resposta || '';

      }


      if (progressText) {

        progressText.textContent =
          `${indiceAtual + 1} de ${cartoes.length}`;

      }


      if (progressBar) {

        const progresso =
          (
            (indiceAtual + 1) /
            cartoes.length
          ) * 100;


        progressBar.style.width =
          `${progresso}%`;

      }


      if (revealActions) {

        revealActions.hidden =
          false;

      }


      if (answerActions) {

        answerActions.hidden =
          true;

      }


      if (correctBtn) {
        correctBtn.disabled = false;
      }


      if (wrongBtn) {
        wrongBtn.disabled = false;
      }

    }


    // ==========================================
    // VIRAR CARTÃO
    // ==========================================

    function revealAnswer() {

      if (!studyCard) {
        return;
      }


      virado = true;


      studyCard.classList.add(
        'flipped'
      );


      if (revealActions) {

        revealActions.hidden =
          true;

      }


      if (answerActions) {

        answerActions.hidden =
          false;

      }

    }


    function flipCard() {

      if (!studyCard) {
        return;
      }


      if (!virado) {

        revealAnswer();

        return;

      }


      studyCard.classList.remove(
        'flipped'
      );


      virado = false;

    }


    studyCard?.addEventListener(
      'click',
      flipCard
    );


    revealBtn?.addEventListener(
      'click',
      revealAnswer
    );


    // ==========================================
    // SALVAR RESULTADO
    // ==========================================

    async function saveResult(
      resultado
    ) {

      const cartao =
        cartoes[indiceAtual];


      if (!cartao) {
        return;
      }


      if (correctBtn) {
        correctBtn.disabled = true;
      }


      if (wrongBtn) {
        wrongBtn.disabled = true;
      }


      try {

        const response =
          await fetch(
            SAVE_URL,
            {

              method:
                'POST',

              credentials:
                'same-origin',

              headers: {

                'Content-Type':
                  'application/json'

              },

              body:
                JSON.stringify({

                  baralho_id:
                    baralho.id,

                  cartao_id:
                    cartao.id,

                  resultado:
                    resultado,

                  concluiu_baralho:
                    indiceAtual ===
                    cartoes.length - 1

                })

            }
          );


        let result;


        try {

          result =
            await response.json();

        } catch (error) {

          throw new Error(
            'Resposta inválida do servidor.'
          );

        }


        if (
          !response.ok ||
          !result.sucesso
        ) {

          throw new Error(
            result.mensagem ||
            'Não foi possível salvar a revisão.'
          );

        }


        // ==========================================
        // MODAL DE ESTRELAS
        // ==========================================

        const estrelas =
          Number(
            result.pontos?.estrelas ||
            0
          );

        const estrelasRevisao =
          Number(
            result.pontos?.revisao_baralho ||
            0
          );

        const bonusTresBaralhos =
          Number(
            result.pontos?.bonus_3_baralhos ||
            0
          );

        if (
          estrelas > 0 &&
          typeof window.mostrarModalEstrelas ===
            'function'
        ) {

          let mensagem =
            'Revisão concluída! Continue assim! :)';

          if (
            estrelasRevisao > 0 &&
            bonusTresBaralhos > 0
          ) {

            mensagem =
              'Revisão concluída e você revisou 3 baralhos diferentes hoje! :)';

          } else if (
            bonusTresBaralhos > 0
          ) {

            mensagem =
              'Você revisou 3 baralhos diferentes hoje! Excelente! :)';
          }

          window.mostrarModalEstrelas(
            estrelas,
            mensagem
          );
        }


        if (
          resultado === 'acerto'
        ) {

          acertosSessao++;

        } else {

          errosSessao++;

        }


        updateSessionStats();


        indiceAtual++;


        setTimeout(
          () => {

            renderCard();

          },
          180
        );


      } catch (error) {

        console.error(
          'Erro ao salvar revisão:',
          error
        );


        showToast(
          error.message ||
          'Erro ao salvar revisão.'
        );


        if (correctBtn) {
          correctBtn.disabled = false;
        }


        if (wrongBtn) {
          wrongBtn.disabled = false;
        }

      }

    }


    correctBtn?.addEventListener(
      'click',
      () => {

        saveResult(
          'acerto'
        );

      }
    );


    wrongBtn?.addEventListener(
      'click',
      () => {

        saveResult(
          'erro'
        );

      }
    );


    // ==========================================
    // FINALIZAR
    // ==========================================

    function finishStudy() {

      if (studyArea) {

        studyArea.hidden =
          true;

      }


      if (finishScreen) {

        finishScreen.hidden =
          false;

      }


      const total =
        acertosSessao +
        errosSessao;


      const porcentagem =
        total > 0
          ? Math.round(
              (
                acertosSessao /
                total
              ) *
              100
            )
          : 0;


      if (finishHits) {

        finishHits.textContent =
          acertosSessao;

      }


      if (finishErrors) {

        finishErrors.textContent =
          errosSessao;

      }


      if (finishPercent) {

        finishPercent.textContent =
          `${porcentagem}%`;

      }

    }


    // ==========================================
    // RECOMEÇAR
    // ==========================================

    function restartStudy() {

      indiceAtual = 0;

      acertosSessao = 0;

      errosSessao = 0;

      virado = false;


      updateSessionStats();


      if (finishScreen) {

        finishScreen.hidden =
          true;

      }


      if (studyArea) {

        studyArea.hidden =
          false;

      }


      renderCard();

    }


    restartBtn?.addEventListener(
      'click',
      restartStudy
    );


    // ==========================================
    // EMBARALHAR
    // ==========================================

    shuffleBtn?.addEventListener(
      'click',
      () => {

        for (
          let i = cartoes.length - 1;
          i > 0;
          i--
        ) {

          const j =
            Math.floor(
              Math.random() *
              (i + 1)
            );


          [
            cartoes[i],
            cartoes[j]
          ] = [
            cartoes[j],
            cartoes[i]
          ];

        }


        indiceAtual = 0;

        acertosSessao = 0;

        errosSessao = 0;


        updateSessionStats();


        if (finishScreen) {

          finishScreen.hidden =
            true;

        }


        if (studyArea) {

          studyArea.hidden =
            false;

        }


        renderCard();


        showToast(
          'Cartões embaralhados!'
        );

      }
    );


    // ==========================================
    // CABEÇALHO
    // ==========================================

    document
      .getElementById(
        'icon-perfil'
      )
      ?.addEventListener(
        'click',
        () => {

          window.location.href =
            FOAG_CONFIG.pages.perfil;

        }
      );


    document
      .getElementById(
        'icon-configuracoes'
      )
      ?.addEventListener(
        'click',
        () => {

          window.location.href =
            FOAG_CONFIG.pages.configuracoes;

        }
      );


    document
      .getElementById(
        'icon-sair'
      )
      ?.addEventListener(
        'click',
        () => {

          logoutModal?.classList.add(
            'open'
          );

        }
      );


    document
      .getElementById(
        'cancel-logout'
      )
      ?.addEventListener(
        'click',
        () => {

          logoutModal?.classList.remove(
            'open'
          );

        }
      );


    document
      .getElementById(
        'confirm-logout'
      )
      ?.addEventListener(
        'click',
        () => {

          window.location.href =
            FOAG_CONFIG.pages.logout;

        }
      );


    logoutModal?.addEventListener(
      'click',
      (event) => {

        if (
          event.target ===
          logoutModal
        ) {

          logoutModal.classList.remove(
            'open'
          );

        }

      }
    );


    // ==========================================
    // INICIALIZAÇÃO
    // ==========================================

    updateSessionStats();

    renderCard();

  }
);