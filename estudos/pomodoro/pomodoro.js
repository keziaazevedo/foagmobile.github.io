// ==========================================
// PERSISTÊNCIA DO POMODORO
// ==========================================

const SAVE_URL =
  window.POMODORO_SAVE_URL ||
  FOAG_CONFIG.endpoints.pomodoroSalvar;

const state =
  window.POMODORO_DATA &&
  typeof window.POMODORO_DATA === 'object'
    ? window.POMODORO_DATA
    : {};


// ==========================================
// NORMALIZAR DADOS
// ==========================================

if (!Array.isArray(state.sessions)) {
  state.sessions = [];
}

if (
  !state.goals ||
  typeof state.goals !== 'object' ||
  Array.isArray(state.goals)
) {
  state.goals = {};
}


// ==========================================
// MATÉRIAS VINDAS DO materias.json
// ==========================================

const materiasData =
  window.MATERIAS_DATA &&
  typeof window.MATERIAS_DATA === 'object'
    ? window.MATERIAS_DATA
    : {
        materias: []
      };

const materias =
  Array.isArray(materiasData.materias)
    ? materiasData.materias
    : [];

const nomesMaterias = materias
  .map((materia) => {
    return String(
      materia.nome || ''
    ).trim();
  })
  .filter((nome) => {
    return nome !== '';
  });

const materiasUnicas = [
  ...new Set(nomesMaterias)
];


// Geral sempre disponível
state.disciplines = [
  'Geral',
  ...materiasUnicas.filter(
    (materia) => materia !== 'Geral'
  )
];


// ==========================================
// BUSCAR COR E ÍCONE DA MATÉRIA
// ==========================================

function getMateriaInfo(nome) {

  const materia =
    materias.find((item) => {
      return (
        String(item.nome || '').trim() ===
        String(nome || '').trim()
      );
    });

  return {
    cor:
      materia?.cor ||
      '#38a5ff',

    icone:
      materia?.icone ||
      'fa-book-open'
  };
}


// ==========================================
// SALVAR POMODORO
// ==========================================

function save() {

  return fetch(
    SAVE_URL,
    {
      method: 'POST',

      credentials: 'same-origin',

      headers: {
        'Content-Type':
          'application/json'
      },

      body:
        JSON.stringify(state)
    }
  )
    .then((response) => {

      if (!response.ok) {
        throw new Error(
          'Erro ao salvar dados do Pomodoro.'
        );
      }

      return response.json();

    })
    .catch((error) => {

      console.error(
        'Erro ao salvar Pomodoro:',
        error
      );

      throw error;

    });
}


// ==========================================
// CABEÇALHO
// ==========================================

const logoutModal =
  document.getElementById(
    'logout-modal'
  );

const confirmLogout =
  document.getElementById(
    'confirm-logout'
  );

const cancelLogout =
  document.getElementById(
    'cancel-logout'
  );

const iconPerfil =
  document.getElementById(
    'icon-perfil'
  );

const iconSair =
  document.getElementById(
    'icon-sair'
  );

const iconConfiguracoes =
  document.getElementById(
    'icon-configuracoes'
  );


// PERFIL

if (iconPerfil) {

  iconPerfil.addEventListener(
    'click',
    () => {

      window.location.href =
        FOAG_CONFIG.pages.perfil;

    }
  );

}


// CONFIGURAÇÕES

if (iconConfiguracoes) {

  iconConfiguracoes.addEventListener(
    'click',
    () => {

      window.location.href =
        FOAG_CONFIG.pages.configuracoes;

    }
  );

}


// ABRIR LOGOUT

if (iconSair) {

  iconSair.addEventListener(
    'click',
    () => {

      if (logoutModal) {
        logoutModal.style.display =
          'flex';
      }

    }
  );

}


// CONFIRMAR LOGOUT

if (confirmLogout) {

  confirmLogout.addEventListener(
    'click',
    () => {

      window.location.href =
        FOAG_CONFIG.pages.logout;

    }
  );

}


// CANCELAR LOGOUT

if (cancelLogout) {

  cancelLogout.addEventListener(
    'click',
    () => {

      if (logoutModal) {
        logoutModal.style.display =
          'none';
      }

    }
  );

}


// FECHAR MODAL CLICANDO FORA

if (logoutModal) {

  logoutModal.addEventListener(
    'click',
    (event) => {

      if (
        event.target ===
        logoutModal
      ) {

        logoutModal.style.display =
          'none';

      }

    }
  );

}


// ==========================================
// ABAS TIMER / CRONÔMETRO
// ==========================================

