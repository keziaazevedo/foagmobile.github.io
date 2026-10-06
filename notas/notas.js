// ==========================================
// MODAL FOGi
// ==========================================

const fogiBtn = document.getElementById("icon-fogi");
const fogiModal = document.getElementById("fogi-modal");
const fogiFrame = document.getElementById("fogi-iframe");
const fogiClose = document.getElementById("fogi-close");

if (fogiBtn && fogiModal && fogiFrame) {
  fogiBtn.addEventListener("click", () => {
    fogiFrame.src = "http://127.0.0.1:5000";
    fogiModal.style.display = "flex";
    document.body.style.overflow = "hidden";
  });
}

if (fogiClose && fogiModal && fogiFrame) {
  fogiClose.addEventListener("click", () => {
    fogiModal.style.display = "none";
    fogiFrame.src = "about:blank";
    document.body.style.overflow = "";
  });
}

window.addEventListener("message", (ev) => {
  if (
    ev.data &&
    ev.data.type === "FOGI_CLOSE" &&
    fogiModal &&
    fogiFrame
  ) {
    fogiModal.style.display = "none";
    fogiFrame.src = "about:blank";
    document.body.style.overflow = "";
  }
});


// ==========================================
// PERFIL E LOGOUT
// ==========================================

const logoutModal = document.getElementById("logout-modal");
const iconPerfil = document.getElementById("icon-perfil");
const iconSair = document.getElementById("icon-sair");
const confirmLogout = document.getElementById("confirm-logout");
const cancelLogout = document.getElementById("cancel-logout");

if (iconPerfil) {
  iconPerfil.addEventListener("click", () => {
    window.location.href = "../perfil/perfil.php";
  });
}

if (iconSair && logoutModal) {
  iconSair.addEventListener("click", () => {
    logoutModal.style.display = "flex";
  });
}

if (confirmLogout) {
  confirmLogout.addEventListener("click", () => {
    window.location.href = "../login/logout.php";
  });
}

if (cancelLogout && logoutModal) {
  cancelLogout.addEventListener("click", () => {
    logoutModal.style.display = "none";
  });
}

if (logoutModal) {
  logoutModal.addEventListener("click", (e) => {
    if (e.target === logoutModal) {
      logoutModal.style.display = "none";
    }
  });
}
// ==========================================
// BOLETIM — CONFIGURAÇÕES + AUTOSAVE
// ==========================================
const configToggle = document.getElementById('btn-config-toggle');
const configPanel = document.getElementById('config-panel');
if (configToggle && configPanel) {
  configToggle.addEventListener('click', () => {
    const aberto = !configPanel.hasAttribute('hidden');
    if (aberto) configPanel.setAttribute('hidden', '');
    else configPanel.removeAttribute('hidden');
    configToggle.setAttribute('aria-expanded', String(!aberto));
  });
}

const notasForm = document.getElementById('notas-form');
const autosaveStatus = document.getElementById('autosave-status');
let autosaveTimer = null;
let autosaveController = null;

function setAutosaveStatus(tipo, texto, icone) {
  if (!autosaveStatus) return;
  autosaveStatus.classList.remove('saving', 'saved', 'error');
  if (tipo) autosaveStatus.classList.add(tipo);
  autosaveStatus.innerHTML = `<i class="fa-solid ${icone}"></i> ${texto}`;
}

