// =====================================================
// FOAG — CALENDÁRIO
// Calendário + Agenda + Horário + Frequência anual
// =====================================================

document.addEventListener('DOMContentLoaded', () => {

    // =====================================================
    // DADOS VINDOS DO PHP
    // =====================================================

    const agendaData =
        window.CAL_AGENDA_DATA &&
        typeof window.CAL_AGENDA_DATA === 'object'
            ? window.CAL_AGENDA_DATA
            : {
                notas: [],
                tarefas: [],
                nao_esquecer: []
            };

    if (!Array.isArray(agendaData.notas)) {
        agendaData.notas = [];
    }

    if (!Array.isArray(agendaData.tarefas)) {
        agendaData.tarefas = [];
    }

    if (!Array.isArray(agendaData.nao_esquecer)) {
        agendaData.nao_esquecer = [];
    }


    const rawCalendData =
        window.CAL_CALEND_DATA &&
        typeof window.CAL_CALEND_DATA === 'object'
            ? window.CAL_CALEND_DATA
            : {};


    const HORARIO_HTML =
        typeof window.CAL_HORARIO_HTML === 'string'
            ? window.CAL_HORARIO_HTML
            : '';


    const CALEND_SAVE_URL =
        window.CAL_CALEND_SAVE_URL ||
        FOAG_CONFIG.endpoints.calendarioSalvar;


    const ANO_ATUAL =
        Number(
            window.CAL_ANO ||
            new Date().getFullYear()
        );


    const NOMES_MESES = [
        'Janeiro',
        'Fevereiro',
        'Março',
        'Abril',
        'Maio',
        'Junho',
        'Julho',
        'Agosto',
        'Setembro',
        'Outubro',
        'Novembro',
        'Dezembro'
    ];


    // =====================================================
    // OBJETO SEGURO
    // =====================================================

    function objeto(valor) {

        return (
            valor &&
            typeof valor === 'object' &&
            !Array.isArray(valor)
        )
            ? valor
            : {};
    }


    const calendData = {

        dias:
            objeto(
                rawCalendData.dias
            ),

        metas:
            objeto(
                rawCalendData.metas
            ),

        configuracoes:
            objeto(
                rawCalendData.configuracoes
            )
    };


    // =====================================================
    // UTILIDADES
    // =====================================================

    function clamp(
        numero,
        minimo,
        maximo
    ) {

        return Math.max(
            minimo,
            Math.min(
                maximo,
                numero
            )
        );
    }


    function hojeIso() {

        const agora =
            new Date();

        return [
            agora.getFullYear(),

            String(
                agora.getMonth() + 1
            ).padStart(
                2,
                '0'
            ),

            String(
                agora.getDate()
            ).padStart(
                2,
                '0'
            )

        ].join('-');
    }


    function dataIsoValida(valor) {

        return (
            typeof valor === 'string' &&
            /^\d{4}-\d{2}-\d{2}$/.test(
                valor
            )
        );
    }


    function diaSemanaIso(iso) {

        return new Date(
            `${iso}T00:00:00`
        ).getDay();
    }


    function ehFimDeSemana(iso) {

        const dia =
            diaSemanaIso(
                iso
            );

        return (
            dia === 0 ||
            dia === 6
        );
    }


    function isoEntre(
        iso,
        inicio,
        fim
    ) {

        return (
            dataIsoValida(
                inicio
            ) &&

            dataIsoValida(
                fim
            ) &&

            iso >= inicio &&
            iso <= fim
        );
    }


    function escapeHtml(valor) {
        return window.FOAG?.utils?.escapeHtml
            ? FOAG.utils.escapeHtml(valor)
            : String(valor ?? '');
    }


    function formataDataBR(iso) {

        if (
            !dataIsoValida(
                iso
            )
        ) {
            return '';
        }

        const [
            ano,
            mes,
            dia
        ] =
            iso.split('-');

        return (
            `${dia}/` +
            `${mes}/` +
            `${ano}`
        );
    }


    // =====================================================
    // COMPATIBILIDADE COM AGENDA
    // =====================================================

    function obterDataItem(item) {

        if (
            !item ||
            typeof item !== 'object'
        ) {
            return '';
        }

        const possiveis = [
            item.data,
            item.date,
            item.data_tarefa,
            item.dataTarefa,
            item.prazo
        ];


        return (
            possiveis.find(
                dataIsoValida
            ) ||
            ''
        );
    }


    function obterTextoItem(item) {

        if (
            !item ||
            typeof item !== 'object'
        ) {
            return '';
        }

        return String(

            item.texto ??

            item.titulo ??

            item.nome ??

            item.descricao ??

            item.tarefa ??

            ''

        ).trim();
    }


    function obterMateriaItem(item) {

        if (
            !item ||
            typeof item !== 'object'
        ) {
            return '';
        }

        return String(

            item.materia_nome ??

            item.materia ??

            item.nome_materia ??

            ''

        ).trim();
    }


    function tarefaConcluida(item) {

        return Boolean(

            item && (

                item.concluida === true ||

                item.concluido === true ||

                item.feita === true ||

                item.status ===
                    'concluida' ||

                item.status ===
                    'concluido'
            )
        );
    }


    // =====================================================
    // SALVAR CALENDÁRIO
    // =====================================================

    async function salvarCalendarioServidor() {

        try {

            const resposta =
                await fetch(
                    CALEND_SAVE_URL,
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
                            JSON.stringify(
                                calendData
                            )
                    }
                );


            const texto =
                await resposta.text();


            let retorno =
                null;


            try {

                retorno =
                    JSON.parse(
                        texto
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

                    texto ||

                    `HTTP ${resposta.status}`
                );
            }


            if (
                retorno?.ok ===
                    false ||

                retorno?.sucesso ===
                    false
            ) {

                throw new Error(

                    retorno?.mensagem ||

                    'Erro ao salvar calendário.'
                );
            }


            // ==========================================
            // DEBUG DOS PONTOS
            // ==========================================

            if (
                retorno?.pontos
            ) {

                console.log(
                    'Resultado dos pontos:',
                    retorno.pontos
                );
            }


            // ==========================================
            // MODAL DE RECOMPENSA
            // ==========================================

            if (
                retorno?.pontos
                    ?.recompensa_concedida === true
            ) {

                const estrelas =
                    Number(
                        retorno.pontos.estrelas ||
                        0
                    );


                const marco =
                    Number(
                        retorno.pontos.marco ||
                        0
                    );


                if (
                    estrelas > 0 &&
                    typeof window.mostrarModalEstrelas ===
                        'function'
                ) {

                    window.mostrarModalEstrelas(

                        estrelas,

                        marco > 0

                            ? `${marco} dias seguidos de presença! Continue assim! :)`

                            : 'Continue assim! :)'
                    );

                } else if (
                    estrelas > 0
                ) {

                    console.warn(
                        'As estrelas foram recebidas, mas mostrarModalEstrelas() não foi carregada.'
                    );
                }
            }


            return true;

        } catch (erro) {

            console.error(
                'Erro ao salvar calendário:',
                erro
            );

            return false;
        }
    }


    // =====================================================
    // AGENDA
    // =====================================================

    function tarefasDoDia(iso) {

        return agendaData.tarefas.filter(
            item => {

                return (

                    obterDataItem(
                        item
                    ) === iso &&

                    obterTextoItem(
                        item
                    ) !== ''
                );
            }
        );
    }


    function lembretesDoDia(iso) {

        return agendaData.nao_esquecer.filter(
            item => {

                return (

                    obterDataItem(
                        item
                    ) === iso &&

                    obterTextoItem(
                        item
                    ) !== ''
                );
            }
        );
    }


    function temItemAgendaNoDia(iso) {

        return (

            tarefasDoDia(
                iso
            ).length > 0 ||

            lembretesDoDia(
                iso
            ).length > 0
        );
    }


    function marcarDiasComTarefa() {

        document
            .querySelectorAll(
                '.calendario .dia[data-date]'
            )
            .forEach(
                dia => {

                    const iso =
                        dia.dataset.date;


                    dia.classList.toggle(

                        'has-tarefa',

                        temItemAgendaNoDia(
                            iso
                        )
                    );
                }
            );
    }


    // =====================================================
    // SALVAR TAREFA DO CALENDÁRIO
    // =====================================================

    async function salvarTextoDoDiaNaAgenda(
        iso,
        texto
    ) {

        const textoLimpo =
            String(
                texto || ''
            ).trim();


        try {

            const resposta =
                await fetch(

                    FOAG_CONFIG.endpoints.calendarioTarefaSalvar,

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
                            JSON.stringify({

                                data:
                                    iso,

                                texto:
                                    textoLimpo
                            })
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

                    textoResposta ||

                    `HTTP ${resposta.status}`
                );
            }


            if (
                !retorno ||
                retorno.ok !== true
            ) {

                throw new Error(

                    retorno?.mensagem ||

                    'Não foi possível salvar a tarefa.'
                );
            }


            if (
                Array.isArray(
                    retorno.tarefas
                )
            ) {

                agendaData.tarefas =
                    retorno.tarefas;
            }


            marcarDiasComTarefa();


            const dia =
                document.querySelector(

                    `.calendario .dia[data-date="${iso}"]`

                );


            if (dia) {

                atualizarDots(
                    dia
                );
            }


            return true;

        } catch (erro) {

            console.error(

                'Erro ao salvar tarefa do calendário:',

                erro
            );


            alert(
                'Não foi possível salvar a tarefa na Agenda.'
            );


            return false;
        }
    }


    // =====================================================
    // HORÁRIO SEMANAL
    // =====================================================

    function buscarHorarios(iso) {

        if (
            !HORARIO_HTML ||
            HORARIO_HTML.trim() === ''
        ) {

            console.warn(
                'CAL_HORARIO_HTML está vazio.'
            );

            return [];
        }


        const diaSemana =
            diaSemanaIso(
                iso
            );


        if (
            diaSemana === 0 ||
            diaSemana === 6
        ) {

            return [];
        }


        /*
         * TABELA:
         * 0 = horário
         * 1 = segunda
         * 2 = terça
         * 3 = quarta
         * 4 = quinta
         * 5 = sexta
         */

        const colunaDia =
            diaSemana;


        const tabela =
            document.createElement(
                'table'
            );


        const tbody =
            document.createElement(
                'tbody'
            );


        tabela.appendChild(
            tbody
        );


        tbody.innerHTML =
            HORARIO_HTML;


        const horarios =
            [];


        tbody
            .querySelectorAll(
                'tr'
            )
            .forEach(
                linha => {

                    const celulas =
                        Array.from(
                            linha.children
                        )
                        .filter(
                            elemento =>
                                elemento.tagName ===
                                'TD'
                        );


                    if (
                        !celulas.length
                    ) {

                        return;
                    }


                    // =====================================
                    // IGNORAR INTERVALO
                    // =====================================

                    if (
                        celulas.length ===
                            1 &&

                        Number(

                            celulas[0]
                                .getAttribute(
                                    'colspan'
                                ) ||

                            celulas[0]
                                .colSpan ||

                            1

                        ) > 1
                    ) {

                        return;
                    }


                    if (
                        celulas.length <=
                        colunaDia
                    ) {

                        return;
                    }


                    // =====================================
                    // MATÉRIA
                    // =====================================

                    const celulaMateria =
                        celulas[
                            colunaDia
                        ];


                    const materia =
                        String(

                            celulaMateria
                                .dataset
                                .materiaNome ||

                            celulaMateria
                                .getAttribute(
                                    'data-materia-nome'
                                ) ||

                            celulaMateria
                                .textContent ||

                            ''

                        ).trim();


                    if (
                        !materia
                    ) {

                        return;
                    }


                    // =====================================
                    // HORÁRIO
                    // =====================================

                    const celulaHorario =
                        celulas[0];


                    const inputsTempo =
                        celulaHorario
                            .querySelectorAll(
                                'input[type="time"]'
                            );


                    const inputInicio =

                        celulaHorario
                            .querySelector(
                                '.input-horario-inicio'
                            ) ||

                        inputsTempo[0] ||

                        null;


                    const inputFim =

                        celulaHorario
                            .querySelector(
                                '.input-horario-fim'
                            ) ||

                        inputsTempo[1] ||

                        null;


                    let inicio =
                        String(

                            inputInicio
                                ?.getAttribute(
                                    'value'
                                ) ||

                            inputInicio
                                ?.value ||

                            ''

                        ).trim();


                    let fim =
                        String(

                            inputFim
                                ?.getAttribute(
                                    'value'
                                ) ||

                            inputFim
                                ?.value ||

                            ''

                        ).trim();


                    if (
                        !inicio &&
                        !fim
                    ) {

                        const encontrados =
                            String(

                                celulaHorario
                                    .textContent ||

                                ''

                            ).match(

                                /\b(?:[01]\d|2[0-3]):[0-5]\d\b/g

                            ) || [];


                        inicio =
                            encontrados[0] ||
                            '';


                        fim =
                            encontrados[1] ||
                            '';
                    }


                    let horario =
                        '';


                    if (
                        inicio &&
                        fim
                    ) {

                        horario =
                            `${inicio} às ${fim}`;

                    } else {

                        horario =
                            inicio ||
                            fim ||
                            '';
                    }


                    horarios.push({

                        materia:
                            materia,

                        inicio:
                            inicio,

                        fim:
                            fim,

                        horario:
                            horario
                    });
                }
            );


        console.log(

            `Horários de ${iso}:`,

            horarios
        );


        return horarios;
    }


    // =====================================================
    // CONFIGURAÇÃO ANUAL
    // =====================================================

    function obterConfigAno(
        ano = ANO_ATUAL
    ) {

        const chave =
            String(
                ano
            );


        if (
            !calendData.configuracoes ||
            typeof calendData.configuracoes !==
                'object' ||

            Array.isArray(
                calendData.configuracoes
            )
        ) {

            calendData.configuracoes =
                {};
        }


        if (
            !calendData.configuracoes[
                chave
            ] ||

            typeof calendData
                .configuracoes[
                    chave
                ] !==
                'object' ||

            Array.isArray(
                calendData
                    .configuracoes[
                        chave
                    ]
            )
        ) {

            calendData.configuracoes[
                chave
            ] = {};
        }


        const config =
            calendData
                .configuracoes[
                    chave
                ];


        if (
            !Number.isFinite(
                Number(
                    config.meta_anual
                )
            )
        ) {

            config.meta_anual =
                80;
        }


        config.inicio_ano_letivo =

            dataIsoValida(
                config.inicio_ano_letivo
            )

                ? config.inicio_ano_letivo

                : '';


        config.fim_ano_letivo =

            dataIsoValida(
                config.fim_ano_letivo
            )

                ? config.fim_ano_letivo

                : '';


        config.inicio_ferias_meio =

            dataIsoValida(
                config.inicio_ferias_meio
            )

                ? config.inicio_ferias_meio

                : '';


        config.fim_ferias_meio =

            dataIsoValida(
                config.fim_ferias_meio
            )

                ? config.fim_ferias_meio

                : '';


        return config;
    }


    function periodoLetivoPermite(
        iso,
        config
    ) {

        if (
            config.inicio_ano_letivo &&
            iso <
                config.inicio_ano_letivo
        ) {

            return false;
        }


        if (
            config.fim_ano_letivo &&
            iso >
                config.fim_ano_letivo
        ) {

            return false;
        }


        if (
            isoEntre(

                iso,

                config.inicio_ferias_meio,

                config.fim_ferias_meio
            )
        ) {

            return false;
        }


        return true;
    }


    function validarConfigAno(
        config
    ) {

        if (
            config.inicio_ano_letivo &&

            config.fim_ano_letivo &&

            config.inicio_ano_letivo >
                config.fim_ano_letivo
        ) {

            return (
                'O início do ano letivo não pode ser depois do final.'
            );
        }


        if (
            config.inicio_ferias_meio &&

            config.fim_ferias_meio &&

            config.inicio_ferias_meio >
                config.fim_ferias_meio
        ) {

            return (
                'O início das férias não pode ser depois do final.'
            );
        }


        return '';
    }


    const inputMetaAnual =
        document.getElementById(
            'meta-anual'
        );


    const inputInicioAno =
        document.getElementById(
            'inicio-ano-letivo'
        );


    const inputFimAno =
        document.getElementById(
            'fim-ano-letivo'
        );


    const inputInicioFerias =
        document.getElementById(
            'inicio-ferias-meio'
        );


    const inputFimFerias =
        document.getElementById(
            'fim-ferias-meio'
        );


    const erroConfig =
        document.getElementById(
            'freq-config-erro'
        );


    function preencherConfigAno() {

        const config =
            obterConfigAno();


        if (
            inputMetaAnual
        ) {

            inputMetaAnual.value =
                String(

                    clamp(

                        Number(
                            config.meta_anual
                        ),

                        0,

                        100
                    )
                );
        }


        if (
            inputInicioAno
        ) {

            inputInicioAno.value =
                config.inicio_ano_letivo;
        }


        if (
            inputFimAno
        ) {

            inputFimAno.value =
                config.fim_ano_letivo;
        }


        if (
            inputInicioFerias
        ) {

            inputInicioFerias.value =
                config.inicio_ferias_meio;
        }


        if (
            inputFimFerias
        ) {

            inputFimFerias.value =
                config.fim_ferias_meio;
        }
    }


    async function atualizarConfigAno() {

        const config =
            obterConfigAno();


        config.meta_anual =
            clamp(

                Number(
                    inputMetaAnual
                        ?.value ||
                    80
                ),

                0,

                100
            );


        config.inicio_ano_letivo =
            inputInicioAno
                ?.value ||
            '';


        config.fim_ano_letivo =
            inputFimAno
                ?.value ||
            '';


        config.inicio_ferias_meio =
            inputInicioFerias
                ?.value ||
            '';


        config.fim_ferias_meio =
            inputFimFerias
                ?.value ||
            '';


        const erro =
            validarConfigAno(
                config
            );


        if (
            erroConfig
        ) {

            erroConfig.textContent =
                erro;
        }


        if (
            erro
        ) {

            return;
        }


        const salvou =
            await salvarCalendarioServidor();


        if (
            !salvou
        ) {

            alert(
                'Não foi possível salvar as configurações do calendário.'
            );

            return;
        }


        atualizarTudo();
    }


    [
        inputMetaAnual,
        inputInicioAno,
        inputFimAno,
        inputInicioFerias,
        inputFimFerias

    ].forEach(
        input => {

            input?.addEventListener(
                'change',
                atualizarConfigAno
            );
        }
    );


    // =====================================================
    // STATUS DO DIA
    // =====================================================

    function statusDia(dia) {

        if (
            dia.classList.contains(
                'vermelho'
            )
        ) {

            return 'vermelho';
        }


        if (
            dia.classList.contains(
                'amarelo'
            )
        ) {

            return 'amarelo';
        }


        if (
            dia.classList.contains(
                'sem-aula'
            )
        ) {

            return 'sem-aula';
        }


        if (
            dia.classList.contains(
                'roxo'
            )
        ) {

            return 'roxo';
        }


        return null;
    }


    // =====================================================
    // DIA LETIVO
    // =====================================================

    function diaContaComoLetivo(
        dia,
        config,
        incluirFuturo = false
    ) {

        const iso =
            dia.dataset.date;


        if (
            !iso
        ) {

            return false;
        }


        if (

            ehFimDeSemana(
                iso
            ) ||

            dia.classList.contains(
                'feriado'
            ) ||

            dia.classList.contains(
                'sem-aula'
            ) ||

            !periodoLetivoPermite(
                iso,
                config
            )
        ) {

            return false;
        }


        if (
            !incluirFuturo &&
            iso > hojeIso()
        ) {

            return false;
        }


        return true;
    }


    // =====================================================
    // PRESENÇA AUTOMÁTICA
    // =====================================================

    function ehPresencaAutomatica(
        dia
    ) {

        const mes =
            dia.closest(
                '.mes'
            );


        if (
            !mes
        ) {

            return false;
        }


        const config =
            obterConfigAno(

                Number(
                    mes.dataset.ano
                )
            );


        if (
            !diaContaComoLetivo(

                dia,

                config,

                false
            )
        ) {

            return false;
        }


        return (

            !dia.classList.contains(
                'vermelho'
            ) &&

            !dia.classList.contains(
                'amarelo'
            )
        );
    }


    // =====================================================
    // MARCAÇÕES DO PERÍODO LETIVO
    // =====================================================

    function aplicarMarcacoesPeriodo() {

        const config =
            obterConfigAno();


        document
            .querySelectorAll(
                '.calendario .dia[data-date]'
            )
            .forEach(
                dia => {

                    const iso =
                        dia.dataset.date;


                    dia.classList.remove(

                        'ferias-meio-ano',

                        'fora-periodo-letivo'
                    );


                    dia
                        .querySelectorAll(
                            '.periodo-badge'
                        )
                        .forEach(
                            badge =>
                                badge.remove()
                        );


                    if (
                        config.inicio_ano_letivo &&
                        iso <
                            config.inicio_ano_letivo
                    ) {

                        dia.classList.add(
                            'fora-periodo-letivo'
                        );
                    }


                    if (
                        config.fim_ano_letivo &&
                        iso >
                            config.fim_ano_letivo
                    ) {

                        dia.classList.add(
                            'fora-periodo-letivo'
                        );
                    }


                    if (
                        isoEntre(

                            iso,

                            config.inicio_ferias_meio,

                            config.fim_ferias_meio
                        )
                    ) {

                        dia.classList.add(
                            'ferias-meio-ano'
                        );
                    }


                    const marcacoes = [

                        [
                            config.inicio_ano_letivo,
                            'Início'
                        ],

                        [
                            config.fim_ano_letivo,
                            'Fim'
                        ],

                        [
                            config.inicio_ferias_meio,
                            'Férias'
                        ],

                        [
                            config.fim_ferias_meio,
                            'Fim férias'
                        ]
                    ];


                    marcacoes.forEach(

                        ([
                            data,
                            texto
                        ]) => {

                            if (
                                data &&
                                data === iso
                            ) {

                                const badge =
                                    document.createElement(
                                        'span'
                                    );


                                badge.className =
                                    'periodo-badge';


                                badge.textContent =
                                    texto;


                                dia.appendChild(
                                    badge
                                );
                            }
                        }
                    );
                }
            );
    }


    // =====================================================
    // DOTS
    // =====================================================

    function criaDot(tipo) {

        const dot =
            document.createElement(
                'span'
            );


        dot.className =
            `dot ${tipo}`;


        return dot;
    }


    function atualizarDots(dia) {

        const dots =
            dia.querySelector(
                '.dots'
            );


        if (
            !dots
        ) {

            return;
        }


        dots.innerHTML =
            '';


        dia.classList.remove(
            'presenca-automatica'
        );


        if (
            ehPresencaAutomatica(
                dia
            )
        ) {

            dots.appendChild(
                criaDot(
                    'presenca'
                )
            );


            dia.classList.add(
                'presenca-automatica'
            );
        }


        if (
            dia.classList.contains(
                'vermelho'
            )
        ) {

            dots.appendChild(
                criaDot(
                    'vermelho'
                )
            );
        }


        if (
            dia.classList.contains(
                'amarelo'
            )
        ) {

            dots.appendChild(
                criaDot(
                    'amarelo'
                )
            );
        }


        if (
            dia.classList.contains(
                'sem-aula'
            )
        ) {

            dots.appendChild(
                criaDot(
                    'semaula'
                )
            );
        }


        if (
            dia.classList.contains(
                'roxo'
            )
        ) {

            dots.appendChild(
                criaDot(
                    'roxo'
                )
            );
        }


        if (
            dia.classList.contains(
                'has-tarefa'
            )
        ) {

            dots.appendChild(
                criaDot(
                    'tarefa'
                )
            );
        }
    }


    // =====================================================
    // HOJE
    // =====================================================

    function destacarHoje() {

        const hoje =
            hojeIso();


        document
            .querySelectorAll(
                '.calendario .dia[data-date]'
            )
            .forEach(
                dia => {

                    const ehHoje =
                        dia.dataset.date ===
                        hoje;


                    dia.classList.toggle(

                        'dia-hoje',

                        ehHoje
                    );


                    const badgeAtual =
                        dia.querySelector(
                            '.hoje-badge'
                        );


                    if (
                        ehHoje &&
                        !badgeAtual
                    ) {

                        const badge =
                            document.createElement(
                                'span'
                            );


                        badge.className =
                            'hoje-badge';


                        badge.textContent =
                            'Hoje';


                        dia.appendChild(
                            badge
                        );
                    }


                    if (
                        !ehHoje &&
                        badgeAtual
                    ) {

                        badgeAtual.remove();
                    }
                }
            );
    }


    // =====================================================
    // DIAS RESTANTES
    // =====================================================

    function diasRestantesPeriodo(
        dias,
        config
    ) {

        const hoje =
            hojeIso();


        return dias.filter(
            dia => {

                const iso =
                    dia.dataset.date;


                return (

                    iso &&

                    iso > hoje &&

                    diaContaComoLetivo(

                        dia,

                        config,

                        true
                    )
                );
            }
        ).length;
    }


    // =====================================================
    // CÁLCULO DE FALTAS
    // =====================================================

    function calcularFaltasPossiveis(
        presencas,
        totalAtual,
        diasRestantes,
        frequenciaMinima
    ) {

        const minimo =
            clamp(

                Number(
                    frequenciaMinima
                ),

                0,

                100

            ) / 100;


        if (
            minimo <= 0
        ) {

            return null;
        }


        const totalFinal =
            totalAtual +
            diasRestantes;


        const presencasFinais =
            presencas +
            diasRestantes;


        const maxFaltas =
            Math.floor(

                presencasFinais -

                minimo *
                    totalFinal +

                0.000000001
            );


        return Math.max(
            0,
            maxFaltas
        );
    }


    // =====================================================
    // MÉTRICAS DO MÊS
    // =====================================================

    function calcularMetricasMesDados(
        mes
    ) {

        const config =
            obterConfigAno(

                Number(
                    mes.dataset.ano
                )
            );


        const dias =
            [
                ...mes.querySelectorAll(
                    '.dia[data-date]'
                )
            ];


        let presencas =
            0;

        let faltas =
            0;

        let atestados =
            0;

        let semAula =
            0;

        let provas =
            0;

        let totalDiasLetivos =
            0;


        dias.forEach(
            dia => {

                if (
                    dia.classList.contains(
                        'sem-aula'
                    )
                ) {

                    semAula++;
                }


                if (
                    dia.classList.contains(
                        'roxo'
                    )
                ) {

                    provas++;
                }


                if (
                    !diaContaComoLetivo(

                        dia,

                        config,

                        false
                    )
                ) {

                    return;
                }


                totalDiasLetivos++;


                if (
                    dia.classList.contains(
                        'vermelho'
                    )
                ) {

                    faltas++;

                    return;
                }


                if (
                    dia.classList.contains(
                        'amarelo'
                    )
                ) {

                    atestados++;

                    return;
                }


                presencas++;
            }
        );


        const percentual =

            totalDiasLetivos > 0

                ? Math.round(

                    (
                        presencas /
                        totalDiasLetivos
                    ) *

                    100
                )

                : 0;


        return {

            presencas,

            faltas,

            atestados,

            semAula,

            provas,

            totalDiasLetivos,

            percentual,

            diasRestantes:
                diasRestantesPeriodo(
                    dias,
                    config
                )
        };
    }


    // =====================================================
    // TEXTO DA FREQUÊNCIA
    // =====================================================

    function textoDiferencaFrequencia(
        atual,
        frequenciaMinima
    ) {

        const diferenca =
            Math.round(

                (
                    atual -
                    frequenciaMinima
                ) *

                10

            ) / 10;


        if (
            diferenca > 0
        ) {

            return (

                `Você está ${diferenca} pontos percentuais acima da frequência mínima exigida.`

            );
        }


        if (
            diferenca < 0
        ) {

            return (

                `Você está ${Math.abs(
                    diferenca
                )} pontos percentuais abaixo da frequência mínima exigida.`

            );
        }


        return (
            'Você está exatamente na frequência mínima exigida.'
        );
    }


    // =====================================================
    // MÉTRICAS MENSAIS
    // =====================================================

    function recalcularMetricasDoMes(
        mes
    ) {

        const dados =
            calcularMetricasMesDados(
                mes
            );


        const inputFrequencia =
            mes.querySelector(
                '.meta-presenca'
            );


        const frequenciaMinima =
            clamp(

                Number(
                    inputFrequencia
                        ?.value ||
                    80
                ),

                0,

                100
            );


        const valores = {

            '.count-presenca':
                dados.presencas,

            '.count-falta':
                dados.faltas,

            '.count-atestado':
                dados.atestados,

            '.count-semaula':
                dados.semAula,

            '.count-prova':
                dados.provas
        };


        Object.entries(
            valores
        )
        .forEach(
            ([
                seletor,
                valor
            ]) => {

                const elemento =
                    mes.querySelector(
                        seletor
                    );


                if (
                    elemento
                ) {

                    elemento.textContent =
                        String(
                            valor
                        );
                }
            }
        );


        const barra =
            mes.querySelector(
                '.progress-bar'
            );


        if (
            barra
        ) {

            barra.style.width =

                `${Math.min(
                    100,
                    dados.percentual
                )}%`;
        }


        const label =
            mes.querySelector(
                '.label-presenca'
            );


        if (
            label
        ) {

            label.textContent =
                `${dados.percentual}%`;
        }


        const status =
            mes.querySelector(
                '.meta-status-mes'
            );


        if (
            status
        ) {

            status.textContent =

                `Mínimo exigido: ${frequenciaMinima}% · ` +

                `Frequência atual: ${dados.percentual}% · ` +

                textoDiferencaFrequencia(

                    dados.percentual,

                    frequenciaMinima
                );
        }


        const faltasRestantes =
            mes.querySelector(
                '.faltas-restantes-mes'
            );


        if (
            faltasRestantes
        ) {

            const quantidade =
                calcularFaltasPossiveis(

                    dados.presencas,

                    dados.totalDiasLetivos,

                    dados.diasRestantes,

                    frequenciaMinima
                );


            faltasRestantes.textContent =

                quantidade === null

                    ? (
                        'Com frequência mínima de 0%, não há limite calculado de faltas.'
                    )

                    : (
                        `Você ainda pode ter até ${quantidade} ` +

                        `${quantidade === 1
                            ? 'falta'
                            : 'faltas'
                        } ` +

                        `sem ficar abaixo da frequência mínima de ${frequenciaMinima}%.`
                    );
        }


        return dados;
    }


    // =====================================================
    // CLASSIFICAÇÃO
    // =====================================================

    function classificacaoRisco(
        percentual
    ) {

        if (
            percentual > 85
        ) {

            return {

                texto:
                    'Frequência ótima',

                classe:
                    'otima'
            };
        }


        if (
            percentual >= 75
        ) {

            return {

                texto:
                    'Atenção',

                classe:
                    'atencao'
            };
        }


        return {

            texto:
                'Risco de reprovação por frequência',

            classe:
                'risco'
        };
    }


    // =====================================================
    // RESUMO ANUAL
    // =====================================================

    function atualizarResumoAnual() {

        const meses =
            [
                ...document.querySelectorAll(
                    '.mes'
                )
            ];


        let presencas =
            0;

        let faltas =
            0;

        let atestados =
            0;

        let totalDiasLetivos =
            0;

        let diasRestantes =
            0;


        const porMes =
            [];


        meses.forEach(
            mes => {

                const dados =
                    recalcularMetricasDoMes(
                        mes
                    );


                presencas +=
                    dados.presencas;


                faltas +=
                    dados.faltas;


                atestados +=
                    dados.atestados;


                totalDiasLetivos +=
                    dados.totalDiasLetivos;


                diasRestantes +=
                    dados.diasRestantes;


                porMes.push({

                    mes:
                        Number(
                            mes.dataset.mes
                        ),

                    ...dados
                });
            }
        );


        const percentual =

            totalDiasLetivos > 0

                ? Math.round(

                    (
                        presencas /
                        totalDiasLetivos
                    ) *

                    100
                )

                : 0;


        const mesesComDados =
            porMes.filter(

                item =>
                    item.totalDiasLetivos >
                    0
            );


        const melhorMes =

            mesesComDados.length > 0

                ? [
                    ...mesesComDados
                ]
                .sort(

                    (
                        a,
                        b
                    ) =>

                        b.percentual -
                        a.percentual
                )[0]

                : null;


        const mesMaisFaltas =

            mesesComDados.length > 0

                ? [
                    ...mesesComDados
                ]
                .sort(

                    (
                        a,
                        b
                    ) =>

                        b.faltas -
                        a.faltas
                )[0]

                : null;


        const config =
            obterConfigAno();


        const frequenciaMinima =
            clamp(

                Number(
                    config.meta_anual ||
                    80
                ),

                0,

                100
            );


        const periodoConfigurado =
            Boolean(

                config.inicio_ano_letivo &&

                config.fim_ano_letivo
            );


        const faltasPossiveis =

            periodoConfigurado

                ? calcularFaltasPossiveis(

                    presencas,

                    totalDiasLetivos,

                    diasRestantes,

                    frequenciaMinima
                )

                : null;


        const totalFinalPrevisto =
            totalDiasLetivos +
            diasRestantes;


        const presencasFinais =
            presencas +
            diasRestantes;


        const projecaoFinal =

            periodoConfigurado &&
            totalFinalPrevisto >
                0

                ? Math.round(

                    (
                        presencasFinais /
                        totalFinalPrevisto
                    ) *

                    100
                )

                : 0;


        const risco =

            totalDiasLetivos > 0

                ? classificacaoRisco(
                    percentual
                )

                : {

                    texto:
                        periodoConfigurado

                            ? 'Sem dias letivos contabilizados'

                            : 'Configure o período letivo',

                    classe:
                        ''
                };


        const textos = {

            'freq-anual-percentual':
                `${percentual}%`,


            'freq-anual-faltas':
                String(
                    faltas
                ),


            'freq-anual-atestados':
                String(
                    atestados
                ),


            'freq-melhor-mes':

                melhorMes

                    ? (
                        `${NOMES_MESES[
                            melhorMes.mes -
                            1
                        ]} · ${melhorMes.percentual}%`
                    )

                    : '—',


            'freq-mes-mais-faltas':

                mesMaisFaltas

                    ? (
                        `${NOMES_MESES[
                            mesMaisFaltas.mes -
                            1
                        ]} · ${mesMaisFaltas.faltas}`
                    )

                    : '—',


            'freq-meta-resumo':

                `Mínimo exigido: ${frequenciaMinima}% · Frequência atual: ${percentual}%`,


            'freq-meta-percent':
                `${percentual}%`,


            'proj-dias-passados':

                periodoConfigurado

                    ? String(
                        totalDiasLetivos
                    )

                    : '—',


            'proj-dias-restantes':

                periodoConfigurado

                    ? String(
                        diasRestantes
                    )

                    : '—',


            'proj-presencas':

                periodoConfigurado

                    ? String(
                        presencas
                    )

                    : '—',


            'proj-final-sem-faltas':

                periodoConfigurado

                    ? `${projecaoFinal}%`

                    : '—',


            'freq-diferenca-meta':

                periodoConfigurado

                    ? textoDiferencaFrequencia(

                        percentual,

                        frequenciaMinima
                    )

                    : (
                        'Defina o início e o final do ano letivo para acompanhar a frequência mínima exigida.'
                    ),


            'freq-faltas-restantes':

                !periodoConfigurado

                    ? (
                        'O limite de faltas será calculado depois que o período letivo for definido.'
                    )

                    : faltasPossiveis ===
                        null

                        ? (
                            'Com frequência mínima de 0%, não há limite calculado de faltas.'
                        )

                        : (
                            `Você ainda pode ter até ${faltasPossiveis} ` +

                            `${faltasPossiveis === 1
                                ? 'falta'
                                : 'faltas'
                            } ` +

                            `sem ficar abaixo da frequência mínima de ${frequenciaMinima}%.`
                        )
        };


        Object.entries(
            textos
        )
        .forEach(
            ([
                id,
                texto
            ]) => {

                const elemento =
                    document.getElementById(
                        id
                    );


                if (
                    elemento
                ) {

                    elemento.textContent =
                        texto;
                }
            }
        );


        const barra =
            document.getElementById(
                'freq-progress-fill'
            );


        if (
            barra
        ) {

            barra.style.width =

                `${Math.min(
                    100,
                    percentual
                )}%`;
        }


        const badge =
            document.getElementById(
                'freq-risco'
            );


        if (
            badge
        ) {

            badge.textContent =
                risco.texto;


            badge.classList.remove(

                'otima',

                'atencao',

                'risco'
            );


            if (
                risco.classe
            ) {

                badge.classList.add(
                    risco.classe
                );
            }
        }


        const aviso =
            document.getElementById(
                'freq-projecao-aviso'
            );


        if (
            aviso
        ) {

            const span =
                aviso.querySelector(
                    'span'
                );


            aviso.classList.remove(

                'neutro',

                'seguro',

                'atencao',

                'critico'
            );


            let classe =
                'neutro';


            let mensagem =
                'Configure o período letivo para gerar a projeção.';


            if (
                periodoConfigurado
            ) {

                if (
                    totalDiasLetivos ===
                    0
                ) {

                    mensagem =
                        'O período letivo está configurado, mas ainda não há dias contabilizados.';

                } else if (

                    percentual <
                        frequenciaMinima &&

                    projecaoFinal >=
                        frequenciaMinima
                ) {

                    classe =
                        'atencao';


                    mensagem =

                        `Sua frequência atual está abaixo do mínimo exigido de ${frequenciaMinima}%, ` +

                        `mas ainda pode se recuperar. ` +

                        `Sem novas faltas, a projeção final é ${projecaoFinal}%.`;

                } else if (

                    percentual <
                    frequenciaMinima
                ) {

                    classe =
                        'critico';


                    mensagem =

                        `Sua frequência está abaixo do mínimo exigido de ${frequenciaMinima}%. ` +

                        `Mesmo sem novas faltas, a projeção final é ${projecaoFinal}%.`;

                } else if (

                    faltasPossiveis ===
                    0
                ) {

                    classe =
                        'critico';


                    mensagem =

                        `Sua frequência está dentro do mínimo exigido de ${frequenciaMinima}%, ` +

                        'mas você não possui margem para novas faltas.';

                } else if (

                    faltasPossiveis !==
                        null &&

                    faltasPossiveis <=
                        2
                ) {

                    classe =
                        'atencao';


                    mensagem =

                        `Atenção: você só pode ter mais ${faltasPossiveis} ` +

                        `${faltasPossiveis === 1
                            ? 'falta'
                            : 'faltas'
                        } ` +

                        `sem ficar abaixo da frequência mínima de ${frequenciaMinima}%.`;

                } else {

                    classe =
                        'seguro';


                    mensagem =

                        `Sua frequência está dentro do mínimo exigido. ` +

                        `Você ainda pode ter até ${faltasPossiveis} ` +

                        `${faltasPossiveis === 1
                            ? 'falta'
                            : 'faltas'
                        } ` +

                        `sem ficar abaixo da frequência mínima de ${frequenciaMinima}%.`;
                }
            }


            aviso.classList.add(
                classe
            );


            if (
                span
            ) {

                span.textContent =
                    mensagem;
            }
        }
    }


    // =====================================================
    // ABRIR / FECHAR MÊS
    // =====================================================

    function fecharMiniAgenda(
        mes
    ) {

        mes
            ?.querySelector(
                '.mini-agenda'
            )
            ?.classList
            .remove(
                'aberto'
            );
    }


    function fecharMes(
        mes
    ) {

        if (
            !mes
        ) {

            return;
        }


        fecharMiniAgenda(
            mes
        );


        mes.classList.remove(
            'expanded'
        );


        mes.__corSelecionada =
            null;


        mes.__atualizarBotoesCor
            ?.();


        const fechar =
            mes.querySelector(
                '.fechar-btn'
            );


        if (
            fechar
        ) {

            fechar.style.display =
                'none';
        }


        if (
            !document.querySelector(
                '.mes.expanded'
            )
        ) {

            document.body
                .classList
                .remove(
                    'no-scroll'
                );


            document
                .getElementById(
                    'cal-backdrop'
                )
                ?.classList
                .remove(
                    'ativo'
                );
        }
    }


    function abrirMes(
        mes
    ) {

        if (
            !mes
        ) {

            return false;
        }


        const aberto =
            document.querySelector(
                '.mes.expanded'
            );


        if (
            aberto &&
            aberto !== mes
        ) {

            fecharMes(
                aberto
            );
        }


        if (
            !mes.classList.contains(
                'expanded'
            )
        ) {

            mes.classList.add(
                'expanded'
            );


            document.body
                .classList
                .add(
                    'no-scroll'
                );


            document
                .getElementById(
                    'cal-backdrop'
                )
                ?.classList
                .add(
                    'ativo'
                );
        }


        let botaoFechar =
            mes.querySelector(
                '.fechar-btn'
            );


        if (
            !botaoFechar
        ) {

            botaoFechar =
                document.createElement(
                    'button'
                );


            botaoFechar.type =
                'button';


            botaoFechar.className =
                'fechar-btn';


            botaoFechar.textContent =
                '×';


            botaoFechar.addEventListener(
                'click',
                evento => {

                    evento.preventDefault();

                    evento.stopPropagation();


                    fecharMes(
                        mes
                    );
                }
            );


            mes.appendChild(
                botaoFechar
            );
        }


        botaoFechar.style.display =
            'flex';


        return true;
    }


    // =====================================================
    // TAREFAS DO DIA
    // =====================================================

    function renderizarTarefasDia(
        iso,
        resumo,
        editor
    ) {

        const tarefas =
            tarefasDoDia(
                iso
            );


        const lembretes =
            lembretesDoDia(
                iso
            );


        resumo.style.display =
            'block';


        editor.style.display =
            'none';


        if (
            !tarefas.length &&
            !lembretes.length
        ) {

            resumo.innerHTML = `

                <p class="agenda-resumo-vazio">
                    Nenhuma tarefa cadastrada para este dia.
                </p>

            `;


            return;
        }


        let html =
            '';


        if (
            tarefas.length
        ) {

            html += `

                <div class="agenda-bloco">

                    <strong>
                        Tarefas do dia
                    </strong>

                    <ul>

            `;


            tarefas.forEach(
                tarefa => {

                    const texto =
                        escapeHtml(

                            obterTextoItem(
                                tarefa
                            )
                        );


                    const materia =
                        escapeHtml(

                            obterMateriaItem(
                                tarefa
                            )
                        );


                    const concluida =
                        tarefaConcluida(
                            tarefa
                        );


                    html += `

                        <li

                            ${
                                concluida

                                    ? 'style="opacity:.6;text-decoration:line-through;"'

                                    : ''
                            }

                        >

                            ${
                                concluida
                                    ? '✓ '
                                    : ''
                            }

                            ${texto}

                            ${
                                materia

                                    ? `<small style="display:block;">${materia}</small>`

                                    : ''
                            }

                        </li>

                    `;
                }
            );


            html += `

                    </ul>

                </div>

            `;
        }


        if (
            lembretes.length
        ) {

            html += `

                <div class="agenda-bloco">

                    <strong>
                        Não esquecer
                    </strong>

                    <ul>

            `;


            lembretes.forEach(
                lembrete => {

                    html += `

                        <li>

                            ${
                                escapeHtml(

                                    obterTextoItem(
                                        lembrete
                                    )
                                )
                            }

                        </li>

                    `;
                }
            );


            html += `

                    </ul>

                </div>

            `;
        }


        resumo.innerHTML =
            html;
    }


    // =====================================================
    // HORÁRIOS DO DIA
    // =====================================================

    function renderizarHorariosDia(
        iso,
        resumo,
        editor
    ) {

        resumo.style.display =
            'block';


        editor.style.display =
            'none';


        const horarios =
            buscarHorarios(
                iso
            );


        if (
            !horarios.length
        ) {

            resumo.innerHTML = `

                <p class="agenda-resumo-vazio">
                    Nenhuma aula cadastrada para este dia.
                </p>

            `;


            return;
        }


        resumo.innerHTML = `

            <div class="agenda-bloco">

                <strong>
                    Horário do dia
                </strong>

                <ul class="agenda-lista-horarios">

                    ${
                        horarios
                            .map(
                                item => `

                                    <li>

                                        ${
                                            item.horario

                                                ? `
                                                    <strong>
                                                        ${
                                                            escapeHtml(
                                                                item.horario
                                                            )
                                                        }
                                                    </strong>
                                                `

                                                : ''
                                        }

                                        <span>

                                            ${
                                                escapeHtml(
                                                    item.materia
                                                )
                                            }

                                        </span>

                                    </li>

                                `
                            )
                            .join('')
                    }

                </ul>

            </div>

        `;
    }


    // =====================================================
    // ABRIR MINI AGENDA
    // =====================================================

    function abrirMiniAgenda(
        dia,
        abrirEditor = false
    ) {

        if (
            !dia
        ) {

            return;
        }


        const mes =
            dia.closest(
                '.mes'
            );


        if (
            !mes
        ) {

            return;
        }


        abrirMes(
            mes
        );


        const iso =
            dia.dataset.date;


        if (
            !iso
        ) {

            return;
        }


        const mini =
            mes.querySelector(
                '.mini-agenda'
            );


        const dataEl =
            mini?.querySelector(
                '.agenda-data'
            );


        const resumo =
            mini?.querySelector(
                '.agenda-resumo'
            );


        const editor =
            mini?.querySelector(
                '.agenda-editor'
            );


        const notas =
            mini?.querySelector(
                '.agenda-notas'
            );


        const btnVer =
            mini?.querySelector(
                '.btn-ver-tarefas'
            );


        const btnNova =
            mini?.querySelector(
                '.btn-nova-tarefa'
            );


        const btnHorarios =
            mini?.querySelector(
                '.btn-ver-horarios'
            );


        if (
            !mini ||
            !dataEl ||
            !resumo ||
            !editor ||
            !notas ||
            !btnVer ||
            !btnNova ||
            !btnHorarios
        ) {

            console.error(
                'Mini agenda incompleta no HTML.'
            );

            return;
        }


        mini.dataset.date =
            iso;


        dataEl.textContent =
            formataDataBR(
                iso
            );


        // =============================================
        // VER TAREFAS
        // =============================================

        btnVer.onclick =
            evento => {

                evento
                    ?.preventDefault();


                evento
                    ?.stopPropagation();


                renderizarTarefasDia(

                    iso,

                    resumo,

                    editor
                );
            };


        // =============================================
        // NOVA TAREFA
        // =============================================

        btnNova.onclick =
            evento => {

                evento
                    ?.preventDefault();


                evento
                    ?.stopPropagation();


                const tarefaCalendario =
                    tarefasDoDia(
                        iso
                    )
                    .find(

                        item =>
                            item.origem ===
                            'calendario'
                    );


                notas.value =

                    tarefaCalendario

                        ? obterTextoItem(
                            tarefaCalendario
                        )

                        : '';


                resumo.style.display =
                    'none';


                editor.style.display =
                    'block';


                notas.focus();
            };


        // =============================================
        // HORÁRIOS
        // =============================================

        btnHorarios.onclick =
            evento => {

                evento
                    ?.preventDefault();


                evento
                    ?.stopPropagation();


                renderizarHorariosDia(

                    iso,

                    resumo,

                    editor
                );
            };


        // =============================================
        // ABRIR CAIXA
        // =============================================

        mini.classList.add(
            'aberto'
        );


        if (
            abrirEditor
        ) {

            btnNova.click();

        } else if (

            tarefasDoDia(
                iso
            ).length > 0 ||

            lembretesDoDia(
                iso
            ).length > 0
        ) {

            btnVer.click();

        } else {

            btnNova.click();
        }
    }


    // =====================================================
    // BOTÕES DE MARCAÇÃO
    // =====================================================

    document
        .querySelectorAll(
            '.mes'
        )
        .forEach(
            mes => {

                mes.__corSelecionada =
                    null;


                const botoes =
                    [
                        ...mes.querySelectorAll(
                            '.btn-cor'
                        )
                    ];


                function atualizarBotoes() {

                    botoes.forEach(
                        botao => {

                            const ativo =

                                botao.dataset.cor ===

                                mes.__corSelecionada;


                            botao.classList.toggle(

                                'selecionado',

                                ativo
                            );


                            botao.style.outline =

                                ativo

                                    ? '3px solid #555'

                                    : 'none';


                            botao.style.transform =

                                ativo

                                    ? 'scale(1.3)'

                                    : 'scale(1)';
                        }
                    );
                }


                mes.__atualizarBotoesCor =
                    atualizarBotoes;


                botoes.forEach(
                    botao => {

                        botao.addEventListener(
                            'click',
                            evento => {

                                evento.preventDefault();

                                evento.stopPropagation();


                                const cor =
                                    botao.dataset.cor;


                                mes.__corSelecionada =

                                    mes.__corSelecionada ===
                                        cor

                                        ? null

                                        : cor;


                                atualizarBotoes();
                            }
                        );
                    }
                );
            }
        );


    // =====================================================
    // CLIQUE PRINCIPAL DO MÊS / DIA
    // =====================================================

    document
        .querySelectorAll(
            '.mes'
        )
        .forEach(
            mes => {

                mes.addEventListener(
                    'click',
                    async evento => {

                        if (
                            evento.target.closest(

                                '.mini-agenda, .toolbar-cal, .fechar-btn, .btn-cor, .meta-presenca'

                            )
                        ) {

                            return;
                        }


                        const dia =
                            evento.target.closest(
                                '.dia[data-date]'
                            );


                        if (
                            !dia
                        ) {

                            abrirMes(
                                mes
                            );

                            return;
                        }


                        evento.preventDefault();

                        evento.stopPropagation();


                        abrirMes(
                            mes
                        );


                        // =================================
                        // COR SELECIONADA
                        // =================================

                        if (
                            mes.__corSelecionada
                        ) {

                            const iso =
                                dia.dataset.date;


                            const cor =
                                mes.__corSelecionada;


                            if (
                                dia.classList.contains(
                                    'feriado'
                                )
                            ) {

                                alert(
                                    'Este dia é feriado automático e não pode ser alterado.'
                                );

                                return;
                            }


                            if (

                                iso > hojeIso() &&

                                (
                                    cor ===
                                        'vermelho' ||

                                    cor ===
                                        'amarelo'
                                )
                            ) {

                                alert(
                                    'Não é possível marcar falta ou atestado em uma data futura.'
                                );

                                return;
                            }


                            // Guarda status anterior
                            const statusAnterior =
                                statusDia(
                                    dia
                                );


                            dia.classList.remove(

                                'vermelho',

                                'amarelo',

                                'sem-aula',

                                'roxo'
                            );


                            if (
                                cor !==
                                'limpar'
                            ) {

                                dia.classList.add(
                                    cor
                                );
                            }


                            const status =
                                statusDia(
                                    dia
                                );


                            if (
                                status
                            ) {

                                calendData.dias[
                                    iso
                                ] =
                                    status;

                            } else {

                                delete calendData.dias[
                                    iso
                                ];
                            }


                            const salvou =
                                await salvarCalendarioServidor();


                            if (
                                !salvou
                            ) {

                                // =================================
                                // RESTAURAR SE DER ERRO
                                // =================================

                                dia.classList.remove(

                                    'vermelho',

                                    'amarelo',

                                    'sem-aula',

                                    'roxo'
                                );


                                if (
                                    statusAnterior
                                ) {

                                    dia.classList.add(
                                        statusAnterior
                                    );


                                    calendData.dias[
                                        iso
                                    ] =
                                        statusAnterior;

                                } else {

                                    delete calendData.dias[
                                        iso
                                    ];
                                }


                                atualizarTudo();


                                alert(
                                    'Não foi possível salvar esta alteração no calendário.'
                                );

                                return;
                            }


                            atualizarTudo();


                            return;
                        }


                        // =================================
                        // SEM MARCAÇÃO = ABRIR DIA
                        // =================================

                        abrirMiniAgenda(

                            dia,

                            false
                        );
                    }
                );


                // =========================================
                // DUPLO CLIQUE
                // =========================================

                mes.addEventListener(
                    'dblclick',
                    evento => {

                        const dia =
                            evento.target.closest(
                                '.dia[data-date]'
                            );


                        if (
                            !dia ||
                            mes.__corSelecionada
                        ) {

                            return;
                        }


                        evento.preventDefault();

                        evento.stopPropagation();


                        abrirMes(
                            mes
                        );


                        abrirMiniAgenda(

                            dia,

                            true
                        );
                    }
                );
            }
        );


    // =====================================================
    // FECHAR MINI AGENDA
    // =====================================================

    document
        .querySelectorAll(
            '.agenda-fechar'
        )
        .forEach(
            botao => {

                botao.addEventListener(
                    'click',
                    evento => {

                        evento.preventDefault();

                        evento.stopPropagation();


                        botao
                            .closest(
                                '.mini-agenda'
                            )
                            ?.classList
                            .remove(
                                'aberto'
                            );
                    }
                );
            }
        );


    // =====================================================
    // SALVAR MINI AGENDA
    // =====================================================

    document
        .querySelectorAll(
            '.agenda-salvar'
        )
        .forEach(
            botao => {

                botao.addEventListener(
                    'click',
                    async evento => {

                        evento.preventDefault();

                        evento.stopPropagation();


                        const mini =
                            botao.closest(
                                '.mini-agenda'
                            );


                        const textarea =
                            mini?.querySelector(
                                '.agenda-notas'
                            );


                        const iso =
                            mini?.dataset.date;


                        if (
                            !mini ||
                            !textarea ||
                            !iso
                        ) {

                            return;
                        }


                        const textoOriginal =
                            botao.textContent;


                        botao.disabled =
                            true;


                        botao.textContent =
                            'Salvando...';


                        const salvou =
                            await salvarTextoDoDiaNaAgenda(

                                iso,

                                textarea.value
                            );


                        botao.disabled =
                            false;


                        botao.textContent =
                            textoOriginal;


                        if (
                            !salvou
                        ) {

                            return;
                        }


                        mini.classList.remove(
                            'aberto'
                        );
                    }
                );
            }
        );


    // =====================================================
    // FREQUÊNCIA MENSAL
    // =====================================================

    document
        .querySelectorAll(
            '.mes .meta-presenca'
        )
        .forEach(
            input => {

                input.addEventListener(
                    'change',
                    async evento => {

                        const mes =
                            evento.target.closest(
                                '.mes'
                            );


                        if (
                            !mes
                        ) {

                            return;
                        }


                        const chave =

                            `${mes.dataset.ano}-${mes.dataset.mes}`;


                        const valorAnterior =
                            calendData.metas[
                                chave
                            ];


                        calendData.metas[
                            chave
                        ] =
                            clamp(

                                Number(
                                    evento.target.value ||
                                    0
                                ),

                                0,

                                100
                            );


                        evento.target.value =
                            String(

                                calendData.metas[
                                    chave
                                ]
                            );


                        const salvou =
                            await salvarCalendarioServidor();


                        if (
                            !salvou
                        ) {

                            if (
                                valorAnterior != null
                            ) {

                                calendData.metas[
                                    chave
                                ] =
                                    valorAnterior;


                                evento.target.value =
                                    String(
                                        valorAnterior
                                    );

                            } else {

                                delete calendData.metas[
                                    chave
                                ];
                            }


                            alert(
                                'Não foi possível salvar a frequência mínima mensal.'
                            );

                            return;
                        }


                        atualizarTudo();
                    }
                );
            }
        );


    // =====================================================
    // IMPRIMIR
    // =====================================================

    document
        .querySelectorAll(
            '.btn-imprimir'
        )
        .forEach(
            botao => {

                botao.addEventListener(
                    'click',
                    evento => {

                        evento.preventDefault();

                        evento.stopPropagation();


                        window.print();
                    }
                );
            }
        );


    // =====================================================
    // EXPORTAR PNG
    // =====================================================

    document
        .querySelectorAll(
            '.btn-exportar-png'
        )
        .forEach(
            botao => {

                botao.addEventListener(
                    'click',
                    async evento => {

                        evento.preventDefault();

                        evento.stopPropagation();


                        const mes =
                            botao.closest(
                                '.mes'
                            );


                        const bloco =
                            mes?.querySelector(
                                '.calendario-mes'
                            );


                        if (

                            !bloco ||

                            typeof html2canvas !==
                                'function'
                        ) {

                            return;
                        }


                        const numeroMes =
                            Number(
                                mes.dataset.mes
                            );


                        const canvas =
                            await html2canvas(

                                bloco,

                                {
                                    useCORS:
                                        true,

                                    backgroundColor:
                                        '#ffffff',

                                    scale:
                                        2
                                }
                            );


                        const link =
                            document.createElement(
                                'a'
                            );


                        link.download =

                            `Calendario_${NOMES_MESES[
                                numeroMes -
                                1
                            ]}_${mes.dataset.ano}.png`;


                        link.href =
                            canvas.toDataURL(
                                'image/png'
                            );


                        link.click();
                    }
                );
            }
        );


    // =====================================================
    // SELETOR DE ANO
    // =====================================================

    document
        .querySelectorAll(
            '.anoSelect'
        )
        .forEach(
            select => {

                for (

                    let ano =
                        ANO_ATUAL - 4;

                    ano <=
                        ANO_ATUAL + 4;

                    ano++
                ) {

                    const option =
                        document.createElement(
                            'option'
                        );


                    option.value =
                        String(
                            ano
                        );


                    option.textContent =
                        String(
                            ano
                        );


                    option.selected =
                        ano ===
                        ANO_ATUAL;


                    select.appendChild(
                        option
                    );
                }


                select.addEventListener(
                    'change',
                    () => {

                        const url =
                            new URL(
                                location.href
                            );


                        url.searchParams.set(

                            'ano',

                            select.value
                        );


                        location.href =
                            url.toString();
                    }
                );
            }
        );


    // =====================================================
    // PERFIL / LOGOUT
    // =====================================================

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


    perfilIcon
        ?.addEventListener(
            'click',
            () => {

                window.location.href =
                    FOAG_CONFIG.pages.perfil;
            }
        );


    iconSair
        ?.addEventListener(
            'click',
            () => {

                if (
                    logoutModal
                ) {

                    logoutModal.style.display =
                        'flex';
                }
            }
        );


    confirmLogout
        ?.addEventListener(
            'click',
            () => {

                window.location.href =
                    FOAG_CONFIG.pages.logout;
            }
        );


    cancelLogout
        ?.addEventListener(
            'click',
            () => {

                if (
                    logoutModal
                ) {

                    logoutModal.style.display =
                        'none';
                }
            }
        );


    logoutModal
        ?.addEventListener(
            'click',
            evento => {

                if (
                    evento.target ===
                    logoutModal
                ) {

                    logoutModal.style.display =
                        'none';
                }
            }
        );


    // =====================================================
    // PAINEL ANUAL EXPANSÍVEL
    // =====================================================

    function configurarPainelAnualExpansivel() {

        const painel =
            document.getElementById(
                'frequencia-anual'
            );


        const topo =
            painel?.querySelector(
                '.freq-anual-topo'
            );


        if (
            !painel ||
            !topo
        ) {

            return;
        }


        if (
            topo.querySelector(
                '.btn-expandir-frequencia'
            )
        ) {

            return;
        }


        painel.classList.add(
            'frequencia-recolhida'
        );


        const botao =
            document.createElement(
                'button'
            );


        botao.type =
            'button';


        botao.className =
            'btn-expandir-frequencia';


        botao.setAttribute(

            'aria-expanded',

            'false'
        );


        botao.setAttribute(

            'aria-label',

            'Expandir frequência anual'
        );


        botao.title =
            'Expandir frequência anual';


        botao.innerHTML =

            '<i class="fa-solid fa-chevron-down"></i>';


        topo.appendChild(
            botao
        );


        botao.addEventListener(
            'click',
            evento => {

                evento.preventDefault();

                evento.stopPropagation();


                const vaiAbrir =
                    painel.classList.contains(
                        'frequencia-recolhida'
                    );


                painel.classList.toggle(
                    'frequencia-recolhida'
                );


                botao.setAttribute(

                    'aria-expanded',

                    vaiAbrir
                        ? 'true'
                        : 'false'
                );


                botao.setAttribute(

                    'aria-label',

                    vaiAbrir

                        ? 'Recolher frequência anual'

                        : 'Expandir frequência anual'
                );


                botao.title =

                    vaiAbrir

                        ? 'Recolher frequência anual'

                        : 'Expandir frequência anual';
            }
        );
    }


    // =====================================================
    // ATUALIZAR TUDO
    // =====================================================

    function atualizarTudo() {

        aplicarMarcacoesPeriodo();

        destacarHoje();

        marcarDiasComTarefa();


        document
            .querySelectorAll(
                '.calendario .dia[data-date]'
            )
            .forEach(
                atualizarDots
            );


        atualizarResumoAnual();
    }


    // =====================================================
    // INICIALIZAÇÃO
    // =====================================================

    configurarPainelAnualExpansivel();

    preencherConfigAno();


    // ==========================================
    // CARREGAR STATUS DOS DIAS
    // ==========================================

    document
        .querySelectorAll(
            '.calendario .dia[data-date]'
        )
        .forEach(
            dia => {

                const status =
                    calendData.dias[
                        dia.dataset.date
                    ];


                if (

                    status &&

                    [
                        'vermelho',
                        'amarelo',
                        'sem-aula',
                        'roxo'

                    ].includes(
                        status
                    )
                ) {

                    dia.classList.add(
                        status
                    );
                }
            }
        );


    // ==========================================
    // CARREGAR FREQUÊNCIAS MENSAIS
    // ==========================================

    document
        .querySelectorAll(
            '.mes'
        )
        .forEach(
            mes => {

                const chave =

                    `${mes.dataset.ano}-${mes.dataset.mes}`;


                const valor =
                    calendData.metas[
                        chave
                    ];


                const input =
                    mes.querySelector(
                        '.meta-presenca'
                    );


                if (
                    input &&
                    valor != null
                ) {

                    input.value =
                        String(
                            valor
                        );
                }
            }
        );


    // ==========================================
    // CALCULAR TUDO
    // ==========================================

    atualizarTudo();


    // =====================================================
    // DEBUG
    // =====================================================

    window._debugCalendario =
        calendData;


    window._debugAgendaCalendario =
        agendaData;


    window._debugHorarioHtml =
        HORARIO_HTML;


    window._debugBuscarHorarios =
        buscarHorarios;


    window._debugSalvarCalendario =
        salvarCalendarioServidor;


    console.log(
        'Calendário FOAG carregado ✅'
    );


    console.log(

        'Horário recebido:',

        HORARIO_HTML.trim()
            ? 'SIM'
            : 'NÃO'
    );
});