document
  .querySelectorAll('.tab-btn')
  .forEach((btn) => {

    btn.addEventListener(
      'click',
      () => {

        document
          .querySelectorAll(
            '.tab-btn'
          )
          .forEach((button) => {

            button.classList.remove(
              'active'
            );

          });

        document
          .querySelectorAll(
            '.tab-panel'
          )
          .forEach((panel) => {

            panel.classList.remove(
              'active'
            );

          });

        btn.classList.add(
          'active'
        );

        const tab =
          btn.getAttribute(
            'data-tab'
          );

        const panel =
          document.getElementById(
            'tab-' + tab
          );

        if (panel) {

          panel.classList.add(
            'active'
          );

        }

      }
    );

  });


// ==========================================
// SELECTS DE MATÉRIA
// ==========================================

const disciplineSel =
  document.getElementById(
    'discipline'
  );

const goalDiscipline =
  document.getElementById(
    'goalDiscipline'
  );

const stopwatchDiscipline =
  document.getElementById(
    'stopwatchDiscipline'
  );


// ==========================================
// PREENCHER SELECT
// ==========================================

function fillSelect(
  selectEl,
  values
) {

  if (!selectEl) {
    return;
  }

  const valorAtual =
    selectEl.value;

  selectEl.innerHTML = '';

  values.forEach((value) => {

    const option =
      document.createElement(
        'option'
      );

    option.value = value;
    option.textContent = value;

    selectEl.appendChild(
      option
    );

  });

  if (
    values.includes(
      valorAtual
    )
  ) {

    selectEl.value =
      valorAtual;

  }

}


// ==========================================
// CARREGAR MATÉRIAS NOS SELECTS
// ==========================================

function refreshDisciplines() {

  fillSelect(
    disciplineSel,
    state.disciplines
  );

  fillSelect(
    goalDiscipline,
    state.disciplines
  );

  fillSelect(
    stopwatchDiscipline,
    state.disciplines
  );

}

refreshDisciplines();


// ==========================================
// POLYFILL UUID
// ==========================================

if (
  !(
    window.crypto &&
    crypto.randomUUID
  )
) {

  window.crypto =
    window.crypto || {};

  crypto.randomUUID =
    function () {

      return (
        'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'
      ).replace(
        /[xy]/g,
        (c) => {

          const r =
            (Math.random() * 16) |
            0;

          const v =
            c === 'x'
              ? r
              : (r & 0x3) | 0x8;

          return v.toString(16);

        }
      );

    };

}


// ==========================================
// MATÉRIAS ESTUDADAS RECENTEMENTE
// ==========================================

const recentStudiesList =
  document.getElementById(
    'recentStudiesList'
  );


// FORMATA DATA

function formatRecentDate(
  timestamp
) {

  const date =
    new Date(timestamp);

  const now =
    new Date();

  const todayStart =
    new Date(
      now.getFullYear(),
      now.getMonth(),
      now.getDate()
    );

  const studyStart =
    new Date(
      date.getFullYear(),
      date.getMonth(),
      date.getDate()
    );

  const diffDays =
    Math.floor(
      (
        todayStart.getTime() -
        studyStart.getTime()
      ) /
      86400000
    );

  const time =
    date.toLocaleTimeString(
      'pt-BR',
      {
        hour: '2-digit',
        minute: '2-digit'
      }
    );


  if (diffDays === 0) {

    return `Hoje às ${time}`;

  }


  if (diffDays === 1) {

    return `Ontem às ${time}`;

  }


  return (
    `${date.toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: '2-digit'
      }
    )} às ${time}`
  );

}


// PEGA AS 5 ÚLTIMAS MATÉRIAS DIFERENTES

function getRecentStudies() {

  const orderedSessions =
    [...state.sessions]

      .filter((session) => {

        return (
          session.mode ===
            'focus' &&

          session.discipline &&

          session.discipline !==
            'Geral'
        );

      })

      .sort((a, b) => {

        return b.ts - a.ts;

      });


  const recentes = [];

  const materiasUsadas =
    new Set();


  for (
    const session
    of orderedSessions
  ) {

    const materia =
      String(
        session.discipline
      ).trim();


    if (
      !materia ||
      materiasUsadas.has(
        materia
      )
    ) {

      continue;

    }


    materiasUsadas.add(
      materia
    );

    recentes.push(
      session
    );


    if (
      recentes.length >= 5
    ) {

      break;

    }

  }


  return recentes;

}


// MOSTRAR RECENTES

