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

let ultimoSaldoRenderizado = null;
let toastTimer = null;

const NOMES_CATEGORIAS_LOJA = {
    temas: 'Tema',
    emojis: 'Emoji',
    fundos: 'Fundo',
    molduras: 'Moldura',
    especiais: 'Cursor'
};

const ICONES_CATEGORIAS_LOJA = {
    temas: 'fa-solid fa-palette',
    emojis: 'fa-solid fa-face-smile',
    fundos: 'fa-solid fa-image',
    molduras: 'fa-solid fa-id-badge',
    especiais: 'fa-solid fa-arrow-pointer'
};


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


        const colecaoHeaderContador =
            document.getElementById(
                'colecaoHeaderContador'
            );

        const colecaoHeaderProgresso =
            document.getElementById(
                'colecaoHeaderProgresso'
            );

        const colecaoPainel =
            document.getElementById(
                'colecaoPainel'
            );

        const colecaoPainelContador =
            document.getElementById(
                'colecaoPainelContador'
            );

        const colecaoPainelPercentual =
            document.getElementById(
                'colecaoPainelPercentual'
            );

        const colecaoPainelProgresso =
            document.getElementById(
                'colecaoPainelProgresso'
            );

        const colecaoEmUso =
            document.getElementById(
                'colecaoEmUso'
            );

        const lojaVitrines =
            document.getElementById(
                'lojaVitrines'
            );

        const vitrineNovidades =
            document.getElementById(
                'vitrineNovidades'
            );

        const vitrineDestaques =
            document.getElementById(
                'vitrineDestaques'
            );

        const vitrineNovidadesItens =
            document.getElementById(
                'vitrineNovidadesItens'
            );

        const vitrineDestaquesItens =
            document.getElementById(
                'vitrineDestaquesItens'
            );

        const modalPreviewItem =
            document.getElementById(
                'modal-preview-item'
            );

        const previewItemVisual =
            document.getElementById(
                'previewItemVisual'
            );

        const previewTitulo =
            document.getElementById(
                'previewTitulo'
            );

        const previewDescricao =
            document.getElementById(
                'previewDescricao'
            );

        const previewCategoria =
            document.getElementById(
                'previewCategoria'
            );

        const lojaToast =
            document.getElementById(
                'lojaToast'
            );

        const lojaToastTitulo =
            document.getElementById(
                'lojaToastTitulo'
            );

        const lojaToastTexto =
            document.getElementById(
                'lojaToastTexto'
            );

        const lojaToastIcone =
            document.getElementById(
                'lojaToastIcone'
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


        const modalCategoria =
            document.getElementById(
                'modalCategoria'
            );

        const modalSaldoAtual =
            document.getElementById(
                'modalSaldoAtual'
            );

        const modalSaldoRestante =
            document.getElementById(
                'modalSaldoRestante'
            );

        const experimentarItemModal =
            document.getElementById(
                'experimentar-item-modal'
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
        // HELPERS VISUAIS
        // =================================================

        function nomeCategoriaLoja(
            categoria
        ) {
            return (
                NOMES_CATEGORIAS_LOJA[
                    categoria
                ] ||
                'Item'
            );
        }


        function obterRaridadeItem(
            item
        ) {
            const raridade =
                String(
                    item?.raridade ||
                    ''
                )
                    .trim()
                    .toLowerCase();

            return raridade;
        }


        function itemTemPreview(
            item
        ) {
            return [
                'temas',
                'fundos',
                'molduras',
                'emojis',
                'especiais'
            ].includes(
                item?.categoria
            );
        }


        function construirImagemItem(
            item,
            classe = 'item-imagem'
        ) {
            const temImagem =
                item?.imagem &&
                String(
                    item.imagem
                ).trim() !== '';

            if (temImagem) {
                return `
                    <img
                        src="${item.imagem}"
                        alt="${item.nome || 'Item'}"
                        class="${classe}"
                    >
                `;
            }

            return `
                <i
                    class="${item?.icone || 'fa-solid fa-gift'}"
                ></i>
            `;
        }


        function mostrarToast(
            titulo,
            texto,
            tipo = 'sucesso'
        ) {
            if (
                !lojaToast
            ) {
                return;
            }

            if (
                toastTimer
            ) {
                clearTimeout(
                    toastTimer
                );
            }

            lojaToast.className =
                `loja-toast mostrar ${tipo}`;

            if (
                lojaToastTitulo
            ) {
                lojaToastTitulo.textContent =
                    titulo;
            }

            if (
                lojaToastTexto
            ) {
                lojaToastTexto.textContent =
                    texto;
            }

            if (
                lojaToastIcone
            ) {
                lojaToastIcone.innerHTML =
                    tipo === 'erro'
                        ? '<i class="fa-solid fa-triangle-exclamation"></i>'
                        : '<i class="fa-solid fa-check"></i>';
            }

            toastTimer =
                setTimeout(
                    () => {
                        lojaToast.classList.remove(
                            'mostrar'
                        );
                    },
                    4200
                );
        }


        function fecharToast() {
            lojaToast?.classList.remove(
                'mostrar'
            );

            if (
                toastTimer
            ) {
                clearTimeout(
                    toastTimer
                );
            }
        }


        function atualizarResumoColecao() {
            const total =
                lojaData.itens.length;

            const comprados =
                lojaData.itens_comprados.length;

            const percentual =
                total > 0
                    ? Math.round(
                        (
                            comprados /
                            total
                        ) * 100
                    )
                    : 0;

            if (
                colecaoHeaderContador
            ) {
                colecaoHeaderContador.textContent =
                    `${comprados} / ${total}`;
            }

            if (
                colecaoHeaderProgresso
            ) {
                colecaoHeaderProgresso.style.width =
                    `${percentual}%`;
            }

            if (
                colecaoPainelContador
            ) {
                colecaoPainelContador.textContent =
                    `${comprados} / ${total}`;
            }

            if (
                colecaoPainelPercentual
            ) {
                colecaoPainelPercentual.textContent =
                    `${percentual}%`;
            }

            if (
                colecaoPainelProgresso
            ) {
                colecaoPainelProgresso.style.width =
                    `${percentual}%`;
            }

            if (
                colecaoPainel
            ) {
                colecaoPainel.hidden =
                    filtroAtual !==
                    'comprados';
            }

            if (
                colecaoEmUso
            ) {
                const ativos =
                    Object.values(
                        lojaData.itens_ativos ||
                        {}
                    )
                        .filter(Boolean)
                        .map(
                            id =>
                                lojaData.itens.find(
                                    item =>
                                        item.id ===
                                        id
                                )
                        )
                        .filter(Boolean);

                colecaoEmUso.innerHTML =
                    ativos.length
                        ? `
                            <span class="colecao-em-uso-label">
                                Em uso
                            </span>

                            ${ativos
                                .map(
                                    item => `
                                        <span class="colecao-em-uso-item">
                                            <i class="${ICONES_CATEGORIAS_LOJA[item.categoria] || 'fa-solid fa-circle'}"></i>
                                            ${item.nome}
                                        </span>
                                    `
                                )
                                .join('')}
                        `
                        : `
                            <span class="colecao-em-uso-vazio">
                                Nenhuma personalização equipada.
                            </span>
                        `;
            }
        }


        function atualizarContadoresFiltros() {
            const contagem =
                {
                    todos:
                        lojaData.itens.length,

                    comprados:
                        lojaData.itens_comprados.length
                };

            lojaData.itens.forEach(
                item => {
                    contagem[
                        item.categoria
                    ] =
                        (
                            contagem[
                                item.categoria
                            ] ||
                            0
                        ) + 1;
                }
            );

            filtrosBtns.forEach(
                botao => {
                    let contador =
                        botao.querySelector(
                            '.filtro-count'
                        );

                    if (
                        !contador
                    ) {
                        contador =
                            document.createElement(
                                'span'
                            );

                        contador.className =
                            'filtro-count';

                        botao.appendChild(
                            contador
                        );
                    }

                    contador.textContent =
                        String(
                            contagem[
                                botao.dataset.filtro
                            ] ||
                            0
                        );
                }
            );
        }


        function criarMiniCardVitrine(
            item
        ) {
            const card =
                document.createElement(
                    'button'
                );

            card.type =
                'button';

            card.className =
                `vitrine-mini-card categoria-${item.categoria}`;

            card.innerHTML = `
                <span class="vitrine-mini-imagem">
                    ${construirImagemItem(
                        item,
                        'vitrine-mini-img'
                    )}
                </span>

                <span class="vitrine-mini-info">
                    <small>
                        ${nomeCategoriaLoja(
                            item.categoria
                        )}
                    </small>

                    <strong>
                        ${item.nome}
                    </strong>

                    <span>
                        <i class="fa-solid fa-star"></i>
                        ${Number(
                            item.preco ||
                            0
                        )}
                    </span>
                </span>
            `;

            card.addEventListener(
                'click',
                function () {
                    abrirPreviewItem(
                        item
                    );
                }
            );

            return card;
        }


        function renderizarVitrines() {
            if (
                !lojaVitrines
            ) {
                return;
            }

            const novidades =
                lojaData.itens
                    .filter(
                        item =>
                            item.novo === true
                    )
                    .slice(
                        0,
                        4
                    );

            const destaques =
                lojaData.itens
                    .filter(
                        item =>
                            item.destaque === true
                    )
                    .slice(
                        0,
                        4
                    );

            if (
                vitrineNovidades &&
                vitrineNovidadesItens
            ) {
                vitrineNovidades.hidden =
                    novidades.length === 0;

                vitrineNovidadesItens.innerHTML =
                    '';

                novidades.forEach(
                    item =>
                        vitrineNovidadesItens.appendChild(
                            criarMiniCardVitrine(
                                item
                            )
                        )
                );
            }

            if (
                vitrineDestaques &&
                vitrineDestaquesItens
            ) {
                vitrineDestaques.hidden =
                    destaques.length === 0;

                vitrineDestaquesItens.innerHTML =
                    '';

                destaques.forEach(
                    item =>
                        vitrineDestaquesItens.appendChild(
                            criarMiniCardVitrine(
                                item
                            )
                        )
                );
            }

            lojaVitrines.hidden =
                novidades.length === 0 &&
                destaques.length === 0;
        }


        function renderizarSkeleton() {
            if (
                !lojaGrid
            ) {
                return;
            }

            lojaGrid.innerHTML =
                Array.from(
                    {
                        length:
                            8
                    }
                )
                    .map(
                        () => `
                            <div class="item-skeleton" aria-hidden="true">
                                <span class="skeleton-visual"></span>
                                <span class="skeleton-linha grande"></span>
                                <span class="skeleton-linha"></span>
                                <span class="skeleton-linha curta"></span>
                            </div>
                        `
                    )
                    .join('');
        }


        function renderizarErroLoja() {
            if (
                !lojaGrid
            ) {
                return;
            }

            lojaGrid.innerHTML = `
                <div class="sem-itens erro-loja">
                    <div class="sem-itens-icone">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <h3>
                        Não conseguimos carregar a Loja
                    </h3>

                    <p>
                        Tente novamente. Se o problema continuar,
                        atualize a página.
                    </p>

                    <button
                        type="button"
                        class="btn-ver-todos-itens"
                        id="btnTentarNovamenteLoja"
                    >
                        <i class="fa-solid fa-rotate-right"></i>
                        Tentar novamente
                    </button>
                </div>
            `;

            document
                .getElementById(
                    'btnTentarNovamenteLoja'
                )
                ?.addEventListener(
                    'click',
                    function () {
                        renderizarItens(
                            filtroAtual
                        );
                    }
                );
        }


        function construirPreviewPalco(
            item
        ) {
            if (
                !previewItemVisual
            ) {
                return;
            }

            previewItemVisual.className =
                `preview-item-visual categoria-${item.categoria}`;

            const imagem =
                construirImagemItem(
                    item,
                    'preview-item-img'
                );

            if (
                item.categoria ===
                'molduras'
            ) {
                previewItemVisual.innerHTML = `
                    <div class="preview-avatar">
                        <i class="fa-solid fa-user"></i>
                        ${imagem}
                    </div>
                `;
            } else if (
                item.categoria ===
                'temas'
            ) {
                previewItemVisual.innerHTML = `
                    <div class="preview-mini-interface">
                        <span class="preview-mini-header"></span>
                        <span class="preview-mini-menu"></span>
                        <span class="preview-mini-conteudo">
                            ${imagem}
                        </span>
                    </div>
                `;
            } else if (
                item.categoria ===
                'fundos'
            ) {
                previewItemVisual.innerHTML = `
                    <div class="preview-fundo-tela">
                        ${imagem}
                        <span class="preview-fundo-avatar">
                            <i class="fa-solid fa-user"></i>
                        </span>
                    </div>
                `;
            } else if (
                item.categoria ===
                'especiais' &&
                item.imagem_click
            ) {
                previewItemVisual.innerHTML = `
                    <div class="preview-cursor-duplo">
                        <div class="preview-cursor-estado">
                            <span>Normal</span>
                            ${imagem}
                        </div>
                        <div class="preview-cursor-estado">
                            <span>Ao clicar</span>
                            <img src="${item.imagem_click}" alt="${item.nome} ao clicar" class="preview-item-img">
                        </div>
                    </div>
                `;
            } else {
                previewItemVisual.innerHTML =
                    imagem;
            }
        }


        function abrirPreviewItem(
            item
        ) {
            if (
                !modalPreviewItem
            ) {
                return;
            }

            construirPreviewPalco(
                item
            );

            if (
                previewTitulo
            ) {
                previewTitulo.textContent =
                    item.nome ||
                    'Visualizar item';
            }

            if (
                previewDescricao
            ) {
                previewDescricao.textContent =
                    item.descricao ||
                    'Veja o item em tamanho maior.';
            }

            if (
                previewCategoria
            ) {
                previewCategoria.textContent =
                    nomeCategoriaLoja(
                        item.categoria
                    );
            }

            modalPreviewItem.style.display =
                'flex';

            document.body.style.overflow =
                'hidden';
        }


        function fecharPreviewItem() {
            if (
                modalPreviewItem
            ) {
                modalPreviewItem.style.display =
                    'none';
            }

            if (
                !modalCompra ||
                modalCompra.style.display !==
                    'flex'
            ) {
                document.body.style.overflow =
                    '';
            }
        }


        // =================================================
        // ATUALIZAR SALDO
        // =================================================

        function atualizarSaldo() {

            if (
                !saldoEstrelas
            ) {

                return;

            }


            const novoSaldo =
                Number(
                    lojaData.estrelas ||
                    0
                );


            if (
                ultimoSaldoRenderizado !==
                    null &&
                novoSaldo >
                    ultimoSaldoRenderizado
            ) {
                const caixaSaldo =
                    saldoEstrelas.closest(
                        '.saldo-estrelas'
                    );

                caixaSaldo?.classList.add(
                    'saldo-animando'
                );

                setTimeout(
                    () =>
                        caixaSaldo?.classList.remove(
                            'saldo-animando'
                        ),
                    750
                );
            }


            saldoEstrelas.textContent =
                novoSaldo;


            ultimoSaldoRenderizado =
                novoSaldo;

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
        // SINCRONIZAR CURSOR GLOBAL
        // =================================================

        function sincronizarCursorGlobal() {

            const cursorAtivo =
                lojaData?.itens_ativos?.cursor ||
                null;


            if (!cursorAtivo) {
                return;
            }


            // Guarda localmente para as outras páginas
            // conseguirem reaplicar o cursor.
            try {
                localStorage.setItem(
                    'foag_cursor_ativo',
                    cursorAtivo
                );
            } catch (erro) {
                console.warn(
                    'FOAG: não foi possível salvar o cursor localmente.',
                    erro
                );
            }


            // Aplica imediatamente, se o sistema global
            // de cursores já estiver carregado.
            if (
                typeof window.ativarCursorFoag ===
                'function'
            ) {

                window.ativarCursorFoag(
                    cursorAtivo
                );

                return;
            }


            // Segundo caminho de compatibilidade.
            if (
                typeof window.aplicarCursorFoag ===
                'function'
            ) {

                window.aplicarCursorFoag(
                    cursorAtivo
                );

                return;
            }


            // Último fallback: avisa o sistema global
            // através de um evento.
            window.dispatchEvent(
                new CustomEvent(
                    'foag:cursor-alterado',
                    {
                        detail: {
                            cursor: cursorAtivo
                        }
                    }
                )
            );

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
                        'Comprado',

                    classe:
                        'comprado',

                    icone:
                        'fa-solid fa-bag-shopping'

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
                        'Comprar',

                    classe:
                        'disponivel',

                    icone:
                        'fa-solid fa-cart-shopping'

                };

            }


            // =============================================
            // SEM SALDO
            // =============================================

            return {

                status:
                    'insuficiente',

                texto:
                    'Saldo insuficiente',

                classe:
                    'insuficiente',

                icone:
                    'fa-solid fa-lock'

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
            // SEM ITENS — ESTADO VAZIO BONITO
            // =============================================

            if (
                itensFiltrados.length ===
                0
            ) {

                const estadosVazios = {

                    todos: {
                        icone:
                            'fa-solid fa-store',
                        titulo:
                            'A loja está sendo preparada',
                        texto:
                            'Novos itens aparecerão aqui quando forem adicionados ao catálogo.'
                    },

                    comprados: {
                        icone:
                            'fa-solid fa-bag-shopping',
                        titulo:
                            'Sua coleção ainda está vazia',
                        texto:
                            'Explore a loja e use suas estrelas para começar sua coleção.'
                    },

                    temas: {
                        icone:
                            'fa-solid fa-palette',
                        titulo:
                            'Nenhum tema por aqui',
                        texto:
                            'Assim que novos temas chegarem, eles aparecerão nesta seção.'
                    },

                    emojis: {
                        icone:
                            'fa-regular fa-face-smile',
                        titulo:
                            'Nenhum emoji por aqui',
                        texto:
                            'Novos emojis colecionáveis aparecerão aqui quando estiverem disponíveis.'
                    },

                    fundos: {
                        icone:
                            'fa-regular fa-image',
                        titulo:
                            'Nenhum fundo por aqui',
                        texto:
                            'Os fundos disponíveis para personalização aparecerão nesta seção.'
                    },

                    molduras: {
                        icone:
                            'fa-regular fa-id-badge',
                        titulo:
                            'Nenhuma moldura por aqui',
                        texto:
                            'Novas molduras para o seu perfil aparecerão aqui.'
                    },

                    especiais: {
                        icone:
                            'fa-solid fa-arrow-pointer',
                        titulo:
                            'Nenhum cursor por aqui',
                        texto:
                            'Novos cursores personalizados aparecerão nesta seção.'
                    }

                };


                const estado =
                    estadosVazios[filtro] ||
                    estadosVazios.todos;


                const podeVoltar =
                    filtro !==
                    'todos';


                lojaGrid.innerHTML = `

                    <div class="sem-itens">

                        <div class="sem-itens-icone">
                            <i class="${estado.icone}"></i>
                        </div>

                        <h3>
                            ${estado.titulo}
                        </h3>

                        <p>
                            ${estado.texto}
                        </p>

                        ${
                            podeVoltar
                                ? `
                                    <button
                                        type="button"
                                        class="btn-ver-todos-itens"
                                        id="btnVerTodosItens"
                                    >
                                        <i class="fa-solid fa-arrow-left"></i>
                                        Ver todos os itens
                                    </button>
                                `
                                : ''
                        }

                    </div>

                `;


                const btnVerTodosItens =
                    document.getElementById(
                        'btnVerTodosItens'
                    );


                btnVerTodosItens?.addEventListener(
                    'click',
                    function () {

                        filtroAtual =
                            'todos';


                        filtrosBtns.forEach(
                            function (
                                botao
                            ) {

                                botao.classList.toggle(
                                    'active',
                                    botao.dataset.filtro ===
                                        'todos'
                                );

                            }
                        );


                        renderizarItens(
                            'todos'
                        );

                    }
                );


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
                        `item-card categoria-${item.categoria}`;

                    if (
                        item.categoria ===
                        'emojis'
                    ) {
                        card.classList.add(
                            'item-card-emoji'
                        );
                    }

                    const raridade =
                        obterRaridadeItem(
                            item
                        );


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


                    const statusVisual =
                        ativo

                            ? {
                                status:
                                    'ativo',

                                texto:
                                    'Ativo',

                                classe:
                                    'ativo',

                                icone:
                                    'fa-solid fa-check'
                            }

                            : status;


                    const tipoEquipavel =
                        getTipoEquipavel(
                            item
                        );


                    if (
                        ativo
                    ) {
                        card.classList.add(
                            'item-ativo-card'
                        );
                    }


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

                        <div class="item-card-topo-meta">
                            <span class="item-categoria-label">
                                ${nomeCategoriaLoja(
                                    item.categoria
                                )}
                            </span>

                            <span class="item-selos">
                                ${
                                    item.novo ===
                                    true
                                        ? `
                                            <span class="item-selo novo">
                                                Novo
                                            </span>
                                        `
                                        : ''
                                }

                                ${
                                    item.destaque ===
                                    true
                                        ? `
                                            <span class="item-selo destaque">
                                                Destaque
                                            </span>
                                        `
                                        : ''
                                }

                                ${
                                    raridade
                                        ? `
                                            <span class="item-raridade ${raridade}">
                                                ${raridade}
                                            </span>
                                        `
                                        : ''
                                }
                            </span>
                        </div>

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

                            ${item.descricao || ''}

                        </div>


                        <div class="item-card-rodape">

                            <div class="preco">

                                <i
                                    class="fa-solid fa-star"
                                ></i>

                                <strong>
                                    ${item.preco}
                                </strong>

                            </div>

                            ${
                                itemTemPreview(
                                    item
                                )
                                    ? `
                                        <button
                                            type="button"
                                            class="btn-preview-card"
                                            aria-label="Visualizar ${item.nome}"
                                        >
                                            <i class="fa-regular fa-eye"></i>
                                            Visualizar
                                        </button>
                                    `
                                    : ''
                            }

                        </div>


                        <div
                            class="status ${statusVisual.classe}"
                            aria-label="${statusVisual.texto}"
                        >

                            <i
                                class="${statusVisual.icone}"
                            ></i>

                            <span>
                                ${statusVisual.texto}
                            </span>

                        </div>

                    `;


                    const btnPreviewCard =
                        card.querySelector(
                            '.btn-preview-card'
                        );


                    btnPreviewCard?.addEventListener(
                        'click',
                        function (
                            evento
                        ) {

                            evento.stopPropagation();

                            abrirPreviewItem(
                                item
                            );

                        }
                    );


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

                modalIcone.className =
                    `modal-icon categoria-${item.categoria}`;

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


            if (
                modalCategoria
            ) {
                modalCategoria.textContent =
                    nomeCategoriaLoja(
                        item.categoria
                    );
            }


            if (
                modalSaldoAtual
            ) {
                modalSaldoAtual.textContent =
                    Number(
                        lojaData.estrelas ||
                        0
                    );
            }


            if (
                modalSaldoRestante
            ) {
                modalSaldoRestante.textContent =
                    Math.max(
                        0,
                        Number(
                            lojaData.estrelas ||
                            0
                        ) -
                        Number(
                            item.preco ||
                            0
                        )
                    );
            }


            if (
                experimentarItemModal
            ) {
                experimentarItemModal.hidden =
                    !itemTemPreview(
                        item
                    );

                experimentarItemModal.onclick =
                    function () {
                        abrirPreviewItem(
                            item
                        );
                    };
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

            atualizarResumoColecao();

            atualizarContadoresFiltros();

            renderizarVitrines();


            renderizarItens(
                filtroAtual
            );


            atualizarCompatibilidadePerfil();

            sincronizarCursorGlobal();

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

                    `"${itemCompra.nome}" foi adicionado à sua coleção. Saldo: ${resposta.dados.estrelas} ⭐`

                );

            } catch (
                erro
            ) {

                mostrarSucesso(

                    erro.message ||
                    'Não foi possível realizar a compra.',
                    'erro'

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


                sincronizarCursorGlobal();


                mostrarSucesso(

                    `"${item.nome}" está em uso agora.`

                );

            } catch (
                erro
            ) {

                mostrarSucesso(

                    erro.message ||
                    'Não foi possível ativar o item.',
                    'erro'

                );

            }

        }


        // =================================================
        // MOSTRAR SUCESSO / AVISO
        // =================================================

        function mostrarSucesso(
            mensagem,
            tipo = 'sucesso'
        ) {

            mostrarToast(
                tipo === 'erro'
                    ? 'Não foi possível'
                    : 'Tudo certo!',
                mensagem,
                tipo
            );

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


                        atualizarResumoColecao();

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


        document
            .getElementById(
                'fechar-compra-x'
            )
            ?.addEventListener(
                'click',
                fecharModalCompra
            );


        document
            .getElementById(
                'fechar-preview-item'
            )
            ?.addEventListener(
                'click',
                fecharPreviewItem
            );


        document
            .getElementById(
                'previewVoltar'
            )
            ?.addEventListener(
                'click',
                fecharPreviewItem
            );


        document
            .getElementById(
                'lojaToastFechar'
            )
            ?.addEventListener(
                'click',
                fecharToast
            );


        document
            .getElementById(
                'fechar-logout-x'
            )
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


        modalPreviewItem?.addEventListener(
            'click',
            function (
                evento
            ) {
                if (
                    evento.target ===
                    modalPreviewItem
                ) {
                    fecharPreviewItem();
                }
            }
        );


        document
            .querySelectorAll(
                '[data-vitrine-filtro]'
            )
            .forEach(
                botao => {
                    botao.addEventListener(
                        'click',
                        function () {
                            filtroAtual =
                                this.dataset.vitrineFiltro ||
                                'todos';

                            filtrosBtns.forEach(
                                filtro => {
                                    filtro.classList.toggle(
                                        'active',
                                        filtro.dataset.filtro ===
                                            filtroAtual
                                    );
                                }
                            );

                            atualizarResumoColecao();

                            renderizarItens(
                                filtroAtual
                            );

                            document
                                .querySelector(
                                    '.loja-filtros'
                                )
                                ?.scrollIntoView(
                                    {
                                        behavior:
                                            'smooth',
                                        block:
                                            'start'
                                    }
                                );
                        }
                    );
                }
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

                fecharPreviewItem();

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

        atualizarResumoColecao();

        atualizarContadoresFiltros();

        renderizarVitrines();

        renderizarSkeleton();


        requestAnimationFrame(
            function () {
                try {
                    renderizarItens(
                        'todos'
                    );
                } catch (
                    erro
                ) {
                    console.error(
                        'Erro ao renderizar Loja:',
                        erro
                    );

                    renderizarErroLoja();
                }
            }
        );


        atualizarCompatibilidadePerfil();

        sincronizarCursorGlobal();


        console.log(
            'Loja pronta ✅'
        );

    }
);