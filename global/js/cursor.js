/* =========================================================
   FOAG — CURSOR PERSONALIZADO GLOBAL
   Versão visual: não depende do limite de cursor do navegador
========================================================= */

(() => {
    "use strict";

    const STORAGE_KEY = "foag_cursor_ativo";

    /* =====================================================
       DESCOBRIR A RAIZ DO PROJETO
    ===================================================== */

    function descobrirBaseProjeto() {
        const script =
            document.currentScript ||
            [...document.scripts].find(script =>
                script.src.includes("/global/js/cursor.js")
            );

        if (!script?.src) {
            return "";
        }

        try {
            const url = new URL(script.src, window.location.href);

            return url.pathname
                .replace(/\/global\/js\/cursor\.js$/, "")
                .replace(/\/+$/, "");
        } catch (erro) {
            return "";
        }
    }

    const BASE_PROJETO = descobrirBaseProjeto();

    function caminho(nome) {
        return `${BASE_PROJETO}/img/loja/cursor/${nome}`;
    }

    /* =====================================================
       CURSORES
    ===================================================== */

    const CURSORES_FOAG = {
        cursor_galaxia: {
            normal: caminho("cursor_galaxia.png"),
            clique: caminho("cursormao_galaxia.png")
        },

        cursor_gato: {
            normal: caminho("cursor_gato.png"),
            clique: caminho("cursormao_gato.png")
        },

        cursor_dragao: {
            normal: caminho("cursor_dragao.png"),
            clique: caminho("cursormao_dragao.png")
        },

        cursor_serafim: {
            normal: caminho("cursor_querubim.png"),
            clique: caminho("cursormao_querubim.png")
        },

        cursor_natureza: {
            normal: caminho("cursor_natureza.png"),
            clique: caminho("cursormao_natureza.png")
        },

        cursor_neon: {
            normal: caminho("cursor_neon.png"),
            clique: caminho("cursormao_neon.png")
        },

        cursor_fogo: {
            normal: caminho("cursor_fogo.png"),
            clique: caminho("cursormao_fogo.png")
        },

        cursor_sakura: {
            normal: caminho("cursor_sakura.png"),
            clique: caminho("cursormao_sakura.png")
        }
    };

    let cursorAtual = null;
    let elementoCursor = null;
    let ultimoX = 0;
    let ultimoY = 0;
    let clicando = false;

    /* =====================================================
       STORAGE
    ===================================================== */

    function salvarCursorLocal(cursorId) {
        try {
            if (cursorId) {
                localStorage.setItem(STORAGE_KEY, cursorId);
            } else {
                localStorage.removeItem(STORAGE_KEY);
            }
        } catch (erro) {
            console.warn("FOAG Cursor: localStorage indisponível.", erro);
        }
    }

    function pegarCursorLocal() {
        try {
            const id = localStorage.getItem(STORAGE_KEY);

            return id && CURSORES_FOAG[id]
                ? id
                : null;
        } catch (erro) {
            return null;
        }
    }

    function pegarCursorDaLoja() {
        const id =
            window.LOJA_DATA?.itens_ativos?.cursor ||
            null;

        return (
            typeof id === "string" &&
            CURSORES_FOAG[id]
        )
            ? id
            : null;
    }

    function pegarCursorDaSessao() {
        try {
            const dados = JSON.parse(
                sessionStorage.getItem("itens_ativos_loja") || "{}"
            );

            const id = dados?.cursor;

            return id && CURSORES_FOAG[id]
                ? id
                : null;
        } catch (erro) {
            return null;
        }
    }

    function pegarCursorAtivo() {
        return (
            pegarCursorDaLoja() ||
            pegarCursorLocal() ||
            pegarCursorDaSessao() ||
            null
        );
    }

    /* =====================================================
       ELEMENTO VISUAL
    ===================================================== */

    function criarElementoCursor() {
        if (elementoCursor?.isConnected) {
            return elementoCursor;
        }

        const img = document.createElement("img");

        img.id = "foag-custom-cursor";
        img.alt = "";
        img.setAttribute("aria-hidden", "true");
        img.draggable = false;

        document.body.appendChild(img);

        elementoCursor = img;

        return img;
    }

    function posicionarCursor(x, y) {
        ultimoX = x;
        ultimoY = y;

        if (!elementoCursor) {
            return;
        }

        elementoCursor.style.transform =
            `translate3d(${x}px, ${y}px, 0)`;
    }

    function atualizarImagemCursor() {
        if (
            !cursorAtual ||
            !CURSORES_FOAG[cursorAtual] ||
            !elementoCursor
        ) {
            return;
        }

        const config =
            CURSORES_FOAG[cursorAtual];

        elementoCursor.src =
            clicando
                ? config.clique
                : config.normal;
    }

    /* =====================================================
       APLICAR
    ===================================================== */

    function aplicarCursorFoag(cursorId = null) {
        const id =
            cursorId ||
            pegarCursorAtivo();

        if (!id || !CURSORES_FOAG[id]) {
            desativarCursorFoag();
            return false;
        }

        cursorAtual = id;

        salvarCursorLocal(id);

        document.documentElement.classList.add(
            "foag-cursor-personalizado"
        );

        document.body?.classList.add(
            "foag-cursor-personalizado"
        );

        criarElementoCursor();

        clicando = false;
        atualizarImagemCursor();
        posicionarCursor(ultimoX, ultimoY);

        console.log("FOAG Cursor ativo:", id);

        return true;
    }

    function ativarCursorFoag(cursorId) {
        if (!CURSORES_FOAG[cursorId]) {
            console.warn(
                "FOAG Cursor não encontrado:",
                cursorId
            );
            return false;
        }

        return aplicarCursorFoag(cursorId);
    }

    function desativarCursorFoag() {
        cursorAtual = null;
        clicando = false;

        salvarCursorLocal(null);

        document.documentElement.classList.remove(
            "foag-cursor-personalizado"
        );

        document.body?.classList.remove(
            "foag-cursor-personalizado"
        );

        elementoCursor?.remove();
        elementoCursor = null;
    }

    function atualizarCursorDaLoja(cursorId) {
        if (cursorId) {
            ativarCursorFoag(cursorId);
        } else {
            desativarCursorFoag();
        }
    }

    /* =====================================================
       MOVIMENTO E CLIQUE
    ===================================================== */

    document.addEventListener(
        "pointermove",
        evento => {
            if (!cursorAtual) {
                return;
            }

            criarElementoCursor();

            elementoCursor.classList.add("visivel");

            posicionarCursor(
                evento.clientX,
                evento.clientY
            );
        },
        { passive: true }
    );

    document.addEventListener(
        "pointerdown",
        evento => {
            if (!cursorAtual || evento.pointerType === "touch") {
                return;
            }

            clicando = true;
            atualizarImagemCursor();

            elementoCursor?.classList.add("clicando");
        },
        true
    );

    document.addEventListener(
        "pointerup",
        evento => {
            if (!cursorAtual || evento.pointerType === "touch") {
                return;
            }

            clicando = false;
            atualizarImagemCursor();

            elementoCursor?.classList.remove("clicando");
        },
        true
    );

    document.addEventListener(
        "pointercancel",
        () => {
            clicando = false;
            atualizarImagemCursor();

            elementoCursor?.classList.remove("clicando");
        },
        true
    );

    document.addEventListener(
        "mouseleave",
        () => {
            elementoCursor?.classList.remove("visivel");
        }
    );

    document.addEventListener(
        "mouseenter",
        () => {
            if (cursorAtual) {
                elementoCursor?.classList.add("visivel");
            }
        }
    );

    /* =====================================================
       EVENTOS DA LOJA
    ===================================================== */

    window.addEventListener(
        "foag:cursor-alterado",
        evento => {
            const id =
                evento.detail?.cursor ||
                null;

            atualizarCursorDaLoja(id);
        }
    );

    window.addEventListener(
        "storage",
        evento => {
            if (evento.key !== STORAGE_KEY) {
                return;
            }

            if (
                evento.newValue &&
                CURSORES_FOAG[evento.newValue]
            ) {
                aplicarCursorFoag(evento.newValue);
            } else {
                desativarCursorFoag();
            }
        }
    );

    /* =====================================================
       INICIALIZAÇÃO
    ===================================================== */

    function iniciar() {
        const ativo =
            pegarCursorAtivo();

        if (ativo) {
            aplicarCursorFoag(ativo);
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            iniciar,
            { once: true }
        );
    } else {
        iniciar();
    }

    /* =====================================================
       API GLOBAL
    ===================================================== */

    window.CURSORES_FOAG =
        CURSORES_FOAG;

    window.pegarCursorAtivo =
        pegarCursorAtivo;

    window.aplicarCursorFoag =
        aplicarCursorFoag;

    window.ativarCursorFoag =
        ativarCursorFoag;

    window.desativarCursorFoag =
        desativarCursorFoag;

    window.atualizarCursorDaLoja =
        atualizarCursorDaLoja;
})();