function updateRecentStudies() {

  if (!recentStudiesList) {
    return;
  }


  recentStudiesList.innerHTML =
    '';


  const recentes =
    getRecentStudies();


  if (
    recentes.length === 0
  ) {

    const empty =
      document.createElement(
        'div'
      );

    empty.className =
      'recent-empty';


    const icon =
      document.createElement(
        'i'
      );

    icon.className =
      'fa-regular fa-clock';


    const text =
      document.createElement(
        'span'
      );

    text.textContent =
      'Suas matérias estudadas recentemente aparecerão aqui.';


    empty.appendChild(
      icon
    );

    empty.appendChild(
      text
    );


    recentStudiesList.appendChild(
      empty
    );

    return;

  }


  recentes.forEach(
    (session) => {

      const item =
        document.createElement(
          'div'
        );

      item.className =
        'recent-study-item';


      const materiaInfo =
        getMateriaInfo(
          session.discipline
        );


      const iconBox =
        document.createElement(
          'div'
        );

      iconBox.className =
        'recent-study-icon';


      iconBox.style.background =
        `${materiaInfo.cor}18`;

      iconBox.style.color =
        materiaInfo.cor;


      const icon =
        document.createElement(
          'i'
        );

      icon.className =
        `fa-solid ${materiaInfo.icone}`;


      iconBox.appendChild(
        icon
      );


      const info =
        document.createElement(
          'div'
        );

      info.className =
        'recent-study-info';


      const name =
        document.createElement(
          'span'
        );

      name.className =
        'recent-study-name';

      name.textContent =
        session.discipline;


      const meta =
        document.createElement(
          'span'
        );

      meta.className =
        'recent-study-meta';

      meta.textContent =
        formatRecentDate(
          session.ts
        );


      info.appendChild(
        name
      );

      info.appendChild(
        meta
      );


      const duration =
        document.createElement(
          'span'
        );

      duration.className =
        'recent-study-duration';

      duration.textContent =
        `${session.minutes} min`;


      item.appendChild(
        iconBox
      );

      item.appendChild(
        info
      );

      item.appendChild(
        duration
      );


      recentStudiesList.appendChild(
        item
      );

    }
  );

}


// ==========================================
// MENSAGENS DO SISTEMA DE ESTRELAS
// ==========================================

const starsFeedbackModal =
  document.getElementById(
    'stars-feedback-modal'
  );

const starsFeedbackTitle =
  document.getElementById(
    'stars-feedback-title'
  );

const starsFeedbackText =
  document.getElementById(
    'stars-feedback-text'
  );

const starsFeedbackIcon =
  document.getElementById(
    'stars-feedback-icon'
  );

const starsFeedbackDontShow =
  document.getElementById(
    'stars-feedback-dont-show'
  );

const starsFeedbackClose =
  document.getElementById(
    'stars-feedback-close'
  );


const STARS_MESSAGE_KEY =
  'foag_ocultar_mensagens_estrelas';


// ==========================================
// VERIFICAR SE PODE MOSTRAR
// ==========================================

function podeMostrarMensagemEstrelas() {

  return (
    localStorage.getItem(
      STARS_MESSAGE_KEY
    ) !== '1'
  );

}


// ==========================================
// MOSTRAR MENSAGEM
// ==========================================

function mostrarMensagemEstrelas(
  titulo,
  mensagem,
  ganhou = false
) {

  if (
    !podeMostrarMensagemEstrelas()
  ) {
    return;
  }


  if (
    !starsFeedbackModal
  ) {
    return;
  }


  if (starsFeedbackTitle) {

    starsFeedbackTitle.textContent =
      titulo;

  }


  if (starsFeedbackText) {

    starsFeedbackText.textContent =
      mensagem;

  }


  if (starsFeedbackDontShow) {

    starsFeedbackDontShow.checked =
      false;

  }


  if (starsFeedbackIcon) {

    starsFeedbackIcon.className =
      ganhou

        ? 'fa-solid fa-star'

        : 'fa-regular fa-clock';

  }


  starsFeedbackModal.classList.add(
    'active'
  );

}


// ==========================================
// FECHAR MENSAGEM
// ==========================================

function fecharMensagemEstrelas() {

  if (
    starsFeedbackDontShow &&
    starsFeedbackDontShow.checked
  ) {

    localStorage.setItem(
      STARS_MESSAGE_KEY,
      '1'
    );

  }


  if (starsFeedbackModal) {

    starsFeedbackModal.classList.remove(
      'active'
    );

  }

}


// ==========================================
// BOTÃO ENTENDI
// ==========================================

if (starsFeedbackClose) {

  starsFeedbackClose.addEventListener(
    'click',
    fecharMensagemEstrelas
  );

}


// ==========================================
// CLICAR FORA
// ==========================================

if (starsFeedbackModal) {

  starsFeedbackModal.addEventListener(
    'click',
    (event) => {

      if (
        event.target ===
        starsFeedbackModal
      ) {

        fecharMensagemEstrelas();

      }

    }
  );

}

// ==========================================
// POMODORO
// ==========================================

let mode = 'focus';

let cycle = 1;

let timer = null;

let endAt = null;

let totalMs = 0;

let remainingMs = 0;


const focusM =
  document.getElementById(
    'focusM'
  );

const shortM =
  document.getElementById(
    'shortM'
  );