async function salvarNotasAutomaticamente() {
  if (!notasForm) return;
  if (autosaveController) autosaveController.abort();
  autosaveController = new AbortController();
  setAutosaveStatus('saving', 'Salvando...', 'fa-cloud-arrow-up');

  const dados = new FormData(notasForm);
  dados.append('salvar_edicoes', '1');
  try {
    const resposta = await fetch(window.location.href, {
      method: 'POST',
      body: dados,
      signal: autosaveController.signal,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!resposta.ok) throw new Error('Falha ao salvar');
    setAutosaveStatus('saved', 'Tudo salvo', 'fa-cloud-check');
  } catch (erro) {
    if (erro.name === 'AbortError') return;
    setAutosaveStatus('error', 'Não foi possível salvar', 'fa-triangle-exclamation');
  }
}

if (notasForm) {
  notasForm.querySelectorAll('.input-nota').forEach((input) => {
    input.addEventListener('input', () => {
      clearTimeout(autosaveTimer);
      setAutosaveStatus('saving', 'Alterações pendentes', 'fa-cloud-arrow-up');
      autosaveTimer = setTimeout(salvarNotasAutomaticamente, 700);
    });
    input.addEventListener('blur', () => {
      clearTimeout(autosaveTimer);
      salvarNotasAutomaticamente();
    });
  });
}

function numeroBR(valor, casas = 2) {
  return Number(valor).toLocaleString('pt-BR', { minimumFractionDigits: casas, maximumFractionDigits: casas });
}


function recalcularMediaGeral() {
  if (!notasForm) return;

  const tipoCurso = notasForm.dataset.tipoCurso || 'escola';
  let pesos = [1, 1, 1, 1];

  try {
    pesos = JSON.parse(notasForm.dataset.pesos || '[1,1,1,1]').map(Number);
  } catch (_) {}

  const medias = [];

  notasForm.querySelectorAll('.nota-row').forEach((row) => {
    const inputs = [...row.querySelectorAll('.input-nota')];

    if (tipoCurso === 'escola') {
      const valores = inputs
        .map((input) => input.value.trim() === '' ? null : Number(input.value))
        .filter((valor) => valor !== null && Number.isFinite(valor));

      if (valores.length === 0) return;

      const soma = valores.reduce((acc, valor) => acc + valor, 0);
      medias.push(soma / valores.length);
      return;
    }

    let soma = 0;
    let somaPesos = 0;

    inputs.forEach((input, idx) => {
      if (input.value.trim() === '') return;

      const nota = Number(input.value);
      const peso = Number(pesos[idx] ?? 1);

      if (!Number.isFinite(nota) || peso <= 0) return;

      soma += nota * peso;
      somaPesos += peso;
    });

    if (somaPesos > 0) {
      medias.push(soma / somaPesos);
    }
  });

  const mediaGeral = medias.length > 0
    ? medias.reduce((acc, media) => acc + media, 0) / medias.length
    : 0;

  const kpiMediaGeral = document.getElementById('kpi-media-geral');

  if (kpiMediaGeral) {
    kpiMediaGeral.textContent = numeroBR(mediaGeral, 2);
  }
}

function recalcularLinha(row) {
  if (!notasForm || !row) return;
  const alvo = Number(notasForm.dataset.mediaAprovacao || 6);
  const maxima = Number(notasForm.dataset.notaMaxima || 10);
  const tipoCurso = notasForm.dataset.tipoCurso || 'escola';
  let pesos = [1,1,1,1];
  try { pesos = JSON.parse(notasForm.dataset.pesos || '[1,1,1,1]').map(Number); } catch (_) {}

  const inputs = [...row.querySelectorAll('.input-nota')];

  if (tipoCurso === 'escola') {
    const valores = inputs
      .map((input) => input.value.trim() === '' ? null : Number(input.value))
      .filter((valor) => valor !== null && Number.isFinite(valor));

    const preenchidas = valores.length;
    const total = valores.reduce((acc, valor) => acc + valor, 0);
    const metaTotal = alvo * inputs.length;
    const restantes = inputs.length - preenchidas;
    const faltam = Math.max(0, metaTotal - total);
    const mediaAtual = preenchidas > 0 ? total / preenchidas : 0;
    const mediaNecessaria = restantes > 0 ? faltam / restantes : null;
    const impossivel = restantes > 0 && faltam > maxima * restantes;

    let status = 'Sem notas';
    if (preenchidas > 0 && preenchidas < inputs.length) {
      status = total >= metaTotal ? 'Meta alcançada' : 'Em andamento';
    } else if (preenchidas === inputs.length) {
      status = total >= metaTotal ? 'Aprovado' : 'Não atingiu a média';
    }

    const mediaEl = row.querySelector('.media-valor');
    if (mediaEl) mediaEl.textContent = numeroBR(total, 1);
    const mediaParcial = row.querySelector('.media-parcial');
    if (mediaParcial) mediaParcial.textContent = `média atual ${numeroBR(mediaAtual, 1)}`;
    const mediaResumida = row.querySelector('.media-atual-resumida');
    if (mediaResumida) mediaResumida.textContent = numeroBR(mediaAtual, 1);
    const faltaResumida = row.querySelector('.falta-numero');
    if (faltaResumida) faltaResumida.textContent = numeroBR(faltam, 1);

    const badge = row.querySelector('.badge-status');
    if (badge) {
      badge.textContent = status;
      badge.classList.remove('status-aprovado','status-recuperacao','status-reprovado');
      if (status === 'Aprovado' || status === 'Meta alcançada') badge.classList.add('status-aprovado');
      else if (status === 'Em andamento' || status === 'Sem notas') badge.classList.add('status-recuperacao');
      else badge.classList.add('status-reprovado');
    }

    const meta = row.querySelector('.falta-detalhada') || row.querySelector('.celula-precisa');
    if (meta) {
      if (preenchidas === 0) {
        meta.innerHTML = `<span class="proxima-meta"><small>Meta anual</small><strong>${numeroBR(metaTotal, 0)} pts</strong></span>`;
      } else if (preenchidas < inputs.length && faltam <= 0) {
        meta.innerHTML = '<span class="meta-ok"><i class="fa-solid fa-check"></i> Meta anual alcançada</span>';
      } else if (impossivel) {
        meta.innerHTML = '<span class="badge-precisa impossivel">Meta não alcançável só com os bimestres restantes</span>';
      } else if (preenchidas < inputs.length) {
        const detalhe = restantes === 1
          ? `precisa de ${numeroBR(faltam, 1)}`
          : `média ${numeroBR(mediaNecessaria, 1)} nos ${restantes} restantes`;
        meta.innerHTML = `<span class="proxima-meta"><small>Faltam ${numeroBR(faltam, 1)} pts</small><strong>${detalhe}</strong></span>`;
      } else if (status === 'Aprovado') {
        meta.innerHTML = '<span class="meta-ok"><i class="fa-solid fa-check"></i> Aprovado no ano</span>';
      } else {
        meta.innerHTML = `<span class="badge-precisa impossivel">Faltaram ${numeroBR(faltam, 1)} pts</span>`;
      }
    }
    return;
  }

  let soma = 0, somaPesos = 0, somaFeitas = 0, proxima = -1;
  inputs.forEach((input, idx) => {
    const peso = Number(pesos[idx] ?? 1);
    const vazio = input.value.trim() === '';
    if (peso <= 0) return;
    if (vazio) {
      if (proxima < 0) proxima = idx;
      return;
    }
    const nota = Number(input.value);
    if (!Number.isFinite(nota)) return;
    soma += nota * peso;
    somaPesos += peso;
    somaFeitas += nota * peso;
  });

  const media = somaPesos > 0 ? soma / somaPesos : 0;
  const mediaEl = row.querySelector('.media-valor');
  if (mediaEl) mediaEl.textContent = numeroBR(media, 2);

  let status = '-';
  if (somaPesos > 0) status = media >= alvo ? 'Aprovado' : (media >= alvo * 0.5 ? 'Recuperação' : 'Reprovado');
  const badge = row.querySelector('.badge-status');
  if (badge) {
    badge.textContent = status;
    badge.classList.remove('status-aprovado','status-recuperacao','status-reprovado');
    if (status === 'Aprovado') badge.classList.add('status-aprovado');
    if (status === 'Recuperação') badge.classList.add('status-recuperacao');
    if (status === 'Reprovado') badge.classList.add('status-reprovado');
  }

  const meta = row.querySelector('.celula-precisa');
  if (meta) {
    if (status === 'Aprovado') {
      meta.innerHTML = '<span class="meta-ok"><i class="fa-solid fa-check"></i> Meta atingida</span>';
    } else if (proxima >= 0 && somaPesos > 0) {
      const somaTodosPesos = pesos.reduce((acc, p) => acc + (p > 0 ? p : 0), 0);
      const pesoProx = Number(pesos[proxima] ?? 1);
      let precisa = pesoProx > 0 ? ((alvo * somaTodosPesos) - somaFeitas) / pesoProx : Infinity;
      precisa = Math.max(0, precisa);
      if (precisa > maxima) meta.innerHTML = '<span class="badge-precisa impossivel">Requer recuperação</span>';
      else meta.innerHTML = `<span class="proxima-meta"><small>Você precisa de</small><strong>${numeroBR(precisa, 1)}</strong></span>`;
    } else {
      meta.textContent = '—';
    }
  }
}

if (notasForm) {
  notasForm.querySelectorAll('.input-nota').forEach((input) => {
    input.addEventListener('input', () => {
      recalcularLinha(input.closest('.nota-row'));
      recalcularMediaGeral();
    });
  });

  recalcularMediaGeral();
}

// ==========================================
// BOLETIM — MODO RESUMIDO / DETALHADO
// ==========================================
const viewToggle = document.getElementById('btn-view-toggle');
const viewToggleLabel = viewToggle?.querySelector('.view-toggle-label');
const viewStorageKey = 'foag_boletim_view_mode';

function aplicarModoBoletim(modo) {
  const resumido = modo === 'resumido';
  document.body.classList.toggle('boletim-resumido', resumido);

  if (viewToggle) {
    viewToggle.setAttribute('aria-pressed', String(resumido));
    const icon = viewToggle.querySelector('i');
    if (icon) {
      icon.className = resumido
        ? 'fa-solid fa-table-columns'
        : 'fa-solid fa-table-list';
    }
  }

  if (viewToggleLabel) {
    viewToggleLabel.textContent = resumido
      ? 'Ver modo detalhado'
      : 'Ver modo resumido';
  }
}

if (viewToggle) {
  let modoSalvo = 'detalhado';
  try {
    modoSalvo = localStorage.getItem(viewStorageKey) || 'detalhado';
  } catch (_) {}
  aplicarModoBoletim(modoSalvo);

  viewToggle.addEventListener('click', () => {
    const novoModo = document.body.classList.contains('boletim-resumido')
      ? 'detalhado'
      : 'resumido';
    aplicarModoBoletim(novoModo);
    try { localStorage.setItem(viewStorageKey, novoModo); } catch (_) {}
  });
}
