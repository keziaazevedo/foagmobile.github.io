document.addEventListener(
    'DOMContentLoaded',
    function () {

        // =================================================
        // ELEMENTOS
        // =================================================

        const noteModal =
            document.getElementById(
                'note-modal'
            );

        const addNoteBtn =
            document.getElementById(
                'add-note'
            );

        const closeNoteModal =
            document.getElementById(
                'close-note-modal'
            );

        const cancelNoteBtn =
            document.getElementById(
                'cancel-note'
            );

        const saveNoteBtn =
            document.getElementById(
                'save-note'
            );

        const noteText =
            document.getElementById(
                'note-text'
            );

        const notesList =
            document.getElementById(
                'notes-list'
            );

        const emptyNotes =
            document.getElementById(
                'empty-notes'
            );

        const createFirstNoteBtn =
            document.getElementById(
                'create-first-note'
            );


        const perfilIcon =
            document.getElementById(
                'icon-perfil'
            );


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


        const iconSair =
            document.getElementById(
                'icon-sair'
            );


        const mensagemAcessibilidade =
            document.getElementById(
                'mensagem-acessibilidade'
            );


        const NOTE_SAVE_URL =
            window.INICIO_NOTE_SAVE_URL ||
            FOAG_CONFIG.endpoints.inicioAnotacaoSalvar;


        let ultimoFoco =
            null;


        // =================================================
        // FRASES
        // =================================================

        const frases = [

            'Organizar é o primeiro passo para o sucesso!',

            'Cada tarefa concluída é uma vitória!',

            'A consistência leva à excelência!',

            'Hoje é um novo dia para ser produtivo!',

            'Pequenos passos levam a grandes conquistas!',

            'A organização transforma sonhos em realidade!',

            'Você está no controle do seu tempo!',

            'Cada dia é uma nova oportunidade!',

            'A disciplina é a ponte entre metas e realizações!',

            'Seu potencial é ilimitado!'

        ];


        // =================================================
        // ACESSIBILIDADE
        // =================================================

        function anunciar(
            mensagem
        ) {

            if (
                !mensagemAcessibilidade
            ) {
                return;
            }


            mensagemAcessibilidade.textContent =
                '';


            setTimeout(
                function () {

                    mensagemAcessibilidade.textContent =
                        mensagem;

                },
                50
            );
        }


        function guardarFoco() {

            if (
                document.activeElement
                instanceof HTMLElement
            ) {

                ultimoFoco =
                    document.activeElement;
            }
        }


        function devolverFoco() {

            if (
                ultimoFoco &&
                typeof ultimoFoco.focus ===
                'function'
            ) {

                ultimoFoco.focus();
            }


            ultimoFoco =
                null;
        }


        // =================================================
        // FRASE MOTIVACIONAL
        // =================================================

        function trocarFrase() {

            const elemento =
                document.getElementById(
                    'quote-text'
                );


            if (
                !elemento
            ) {
                return;
            }


            const indice =
                Math.floor(
                    Math.random() *
                    frases.length
                );


            elemento.textContent =
                frases[
                    indice
                ];
        }


        trocarFrase();


        setInterval(
            trocarFrase,
            3600000
        );


        // =================================================
        // ABRIR MODAL
        // =================================================

        function abrirModalNota() {

            if (
                !noteModal
            ) {
                return;
            }


            guardarFoco();


            noteModal.style.display =
                'flex';


            noteModal.setAttribute(
                'aria-hidden',
                'false'
            );


            if (
                noteText
            ) {

                noteText.value =
                    '';


                requestAnimationFrame(
                    function () {

                        noteText.focus();
                    }
                );
            }


            anunciar(
                'Janela de nova anotação aberta.'
            );
        }


        // =================================================
        // FECHAR MODAL
        // =================================================

        function fecharModalNota() {

            if (
                !noteModal
            ) {
                return;
            }


            noteModal.style.display =
                'none';


            noteModal.setAttribute(
                'aria-hidden',
                'true'
            );


            if (
                noteText
            ) {

                noteText.value =
                    '';
            }


            devolverFoco();
        }


        // =================================================
        // NOTIFICAÇÃO
        // =================================================

        function mostrarNotificacao(
            mensagem,
            erro = false
        ) {

            const notificacao =
                document.createElement(
                    'div'
                );


            notificacao.setAttribute(
                'role',
                'status'
            );


            notificacao.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;

                padding: 12px 18px;

                border-radius: 8px;

                background:
                    ${erro
                        ? '#dc3545'
                        : '#38a5ff'};

                color: white;

                font-family:
                    'Poppins',
                    sans-serif;

                font-size: 14px;

                box-shadow:
                    0 5px 18px
                    rgba(0,0,0,.20);
            `;


            notificacao.textContent =
                mensagem;


            document.body.appendChild(
                notificacao
            );


            anunciar(
                mensagem
            );


            setTimeout(
                function () {

                    notificacao.remove();

                },
                3000
            );
        }


        // =================================================
        // ADICIONAR NOTA VISUALMENTE
        // =================================================

        function adicionarNotaNaTela(
            anotacao
        ) {

            if (
                !notesList ||
                !anotacao
            ) {
                return;
            }


            const item =
                document.createElement(
                    'div'
                );


            item.className =
                'note-item';


            // TÍTULO

            if (
                anotacao.titulo
            ) {

                const titulo =
                    document.createElement(
                        'strong'
                    );


                titulo.className =
                    'note-title';


                titulo.textContent =
                    anotacao.titulo;


                item.appendChild(
                    titulo
                );
            }


            // TEXTO

            if (
                anotacao.text
            ) {

                const texto =
                    document.createElement(
                        'p'
                    );


                texto.className =
                    'note-text';


                texto.textContent =
                    anotacao.text;


                item.appendChild(
                    texto
                );
            }


            // DATA

            if (
                anotacao.date
            ) {

                const data =
                    document.createElement(
                        'span'
                    );


                data.className =
                    'note-date';


                data.textContent =
                    anotacao.date;


                item.appendChild(
                    data
                );
            }


            notesList.prepend(
                item
            );


            notesList.style.display =
                'flex';


            if (
                emptyNotes
            ) {

                emptyNotes.style.display =
                    'none';
            }


            // No máximo 5 na Home

            while (
                notesList.children.length >
                5
            ) {

                notesList
                    .lastElementChild
                    ?.remove();
            }
        }


        // =================================================
        // SALVAR NOTA
        // =================================================

        async function salvarNota() {

            if (
                !noteText
            ) {
                return;
            }


            const texto =
                noteText.value.trim();


            if (
                texto === ''
            ) {

                mostrarNotificacao(
                    'Digite uma anotação antes de salvar.',
                    true
                );


                noteText.focus();

                return;
            }


            const textoBotao =
                saveNoteBtn
                    ?.innerHTML ||
                'Salvar';


            if (
                saveNoteBtn
            ) {

                saveNoteBtn.disabled =
                    true;


                saveNoteBtn.innerHTML = `
                    <i
                        class="fa-solid fa-spinner fa-spin"
                    ></i>
                    Salvando...
                `;
            }


            try {

                const resposta =
                    await fetch(
                        NOTE_SAVE_URL,
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
                                    {
                                        text:
                                            texto
                                    }
                                )
                        }
                    );


                let dados;


                try {

                    dados =
                        await resposta.json();

                } catch (_) {

                    throw new Error(
                        'Resposta inválida do servidor.'
                    );
                }


                if (
                    !resposta.ok ||
                    dados.sucesso !==
                    true
                ) {

                    throw new Error(
                        dados.mensagem ||
                        'Não foi possível salvar a anotação.'
                    );
                }


                adicionarNotaNaTela(
                    dados.anotacao
                );


                fecharModalNota();


                mostrarNotificacao(
                    'Anotação salva com sucesso!'
                );


            } catch (erro) {

                console.error(
                    'Erro ao salvar anotação:',
                    erro
                );


                mostrarNotificacao(
                    erro.message ||
                    'Erro ao salvar anotação.',
                    true
                );


            } finally {

                if (
                    saveNoteBtn
                ) {

                    saveNoteBtn.disabled =
                        false;


                    saveNoteBtn.innerHTML =
                        textoBotao;
                }
            }
        }


        // =================================================
        // LOGOUT
        // =================================================

        function abrirLogout() {

            if (
                !logoutModal
            ) {
                return;
            }


            guardarFoco();


            logoutModal.style.display =
                'flex';


            requestAnimationFrame(
                function () {

                    cancelLogout
                        ?.focus();
                }
            );
        }


        function fecharLogout() {

            if (
                !logoutModal
            ) {
                return;
            }


            logoutModal.style.display =
                'none';


            devolverFoco();
        }


        // =================================================
        // EVENTOS
        // =================================================

        addNoteBtn
            ?.addEventListener(
                'click',
                abrirModalNota
            );


        createFirstNoteBtn
            ?.addEventListener(
                'click',
                abrirModalNota
            );


        closeNoteModal
            ?.addEventListener(
                'click',
                fecharModalNota
            );


        cancelNoteBtn
            ?.addEventListener(
                'click',
                fecharModalNota
            );


        saveNoteBtn
            ?.addEventListener(
                'click',
                salvarNota
            );


        noteText
            ?.addEventListener(
                'keydown',
                function (
                    evento
                ) {

                    if (
                        evento.ctrlKey &&
                        evento.key ===
                        'Enter'
                    ) {

                        evento.preventDefault();

                        salvarNota();
                    }
                }
            );


        noteModal
            ?.addEventListener(
                'click',
                function (
                    evento
                ) {

                    if (
                        evento.target ===
                        noteModal
                    ) {

                        fecharModalNota();
                    }
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
                abrirLogout
            );


        cancelLogout
            ?.addEventListener(
                'click',
                fecharLogout
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

                        fecharLogout();
                    }
                }
            );


        // ESC

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


                if (
                    noteModal &&
                    noteModal.style.display ===
                    'flex'
                ) {

                    fecharModalNota();

                    return;
                }


                if (
                    logoutModal &&
                    logoutModal.style.display ===
                    'flex'
                ) {

                    fecharLogout();
                }
            }
        );


        console.log(
            'Página inicial FOAG pronta ✅'
        );

    }
);