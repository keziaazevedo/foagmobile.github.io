/* =========================================================
   FOAG — SISTEMA GLOBAL DE CURSORES
   Arquivo: global/js/cursor.js
========================================================= */

(() => {

    "use strict";

    /* =====================================================
       CURSORES DISPONÍVEIS
    ===================================================== */

    const CURSORES_FOAG = {

        cursor_galaxia: {
            normal: "/foagmobile.github.io/img/loja/cursor/cursor_galaxia.png",
            clique: "/foagmobile.github.io/img/loja/cursor/cursormao_galaxia.png"
        },

        cursor_gato: {
            normal: "/foagmobile.github.io/img/loja/cursor/cursor_gato.png",
            clique: "/foagmobile.github.io/img/loja/cursor/cursormao_gato.png"
        },

        cursor_dragao: {
            normal: "/foagmobile.github.io/img/loja/cursor/cursor_dragao.png",
            clique: "/foagmobile.github.io/img/loja/cursor/cursormao_dragao.png"
        },

        cursor_serafim: {
            normal: "/foagmobile.github.io/img/loja/cursor/cursor_querubim.png",
            clique: "/foagmobile.github.io/img/loja/cursor/cursormao_querubim.png"
        },

        cursor_natureza: {
            normal: "/foagmobile.github.io/img/loja/cursor/cursor_natureza.png",
            clique: "/foagmobile.github.io/img/loja/cursor/cursormao_natureza.png"
        },

        cursor_neon: {
            normal: "/foagmobile.github.io/img/loja/cursor/cursor_neon.png",
            clique: "/foagmobile.github.io/img/loja/cursor/cursormao_neon.png"
        },

        cursor_fogo: {
            normal: "/foagmobile.github.io/img/loja/cursor/cursor_fogo.png",
            clique: "/foagmobile.github.io/img/loja/cursor/cursormao_fogo.png"
        },

        cursor_sakura: {
            normal: "/foagmobile.github.io/img/loja/cursor/cursor_sakura.png",
            clique: "/foagmobile.github.io/img/loja/cursor/cursormao_sakura.png"
        }

    };

    /* =====================================================
       CONFIGURAÇÕES
    ===================================================== */

    const STORAGE_KEY = "foag_cursor_ativo";

    let cursorAtual = null;


    /* =====================================================
       SALVAR CURSOR NO NAVEGADOR
    ===================================================== */

    function salvarCursorLocal(cursorId) {

        if (!cursorId) {
            localStorage.removeItem(STORAGE_KEY);
            return;
        }

        localStorage.setItem(
            STORAGE_KEY,
            cursorId
        );
    }


    /* =====================================================
       PEGAR CURSOR SALVO LOCALMENTE
    ===================================================== */

    function pegarCursorLocal() {

        const cursorId =
            localStorage.getItem(STORAGE_KEY);

        if (
            cursorId &&
            CURSORES_FOAG[cursorId]
        ) {
            return cursorId;
        }

        return null;
    }


    /* =====================================================
       PEGAR CURSOR DA LOJA
       window.LOJA_DATA vem da loja.php
    ===================================================== */

    function pegarCursorDaLoja() {

        try {

            if (
                window.LOJA_DATA &&
                window.LOJA_DATA.itens_ativos &&
                window.LOJA_DATA.itens_ativos.cursor
            ) {

                const cursorId =
                    window.LOJA_DATA.itens_ativos.cursor;

                if (
                    typeof cursorId === "string" &&
                    CURSORES_FOAG[cursorId]
                ) {
                    return cursorId;
                }

            }

        } catch (erro) {

            console.error(
                "FOAG Cursor: erro ao ler LOJA_DATA.",
                erro
            );

        }

        return null;
    }


    /* =====================================================
       COMPATIBILIDADE COM TESTES ANTIGOS
       sessionStorage
    ===================================================== */

    function pegarCursorSessionStorage() {

        try {

            const dados = JSON.parse(
                sessionStorage.getItem(
                    "itens_ativos_loja"
                ) || "{}"
            );

            if (
                dados.cursor &&
                CURSORES_FOAG[dados.cursor]
            ) {
                return dados.cursor;
            }

        } catch (erro) {

            console.warn(
                "FOAG Cursor: sessionStorage inválido."
            );

        }

        return null;
    }


    /* =====================================================
       DESCOBRIR CURSOR ATIVO

       Prioridade:
       1. Loja/PHP
       2. LocalStorage
       3. SessionStorage de teste
    ===================================================== */

    function pegarCursorAtivo() {

        const cursorLoja =
            pegarCursorDaLoja();

        if (cursorLoja) {

            salvarCursorLocal(cursorLoja);

            return cursorLoja;
        }


        const cursorLocal =
            pegarCursorLocal();

        if (cursorLocal) {
            return cursorLocal;
        }


        const cursorTeste =
            pegarCursorSessionStorage();

        if (cursorTeste) {

            salvarCursorLocal(cursorTeste);

            return cursorTeste;
        }


        return null;
    }


    /* =====================================================
       REMOVER CURSOR PERSONALIZADO
    ===================================================== */

    function removerCursorFoag() {

        cursorAtual = null;

        document.documentElement
            .style
            .removeProperty(
                "--foag-cursor-normal"
            );

        document.documentElement
            .style
            .removeProperty(
                "--foag-cursor-clique"
            );

        if (document.body) {

            document.body.classList.remove(
                "cursor-foag-ativo"
            );

        }

    }


    /* =====================================================
       APLICAR CURSOR
    ===================================================== */

    function aplicarCursorFoag(
        cursorId = null
    ) {

        const id =
            cursorId ||
            pegarCursorAtivo();


        if (
            !id ||
            !CURSORES_FOAG[id]
        ) {

            removerCursorFoag();

            return false;
        }


        const cursor =
            CURSORES_FOAG[id];


        document.documentElement
            .style
            .setProperty(
                "--foag-cursor-normal",
                `url("${cursor.normal}") 4 2, auto`
            );


        document.documentElement
            .style
            .setProperty(
                "--foag-cursor-clique",
                `url("${cursor.clique}") 4 2, pointer`
            );


        if (document.body) {

            document.body.classList.add(
                "cursor-foag-ativo"
            );

        }


        cursorAtual = id;

        salvarCursorLocal(id);

        console.log(
            `FOAG Cursor ativo: ${id}`
        );

        return true;
    }


    /* =====================================================
       TROCAR CURSOR MANUALMENTE

       Essa função será útil para o botão "Ativar"
       da Loja.
    ===================================================== */

    function ativarCursorFoag(cursorId) {

        if (!CURSORES_FOAG[cursorId]) {

            console.warn(
                `FOAG Cursor não encontrado: ${cursorId}`
            );

            return false;
        }


        salvarCursorLocal(cursorId);

        return aplicarCursorFoag(
            cursorId
        );
    }


    /* =====================================================
       DESATIVAR
    ===================================================== */

    function desativarCursorFoag() {

        salvarCursorLocal(null);

        removerCursorFoag();

    }


    /* =====================================================
       SINCRONIZAR COM A LOJA

       Pode ser chamada pelo loja.js depois de ativar
       um item.
    ===================================================== */

    function atualizarCursorDaLoja(
        cursorId
    ) {

        if (!cursorId) {

            desativarCursorFoag();

            return;
        }


        if (!CURSORES_FOAG[cursorId]) {

            console.warn(
                "FOAG Cursor: item inválido:",
                cursorId
            );

            return;
        }


        ativarCursorFoag(
            cursorId
        );

    }


    /* =====================================================
       PRÉ-CARREGAR AS IMAGENS
    ===================================================== */

    function preloadCursor(cursorId) {

        const cursor =
            CURSORES_FOAG[cursorId];

        if (!cursor) {
            return;
        }


        const normal =
            new Image();

        normal.src =
            cursor.normal;


        const clique =
            new Image();

        clique.src =
            cursor.clique;

    }


    function preloadCursores() {

        Object.keys(
            CURSORES_FOAG
        ).forEach(
            preloadCursor
        );

    }


    /* =====================================================
       CARREGAMENTO DA PÁGINA
    ===================================================== */

    function iniciarCursorFoag() {

        preloadCursores();

        aplicarCursorFoag();

    }


    if (
        document.readyState ===
        "loading"
    ) {

        document.addEventListener(
            "DOMContentLoaded",
            iniciarCursorFoag
        );

    } else {

        iniciarCursorFoag();

    }


    /* =====================================================
       SINCRONIZAÇÃO ENTRE ABAS
    ===================================================== */

    window.addEventListener(
        "storage",
        event => {

            if (
                event.key ===
                STORAGE_KEY
            ) {

                if (
                    event.newValue &&
                    CURSORES_FOAG[
                        event.newValue
                    ]
                ) {

                    aplicarCursorFoag(
                        event.newValue
                    );

                } else {

                    removerCursorFoag();

                }

            }

        }
    );


    /* =====================================================
       EVENTO PERSONALIZADO

       O loja.js pode disparar:

       window.dispatchEvent(
           new CustomEvent(
               "foag:cursor-alterado",
               {
                   detail: {
                       cursor: "cursor_gato"
                   }
               }
           )
       );
    ===================================================== */

    window.addEventListener(
        "foag:cursor-alterado",
        event => {

            const cursorId =
                event.detail?.cursor;

            if (cursorId) {

                ativarCursorFoag(
                    cursorId
                );

            } else {

                desativarCursorFoag();

            }

        }
    );


    /* =====================================================
       FUNÇÕES GLOBAIS

       Deixamos disponíveis para Loja e Console.
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