// =====================================================
// loja.js — Loja de Estrelas FOAG
// PASSO 2: saldo central em pontos.json
// =====================================================


// =====================================================
// DADOS GLOBAIS
// =====================================================

const LOJA_ACTION_URL =
    window.LOJA_ACTION_URL ||
    'salvar_loja.php';


let lojaData =
    window.LOJA_DATA || {

        estrelas: 0,

        total_estudado: 0,

        itens: [],

        itens_comprados: [],

        itens_ativos: {

            tema: null,

            fundo: null,

            moldura: null,

            cursor: null

        }

    };


let itemSelecionado = null;

let filtroAtual = 'todos';


// =====================================================
// INICIALIZAÇÃO
// =====================================================

document.addEventListener(
    'DOMContentLoaded',
    function () {

        console.log(
            'Loja carregada ✅'
        );


        // =================================================
        // ELEMENTOS PRINCIPAIS
        // =================================================

        const lojaGrid =
            document.getElementById(
                'lojaGrid'
            );


        const saldoEstrelas =
            document.getElementById(
                'saldoEstrelas'
            );


        const filtrosBtns =
            document.querySelectorAll(
                '.filtro-btn'
            );


        // =================================================
        // LOGOUT
        // =================================================

        const iconSair =
            document.getElementById(
                'icon-sair'
            );


        const logoutModal =
            document.getElementById(
                'logout-modal'
            );


        const confirmarLogout =
            document.getElementById(
                'confirm-logout'
            );


        const cancelarLogout =
            document.getElementById(
                'cancel-logout'
            );


        // =================================================
        // MODAL DE COMPRA
        // =================================================

        const modalCompra =
            document.getElementById(
                'modal-compra'
            );


        const modalIcone =
            document.getElementById(
                'modalIcone'
            );


        const modalTitulo =
            document.getElementById(
                'modalTitulo'
            );


        const modalDescricao =
            document.getElementById(
                'modalDescricao'
            );


        const modalPreco =
            document.getElementById(
                'modalPreco'
            );


        const confirmarCompra =
            document.getElementById(
                'confirmar-compra'
            );


        const cancelarCompra =
            document.getElementById(
                'cancelar-compra'
            );


        // =================================================
        // MODAL DE SUCESSO
        // =================================================

        const modalSucesso =
            document.getElementById(
                'modal-sucesso'
            );


        const mensagemSucesso =
            document.getElementById(
                'mensagemSucesso'
            );


        const fecharSucesso =
            document.getElementById(
                'fechar-sucesso'
            );


        // =================================================
        // GARANTIR ESTRUTURA
        // =================================================

        if (
            !Array.isArray(
                lojaData.itens
            )
        ) {

            lojaData.itens = [];

        }


        if (
            !Array.isArray(
                lojaData.itens_comprados
            )
        ) {

            lojaData.itens_comprados =
                [];

        }


        if (
            !lojaData.itens_ativos ||
            typeof lojaData.itens_ativos !==
                'object' ||
            Array.isArray(
                lojaData.itens_ativos
            )
        ) {

            lojaData.itens_ativos = {

                tema: null,

                fundo: null,

                moldura: null,

                cursor: null

            };

        }


        // =================================================
        // ABRIR LOGOUT
        // =================================================

        iconSair?.addEventListener(
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


        // =================================================
        // CONFIRMAR LOGOUT
        // =================================================

        confirmarLogout?.addEventListener(
            'click',
            function () {

                window.location.href =
                    '../login/logout.php';

            }
        );


        // =================================================
        // CANCELAR LOGOUT
        // =================================================

        cancelarLogout?.addEventListener(
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


        // =================================================
        // FECHAR LOGOUT CLICANDO FORA
        // =================================================

        logoutModal?.addEventListener(
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
        // ATUALIZAR SALDO
        // =================================================

        function atualizarSaldo() {

            if (
                !saldoEstrelas
            ) {

                return;

            }


            saldoEstrelas.textContent =
                Number(
                    lojaData.estrelas ||
                    0
                );

        }


        // =================================================
        // VERIFICAR ITEM COMPRADO
        // =================================================

        function verificarSeComprado(
            itemId
        ) {

            return (
                lojaData
                    .itens_comprados
                    .includes(
                        itemId
                    )
            );

        }


        // =================================================
        // IDENTIFICAR TIPO EQUIPÁVEL
        // =================================================

        function getTipoEquipavel(
            item
        ) {

            switch (
                item.categoria
            ) {

                case 'temas':

                    return 'tema';


                case 'fundos':

                    return 'fundo';


                case 'molduras':

                    return 'moldura';


                case 'especiais':

                    return 'cursor';


                default:

                    return null;

            }

        }


        // =================================================
        // VERIFICAR SE ITEM ESTÁ ATIVO
        // =================================================

        function verificarSeAtivo(
            item
        ) {

            const tipo =
                getTipoEquipavel(
                    item
                );


            if (
                !tipo
            ) {

                return false;

            }


            return (
                lojaData
                    .itens_ativos?.[
                        tipo
                    ] ===
                item.id
            );

        }


        // =================================================
        // STATUS DO ITEM
        // =================================================

        function getStatusItem(
            item
        ) {

            const comprado =
                verificarSeComprado(
                    item.id
                );


            const estrelas =
                Number(
                    lojaData.estrelas ||
                    0
                );


            const preco =
                Number(
                    item.preco ||
                    0
                );


            // =============================================
            // JÁ COMPRADO
            // =============================================

            if (
                comprado
            ) {

                return {

                    status:
                        'comprado',

                    texto:
                        '✅ Comprado',

                    classe:
                        'comprado'

                };

            }


            // =============================================
            // TEM SALDO
            // =============================================

            if (
                estrelas >=
                preco
            ) {

                return {

                    status:
                        'disponivel',

                    texto:
                        '🛒 Comprar',

                    classe:
                        'disponivel'

                };

            }


            // =============================================
            // SEM SALDO
            // =============================================

            return {

                status:
                    'insuficiente',

                texto:
                    '⭐ Insuficiente',

                classe:
                    'insuficiente'

            };

        }


        // =================================================
        // RENDERIZAR ITENS
        // =================================================

        function renderizarItens(
            filtro = 'todos'
        ) {

            if (
                !lojaGrid
            ) {

                return;

            }


            lojaGrid.innerHTML =
                '';


            let itensFiltrados =
                lojaData.itens;


            // =============================================
            // MEUS ITENS
            // =============================================

            if (
                filtro ===
                'comprados'
            ) {

                itensFiltrados =
                    lojaData.itens.filter(
                        item =>
                            verificarSeComprado(
                                item.id
                            )
                    );

            }


            // =============================================
            // FILTRO DE CATEGORIA
            // =============================================

            else if (
                filtro !==
                'todos'
            ) {

                itensFiltrados =
                    lojaData.itens.filter(
                        item =>
                            item.categoria ===
                            filtro
                    );

            }


            // =============================================
            // SEM ITENS
            // =============================================

            if (
                itensFiltrados.length ===
                0
            ) {

                lojaGrid.innerHTML = `

                    <div class="sem-itens">

                        <i
                            class="fa-regular fa-face-frown"
                        ></i>

                        <p>
                            Nenhum item encontrado nesta categoria.
                        </p>

                    </div>

                `;


                return;

            }


            // =============================================
            // CRIAR CARDS
            // =============================================

            itensFiltrados.forEach(
                function (
                    item
                ) {

                    const card =
                        document.createElement(
                            'div'
                        );


                    card.className =
                        'item-card';

                    if (
                        item.categoria ===
                        'emojis'
                    ) {
                        card.classList.add(
                            'item-card-emoji'
                        );
                    }


                    const status =
                        getStatusItem(
                            item
                        );


                    const comprado =
                        verificarSeComprado(
                            item.id
                        );


                    const ativo =
                        verificarSeAtivo(
                            item
                        );


                    const tipoEquipavel =
                        getTipoEquipavel(
                            item
                        );


                    const temImagem =
                        item.imagem &&
                        String(
                            item.imagem
                        ).trim() !==
                            '';


                    // =====================================
                    // HTML DO CARD
                    // =====================================

                    card.innerHTML = `

                        <div class="icone">

                            ${
                                temImagem

                                    ? `

                                        <img
                                            src="${item.imagem}"
                                            alt="${item.nome}"
                                            class="item-imagem${item.categoria === 'emojis' ? ' emoji-imagem' : ''}"
                                        >

                                    `

                                    : `

                                        <i
                                            class="${item.icone || 'fa-solid fa-gift'}"
                                        ></i>

                                    `
                            }

                        </div>


                        <div class="nome">

                            ${item.nome}

                        </div>


                        <div class="descricao">

                            ${item.descricao}

                        </div>


                        <div class="preco">

                            <i
                                class="fa-solid fa-star"
                            ></i>

                            ${item.preco}

                        </div>


                        <div
                            class="status ${status.classe}"
                        >

                            ${status.texto}

                        </div>


                        ${
                            ativo

                                ? `

                                    <div class="badge-ativo">

                                        ✔️ ATIVO

                                    </div>

                                `

                                : ''
                        }

                    `;


                    // =====================================
                    // ITEM COMPRADO E EQUIPÁVEL
                    // =====================================

                    if (
                        comprado &&
                        tipoEquipavel
                    ) {

                        card.style.cursor =
                            'pointer';


                        card.addEventListener(
                            'click',
                            function () {

                                ativarItem(
                                    item
                                );

                            }
                        );

                    }


                    // =====================================
                    // EMOJI JÁ COMPRADO
                    // =====================================

                    else if (
                        comprado
                    ) {

                        card.style.cursor =
                            'default';

                    }


                    // =====================================
                    // DISPONÍVEL PARA COMPRA
                    // =====================================

                    else if (
                        status.status ===
                        'disponivel'
                    ) {

                        card.style.cursor =
                            'pointer';


                        card.addEventListener(
                            'click',
                            function () {

                                abrirModalCompra(
                                    item
                                );

                            }
                        );

                    }


                    // =====================================
                    // SEM SALDO
                    // =====================================

                    else {

                        card.style.cursor =
                            'not-allowed';

                    }


                    lojaGrid.appendChild(
                        card
                    );

                }
            );

        }


        // =================================================
        // ABRIR MODAL DE COMPRA
        // =================================================

        function abrirModalCompra(
            item
        ) {

            itemSelecionado =
                item;


            // =============================================
            // IMAGEM / ÍCONE
            // =============================================

            if (
                modalIcone
            ) {

                modalIcone.classList.remove(
                    'modal-icon-emoji'
                );

                if (
                    item.categoria ===
                    'emojis'
                ) {
                    modalIcone.classList.add(
                        'modal-icon-emoji'
                    );
                }

                const temImagem =
                    item.imagem &&
                    String(
                        item.imagem
                    ).trim() !==
                        '';


                modalIcone.innerHTML =

                    temImagem

                        ? `

                            <img
                                src="${item.imagem}"
                                alt="${item.nome}"
                                class="modal-item-imagem${item.categoria === 'emojis' ? ' modal-emoji-imagem' : ''}"
                            >

                        `

                        : `

                            <i
                                class="${item.icone || 'fa-solid fa-gift'}"
                            ></i>

                        `;

            }


            // =============================================
            // TÍTULO
            // =============================================

            if (
                modalTitulo
            ) {

                modalTitulo.textContent =
                    `Comprar ${item.nome}`;

            }


            // =============================================
            // DESCRIÇÃO
            // =============================================

            if (
                modalDescricao
            ) {

                modalDescricao.textContent =
                    item.descricao;

            }


            // =============================================
            // PREÇO
            // =============================================

            if (
                modalPreco
            ) {

                modalPreco.textContent =
                    item.preco;

            }


            // =============================================
            // MOSTRAR
            // =============================================

            if (
                modalCompra
            ) {

                modalCompra.style.display =
                    'flex';


                document.body.style.overflow =
                    'hidden';

            }

        }


        // =================================================
        // FECHAR MODAL DE COMPRA
        // =================================================

        function fecharModalCompra() {

            if (
                modalCompra
            ) {

                modalCompra.style.display =
                    'none';

            }


            document.body.style.overflow =
                '';


            itemSelecionado =
                null;

        }


        // =================================================
        // APLICAR DADOS DO SERVIDOR
        // =================================================

        function aplicarDadosServidor(
            resposta
        ) {

            const dados =
                resposta?.dados;


            if (
                !dados
            ) {

                return;

            }


            // =============================================
            // SALDO
            // =============================================

            if (
                dados.estrelas !==
                undefined
            ) {

                lojaData.estrelas =
                    Number(
                        dados.estrelas ||
                        0
                    );

            }


            // =============================================
            // ITENS COMPRADOS
            // =============================================

            if (
                Array.isArray(
                    dados.itens_comprados
                )
            ) {

                lojaData.itens_comprados =
                    dados.itens_comprados;

            }


            // =============================================
            // ITENS ATIVOS
            // =============================================

            if (
                dados.itens_ativos &&
                typeof dados.itens_ativos ===
                    'object'
            ) {

                lojaData.itens_ativos =
                    dados.itens_ativos;

            }


            atualizarSaldo();


            renderizarItens(
                filtroAtual
            );


            atualizarCompatibilidadePerfil();

        }


        // =================================================
        // ENVIAR AÇÃO PARA O SERVIDOR
        // =================================================

        async function enviarAcaoLoja(
            acao,
            itemId
        ) {

            const resposta =
                await fetch(
                    LOJA_ACTION_URL,
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

                                acao:
                                    acao,

                                item_id:
                                    itemId

                            })

                    }
                );


            let dados;


            // =============================================
            // LER JSON
            // =============================================

            try {

                dados =
                    await resposta.json();

            } catch (
                erro
            ) {

                throw new Error(
                    'O servidor retornou uma resposta inválida.'
                );

            }


            // =============================================
            // ATUALIZAR DADOS
            // =============================================

            aplicarDadosServidor(
                dados
            );


            // =============================================
            // ERRO DO SERVIDOR
            // =============================================

            if (
                !resposta.ok ||
                !dados.sucesso
            ) {

                throw new Error(
                    dados.mensagem ||
                    'Não foi possível concluir a operação.'
                );

            }


            return dados;

        }


        // =================================================
        // COMPRAR ITEM
        // =================================================

        async function comprarItem() {

            if (
                !itemSelecionado
            ) {

                return;

            }


            const itemCompra = {

                ...itemSelecionado

            };


            // =============================================
            // BLOQUEAR BOTÃO
            // =============================================

            if (
                confirmarCompra
            ) {

                confirmarCompra.disabled =
                    true;

            }


            try {

                // =========================================
                // MANDA APENAS O ID
                // =========================================

                const resposta =
                    await enviarAcaoLoja(
                        'comprar',
                        itemCompra.id
                    );


                fecharModalCompra();


                mostrarSucesso(

                    `🎉 Você comprou "${itemCompra.nome}"! Saldo atual: ${resposta.dados.estrelas} ⭐`

                );

            } catch (
                erro
            ) {

                mostrarSucesso(

                    erro.message ||
                    'Não foi possível realizar a compra.'

                );

            } finally {

                if (
                    confirmarCompra
                ) {

                    confirmarCompra.disabled =
                        false;

                }

            }

        }


        // =================================================
        // ATIVAR ITEM
        // =================================================

        async function ativarItem(
            item
        ) {

            if (
                !verificarSeComprado(
                    item.id
                )
            ) {

                return;

            }


            try {

                await enviarAcaoLoja(
                    'ativar',
                    item.id
                );


                mostrarSucesso(

                    `✅ "${item.nome}" ativado!`

                );

            } catch (
                erro
            ) {

                mostrarSucesso(

                    erro.message ||
                    'Não foi possível ativar o item.'

                );

            }

        }


        // =================================================
        // MOSTRAR SUCESSO / AVISO
        // =================================================

        function mostrarSucesso(
            mensagem
        ) {

            if (
                mensagemSucesso
            ) {

                mensagemSucesso.textContent =
                    mensagem;

            }


            if (
                modalSucesso
            ) {

                modalSucesso.style.display =
                    'flex';


                document.body.style.overflow =
                    'hidden';

            }

        }


        // =================================================
        // FECHAR MODAL DE SUCESSO
        // =================================================

        function fecharSucessoModal() {

            if (
                modalSucesso
            ) {

                modalSucesso.style.display =
                    'none';

            }


            document.body.style.overflow =
                '';

        }


        // =================================================
        // COMPATIBILIDADE TEMPORÁRIA COM PERFIL
        // =================================================
        //
        // O perfil será ligado diretamente ao loja.json
        // depois.
        //
        // Por enquanto salvamos também no sessionStorage
        // para não quebrar páginas que ainda usam isso.
        //
        // =================================================

        function atualizarCompatibilidadePerfil() {

            const itensComprados =
                lojaData.itens_comprados ||
                [];


            const itensDoUsuario =
                lojaData.itens.filter(
                    item =>
                        itensComprados.includes(
                            item.id
                        )
                );


            // =============================================
            // SESSION STORAGE
            // =============================================

            try {

                sessionStorage.setItem(

                    'itens_loja',

                    JSON.stringify(
                        itensDoUsuario
                    )

                );


                sessionStorage.setItem(

                    'itens_ativos_loja',

                    JSON.stringify(
                        lojaData.itens_ativos ||
                        {}
                    )

                );


                sessionStorage.setItem(

                    'estrelas_total',

                    String(
                        lojaData.estrelas ||
                        0
                    )

                );


                sessionStorage.setItem(

                    'loja_atualizada',

                    Date.now().toString()

                );


            } catch (
                erro
            ) {

                console.error(

                    'Erro no sessionStorage:',

                    erro

                );

            }


            // =============================================
            // BROADCAST CHANNEL
            // =============================================

            try {

                const channel =
                    new BroadcastChannel(
                        'foag_loja'
                    );


                channel.postMessage({

                    type:
                        'LOJA_ATUALIZADA',

                    itens_ativos:
                        lojaData.itens_ativos,

                    estrelas:
                        lojaData.estrelas

                });


                setTimeout(
                    () => {

                        channel.close();

                    },
                    100
                );


            } catch (
                erro
            ) {

                // BroadcastChannel é opcional.

            }

        }


        // =================================================
        // FILTROS
        // =================================================

        filtrosBtns.forEach(
            function (
                btn
            ) {

                btn.addEventListener(
                    'click',
                    function () {

                        // =================================
                        // REMOVER ACTIVE
                        // =================================

                        filtrosBtns.forEach(
                            function (
                                botao
                            ) {

                                botao.classList.remove(
                                    'active'
                                );

                            }
                        );


                        // =================================
                        // ATIVAR CLICADO
                        // =================================

                        this.classList.add(
                            'active'
                        );


                        filtroAtual =
                            this.dataset.filtro ||
                            'todos';


                        renderizarItens(
                            filtroAtual
                        );

                    }
                );

            }
        );


        // =================================================
        // CONFIRMAR COMPRA
        // =================================================

        confirmarCompra?.addEventListener(
            'click',
            comprarItem
        );


        // =================================================
        // CANCELAR COMPRA
        // =================================================

        cancelarCompra?.addEventListener(
            'click',
            fecharModalCompra
        );


        // =================================================
        // FECHAR COMPRA CLICANDO FORA
        // =================================================

        modalCompra?.addEventListener(
            'click',
            function (
                evento
            ) {

                if (
                    evento.target ===
                    modalCompra
                ) {

                    fecharModalCompra();

                }

            }
        );


        // =================================================
        // FECHAR MODAL SUCESSO
        // =================================================

        fecharSucesso?.addEventListener(
            'click',
            fecharSucessoModal
        );


        // =================================================
        // FECHAR SUCESSO CLICANDO FORA
        // =================================================

        modalSucesso?.addEventListener(
            'click',
            function (
                evento
            ) {

                if (
                    evento.target ===
                    modalSucesso
                ) {

                    fecharSucessoModal();

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


                fecharModalCompra();


                fecharSucessoModal();


                if (
                    logoutModal
                ) {

                    logoutModal.style.display =
                        'none';

                }


                document.body.style.overflow =
                    '';

            }
        );


        // =================================================
        // INICIAR LOJA
        // =================================================

        atualizarSaldo();


        renderizarItens(
            'todos'
        );


        atualizarCompatibilidadePerfil();


        console.log(
            'Loja pronta ✅'
        );

    }
);