const longM =
  document.getElementById(
    'longM'
  );

const everyCycles =
  document.getElementById(
    'everyCycles'
  );

const timerEl =
  document.getElementById(
    'timer'
  );

const modePill =
  document.getElementById(
    'modePill'
  );

const cyclePill =
  document.getElementById(
    'cyclePill'
  );

const progressBar =
  document.getElementById(
    'timerProgress'
  );

const ding =
  document.getElementById(
    'ding'
  );


// ==========================================
// DEFINIR MODO
// ==========================================

function setMode(
  newMode
) {

  mode = newMode;


  const mins =

    newMode === 'focus'

      ? +(focusM?.value || 25)

      : newMode === 'short'

        ? +(shortM?.value || 5)

        : +(longM?.value || 15);


  totalMs =
    mins * 60 * 1000;


  remainingMs =
    totalMs;


  endAt = null;


  renderTimer();


  if (modePill) {

    modePill.innerHTML = `

      <i class="fa-solid fa-hourglass-half"></i>

      ${
        newMode === 'focus'
          ? 'Foco'

          : newMode === 'short'
            ? 'Pausa curta'

            : 'Pausa longa'
      }

    `;

  }

}


// ==========================================
// RENDERIZAR TIMER
// ==========================================

function renderTimer() {

  if (!timerEl) {
    return;
  }


  const mm =
    Math.floor(
      remainingMs /
      60000
    )
      .toString()
      .padStart(
        2,
        '0'
      );


  const ss =
    Math.floor(
      (
        remainingMs %
        60000
      ) /
      1000
    )
      .toString()
      .padStart(
        2,
        '0'
      );


  timerEl.textContent =
    `${mm}:${ss}`;


  const pct =
    totalMs

      ? Math.max(
          0,

          100 -
            Math.floor(
              (
                remainingMs /
                totalMs
              ) *
              100
            )
        )

      : 0;


  if (progressBar) {

    progressBar.style.width =
      pct + '%';

  }


  if (cyclePill) {

    cyclePill.innerHTML = `

      <i class="fa-solid fa-repeat"></i>

      Ciclo ${cycle}

    `;

  }


  document.title =
    `${mm}:${ss} – FOAG`;

}


// ==========================================
// TICK TIMER
// ==========================================

function tick() {

  const now =
    Date.now();


  remainingMs =
    Math.max(
      0,
      endAt - now
    );


  renderTimer();


  if (
    remainingMs <= 0
  ) {

    clearInterval(
      timer
    );

    timer = null;


    completeCycle();


    try {

      if (
        ding &&
        ding.play
      ) {

        ding.play();

      }

    } catch (error) {

      console.error(
        'Erro ao reproduzir som:',
        error
      );

    }

  }

}


// ==========================================
// INICIAR
// ==========================================

function start() {

  if (timer) {
    return;
  }


  if (
    remainingMs <= 0
  ) {

    setMode(
      mode
    );

  }


  if (!endAt) {

    endAt =
      Date.now() +
      remainingMs;

  }


  timer =
    setInterval(
      tick,
      200
    );

}


// ==========================================
// PAUSAR
// ==========================================

function pause() {

  if (!timer) {
    return;
  }


  clearInterval(
    timer
  );


  timer = null;


  remainingMs =
    Math.max(

      0,

      endAt -
      Date.now()

    );


  endAt = null;


  renderTimer();

}


// ==========================================
// RESETAR
// ==========================================

function reset() {

  pause();

  setMode(
    mode
  );

}


// ==========================================
// COMPLETAR CICLO
// ==========================================

function completeCycle() {

  const mins =
    Math.round(
      totalMs /
      60000
    );


  const discipline =

    disciplineSel

      ? disciplineSel.value

      : 'Geral';


  // ========================================
  // SALVAR SESSÃO COM ID E ORIGEM
  // ========================================

  state.sessions.push({

    id:
      crypto.randomUUID(),

    ts:
      Date.now(),

    minutes:
      mins,

    mode:
      mode,

    source:
      'pomodoro',

    discipline:
      discipline

  });


  save()
    .then((resultado) => {

      console.log(
        'Pomodoro salvo:',
        resultado
      );

    })
    .catch(() => {});


  updateHistory();

  updateCharts();

  updateGoalsView();

  updateRecentStudies();


  if (
    mode === 'focus'
  ) {

    const ec =
      +(
        everyCycles?.value ||
        4
      );


    cycle++;


    if (
      (cycle - 1) %
        ec ===
      0
    ) {

      setMode(
        'long'
      );

    } else {

      setMode(
        'short'
      );

    }

  } else {

    setMode(
      'focus'
    );

  }

}


// ==========================================
// BOTÕES POMODORO
// ==========================================

const startBtn =
  document.getElementById(
    'startBtn'
  );

