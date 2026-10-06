document.addEventListener('DOMContentLoaded', () => {

  // ==========================================
  // ELEMENTOS
  // ==========================================

  const subjectModal =
    document.getElementById('subject-modal');

  const subjectForm =
    document.getElementById('subject-form');

  const subjectName =
    document.getElementById('subject-name');

  const subjectColor =
    document.getElementById('subject-color');

  const subjectIcon =
    document.getElementById('subject-icon');

  const subjectModalTitle =
    document.getElementById('subject-modal-title');

  const subjectModalDescription =
    document.getElementById('subject-modal-description');

  const subjectsGrid =
    document.getElementById('subjects-grid');

  const subjectsEmpty =
    document.getElementById('subjects-empty');

  const noResults =
    document.getElementById('subjects-no-results');

  const statSubjects =
    document.getElementById('stat-subjects');

  const toast =
    document.getElementById('toast');

  const logoutModal =
    document.getElementById('logout-modal');

  const deleteSubjectModal =
    document.getElementById('delete-subject-modal');

  const deleteSubjectName =
    document.getElementById('delete-subject-name');

  const confirmDeleteSubject =
    document.getElementById('confirm-delete-subject');

  const cancelDeleteSubject =
    document.getElementById('cancel-delete-subject');

  const submitButton =
    subjectForm?.querySelector(
      'button[type="submit"]'
    );


  // ==========================================
  // URLS
  // ==========================================

  const SAVE_MATERIA_URL =
    window.MATERIAS_SAVE_URL ||
    FOAG_CONFIG.endpoints.estudosMateriaSalvar;

  const UPDATE_MATERIA_URL =
    window.MATERIAS_UPDATE_URL ||
    FOAG_CONFIG.endpoints.estudosMateriaEditar;

  const DELETE_MATERIA_URL =
    window.MATERIAS_DELETE_URL ||
    FOAG_CONFIG.endpoints.estudosMateriaExcluir;


  // ==========================================
  // DADOS DAS MATÉRIAS
  // ==========================================

  const materiasData =
    window.MATERIAS_DATA &&
    typeof window.MATERIAS_DATA === 'object'
      ? window.MATERIAS_DATA
      : {
          materias: []
        };


  let materias =
    Array.isArray(materiasData.materias)
      ? [...materiasData.materias]
      : [];


  // ==========================================
  // DADOS DO POMODORO
  // ==========================================

  const pomodoroData =
    window.POMODORO_DATA &&
    typeof window.POMODORO_DATA === 'object'
      ? window.POMODORO_DATA
      : {
          sessions: []
        };


  const sessoesPomodoro =
    Array.isArray(
      pomodoroData.sessions
    )
      ? pomodoroData.sessions
      : [];


  // ==========================================
  // DADOS DOS FLASHCARDS
  // ==========================================

  const flashcardsData =
    window.FLASHCARDS_DATA &&
    typeof window.FLASHCARDS_DATA === 'object'
      ? window.FLASHCARDS_DATA
      : {
          baralhos: []
        };


  const baralhos =
    Array.isArray(
      flashcardsData.baralhos
    )
      ? flashcardsData.baralhos
      : [];


  let subjectsCount = 0;

  let toastTimer = null;

  let materiaParaExcluir = null;

  let cardParaExcluir = null;

  let materiaEmEdicao = null;


  // ==========================================
  // TOAST
  // ==========================================

  const showToast = (message) => {

    if (!toast) {
      return;
    }


    toast.textContent =
      message;


    toast.classList.add(
      'show'
    );


    clearTimeout(
      toastTimer
    );


    toastTimer =
      setTimeout(
        () => {

          toast.classList.remove(
            'show'
          );

        },
        2600
      );

  };


  // ==========================================
  // NORMALIZAR NOME DA MATÉRIA
  // ==========================================

  function normalizarMateria(
    valor
  ) {

    return String(
      valor || ''
    )
      .trim()
      .toLocaleLowerCase(
        'pt-BR'
      );

  }


  // ==========================================
  // ATUALIZAR ESTADO
  // ==========================================

  const updateSubjectsState = () => {

    subjectsCount =
      materias.length;


    if (statSubjects) {

      statSubjects.textContent =
        subjectsCount;

    }


    if (subjectsCount > 0) {

      if (subjectsEmpty) {

        subjectsEmpty.hidden =
          true;

      }


      if (subjectsGrid) {

        subjectsGrid.hidden =
          false;

      }


      // Evita que a mensagem de busca vazia permaneça visível
      // depois que uma matéria é cadastrada.
      if (noResults) {

        noResults.hidden =
          true;

      }

    } else {

      if (subjectsEmpty) {

        subjectsEmpty.hidden =
          false;

      }


      if (subjectsGrid) {

        subjectsGrid.hidden =
          true;

      }

    }

  };


  // ==========================================
  // CALCULAR TEMPO ESTUDADO
  // ==========================================

  function getMinutosEstudados(
    nomeMateria
  ) {

    const nomeNormalizado =
      normalizarMateria(
        nomeMateria
      );


    let totalMinutos =
      0;


    sessoesPomodoro.forEach(
      (sessao) => {

        const disciplina =
          sessao.discipline ??
          sessao.disciplina ??
          sessao.materia ??
          '';


        const modo =
          sessao.mode ??
          sessao.modo ??
          'focus';


        const minutos =
          Number(
            sessao.minutes ??
            sessao.minutos ??
            0
          );


        if (
          modo !== 'focus'
        ) {

          return;

        }


        if (
          normalizarMateria(
            disciplina
          ) !==
          nomeNormalizado
        ) {

          return;

        }


        if (
          Number.isFinite(
            minutos
          ) &&
          minutos > 0
        ) {

          totalMinutos +=
            minutos;

        }

      }
    );


    return Math.round(
      totalMinutos
    );

  }


  // ==========================================
  // FORMATAR TEMPO ESTUDADO
  // ==========================================

  function formatarTempoEstudado(
    minutos
  ) {

    if (
      !minutos ||
      minutos <= 0
    ) {

      return '0h estudadas';

    }


    const horas =
      Math.floor(
        minutos / 60
      );


    const minutosRestantes =
      minutos % 60;


    if (
      horas === 0
    ) {

      return `${minutosRestantes}min estudados`;

    }


    if (
      minutosRestantes === 0
    ) {

      return `${horas}h estudadas`;

    }


    return `${horas}h ${minutosRestantes}min estudadas`;

  }


  // ==========================================
  // CONTAR FLASHCARDS DA MATÉRIA
  // ==========================================

  function getTotalFlashcards(
    nomeMateria
  ) {

    const nomeNormalizado =
      normalizarMateria(
        nomeMateria
      );


    let total =
      0;


    baralhos.forEach(
      (baralho) => {

        if (
          normalizarMateria(
            baralho.materia
          ) !==
          nomeNormalizado
        ) {

          return;

        }


        const cartoes =
          Array.isArray(
            baralho.cartoes
          )
            ? baralho.cartoes
            : [];


        total +=
          cartoes.length;

      }
    );


    return total;

  }


  // ==========================================
  // ABRIR MODAL NOVA MATÉRIA
  // ==========================================

  const openSubjectModal = () => {

    if (!subjectModal) {
      return;
    }


    materiaEmEdicao =
      null;


    if (subjectForm) {

      subjectForm.reset();

    }


    if (subjectColor) {

      subjectColor.value =
        '#38a5ff';

    }


    if (subjectIcon) {

      subjectIcon.value =
        'fa-book';

    }


    if (subjectModalTitle) {

      subjectModalTitle.textContent =
        'Nova matéria';

    }


    if (subjectModalDescription) {

      subjectModalDescription.textContent =
        'Escolha um nome, uma cor e um ícone para identificar a matéria.';

    }


    if (submitButton) {

      submitButton.innerHTML = `
        <i class="fa-solid fa-plus"></i>
        Adicionar
      `;

    }


    subjectModal.classList.add(
      'open'
    );


    subjectModal.setAttribute(
      'aria-hidden',
      'false'
    );


    setTimeout(
      () => {

        subjectName?.focus();

      },
      50
    );

  };


  // ==========================================
  // ABRIR MODAL EDITAR MATÉRIA
  // ==========================================

  const openEditSubjectModal = (
    materia
  ) => {

    if (
      !subjectModal ||
      !materia
    ) {

      return;

    }


    materiaEmEdicao =
      materia;


    if (subjectName) {

      subjectName.value =
        materia.nome ||
        '';

    }


    if (subjectColor) {

      subjectColor.value =
        materia.cor ||
        '#94a3b8';

    }


    if (subjectIcon) {

      const iconeAtual =
        materia.icone ||
        'fa-circle-question';


      const existeOpcao =
        Array.from(
          subjectIcon.options
        ).some(
          (option) => {

            return (
              option.value ===
              iconeAtual
            );

          }
        );


      if (!existeOpcao) {

        const option =
          document.createElement(
            'option'
          );


        option.value =
          iconeAtual;


        option.textContent =
          'Ícone atual';


        subjectIcon.appendChild(
          option
        );

      }


      subjectIcon.value =
        iconeAtual;

    }


    if (subjectModalTitle) {

      subjectModalTitle.textContent =
        'Editar matéria';

    }


    if (subjectModalDescription) {

      subjectModalDescription.textContent =
        'Altere o nome, a cor ou o ícone. O vínculo com o boletim será mantido.';

    }


    if (submitButton) {

      submitButton.innerHTML = `
        <i class="fa-solid fa-check"></i>
        Salvar alterações
      `;

    }


    subjectModal.classList.add(
      'open'
    );


    subjectModal.setAttribute(
      'aria-hidden',
      'false'
    );


    setTimeout(
      () => {

        subjectName?.focus();

      },
      50
    );

  };


  // ==========================================
  // FECHAR MODAL DE MATÉRIA
  // ==========================================

  const closeSubjectModal = () => {

    if (!subjectModal) {
      return;
    }


    subjectModal.classList.remove(
      'open'
    );


    subjectModal.setAttribute(
      'aria-hidden',
      'true'
    );


    materiaEmEdicao =
      null;


    if (subjectForm) {

      subjectForm.reset();

    }


    if (subjectColor) {

      subjectColor.value =
        '#38a5ff';

    }


    if (subjectIcon) {

      subjectIcon.value =
        'fa-book';

    }


    if (subjectModalTitle) {

      subjectModalTitle.textContent =
        'Nova matéria';

    }


    if (subjectModalDescription) {

      subjectModalDescription.textContent =
        'Escolha um nome, uma cor e um ícone para identificar a matéria.';

    }


    if (
      submitButton &&
      !submitButton.disabled
    ) {

      submitButton.innerHTML = `
        <i class="fa-solid fa-plus"></i>
        Adicionar
      `;

    }

  };


  // ==========================================
  // ABRIR MODAL DE EXCLUSÃO
  // ==========================================

  function excluirMateria(
    materia,
    card
  ) {

    if (!deleteSubjectModal) {

      showToast(
        'Não foi possível abrir a confirmação de exclusão.'
      );

      return;

    }


    materiaParaExcluir =
      materia;


    cardParaExcluir =
      card;


    if (deleteSubjectName) {

      deleteSubjectName.textContent =
        `“${materia.nome}”`;

    }


    deleteSubjectModal.classList.add(
      'open'
    );


    deleteSubjectModal.setAttribute(
      'aria-hidden',
      'false'
    );

  }


  // ==========================================
  // FECHAR MODAL DE EXCLUSÃO
  // ==========================================

  function fecharModalExcluirMateria() {

    if (!deleteSubjectModal) {
      return;
    }


    if (
      confirmDeleteSubject?.disabled
    ) {

      return;

    }


    deleteSubjectModal.classList.remove(
      'open'
    );


    deleteSubjectModal.setAttribute(
      'aria-hidden',
      'true'
    );


    materiaParaExcluir =
      null;


    cardParaExcluir =
      null;

  }


  // ==========================================
  // EXECUTAR EXCLUSÃO
  // ==========================================

  async function executarExclusaoMateria() {

    if (
      !materiaParaExcluir ||
      !cardParaExcluir
    ) {

      return;

    }


    const materia =
      materiaParaExcluir;


    const card =
      cardParaExcluir;


    const botaoCard =
      card.querySelector(
        '.subject-delete-btn'
      );


    const conteudoOriginalConfirmacao =
      confirmDeleteSubject?.innerHTML;


    const conteudoOriginalBotaoCard =
      botaoCard?.innerHTML;


    if (confirmDeleteSubject) {

      confirmDeleteSubject.disabled =
        true;


      confirmDeleteSubject.innerHTML = `
        <i class="fa-solid fa-spinner fa-spin"></i>
        Excluindo...
      `;

    }


    if (botaoCard) {

      botaoCard.disabled =
        true;


      botaoCard.innerHTML = `
        <i class="fa-solid fa-spinner fa-spin"></i>
      `;

    }


    try {

      const response =
        await fetch(
          DELETE_MATERIA_URL,
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

                id:
                  materia.id

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
          'Não foi possível excluir a matéria.'
        );

      }


      materias =
        materias.filter(
          (item) => {

            return (
              item.id !==
              materia.id
            );

          }
        );


      card.remove();


      updateSubjectsState();


      deleteSubjectModal.classList.remove(
        'open'
      );


      deleteSubjectModal.setAttribute(
        'aria-hidden',
        'true'
      );


      materiaParaExcluir =
        null;


      cardParaExcluir =
        null;


      showToast(
        `Matéria “${materia.nome}” e seus flashcards foram excluídos.`
      );


    } catch (error) {

      console.error(
        'Erro ao excluir matéria:',
        error
      );


      showToast(
        error.message ||
        'Erro ao excluir matéria.'
      );


      if (botaoCard) {

        botaoCard.disabled =
          false;


        botaoCard.innerHTML =
          conteudoOriginalBotaoCard ||
          `
            <i class="fa-regular fa-trash-can"></i>
          `;

      }


    } finally {

      if (confirmDeleteSubject) {

        confirmDeleteSubject.disabled =
          false;


        confirmDeleteSubject.innerHTML =
          conteudoOriginalConfirmacao ||
          `
            <i class="fa-regular fa-trash-can"></i>
            Excluir matéria
          `;

      }

    }

  }


  // ==========================================
  // DADOS AUXILIARES DO DASHBOARD
  // ==========================================

  function getFocusSessions(nomeMateria = null) {
    const alvo = nomeMateria ? normalizarMateria(nomeMateria) : null;
    return sessoesPomodoro.filter((sessao) => {
      const modo = sessao.mode ?? sessao.modo ?? 'focus';
      if (modo !== 'focus') return false;
      if (!alvo) return true;
      const disciplina = sessao.discipline ?? sessao.disciplina ?? sessao.materia ?? '';
      return normalizarMateria(disciplina) === alvo;
    });
  }

  function getSessionTimestamp(sessao) {
    const raw = Number(sessao.ts ?? sessao.timestamp ?? sessao.data ?? 0);
    return Number.isFinite(raw) ? raw : 0;
  }

  function getLastStudy(nomeMateria) {
    const sessoes = getFocusSessions(nomeMateria).sort((a,b) => getSessionTimestamp(b) - getSessionTimestamp(a));
    return sessoes[0] || null;
  }

  function formatRelativeDate(timestamp) {
    if (!timestamp) return 'Ainda não estudada';
    const data = new Date(timestamp);
    if (Number.isNaN(data.getTime())) return 'Estudo registrado';
    const hoje = new Date();
    const inicioHoje = new Date(hoje.getFullYear(), hoje.getMonth(), hoje.getDate());
    const inicioData = new Date(data.getFullYear(), data.getMonth(), data.getDate());
    const diff = Math.round((inicioHoje - inicioData) / 86400000);
    if (diff === 0) return `Hoje, ${data.toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'})}`;
    if (diff === 1) return 'Ontem';
    if (diff > 1 && diff < 7) return `Há ${diff} dias`;
    return data.toLocaleDateString('pt-BR',{day:'2-digit',month:'2-digit',year:'numeric'});
  }

  function getMateriaByName(nome) {
    return materias.find((m) => normalizarMateria(m.nome) === normalizarMateria(nome));
  }

  // ==========================================
  // CRIAR CARD DA MATÉRIA
  // ==========================================

  const createSubjectCard = (
    materia
  ) => {

    if (!subjectsGrid) {
      return;
    }


    const card =
      document.createElement(
        'article'
      );


    card.className =
      'subject-card';


    card.dataset.id =
      materia.id ||
      '';


    card.style.setProperty(
      '--subject-color',
      materia.cor ||
      '#94a3b8'
    );


    const minutosEstudados =
      getMinutosEstudados(
        materia.nome
      );


    const tempoEstudado =
      formatarTempoEstudado(
        minutosEstudados
      );


    const totalFlashcards =
      getTotalFlashcards(
        materia.nome
      );


    const totalSessoes = getFocusSessions(materia.nome).length;
    const ultimaSessao = getLastStudy(materia.nome);
    const ultimaSessaoTexto = formatRelativeDate(getSessionTimestamp(ultimaSessao || {}));

    card.innerHTML = `
      <div class="subject-card-top">
        <div class="subject-card-icon">
          <i class="fa-solid ${materia.icone || 'fa-circle-question'}"></i>
        </div>
        <h3></h3>
      </div>

      <div class="subject-card-meta">
        <span class="subject-study-time"><i class="fa-solid fa-clock"></i>${tempoEstudado}</span>
        <span class="subject-sessions-count"><i class="fa-solid fa-circle-check"></i>${totalSessoes} ${totalSessoes === 1 ? 'sessão' : 'sessões'}</span>
        <span class="subject-flashcards-count"><i class="fa-solid fa-layer-group"></i>${totalFlashcards} ${totalFlashcards === 1 ? 'flashcard' : 'flashcards'}</span>
        <span class="subject-last-study"><i class="fa-regular fa-calendar"></i>${ultimaSessaoTexto}</span>
      </div>

      <div class="subject-card-actions-row">
        <a class="subject-study-btn" href="${FOAG_CONFIG.url('estudos/pomodoro/pomodoro.php')}"><i class="fa-solid fa-play"></i> Estudar</a>
        <a class="subject-flash-btn" href="${FOAG_CONFIG.url('estudos/flashcards/flashcards.php')}"><i class="fa-solid fa-layer-group"></i> Flashcards</a>
      </div>
    `;


    // ==================================
    // NOME DA MATÉRIA
    // ==================================

    const title =
      card.querySelector(
        'h3'
      );


    if (title) {

      title.textContent =
        materia.nome ||
        'Sem nome';

    }


    // ==================================
    // ÁREA DOS BOTÕES
    // ==================================

    const actions =
      document.createElement(
        'div'
      );


    actions.className =
      'subject-card-actions';


    // ==================================
    // BOTÃO EDITAR
    // ==================================

    const editButton =
      document.createElement(
        'button'
      );


    editButton.type =
      'button';


    editButton.className =
      'subject-edit-btn';


    editButton.title =
      'Editar matéria';


    editButton.setAttribute(
      'aria-label',
      `Editar ${materia.nome}`
    );


    editButton.innerHTML = `
      <i class="fa-regular fa-pen-to-square"></i>
    `;


    editButton.addEventListener(
      'click',
      (event) => {

        event.preventDefault();

        event.stopPropagation();


        openEditSubjectModal(
          materia
        );

      }
    );


    // ==================================
    // BOTÃO EXCLUIR
    // ==================================

    const deleteButton =
      document.createElement(
        'button'
      );


    deleteButton.type =
      'button';


    deleteButton.className =
      'subject-delete-btn';


    deleteButton.title =
      'Excluir matéria';


    deleteButton.setAttribute(
      'aria-label',
      `Excluir ${materia.nome}`
    );


    deleteButton.innerHTML = `
      <i class="fa-regular fa-trash-can"></i>
    `;


    deleteButton.addEventListener(
      'click',
      (event) => {

        event.preventDefault();

        event.stopPropagation();


        excluirMateria(
          materia,
          card
        );

      }
    );


    actions.appendChild(
      editButton
    );


    actions.appendChild(
      deleteButton
    );


    card.appendChild(
      actions
    );


    subjectsGrid.appendChild(
      card
    );

  };


  // ==========================================
  // CARREGAR MATÉRIAS
  // ==========================================

  const carregarMaterias = () => {

    if (!subjectsGrid) {
      return;
    }


    subjectsGrid.innerHTML =
      '';


    materias.forEach(
      (materia) => {

        createSubjectCard(
          materia
        );

      }
    );


    updateSubjectsState();

  };


  // ==========================================
  // CARREGAR AO ABRIR
  // ==========================================

  carregarMaterias();


  // ==========================================
  // DASHBOARD, BUSCA E ATIVIDADE
  // ==========================================

  const statStudyTime = document.getElementById('stat-study-time');
  const statSessions = document.getElementById('stat-sessions');
  const statStreak = document.getElementById('stat-streak');
  const searchInput = document.getElementById('subject-search');
  const sortSelect = document.getElementById('subject-sort');

  function startOfWeek(date = new Date()) {
    const d = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const day = d.getDay();
    const delta = day === 0 ? -6 : 1 - day;
    d.setDate(d.getDate() + delta);
    return d.getTime();
  }

  function formatCompactMinutes(minutos) {
    const total = Math.max(0, Math.round(minutos || 0));
    const h = Math.floor(total / 60);
    const m = total % 60;
    if (!h) return `${m}min`;
    return `${h}h ${String(m).padStart(2,'0')}min`;
  }

  function calculateStreak(sessoes) {
    const dias = new Set(sessoes.map((s) => {
      const d = new Date(getSessionTimestamp(s));
      return Number.isNaN(d.getTime()) ? null : `${d.getFullYear()}-${d.getMonth()+1}-${d.getDate()}`;
    }).filter(Boolean));
    let cursor = new Date();
    cursor = new Date(cursor.getFullYear(), cursor.getMonth(), cursor.getDate());
    const keyHoje = `${cursor.getFullYear()}-${cursor.getMonth()+1}-${cursor.getDate()}`;
    if (!dias.has(keyHoje)) cursor.setDate(cursor.getDate()-1);
    let streak = 0;
    while (true) {
      const key = `${cursor.getFullYear()}-${cursor.getMonth()+1}-${cursor.getDate()}`;
      if (!dias.has(key)) break;
      streak++;
      cursor.setDate(cursor.getDate()-1);
    }
    return streak;
  }

  function renderDashboard() {
    const foco = getFocusSessions();
    const inicioSemana = startOfWeek();
    const destaSemana = foco.filter((s) => getSessionTimestamp(s) >= inicioSemana);
    const minutosSemana = destaSemana.reduce((soma,s) => soma + Number(s.minutes ?? s.minutos ?? 0), 0);
    if (statStudyTime) statStudyTime.textContent = formatCompactMinutes(minutosSemana);
    if (statSessions) statSessions.textContent = destaSemana.length;
    const streak = calculateStreak(foco);
    if (statStreak) statStreak.textContent = `${streak} ${streak === 1 ? 'dia' : 'dias'}`;

    const diasAtivos = new Set(destaSemana.map((s) => {
      const d = new Date(getSessionTimestamp(s));
      return `${d.getFullYear()}-${d.getMonth()+1}-${d.getDate()}`;
    })).size;

    const metaHorasSalva = Number(localStorage.getItem('foag_meta_estudo_semanal_horas'));
    const metaHoras = Number.isFinite(metaHorasSalva) && metaHorasSalva > 0 ? metaHorasSalva : 5;
    const metaMin = metaHoras * 60;
    const perc = Math.min(100, Math.round((minutosSemana / metaMin) * 100));
    const goalLabel = document.getElementById('weekly-goal-label');
    const goalProgress = document.getElementById('weekly-goal-progress');
    const goalPercent = document.getElementById('weekly-goal-percent');
    const studyDays = document.getElementById('weekly-study-days');
    if (goalLabel) goalLabel.textContent = `${formatCompactMinutes(minutosSemana)} de ${metaHoras}h`;
    if (goalProgress) goalProgress.style.width = `${perc}%`;
    if (goalPercent) goalPercent.textContent = `${perc}% concluído`;
    if (studyDays) studyDays.innerHTML = `<i class="fa-solid fa-fire"></i> ${diasAtivos} ${diasAtivos === 1 ? 'dia ativo' : 'dias ativos'}`;

    const ultima = [...foco].sort((a,b)=>getSessionTimestamp(b)-getSessionTimestamp(a))[0];
    const continueSubject = document.getElementById('continue-subject');
    const continueDetail = document.getElementById('continue-detail');
    const continueIcon = document.getElementById('continue-icon');
    if (ultima) {
      const nome = ultima.discipline ?? ultima.disciplina ?? ultima.materia ?? 'Geral';
      const materia = getMateriaByName(nome);
      if (continueSubject) continueSubject.textContent = nome;
      if (continueDetail) continueDetail.textContent = `Último estudo: ${formatRelativeDate(getSessionTimestamp(ultima))} · ${Number(ultima.minutes ?? ultima.minutos ?? 0)} min`;
      if (continueIcon) {
        continueIcon.style.color = materia?.cor || '#1684df';
        continueIcon.innerHTML = `<i class="fa-solid ${materia?.icone || 'fa-book-open'}"></i>`;
      }
    }

    const totalCards = baralhos.reduce((n,b)=>n+(Array.isArray(b.cartoes)?b.cartoes.length:0),0);
    const flashInfo = document.getElementById('method-flashcards-info');
    const pomoInfo = document.getElementById('method-pomodoro-info');
    if (flashInfo) flashInfo.textContent = totalCards ? `${totalCards} ${totalCards === 1 ? 'cartão criado' : 'cartões criados'} para revisar.` : 'Crie cartões e revise conteúdos importantes.';
    if (pomoInfo) pomoInfo.textContent = foco.length ? `${foco.length} ${foco.length === 1 ? 'sessão registrada' : 'sessões registradas'} no seu histórico.` : 'Organize períodos de foco e acompanhe suas sessões.';
  }

  function renderReviewList() {
    const el = document.getElementById('review-list');
    if (!el) return;
    const itens = baralhos.map((baralho) => {
      const cards = Array.isArray(baralho.cartoes) ? baralho.cartoes : [];
      let ultima = 0;
      cards.forEach((c) => (Array.isArray(c.revisoes)?c.revisoes:[]).forEach((r)=>{ ultima=Math.max(ultima, Number(r.ts ?? r.timestamp ?? 0)); }));
      return {baralho,cards,ultima};
    }).filter((x)=>x.cards.length).sort((a,b)=>a.ultima-b.ultima).slice(0,3);
    if (!itens.length) { el.innerHTML = '<div class="list-empty">Crie flashcards para começar suas revisões.</div>'; return; }
    el.innerHTML = itens.map(({baralho,cards,ultima}) => `
      <div class="review-item">
        <div class="review-item-icon"><i class="fa-solid fa-layer-group"></i></div>
        <div class="review-item-main"><strong>${escapeHtml(baralho.nome || baralho.titulo || baralho.materia || 'Baralho')}</strong><span>${cards.length} ${cards.length===1?'cartão':'cartões'} · ${ultima ? `Última revisão: ${formatRelativeDate(ultima)}` : 'Ainda não revisado'}</span></div>
        <a class="review-button" href="${FOAG_CONFIG.url('estudos/flashcards/flashcards.php')}">Revisar</a>
      </div>`).join('');
  }

  function escapeHtml(texto) {
        return window.FOAG?.utils?.escapeHtml
            ? FOAG.utils.escapeHtml(texto)
            : String(texto ?? '');
    }

  function renderActivity() {
    const el = document.getElementById('activity-list');
    if (!el) return;
    const atividades = [];
    getFocusSessions().forEach((s)=>atividades.push({ts:getSessionTimestamp(s), tipo:'pomo', titulo:s.discipline ?? s.disciplina ?? s.materia ?? 'Estudo', detalhe:`Pomodoro · ${Number(s.minutes ?? s.minutos ?? 0)} min`}));
    baralhos.forEach((b)=>{
      const nome = b.nome || b.titulo || b.materia || 'Flashcards';
      (Array.isArray(b.cartoes)?b.cartoes:[]).forEach((c)=>(Array.isArray(c.revisoes)?c.revisoes:[]).forEach((r)=>atividades.push({ts:Number(r.ts ?? r.timestamp ?? 0),tipo:'flash',titulo:nome,detalhe:'Flashcard revisado'})));
    });
    atividades.sort((a,b)=>b.ts-a.ts);
    const vistos = atividades.slice(0,3);
    if (!vistos.length) { el.innerHTML='<div class="list-empty">Suas sessões e revisões recentes aparecerão aqui.</div>'; return; }
    el.innerHTML = vistos.map((a)=>`<div class="activity-item"><div class="activity-item-icon"><i class="fa-solid ${a.tipo==='pomo'?'fa-stopwatch':'fa-layer-group'}"></i></div><div class="activity-item-main"><strong>${escapeHtml(a.titulo)}</strong><span>${escapeHtml(a.detalhe)} · ${formatRelativeDate(a.ts)}</span></div></div>`).join('');
  }

  function renderFilteredSubjects() {
    if (!subjectsGrid) return;
    const termo = normalizarMateria(searchInput?.value || '');
    let lista = materias.filter((m)=>normalizarMateria(m.nome).includes(termo));
    const ordem = sortSelect?.value || 'recent';
    if (ordem === 'studied') lista.sort((a,b)=>getMinutosEstudados(b.nome)-getMinutosEstudados(a.nome));
    if (ordem === 'az') lista.sort((a,b)=>String(a.nome||'').localeCompare(String(b.nome||''),'pt-BR'));
    subjectsGrid.innerHTML='';
    lista.forEach(createSubjectCard);
    const hasMaterias = materias.length > 0;
    subjectsEmpty.hidden = hasMaterias;
    subjectsGrid.hidden = !hasMaterias || lista.length === 0;
    if (noResults) noResults.hidden = !hasMaterias || lista.length > 0;
  }

  searchInput?.addEventListener('input', renderFilteredSubjects);
  sortSelect?.addEventListener('change', renderFilteredSubjects);

  document.getElementById('edit-weekly-goal')?.addEventListener('click', () => {
    const atual = Number(localStorage.getItem('foag_meta_estudo_semanal_horas')) || 5;
    const resposta = window.prompt('Quantas horas você quer estudar por semana?', String(atual));
    if (resposta === null) return;
    const horas = Number(String(resposta).replace(',','.'));
    if (!Number.isFinite(horas) || horas <= 0 || horas > 100) { showToast('Digite uma meta entre 0,5 e 100 horas.'); return; }
    localStorage.setItem('foag_meta_estudo_semanal_horas', String(horas));
    renderDashboard();
    showToast('Meta semanal atualizada.');
  });

  renderDashboard();
  renderReviewList();
  renderActivity();

  // ==========================================
  // BOTÕES ABRIR NOVA MATÉRIA
  // ==========================================

  document
    .getElementById(
      'open-subject-modal'
    )
    ?.addEventListener(
      'click',
      openSubjectModal
    );


  document
    .getElementById(
      'open-subject-modal-secondary'
    )
    ?.addEventListener(
      'click',
      openSubjectModal
    );


  document
    .getElementById(
      'open-subject-modal-empty'
    )
    ?.addEventListener(
      'click',
      openSubjectModal
    );


  // ==========================================
  // BOTÕES FECHAR MODAL
  // ==========================================

  document
    .getElementById(
      'close-subject-modal'
    )
    ?.addEventListener(
      'click',
      closeSubjectModal
    );


  document
    .getElementById(
      'cancel-subject-modal'
    )
    ?.addEventListener(
      'click',
      closeSubjectModal
    );


  // ==========================================
  // FECHAR MODAL CLICANDO FORA
  // ==========================================

  subjectModal?.addEventListener(
    'click',
    (event) => {

      if (
        event.target ===
        subjectModal
      ) {

        closeSubjectModal();

      }

    }
  );


  // ==========================================
  // MODAL DE EXCLUSÃO
  // ==========================================

  confirmDeleteSubject
    ?.addEventListener(
      'click',
      executarExclusaoMateria
    );


  cancelDeleteSubject
    ?.addEventListener(
      'click',
      fecharModalExcluirMateria
    );


  deleteSubjectModal
    ?.addEventListener(
      'click',
      (event) => {

        if (
          event.target ===
          deleteSubjectModal
        ) {

          fecharModalExcluirMateria();

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
        event.key ===
        'Escape'
      ) {

        if (
          subjectModal?.classList.contains(
            'open'
          )
        ) {

          closeSubjectModal();

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


        if (
          deleteSubjectModal?.classList.contains(
            'open'
          )
        ) {

          fecharModalExcluirMateria();

        }

      }

    }
  );


  // ==========================================
  // SALVAR / EDITAR MATÉRIA
  // ==========================================

  subjectForm?.addEventListener(
    'submit',
    async (event) => {

      event.preventDefault();


      const nome =
        subjectName?.value
          ?.trim();


      const cor =
        subjectColor?.value ||
        '#38a5ff';


      const icone =
        subjectIcon?.value ||
        'fa-book';


      if (!nome) {

        subjectName?.focus();

        return;

      }


      // ==================================
      // EVITAR MATÉRIA REPETIDA
      // ==================================

      const existe =
        materias.some(
          (materia) => {

            const mesmoNome =
              normalizarMateria(
                materia.nome
              ) ===
              normalizarMateria(
                nome
              );


            const mesmaMateria =
              materiaEmEdicao &&
              String(
                materia.id
              ) ===
              String(
                materiaEmEdicao.id
              );


            return (
              mesmoNome &&
              !mesmaMateria
            );

          }
        );


      if (existe) {

        showToast(
          'Essa matéria já foi cadastrada.'
        );

        return;

      }


      // ==================================
      // DESCOBRIR SE É EDIÇÃO OU CRIAÇÃO
      // ==================================

      const editando =
        Boolean(
          materiaEmEdicao
        );


      const materiaId =
        materiaEmEdicao?.id ||
        null;


      const url =
        editando
          ? UPDATE_MATERIA_URL
          : SAVE_MATERIA_URL;


      // ==================================
      // BLOQUEAR BOTÃO
      // ==================================

      let textoBotaoOriginal =
        '';


      if (submitButton) {

        textoBotaoOriginal =
          submitButton.innerHTML;


        submitButton.disabled =
          true;


        submitButton.innerHTML = `
          <i class="fa-solid fa-spinner fa-spin"></i>
          Salvando...
        `;

      }


      try {

        const payload = {

          nome:
            nome,

          cor:
            cor,

          icone:
            icone

        };


        if (editando) {

          payload.id =
            materiaId;

        }


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
                  payload
                )

            }
          );


        let data;


        try {

          data =
            await response.json();

        } catch (error) {

          throw new Error(
            'Resposta inválida do servidor.'
          );

        }


        if (
          !response.ok ||
          !data.sucesso
        ) {

          throw new Error(
            data.mensagem ||
            'Não foi possível salvar.'
          );

        }


        // ==================================
        // SE ESTIVER EDITANDO
        // ==================================

        if (editando) {

          materias =
            materias.map(
              (materia) => {

                if (
                  String(
                    materia.id
                  ) ===
                  String(
                    materiaId
                  )
                ) {

                  return (
                    data.materia
                  );

                }


                return materia;

              }
            );


          carregarMaterias();


          showToast(
            `Matéria “${data.materia.nome}” atualizada.`
          );

        }


        // ==================================
        // SE FOR NOVA MATÉRIA
        // ==================================

        else {

          materias.push(
            data.materia
          );


          createSubjectCard(
            data.materia
          );


          updateSubjectsState();


          showToast(
            `Matéria “${data.materia.nome}” adicionada.`
          );

        }


        closeSubjectModal();


      } catch (error) {

        console.error(
          'Erro ao salvar matéria:',
          error
        );


        showToast(
          error.message ||
          'Erro ao salvar matéria.'
        );


      } finally {

        if (submitButton) {

          submitButton.disabled =
            false;


          if (
            subjectModal?.classList.contains(
              'open'
            )
          ) {

            submitButton.innerHTML =
              textoBotaoOriginal;

          }

        }

      }

    }
  );


  // ==========================================
  // MÉTODOS EM BREVE
  // ==========================================

  document
    .querySelectorAll(
      '[data-coming-soon]'
    )
    .forEach(
      (card) => {

        card.addEventListener(
          'click',
          (event) => {

            event.preventDefault();


            const nomeMetodo =
              card.dataset.comingSoon;


            showToast(
              `${nomeMetodo} será adicionado em breve.`
            );

          }
        );

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


  // ==========================================
  // FECHAR LOGOUT CLICANDO FORA
  // ==========================================

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

});