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
        window.AGENDA_SAVE_URL || 'salvar_agenda.php';

    const HORARIO_SAVE_URL =
        window.HORARIO_SAVE_URL || 'salvar_agenda.php';

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
        return String(
            valor ?? ''
        )
            .replaceAll(
                '&',
                '&amp;'
            )
            .replaceAll(
                '<',
                '&lt;'
            )
            .replaceAll(
                '>',
                '&gt;'
            )
            .replaceAll(
                '"',
                '&quot;'
            )
            .replaceAll(
                "'",
                '&#039;'
            );
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

    function ordenarLinhasTarefas() {
        if (
            !listaTarefas
        ) {
            return;
        }

        const linhas =
            Array.from(
                listaTarefas.rows
            );

        linhas.sort(
            function (
                linhaA,
                linhaB
            ) {
                return compararTarefas(
                    dadosDaLinha(
                        linhaA
                    ),

                    dadosDaLinha(
                        linhaB
                    )
                );
            }
        );

        linhas.forEach(
            function (linha) {
                listaTarefas
                    .appendChild(
                        linha
                    );

                aplicarEstadoTarefa(
                    linha
                );
            }
        );

        atualizarIndices(
            listaTarefas
        );
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

        // Data
        const celulaData =
            linha.insertCell(
                2
            );

        const inputData =
            document.createElement(
                'input'
            );

        inputData.type =
            'date';

        inputData.value =
            String(
                dadosIniciais.data ??
                dadosIniciais.date ??
                ''
            );

        celulaData.appendChild(
            inputData
        );

        if (
            ehTarefa
        ) {
            inputData.addEventListener(
                'change',
                function () {
                    aplicarEstadoTarefa(
                        linha
                    );

                    ordenarLinhasTarefas();

                    salvarDadosAgenda();
                }
            );
        }

        // Ações
        const celulaAcoes =
            linha.insertCell(
                3
            );

        const botaoExcluir =
            document.createElement(
                'button'
            );

        botaoExcluir.type =
            'button';

        botaoExcluir.textContent =
            'Excluir';

        botaoExcluir.className =
            'btn-excluir';

        botaoExcluir.addEventListener(
            'click',
            function () {
                const texto =
                    textoDaLinha(
                        linha
                    ) ||
                    'Item sem título';

                const tipo =
                    ehTarefa
                        ? 'tarefa'
                        : 'nao-esquecer';

                const titulo =
                    ehTarefa
                        ? 'Excluir Tarefa'
                        : 'Excluir Lembrete';

                const resumo =
                    texto.length >
                    50
                        ? `${texto.substring(
                            0,
                            50
                        )}...`
                        : texto;

                abrirModalExclusao(
                    titulo,

                    `Tem certeza que deseja excluir "${resumo}"?`,

                    tipo,

                    {
                        linha:
                            linha
                    }
                );
            }
        );

        celulaAcoes.appendChild(
            botaoExcluir
        );

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
        }

        fecharModalExclusao();
    }

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
        };

    window.removerLinha =
        function () {
            if (
                !corpoTabelaHorario
            ) {
                return;
            }

            const quantidadeLinhas =
                corpoTabelaHorario
                    .rows
                    .length;

            if (
                quantidadeLinhas ===
                0
            ) {
                alert(
                    'Não existem linhas para remover.'
                );

                return;
            }

            corpoTabelaHorario
                .deleteRow(
                    quantidadeLinhas -
                        1
                );

            esconderMaterias();

            agendarSalvamentoHorario();
        };

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
                    '../configuracoes/configuracoes.php';
            }
        );

    perfilIcon
        ?.addEventListener(
            'click',
            function () {
                window.location.href =
                    '../perfil/perfil.php';
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
                    '../login/logout.php';
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
