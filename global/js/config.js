/* =========================================================
   FOAG — CONFIGURAÇÃO GLOBAL DO FRONTEND
   Centraliza páginas, APIs e caminhos usados pelo JavaScript.
========================================================= */

(() => {
    "use strict";

    function descobrirBaseUrl() {
        const script =
            document.currentScript ||
            [...document.scripts].find(item =>
                item.src.includes('/global/js/config.js')
            );

        if (!script?.src) {
            return '';
        }

        try {
            const url = new URL(script.src, window.location.href);

            return url.pathname
                .replace(/\/global\/js\/config\.js$/, '')
                .replace(/\/+$/, '');
        } catch (erro) {
            console.warn('FOAG Config: não foi possível descobrir a URL base.', erro);
            return '';
        }
    }

    const baseUrl = descobrirBaseUrl();

    function url(caminho = '') {
        const limpo = String(caminho || '').replace(/^\/+/, '');
        return limpo ? `${baseUrl}/${limpo}` : baseUrl;
    }

    function api(caminho = '') {
        const limpo = String(caminho || '').replace(/^\/+/, '');
        return url(limpo ? `api/${limpo}` : 'api');
    }

    const pages = {
        inicio: url('inicioo/inicio.php'),
        estudos: url('estudos/estudos.php'),
        agenda: url('bloco/agenda.php'),
        calendario: url('calend/calendario.php'),
        boletim: url('notas/notas.php'),
        comunidade: url('comunidade/comunidade.php'),
        ranking: url('rank/rank.php'),
        loja: url('loja/loja.php'),
        perfil: url('perfil/perfil.php'),
        configuracoes: url('configuracoes/configuracoes.php'),
        login: url('login/index.php'),
        cadastro: url('cadastro/cadastro.php'),
        recuperarSenha: url('mudarsenha/esqueci.php'),
        logout: api('auth/logout.php')
    };

    const endpoints = {
        agendaSalvar: api('agenda/salvar_agenda.php'),
        agendaHorario: api('agenda/horario_api.php'),

        calendarioSalvar: api('calendario/salvar_calendario.php'),
        calendarioTarefaSalvar: api('calendario/salvar_tarefa_calendario.php'),

        comunidadeChatSalvar: api('comunidade/salvar_chat.php'),
        comunidadeInteracao: api('comunidade/interacao.php'),
        comunidadeInteracoesSalvar: api('comunidade/salvar_interacao.php'),

        configuracoesExcluirConta: api('configuracoes/excluir.php'),

        estudosMateriaSalvar: api('estudos/salvar_materia.php'),
        estudosMateriaEditar: api('estudos/editar_materia.php'),
        estudosMateriaExcluir: api('estudos/excluir_materia.php'),

        flashcardsBaralhoSalvar: api('estudos/flashcards/salvar_baralho.php'),
        flashcardsCartaoSalvar: api('estudos/flashcards/salvar_cartao.php'),
        flashcardsCartaoEditar: api('estudos/flashcards/editar_cartao.php'),
        flashcardsCartaoExcluir: api('estudos/flashcards/excluir_cartao.php'),
        flashcardsRevisaoSalvar: api('estudos/flashcards/salvar_revisao.php'),

        pomodoroSalvar: api('estudos/pomodoro/salvar_pomodoro.php'),

        inicioAnotacaoSalvar: api('inicio/salvar_anotacao.php'),
        estrelasAdicionar: api('estrelas/adicionar_estrelas.php'),

        lojaSalvar: api('loja/salvar_loja.php'),
        lojaEstrelaSalvar: api('loja/salvar_estrela.php'),
        lojaDados: api('loja/loja_data.php'),

        cursorUsuario: api('usuario/cursor_usuario.php'),

        authLogin: api('auth/login.php'),
        authLogout: api('auth/logout.php'),
        authCadastro: api('auth/cadastro.php'),
        authRedefinirSenha: api('auth/redefinir_senha.php')
    };

    window.FOAG_CONFIG = Object.freeze({
        baseUrl,
        apiUrl: api(),
        url,
        api,
        pages: Object.freeze(pages),
        endpoints: Object.freeze(endpoints)
    });
})();