const pauseBtn =
  document.getElementById(
    'pauseBtn'
  );

const resetBtn =
  document.getElementById(
    'resetBtn'
  );


if (startBtn) {

  startBtn.addEventListener(
    'click',
    start
  );

}


if (pauseBtn) {

  pauseBtn.addEventListener(
    'click',
    pause
  );

}


if (resetBtn) {

  resetBtn.addEventListener(
    'click',
    reset
  );

}


setMode(
  'focus'
);


// ==========================================
// CRONÔMETRO
// ==========================================

const swDisplay =
  document.getElementById(
    'stopwatchDisplay'
  );

const swStartBtn =
  document.getElementById(
    'swStart'
  );

const swPauseBtn =
  document.getElementById(
    'swPause'
  );

const swResetBtn =
  document.getElementById(
    'swReset'
  );

const swLapBtn =
  document.getElementById(
    'swLap'
  );

const swSaveBtn =
  document.getElementById(
    'swSaveSession'
  );

const lapsList =
  document.getElementById(
    'lapsList'
  );


let swRunning =
  false;

let swStartAt =
  null;

let swElapsed =
  0;

let swTimer =
  null;


const swLaps = [];


// ==========================================
// FORMATAR CRONÔMETRO
// ==========================================

function formatStopwatchTime(
  total
) {

  const h =
    Math.floor(
      total /
      3600000
    )
      .toString()
      .padStart(
        2,
        '0'
      );


  const m =
    Math.floor(
      (
        total %
        3600000
      ) /
      60000
    )
      .toString()
      .padStart(
        2,
        '0'
      );


  const s =
    Math.floor(
      (
        total %
        60000
      ) /
      1000
    )
      .toString()
      .padStart(
        2,
        '0'
      );


  return (
    `${h}:${m}:${s}`
  );

}


// ==========================================
// RENDERIZAR CRONÔMETRO
// ==========================================

function renderStopwatch() {

  if (!swDisplay) {
    return;
  }


  swDisplay.textContent =
    formatStopwatchTime(
      swElapsed
    );

}


// ==========================================
// VOLTAS
// ==========================================

function renderLaps() {

  if (!lapsList) {
    return;
  }


  lapsList.innerHTML =
    '';


  swLaps.forEach(
    (ms, idx) => {

      const prev =

        idx === 0

          ? 0

          : swLaps[
              idx - 1
            ];


      const lapDur =
        ms - prev;


      const div =
        document.createElement(
          'div'
        );


      div.className =
        'task';


      const strong =
        document.createElement(
          'strong'
        );


      strong.textContent =
        `Volta ${idx + 1}`;


      const small =
        document.createElement(
          'small'
        );


      small.textContent =
        `Tempo total: ${formatStopwatchTime(ms)} | Parcial: ${formatStopwatchTime(lapDur)}`;


      div.appendChild(
        strong
      );

      div.appendChild(
        small
      );


      lapsList.appendChild(
        div
      );

    }
  );

}


// ==========================================
// TICK CRONÔMETRO
// ==========================================

function swTick() {

  if (!swRunning) {
    return;
  }


  swElapsed =
    Date.now() -
    swStartAt;


  renderStopwatch();

}


// ==========================================
// INICIAR CRONÔMETRO
// ==========================================

if (swStartBtn) {

  swStartBtn.addEventListener(
    'click',
    () => {

      if (swRunning) {
        return;
      }


      swRunning =
        true;


      swStartAt =
        Date.now() -
        swElapsed;


      swTimer =
        setInterval(
          swTick,
          200
        );

    }
  );

}


// ==========================================
// PAUSAR CRONÔMETRO
// ==========================================

if (swPauseBtn) {

  swPauseBtn.addEventListener(
    'click',
    () => {

      if (!swRunning) {
        return;
      }


      swElapsed =
        Date.now() -
        swStartAt;


      swRunning =
        false;


      clearInterval(
        swTimer
      );


      swTimer =
        null;


      renderStopwatch();

    }
  );

}


// ==========================================
// RESET CRONÔMETRO
// ==========================================

if (swResetBtn) {

  swResetBtn.addEventListener(
    'click',
    () => {

      swRunning =
        false;


      clearInterval(
        swTimer
      );


      swTimer =
        null;


      swElapsed =
        0;


      swStartAt =
        null;


      swLaps.length =
        0;


      renderStopwatch();

      renderLaps();

    }
  );

}


// ==========================================
// VOLTA
// ==========================================

if (swLapBtn) {

  swLapBtn.addEventListener(
    'click',
    () => {

      if (!swRunning) {
        return;
      }


      swElapsed =
        Date.now() -
        swStartAt;


      swLaps.push(
        swElapsed
      );


      renderLaps();

    }
  );

}


// ==========================================
// SALVAR SESSÃO DO CRONÔMETRO
// ==========================================

