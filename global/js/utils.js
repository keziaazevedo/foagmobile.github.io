/* =========================================================
   FOAG — UTILITÁRIOS GLOBAIS
   Funções pequenas e reutilizáveis do frontend.
========================================================= */

(() => {
    'use strict';

    const FOAG = window.FOAG = window.FOAG || {};

    FOAG.utils = FOAG.utils || {};
    FOAG.ui = FOAG.ui || {};
    FOAG.api = FOAG.api || {};

    /**
     * Escapa texto antes de inseri-lo em HTML.
     */
    FOAG.utils.escapeHtml = function escapeHtml(valor) {
        return String(valor ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    };

    /**
     * Tenta converter uma string em JSON sem lançar erro.
     */
    FOAG.utils.parseJson = function parseJson(valor, fallback = null) {
        try {
            return JSON.parse(valor);
        } catch (_) {
            return fallback;
        }
    };

    /**
     * Exibe um toast simples baseado em uma classe CSS existente.
     * Mantém o visual definido por cada página.
     */
    FOAG.ui.showSimpleToast = function showSimpleToast(
        elemento,
        mensagem,
        opcoes = {}
    ) {
        if (!elemento) {
            return null;
        }

        const classe = opcoes.classe || 'show';
        const duracao = Number(opcoes.duracao) || 2600;

        elemento.textContent = mensagem;
        elemento.classList.add(classe);

        return window.setTimeout(() => {
            elemento.classList.remove(classe);
        }, duracao);
    };

    /**
     * Abre e fecha modais sem impor aparência.
     */
    FOAG.ui.openModal = function openModal(elemento, classe = 'show') {
        if (!elemento) return;
        elemento.classList.add(classe);
        elemento.setAttribute('aria-hidden', 'false');
    };

    FOAG.ui.closeModal = function closeModal(elemento, classe = 'show') {
        if (!elemento) return;
        elemento.classList.remove(classe);
        elemento.setAttribute('aria-hidden', 'true');
    };

    /**
     * Helper genérico para endpoints que retornam JSON.
     * Não substitui automaticamente fluxos específicos das páginas.
     */
    FOAG.api.requestJson = async function requestJson(url, opcoes = {}) {
        const resposta = await fetch(url, opcoes);
        const texto = await resposta.text();
        const dados = FOAG.utils.parseJson(texto, null);

        if (!resposta.ok) {
            const erro = new Error(
                dados?.mensagem || dados?.erro || `Erro HTTP ${resposta.status}`
            );
            erro.status = resposta.status;
            erro.dados = dados;
            throw erro;
        }

        return dados;
    };
})();
