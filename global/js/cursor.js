/* =========================================================
   FOAG — CURSOR PERSONALIZADO GLOBAL
   Cada usuário usa somente o cursor salvo em seu loja.json
========================================================= */

(() => {
    "use strict";

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
    let codigoUsuarioAtual = null;

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

        const config = CURSORES_FOAG[cursorAtual];

        elementoCursor.src =
            clicando
                ? config.clique
                : config.normal;
    }

    function removerCursorFoag() {
        cursorAtual = null;
        clicando = false;

        document.documentElement.classList.remove(
            "foag-cursor-personalizado"
        );

        document.body?.classList.remove(
            "foag-cursor-personalizado"
        );

        elementoCursor?.remove();
        elementoCursor = null;
    }

    function aplicarCursorFoag(cursorId = null) {
        if (!cursorId || !CURSORES_FOAG[cursorId]) {
            removerCursorFoag();
            return false;
        }

        cursorAtual = cursorId;

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

        console.log(
            `FOAG Cursor ativo para ${codigoUsuarioAtual ?? "usuário"}:`,
            cursorId
        );

        return true;
    }

    function ativarCursorFoag(cursorId) {
        if (!CURSORES_FOAG[cursorId]) {
            console.warn("FOAG Cursor não encontrado:", cursorId);
            return false;
        }

        return aplicarCursorFoag(cursorId);
    }

    function desativarCursorFoag() {
        removerCursorFoag();
    }

    function atualizarCursorDaLoja(cursorId) {
        if (cursorId) {
            ativarCursorFoag(cursorId);
        } else {
            desativarCursorFoag();
        }
    }

    async function carregarCursorDoUsuario() {
        try {
            const resposta = await fetch(
                `${BASE_PROJETO}/global/cursor_usuario.php?_=${Date.now()}`,
                {
                    credentials: "same-origin",
                    cache: "no-store"
                }
            );

            if (!resposta.ok) {
                throw new Error(`HTTP ${resposta.status}`);
            }

            const dados = await resposta.json();

            codigoUsuarioAtual = dados.codigo_usuario || null;

            if (
                dados.ok &&
                dados.cursor &&
                CURSORES_FOAG[dados.cursor]
            ) {
                aplicarCursorFoag(dados.cursor);
            } else {
                removerCursorFoag();
            }

            return dados;
        } catch (erro) {
            console.error(
                "FOAG Cursor: não foi possível carregar o cursor do usuário.",
                erro
            );

            removerCursorFoag();
            return null;
        }
    }

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

    window.addEventListener(
        "foag:cursor-alterado",
        evento => {
            const cursorId = evento.detail?.cursor || null;
            atualizarCursorDaLoja(cursorId);
        }
    );

    function iniciarCursorFoag() {
        carregarCursorDoUsuario();
    }

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            iniciarCursorFoag,
            { once: true }
        );
    } else {
        iniciarCursorFoag();
    }

    window.CURSORES_FOAG = CURSORES_FOAG;
    window.aplicarCursorFoag = aplicarCursorFoag;
    window.ativarCursorFoag = ativarCursorFoag;
    window.desativarCursorFoag = desativarCursorFoag;
    window.atualizarCursorDaLoja = atualizarCursorDaLoja;
    window.carregarCursorDoUsuario = carregarCursorDoUsuario;
})();
