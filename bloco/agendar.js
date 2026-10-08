// =====================================================
// agendar.js — FOAG
// Agenda + Notas + Tarefas + Horário + Matérias
// =====================================================

document.addEventListener('DOMContentLoaded', function () {
    console.log('Agenda + Horário carregados ✅');

    // =================================================
    // CONFIGURAÇÕES
    // =================================================

    const AGENDA_SAVE_URL =
        window.AGENDA_SAVE_URL || FOAG_CONFIG.endpoints.agendaSalvar;

    const HORARIO_SAVE_URL =
        window.HORARIO_SAVE_URL || FOAG_CONFIG.endpoints.agendaSalvar;

    const HORARIO_HTML =
        typeof window.HORARIO_HTML === 'string'
            ? window.HORARIO_HTML
            : '';

    // =================================================
    // MATÉRIAS
    // =================================================

    let materiasHorario =
        Array.isArray(window.MATERIAS_DATA)
            ? window.MATERIAS_DATA
            : [];

    materiasHorario =
        materiasHorario
            .filter(function (materia) {
                return (
                    materia &&
                    typeof materia === 'object' &&
                    String(
                        materia.nome || ''
                    ).trim() !== ''
                );
            })
            .map(function (materia) {
                return {
                    id: String(
                        materia.id ||
                        materia.codigo ||
                        materia.nome ||
                        ''
                    ),

                    nome: String(
                        materia.nome || ''
                    ).trim(),

                    cor: String(
                        materia.cor ||
                        '#38a5ff'
                    ),

                    icone: String(
                        materia.icone ||
                        'fa-book'
                    )
                };
            });

    // =================================================
    // DADOS DA AGENDA
    // =================================================

    let agendaData =
        window.AGENDA_DATA;

    if (
        !agendaData ||
        typeof agendaData !== 'object' ||
        Array.isArray(agendaData)
    ) {
        agendaData = {
            notas: [],
            tarefas: [],
            nao_esquecer: []
        };
    }

    if (!Array.isArray(agendaData.notas)) {
        agendaData.notas = [];
    }

    if (!Array.isArray(agendaData.tarefas)) {
        agendaData.tarefas = [];
    }

    if (!Array.isArray(agendaData.nao_esquecer)) {
        agendaData.nao_esquecer = [];
    }

    // =================================================
    // FUNÇÕES AUXILIARES
    // =================================================

    function debounce(
        funcao,
        tempo = 500
    ) {
        let temporizador;

        return function (...argumentos) {
            clearTimeout(
                temporizador
            );

            temporizador =
                setTimeout(
                    function () {
                        funcao.apply(
                            null,
                            argumentos
                        );
                    },
                    tempo
                );
        };
    }

    function escaparHtml(valor) {
        return window.FOAG?.utils?.escapeHtml
            ? FOAG.utils.escapeHtml(valor)
            : String(valor ?? '');
    }

    function nomeArquivoSeguro(nome) {
        const nomeTratado =
            String(
                nome || 'nota'
            )
                .replace(
                    /[<>:"/\\|?*\x00-\x1F]/g,
                    '_'
                )
                .trim();

        return nomeTratado ||
            'nota';
    }

    function dataHojeIso() {
        const hoje =
            new Date();

        return [
            hoje.getFullYear(),

            String(
                hoje.getMonth() + 1
            ).padStart(
                2,
                '0'
            ),

            String(
                hoje.getDate()
            ).padStart(
                2,
                '0'
            )
        ].join('-');
    }

    function normalizarTexto(texto) {
        return String(
            texto || ''
        )
            .normalize(
                'NFD'
            )
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .toLowerCase()
            .trim();
    }

    // =================================================
    // STATUS DE SALVAMENTO
    // =================================================

    function atualizarStatusSalvamento(
        estado
    ) {
        const status =
            document.getElementById(
                'status-salvamento'
            );

        if (!status) {
            return;
        }

        const icone =
            status.querySelector(
                'i'
            );

        const texto =
            status.querySelector(
                'span'
            );

        status.dataset.status =
            estado;

        if (
            estado ===
            'salvando'
        ) {
            if (icone) {
                icone.className =
                    'fa-solid fa-cloud-arrow-up';
            }

            if (texto) {
                texto.textContent =
                    'Salvando...';
            }

            return;
        }

        if (
            estado ===
            'erro'
        ) {
            if (icone) {
                icone.className =
                    'fa-solid fa-triangle-exclamation';
            }

            if (texto) {
                texto.textContent =
                    'Erro ao salvar';
            }

            return;
        }

        if (icone) {
            icone.className =
                'fa-solid fa-circle-check';
        }

        if (texto) {
            texto.textContent =
                'Salvo';
        }
    }

    // =================================================
    // ELEMENTOS DA AGENDA
    // =================================================

    const listaTarefas =
        document.getElementById(
            'lista-tarefas'
        );

    const listaNaoEsquecer =
        document.getElementById(
            'lista-nao-esquecer'
        );

    const salvarNotaButton =
        document.getElementById(
            'btn-salvar-nota'
        );

    const textareaNotas =
        document.querySelector(
            '#notas textarea'
        );

    const noteList =
        document.getElementById(
            'noteList'
        );

    const addTarefaButton =
        document.getElementById(
            'add-tarefa'
        );

    const addNaoEsquecerButton =
        document.getElementById(
            'add-nao-esquecer'
        );

    // =================================================
    // MODAIS
    // =================================================

    const modalNomearNota =
        document.getElementById(
            'modal-nomear-nota'
        );

    const inputNomeNota =
        document.getElementById(
            'nome-nota'
        );

    const btnConfirmarNomeNota =
        document.getElementById(
            'confirmar-nome-nota'
        );

    const btnCancelarNomeNota =
        document.getElementById(
            'cancelar-nome-nota'
        );

    const modalExcluir =
        document.getElementById(
            'modal-excluir'
        );

    const excluirTitulo =
        document.getElementById(
            'excluir-titulo'
        );

    const excluirMensagem =
        document.getElementById(
            'excluir-mensagem'
        );

    const btnConfirmarExclusao =
        document.getElementById(
            'confirmar-exclusao'
        );

    const btnCancelarExclusao =
        document.getElementById(
            'cancelar-exclusao'
        );

    let notaPendente =
        '';

    let notaEmEdicaoId =
        null;

    let tipoExclusao =
        '';

    let dadosExclusao =
        null;

    function abrirModalNomearNota(
        tituloInicial = ''
    ) {
        if (
            !modalNomearNota ||
            !inputNomeNota
        ) {
            return;
        }

        inputNomeNota.value =
            tituloInicial;

        modalNomearNota.style.display =
            'flex';

        setTimeout(
            function () {
                inputNomeNota.focus();
                inputNomeNota.select();
            },
            50
        );
    }

    function fecharModalNomearNota() {
        if (
            modalNomearNota
        ) {
            modalNomearNota.style.display =
                'none';
        }

        if (
            inputNomeNota
        ) {
            inputNomeNota.value =
                '';
        }

        notaPendente =
            '';

        notaEmEdicaoId =
            null;
    }

    function abrirModalExclusao(
        titulo,
        mensagem,
        tipo,
        dados
    ) {
        if (
            !modalExcluir ||
            !excluirTitulo ||
            !excluirMensagem
        ) {
            return;
        }

        excluirTitulo.textContent =
            titulo;

        excluirMensagem.textContent =
            mensagem;

        tipoExclusao =
            tipo;

        dadosExclusao =
            dados;

        modalExcluir.style.display =
            'flex';
    }

    function fecharModalExclusao() {
        if (
            modalExcluir
        ) {
            modalExcluir.style.display =
                'none';
        }

        tipoExclusao =
            '';

        dadosExclusao =
            null;
    }

    // =================================================
    // SALVAR AGENDA
    // =================================================

    let filaSalvamentoAgenda =
        Promise.resolve();

    async function enviarAgendaParaServidor(
        payload
    ) {
        const resposta =
            await fetch(
                AGENDA_SAVE_URL,
                {
                    method:
                        'POST',

                    credentials:
                        'same-origin',

                    cache:
                        'no-store',

                    headers: {
                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json'
                    },

                    body:
                        payload
                }
            );

        const textoResposta =
            await resposta.text();

        let retorno =
            null;

        try {
            retorno =
                JSON.parse(
                    textoResposta
                );
        } catch (_) {
            retorno =
                null;
        }

        if (
            !resposta.ok
        ) {
            throw new Error(
                retorno?.mensagem ||
                retorno?.erro ||
                textoResposta ||
                `Erro HTTP ${resposta.status}`
            );
        }

        if (
            retorno &&
            retorno.ok === false
        ) {
            throw new Error(
                retorno.mensagem ||
                retorno.erro ||
                'Não foi possível salvar a Agenda.'
            );
        }

        return true;
    }

    function salvarAgendaNoServidor() {
        atualizarStatusSalvamento(
            'salvando'
        );

        const payload =
            JSON.stringify(
                agendaData
            );

        filaSalvamentoAgenda =
            filaSalvamentoAgenda.then(
                function () {
                    return enviarAgendaParaServidor(
                        payload
                    );
                },
                function () {
                    return enviarAgendaParaServidor(
                        payload
                    );
                }
            );

        filaSalvamentoAgenda =
            filaSalvamentoAgenda
                .then(
                    function () {
                        atualizarStatusSalvamento(
                            'salvo'
                        );

                        return true;
                    }
                )
                .catch(
                    function (erro) {
                        console.error(
                            'Erro ao salvar a Agenda:',
                            erro
                        );

                        atualizarStatusSalvamento(
                            'erro'
                        );

                        return false;
                    }
                );

        return filaSalvamentoAgenda;
    }

    // =================================================
    // TAREFAS E LEMBRETES
    // =================================================

    function atualizarIndices(
        lista
    ) {
        if (!lista) {
            return;
        }

        Array.from(
            lista.rows
        ).forEach(
            function (
                linha,
                indice
            ) {
                if (
                    linha.cells[0]
                ) {
                    linha.cells[0]
                        .textContent =
                        indice + 1;
                }
            }
        );
    }

    function textoDaLinha(
        linha
    ) {
        const textoTarefa =
            linha?.querySelector(
                '.tarefa-texto'
            );

        if (
            textoTarefa
        ) {
            return textoTarefa
                .textContent
                .trim();
        }

        return (
            linha?.cells[1]
                ?.textContent
                .trim() ||
            ''
        );
    }

    function aplicarEstadoTarefa(
        linha
    ) {
        if (!linha) {
            return;
        }

        const checkbox =
            linha.querySelector(
                '.tarefa-checkbox'
            );

        const inputData =
            linha.cells[2]
                ?.querySelector(
                    'input[type="date"]'
                );

        if (!checkbox) {
            return;
        }

        const concluida =
            checkbox.checked;

        const data =
            inputData?.value ||
            '';

        const hoje =
            dataHojeIso();

        linha.classList.toggle(
            'tarefa-concluida',
            concluida
        );

        linha.classList.toggle(
            'tarefa-atrasada',
            !concluida &&
            data !== '' &&
            data < hoje
        );

        linha.classList.toggle(
            'tarefa-hoje',
            !concluida &&
            data !== '' &&
            data === hoje
        );
    }

    function compararTarefas(
        a,
        b
    ) {
        const concluidaA =
            Boolean(
                a.concluida
            );

        const concluidaB =
            Boolean(
                b.concluida
            );

        if (
            concluidaA !==
            concluidaB
        ) {
            return concluidaA
                ? 1
                : -1;
        }

        const dataA =
            String(
                a.data || ''
            );

        const dataB =
            String(
                b.data || ''
            );

        if (
            dataA &&
            dataB &&
            dataA !== dataB
        ) {
            return dataA
                .localeCompare(
                    dataB
                );
        }

        if (
            dataA &&
            !dataB
        ) {
            return -1;
        }

        if (
            !dataA &&
            dataB
        ) {
            return 1;
        }

        return String(
            a.texto || ''
        ).localeCompare(
            String(
                b.texto || ''
            ),
            'pt-BR'
        );
    }

    function dadosDaLinha(
        linha
    ) {
        const dadosOriginais =
            linha?._dadosOriginais &&
            typeof linha._dadosOriginais ===
                'object'
                ? linha._dadosOriginais
                : {};

        const inputData =
            linha?.cells[2]
                ?.querySelector(
                    'input[type="date"]'
                );

        const checkbox =
            linha?.querySelector(
                '.tarefa-checkbox'
            );

        const resultado = {
            ...dadosOriginais,

            texto:
                textoDaLinha(
                    linha
                ),

            data:
                inputData?.value ||
                ''
        };

        if (
            checkbox
        ) {
            resultado.concluida =
                checkbox.checked;
        }

        return resultado;
    }


    function compararPorDataProxima(linhaA, linhaB) {
        const a = dadosDaLinha(linhaA);
        const b = dadosDaLinha(linhaB);
        const dataA = String(a.data || '');
        const dataB = String(b.data || '');

        if (dataA && dataB && dataA !== dataB) {
            return dataA.localeCompare(dataB);
        }
        if (dataA && !dataB) return -1;
        if (!dataA && dataB) return 1;

        // Em tarefas, concluídas ficam depois apenas quando a data empata.
        const concluidaA = Boolean(a.concluida);
        const concluidaB = Boolean(b.concluida);
        if (concluidaA !== concluidaB) return concluidaA ? 1 : -1;

        return String(a.texto || '').localeCompare(String(b.texto || ''), 'pt-BR');
    }

    function ordenarListaPorData(lista) {
        if (!lista) return;
        const linhas = Array.from(lista.rows);
        linhas.sort(compararPorDataProxima);
        linhas.forEach((linha) => lista.appendChild(linha));
        atualizarIndices(lista);
    }

    const estadoExpandido = {
        tarefas: false,
        lembretes: false
    };

    function atualizarVisibilidadeLista(tipo) {
        const lista = tipo === 'tarefas' ? listaTarefas : listaNaoEsquecer;
        const painel = document.getElementById(tipo === 'tarefas' ? 'tarefas' : 'lembretes');
        const botao = document.getElementById(tipo === 'tarefas' ? 'expandir-tarefas' : 'expandir-lembretes');
        if (!lista || !painel || !botao) return;

        const linhas = Array.from(lista.rows);
        const emSelecao = painel.classList.contains('modo-selecao');
        const expandido = estadoExpandido[tipo] || emSelecao;
        const precisaExpandir = linhas.length > 3;

        botao.hidden = !precisaExpandir || emSelecao;
        botao.setAttribute('aria-expanded', expandido ? 'true' : 'false');
        botao.innerHTML = expandido
            ? '<i class="fa-solid fa-down-left-and-up-right-to-center" aria-hidden="true"></i> Recolher'
            : '<i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i> Expandir';

        linhas.forEach((linha, index) => {
            const ocultar = !expandido && index >= 3;
            linha.classList.toggle('agenda-item-oculto', ocultar);
        });

        painel.classList.toggle('agenda-painel-expandido', expandido && precisaExpandir);
    }

    function atualizarListasAgenda() {
        ordenarListaPorData(listaTarefas);
        ordenarListaPorData(listaNaoEsquecer);
        atualizarVisibilidadeLista('tarefas');
        atualizarVisibilidadeLista('lembretes');
    }

    function ordenarLinhasTarefas() {
        ordenarListaPorData(listaTarefas);
        Array.from(listaTarefas?.rows || []).forEach(aplicarEstadoTarefa);
        atualizarVisibilidadeLista('tarefas');
    }

    function ordenarLinhasLembretes() {
        ordenarListaPorData(listaNaoEsquecer);
        atualizarVisibilidadeLista('lembretes');
    }

    function criarLinhaAgenda(
        lista,
        dadosIniciais = {}
    ) {
        if (!lista) {
            return null;
        }

        const linha =
            lista.insertRow();

        const ehTarefa =
            lista.id ===
            'lista-tarefas';

        linha._dadosOriginais = {
            ...dadosIniciais
        };

        // Número
        const celulaIndice =
            linha.insertCell(
                0
            );

        celulaIndice.textContent =
            lista.rows.length;

        // Texto
        const celulaConteudo =
            linha.insertCell(
                1
            );

        celulaConteudo.style.wordBreak =
            'break-word';

        if (
            ehTarefa
        ) {
            const wrapper =
                document.createElement(
                    'div'
                );

            wrapper.className =
                'tarefa-conteudo-wrapper';

            const checkbox =
                document.createElement(
                    'input'
                );

            checkbox.type =
                'checkbox';

            checkbox.className =
                'tarefa-checkbox';

            checkbox.checked =
                Boolean(
                    dadosIniciais.concluida
                );

            checkbox.setAttribute(
                'aria-label',
                'Marcar tarefa como concluída'
            );

            const texto =
                document.createElement(
                    'span'
                );

            texto.className =
                'tarefa-texto';

            texto.contentEditable =
                'true';

            texto.spellcheck =
                true;

            texto.textContent =
                String(
                    dadosIniciais.texto ??
                    dadosIniciais.titulo ??
                    ''
                );

            wrapper.appendChild(
                checkbox
            );

            wrapper.appendChild(
                texto
            );

            celulaConteudo.appendChild(
                wrapper
            );

            checkbox.addEventListener(
                'change',
                function () {
                    aplicarEstadoTarefa(
                        linha
                    );

                    ordenarLinhasTarefas();

                    salvarDadosAgenda();
                }
            );

        } else {
            celulaConteudo.contentEditable =
                'true';

            celulaConteudo.spellcheck =
                true;

            celulaConteudo.textContent =
                String(
                    dadosIniciais.texto ??
                    dadosIniciais.titulo ??
                    ''
                );
        }

        // Data — entrada visual DD / MM / AAAA.
        // O valor real continua em ISO no input escondido, preservando o JSON atual.
        const celulaData =
            linha.insertCell(
                2
            );

        const inputData = document.createElement('input');
        inputData.type = 'date';
        inputData.className = 'agenda-date-iso';
        inputData.hidden = true;
        inputData.value = String(
            dadosIniciais.data ??
            dadosIniciais.date ??
            ''
        );

        const grupoData = document.createElement('div');
        grupoData.className = 'agenda-date-fields';

        const criarParteData = (classe, placeholder, tamanho, rotulo) => {
            const campo = document.createElement('input');
            campo.type = 'text';
            campo.className = `agenda-date-part ${classe}`;
            campo.placeholder = placeholder;
            campo.inputMode = 'numeric';
            campo.maxLength = tamanho;
            campo.autocomplete = 'off';
            campo.setAttribute('aria-label', rotulo);
            return campo;
        };

        const campoDia = criarParteData('agenda-date-day', 'DD', 2, 'Dia');
        const campoMes = criarParteData('agenda-date-month', 'MM', 2, 'Mês');
        const campoAno = criarParteData('agenda-date-year', 'AAAA', 4, 'Ano');

        const separador1 = document.createElement('span');
        separador1.className = 'agenda-date-separator';
        separador1.textContent = '/';
        const separador2 = separador1.cloneNode(true);

        const erroData = document.createElement('small');
        erroData.className = 'agenda-date-error';
        erroData.setAttribute('aria-live', 'polite');

        grupoData.append(campoDia, separador1, campoMes, separador2, campoAno);
        celulaData.append(grupoData, erroData, inputData);

        const preencherPartesData = (iso) => {
            const partes = String(iso || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
            campoDia.value = partes ? partes[3] : '';
            campoMes.value = partes ? partes[2] : '';
            campoAno.value = partes ? partes[1] : '';
        };

        const isoDaDataDigitada = () => {
            const diaTxt = campoDia.value.trim();
            const mesTxt = campoMes.value.trim();
            const anoTxt = campoAno.value.trim();

            if (diaTxt.length !== 2 || mesTxt.length !== 2 || anoTxt.length !== 4) {
                return null;
            }

            const dia = Number(diaTxt);
            const mes = Number(mesTxt);
            const ano = Number(anoTxt);

            if (ano < Number(dataHojeIso().slice(0, 4)) || ano > 2030) {
                return null;
            }

            const data = new Date(ano, mes - 1, dia);
            if (
                data.getFullYear() !== ano ||
                data.getMonth() !== mes - 1 ||
                data.getDate() !== dia
            ) {
                return null;
            }

            return `${ano}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
        };

        const limparErroData = () => {
            erroData.textContent = '';
            grupoData.classList.remove('is-invalid');
        };

        const mostrarErroData = (mensagem) => {
            erroData.textContent = mensagem;
            grupoData.classList.add('is-invalid');
        };

        const validarDataDigitada = ({ salvar = false, mostrarIncompleta = false } = {}) => {
            limparErroData();

            const vazia = !campoDia.value && !campoMes.value && !campoAno.value;
            if (vazia) {
                inputData.value = '';
                if (salvar) salvarDadosAgenda();
                return true;
            }

            const completa =
                campoDia.value.length === 2 &&
                campoMes.value.length === 2 &&
                campoAno.value.length === 4;

            if (!completa) {
                inputData.value = '';
                if (mostrarIncompleta) {
                    mostrarErroData('Complete a data.');
                }
                return false;
            }

            const iso = isoDaDataDigitada();
            if (!iso) {
                inputData.value = '';
                if (Number(campoAno.value) > 2030) {
                    mostrarErroData('Máximo: 2030.');
                } else {
                    mostrarErroData('Data inválida.');
                }
                return false;
            }

            if (iso < dataHojeIso()) {
                inputData.value = '';
                mostrarErroData('Escolha hoje ou uma data futura.');
                return false;
            }

            inputData.value = iso;
            campoDia.value = iso.slice(8, 10);
            campoMes.value = iso.slice(5, 7);
            campoAno.value = iso.slice(0, 4);

            if (ehTarefa) {
                aplicarEstadoTarefa(linha);
                ordenarLinhasTarefas();
            } else {
                ordenarLinhasLembretes();
            }

            if (salvar) salvarDadosAgenda();
            return true;
        };

        preencherPartesData(inputData.value);

        [campoDia, campoMes, campoAno].forEach((campo) => {
            campo.addEventListener('input', function () {
                const limite = campo === campoAno ? 4 : 2;
                campo.value = campo.value.replace(/\D/g, '').slice(0, limite);
                inputData.value = '';
                limparErroData();

                if (campo === campoDia && campo.value.length === 2) {
                    campoMes.focus();
                    campoMes.select();
                } else if (campo === campoMes && campo.value.length === 2) {
                    campoAno.focus();
                    campoAno.select();
                }

                if (
                    campoDia.value.length === 2 &&
                    campoMes.value.length === 2 &&
                    campoAno.value.length === 4
                ) {
                    validarDataDigitada({ salvar: true });
                }
            });

            campo.addEventListener('keydown', function (event) {
                if (event.key === 'Backspace' && !campo.value) {
                    if (campo === campoAno) campoMes.focus();
                    if (campo === campoMes) campoDia.focus();
                }
                if (event.key === 'Enter') {
                    event.preventDefault();
                    validarDataDigitada({ salvar: true, mostrarIncompleta: true });
                }
            });

            campo.addEventListener('blur', function () {
                setTimeout(() => {
                    if (!grupoData.contains(document.activeElement)) {
                        validarDataDigitada({ salvar: true, mostrarIncompleta: true });
                    }
                }, 0);
            });
        });

        // Seleção para exclusão em lote. O botão de excluir fica no cabeçalho do painel.
        const celulaAcoes = linha.insertCell(3);
        celulaAcoes.className = 'agenda-item-selecao';

        const checkboxSelecao = document.createElement('input');
        checkboxSelecao.type = 'checkbox';
        checkboxSelecao.className = 'agenda-selecao-checkbox';
        checkboxSelecao.setAttribute('aria-label', ehTarefa ? 'Selecionar tarefa para excluir' : 'Selecionar lembrete para excluir');
        celulaAcoes.appendChild(checkboxSelecao);

        if (
            ehTarefa
        ) {
            aplicarEstadoTarefa(
                linha
            );
        }

        return linha;
    }

    function salvarDadosAgenda() {
        if (
            !listaTarefas ||
            !listaNaoEsquecer
        ) {
            return Promise.resolve(
                false
            );
        }

        agendaData.tarefas =
            Array.from(
                listaTarefas.rows
            )
                .map(
                    dadosDaLinha
                )
                .filter(
                    function (
                        item
                    ) {
                        return (
                            item.texto !==
                                '' ||
                            item.data !==
                                ''
                        );
                    }
                )
                .sort(
                    compararTarefas
                );

        agendaData.nao_esquecer =
            Array.from(
                listaNaoEsquecer.rows
            )
                .map(
                    dadosDaLinha
                )
                .filter(
                    function (
                        item
                    ) {
                        return (
                            item.texto !==
                                '' ||
                            item.data !==
                                ''
                        );
                    }
                );

        return salvarAgendaNoServidor();
    }

    const salvarDadosComAtraso =
        debounce(
            salvarDadosAgenda,
            500
        );

    function carregarDadosAgenda() {
        if (
            !listaTarefas ||
            !listaNaoEsquecer
        ) {
            return;
        }

        listaTarefas.innerHTML =
            '';

        listaNaoEsquecer.innerHTML =
            '';

        agendaData.tarefas =
            agendaData.tarefas
                .map(
                    function (
                        tarefa
                    ) {
                        return {
                            ...tarefa,

                            concluida:
                                Boolean(
                                    tarefa.concluida
                                )
                        };
                    }
                )
                .sort(
                    compararTarefas
                );

        agendaData.tarefas.forEach(
            function (
                tarefa
            ) {
                criarLinhaAgenda(
                    listaTarefas,
                    tarefa
                );
            }
        );

        agendaData.nao_esquecer
            .forEach(
                function (
                    item
                ) {
                    criarLinhaAgenda(
                        listaNaoEsquecer,
                        item
                    );
                }
            );

        atualizarIndices(
            listaTarefas
        );

        atualizarIndices(
            listaNaoEsquecer
        );
        atualizarContadoresAgenda();
    }

    function excluirTarefa(
        linha
    ) {
        if (!linha) {
            return;
        }

        linha.remove();

        atualizarIndices(
            listaTarefas
        );

        salvarDadosAgenda();
    }

    function excluirNaoEsquecer(
        linha
    ) {
        if (!linha) {
            return;
        }

        linha.remove();

        atualizarIndices(
            listaNaoEsquecer
        );

        salvarDadosAgenda();
    }

    // =================================================
    // NOTAS
    // =================================================

    function baixarPdfNota(
        titulo,
        conteudo
    ) {
        if (
            !window.jspdf ||
            !window.jspdf.jsPDF
        ) {
            alert(
                'A biblioteca de PDF não foi carregada.'
            );

            return;
        }

        const {
            jsPDF
        } = window.jspdf;

        const documento =
            new jsPDF();

        const larguraPagina =
            documento
                .internal
                .pageSize
                .getWidth();

        const margem =
            15;

        let posicaoY =
            15;

        documento.setFont(
            'helvetica',
            'bold'
        );

        documento.setFontSize(
            20
        );

        documento.setTextColor(
            40,
            40,
            120
        );

        documento.text(
            'FOAG — Minhas Notas',

            larguraPagina / 2,

            posicaoY,

            {
                align:
                    'center'
            }
        );

        posicaoY +=
            9;

        documento.setFont(
            'helvetica',
            'normal'
        );

        documento.setFontSize(
            10
        );

        documento.text(
            `Exportado em: ${new Date().toLocaleString('pt-BR')}`,

            larguraPagina / 2,

            posicaoY,

            {
                align:
                    'center'
            }
        );

        posicaoY +=
            13;

        documento.setFont(
            'helvetica',
            'bold'
        );

        documento.setFontSize(
            16
        );

        documento.setTextColor(
            0,
            0,
            0
        );

        const tituloQuebrado =
            documento
                .splitTextToSize(
                    titulo,

                    larguraPagina -
                        margem *
                        2
                );

        documento.text(
            tituloQuebrado,

            margem,

            posicaoY
        );

        posicaoY +=
            tituloQuebrado.length *
                8 +
            4;

        documento.setFont(
            'helvetica',
            'normal'
        );

        documento.setFontSize(
            12
        );

        const conteudoQuebrado =
            documento
                .splitTextToSize(
                    conteudo,

                    larguraPagina -
                        margem *
                        2
                );

        documento.text(
            conteudoQuebrado,

            margem,

            posicaoY
        );

        documento.save(
            `${nomeArquivoSeguro(
                titulo
            )}.pdf`
        );
    }

    function carregarNotas() {
        if (
            !noteList
        ) {
            return;
        }

        noteList.innerHTML =
            '';

        const notasOrdenadas =
            [
                ...agendaData.notas
            ].sort(
                function (
                    notaA,
                    notaB
                ) {
                    return (
                        Number(
                            notaB.id
                        ) -
                        Number(
                            notaA.id
                        )
                    );
                }
            );

        if (
            notasOrdenadas.length ===
            0
        ) {
            const semNotas =
                document.createElement(
                    'div'
                );

            semNotas.className =
                'sem-notas';

            semNotas.textContent =
                'Nenhuma nota salva ainda.';

            noteList.appendChild(
                semNotas
            );

            return;
        }

        notasOrdenadas.forEach(
            function (
                nota
            ) {
                const elementoNota =
                    document.createElement(
                        'div'
                    );

                elementoNota.className =
                    'nota-item';

                elementoNota.innerHTML = `
                    <span class="nota-titulo">
                        ${escaparHtml(
                            nota.titulo
                        )}
                    </span>

                    <span class="nota-data">
                        ${escaparHtml(
                            nota.data
                        )}
                    </span>

                    <div class="nota-conteudo">
                        ${escaparHtml(
                            nota.texto
                        )}
                    </div>

                    <div class="nota-acoes">

                        <button
                            type="button"
                            class="btn-nota btn-editar">
                            Editar
                        </button>

                        <button
                            type="button"
                            class="btn-nota btn-excluir-nota">
                            Excluir
                        </button>

                        <button
                            type="button"
                            class="btn-nota btn-pequeno">
                            Baixar PDF
                        </button>

                    </div>
                `;

                elementoNota
                    .querySelector(
                        '.btn-editar'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            editarNota(
                                nota.id
                            );
                        }
                    );

                elementoNota
                    .querySelector(
                        '.btn-excluir-nota'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            abrirModalExclusao(
                                'Excluir Nota',

                                `Tem certeza que deseja excluir a nota "${nota.titulo}"?`,

                                'nota',

                                {
                                    id:
                                        nota.id
                                }
                            );
                        }
                    );

                elementoNota
                    .querySelector(
                        '.btn-pequeno'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            baixarPdfNota(
                                nota.titulo,
                                nota.texto
                            );
                        }
                    );

                noteList.appendChild(
                    elementoNota
                );
            }
        );
    }

    function editarNota(
        id
    ) {
        const nota =
            agendaData.notas.find(
                function (
                    item
                ) {
                    return (
                        item.id ===
                        id
                    );
                }
            );

        if (!nota) {
            return;
        }

        notaEmEdicaoId =
            nota.id;

        notaPendente =
            nota.texto;

        if (
            textareaNotas
        ) {
            textareaNotas.value =
                nota.texto;

            textareaNotas.focus();

            document
                .getElementById(
                    'editando-nota-aviso'
                )
                ?.remove();

            const mensagem =
                document.createElement(
                    'div'
                );

            mensagem.id =
                'editando-nota-aviso';

            mensagem.style.cssText = `
                color: #38a5ff;
                font-size: 13px;
                margin-top: 5px;
                padding: 8px 12px;
                background: #eef8ff;
                border-radius: 6px;
                border-left: 3px solid #38a5ff;
            `;

            mensagem.textContent =
                `✏️ Editando: "${nota.titulo}"`;

            textareaNotas
                .parentNode
                .insertBefore(
                    mensagem,

                    textareaNotas
                        .nextSibling
                );
        }

        if (
            salvarNotaButton
        ) {
            salvarNotaButton.textContent =
                '✏️ Atualizar Nota';

            salvarNotaButton
                .dataset
                .editando =
                'true';
        }
    }

    function salvarNotaComTitulo(
        texto,
        titulo
    ) {
        const textoTratado =
            String(
                texto || ''
            ).trim();

        let tituloTratado =
            String(
                titulo || ''
            ).trim();

        if (
            notaEmEdicaoId &&
            !tituloTratado
        ) {
            const notaOriginal =
                agendaData.notas.find(
                    function (
                        nota
                    ) {
                        return (
                            nota.id ===
                            notaEmEdicaoId
                        );
                    }
                );

            if (
                notaOriginal
            ) {
                tituloTratado =
                    notaOriginal.titulo;
            }
        }

        if (
            !textoTratado
        ) {
            alert(
                'Escreva o conteúdo da nota.'
            );

            return false;
        }

        if (
            !tituloTratado
        ) {
            alert(
                'Dê um nome para sua nota.'
            );

            return false;
        }

        const notaComMesmoTitulo =
            agendaData.notas.find(
                function (
                    nota
                ) {
                    return (
                        nota.titulo ===
                            tituloTratado &&
                        nota.id !==
                            notaEmEdicaoId
                    );
                }
            );

        if (
            notaComMesmoTitulo
        ) {
            abrirModalExclusao(
                'Sobrescrever Nota',

                `Já existe uma nota com o título "${tituloTratado}". Deseja sobrescrever?`,

                'sobrescrever',

                {
                    titulo:
                        tituloTratado,

                    texto:
                        textoTratado,

                    notaEmEdicaoId:
                        notaEmEdicaoId
                }
            );

            return false;
        }

        const indiceEdicao =
            agendaData.notas.findIndex(
                function (
                    nota
                ) {
                    return (
                        nota.id ===
                        notaEmEdicaoId
                    );
                }
            );

        if (
            indiceEdicao >=
            0
        ) {
            agendaData.notas[
                indiceEdicao
            ] = {
                ...agendaData.notas[
                    indiceEdicao
                ],

                titulo:
                    tituloTratado,

                texto:
                    textoTratado,

                data:
                    new Date()
                        .toLocaleString(
                            'pt-BR'
                        )
            };

        } else {
            agendaData.notas.push(
                {
                    id:
                        Date.now(),

                    titulo:
                        tituloTratado,

                    texto:
                        textoTratado,

                    data:
                        new Date()
                            .toLocaleString(
                                'pt-BR'
                            )
                }
            );
        }

        salvarAgendaNoServidor();

        carregarNotas();

        if (
            textareaNotas
        ) {
            textareaNotas.value =
                '';
        }

        if (
            salvarNotaButton
        ) {
            salvarNotaButton.textContent =
                'Salvar Nota';

            salvarNotaButton
                .dataset
                .editando =
                '';
        }

        notaEmEdicaoId =
            null;

        document
            .getElementById(
                'editando-nota-aviso'
            )
            ?.remove();

        return true;
    }

    function sobrescreverNota(
        dados
    ) {
        if (!dados) {
            return;
        }

        const idEdicao =
            dados.notaEmEdicaoId ||
            null;

        agendaData.notas =
            agendaData.notas.filter(
                function (
                    nota
                ) {
                    return (
                        nota.titulo !==
                            dados.titulo &&
                        nota.id !==
                            idEdicao
                    );
                }
            );

        agendaData.notas.push(
            {
                id:
                    idEdicao ||
                    Date.now(),

                titulo:
                    dados.titulo,

                texto:
                    dados.texto,

                data:
                    new Date()
                        .toLocaleString(
                            'pt-BR'
                        )
            }
        );

        salvarAgendaNoServidor();

        carregarNotas();

        if (
            textareaNotas
        ) {
            textareaNotas.value =
                '';
        }

        notaEmEdicaoId =
            null;

        if (
            salvarNotaButton
        ) {
            salvarNotaButton.textContent =
                'Salvar Nota';

            salvarNotaButton
                .dataset
                .editando =
                '';
        }

        document
            .getElementById(
                'editando-nota-aviso'
            )
            ?.remove();
    }

    function excluirNota(
        id
    ) {
        agendaData.notas =
            agendaData.notas.filter(
                function (
                    nota
                ) {
                    return (
                        nota.id !==
                        id
                    );
                }
            );

        salvarAgendaNoServidor();

        carregarNotas();
    }

    function executarExclusao() {
        if (
            !dadosExclusao
        ) {
            fecharModalExclusao();

            return;
        }

        switch (
            tipoExclusao
        ) {
            case 'nota':

                excluirNota(
                    dadosExclusao.id
                );

                break;

            case 'tarefa':

                excluirTarefa(
                    dadosExclusao.linha
                );

                break;

            case 'nao-esquecer':

                excluirNaoEsquecer(
                    dadosExclusao.linha
                );

                break;

            case 'sobrescrever':

                sobrescreverNota(
                    dadosExclusao
                );

                break;

            case 'horario-linha':

                if (
                    dadosExclusao.linha &&
                    corpoTabelaHorario?.contains(dadosExclusao.linha)
                ) {
                    dadosExclusao.linha.remove();
                    linhaHorarioSelecionada = null;

                    if (btnExcluirLinhaHorario) {
                        btnExcluirLinhaHorario.classList.add('horario-btn-desativado');
                btnExcluirLinhaHorario.setAttribute('aria-disabled', 'true');
                    }

                    esconderMaterias();
                    agendarSalvamentoHorario();
                    renderizarHorarioVisual();
                }

                break;
        }

        fecharModalExclusao();
    }

    // =================================================
    // EXCLUSÃO EM LOTE — TAREFAS E LEMBRETES
    // =================================================
    const btnExcluirTarefas = document.getElementById('excluir-tarefas-toggle');
    const btnExcluirLembretes = document.getElementById('excluir-lembretes-toggle');
    const acoesExcluirTarefas = document.getElementById('acoes-excluir-tarefas');
    const acoesExcluirLembretes = document.getElementById('acoes-excluir-lembretes');
    const btnCancelarExcluirTarefas = document.getElementById('cancelar-excluir-tarefas');
    const btnCancelarExcluirLembretes = document.getElementById('cancelar-excluir-lembretes');
    const btnConfirmarExcluirTarefas = document.getElementById('confirmar-excluir-tarefas');
    const btnConfirmarExcluirLembretes = document.getElementById('confirmar-excluir-lembretes');

    function atualizarContadoresAgenda() {
        const ct = document.getElementById('contador-tarefas');
        const cl = document.getElementById('contador-lembretes');
        if (ct && listaTarefas) ct.textContent = String(listaTarefas.rows.length);
        if (cl && listaNaoEsquecer) cl.textContent = String(listaNaoEsquecer.rows.length);
    }

    function definirModoSelecao(lista, painel, acoes, ativo) {
        if (!lista || !painel || !acoes) return;
        painel.classList.toggle('modo-selecao', ativo);
        acoes.hidden = !ativo;
        lista.querySelectorAll('.agenda-selecao-checkbox').forEach((cb) => {
            cb.checked = false;
        });
        atualizarVisibilidadeLista(lista === listaTarefas ? 'tarefas' : 'lembretes');
    }

    function excluirSelecionados(lista, painel, acoes) {
        if (!lista) return;
        const selecionados = Array.from(lista.querySelectorAll('tr')).filter((linha) =>
            linha.querySelector('.agenda-selecao-checkbox')?.checked
        );
        if (!selecionados.length) {
            alert('Selecione pelo menos um item para excluir.');
            return;
        }
        selecionados.forEach((linha) => linha.remove());
        atualizarIndices(lista);
        atualizarContadoresAgenda();
        definirModoSelecao(lista, painel, acoes, false);
        salvarDadosAgenda();
        atualizarListasAgenda();
    }

    const btnExpandirTarefas = document.getElementById('expandir-tarefas');
    const btnExpandirLembretes = document.getElementById('expandir-lembretes');

    btnExpandirTarefas?.addEventListener('click', () => {
        estadoExpandido.tarefas = !estadoExpandido.tarefas;
        atualizarVisibilidadeLista('tarefas');
    });
    btnExpandirLembretes?.addEventListener('click', () => {
        estadoExpandido.lembretes = !estadoExpandido.lembretes;
        atualizarVisibilidadeLista('lembretes');
    });

    btnExcluirTarefas?.addEventListener('click', () =>
        definirModoSelecao(listaTarefas, document.getElementById('tarefas'), acoesExcluirTarefas, true)
    );
    btnExcluirLembretes?.addEventListener('click', () =>
        definirModoSelecao(listaNaoEsquecer, document.getElementById('lembretes'), acoesExcluirLembretes, true)
    );
    btnCancelarExcluirTarefas?.addEventListener('click', () =>
        definirModoSelecao(listaTarefas, document.getElementById('tarefas'), acoesExcluirTarefas, false)
    );
    btnCancelarExcluirLembretes?.addEventListener('click', () =>
        definirModoSelecao(listaNaoEsquecer, document.getElementById('lembretes'), acoesExcluirLembretes, false)
    );
    btnConfirmarExcluirTarefas?.addEventListener('click', () =>
        excluirSelecionados(listaTarefas, document.getElementById('tarefas'), acoesExcluirTarefas)
    );
    btnConfirmarExcluirLembretes?.addEventListener('click', () =>
        excluirSelecionados(listaNaoEsquecer, document.getElementById('lembretes'), acoesExcluirLembretes)
    );

    // Estado inicial: ordenar e limitar a três itens.
    atualizarListasAgenda();

    // =================================================
    // EVENTOS DA AGENDA
    // =================================================

    addTarefaButton
        ?.addEventListener(
            'click',
            function () {
                const linha =
                    criarLinhaAgenda(
                        listaTarefas,

                        {
                            concluida:
                                false
                        }
                    );

                salvarDadosAgenda();
                atualizarContadoresAgenda();
                atualizarListasAgenda();
                atualizarListasAgenda();

                linha
                    ?.querySelector(
                        '.tarefa-texto'
                    )
                    ?.focus();
            }
        );

    addNaoEsquecerButton
        ?.addEventListener(
            'click',
            function () {
                const linha =
                    criarLinhaAgenda(
                        listaNaoEsquecer
                    );

                salvarDadosAgenda();
                atualizarContadoresAgenda();

                linha
                    ?.cells[1]
                    ?.focus();
            }
        );

    salvarNotaButton
        ?.addEventListener(
            'click',
            function () {
                const texto =
                    textareaNotas
                        ?.value
                        .trim() ||
                    '';

                if (
                    !texto
                ) {
                    alert(
                        'Escreva algo na nota antes de salvar.'
                    );

                    return;
                }

                if (
                    notaEmEdicaoId
                ) {
                    const notaOriginal =
                        agendaData.notas.find(
                            function (
                                nota
                            ) {
                                return (
                                    nota.id ===
                                    notaEmEdicaoId
                                );
                            }
                        );

                    if (
                        notaOriginal
                    ) {
                        salvarNotaComTitulo(
                            texto,

                            notaOriginal
                                .titulo
                        );

                    } else {
                        notaPendente =
                            texto;

                        abrirModalNomearNota();
                    }

                    return;
                }

                notaPendente =
                    texto;

                abrirModalNomearNota();
            }
        );

    btnConfirmarNomeNota
        ?.addEventListener(
            'click',
            function () {
                const titulo =
                    inputNomeNota
                        ?.value
                        .trim() ||
                    '';

                const salvou =
                    salvarNotaComTitulo(
                        notaPendente,
                        titulo
                    );

                if (
                    salvou
                ) {
                    fecharModalNomearNota();
                }
            }
        );

    inputNomeNota
        ?.addEventListener(
            'keydown',
            function (
                evento
            ) {
                if (
                    evento.key ===
                    'Enter'
                ) {
                    evento.preventDefault();

                    btnConfirmarNomeNota
                        ?.click();
                }
            }
        );

    btnCancelarNomeNota
        ?.addEventListener(
            'click',
            fecharModalNomearNota
        );

    btnConfirmarExclusao
        ?.addEventListener(
            'click',
            executarExclusao
        );

    btnCancelarExclusao
        ?.addEventListener(
            'click',
            fecharModalExclusao
        );

    modalNomearNota
        ?.addEventListener(
            'click',
            function (
                evento
            ) {
                if (
                    evento.target ===
                    modalNomearNota
                ) {
                    fecharModalNomearNota();
                }
            }
        );

    modalExcluir
        ?.addEventListener(
            'click',
            function (
                evento
            ) {
                if (
                    evento.target ===
                    modalExcluir
                ) {
                    fecharModalExclusao();
                }
            }
        );

    listaTarefas
        ?.addEventListener(
            'input',
            function (
                evento
            ) {
                if (
                    evento.target
                        .classList
                        ?.contains(
                            'tarefa-texto'
                        ) ||

                    evento.target
                        .closest
                        ?.(
                            '.tarefa-texto'
                        )
                ) {
                    salvarDadosComAtraso();
                }
            }
        );

    listaNaoEsquecer
        ?.addEventListener(
            'input',
            salvarDadosComAtraso
        );

    listaNaoEsquecer
        ?.addEventListener(
            'change',
            salvarDadosAgenda
        );

    // =================================================
    // HORÁRIO
    // =================================================

    const tabelaHorario =
        document.getElementById(
            'scheduleTable'
        );

    const corpoTabelaHorario =
        tabelaHorario
            ?.querySelector(
                'tbody'
            ) ||
        null;

    // =================================================
    // CAMPO DE HORÁRIO MAIS FÁCIL
    // =================================================

    function extrairHoras(
        texto
    ) {
        const horas =
            String(
                texto || ''
            ).match(
                /\b(?:[01]\d|2[0-3]):[0-5]\d\b/g
            ) ||
            [];

        return {
            inicio:
                horas[0] ||
                '',

            fim:
                horas[1] ||
                ''
        };
    }

    function criarCampoHora(
        classe,
        valor,
        ariaLabel
    ) {
        const input =
            document.createElement(
                'input'
            );

        input.type =
            'time';

        input.className =
            classe;

        input.value =
            valor || '';

        input.setAttribute(
            'aria-label',
            ariaLabel
        );

        return input;
    }

    function prepararCelulaHorario(
        celula
    ) {
        if (
            !celula
        ) {
            return;
        }

        if (
            Number(
                celula.colSpan ||
                1
            ) > 1
        ) {
            return;
        }

        if (
            celula.querySelector(
                '.horario-inputs'
            )
        ) {
            celula.contentEditable =
                'false';

            return;
        }

        const textoAntigo =
            celula
                .textContent
                .trim();

        const horas =
            extrairHoras(
                textoAntigo
            );

        celula.innerHTML =
            '';

        celula.contentEditable =
            'false';

        celula.classList.add(
            'celula-horario'
        );

        const wrapper =
            document.createElement(
                'div'
            );

        wrapper.className =
            'horario-inputs';

        const inicio =
            criarCampoHora(
                'input-horario-inicio',

                horas.inicio,

                'Horário de início da aula'
            );

        const separador =
            document.createElement(
                'span'
            );

        separador.className =
            'horario-separador';

        separador.textContent =
            'às';

        const fim =
            criarCampoHora(
                'input-horario-fim',

                horas.fim,

                'Horário de término da aula'
            );

        wrapper.appendChild(
            inicio
        );

        wrapper.appendChild(
            separador
        );

        wrapper.appendChild(
            fim
        );

        celula.appendChild(
            wrapper
        );
    }

    function tornarHorarioEditavel() {
        if (
            !corpoTabelaHorario
        ) {
            return;
        }

        Array.from(
            corpoTabelaHorario.rows
        ).forEach(
            function (
                linha
            ) {
                const celulas =
                    Array.from(
                        linha.cells
                    );

                if (
                    celulas.length ===
                        1 &&
                    Number(
                        celulas[0]
                            .colSpan ||
                        1
                    ) > 1
                ) {
                    celulas[0]
                        .contentEditable =
                        'true';

                    return;
                }

                celulas.forEach(
                    function (
                        celula,
                        indice
                    ) {
                        if (
                            indice ===
                            0
                        ) {
                            prepararCelulaHorario(
                                celula
                            );

                        } else {
                            celula.contentEditable =
                                'true';
                        }
                    }
                );
            }
        );
    }

    if (
        corpoTabelaHorario &&
        HORARIO_HTML.trim() !==
            ''
    ) {
        corpoTabelaHorario.innerHTML =
            HORARIO_HTML;
    }

    tornarHorarioEditavel();

    // =================================================
    // VISUALIZAÇÃO MODERNA DO HORÁRIO
    // =================================================

    const horarioVisual = document.getElementById('horario-visual');
    const horarioEditor = document.getElementById('horario-editor');
    const btnEditarHorario = document.getElementById('btn-editar-horario');
    const btnFecharEdicaoHorario = document.getElementById('btn-fechar-edicao-horario');
    const btnMenuHorario = document.getElementById('btn-menu-horario');
    const horarioMenuOpcoes = document.getElementById('horario-menu-opcoes');
    const proximaTitulo = document.getElementById('horario-proxima-titulo');
    const proximaMeta = document.getElementById('horario-proxima-meta');

    const nomesDiasHorario = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'];

    function textoLimpoCelula(celula) {
        return String(celula?.innerText || celula?.textContent || '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function obterFaixaLinha(linha) {
        const inicio = linha?.querySelector('.input-horario-inicio')?.value || '';
        const fim = linha?.querySelector('.input-horario-fim')?.value || '';
        return { inicio, fim };
    }

    function minutosDeHora(hora) {
        const m = String(hora || '').match(/^(\d{2}):(\d{2})$/);
        return m ? Number(m[1]) * 60 + Number(m[2]) : null;
    }

    function corMateriaPorNome(nome) {
        const texto = String(nome || '').toLowerCase();
        let hash = 0;
        for (let i = 0; i < texto.length; i++) hash = ((hash << 5) - hash) + texto.charCodeAt(i);
        const tons = ['#38a5ff', '#8b5cf6', '#f59e0b', '#10b981', '#ec4899', '#06b6d4', '#ef4444'];
        return tons[Math.abs(hash) % tons.length];
    }

    function coletarHorarioVisual() {
        const dias = nomesDiasHorario.map(() => []);
        if (!corpoTabelaHorario) return dias;

        Array.from(corpoTabelaHorario.rows).forEach((linha) => {
            const { inicio, fim } = obterFaixaLinha(linha);
            if (!inicio && !fim) return;

            const celulas = Array.from(linha.cells || []);
            const intervalo = celulas.some(c => Number(c.colSpan || 1) > 1);

            if (intervalo) {
                const texto = textoLimpoCelula(celulas.find(c => Number(c.colSpan || 1) > 1)) || 'Intervalo';
                dias.forEach((lista) => lista.push({ inicio, fim, nome: texto, intervalo: true }));
                return;
            }

            for (let i = 1; i <= 5; i++) {
                const nome = textoLimpoCelula(celulas[i]);
                if (!nome) continue;
                dias[i - 1].push({ inicio, fim, nome, intervalo: false });
            }
        });

        dias.forEach(lista => lista.sort((a, b) => (minutosDeHora(a.inicio) ?? 9999) - (minutosDeHora(b.inicio) ?? 9999)));
        return dias;
    }

    function atualizarProximaAula(dias) {
        if (!proximaTitulo || !proximaMeta) return;

        const agora = new Date();
        const jsDia = agora.getDay();
        const indiceHoje = jsDia >= 1 && jsDia <= 5 ? jsDia - 1 : -1;
        const minutosAgora = agora.getHours() * 60 + agora.getMinutes();
        let escolhida = null;
        let deslocamento = 0;

        for (let passo = 0; passo < 7 && !escolhida; passo++) {
            const dataTeste = new Date(agora);
            dataTeste.setDate(agora.getDate() + passo);
            const d = dataTeste.getDay();
            if (d < 1 || d > 5) continue;
            const idx = d - 1;
            const candidatas = dias[idx].filter(item => !item.intervalo && item.inicio);
            const validas = passo === 0 && idx === indiceHoje
                ? candidatas.filter(item => (minutosDeHora(item.inicio) ?? -1) >= minutosAgora)
                : candidatas;
            if (validas.length) {
                escolhida = validas[0];
                deslocamento = passo;
            }
        }

        if (!escolhida) {
            proximaTitulo.textContent = 'Nenhuma aula encontrada';
            proximaMeta.textContent = 'Cadastre ou edite seu horário.';
            return;
        }

        proximaTitulo.textContent = escolhida.nome;
        const quando = deslocamento === 0 ? 'Hoje' : deslocamento === 1 ? 'Amanhã' : nomesDiasHorario[(agora.getDay() + deslocamento + 6) % 7] || 'Próximo dia';
        proximaMeta.textContent = `${quando} • ${escolhida.inicio}${escolhida.fim ? `–${escolhida.fim}` : ''}`;
    }

    function renderizarHorarioVisual() {
        if (!horarioVisual) return;
        const dias = coletarHorarioVisual();
        atualizarProximaAula(dias);
        horarioVisual.innerHTML = '';

        dias.forEach((itens, indice) => {
            const coluna = document.createElement('section');
            coluna.className = 'horario-dia-card';

            const titulo = document.createElement('div');
            titulo.className = 'horario-dia-titulo';
            titulo.textContent = nomesDiasHorario[indice];
            coluna.appendChild(titulo);

            if (!itens.length) {
                const vazio = document.createElement('div');
                vazio.className = 'horario-dia-vazio';
                vazio.textContent = 'Sem aulas';
                coluna.appendChild(vazio);
            } else {
                itens.forEach((item) => {
                    const card = document.createElement('div');
                    card.className = item.intervalo ? 'horario-aula-card horario-aula-intervalo' : 'horario-aula-card';
                    if (!item.intervalo) card.style.setProperty('--materia-cor', corMateriaPorNome(item.nome));

                    const hora = document.createElement('span');
                    hora.className = 'horario-aula-hora';
                    hora.textContent = `${item.inicio}${item.fim ? `–${item.fim}` : ''}`;

                    const nome = document.createElement('strong');
                    nome.textContent = item.nome;

                    card.append(hora, nome);
                    coluna.appendChild(card);
                });
            }

            horarioVisual.appendChild(coluna);
        });
    }

    function abrirEdicaoHorario() {
        if (horarioEditor) horarioEditor.hidden = false;
        if (horarioVisual) horarioVisual.hidden = true;
        btnEditarHorario?.classList.add('is-active');
    }

    function fecharEdicaoHorario() {
        if (horarioEditor) horarioEditor.hidden = true;
        if (horarioVisual) horarioVisual.hidden = false;
        btnEditarHorario?.classList.remove('is-active');
        renderizarHorarioVisual();
        if (typeof salvarHorarioNoServidor === 'function') salvarHorarioNoServidor(false);
    }

    btnEditarHorario?.addEventListener('click', abrirEdicaoHorario);
    btnFecharEdicaoHorario?.addEventListener('click', fecharEdicaoHorario);
    btnMenuHorario?.addEventListener('click', function (e) {
        e.stopPropagation();
        if (horarioMenuOpcoes) horarioMenuOpcoes.hidden = !horarioMenuOpcoes.hidden;
    });
    document.addEventListener('click', function (e) {
        if (horarioMenuOpcoes && !horarioMenuOpcoes.hidden && !horarioMenuOpcoes.contains(e.target) && e.target !== btnMenuHorario) {
            horarioMenuOpcoes.hidden = true;
        }
    });

    corpoTabelaHorario?.addEventListener('input', function () {
        window.clearTimeout(window.__foagHorarioPreviewTimer);
        window.__foagHorarioPreviewTimer = window.setTimeout(renderizarHorarioVisual, 120);
    });

    renderizarHorarioVisual();

    if (corpoTabelaHorario && typeof MutationObserver !== 'undefined') {
        const observadorHorario = new MutationObserver(function () {
            window.clearTimeout(window.__foagHorarioMutationTimer);
            window.__foagHorarioMutationTimer = window.setTimeout(renderizarHorarioVisual, 80);
        });
        observadorHorario.observe(corpoTabelaHorario, { childList: true, subtree: true, characterData: true });
    }

    // =================================================
    // AUTOCOMPLETE DAS MATÉRIAS
    // Só aparece depois que começa a digitar
    // =================================================

    let caixaMaterias =
        null;

    let celulaMateriaAtual =
        null;

    let indiceMateriaSelecionada =
        -1;

    function criarCaixaMaterias() {
        if (
            caixaMaterias
        ) {
            return caixaMaterias;
        }

        caixaMaterias =
            document.createElement(
                'div'
            );

        caixaMaterias.className =
            'horario-autocomplete';

        caixaMaterias.style.display =
            'none';

        document.body.appendChild(
            caixaMaterias
        );

        return caixaMaterias;
    }

    function esconderMaterias() {
        if (
            !caixaMaterias
        ) {
            return;
        }

        caixaMaterias.style.display =
            'none';

        caixaMaterias.innerHTML =
            '';

        indiceMateriaSelecionada =
            -1;
    }

    function filtrarMaterias(
        texto
    ) {
        const busca =
            normalizarTexto(
                texto
            );

        if (
            busca === ''
        ) {
            return [];
        }

        return materiasHorario
            .filter(
                function (
                    materia
                ) {
                    return normalizarTexto(
                        materia.nome
                    ).includes(
                        busca
                    );
                }
            )
            .slice(
                0,
                8
            );
    }

    function posicionarCaixaMaterias(
        celula
    ) {
        if (
            !celula ||
            !caixaMaterias
        ) {
            return;
        }

        const rect =
            celula
                .getBoundingClientRect();

        caixaMaterias.style.position =
            'absolute';

        caixaMaterias.style.left =
            `${
                rect.left +
                window.scrollX
            }px`;

        caixaMaterias.style.top =
            `${
                rect.bottom +
                window.scrollY +
                5
            }px`;

        caixaMaterias.style.width =
            `${
                Math.max(
                    rect.width,
                    200
                )
            }px`;
    }

    function colocarCursorNoFinal(
        elemento
    ) {
        if (
            !elemento
        ) {
            return;
        }

        elemento.focus();

        const selecao =
            window.getSelection();

        const range =
            document.createRange();

        range.selectNodeContents(
            elemento
        );

        range.collapse(
            false
        );

        selecao.removeAllRanges();

        selecao.addRange(
            range
        );
    }

    function selecionarMateria(
        materia
    ) {
        if (
            !celulaMateriaAtual ||
            !materia
        ) {
            return;
        }

        celulaMateriaAtual.textContent =
            materia.nome;

        celulaMateriaAtual
            .dataset
            .materiaId =
            materia.id;

        celulaMateriaAtual
            .dataset
            .materiaNome =
            materia.nome;

        celulaMateriaAtual
            .dataset
            .materiaCor =
            materia.cor;

        esconderMaterias();

        colocarCursorNoFinal(
            celulaMateriaAtual
        );

        agendarSalvamentoHorario();
    }

    function atualizarDestaqueMateria() {
        if (
            !caixaMaterias
        ) {
            return;
        }

        const itens =
            Array.from(
                caixaMaterias
                    .querySelectorAll(
                        '.horario-autocomplete-item'
                    )
            );

        itens.forEach(
            function (
                item,
                indice
            ) {
                item.classList.toggle(
                    'ativo',

                    indice ===
                    indiceMateriaSelecionada
                );
            }
        );
    }

    function mostrarMaterias(
        celula
    ) {
        if (
            !celula
        ) {
            return;
        }

        if (
            celula.cellIndex ===
            0
        ) {
            esconderMaterias();

            return;
        }

        if (
            Number(
                celula.colSpan ||
                1
            ) > 1
        ) {
            esconderMaterias();

            return;
        }

        const textoDigitado =
            String(
                celula.textContent ||
                ''
            ).trim();

        // NÃO mostra nada enquanto está vazio
        if (
            textoDigitado ===
            ''
        ) {
            esconderMaterias();

            return;
        }

        celulaMateriaAtual =
            celula;

        criarCaixaMaterias();

        const sugestoes =
            filtrarMaterias(
                textoDigitado
            );

        caixaMaterias.innerHTML =
            '';

        indiceMateriaSelecionada =
            -1;

        if (
            sugestoes.length ===
            0
        ) {
            esconderMaterias();

            return;
        }

        sugestoes.forEach(
            function (
                materia
            ) {
                const item =
                    document.createElement(
                        'button'
                    );

                item.type =
                    'button';

                item.className =
                    'horario-autocomplete-item';

                const cor =
                    document.createElement(
                        'span'
                    );

                cor.className =
                    'horario-autocomplete-cor';

                cor.style.backgroundColor =
                    materia.cor;

                const nome =
                    document.createElement(
                        'span'
                    );

                nome.className =
                    'horario-autocomplete-nome';

                nome.textContent =
                    materia.nome;

                item.appendChild(
                    cor
                );

                item.appendChild(
                    nome
                );

                item.addEventListener(
                    'mousedown',
                    function (
                        evento
                    ) {
                        evento.preventDefault();

                        selecionarMateria(
                            materia
                        );
                    }
                );

                caixaMaterias.appendChild(
                    item
                );
            }
        );

        posicionarCaixaMaterias(
            celula
        );

        caixaMaterias.style.display =
            'block';
    }

    // =================================================
    // SALVAMENTO AUTOMÁTICO DO HORÁRIO
    // =================================================

    let filaSalvamentoHorario =
        Promise.resolve();

    function sincronizarValoresHorarioNoHtml() {
        if (
            !corpoTabelaHorario
        ) {
            return;
        }

        corpoTabelaHorario
            .querySelectorAll(
                'input[type="time"]'
            )
            .forEach(
                function (
                    input
                ) {
                    input.setAttribute(
                        'value',
                        input.value
                    );
                }
            );
    }

    async function enviarHorarioParaServidor(
        payload
    ) {
        const resposta =
            await fetch(
                HORARIO_SAVE_URL,
                {
                    method:
                        'POST',

                    credentials:
                        'same-origin',

                    cache:
                        'no-store',

                    headers: {
                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json'
                    },

                    body:
                        payload
                }
            );

        const textoResposta =
            await resposta.text();

        let retorno =
            null;

        try {
            retorno =
                JSON.parse(
                    textoResposta
                );
        } catch (_) {
            retorno =
                null;
        }

        if (
            !resposta.ok
        ) {
            throw new Error(
                retorno?.mensagem ||
                retorno?.erro ||
                textoResposta ||
                `Erro HTTP ${resposta.status}`
            );
        }

        if (
            retorno &&
            retorno.ok === false
        ) {
            throw new Error(
                retorno.mensagem ||
                retorno.erro ||
                'Não foi possível salvar o horário.'
            );
        }

        return true;
    }

    function salvarHorarioNoServidor(
        mostrarModal = false
    ) {
        if (
            !corpoTabelaHorario
        ) {
            return Promise.resolve(
                false
            );
        }

        sincronizarValoresHorarioNoHtml();

        atualizarStatusSalvamento(
            'salvando'
        );

        const payload =
            JSON.stringify(
                {
                    html:
                        corpoTabelaHorario
                            .innerHTML
                }
            );

        filaSalvamentoHorario =
            filaSalvamentoHorario.then(
                function () {
                    return enviarHorarioParaServidor(
                        payload
                    );
                },
                function () {
                    return enviarHorarioParaServidor(
                        payload
                    );
                }
            );

        filaSalvamentoHorario =
            filaSalvamentoHorario
                .then(
                    function () {
                        atualizarStatusSalvamento(
                            'salvo'
                        );

                        if (
                            mostrarModal
                        ) {
                            abrirModalSucessoHorario();
                        }

                        return true;
                    }
                )
                .catch(
                    function (
                        erro
                    ) {
                        console.error(
                            'Erro ao salvar horário:',
                            erro
                        );

                        atualizarStatusSalvamento(
                            'erro'
                        );

                        if (
                            mostrarModal
                        ) {
                            alert(
                                `Erro ao salvar o horário: ${erro.message}`
                            );
                        }

                        return false;
                    }
                );

        return filaSalvamentoHorario;
    }

    const agendarSalvamentoHorario =
        debounce(
            function () {
                salvarHorarioNoServidor(
                    false
                );
            },
            1000
        );

    // =================================================
    // EVENTOS DO HORÁRIO
    // =================================================

    criarCaixaMaterias();

    corpoTabelaHorario
        ?.addEventListener(
            'focusin',
            function (
                evento
            ) {
                const celula =
                    evento.target
                        .closest
                        ?.(
                            'td'
                        );

                if (
                    !celula
                ) {
                    return;
                }

                celulaMateriaAtual =
                    celula;

                // Só clicar NÃO mostra sugestões
                esconderMaterias();
            }
        );

    corpoTabelaHorario
        ?.addEventListener(
            'input',
            function (
                evento
            ) {
                const alvo =
                    evento.target;

                if (
                    alvo.matches
                        ?.(
                            'input[type="time"]'
                        )
                ) {
                    agendarSalvamentoHorario();

                    return;
                }

                const celula =
                    alvo.closest
                        ?.(
                            'td'
                        );

                if (
                    !celula
                ) {
                    return;
                }

                if (
                    celula.cellIndex >
                    0
                ) {
                    delete celula
                        .dataset
                        .materiaId;

                    delete celula
                        .dataset
                        .materiaNome;

                    delete celula
                        .dataset
                        .materiaCor;

                    mostrarMaterias(
                        celula
                    );
                }

                agendarSalvamentoHorario();
            }
        );

    corpoTabelaHorario
        ?.addEventListener(
            'change',
            function (
                evento
            ) {
                if (
                    evento.target
                        .matches
                        ?.(
                            'input[type="time"]'
                        )
                ) {
                    sincronizarValoresHorarioNoHtml();

                    agendarSalvamentoHorario();
                }
            }
        );

    corpoTabelaHorario
        ?.addEventListener(
            'keydown',
            function (
                evento
            ) {
                const celula =
                    evento.target
                        .closest
                        ?.(
                            'td'
                        );

                if (
                    !celula ||
                    celula.cellIndex ===
                        0 ||
                    !caixaMaterias ||
                    caixaMaterias
                        .style
                        .display ===
                        'none'
                ) {
                    return;
                }

                const itens =
                    Array.from(
                        caixaMaterias
                            .querySelectorAll(
                                '.horario-autocomplete-item'
                            )
                    );

                if (
                    itens.length ===
                    0
                ) {
                    return;
                }

                if (
                    evento.key ===
                    'ArrowDown'
                ) {
                    evento.preventDefault();

                    indiceMateriaSelecionada++;

                    if (
                        indiceMateriaSelecionada >=
                        itens.length
                    ) {
                        indiceMateriaSelecionada =
                            0;
                    }

                    atualizarDestaqueMateria();

                    return;
                }

                if (
                    evento.key ===
                    'ArrowUp'
                ) {
                    evento.preventDefault();

                    indiceMateriaSelecionada--;

                    if (
                        indiceMateriaSelecionada <
                        0
                    ) {
                        indiceMateriaSelecionada =
                            itens.length -
                            1;
                    }

                    atualizarDestaqueMateria();

                    return;
                }

                if (
                    evento.key ===
                        'Enter' &&
                    indiceMateriaSelecionada >=
                        0
                ) {
                    evento.preventDefault();

                    itens[
                        indiceMateriaSelecionada
                    ].dispatchEvent(
                        new MouseEvent(
                            'mousedown',

                            {
                                bubbles:
                                    true
                            }
                        )
                    );

                    return;
                }

                if (
                    evento.key ===
                    'Escape'
                ) {
                    esconderMaterias();
                }
            }
        );

    corpoTabelaHorario
        ?.addEventListener(
            'focusout',
            function () {
                setTimeout(
                    function () {
                        if (
                            !caixaMaterias
                                ?.matches(
                                    ':hover'
                                )
                        ) {
                            esconderMaterias();
                        }
                    },
                    150
                );
            }
        );

    window.addEventListener(
        'resize',
        function () {
            if (
                celulaMateriaAtual &&
                caixaMaterias &&
                caixaMaterias
                    .style
                    .display !==
                    'none'
            ) {
                posicionarCaixaMaterias(
                    celulaMateriaAtual
                );
            }
        }
    );

    window.addEventListener(
        'scroll',
        function () {
            if (
                celulaMateriaAtual &&
                caixaMaterias &&
                caixaMaterias
                    .style
                    .display !==
                    'none'
            ) {
                posicionarCaixaMaterias(
                    celulaMateriaAtual
                );
            }
        },
        true
    );

    // =================================================
    // MODAL DE SUCESSO DO HORÁRIO
    // =================================================

    const modalSucesso =
        document.getElementById(
            'modal-sucesso'
        );

    const btnFecharModal =
        document.getElementById(
            'fechar-modal'
        );

    function abrirModalSucessoHorario() {
        if (
            !modalSucesso
        ) {
            return;
        }

        modalSucesso.style.display =
            'flex';

        document.body.style.overflow =
            'hidden';
    }

    function fecharModalSucessoHorario() {
        if (
            !modalSucesso
        ) {
            return;
        }

        modalSucesso.style.display =
            'none';

        document.body.style.overflow =
            '';
    }

    window.abrirModalSucesso =
        abrirModalSucessoHorario;

    btnFecharModal
        ?.addEventListener(
            'click',
            fecharModalSucessoHorario
        );

    modalSucesso
        ?.addEventListener(
            'click',
            function (
                evento
            ) {
                if (
                    evento.target ===
                    modalSucesso
                ) {
                    fecharModalSucessoHorario();
                }
            }
        );

    // =================================================
    // BOTÕES DO HORÁRIO
    // =================================================

    window.salvarEdicoes =
        function () {
            esconderMaterias();

            return salvarHorarioNoServidor(
                true
            );
        };

    window.adicionarLinha =
        function () {
            if (
                !corpoTabelaHorario
            ) {
                return;
            }

            const novaLinha =
                corpoTabelaHorario
                    .insertRow();

            for (
                let coluna = 0;
                coluna < 6;
                coluna++
            ) {
                const celula =
                    novaLinha
                        .insertCell();

                if (
                    coluna ===
                    0
                ) {
                    prepararCelulaHorario(
                        celula
                    );

                } else {
                    celula.contentEditable =
                        'true';
                }
            }

            const primeiroInput =
                novaLinha
                    .cells[0]
                    ?.querySelector(
                        '.input-horario-inicio'
                    );

            primeiroInput
                ?.focus();

            agendarSalvamentoHorario();
            renderizarHorarioVisual();
        };

    // Exclusão de uma linha específica do horário.
    const btnExcluirLinhaHorario = document.getElementById('btn-excluir-linha-horario');
    let linhaHorarioSelecionada = null;

    function selecionarLinhaHorario(linha) {
        if (!linha || !corpoTabelaHorario || !corpoTabelaHorario.contains(linha)) return;

        if (linhaHorarioSelecionada && linhaHorarioSelecionada !== linha) {
            linhaHorarioSelecionada.classList.remove('horario-linha-selecionada');
        }

        linhaHorarioSelecionada = linha;
        linhaHorarioSelecionada.classList.add('horario-linha-selecionada');

        if (btnExcluirLinhaHorario) {
            btnExcluirLinhaHorario.classList.remove('horario-btn-desativado');
            btnExcluirLinhaHorario.setAttribute('aria-disabled', 'false');
        }
    }

    if (corpoTabelaHorario) {
        corpoTabelaHorario.addEventListener('click', function (event) {
            const linha = event.target.closest('tr');
            if (linha) selecionarLinhaHorario(linha);
        });
    }

    btnExcluirLinhaHorario?.addEventListener('click', function () {
        const indisponivel = btnExcluirLinhaHorario.classList.contains('horario-btn-desativado');
        if (indisponivel || !linhaHorarioSelecionada || !corpoTabelaHorario?.contains(linhaHorarioSelecionada)) {
            return;
        }

        const ehIntervalo = Array.from(linhaHorarioSelecionada.cells || [])
            .some((celula) => Number(celula.colSpan || 1) > 1);

        const primeiraCelula = linhaHorarioSelecionada.cells?.[0];
        const inicio = primeiraCelula?.querySelector('.input-horario-inicio')?.value || '';
        const fim = primeiraCelula?.querySelector('.input-horario-fim')?.value || '';
        const faixa = inicio && fim ? ` (${inicio} às ${fim})` : '';

        abrirModalExclusao(
            ehIntervalo ? 'Excluir intervalo?' : 'Excluir aula?',
            ehIntervalo
                ? `Tem certeza que deseja excluir este intervalo${faixa}? Essa ação não pode ser desfeita.`
                : `Tem certeza que deseja excluir esta linha de aula${faixa}? Essa ação não pode ser desfeita.`,
            'horario-linha',
            {
                linha: linhaHorarioSelecionada
            }
        );
    });

    window.adicionarIntervalo =
        function () {
            if (
                !corpoTabelaHorario
            ) {
                return;
            }

            const novaLinha =
                corpoTabelaHorario
                    .insertRow();

            const celula =
                novaLinha
                    .insertCell();

            celula.colSpan =
                6;

            celula.contentEditable =
                'true';

            celula.textContent =
                'Intervalo';

            celula.focus();

            agendarSalvamentoHorario();
            renderizarHorarioVisual();
        };

    // =================================================
    // PDF DO HORÁRIO
    // =================================================

    window.salvarComoPDF =
        function () {
            if (
                !tabelaHorario
            ) {
                alert(
                    'A tabela de horário não foi encontrada.'
                );

                return;
            }

            if (
                !window.jspdf ||
                !window.jspdf.jsPDF
            ) {
                alert(
                    'A biblioteca jsPDF não foi carregada.'
                );

                return;
            }

            const {
                jsPDF
            } = window.jspdf;

            const documento =
                new jsPDF(
                    {
                        orientation:
                            'landscape',

                        unit:
                            'mm',

                        format:
                            'a4'
                    }
                );

            if (
                typeof documento
                    .autoTable !==
                'function'
            ) {
                alert(
                    'A biblioteca jsPDF AutoTable não foi carregada.'
                );

                return;
            }

            const cabecalhoTabela =
                tabelaHorario
                    .querySelector(
                        'thead tr'
                    );

            const cabecalhos =
                cabecalhoTabela
                    ? Array.from(
                        cabecalhoTabela
                            .cells
                    ).map(
                        function (
                            celula
                        ) {
                            return celula
                                .textContent
                                .trim();
                        }
                    )
                    : [
                        'Horário',
                        'Segunda-feira',
                        'Terça-feira',
                        'Quarta-feira',
                        'Quinta-feira',
                        'Sexta-feira'
                    ];

            const linhasPdf =
                corpoTabelaHorario
                    ? Array.from(
                        corpoTabelaHorario
                            .rows
                    ).map(
                        function (
                            linha
                        ) {
                            const celulas =
                                Array.from(
                                    linha.cells
                                );

                            if (
                                celulas.length ===
                                    1 &&
                                Number(
                                    celulas[0]
                                        .colSpan
                                ) > 1
                            ) {
                                return [
                                    celulas[0]
                                        .textContent
                                        .trim(),

                                    '',
                                    '',
                                    '',
                                    '',
                                    ''
                                ];
                            }

                            const valores =
                                celulas.map(
                                    function (
                                        celula,
                                        indice
                                    ) {
                                        if (
                                            indice ===
                                            0
                                        ) {
                                            const inicio =
                                                celula
                                                    .querySelector(
                                                        '.input-horario-inicio'
                                                    )
                                                    ?.value ||
                                                '';

                                            const fim =
                                                celula
                                                    .querySelector(
                                                        '.input-horario-fim'
                                                    )
                                                    ?.value ||
                                                '';

                                            if (
                                                inicio ||
                                                fim
                                            ) {
                                                if (
                                                    inicio &&
                                                    fim
                                                ) {
                                                    return `${inicio} às ${fim}`;
                                                }

                                                return (
                                                    inicio ||
                                                    fim
                                                );
                                            }
                                        }

                                        return celula
                                            .textContent
                                            .trim();
                                    }
                                );

                            while (
                                valores.length <
                                cabecalhos.length
                            ) {
                                valores.push(
                                    ''
                                );
                            }

                            return valores;
                        }
                    )
                    : [];

            documento.setFont(
                'helvetica',
                'bold'
            );

            documento.setFontSize(
                22
            );

            documento.setTextColor(
                56,
                165,
                255
            );

            documento.text(
                'FOAG — Horário Escolar',

                14,

                15
            );

            documento.setFont(
                'helvetica',
                'normal'
            );

            documento.setFontSize(
                10
            );

            documento.setTextColor(
                60,
                60,
                60
            );

            documento.text(
                `Gerado em: ${new Date().toLocaleString('pt-BR')}`,

                14,

                22
            );

            documento.autoTable(
                {
                    head:
                        [
                            cabecalhos
                        ],

                    body:
                        linhasPdf,

                    startY:
                        28,

                    theme:
                        'grid',

                    headStyles: {
                        fillColor:
                            [
                                56,
                                165,
                                255
                            ],

                        textColor:
                            [
                                255,
                                255,
                                255
                            ],

                        fontSize:
                            10,

                        fontStyle:
                            'bold',

                        halign:
                            'center'
                    },

                    bodyStyles: {
                        textColor:
                            [
                                30,
                                41,
                                59
                            ],

                        fontSize:
                            9,

                        halign:
                            'center',

                        valign:
                            'middle'
                    },

                    margin: {
                        left:
                            10,

                        right:
                            10
                    }
                }
            );

            documento.save(
                'horario_escolar.pdf'
            );
        };

    // =================================================
    // HEADER
    // =================================================

    const configuracoesIcon =
        document.getElementById(
            'icon-configuracoes'
        );

    const perfilIcon =
        document.getElementById(
            'icon-perfil'
        );

    const logoutModal =
        document.getElementById(
            'logout-modal'
        );

    const iconSair =
        document.getElementById(
            'icon-sair'
        );

    const confirmLogout =
        document.getElementById(
            'confirm-logout'
        );

    const cancelLogout =
        document.getElementById(
            'cancel-logout'
        );

    configuracoesIcon
        ?.addEventListener(
            'click',
            function () {
                window.location.href =
                    FOAG_CONFIG.pages.configuracoes;
            }
        );

    perfilIcon
        ?.addEventListener(
            'click',
            function () {
                window.location.href =
                    FOAG_CONFIG.pages.perfil;
            }
        );

    iconSair
        ?.addEventListener(
            'click',
            function () {
                if (
                    logoutModal
                ) {
                    logoutModal.style.display =
                        'flex';
                }
            }
        );

    cancelLogout
        ?.addEventListener(
            'click',
            function () {
                if (
                    logoutModal
                ) {
                    logoutModal.style.display =
                        'none';
                }
            }
        );

    confirmLogout
        ?.addEventListener(
            'click',
            function () {
                window.location.href =
                    FOAG_CONFIG.pages.logout;
            }
        );

    logoutModal
        ?.addEventListener(
            'click',
            function (
                evento
            ) {
                if (
                    evento.target ===
                    logoutModal
                ) {
                    logoutModal.style.display =
                        'none';
                }
            }
        );

    // =================================================
    // ESC
    // =================================================

    document.addEventListener(
        'keydown',
        function (
            evento
        ) {
            if (
                evento.key !==
                'Escape'
            ) {
                return;
            }

            esconderMaterias();

            if (
                modalNomearNota
                    ?.style
                    .display ===
                'flex'
            ) {
                fecharModalNomearNota();
            }

            if (
                modalExcluir
                    ?.style
                    .display ===
                'flex'
            ) {
                fecharModalExclusao();
            }

            if (
                modalSucesso
                    ?.style
                    .display ===
                'flex'
            ) {
                fecharModalSucessoHorario();
            }

            if (
                logoutModal
                    ?.style
                    .display ===
                'flex'
            ) {
                logoutModal.style.display =
                    'none';
            }
        }
    );

    // =================================================
    // CARREGAMENTO INICIAL
    // =================================================

    carregarDadosAgenda();

    carregarNotas();

    atualizarStatusSalvamento(
        'salvo'
    );

    window._debugAgenda =
        agendaData;

    window._debugMaterias =
        materiasHorario;

    window._debugSalvarAgenda =
        salvarAgendaNoServidor;

    window._debugSalvarHorario =
        salvarHorarioNoServidor;

    console.log(
        'Tudo pronto: Agenda + Horário + Matérias ✅'
    );
});
// =====================================================
// FOAG Agenda — painel do dia + ações rápidas
// =====================================================
document.addEventListener('DOMContentLoaded', function () {
    const novoBtn = document.getElementById('agenda-novo-btn');
    const novoMenu = document.getElementById('agenda-novo-menu');
    const novaAnotacaoBtn = document.getElementById('btn-nova-anotacao');
    const notaEditor = document.getElementById('nota-editor');
    const hojeLista = document.getElementById('agenda-hoje-lista');
    const hojeData = document.getElementById('agenda-hoje-data');
    const resumoPendentes = document.getElementById('resumo-pendentes');
    const resumoHoje = document.getElementById('resumo-hoje');
    const resumoAtrasadas = document.getElementById('resumo-atrasadas');
    const resumoNotas = document.getElementById('resumo-notas');

    const hoje = new Date();
    const isoHoje = [
        hoje.getFullYear(),
        String(hoje.getMonth() + 1).padStart(2, '0'),
        String(hoje.getDate()).padStart(2, '0')
    ].join('-');

    if (hojeData) {
        hojeData.textContent = hoje.toLocaleDateString('pt-BR', {
            weekday: 'long',
            day: '2-digit',
            month: 'long'
        });
    }

    function valorTextoDaLinha(linha) {
        if (!linha) return '';
        const textoTarefa = linha.querySelector('.tarefa-texto');
        if (textoTarefa) return textoTarefa.textContent.trim();
        return (linha.cells?.[1]?.textContent || '').trim();
    }

    function valorDataDaLinha(linha) {
        return linha?.querySelector('input[type="date"]')?.value || '';
    }

    function tarefaConcluida(linha) {
        return Boolean(linha?.querySelector('.tarefa-checkbox')?.checked);
    }

    function renderPainelHoje() {
        const linhasTarefas = Array.from(document.querySelectorAll('#lista-tarefas tr'));
        const linhasLembretes = Array.from(document.querySelectorAll('#lista-nao-esquecer tr'));

        const pendentes = linhasTarefas.filter(l => !tarefaConcluida(l) && valorTextoDaLinha(l));
        const atrasadas = pendentes.filter(l => {
            const data = valorDataDaLinha(l);
            return data && data < isoHoje;
        });
        const tarefasHoje = pendentes.filter(l => valorDataDaLinha(l) === isoHoje);
        const lembretesHoje = linhasLembretes.filter(l => valorDataDaLinha(l) === isoHoje && valorTextoDaLinha(l));

        if (resumoPendentes) resumoPendentes.textContent = pendentes.length;
        if (resumoHoje) resumoHoje.textContent = tarefasHoje.length + lembretesHoje.length;
        if (resumoAtrasadas) resumoAtrasadas.textContent = atrasadas.length;
        if (resumoNotas) resumoNotas.textContent = document.querySelectorAll('#noteList .nota-item').length;

        if (!hojeLista) return;
        hojeLista.innerHTML = '';

        const itens = [
            ...atrasadas.map(l => ({
                tipo: 'atrasada',
                texto: valorTextoDaLinha(l),
                meta: 'Tarefa atrasada',
                icone: 'fa-triangle-exclamation'
            })),
            ...tarefasHoje.map(l => ({
                tipo: 'tarefa',
                texto: valorTextoDaLinha(l),
                meta: 'Tarefa de hoje',
                icone: 'fa-list-check'
            })),
            ...lembretesHoje.map(l => ({
                tipo: 'lembrete',
                texto: valorTextoDaLinha(l),
                meta: 'Lembrete de hoje',
                icone: 'fa-bell'
            }))
        ].slice(0, 6);

        if (!itens.length) {
            hojeLista.innerHTML = '<div class="agenda-vazio">Nada para hoje. Aproveite para adiantar alguma coisa ✨</div>';
            return;
        }

        itens.forEach(item => {
            const el = document.createElement('div');
            el.className = 'agenda-hoje-item';
            el.innerHTML = `
                <span class="agenda-hoje-icon"><i class="fa-solid ${item.icone}"></i></span>
                <div>
                    <strong></strong>
                    <small>${item.meta}</small>
                </div>
            `;
            el.querySelector('strong').textContent = item.texto;
            hojeLista.appendChild(el);
        });
    }

    function abrirEditorNota() {
        if (!notaEditor) return;
        notaEditor.hidden = false;
        document.getElementById('nota-texto')?.focus();
    }

    novoBtn?.addEventListener('click', function (e) {
        e.stopPropagation();
        if (novoMenu) novoMenu.hidden = !novoMenu.hidden;
    });

    document.addEventListener('click', function (e) {
        if (novoMenu && !novoMenu.hidden && !novoMenu.contains(e.target) && e.target !== novoBtn) {
            novoMenu.hidden = true;
        }
    });

    novoMenu?.addEventListener('click', function (e) {
        const botao = e.target.closest('[data-agenda-action]');
        if (!botao) return;
        const acao = botao.dataset.agendaAction;
        novoMenu.hidden = true;

        if (acao === 'tarefa') {
            document.getElementById('tarefas')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('add-tarefa')?.click();
        } else if (acao === 'lembrete') {
            document.getElementById('lembretes')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('add-nao-esquecer')?.click();
        } else if (acao === 'anotacao') {
            document.getElementById('notas')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            abrirEditorNota();
        }
    });

    novaAnotacaoBtn?.addEventListener('click', abrirEditorNota);

    document.getElementById('noteList')?.addEventListener('click', function (e) {
        if (e.target.closest('.btn-editar')) abrirEditorNota();
    });

    const observar = new MutationObserver(renderPainelHoje);
    ['lista-tarefas', 'lista-nao-esquecer', 'noteList'].forEach(id => {
        const el = document.getElementById(id);
        if (el) observar.observe(el, { childList: true, subtree: true, characterData: true });
    });

    document.addEventListener('change', function (e) {
        if (e.target.matches('#lista-tarefas input, #lista-nao-esquecer input')) renderPainelHoje();
    });
    document.addEventListener('input', function (e) {
        if (e.target.closest('#lista-tarefas, #lista-nao-esquecer')) renderPainelHoje();
    });

    setTimeout(renderPainelHoje, 0);
});