if (swSaveBtn) {

  swSaveBtn.addEventListener(
    'click',
    () => {


      if (swRunning) {

        swElapsed =
          Date.now() -
          swStartAt;

      }


      // ====================================
      // MINUTOS COMPLETOS
      // 29:59 CONTINUA SENDO 29
      // ====================================

      const minutes =
        Math.floor(
          swElapsed /
          60000
        );


      if (
        minutes <= 0
      ) {

        alert(
          'Cronômetro zerado. Inicie e registre algum tempo antes de salvar.'
        );

        return;

      }


      const discipline =

        stopwatchDiscipline

          ? stopwatchDiscipline.value

          : 'Geral';


      // ====================================
      // SALVAR SESSÃO COM ID E ORIGEM
      // ====================================

      state.sessions.push({

        id:
          crypto.randomUUID(),

        ts:
          Date.now(),

        minutes:
          minutes,

        mode:
          'focus',

        source:
          'cronometro',

        discipline:
          discipline

      });


      save()
      .then((resultado) => {

        console.log(
          'Sessão salva:',
          resultado
        );


        // ======================================
// MENSAGEM DAS ESTRELAS
// ======================================

if (
  minutes < 30
) {

  const faltam =
    30 - minutes;


  mostrarMensagemEstrelas(

    'Continue estudando!',

    `Sua sessão foi salva, mas você precisa estudar mais ${faltam} minuto${faltam === 1 ? '' : 's'} para acumular estrelas pelo cronômetro.`,

    false

  );

}

else if (
  resultado.limite_diario_atingido
) {

  mostrarMensagemEstrelas(

    'Sessão salva! 📚',

    'Você já recebeu estrelas pelas 3 sessões permitidas hoje. Continue estudando: seu tempo ainda conta para os bônus de 2h e 4h.',

    false

  );

}

else if (
  Number(
    resultado.estrelas_sessoes || 0
  ) > 0
) {

  mostrarMensagemEstrelas(

    'Você ganhou estrelas! ⭐',

    `Sessão concluída! Você ganhou +${resultado.estrelas_sessoes} estrelas.`,

    true

  );

}


        // ======================================
        // ZERAR CRONÔMETRO APÓS SALVAR
        // ======================================

        swRunning = false;

        clearInterval(
          swTimer
        );

        swTimer = null;

        swElapsed = 0;

        swStartAt = null;

        swLaps.length = 0;

        renderStopwatch();

        renderLaps();

      })
        .catch(() => {

          alert(
            'Não foi possível salvar a sessão.'
          );

        });


      updateHistory();

      updateCharts();

      updateGoalsView();

      updateRecentStudies();

    }
  );

}


renderStopwatch();


// ==========================================
// METAS SEMANAIS
// ==========================================

const saveGoalBtn =
  document.getElementById(
    'saveGoal'
  );

const goalHours =
  document.getElementById(
    'goalHours'
  );

const goalsList =
  document.getElementById(
    'goalsList'
  );


// ==========================================
// SEMANA ATUAL
// ==========================================

function getWeekRange(
  d = new Date()
) {

  const dt =
    new Date(d);


  const day =
    (
      dt.getDay() +
      6
    ) %
    7;


  const monday =
    new Date(dt);


  monday.setDate(
    dt.getDate() -
    day
  );


  monday.setHours(
    0,
    0,
    0,
    0
  );


  const sunday =
    new Date(
      monday
    );


  sunday.setDate(
    monday.getDate() +
    6
  );


  sunday.setHours(
    23,
    59,
    59,
    999
  );


  return {

    monday,

    sunday

  };

}


// ==========================================
// MINUTOS POR MATÉRIA NA SEMANA
// ==========================================

function minutesInWeekByDiscipline() {

  const {
    monday,
    sunday
  } =
    getWeekRange();


  const acc = {};


  for (
    const session
    of state.sessions
  ) {

    if (
      session.mode !==
      'focus'
    ) {

      continue;

    }


    const time =
      Number(
        session.ts
      );


    if (
      time >=
        monday.getTime() &&

      time <=
        sunday.getTime()
    ) {

      const discipline =
        session.discipline ||
        'Geral';


      acc[discipline] =
        (
          acc[discipline] ||
          0
        ) +
        Number(
          session.minutes ||
          0
        );

    }

  }


  return acc;

}


// ==========================================
// ATUALIZAR METAS
// ==========================================

