document.addEventListener(
  'DOMContentLoaded',
  () => {

    // ==========================================
    // DADOS
    // ==========================================

    const baralho =
      window.BARALHO_DATA &&
      typeof window.BARALHO_DATA === 'object'
        ? window.BARALHO_DATA
        : {
            cartoes: []
          };


    const BARALHO_ID =
      window.BARALHO_ID || '';


    const SAVE_URL =
      window.CARTAO_SAVE_URL ||
      FOAG_CONFIG.endpoints.flashcardsCartaoSalvar;


    const EDIT_URL =
      window.CARTAO_EDIT_URL ||
      FOAG_CONFIG.endpoints.flashcardsCartaoEditar;


    const DELETE_URL =
      window.CARTAO_DELETE_URL ||
      FOAG_CONFIG.endpoints.flashcardsCartaoExcluir;


    const BARALHO_COR =
      window.BARALHO_COR ||
      '#38a5ff';


    let cartoes =
      Array.isArray(baralho.cartoes)
        ? [...baralho.cartoes]
        : [];


    // ID do cartão sendo editado
    let editingCardId =
      null;


    // ==========================================
    // ELEMENTOS
    // ==========================================

    const cardsGrid =
      document.getElementById(
        'cards-grid'
      );

    const cardsEmpty =
      document.getElementById(
        'cards-empty'
      );

    const cardsTotal =
      document.getElementById(
        'cards-total'
      );

    const cardModal =
      document.getElementById(
        'card-modal'
      );

    const cardForm =
      document.getElementById(
        'card-form'
      );

    const cardQuestion =
      document.getElementById(
        'card-question'
      );

    const cardAnswer =
      document.getElementById(
        'card-answer'
      );

    const modalTitle =
      document.getElementById(
        'card-modal-title'
      );

    const modalSubtitle =
      document.getElementById(
        'card-modal-subtitle'
      );

    const submitButton =
      document.getElementById(
        'card-submit-btn'
      ) ||
      cardForm?.querySelector(
        'button[type="submit"]'
      );

    const toast =
      document.getElementById(
        'toast'
      );

    const logoutModal =
      document.getElementById(
        'logout-modal'
      );


    let toastTimer =
      null;


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
    // FORMATAR DATA
    // ==========================================

    function formatDate(
      value
    ) {

      if (!value) {
        return '';
      }


      const date =
        new Date(
          String(value)
            .replace(
              ' ',
              'T'
            )
        );


      if (
        Number.isNaN(
          date.getTime()
        )
      ) {

        return '';

      }


      return date.toLocaleDateString(
        'pt-BR'
      );

    }


    // ==========================================
    // ESTADO DA LISTA
    // ==========================================

    function updateCardsState() {

      const quantidade =
        cartoes.length;


      if (cardsTotal) {

        cardsTotal.textContent =
          quantidade;

      }


      if (quantidade > 0) {

        if (cardsGrid) {
          cardsGrid.hidden =
            false;
        }


        if (cardsEmpty) {
          cardsEmpty.hidden =
            true;
        }

      } else {

        if (cardsGrid) {
          cardsGrid.hidden =
            true;
        }


        if (cardsEmpty) {
          cardsEmpty.hidden =
            false;
        }

      }

    }


    // ==========================================
    // ABRIR MODAL NOVO CARTÃO
    // ==========================================

    function openNewCardModal() {

      editingCardId =
        null;


      if (modalTitle) {

        modalTitle.textContent =
          'Novo cartão';

      }


      if (modalSubtitle) {

        modalSubtitle.textContent =
          'Crie uma pergunta e sua resposta.';

      }


      if (submitButton) {

        submitButton.innerHTML = `
          <i class="fa-solid fa-plus"></i>
          Adicionar cartão
        `;

      }


      if (cardQuestion) {

        cardQuestion.value =
          '';

      }


      if (cardAnswer) {

        cardAnswer.value =
          '';

      }


      openModal();

    }


    // ==========================================
    // ABRIR MODAL EDITAR
    // ==========================================

    function openEditCardModal(
      cartao
    ) {

      editingCardId =
        cartao.id;


      if (modalTitle) {

        modalTitle.textContent =
          'Editar cartão';

      }


      if (modalSubtitle) {

        modalSubtitle.textContent =
          'Altere a pergunta ou a resposta do cartão.';

      }


      if (submitButton) {

        submitButton.innerHTML = `
          <i class="fa-solid fa-check"></i>
          Salvar alterações
        `;

      }


      if (cardQuestion) {

        cardQuestion.value =
          cartao.pergunta || '';

      }


      if (cardAnswer) {

        cardAnswer.value =
          cartao.resposta || '';

      }


      openModal();

    }


    // ==========================================
    // ABRIR MODAL
    // ==========================================

    function openModal() {

      if (!cardModal) {
        return;
      }


      cardModal.classList.add(
        'open'
      );


      cardModal.setAttribute(
        'aria-hidden',
        'false'
      );


      setTimeout(
        () => {

          cardQuestion?.focus();

        },
        50
      );

    }


    // ==========================================
    // FECHAR MODAL
    // ==========================================

    function closeCardModal() {

      if (!cardModal) {
        return;
      }


      cardModal.classList.remove(
        'open'
      );


      cardModal.setAttribute(
        'aria-hidden',
        'true'
      );


      cardForm?.reset();


      editingCardId =
        null;

    }


    // ==========================================
    // EXCLUIR CARTÃO
    // ==========================================

    async function deleteCard(
      cartao
    ) {

      const confirmar =
        confirm(
          `Deseja realmente excluir este cartão?\n\n${cartao.pergunta}`
        );


      if (!confirmar) {
        return;
      }


      try {

        const response =
          await fetch(
            DELETE_URL,
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
                    BARALHO_ID,

                  cartao_id:
                    cartao.id

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
            'Não foi possível excluir o cartão.'
          );

        }


        cartoes =
          cartoes.filter(
            (item) => {

              return (
                item.id !==
                cartao.id
              );

            }
          );


        renderCards();


        showToast(
          'Cartão excluído com sucesso!'
        );


      } catch (error) {

        console.error(
          'Erro ao excluir cartão:',
          error
        );


        showToast(
          error.message ||
          'Erro ao excluir cartão.'
        );

      }

    }


    // ==========================================
    // CRIAR ELEMENTO DO CARTÃO
    // ==========================================

    function createCardElement(
      cartao
    ) {

      const card =
        document.createElement(
          'article'
        );


      card.className =
        'flash-card-item';


      card.dataset.id =
        cartao.id || '';


      card.style.setProperty(
        '--deck-color',
        BARALHO_COR
      );


      // ==================================
      // PERGUNTA
      // ==================================

      const questionBlock =
        document.createElement(
          'div'
        );


      questionBlock.className =
        'card-block';


      const questionLabel =
        document.createElement(
          'div'
        );


      questionLabel.className =
        'card-label';


      questionLabel.innerHTML = `
        <i class="fa-regular fa-circle-question"></i>
        Pergunta
      `;


      const questionText =
        document.createElement(
          'div'
        );


      questionText.className =
        'card-text';


      questionText.textContent =
        cartao.pergunta || '';


      questionBlock.appendChild(
        questionLabel
      );


      questionBlock.appendChild(
        questionText
      );


      // ==================================
      // RESPOSTA
      // ==================================

      const answerBlock =
        document.createElement(
          'div'
        );


      answerBlock.className =
        'card-block';


      const answerLabel =
        document.createElement(
          'div'
        );


      answerLabel.className =
        'card-label';


      answerLabel.innerHTML = `
        <i class="fa-regular fa-lightbulb"></i>
        Resposta
      `;


      const answerText =
        document.createElement(
          'div'
        );


      answerText.className =
        'card-text';


      answerText.textContent =
        cartao.resposta || '';


      answerBlock.appendChild(
        answerLabel
      );


      answerBlock.appendChild(
        answerText
      );


      // ==================================
      // RODAPÉ
      // ==================================

      const footer =
        document.createElement(
          'div'
        );


      footer.className =
        'card-footer';


      const created =
        document.createElement(
          'span'
        );


      created.className =
        'card-created';


      const dataCriacao =
        formatDate(
          cartao.criado_em
        );


      created.textContent =
        dataCriacao
          ? `Criado em ${dataCriacao}`
          : '';


      const actions =
        document.createElement(
          'div'
        );


      actions.className =
        'card-actions';


      // ==================================
      // EDITAR
      // ==================================

      const editBtn =
        document.createElement(
          'button'
        );


      editBtn.type =
        'button';


      editBtn.className =
        'card-action-btn edit';


      editBtn.title =
        'Editar cartão';


      editBtn.innerHTML = `
        <i class="fa-solid fa-pen"></i>
      `;


      editBtn.addEventListener(
        'click',
        () => {

          openEditCardModal(
            cartao
          );

        }
      );


      // ==================================
      // EXCLUIR
      // ==================================

      const deleteBtn =
        document.createElement(
          'button'
        );


      deleteBtn.type =
        'button';


      deleteBtn.className =
        'card-action-btn delete';


      deleteBtn.title =
        'Excluir cartão';


      deleteBtn.innerHTML = `
        <i class="fa-regular fa-trash-can"></i>
      `;


      deleteBtn.addEventListener(
        'click',
        () => {

          deleteCard(
            cartao
          );

        }
      );


      actions.appendChild(
        editBtn
      );


      actions.appendChild(
        deleteBtn
      );


      footer.appendChild(
        created
      );


      footer.appendChild(
        actions
      );


      card.appendChild(
        questionBlock
      );


      card.appendChild(
        answerBlock
      );


      card.appendChild(
        footer
      );


      return card;

    }


    // ==========================================
    // RENDERIZAR
    // ==========================================

    function renderCards() {

      if (!cardsGrid) {
        return;
      }


      cardsGrid.innerHTML =
        '';


      cartoes.forEach(
        (cartao) => {

          const element =
            createCardElement(
              cartao
            );


          cardsGrid.appendChild(
            element
          );

        }
      );


      updateCardsState();

    }


    // ==========================================
    // BOTÕES NOVO CARTÃO
    // ==========================================

    document
      .getElementById(
        'open-card-modal'
      )
      ?.addEventListener(
        'click',
        () => {

          openNewCardModal();

        }
      );


    document
      .getElementById(
        'open-card-modal-empty'
      )
      ?.addEventListener(
        'click',
        () => {

          openNewCardModal();

        }
      );


    document
      .getElementById(
        'close-card-modal'
      )
      ?.addEventListener(
        'click',
        closeCardModal
      );


    document
      .getElementById(
        'cancel-card-modal'
      )
      ?.addEventListener(
        'click',
        closeCardModal
      );


    cardModal?.addEventListener(
      'click',
      (event) => {

        if (
          event.target ===
          cardModal
        ) {

          closeCardModal();

        }

      }
    );


    // ==========================================
    // SALVAR / EDITAR
    // ==========================================

    cardForm?.addEventListener(
      'submit',
      async (event) => {

        event.preventDefault();


        const pergunta =
          cardQuestion?.value
            ?.trim();


        const resposta =
          cardAnswer?.value
            ?.trim();


        if (!pergunta) {

          showToast(
            'Digite a pergunta do cartão.'
          );


          cardQuestion?.focus();

          return;

        }


        if (!resposta) {

          showToast(
            'Digite a resposta do cartão.'
          );


          cardAnswer?.focus();

          return;

        }


        const estaEditando =
          editingCardId !== null;


        const url =
          estaEditando
            ? EDIT_URL
            : SAVE_URL;


        const dados = {

          baralho_id:
            BARALHO_ID,

          pergunta:
            pergunta,

          resposta:
            resposta

        };


        if (estaEditando) {

          dados.cartao_id =
            editingCardId;

        }


        const idEditando =
          editingCardId;


        const originalText =
          submitButton?.innerHTML;


        if (submitButton) {

          submitButton.disabled =
            true;


          submitButton.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin"></i>
            Salvando...
          `;

        }


        try {

          const response =
            await fetch(
              url,
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
              'Não foi possível salvar o cartão.'
            );

          }


          if (estaEditando) {

            const indice =
              cartoes.findIndex(
                (cartao) => {

                  return (
                    cartao.id ===
                    idEditando
                  );

                }
              );


            if (
              indice !== -1 &&
              result.cartao
            ) {

              cartoes[indice] =
                result.cartao;

            }


          } else {

            if (result.cartao) {

              cartoes.push(
                result.cartao
              );

            }

          }


          closeCardModal();


          renderCards();


          showToast(
            estaEditando
              ? 'Cartão atualizado com sucesso!'
              : 'Cartão adicionado com sucesso!'
          );


          // ==========================================
          // MODAL DE ESTRELAS
          // ==========================================

          if (!estaEditando) {

            const estrelas =
              Number(
                result.pontos?.estrelas ||
                0
              );

            const recompensas =
              Array.isArray(
                result.pontos?.recompensas
              )
                ? result.pontos.recompensas
                : [];

            if (
              estrelas > 0 &&
              typeof window.mostrarModalEstrelas ===
                'function'
            ) {

              const marcos =
                recompensas
                  .map((item) =>
                    Number(item.marco || 0)
                  )
                  .filter((marco) =>
                    marco > 0
                  );

              let mensagem =
                'Seu baralho ficou ainda mais completo! :)';

              if (
                marcos.includes(15) &&
                marcos.includes(31)
              ) {

                mensagem =
                  'Seu baralho alcançou 15 cartões e também ultrapassou 30 cartões! :)';

              } else if (
                marcos.includes(31)
              ) {

                mensagem =
                  'Seu baralho ultrapassou 30 cartões! Continue assim! :)';

              } else if (
                marcos.includes(15)
              ) {

                mensagem =
                  'Seu baralho alcançou 15 cartões! Agora ele já está pronto para render muitos estudos! :)';
              }

              window.mostrarModalEstrelas(
                estrelas,
                mensagem
              );
            }
          }


        } catch (error) {

          console.error(
            'Erro ao salvar cartão:',
            error
          );


          showToast(
            error.message ||
            'Erro ao salvar cartão.'
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
    // ESC
    // ==========================================

    document.addEventListener(
      'keydown',
      (event) => {

        if (
          event.key !==
          'Escape'
        ) {
          return;
        }


        if (
          cardModal?.classList.contains(
            'open'
          )
        ) {

          closeCardModal();

        }


        if (
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

    renderCards();

  }
);