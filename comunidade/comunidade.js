// comunidade.js — Comunidade FOAG corrigida
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // =================================================
    // DADOS / CONFIGURAÇÕES
    // =================================================

    let chatData = window.CHAT_DATA || { perguntas: [] };
    let todasPerguntas = window.TODAS_PERGUNTAS || [];
    let interacoes = window.INTERACOES || { curtidas: [], salvos: [] };

    const usuarioCodigo = String(window.USUARIO_CODIGO || '');
    const usuarioNome = window.USUARIO_NOME || 'Usuário';

    const usuariosVisuais =
        window.USUARIOS_VISUAIS &&
        typeof window.USUARIOS_VISUAIS === 'object'
            ? window.USUARIOS_VISUAIS
            : {};

    const usuariosPorNome =
        window.USUARIOS_POR_NOME &&
        typeof window.USUARIOS_POR_NOME === 'object'
            ? window.USUARIOS_POR_NOME
            : {};

    const CHAT_ACTION_URL = window.CHAT_SAVE_URL || FOAG_CONFIG.endpoints.comunidadeChatSalvar;
    const INTERACAO_URL = window.INTERACAO_URL || FOAG_CONFIG.endpoints.comunidadeInteracao;
    const INTERACOES_SAVE_URL = window.INTERACOES_SAVE_URL || FOAG_CONFIG.endpoints.comunidadeInteracoesSalvar;

    const palavrasProibidas = Array.isArray(window.PALAVRAS_PROIBIDAS)
        ? window.PALAVRAS_PROIBIDAS
        : [];

    if (!Array.isArray(chatData.perguntas)) chatData.perguntas = [];
    if (!Array.isArray(todasPerguntas)) todasPerguntas = [];
    if (!Array.isArray(interacoes.curtidas)) interacoes.curtidas = [];
    if (!Array.isArray(interacoes.salvos)) interacoes.salvos = [];

    // =================================================
    // AUXILIARES
    // =================================================

    function escaparRegex(texto) {
        return String(texto).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function censurarTexto(texto) {
        if (!texto) return '';

        let resultado = String(texto);

        const palavras = [...palavrasProibidas]
            .map(p => String(p).trim())
            .filter(Boolean)
            .sort((a, b) => b.length - a.length);

        palavras.forEach(function (palavra) {
            const regex = new RegExp(
                '\\b' + escaparRegex(palavra) + '\\b',
                'gi'
            );

            resultado = resultado.replace(
                regex,
                match => '*'.repeat(match.length)
            );
        });

        return resultado;
    }

    function verificarCensura(texto) {
        if (!texto) return false;

        return palavrasProibidas.some(function (palavra) {
            palavra = String(palavra || '').trim();

            if (!palavra) return false;

            const regex = new RegExp(
                '\\b' + escaparRegex(palavra) + '\\b',
                'i'
            );

            return regex.test(texto);
        });
    }

    function escaparHtml(valor) {
        return window.FOAG?.utils?.escapeHtml
            ? FOAG.utils.escapeHtml(valor)
            : String(valor ?? '');
    }

    function obterIniciais(nome) {
        const partes = String(nome || '')
            .trim()
            .split(/\s+/)
            .filter(Boolean);

        if (!partes.length) return '?';

        if (partes.length === 1) {
            return partes[0].charAt(0).toUpperCase();
        }

        return (
            partes[0].charAt(0) +
            partes[partes.length - 1].charAt(0)
        ).toUpperCase();
    }


    function normalizarNomeAvatar(nome) {
        return String(nome || '')
            .trim()
            .toLocaleLowerCase('pt-BR');
    }

    function obterVisualUsuario(codigo, nome) {
        const id = String(codigo || '').trim();

        if (
            id &&
            usuariosVisuais[id]
        ) {
            return usuariosVisuais[id];
        }

        const chaveNome =
            normalizarNomeAvatar(nome);

        const codigoPorNome =
            usuariosPorNome[chaveNome];

        if (
            codigoPorNome &&
            usuariosVisuais[String(codigoPorNome)]
        ) {
            return usuariosVisuais[
                String(codigoPorNome)
            ];
        }

        return null;
    }

    /*
     * Os ajustes foram calibrados no Perfil usando
     * um avatar-base de 132px. Na Comunidade os
     * deslocamentos são convertidos para porcentagem,
     * então funcionam tanto no avatar de pergunta
     * quanto no avatar menor das respostas.
     */
    function deslocamentoAvatarPercentual(valorPx) {
        const numero = Number(valorPx);

        if (!Number.isFinite(numero)) {
            return 0;
        }

        return (numero / 132) * 100;
    }

    function normalizarAjusteAvatar(visual) {
        const ajuste =
            visual &&
            visual.moldura &&
            visual.moldura.ajuste_perfil
                ? visual.moldura.ajuste_perfil
                : {};

        const numero = function(valor, padrao) {
            const convertido = Number(valor);

            return Number.isFinite(convertido)
                ? convertido
                : padrao;
        };

        return {
            moldura_escala:
                numero(ajuste.moldura_escala, 1.28),

            moldura_x:
                deslocamentoAvatarPercentual(
                    numero(ajuste.moldura_x, 0)
                ),

            moldura_y:
                deslocamentoAvatarPercentual(
                    numero(ajuste.moldura_y, 0)
                ),

            foto_escala:
                numero(ajuste.foto_escala, 1),

            foto_x:
                deslocamentoAvatarPercentual(
                    numero(ajuste.foto_x, 0)
                ),

            foto_y:
                deslocamentoAvatarPercentual(
                    numero(ajuste.foto_y, 0)
                )
        };
    }

    function renderizarAvatarUsuario(
        codigo,
        nome,
        tamanho = 'pergunta'
    ) {
        const visual =
            obterVisualUsuario(codigo, nome);

        const iniciais =
            escaparHtml(obterIniciais(nome || '?'));

        const classeTamanho =
            tamanho === 'resposta'
                ? 'avatar-comunidade-resposta'
                : 'avatar-comunidade-pergunta';

        /*
         * Compatibilidade com registros antigos ou
         * usuários sem perfil encontrado.
         */
        if (!visual || !visual.foto) {
            return `
                <div class="avatar-comunidade ${classeTamanho} sem-foto">
                    <span class="avatar-comunidade-fallback">
                        ${iniciais}
                    </span>
                </div>
            `;
        }

        const moldura =
            visual.moldura &&
            visual.moldura.imagem
                ? visual.moldura
                : null;

        const ajuste =
            normalizarAjusteAvatar(visual);

        const estilos = moldura
            ? [
                `--comu-moldura-escala:${ajuste.moldura_escala}`,
                `--comu-moldura-x:${ajuste.moldura_x}%`,
                `--comu-moldura-y:${ajuste.moldura_y}%`,
                `--comu-foto-escala:${ajuste.foto_escala}`,
                `--comu-foto-x:${ajuste.foto_x}%`,
                `--comu-foto-y:${ajuste.foto_y}%`
            ].join(';')
            : '';

        return `
            <div
                class="avatar-comunidade ${classeTamanho} ${moldura ? 'tem-moldura' : ''}"
                ${estilos ? `style="${estilos}"` : ''}
            >
                <div class="avatar-comunidade-foto-wrap">
                    <img
                        class="avatar-comunidade-foto"
                        src="${escaparHtml(visual.foto)}"
                        alt="Foto de perfil de ${escaparHtml(nome || visual.nome || 'Usuário')}"
                        loading="lazy"
                        onerror="
                            this.style.display='none';
                            this.nextElementSibling.style.display='flex';
                        "
                    >
                    <span
                        class="avatar-comunidade-fallback"
                        aria-hidden="true"
                    >
                        ${iniciais}
                    </span>
                </div>

                ${
                    moldura
                        ? `
                            <img
                                class="avatar-comunidade-moldura"
                                src="${escaparHtml(moldura.imagem)}"
                                alt=""
                                aria-hidden="true"
                            >
                        `
                        : ''
                }
            </div>
        `;
    }

    function renderizarEmojiUsuario(codigo, nome) {
    const visual = obterVisualUsuario(codigo, nome);

    if (
        !visual ||
        !visual.emoji ||
        !visual.emoji.imagem
    ) {
        return '';
    }

    return `
        <img
            class="emoji-nome-comunidade"
            src="${escaparHtml(visual.emoji.imagem)}"
            alt="${escaparHtml(visual.emoji.nome || 'Emoji')}"
            title="${escaparHtml(visual.emoji.nome || 'Emoji')}"
            loading="lazy"
        >
    `;
}

    function formatarData(data) {
        if (!data) return 'Data desconhecida';

        const d = new Date(data);

        if (Number.isNaN(d.getTime())) {
            return 'Data inválida';
        }

        return d.toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function ehMinhaResposta(resposta) {
        if (
            resposta.usuario_id !== undefined &&
            resposta.usuario_id !== null
        ) {
            return String(resposta.usuario_id) === usuarioCodigo;
        }

        // Compatibilidade com respostas antigas
        // que ainda não possuíam usuario_id
        return String(resposta.autor || '') === String(usuarioNome);
    }

    function isCurtido(id) {
        return interacoes.curtidas.includes(String(id));
    }

    function isSalvo(id) {
        return interacoes.salvos.includes(String(id));
    }

    // =================================================
    // REQUISIÇÃO JSON
    // =================================================

    async function requisicaoJson(url, dados) {
        const resposta = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(dados)
        });

        const texto = await resposta.text();

        let retorno = null;

        try {
            retorno = texto ? JSON.parse(texto) : {};
        } catch (_) {
            retorno = null;
        }

        if (!resposta.ok) {
            throw new Error(
                retorno?.mensagem ||
                retorno?.erro ||
                texto ||
                `Erro HTTP ${resposta.status}`
            );
        }

        if (!retorno || retorno.ok === false) {
            throw new Error(
                retorno?.mensagem ||
                retorno?.erro ||
                'Não foi possível concluir a operação.'
            );
        }

        return retorno;
    }

    // =================================================
    // CONTADORES
    // =================================================

    function atualizarContadores() {
        const badgeMinhas = document.querySelector(
            '.aba-btn[data-aba="minhas"] .badge'
        );

        const badgeExplorar = document.querySelector(
            '.aba-btn[data-aba="explorar"] .badge'
        );

        const totalMinhas = document.getElementById('total-perguntas');
        const totalExplorar = document.getElementById('total-explorar');

        if (badgeMinhas) {
            badgeMinhas.textContent = chatData.perguntas.length;
        }

        if (badgeExplorar) {
            badgeExplorar.textContent = todasPerguntas.length;
        }

        if (totalMinhas) {
            totalMinhas.textContent = chatData.perguntas.length;
        }

        if (totalExplorar) {
            totalExplorar.textContent = todasPerguntas.length;
        }
    }

    // =================================================
    // LOCALIZAR DADOS
    // =================================================

    function obterContainerRespostas(perguntaId, origem) {
        return document.getElementById(
            `${origem}-respostas-${perguntaId}`
        );
    }

    function encontrarPergunta(perguntaId) {
        const id = String(perguntaId);

        return (
            chatData.perguntas.find(
                p => String(p.id) === id
            ) ||
            todasPerguntas.find(
                p => String(p.id) === id
            ) ||
            null
        );
    }

    // =================================================
    // ATUALIZAÇÃO LOCAL DAS RESPOSTAS
    // =================================================

    function adicionarRespostaLocal(perguntaId, resposta) {
        const id = String(perguntaId);

        [
            chatData.perguntas,
            todasPerguntas
        ].forEach(function (lista) {

            const pergunta = lista.find(
                p => String(p.id) === id
            );

            if (!pergunta) return;

            if (!Array.isArray(pergunta.respostas)) {
                pergunta.respostas = [];
            }

            const jaExiste = pergunta.respostas.some(
                r => String(r.id) === String(resposta.id)
            );

            if (!jaExiste) {
                pergunta.respostas.push({ ...resposta });
            }
        });
    }

    function removerRespostaLocal(perguntaId, respostaId) {
        const pid = String(perguntaId);
        const rid = String(respostaId);

        [
            chatData.perguntas,
            todasPerguntas
        ].forEach(function (lista) {

            const pergunta = lista.find(
                p => String(p.id) === pid
            );

            if (
                !pergunta ||
                !Array.isArray(pergunta.respostas)
            ) {
                return;
            }

            pergunta.respostas = pergunta.respostas.filter(
                r => String(r.id) !== rid
            );
        });
    }

    // =================================================
    // RENDERIZAR RESPOSTAS
    // =================================================

    function renderizarRespostas(
        respostas,
        perguntaId,
        origem
    ) {
        if (
            !Array.isArray(respostas) ||
            respostas.length === 0
        ) {
            return `
                <div class="sem-respostas">
                    Nenhuma resposta ainda.
                </div>
            `;
        }

        return respostas.map(function (resposta) {

            const minha = ehMinhaResposta(resposta);

            const texto = censurarTexto(
                resposta.texto ??
                resposta.texto_original ??
                ''
            );

            return `
                <div
                    class="resposta-item"
                    data-id="${escaparHtml(resposta.id || '')}"
                >

                    <div class="resposta-header">

                        ${renderizarAvatarUsuario(
                            resposta.usuario_id || '',
                            resposta.autor || 'Anônimo',
                            'resposta'
                        )}

                        <span class="nome">

                           ${escaparHtml(resposta.autor || 'Anônimo')}
    ${renderizarEmojiUsuario(
        resposta.usuario_id || '',
        resposta.autor || ''
    )}
    ${
        minha
            ? `<span class="usuario-tag-resposta">Você</span>`
            : ''
    } 

                            ${
                                minha
                                    ? `
                                        <span class="usuario-tag-resposta">
                                            Você
                                        </span>
                                    `
                                    : ''
                            }

                        </span>

                        <span class="data">
                            ${escaparHtml(
                                formatarData(resposta.data)
                            )}
                        </span>

                        ${
                            minha
                                ? `
                                    <button
                                        type="button"
                                        class="btn-excluir-resposta"
                                        data-id="${escaparHtml(
                                            resposta.id || ''
                                        )}"
                                        data-pergunta-id="${escaparHtml(
                                            perguntaId
                                        )}"
                                        data-origem="${escaparHtml(
                                            origem
                                        )}"
                                        title="Excluir resposta"
                                    >
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                `
                                : ''
                        }

                    </div>

                    <div class="resposta-texto">
                        ${escaparHtml(texto)}
                    </div>

                </div>
            `;
        }).join('');
    }

    // =================================================
    // FORMULÁRIO DE RESPOSTA
    // =================================================

    function renderizarFormularioResposta(
        perguntaId,
        origem
    ) {
        return `
            <div
                class="resposta-form"
                data-pergunta-id="${escaparHtml(perguntaId)}"
                data-origem="${escaparHtml(origem)}"
                style="display:none;"
            >

                <div class="resposta-form-wrapper">

                    <textarea
                        class="resposta-textarea"
                        placeholder="Escreva sua resposta..."
                        rows="3"
                    ></textarea>

                    <div class="resposta-form-actions">

                        <div
                            class="resposta-censure-preview"
                            style="display:none;"
                        >
                            <i class="fa-solid fa-triangle-exclamation"></i>

                            <span>
                                Palavra ofensiva detectada.
                                Ela será censurada automaticamente.
                            </span>
                        </div>

                        <div class="resposta-buttons">

                            <button
                                type="button"
                                class="btn-cancelar-resposta"
                                data-id="${escaparHtml(perguntaId)}"
                                data-origem="${escaparHtml(origem)}"
                            >
                                Cancelar
                            </button>

                            <button
                                type="button"
                                class="btn-enviar-resposta"
                                data-id="${escaparHtml(perguntaId)}"
                                data-origem="${escaparHtml(origem)}"
                            >
                                <i class="fa-regular fa-paper-plane"></i>
                                Responder
                            </button>

                        </div>

                    </div>

                </div>

            </div>
        `;
    }

    // =================================================
    // RENDERIZAR MINHAS PERGUNTAS
    // =================================================

    function renderizarMinhasPerguntas() {
        const container = document.getElementById(
            'minhas-perguntas-lista'
        );

        if (!container) return;

        const perguntas = [...chatData.perguntas].sort(
            (a, b) =>
                new Date(b.data) -
                new Date(a.data)
        );

        if (!perguntas.length) {
            container.innerHTML = `
                <div class="sem-resultados">

                    <i class="fa-regular fa-comment-dots"></i>

                    <h3>
                        Nenhuma pergunta sua ainda
                    </h3>

                    <p>
                        Faça uma pergunta para a comunidade.
                    </p>

                </div>
            `;

            return;
        }

        container.innerHTML = perguntas.map(
            function (pergunta) {

                const id = String(
                    pergunta.id || ''
                );

                const respostas =
                    Array.isArray(pergunta.respostas)
                        ? pergunta.respostas
                        : [];

                const texto = censurarTexto(
                    pergunta.texto ??
                    pergunta.texto_original ??
                    ''
                );

                return `
                    <div
                        class="pergunta-item"
                        data-id="${escaparHtml(id)}"
                    >

                        <div class="pergunta-topo">

                            <div class="pergunta-autor">

                                ${renderizarAvatarUsuario(
                                    usuarioCodigo,
                                    usuarioNome,
                                    'pergunta'
                                )}

                                <div>

                                    <span class="nome">
                                         ${escaparHtml(usuarioNome)}
                                         ${renderizarEmojiUsuario(usuarioCodigo, usuarioNome)}
                                    </span>

                                    <span class="data">
                                        ${escaparHtml(
                                            formatarData(pergunta.data)
                                        )}
                                    </span>

                                    <span class="usuario-tag">
                                        <i class="fa-regular fa-user"></i>
                                        Você
                                    </span>

                                </div>

                            </div>

                            <span class="pergunta-materia">
                                ${escaparHtml(
                                    pergunta.materia || 'Geral'
                                )}
                            </span>

                        </div>

                        <div class="pergunta-texto">
                            ${escaparHtml(texto)}
                        </div>

                        <div class="pergunta-rodape">

                            <div class="pergunta-acoes">

                                <button
                                    type="button"
                                    class="btn-ver-respostas"
                                    data-id="${escaparHtml(id)}"
                                    data-origem="minhas"
                                >
                                    <i class="fa-regular fa-comment"></i>

                                    ${respostas.length}
                                    resposta${respostas.length !== 1 ? 's' : ''}
                                </button>

                                <button
                                    type="button"
                                    class="btn-responder"
                                    data-id="${escaparHtml(id)}"
                                    data-origem="minhas"
                                >
                                    <i class="fa-regular fa-pen-to-square"></i>
                                    Responder
                                </button>

                                <button
                                    type="button"
                                    class="btn-excluir"
                                    data-id="${escaparHtml(id)}"
                                >
                                    <i class="fa-regular fa-trash-can"></i>
                                    Excluir
                                </button>

                            </div>

                        </div>

                        <div
                            class="respostas-container"
                            id="minhas-respostas-${escaparHtml(id)}"
                        >

                            ${renderizarRespostas(
                                respostas,
                                id,
                                'minhas'
                            )}

                            ${renderizarFormularioResposta(
                                id,
                                'minhas'
                            )}

                        </div>

                    </div>
                `;
            }
        ).join('');

        adicionarEventosMinhasPerguntas();
        adicionarEventosRespostas();
    }

    // =================================================
    // RENDERIZAR EXPLORAR
    // =================================================

    function renderizarExplorar() {
        const container = document.getElementById(
            'explorar-perguntas-lista'
        );

        if (!container) return;

        if (!todasPerguntas.length) {
            container.innerHTML = `
                <div class="sem-resultados">

                    <i class="fa-regular fa-face-frown"></i>

                    <h3>
                        Nenhuma pergunta encontrada
                    </h3>

                    <p>
                        Tente ajustar os filtros de busca.
                    </p>

                </div>
            `;

            return;
        }

        container.innerHTML = todasPerguntas.map(
            function (pergunta) {

                const id = String(
                    pergunta.id || ''
                );

                const respostas =
                    Array.isArray(pergunta.respostas)
                        ? pergunta.respostas
                        : [];

                const ehDoUsuario =
                    String(
                        pergunta.usuario_id || ''
                    ) === usuarioCodigo;

                const curtido = isCurtido(id);
                const salvo = isSalvo(id);

                const texto = censurarTexto(
                    pergunta.texto ??
                    pergunta.texto_original ??
                    ''
                );

                return `
                    <div
                        class="pergunta-item"
                        data-id="${escaparHtml(id)}"
                    >

                        <div class="pergunta-topo">

                            <div class="pergunta-autor">

                                ${renderizarAvatarUsuario(
                                    pergunta.usuario_id || '',
                                    pergunta.autor || 'Anônimo',
                                    'pergunta'
                                )}

                                <div>

                                    <span class="nome">
    ${escaparHtml(pergunta.autor || 'Anônimo')}
    ${renderizarEmojiUsuario(pergunta.usuario_id || '', pergunta.autor || '')}
</span>
                                    <span class="data">
                                        ${escaparHtml(
                                            formatarData(pergunta.data)
                                        )}
                                    </span>

                                    ${
                                        ehDoUsuario
                                            ? `
                                                <span class="usuario-tag">
                                                    <i class="fa-regular fa-user"></i>
                                                    Você
                                                </span>
                                            `
                                            : ''
                                    }

                                </div>

                            </div>

                            <span class="pergunta-materia">
                                ${escaparHtml(
                                    pergunta.materia || 'Geral'
                                )}
                            </span>

                        </div>

                        <div class="pergunta-texto">
                            ${escaparHtml(texto)}
                        </div>

                        <div class="pergunta-rodape">

                            <div class="pergunta-acoes">

                                <button
                                    type="button"
                                    class="btn-curtir ${
                                        curtido ? 'curtido' : ''
                                    }"
                                    data-id="${escaparHtml(id)}"
                                >
                                    <i class="${
                                        curtido
                                            ? 'fa-solid'
                                            : 'fa-regular'
                                    } fa-heart"></i>

                                    <span>
                                        ${
                                            curtido
                                                ? 'Curtido'
                                                : 'Curtir'
                                        }
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-salvar ${
                                        salvo ? 'salvo' : ''
                                    }"
                                    data-id="${escaparHtml(id)}"
                                >
                                    <i class="${
                                        salvo
                                            ? 'fa-solid'
                                            : 'fa-regular'
                                    } fa-bookmark"></i>

                                    <span>
                                        ${
                                            salvo
                                                ? 'Salvo'
                                                : 'Salvar'
                                        }
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="btn-ver-respostas"
                                    data-id="${escaparHtml(id)}"
                                    data-origem="explorar"
                                >
                                    <i class="fa-regular fa-comment"></i>

                                    ${respostas.length}
                                    resposta${respostas.length !== 1 ? 's' : ''}
                                </button>

                                <button
                                    type="button"
                                    class="btn-responder"
                                    data-id="${escaparHtml(id)}"
                                    data-origem="explorar"
                                >
                                    <i class="fa-regular fa-pen-to-square"></i>
                                    Responder
                                </button>

                            </div>

                        </div>

                        <div
                            class="respostas-container"
                            id="explorar-respostas-${escaparHtml(id)}"
                        >

                            ${renderizarRespostas(
                                respostas,
                                id,
                                'explorar'
                            )}

                            ${renderizarFormularioResposta(
                                id,
                                'explorar'
                            )}

                        </div>

                    </div>
                `;
            }
        ).join('');

        adicionarEventosExplorar();
        adicionarEventosRespostas();
    }

    // =================================================
    // EVENTOS DAS RESPOSTAS
    // =================================================

    function adicionarEventosRespostas() {

        // =========================================
        // ABRIR FORMULÁRIO
        // =========================================

        document.querySelectorAll(
            '.btn-responder'
        ).forEach(function (botao) {

            botao.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();

                const id = String(
                    this.dataset.id || ''
                );

                const origem =
                    this.dataset.origem ||
                    'explorar';

                const container =
                    obterContainerRespostas(
                        id,
                        origem
                    );

                if (!container) return;

                container.classList.add(
                    'visivel'
                );

                const form =
                    container.querySelector(
                        '.resposta-form'
                    );

                if (!form) return;

                form.style.display = 'block';

                const textarea =
                    form.querySelector(
                        '.resposta-textarea'
                    );

                if (textarea) {
                    setTimeout(
                        () => textarea.focus(),
                        50
                    );
                }
            };
        });

        // =========================================
        // MOSTRAR / ESCONDER RESPOSTAS
        // =========================================

        document.querySelectorAll(
            '.btn-ver-respostas'
        ).forEach(function (botao) {

            botao.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();

                const id = String(
                    this.dataset.id || ''
                );

                const origem =
                    this.dataset.origem ||
                    'explorar';

                const container =
                    obterContainerRespostas(
                        id,
                        origem
                    );

                if (container) {
                    container.classList.toggle(
                        'visivel'
                    );
                }
            };
        });

        // =========================================
        // ENVIAR RESPOSTA
        // =========================================

        document.querySelectorAll(
            '.btn-enviar-resposta'
        ).forEach(function (botao) {

            botao.onclick = async function (e) {
                e.preventDefault();
                e.stopPropagation();

                const id = String(
                    this.dataset.id || ''
                );

                const form =
                    this.closest(
                        '.resposta-form'
                    );

                const textarea =
                    form?.querySelector(
                        '.resposta-textarea'
                    );

                const texto =
                    textarea?.value.trim() ||
                    '';

                if (!texto) {
                    alert(
                        'Escreva sua resposta antes de enviar.'
                    );

                    textarea?.focus();

                    return;
                }

                const htmlOriginal =
                    this.innerHTML;

                this.disabled = true;

                this.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    Enviando...
                `;

                try {

                    const retorno =
                        await requisicaoJson(
                            INTERACAO_URL,
                            {
                                acao: 'responder',
                                pergunta_id: id,
                                texto: texto
                            }
                        );

                    adicionarRespostaLocal(
                        id,
                        retorno.resposta
                    );

                    renderizarMinhasPerguntas();
                    renderizarExplorar();

                    atualizarContadores();

                } catch (erro) {

                    console.error(
                        'Erro ao responder:',
                        erro
                    );

                    alert(
                        erro.message ||
                        'Não foi possível salvar a resposta.'
                    );

                    this.disabled = false;
                    this.innerHTML = htmlOriginal;
                }
            };
        });

        // =========================================
        // CANCELAR RESPOSTA
        // =========================================

        document.querySelectorAll(
            '.btn-cancelar-resposta'
        ).forEach(function (botao) {

            botao.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();

                const form =
                    this.closest(
                        '.resposta-form'
                    );

                if (!form) return;

                const textarea =
                    form.querySelector(
                        '.resposta-textarea'
                    );

                const preview =
                    form.querySelector(
                        '.resposta-censure-preview'
                    );

                if (textarea) {
                    textarea.value = '';
                }

                if (preview) {
                    preview.style.display = 'none';
                }

                form.style.display = 'none';
            };
        });

        // =========================================
        // EXCLUIR RESPOSTA
        // =========================================

        document.querySelectorAll(
            '.btn-excluir-resposta'
        ).forEach(function (botao) {

            botao.onclick = async function (e) {
                e.preventDefault();
                e.stopPropagation();

                const respostaId =
                    String(
                        this.dataset.id || ''
                    );

                const perguntaId =
                    String(
                        this.dataset.perguntaId ||
                        ''
                    );

                if (
                    !confirm(
                        'Tem certeza que deseja excluir esta resposta?'
                    )
                ) {
                    return;
                }

                this.disabled = true;

                try {

                    await requisicaoJson(
                        INTERACAO_URL,
                        {
                            acao: 'excluir_resposta',
                            pergunta_id: perguntaId,
                            resposta_id: respostaId
                        }
                    );

                    removerRespostaLocal(
                        perguntaId,
                        respostaId
                    );

                    renderizarMinhasPerguntas();
                    renderizarExplorar();

                } catch (erro) {

                    console.error(
                        'Erro ao excluir resposta:',
                        erro
                    );

                    alert(
                        erro.message ||
                        'Não foi possível excluir a resposta.'
                    );

                    this.disabled = false;
                }
            };
        });

        // =========================================
        // CENSURA EM TEMPO REAL
        // =========================================

        document.querySelectorAll(
            '.resposta-textarea'
        ).forEach(function (textarea) {

            textarea.oninput = function () {

                const form =
                    this.closest(
                        '.resposta-form'
                    );

                const preview =
                    form?.querySelector(
                        '.resposta-censure-preview'
                    );

                if (!preview) return;

                preview.style.display =
                    verificarCensura(
                        this.value
                    ) &&
                    this.value.trim()
                        ? 'flex'
                        : 'none';
            };

            // CTRL + ENTER
            textarea.onkeydown = function (e) {

                if (
                    e.key === 'Enter' &&
                    e.ctrlKey
                ) {
                    e.preventDefault();

                    const form =
                        this.closest(
                            '.resposta-form'
                        );

                    form
                        ?.querySelector(
                            '.btn-enviar-resposta'
                        )
                        ?.click();
                }
            };
        });
    }

    // =================================================
    // EVENTOS DAS MINHAS PERGUNTAS
    // =================================================

    function adicionarEventosMinhasPerguntas() {

        document.querySelectorAll(
            '#aba-minhas .btn-excluir'
        ).forEach(function (botao) {

            botao.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();

                abrirModalExclusao(
                    String(
                        this.dataset.id || ''
                    )
                );
            };
        });
    }

    // =================================================
    // EVENTOS DO EXPLORAR
    // =================================================

    function adicionarEventosExplorar() {

        document.querySelectorAll(
            '#aba-explorar .btn-curtir'
        ).forEach(function (botao) {

            botao.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();

                toggleCurtir(
                    String(
                        this.dataset.id || ''
                    )
                );
            };
        });

        document.querySelectorAll(
            '#aba-explorar .btn-salvar'
        ).forEach(function (botao) {

            botao.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();

                toggleSalvar(
                    String(
                        this.dataset.id || ''
                    )
                );
            };
        });
    }

    // =================================================
    // SALVAR INTERAÇÕES
    // =================================================

    async function salvarInteracoes() {

        return requisicaoJson(
            INTERACOES_SAVE_URL,
            {
                curtidas:
                    interacoes.curtidas,

                salvos:
                    interacoes.salvos
            }
        );
    }

    // =================================================
    // CURTIR
    // =================================================

    async function toggleCurtir(id) {

        const backup = [
            ...interacoes.curtidas
        ];

        const index =
            interacoes.curtidas.indexOf(id);

        if (index >= 0) {

            interacoes.curtidas.splice(
                index,
                1
            );

        } else {

            interacoes.curtidas.push(id);
        }

        renderizarExplorar();

        try {

            await salvarInteracoes();

        } catch (erro) {

            interacoes.curtidas =
                backup;

            renderizarExplorar();

            console.error(
                'Erro ao salvar curtida:',
                erro
            );

            alert(
                'Não foi possível salvar a curtida.'
            );
        }
    }

    // =================================================
    // SALVAR PERGUNTA
    // =================================================

    async function toggleSalvar(id) {

        const backup = [
            ...interacoes.salvos
        ];

        const index =
            interacoes.salvos.indexOf(id);

        if (index >= 0) {

            interacoes.salvos.splice(
                index,
                1
            );

        } else {

            interacoes.salvos.push(id);
        }

        renderizarExplorar();

        try {

            await salvarInteracoes();

        } catch (erro) {

            interacoes.salvos =
                backup;

            renderizarExplorar();

            console.error(
                'Erro ao salvar pergunta:',
                erro
            );

            alert(
                'Não foi possível salvar esta pergunta.'
            );
        }
    }

    // =================================================
    // PUBLICAR PERGUNTA
    // =================================================

    const btnPostar =
        document.getElementById(
            'btn-postar-pergunta'
        );

    const perguntaTexto =
        document.getElementById(
            'pergunta-texto'
        );

    const perguntaMateria =
        document.getElementById(
            'pergunta-materia'
        );

    const censurePreview =
        document.getElementById(
            'censure-preview'
        );

    const censurePreviewText =
        document.getElementById(
            'censure-preview-text'
        );

    // =================================================
    // CENSURA AO DIGITAR PERGUNTA
    // =================================================

    perguntaTexto?.addEventListener(
        'input',
        function () {

            const temCensura =
                verificarCensura(
                    this.value
                );

            if (censurePreview) {

                censurePreview.style.display =
                    temCensura
                        ? 'block'
                        : 'none';
            }

            if (
                temCensura &&
                censurePreviewText
            ) {

                censurePreviewText.textContent =
                    'Palavra ofensiva detectada. Ela será censurada automaticamente ao publicar.';
            }
        }
    );

    // =================================================
    // PUBLICAR
    // =================================================

    btnPostar?.addEventListener(
        'click',
        async function () {

            const texto =
                perguntaTexto?.value.trim() ||
                '';

            if (!texto) {

                alert(
                    'Escreva sua pergunta antes de publicar.'
                );

                perguntaTexto?.focus();

                return;
            }

            if (texto.length < 5) {

                alert(
                    'Sua pergunta é muito curta. Escreva mais detalhes.'
                );

                perguntaTexto?.focus();

                return;
            }

            const materia =
                perguntaMateria?.value ||
                'Geral';

            const htmlOriginal =
                this.innerHTML;

            this.disabled = true;

            this.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Publicando...
            `;

            try {

                const retorno =
                    await requisicaoJson(
                        CHAT_ACTION_URL,
                        {
                            acao:
                                'criar_pergunta',

                            texto:
                                texto,

                            materia:
                                materia
                        }
                    );

                const novaPergunta =
                    retorno.pergunta;

                chatData.perguntas.push({
                    ...novaPergunta
                });

                // =====================================
                // SE NÃO HÁ FILTRO, JÁ COLOCA NO EXPLORAR
                // =====================================

                const params =
                    new URLSearchParams(
                        window.location.search
                    );

                const temFiltro =
                    Boolean(
                        params.get('busca')
                    ) ||
                    (
                        params.get('materia') &&
                        params.get('materia') !==
                        'todas'
                    );

                if (!temFiltro) {

                    todasPerguntas.unshift({
                        ...novaPergunta
                    });
                }

                if (perguntaTexto) {

                    perguntaTexto.value = '';
                }

                if (censurePreview) {

                    censurePreview.style.display =
                        'none';
                }

                renderizarMinhasPerguntas();
                renderizarExplorar();

                atualizarContadores();

            } catch (erro) {

                console.error(
                    'Erro ao publicar pergunta:',
                    erro
                );

                alert(
                    erro.message ||
                    'Não foi possível publicar a pergunta.'
                );

            } finally {

                this.disabled = false;
                this.innerHTML =
                    htmlOriginal;
            }
        }
    );

    // =================================================
    // CTRL + ENTER PARA PUBLICAR
    // =================================================

    perguntaTexto?.addEventListener(
        'keydown',
        function (e) {

            if (
                e.key === 'Enter' &&
                e.ctrlKey
            ) {

                e.preventDefault();

                btnPostar?.click();
            }
        }
    );

    // =================================================
    // MODAL DE EXCLUSÃO
    // =================================================

    const modalExcluir =
        document.getElementById(
            'modal-excluir'
        );

    const btnConfirmarExclusao =
        document.getElementById(
            'confirmar-exclusao'
        );

    const btnCancelarExclusao =
        document.getElementById(
            'cancelar-exclusao'
        );

    let idParaExcluir = null;

    function abrirModalExclusao(id) {

        idParaExcluir = id;

        if (modalExcluir) {

            modalExcluir.style.display =
                'flex';
        }
    }

    function fecharModalExclusao() {

        if (modalExcluir) {

            modalExcluir.style.display =
                'none';
        }

        idParaExcluir = null;
    }

    btnCancelarExclusao?.addEventListener(
        'click',
        fecharModalExclusao
    );

    modalExcluir?.addEventListener(
        'click',
        function (e) {

            if (e.target === modalExcluir) {

                fecharModalExclusao();
            }
        }
    );

    // =================================================
    // CONFIRMAR EXCLUSÃO
    // =================================================

    btnConfirmarExclusao?.addEventListener(
        'click',
        async function () {

            if (!idParaExcluir) {
                return;
            }

            this.disabled = true;

            try {

                await requisicaoJson(
                    CHAT_ACTION_URL,
                    {
                        acao:
                            'excluir_pergunta',

                        pergunta_id:
                            idParaExcluir
                    }
                );

                chatData.perguntas =
                    chatData.perguntas.filter(
                        p =>
                            String(p.id) !==
                            String(idParaExcluir)
                    );

                todasPerguntas =
                    todasPerguntas.filter(
                        p =>
                            String(p.id) !==
                            String(idParaExcluir)
                    );

                interacoes.curtidas =
                    interacoes.curtidas.filter(
                        id =>
                            String(id) !==
                            String(idParaExcluir)
                    );

                interacoes.salvos =
                    interacoes.salvos.filter(
                        id =>
                            String(id) !==
                            String(idParaExcluir)
                    );

                fecharModalExclusao();

                renderizarMinhasPerguntas();
                renderizarExplorar();

                atualizarContadores();

                // Salva as interações,
                // mas a exclusão da pergunta não depende disso.
                salvarInteracoes().catch(
                    console.error
                );

            } catch (erro) {

                console.error(
                    'Erro ao excluir pergunta:',
                    erro
                );

                alert(
                    erro.message ||
                    'Não foi possível excluir a pergunta.'
                );

            } finally {

                this.disabled = false;
            }
        }
    );

    // =================================================
    // ABAS
    // =================================================

    document.querySelectorAll(
        '.aba-btn'
    ).forEach(function (botao) {

        botao.addEventListener(
            'click',
            function () {

                const aba =
                    this.dataset.aba;

                document.querySelectorAll(
                    '.aba-btn'
                ).forEach(
                    item =>
                        item.classList.remove(
                            'ativo'
                        )
                );

                document.querySelectorAll(
                    '.aba-conteudo'
                ).forEach(
                    item =>
                        item.classList.remove(
                            'ativo'
                        )
                );

                this.classList.add(
                    'ativo'
                );

                document
                    .getElementById(
                        `aba-${aba}`
                    )
                    ?.classList.add(
                        'ativo'
                    );

                const url =
                    new URL(
                        window.location.href
                    );

                url.searchParams.set(
                    'aba',
                    aba
                );

                window.history.replaceState(
                    {},
                    '',
                    url
                );
            }
        );
    });

    // =================================================
    // FILTROS
    // =================================================

    const buscaInput =
        document.getElementById(
            'busca-input'
        );

    const btnBuscar =
        document.getElementById(
            'btn-buscar'
        );

    const filtroMateriaSelect =
        document.getElementById(
            'filtro-materia'
        );

    const btnLimparFiltros =
        document.getElementById(
            'btn-limpar-filtros'
        );

    function aplicarFiltros() {

        const busca =
            buscaInput?.value.trim() ||
            '';

        const materia =
            filtroMateriaSelect?.value ||
            'todas';

        const url =
            new URL(
                window.location.href
            );

        url.searchParams.set(
            'aba',
            'explorar'
        );

        if (busca) {

            url.searchParams.set(
                'busca',
                busca
            );

        } else {

            url.searchParams.delete(
                'busca'
            );
        }

        if (materia !== 'todas') {

            url.searchParams.set(
                'materia',
                materia
            );

        } else {

            url.searchParams.delete(
                'materia'
            );
        }

        window.location.href =
            url.toString();
    }

    btnBuscar?.addEventListener(
        'click',
        aplicarFiltros
    );

    buscaInput?.addEventListener(
        'keydown',
        function (e) {

            if (e.key === 'Enter') {

                e.preventDefault();

                aplicarFiltros();
            }
        }
    );

    filtroMateriaSelect?.addEventListener(
        'change',
        aplicarFiltros
    );

    btnLimparFiltros?.addEventListener(
        'click',
        function () {

            const url =
                new URL(
                    window.location.href
                );

            url.searchParams.delete(
                'busca'
            );

            url.searchParams.delete(
                'materia'
            );

            url.searchParams.set(
                'aba',
                'explorar'
            );

            window.location.href =
                url.toString();
        }
    );

    // =================================================
    // HEADER
    // =================================================

    document
        .getElementById(
            'icon-configuracoes'
        )
        ?.addEventListener(
            'click',
            function () {

                window.location.href =
                    FOAG_CONFIG.pages.configuracoes;
            }
        );

    document
        .getElementById(
            'icon-perfil'
        )
        ?.addEventListener(
            'click',
            function () {

                window.location.href =
                    FOAG_CONFIG.pages.perfil;
            }
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

    const confirmLogout =
        document.getElementById(
            'confirm-logout'
        );

    const cancelLogout =
        document.getElementById(
            'cancel-logout'
        );

    iconSair?.addEventListener(
        'click',
        function () {

            if (logoutModal) {

                logoutModal.style.display =
                    'flex';
            }
        }
    );

    cancelLogout?.addEventListener(
        'click',
        function () {

            if (logoutModal) {

                logoutModal.style.display =
                    'none';
            }
        }
    );

    logoutModal?.addEventListener(
        'click',
        function (e) {

            if (
                e.target === logoutModal
            ) {

                logoutModal.style.display =
                    'none';
            }
        }
    );

    confirmLogout?.addEventListener(
        'click',
        function () {

            window.location.href =
                FOAG_CONFIG.pages.logout;
        }
    );

    // =================================================
    // ESC
    // =================================================

    document.addEventListener(
        'keydown',
        function (e) {

            if (e.key !== 'Escape') {
                return;
            }

            if (logoutModal) {

                logoutModal.style.display =
                    'none';
            }

            fecharModalExclusao();

            document.querySelectorAll(
                '.resposta-form'
            ).forEach(function (form) {

                form.style.display =
                    'none';

                form
                    .querySelector(
                        '.resposta-textarea'
                    )
                    ?.blur();
            });

            document.querySelectorAll(
                '.respostas-container'
            ).forEach(function (container) {

                container.classList.remove(
                    'visivel'
                );
            });
        }
    );

    // =================================================
    // INICIALIZAÇÃO
    // =================================================

    renderizarMinhasPerguntas();
    renderizarExplorar();
    atualizarContadores();

    console.log(
        'Comunidade FOAG carregada ✅'
    );
});