function updateGoalsView() {

  if (!goalsList) {
    return;
  }


  goalsList.innerHTML =
    '';


  const minsMap =
    minutesInWeekByDiscipline();


  let encontrouMeta =
    false;


  for (
    const discipline
    of state.disciplines
  ) {

    const goalH =
      Number(
        state.goals[
          discipline
        ] ||
        0
      );


    if (!goalH) {
      continue;
    }


    encontrouMeta =
      true;


    const doneMin =
      Number(
        minsMap[
          discipline
        ] ||
        0
      );


    const goalMin =
      goalH * 60;


    const pct =
      Math.min(
        100,

        Math.floor(
          (
            doneMin /
            goalMin
          ) *
          100
        )
      );


    const wrap =
      document.createElement(
        'div'
      );


    const top =
      document.createElement(
        'div'
      );


    top.className =
      'row between';


    const strong =
      document.createElement(
        'strong'
      );


    strong.textContent =
      discipline;


    const value =
      document.createElement(
        'span'
      );


    value.style.color =
      '#666';


    const doneHours =
      Math.round(
        (
          doneMin /
          60
        ) *
        10
      ) /
      10;


    value.textContent =
      `${doneHours}h / ${goalH}h`;


    top.appendChild(
      strong
    );

    top.appendChild(
      value
    );


    const progress =
      document.createElement(
        'div'
      );


    progress.className =
      'progress mt';


    const progressValue =
      document.createElement(
        'span'
      );


    progressValue.style.width =
      `${pct}%`;


    progress.appendChild(
      progressValue
    );


    wrap.appendChild(
      top
    );

    wrap.appendChild(
      progress
    );


    goalsList.appendChild(
      wrap
    );

  }


  if (!encontrouMeta) {

    const empty =
      document.createElement(
        'div'
      );


    empty.className =
      'recent-empty';


    const icon =
      document.createElement(
        'i'
      );


    icon.className =
      'fa-solid fa-bullseye';


    const text =
      document.createElement(
        'span'
      );


    text.textContent =
      'Você ainda não definiu nenhuma meta semanal.';


    empty.appendChild(
      icon
    );

    empty.appendChild(
      text
    );


    goalsList.appendChild(
      empty
    );

  }

}


// ==========================================
// SALVAR META
// ==========================================

if (saveGoalBtn) {

  saveGoalBtn.addEventListener(
    'click',
    () => {


      const discipline =
        goalDiscipline?.value;


      const hours =
        Number(
          goalHours?.value
        );


      if (!discipline) {
        return;
      }


      if (
        !Number.isFinite(
          hours
        ) ||
        hours <= 0
      ) {

        alert(
          'Informe uma quantidade válida de horas.'
        );


        goalHours?.focus();


        return;

      }


      state.goals[
        discipline
      ] =
        hours;


      save()
        .catch(() => {});


      if (goalHours) {

        goalHours.value =
          '';

      }


      updateGoalsView();

    }
  );

}


// ==========================================
// HISTÓRICO
// ==========================================

const historyTableBody =
  document.querySelector(
    '#historyTable tbody'
  );


function updateHistory() {

  if (!historyTableBody) {
    return;
  }


  historyTableBody.innerHTML =
    '';


  const rows =
    [...state.sessions]
      .sort(
        (a, b) => {

          return (
            b.ts -
            a.ts
          );

        }
      );


  for (
    const session
    of rows
  ) {

    const tr =
      document.createElement(
        'tr'
      );


    const date =
      new Date(
        session.ts
      );


    const tdDate =
      document.createElement(
        'td'
      );


    tdDate.textContent =
      date.toLocaleString(
        'pt-BR'
      );


    const tdDiscipline =
      document.createElement(
        'td'
      );


    tdDiscipline.textContent =
      session.discipline ||
      'Geral';


    const tdMode =
      document.createElement(
        'td'
      );


    tdMode.textContent =

      session.mode ===
        'focus'

        ? 'Foco'

        : 'Pausa';


    const tdMinutes =
      document.createElement(
        'td'
      );


    tdMinutes.textContent =
      Number(
        session.minutes ||
        0
      );


    tr.appendChild(
      tdDate
    );

    tr.appendChild(
      tdDiscipline
    );

    tr.appendChild(
      tdMode
    );

    tr.appendChild(
      tdMinutes
    );


    historyTableBody.appendChild(
      tr
    );

  }

}


// ==========================================
// LIMPAR HISTÓRICO
// ==========================================

const clearHistoryBtn =
  document.getElementById(
    'clearHistory'
  );

const exportCsvBtn =
  document.getElementById(
    'exportCsv'
  );


if (clearHistoryBtn) {

  clearHistoryBtn.addEventListener(
    'click',
    () => {


      if (
        !confirm(
          'Tem certeza que deseja limpar o histórico?'
        )
      ) {

        return;

      }


      state.sessions =
        [];


      save()
        .catch(() => {});


      updateHistory();

      updateCharts();

      updateGoalsView();

      updateRecentStudies();

    }
  );

}


// ==========================================
// EXPORTAR CSV
// ==========================================

