document.addEventListener('DOMContentLoaded', () => {

  // ==========================================
  // DADOS VINDOS DO PHP
  // ==========================================

  const materiasData =
    window.MATERIAS_DATA &&
    typeof window.MATERIAS_DATA === 'object'
      ? window.MATERIAS_DATA
      : {
          materias: []
        };

  const flashcardsData =
    window.FLASHCARDS_DATA &&
    typeof window.FLASHCARDS_DATA === 'object'
      ? window.FLASHCARDS_DATA
      : {
          baralhos: []
        };

  const SAVE_URL =
    window.FLASHCARDS_SAVE_URL ||
    FOAG_CONFIG.endpoints.flashcardsBaralhoSalvar;


  // ==========================================
  // NORMALIZAR DADOS
  // ==========================================

  const materias =
    Array.isArray(materiasData.materias)
      ? materiasData.materias
      : [];

  let baralhos =
    Array.isArray(flashcardsData.baralhos)
      ? flashcardsData.baralhos
      : [];


  // ==========================================
  // ELEMENTOS
  // ==========================================

  const deckModal =
    document.getElementById('deck-modal');

  const deckForm =
    document.getElementById('deck-form');

  const deckSubject =
    document.getElementById('deck-subject');

  const deckName =
    document.getElementById('deck-name');

  const deckDescription =
    document.getElementById('deck-description');

  const filterSubject =
    document.getElementById('filter-subject');

  const decksGrid =
    document.getElementById('decks-grid');

  const decksEmpty =
    document.getElementById('decks-empty');

  const statDecks =
    document.getElementById('stat-decks');

  const statCards =
    document.getElementById('stat-cards');

  const statReviewed =
    document.getElementById('stat-reviewed');

  const toast =
    document.getElementById('toast');

  const logoutModal =
    document.getElementById('logout-modal');

  const submitButton =
    deckForm?.querySelector(
      'button[type="submit"]'
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
          { duracao: 2600, classe: 'show' }
        );
        return;
      }

      toast.textContent = message;
      toast.classList.add('show');
      toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
    }


  // ==========================================
  // BUSCAR MATÉRIA
  // ==========================================

  function getMateria(nome) {

    return materias.find((materia) => {

      return (
        String(materia.nome || '').trim() ===
        String(nome || '').trim()
      );

    }) || null;

  }


  // ==========================================
  // PREENCHER MATÉRIAS
  // ==========================================

  function carregarMaterias() {

    if (deckSubject) {

      deckSubject.innerHTML = '';

      if (materias.length === 0) {

        const option =
          document.createElement('option');

        option.value = '';

        option.textContent =
          'Nenhuma matéria cadastrada';

        deckSubject.appendChild(option);

        deckSubject.disabled = true;

      } else {

        deckSubject.disabled = false;

        materias.forEach((materia) => {

          const nome =
            String(
              materia.nome || ''
            ).trim();

          if (!nome) {
            return;
          }

          const option =
            document.createElement('option');

          option.value = nome;

          option.textContent = nome;

          deckSubject.appendChild(option);

        });

      }

    }


    // FILTRO

    if (filterSubject) {

      filterSubject.innerHTML = '';

      const todas =
        document.createElement('option');

      todas.value = 'todos';

      todas.textContent =
        'Todas as matérias';

      filterSubject.appendChild(todas);


      materias.forEach((materia) => {

        const nome =
          String(
            materia.nome || ''
          ).trim();

        if (!nome) {
          return;
        }

        const option =
          document.createElement('option');

        option.value = nome;

        option.textContent = nome;

        filterSubject.appendChild(option);

      });

    }

  }


  // ==========================================
  // CONTAR CARTÕES
  // ==========================================

  function getTotalCards() {

    return baralhos.reduce(
      (total, baralho) => {

        const cartoes =
          Array.isArray(baralho.cartoes)
            ? baralho.cartoes
            : [];

        return (
          total +
          cartoes.length
        );

      },
      0
    );

  }


  // ==========================================
  // CARTÕES REVISADOS HOJE
  // ==========================================

  function getReviewedToday() {

    const hoje =
      new Date();

    const inicioHoje =
      new Date(
        hoje.getFullYear(),
        hoje.getMonth(),
        hoje.getDate()
      ).getTime();

    const fimHoje =
      inicioHoje +
      86400000 -
      1;

    let total = 0;


    baralhos.forEach((baralho) => {

      const cartoes =
        Array.isArray(baralho.cartoes)
          ? baralho.cartoes
          : [];

      cartoes.forEach((cartao) => {

        // Futuramente cada cartão poderá
        // guardar suas revisões aqui.
        const revisoes =
          Array.isArray(cartao.revisoes)
            ? cartao.revisoes
            : [];

        revisoes.forEach((revisao) => {

          const timestamp =
            Number(
              revisao.ts ||
              revisao.timestamp ||
              0
            );

          if (
            timestamp >= inicioHoje &&
            timestamp <= fimHoje
          ) {

            total++;

          }

        });

      });

    });


    return total;

  }


  // ==========================================
  // ESTATÍSTICAS
  // ==========================================

  function updateStats() {

    if (statDecks) {

      statDecks.textContent =
        baralhos.length;

    }


    if (statCards) {

      statCards.textContent =
        getTotalCards();

    }


    if (statReviewed) {

      statReviewed.textContent =
        getReviewedToday();

    }

  }


  // ==========================================
  // ESTADO DA LISTA
  // ==========================================

  function updateDecksState(
    quantidade
  ) {

    if (quantidade > 0) {

      if (decksEmpty) {
        decksEmpty.hidden = true;
      }

      if (decksGrid) {
        decksGrid.hidden = false;
      }

    } else {

      if (decksEmpty) {
        decksEmpty.hidden = false;
      }

      if (decksGrid) {
        decksGrid.hidden = true;
      }

    }

  }


  // ==========================================
  // CRIAR CARD DO BARALHO
  // ==========================================

  function createDeckCard(
    baralho
  ) {

    const materia =
      getMateria(
        baralho.materia
      );


    const cor =
      baralho.cor ||
      materia?.cor ||
      '#38a5ff';


    const icone =
      baralho.icone ||
      materia?.icone ||
      'fa-book';


    const cartoes =
      Array.isArray(
        baralho.cartoes
      )
        ? baralho.cartoes
        : [];


    const card =
      document.createElement(
        'article'
      );


    card.className =
      'deck-card';


    card.dataset.id =
      baralho.id || '';


    card.dataset.subject =
      baralho.materia || '';


    card.style.setProperty(
      '--subject-color',
      cor
    );


    // ==========================
    // TOPO
    // ==========================

    const top =
      document.createElement(
        'div'
      );

    top.className =
      'deck-card-top';


    const iconBox =
      document.createElement(
        'div'
      );

    iconBox.className =
      'deck-icon';


    const icon =
      document.createElement(
        'i'
      );

    icon.className =
      `fa-solid ${icone}`;


    iconBox.appendChild(icon);


    const title =
      document.createElement(
        'h3'
      );

    title.textContent =
      baralho.nome ||
      'Sem nome';


    top.appendChild(
      iconBox
    );

    top.appendChild(
      title
    );


    // ==========================
    // MATÉRIA
    // ==========================

    const subject =
      document.createElement(
        'span'
      );

    subject.className =
      'deck-subject';

    subject.textContent =
      baralho.materia ||
      'Sem matéria';


    // ==========================
    // DESCRIÇÃO
    // ==========================

    const description =
      document.createElement(
        'p'
      );

    description.className =
      'deck-description';


    if (
      baralho.descricao &&
      String(
        baralho.descricao
      ).trim() !== ''
    ) {

      description.textContent =
        baralho.descricao;

    } else {

      description.textContent =
        'Adicione cartões para começar sua revisão.';

    }


    // ==========================
    // RODAPÉ
    // ==========================

    const footer =
      document.createElement(
        'div'
      );

    footer.className =
      'deck-footer';


    const count =
      document.createElement(
        'span'
      );

    count.className =
      'deck-count';


    const countIcon =
      document.createElement(
        'i'
      );

    countIcon.className =
      'fa-solid fa-clone';


    const countText =
      document.createElement(
        'span'
      );


    countText.textContent =
      cartoes.length === 1
        ? '1 cartão'
        : `${cartoes.length} cartões`;


    count.appendChild(
      countIcon
    );

    count.appendChild(
      countText
    );


    
    // ==========================
    // AÇÕES DO BARALHO
    // ==========================

    const deckActions =
    document.createElement('div');

    deckActions.className =
    'deck-actions';


    // BOTÃO GERENCIAR

    const manageButton =
    document.createElement('button');

    manageButton.type =
    'button';

    manageButton.className =
    'deck-manage-btn';

    manageButton.innerHTML = `
    <i class="fa-solid fa-pen-to-square"></i>
    Gerenciar
    `;

    manageButton.addEventListener(
    'click',
    () => {

        window.location.href =
        `${FOAG_CONFIG.url('estudos/flashcards/baralho.php')}?id=${encodeURIComponent(baralho.id)}`;

    }
    );


    // BOTÃO ESTUDAR

    const studyButton =
    document.createElement('button');

    studyButton.type =
    'button';

    studyButton.className =
    'deck-study-btn';

    studyButton.innerHTML = `
    <i class="fa-solid fa-play"></i>
    Estudar
    `;

    studyButton.addEventListener(
    'click',
    () => {

        if (
        cartoes.length === 0
        ) {

        showToast(
            `O baralho “${baralho.nome}” ainda não possui cartões.`
        );

        return;

        }

        window.location.href =
        `${FOAG_CONFIG.url('estudos/flashcards/estudar.php')}?id=${encodeURIComponent(baralho.id)}`;

    }
    );


    deckActions.appendChild(
    manageButton
    );

    deckActions.appendChild(
    studyButton
    );


    footer.appendChild(
    count
    );

    footer.appendChild(
    deckActions
    );

    // ==========================
    // MONTAR CARD
    // ==========================

    card.appendChild(
      top
    );

    card.appendChild(
      subject
    );

    card.appendChild(
      description
    );

    card.appendChild(
      footer
    );


    return card;

  }


  // ==========================================
  // RENDERIZAR BARALHOS
  // ==========================================

  function renderDecks() {

    if (!decksGrid) {
      return;
    }


    decksGrid.innerHTML = '';


    const filtro =
      filterSubject?.value ||
      'todos';


    const filtrados =
      baralhos.filter(
        (baralho) => {

          if (
            filtro === 'todos'
          ) {

            return true;

          }


          return (
            baralho.materia ===
            filtro
          );

        }
      );


    filtrados.forEach(
      (baralho) => {

        const card =
          createDeckCard(
            baralho
          );

        decksGrid.appendChild(
          card
        );

      }
    );


    updateDecksState(
      filtrados.length
    );


    updateStats();

  }


  // ==========================================
  // ABRIR MODAL
  // ==========================================

  function openDeckModal() {

    if (!deckModal) {
      return;
    }


    if (
      materias.length === 0
    ) {

      showToast(
        'Cadastre uma matéria primeiro na página inicial de Estudos.'
      );

      return;

    }


    deckModal.classList.add(
      'open'
    );


    deckModal.setAttribute(
      'aria-hidden',
      'false'
    );


    setTimeout(() => {

      deckName?.focus();

    }, 50);

  }


  // ==========================================
  // FECHAR MODAL
  // ==========================================

  function closeDeckModal() {

    if (!deckModal) {
      return;
    }


    deckModal.classList.remove(
      'open'
    );


    deckModal.setAttribute(
      'aria-hidden',
      'true'
    );


    deckForm?.reset();


    // Volta para primeira matéria
    if (
      deckSubject &&
      deckSubject.options.length
    ) {

      deckSubject.selectedIndex =
        0;

    }

  }


  // ==========================================
  // BOTÕES DO MODAL
  // ==========================================

  document
    .getElementById(
      'open-deck-modal'
    )
    ?.addEventListener(
      'click',
      openDeckModal
    );


  document
    .getElementById(
      'open-deck-modal-empty'
    )
    ?.addEventListener(
      'click',
      openDeckModal
    );


  document
    .getElementById(
      'close-deck-modal'
    )
    ?.addEventListener(
      'click',
      closeDeckModal
    );


  document
    .getElementById(
      'cancel-deck-modal'
    )
    ?.addEventListener(
      'click',
      closeDeckModal
    );


  // ==========================================
  // FECHAR CLICANDO FORA
  // ==========================================

  deckModal?.addEventListener(
    'click',
    (event) => {

      if (
        event.target ===
        deckModal
      ) {

        closeDeckModal();

      }

    }
  );


  // ==========================================
  // ESC
  // ==========================================

  document.addEventListener(
    'keydown',
    (event) => {

      if (
        event.key === 'Escape' &&
        deckModal?.classList.contains(
          'open'
        )
      ) {

        closeDeckModal();

      }


      if (
        event.key === 'Escape' &&
        logoutModal?.classList.contains(
          'open'
        )
      ) {

        logoutModal.classList.remove(
          'open'
        );

      }

    }
  );


  // ==========================================
  // FILTRO
  // ==========================================

  filterSubject?.addEventListener(
    'change',
    renderDecks
  );


  // ==========================================
  // CRIAR BARALHO
  // ==========================================

  deckForm?.addEventListener(
    'submit',
    async (event) => {

      event.preventDefault();


      const materia =
        deckSubject?.value
          ?.trim();


      const nome =
        deckName?.value
          ?.trim();


      const descricao =
        deckDescription?.value
          ?.trim() || '';


      if (!materia) {

        showToast(
          'Escolha uma matéria.'
        );

        return;

      }


      if (!nome) {

        deckName?.focus();

        return;

      }


      // Não deixar baralho repetido
      // dentro da mesma matéria

      const existe =
        baralhos.some(
          (baralho) => {

            return (
              String(
                baralho.nome || ''
              )
                .trim()
                .toLowerCase() ===
                nome.toLowerCase() &&

              baralho.materia ===
                materia
            );

          }
        );


      if (existe) {

        showToast(
          'Já existe um baralho com esse nome nessa matéria.'
        );

        return;

      }


      const materiaInfo =
        getMateria(
          materia
        );


      const dados = {

        nome:
          nome,

        materia:
          materia,

        descricao:
          descricao,

        cor:
          materiaInfo?.cor ||
          '#38a5ff',

        icone:
          materiaInfo?.icone ||
          'fa-book'

      };


      // ==========================
      // BOTÃO SALVANDO
      // ==========================

      const originalText =
        submitButton
          ?.innerHTML;


      if (submitButton) {

        submitButton.disabled =
          true;


        submitButton.innerHTML = `
          <i class="fa-solid fa-spinner fa-spin"></i>
          Criando...
        `;

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
                JSON.stringify(
                  dados
                )
            }
          );


        let result = null;


        try {

          result =
            await response.json();

        } catch (error) {

          throw new Error(
            'O servidor retornou uma resposta inválida.'
          );

        }


        if (
          !response.ok ||
          !result.sucesso
        ) {

          throw new Error(
            result.mensagem ||
            'Não foi possível criar o baralho.'
          );

        }


        // ==========================
        // ADICIONAR NA LISTA
        // ==========================

        if (result.baralho) {

          baralhos.push(
            result.baralho
          );

        }


        closeDeckModal();

        renderDecks();


        showToast(
          `Baralho “${nome}” criado com sucesso.`
        );


      } catch (error) {

        console.error(
          'Erro ao criar baralho:',
          error
        );


        showToast(
          error.message ||
          'Erro ao criar baralho.'
        );


      } finally {

        if (submitButton) {

          submitButton.disabled =
            false;


          submitButton.innerHTML =
            originalText;

        }

      }

    }
  );


  // ==========================================
  // PERFIL
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


  // ==========================================
  // CONFIGURAÇÕES
  // ==========================================

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


  // ==========================================
  // LOGOUT
  // ==========================================

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

  carregarMaterias();

  renderDecks();

});