if (exportCsvBtn) {

  exportCsvBtn.addEventListener(
    'click',
    () => {


      const header = [

        'data',

        'materia',

        'modo',

        'minutos'

      ];


      const lines = [

        header.join(',') +
        '\n'

      ];


      for (
        const session
        of state.sessions
      ) {

        const date =
          new Date(
            session.ts
          ).toISOString();


        lines.push(

          [

            date,

            session.discipline,

            session.mode,

            session.minutes

          ]
            .map(
              (value) => {

                return (
                  `"${String(value)
                    .replace(
                      /"/g,
                      '""'
                    )}"`
                );

              }
            )
            .join(',') +
            '\n'

        );

      }


      const blob =
        new Blob(
          [
            lines.join('')
          ],
          {
            type:
              'text/csv;charset=utf-8;'
          }
        );


      const link =
        document.createElement(
          'a'
        );


      const url =
        URL.createObjectURL(
          blob
        );


      link.href =
        url;


      link.download =
        'foag_estudos.csv';


      document.body.appendChild(
        link
      );


      link.click();


      link.remove();


      URL.revokeObjectURL(
        url
      );

    }
  );

}


// ==========================================
// GRÁFICOS
// ==========================================

const lineCtx =
  document.getElementById(
    'lineChart'
  );

const pieCtx =
  document.getElementById(
    'pieChart'
  );


let lineChart;

let pieChart;


// ==========================================
// HORAS NOS ÚLTIMOS DIAS
// ==========================================

function hoursLastNDays(
  n = 14
) {

  const today =
    new Date();


  today.setHours(
    0,
    0,
    0,
    0
  );


  const labels = [];

  const hours = [];


  for (
    let i = n - 1;
    i >= 0;
    i--
  ) {

    const day =
      new Date(
        today
      );


    day.setDate(
      today.getDate() -
      i
    );


    const start =
      day.getTime();


    const end =
      start +
      86400000 -
      1;


    const minutes =
      state.sessions

        .filter(
          (session) => {

            return (

              session.mode ===
                'focus' &&

              session.ts >=
                start &&

              session.ts <=
                end

            );

          }
        )

        .reduce(
          (
            total,
            session
          ) => {

            return (

              total +

              Number(
                session.minutes ||
                0
              )

            );

          },
          0
        );


    labels.push(

      day.toLocaleDateString(
        'pt-BR',
        {
          day:
            '2-digit',

          month:
            '2-digit'
        }
      )

    );


    hours.push(

      Math.round(
        (
          minutes /
          60
        ) *
        100
      ) /
      100

    );

  }


  return {

    labels,

    hours

  };

}


// ==========================================
// DISTRIBUIÇÃO POR MATÉRIA
// ==========================================

function distributionByDiscipline() {

  const by = {};


  for (
    const session
    of state.sessions
  ) {

    if (
      session.mode !==
      'focus'
    ) {

      continue;

    }


    const discipline =
      session.discipline ||
      'Geral';


    by[
      discipline
    ] =

      (
        by[
          discipline
        ] ||
        0
      ) +

      Number(
        session.minutes ||
        0
      );

  }


  const labels =
    Object.keys(by);


  const hours =
    labels.map(
      (key) => {

        return (

          Math.round(
            (
              by[key] /
              60
            ) *
            100
          ) /
          100

        );

      }
    );


  return {

    labels,

    hours

  };

}


// ==========================================
// ATUALIZAR GRÁFICOS
// ==========================================

function updateCharts() {

  if (
    typeof Chart ===
      'undefined' ||

    !lineCtx ||

    !pieCtx
  ) {

    return;

  }


  const hl =
    hoursLastNDays(
      14
    );


  const dist =
    distributionByDiscipline();


  if (lineChart) {

    lineChart.destroy();

  }


  if (pieChart) {

    pieChart.destroy();

  }


  lineChart =
    new Chart(
      lineCtx,
      {

        type:
          'line',

        data: {

          labels:
            hl.labels,

          datasets: [
            {

              label:
                'Horas por dia',

              data:
                hl.hours,

              tension:
                0.3,

              fill:
                false

            }
          ]

        },

        options: {

          responsive:
            true,

          maintainAspectRatio:
            false,

          plugins: {

            legend: {

              display:
                false

            }

          },

          scales: {

            y: {

              beginAtZero:
                true

            }

          }

        }

      }
    );


  pieChart =
    new Chart(
      pieCtx,
      {

        type:
          'doughnut',

        data: {

          labels:
            dist.labels,

          datasets: [
            {

              data:
                dist.hours

            }
          ]

        },

        options: {

          responsive:
            true,

          maintainAspectRatio:
            false,

          plugins: {

            legend: {

              position:
                'bottom'

            }

          }

        }

      }
    );

}


// ==========================================
// INICIALIZAÇÃO
// ==========================================

updateHistory();

updateCharts();

updateGoalsView();

updateRecentStudies();