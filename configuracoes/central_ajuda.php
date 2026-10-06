<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Central de Ajuda do FOAG - encontre respostas e orientações para utilizar a plataforma.">
    <meta name="theme-color" content="#168fe8">
    <title>Central de Ajuda - FOAG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --azul-principal: #168fe8;
            --azul-escuro: #0874c4;
            --azul-claro: #eaf6ff;
            --azul-muito-claro: #f5fbff;
            --texto-principal: #263238;
            --texto-secundario: #68757e;
            --branco: #ffffff;
            --borda: #dcecf7;
            --verde: #198754;
            --amarelo: #f4a825;
            --vermelho: #dc3545;
            --sombra: 0 12px 35px rgba(28, 102, 153, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #dff2ff 0%, #f8fcff 45%, #ffffff 100%);
            color: var(--texto-principal);
        }

        button,
        input,
        textarea,
        select,
        a {
            font: inherit;
        }

        button {
            border: none;
        }

        a {
            color: inherit;
        }

        button:focus-visible,
        input:focus-visible,
        textarea:focus-visible,
        select:focus-visible,
        a:focus-visible {
            outline: 3px solid rgba(22, 143, 232, 0.25);
            outline-offset: 3px;
        }

        .pagina {
            width: 100%;
            max-width: 1250px;
            margin: 0 auto;
            padding: 45px 20px;
        }

        .cabecalho {
            position: relative;
            overflow: hidden;
            margin-bottom: 25px;
            padding: 45px;
            border-radius: 24px;
            background: linear-gradient(135deg, #0874c4, #168fe8, #48adf5);
            color: var(--branco);
            box-shadow: var(--sombra);
        }

        .cabecalho::before,
        .cabecalho::after {
            position: absolute;
            content: "";
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .cabecalho::before {
            top: -90px;
            right: -40px;
            width: 250px;
            height: 250px;
        }

        .cabecalho::after {
            right: 200px;
            bottom: -130px;
            width: 210px;
            height: 210px;
        }

        .cabecalho-conteudo {
            position: relative;
            z-index: 1;
            max-width: 820px;
        }

        .identificacao {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 17px;
            padding: 7px 13px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.13);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .identificacao-ponto {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #b9f6ca;
        }

        .cabecalho h1 {
            margin-bottom: 10px;
            font-size: clamp(2rem, 4vw, 3.2rem);
            line-height: 1.15;
        }

        .cabecalho p {
            max-width: 700px;
            margin-bottom: 25px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            line-height: 1.7;
        }

        .busca {
            position: relative;
            width: 100%;
            max-width: 700px;
        }

        .busca input {
            width: 100%;
            height: 58px;
            padding: 0 58px 0 20px;
            border: 2px solid transparent;
            border-radius: 14px;
            background: var(--branco);
            color: var(--texto-principal);
            font-size: 0.94rem;
            box-shadow: 0 10px 24px rgba(0, 72, 124, 0.18);
            transition: 0.2s;
        }

        .busca input:focus {
            border-color: #8fd0ff;
            outline: none;
        }

        .busca input::placeholder {
            color: #8998a1;
        }

        .busca-icone {
            position: absolute;
            top: 50%;
            right: 18px;
            transform: translateY(-50%);
            color: var(--azul-principal);
            font-size: 1.25rem;
            pointer-events: none;
        }

        .acoes-superiores {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 25px;
        }

        .botao {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 11px 18px;
            border: 1px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: transform 0.2s, background 0.2s, box-shadow 0.2s;
        }

        .botao:hover {
            transform: translateY(-2px);
        }

        .botao-principal {
            background: var(--azul-principal);
            color: var(--branco);
            box-shadow: 0 7px 18px rgba(22, 143, 232, 0.24);
        }

        .botao-principal:hover {
            background: var(--azul-escuro);
        }

        .botao-secundario {
            border-color: var(--borda);
            background: var(--branco);
            color: var(--texto-principal);
        }

        .botao-secundario:hover {
            background: var(--azul-muito-claro);
        }

        .titulo-secao {
            margin-bottom: 18px;
        }

        .titulo-secao h2 {
            margin-bottom: 5px;
            color: var(--texto-principal);
            font-size: 1.35rem;
        }

        .titulo-secao p {
            color: var(--texto-secundario);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .atalhos {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 38px;
        }

        .atalho {
            display: block;
            padding: 23px 20px;
            border: 1px solid var(--borda);
            border-radius: 16px;
            background: var(--branco);
            box-shadow: var(--sombra);
            text-decoration: none;
            transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .atalho:hover {
            transform: translateY(-4px);
            border-color: #9ed4f8;
            box-shadow: 0 16px 35px rgba(28, 102, 153, 0.16);
        }

        .atalho-icone {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            margin-bottom: 15px;
            border-radius: 13px;
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-size: 1.25rem;
            font-weight: 700;
        }

        .atalho h3 {
            margin-bottom: 6px;
            color: var(--texto-principal);
            font-size: 0.98rem;
        }

        .atalho p {
            color: var(--texto-secundario);
            font-size: 0.8rem;
            line-height: 1.6;
        }

        .layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 25px;
            align-items: start;
        }

        .categorias {
            position: sticky;
            top: 20px;
            padding: 22px;
            border: 1px solid var(--borda);
            border-radius: 18px;
            background: var(--branco);
            box-shadow: var(--sombra);
        }

        .categorias h2 {
            margin-bottom: 14px;
            font-size: 1rem;
        }

        .lista-categorias {
            display: grid;
            gap: 7px;
        }

        .categoria-btn {
            width: 100%;
            padding: 11px 12px;
            border-radius: 9px;
            background: transparent;
            color: var(--texto-secundario);
            cursor: pointer;
            text-align: left;
            font-size: 0.82rem;
            transition: 0.2s;
        }

        .categoria-btn:hover,
        .categoria-btn.ativa {
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-weight: 600;
        }

        .status {
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid var(--borda);
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--texto-secundario);
            font-size: 0.78rem;
            line-height: 1.5;
        }

        .status-ponto {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--verde);
            box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.12);
        }

        .conteudo-ajuda {
            min-width: 0;
        }

        .resultado-busca {
            display: none;
            margin-bottom: 15px;
            padding: 13px 16px;
            border: 1px solid var(--borda);
            border-radius: 10px;
            background: var(--azul-muito-claro);
            color: var(--texto-secundario);
            font-size: 0.84rem;
        }

        .resultado-busca.visivel {
            display: block;
        }

        .faq-lista {
            display: grid;
            gap: 12px;
        }

        .faq-item {
            overflow: hidden;
            border: 1px solid var(--borda);
            border-radius: 13px;
            background: var(--branco);
            box-shadow: 0 7px 20px rgba(28, 102, 153, 0.07);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .faq-item:hover {
            border-color: #b7dcf5;
        }

        .faq-item.aberto {
            border-color: #92cdf5;
            box-shadow: 0 10px 25px rgba(28, 102, 153, 0.11);
        }

        .faq-pergunta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            width: 100%;
            padding: 19px 20px;
            background: var(--branco);
            color: var(--texto-principal);
            cursor: pointer;
            text-align: left;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .faq-pergunta:hover {
            background: #fbfdff;
        }

        .faq-sinal {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 29px;
            height: 29px;
            border-radius: 8px;
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-size: 1.1rem;
            transition: transform 0.25s;
        }

        .faq-item.aberto .faq-sinal {
            transform: rotate(45deg);
        }

        .faq-resposta {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .faq-resposta-conteudo {
            padding: 0 20px 20px;
            color: #52616a;
            font-size: 0.87rem;
            line-height: 1.8;
        }

        .faq-resposta-conteudo p {
            margin-bottom: 10px;
        }

        .faq-resposta-conteudo p:last-child {
            margin-bottom: 0;
        }

        .faq-resposta-conteudo ul,
        .faq-resposta-conteudo ol {
            margin: 8px 0 10px 20px;
        }

        .faq-resposta-conteudo li {
            margin-bottom: 6px;
        }

        .faq-resposta-conteudo strong {
            color: var(--texto-principal);
        }

        .faq-resposta-conteudo code {
            padding: 2px 6px;
            border-radius: 5px;
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-family: Consolas, monospace;
            font-size: 0.8rem;
        }

        .nenhum-resultado {
            display: none;
            padding: 35px 20px;
            border: 1px dashed #a9d3ef;
            border-radius: 14px;
            background: var(--azul-muito-claro);
            text-align: center;
        }

        .nenhum-resultado.visivel {
            display: block;
        }

        .nenhum-resultado h3 {
            margin-bottom: 7px;
            color: var(--azul-escuro);
            font-size: 1rem;
        }

        .nenhum-resultado p {
            color: var(--texto-secundario);
            font-size: 0.84rem;
        }

        .contato-area {
            margin-top: 40px;
            padding: 30px;
            border-radius: 18px;
            background: linear-gradient(135deg, #0874c4, #168fe8);
            color: var(--branco);
            box-shadow: var(--sombra);
        }

        .contato-area h2 {
            margin-bottom: 8px;
            font-size: 1.35rem;
        }

        .contato-area > p {
            max-width: 700px;
            margin-bottom: 22px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.9rem;
            line-height: 1.7;
        }

        .contato-opcoes {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 13px;
        }

        .contato-card {
            padding: 19px;
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.11);
        }

        .contato-card strong {
            display: block;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .contato-card p {
            margin-bottom: 12px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.78rem;
            line-height: 1.5;
        }

        .contato-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 39px;
            padding: 9px 14px;
            border-radius: 8px;
            background: var(--branco);
            color: var(--azul-escuro);
            text-decoration: none;
            font-size: 0.78rem;
            font-weight: 600;
            transition: 0.2s;
        }

        .contato-link:hover {
            transform: translateY(-2px);
            background: var(--azul-claro);
        }

        .rodape {
            margin-top: 35px;
            padding: 25px 0 5px;
            border-top: 1px solid var(--borda);
            text-align: center;
        }

        .rodape-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .rodape-links a {
            color: var(--azul-principal);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .rodape-links a:hover {
            text-decoration: underline;
        }

        .rodape p {
            color: var(--texto-secundario);
            font-size: 0.75rem;
        }

        .voltar-topo {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--azul-principal);
            color: var(--branco);
            cursor: pointer;
            font-size: 1.2rem;
            box-shadow: 0 8px 22px rgba(22, 143, 232, 0.35);
            transition: 0.2s;
        }

        .voltar-topo:hover {
            transform: translateY(-3px);
            background: var(--azul-escuro);
        }

        .voltar-topo.visivel {
            display: flex;
        }

        @media (max-width: 1000px) {
            .atalhos {
                grid-template-columns: repeat(2, 1fr);
            }

            .layout {
                grid-template-columns: 1fr;
            }

            .categorias {
                position: static;
            }

            .lista-categorias {
                grid-template-columns: repeat(3, 1fr);
            }

            .status {
                display: none;
            }
        }

        @media (max-width: 650px) {
            .pagina {
                padding: 20px 12px 35px;
            }

            .cabecalho {
                padding: 30px 23px;
                border-radius: 18px;
            }

            .cabecalho h1 {
                font-size: 1.9rem;
            }

            .cabecalho p {
                font-size: 0.88rem;
            }

            .busca input {
                height: 54px;
                font-size: 0.85rem;
            }

            .acoes-superiores {
                display: grid;
                grid-template-columns: 1fr;
            }

            .botao {
                width: 100%;
            }

            .atalhos {
                grid-template-columns: 1fr;
            }

            .lista-categorias {
                grid-template-columns: 1fr;
            }

            .faq-pergunta {
                padding: 17px 16px;
                font-size: 0.85rem;
            }

            .faq-resposta-conteudo {
                padding: 0 16px 18px;
                font-size: 0.82rem;
            }

            .contato-area {
                padding: 25px 20px;
            }

            .contato-opcoes {
                grid-template-columns: 1fr;
            }

            .voltar-topo {
                right: 15px;
                bottom: 15px;
            }
        }

        @media print {
            body {
                background: #ffffff;
            }

            .cabecalho {
                padding: 20px;
                border-radius: 0;
                background: #ffffff;
                color: #000000;
                box-shadow: none;
            }

            .cabecalho p {
                color: #444444;
            }

            .busca,
            .acoes-superiores,
            .atalhos,
            .categorias,
            .contato-area,
            .voltar-topo {
                display: none !important;
            }

            .pagina {
                max-width: none;
                padding: 0;
            }

            .layout {
                display: block;
            }

            .faq-item {
                break-inside: avoid;
                box-shadow: none;
            }

            .faq-resposta {
                max-height: none !important;
            }
        }
    </style>
    <link rel="stylesheet" href="../global/css/cursor.css">
</head>

<body>
    <div class="pagina">
        <header class="cabecalho">
            <div class="cabecalho-conteudo">
                <span class="identificacao">
                    <span class="identificacao-ponto"></span>
                    Suporte e orientações
                </span>

                <h1>Como podemos ajudar?</h1>

                <p>
                    Encontre respostas sobre cadastro, agenda, calendário,
                    Pomodoro, perfil, privacidade e outras funções do FOAG.
                </p>

                <div class="busca">
                    <input
                        type="search"
                        id="campo-busca"
                        placeholder="Digite uma dúvida, por exemplo: alterar senha"
                        aria-label="Pesquisar na Central de Ajuda"
                        autocomplete="off"
                    >

                    <span class="busca-icone">⌕</span>
                </div>
            </div>
        </header>

        <div class="acoes-superiores">
            <a class="botao botao-principal" href="configuracoes.php">
                ← Voltar às configurações
            </a>

            <a class="botao botao-secundario" href="termos.php">
                Termos de Uso
            </a>

            <a class="botao botao-secundario" href="politica_privacidade.php">
                Política de Privacidade
            </a>

            <button class="botao botao-secundario" type="button" onclick="window.print()">
                Imprimir
            </button>
        </div>

        <section>
            <div class="titulo-secao">
                <h2>Acessos rápidos</h2>

                <p>
                    Selecione uma área para encontrar a orientação desejada.
                </p>
            </div>

            <div class="atalhos">
                <a class="atalho" href="../perfil/perfil.php">
                    <span class="atalho-icone">P</span>

                    <h3>Meu perfil</h3>

                    <p>
                        Consulte ou altere suas informações pessoais e foto.
                    </p>
                </a>

                <a class="atalho" href="../agenda/agenda.php">
                    <span class="atalho-icone">A</span>

                    <h3>Agenda e calendário</h3>

                    <p>
                        Organize tarefas, compromissos, provas e atividades.
                    </p>
                </a>

                <a class="atalho" href="../pomodoro/pomodoro.php">
                    <span class="atalho-icone">25</span>

                    <h3>Pomodoro</h3>

                    <p>
                        Configure sessões de foco, pausas e histórico de estudo.
                    </p>
                </a>

                <a class="atalho" href="mailto:rafaella@gmail.com?subject=Ajuda%20com%20o%20FOAG">
                    <span class="atalho-icone">@</span>

                    <h3>Falar com o suporte</h3>

                    <p>
                        Envie uma mensagem para os responsáveis pela plataforma.
                    </p>
                </a>
            </div>
        </section>

        <div class="layout">
            <aside class="categorias">
                <h2>Categorias</h2>

                <div class="lista-categorias">
                    <button class="categoria-btn ativa" type="button" data-categoria="todas">
                        Todas as dúvidas
                    </button>

                    <button class="categoria-btn" type="button" data-categoria="conta">
                        Conta e acesso
                    </button>

                    <button class="categoria-btn" type="button" data-categoria="perfil">
                        Perfil e configurações
                    </button>

                    <button class="categoria-btn" type="button" data-categoria="agenda">
                        Agenda e calendário
                    </button>

                    <button class="categoria-btn" type="button" data-categoria="pomodoro">
                        Pomodoro
                    </button>

                    <button class="categoria-btn" type="button" data-categoria="dados">
                        Dados e privacidade
                    </button>

                    <button class="categoria-btn" type="button" data-categoria="problemas">
                        Problemas técnicos
                    </button>
                </div>

                <div class="status">
                    <div class="status-item">
                        <span class="status-ponto"></span>

                        Sistema disponível
                    </div>
                </div>
            </aside>

            <main class="conteudo-ajuda">
                <div class="titulo-secao">
                    <h2>Perguntas frequentes</h2>

                    <p>
                        Clique em uma pergunta para visualizar a resposta.
                    </p>
                </div>

                <div class="resultado-busca" id="resultado-busca"></div>

                <div class="faq-lista" id="faq-lista">
                    <article class="faq-item" data-categoria="conta" data-termos="criar conta cadastro registrar usuário email senha">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como criar uma conta no FOAG?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Na página inicial, clique em
                                    <strong>Criar conta</strong> ou
                                    <strong>Cadastre-se</strong>.
                                </p>

                                <ol>
                                    <li>Informe seu nome e e-mail;</li>
                                    <li>Cadastre uma senha segura;</li>
                                    <li>Preencha os dados acadêmicos solicitados;</li>
                                    <li>Confirme o cadastro.</li>
                                </ol>

                                <p>
                                    Utilize informações verdadeiras e um e-mail ao
                                    qual você tenha acesso.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="conta" data-termos="entrar login acesso conta email senha">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como entrar na minha conta?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Acesse a página de login, informe o e-mail
                                    cadastrado e sua senha. Depois, clique em
                                    <strong>Entrar</strong>.
                                </p>

                                <p>
                                    Caso o sistema retorne para o login ao abrir
                                    uma página, sua sessão pode ter expirado.
                                    Entre novamente na conta.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="conta" data-termos="esqueci senha recuperar redefinir alterar">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Esqueci minha senha. O que devo fazer?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Utilize a opção <strong>Esqueci minha senha</strong>
                                    na página de login, caso ela esteja disponível.
                                </p>

                                <p>
                                    Se o recurso ainda não estiver ativo, entre em
                                    contato com o suporte pelo e-mail
                                    <strong>rafaella@gmail.com</strong>.
                                    Nunca envie sua senha atual.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="conta" data-termos="sair logout encerrar sessão conta">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como sair da minha conta?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Clique no ícone de saída localizado no cabeçalho
                                    ou menu lateral. Depois, confirme a opção
                                    <strong>Sair</strong>.
                                </p>

                                <p>
                                    Em computadores compartilhados, sempre encerre
                                    sua sessão antes de fechar o navegador.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="perfil" data-termos="editar perfil nome foto escola série informações">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como editar meu perfil?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Abra a página <strong>Perfil</strong> e clique
                                    no botão <strong>Editar perfil</strong>.
                                </p>

                                <p>
                                    Altere os dados desejados e clique em
                                    <strong>Salvar alterações</strong>.
                                    Verifique se a foto enviada está em um formato
                                    aceito, como JPG ou PNG.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="perfil" data-termos="modo escuro dark tema claro aparência configuração">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como ativar o modo escuro?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Acesse <strong>Configurações</strong> e localize
                                    a opção de aparência ou modo escuro.
                                </p>

                                <p>
                                    Ao ativar a opção, o FOAG salvará sua preferência
                                    no navegador ou na sua conta, dependendo da
                                    configuração do sistema.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="perfil" data-termos="configurações notificação preferência alterar">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Onde encontro as configurações?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Clique no ícone de configurações localizado no
                                    menu do FOAG.
                                </p>

                                <p>
                                    Nessa página você poderá acessar preferências,
                                    privacidade, Termos de Uso, Central de Ajuda e
                                    outras opções da conta.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="agenda" data-termos="adicionar tarefa atividade agenda compromisso prova trabalho">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como adicionar uma tarefa na agenda?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <ol>
                                    <li>Abra a página Agenda;</li>
                                    <li>Clique em adicionar nova tarefa;</li>
                                    <li>Informe o título e a data;</li>
                                    <li>Adicione horário, matéria ou descrição;</li>
                                    <li>Clique em salvar.</li>
                                </ol>

                                <p>
                                    Revise a data cadastrada para evitar que a
                                    atividade apareça no dia errado.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="agenda" data-termos="editar excluir apagar tarefa evento compromisso">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como editar ou excluir uma tarefa?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Clique na tarefa que deseja alterar. Selecione
                                    <strong>Editar</strong> para modificar as
                                    informações ou <strong>Excluir</strong> para
                                    removê-la.
                                </p>

                                <p>
                                    A exclusão pode ser permanente. Confirme se
                                    escolheu a tarefa correta antes de continuar.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="agenda" data-termos="calendário data mês semana evento não aparece">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Por que minha tarefa não aparece no calendário?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>Confira os seguintes pontos:</p>

                                <ul>
                                    <li>Se a tarefa foi realmente salva;</li>
                                    <li>Se a data está correta;</li>
                                    <li>Se você está visualizando o mês correto;</li>
                                    <li>Se a tarefa pertence à conta conectada;</li>
                                    <li>Se o navegador atualizou a página.</li>
                                </ul>

                                <p>
                                    Depois de salvar, atualize a página usando
                                    <code>Ctrl + F5</code>.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="agenda" data-termos="disciplina matéria adicionar nova disciplina">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como adicionar uma nova disciplina?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Na página de agenda, calendário ou matérias,
                                    clique em <strong>Adicionar disciplina</strong>.
                                </p>

                                <p>
                                    Informe o nome, escolha uma cor e salve. A nova
                                    disciplina poderá ser utilizada em tarefas,
                                    provas, trabalhos e horários.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="pomodoro" data-termos="pomodoro iniciar temporizador foco pausa">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como utilizar o Pomodoro?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Abra a página do Pomodoro, escolha o tempo de
                                    foco e clique em <strong>Iniciar</strong>.
                                </p>

                                <p>
                                    O método mais comum utiliza 25 minutos de foco
                                    e 5 minutos de pausa. Você poderá ajustar os
                                    tempos conforme sua preferência.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="pomodoro" data-termos="pomodoro salvar histórico sessão produtividade json">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            O histórico do Pomodoro é salvo?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Quando o salvamento estiver configurado, cada
                                    sessão concluída poderá ser registrada no
                                    histórico do usuário.
                                </p>

                                <p>
                                    Se a sessão não aparecer, verifique se você está
                                    conectado, se o cronômetro foi concluído e se
                                    o arquivo de armazenamento possui permissão de
                                    escrita.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="pomodoro" data-termos="pomodoro zerar reiniciar pausar continuar">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Posso pausar ou reiniciar o cronômetro?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Sim. Utilize o botão <strong>Pausar</strong>
                                    para interromper temporariamente e
                                    <strong>Continuar</strong> para retomar.
                                </p>

                                <p>
                                    O botão <strong>Reiniciar</strong> retorna o
                                    cronômetro ao tempo inicial e pode cancelar a
                                    sessão atual.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="dados" data-termos="dados pessoais privacidade coleta informações">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Quais dados o FOAG armazena?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    O FOAG poderá armazenar dados de cadastro,
                                    perfil, matérias, tarefas, notas, horários,
                                    metas, preferências e registros do Pomodoro.
                                </p>

                                <p>
                                    Consulte a
                                    <a href="politica_privacidade.php">
                                        Política de Privacidade
                                    </a>
                                    para conhecer todos os detalhes.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="dados" data-termos="excluir conta apagar dados remoção">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Como solicitar a exclusão da minha conta?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Utilize a opção de exclusão disponível nas
                                    configurações da conta ou envie uma solicitação
                                    para <strong>rafaella@gmail.com</strong>.
                                </p>

                                <p>
                                    Informe seu nome e o e-mail cadastrado.
                                    Nunca envie sua senha.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="dados" data-termos="senha segurança json hash proteção">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Minha senha fica protegida?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    A senha deve ser armazenada utilizando
                                    <strong>hash seguro</strong>, por exemplo com
                                    <code>password_hash()</code> no PHP.
                                </p>

                                <p>
                                    A verificação do login deve utilizar
                                    <code>password_verify()</code>. A senha nunca
                                    deve ser salva diretamente como texto no JSON.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="problemas" data-termos="botão não funciona clique javascript erro">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Um botão não está funcionando. O que fazer?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>Verifique:</p>

                                <ul>
                                    <li>Se o arquivo JavaScript foi carregado;</li>
                                    <li>Se o ID usado no HTML é igual ao usado no JavaScript;</li>
                                    <li>Se o caminho do arquivo está correto;</li>
                                    <li>Se existem erros no console do navegador;</li>
                                    <li>Se o código executa depois que o HTML foi carregado.</li>
                                </ul>

                                <p>
                                    Abra o console usando <code>F12</code> e
                                    consulte a aba <strong>Console</strong>.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="problemas" data-termos="página sem estilo css não carrega caminho">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            A página abriu sem estilo. Como corrigir?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Normalmente isso acontece quando o caminho do
                                    arquivo CSS está incorreto.
                                </p>

                                <p>
                                    Confirme o valor utilizado em:
                                </p>

                                <p>
                                    <code>&lt;link rel="stylesheet" href="..."&gt;</code>
                                </p>

                                <p>
                                    Considere a pasta atual da página. Por exemplo,
                                    talvez seja necessário utilizar
                                    <code>../css/arquivo.css</code>.
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="problemas" data-termos="login redireciona página sessão php">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Por que uma página me redireciona para o login?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Isso geralmente acontece quando a página não
                                    reconhece a sessão do usuário.
                                </p>

                                <p>Confira se:</p>

                                <ul>
                                    <li><code>session_start();</code> foi executado;</li>
                                    <li>O mesmo nome de sessão é usado em todas as páginas;</li>
                                    <li>A sessão é criada corretamente no login;</li>
                                    <li>Não existe código destruindo a sessão;</li>
                                    <li>O caminho de redirecionamento está correto.</li>
                                </ul>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item" data-categoria="problemas" data-termos="informações não salvam json permissão pasta">
                        <button class="faq-pergunta" type="button" aria-expanded="false">
                            Por que minhas informações não estão sendo salvas?

                            <span class="faq-sinal">+</span>
                        </button>

                        <div class="faq-resposta">
                            <div class="faq-resposta-conteudo">
                                <p>
                                    Quando o FOAG utiliza arquivos JSON, verifique:
                                </p>

                                <ul>
                                    <li>Se o arquivo JSON existe;</li>
                                    <li>Se o caminho informado está correto;</li>
                                    <li>Se a pasta possui permissão de escrita;</li>
                                    <li>Se o JSON está com estrutura válida;</li>
                                    <li>Se o PHP está recebendo os dados do formulário;</li>
                                    <li>Se <code>file_put_contents()</code> retornou sucesso.</li>
                                </ul>

                                <p>
                                    Também é importante impedir que duas gravações
                                    ocorram ao mesmo tempo usando bloqueio de arquivo.
                                </p>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="nenhum-resultado" id="nenhum-resultado">
                    <h3>Nenhuma resposta encontrada</h3>

                    <p>
                        Tente pesquisar com outras palavras ou entre em contato
                        com o suporte do FOAG.
                    </p>
                </div>

                <section class="contato-area">
                    <h2>Ainda precisa de ajuda?</h2>

                    <p>
                        Envie sua dúvida com uma descrição clara do problema.
                        Quando possível, informe a página utilizada e o que
                        aconteceu. Não envie sua senha.
                    </p>

                    <div class="contato-opcoes">
                        <div class="contato-card">
                            <strong>Enviar um e-mail</strong>

                            <p>
                                Indicado para dúvidas, problemas na conta e
                                solicitações relacionadas aos dados pessoais.
                            </p>

                            <a
                                class="contato-link"
                                href="mailto:rafaella@gmail.com?subject=Ajuda%20com%20o%20FOAG&body=Nome:%0AEmail%20da%20conta:%0APágina%20com%20problema:%0ADescrição:%0A"
                            >
                                Enviar mensagem
                            </a>
                        </div>

                        <div class="contato-card">
                            <strong>Responsáveis pelo FOAG</strong>

                            <p>
                                Rafaella, Ralley e Kezia são responsáveis pelo
                                atendimento e manutenção da plataforma.
                            </p>

                            <a class="contato-link" href="politica_privacidade.php">
                                Ver dados de contato
                            </a>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <footer class="rodape">
            <div class="rodape-links">
                <a href="termos.php">Termos de Uso</a>
                <a href="politica_privacidade.php">Política de Privacidade</a>
                <a href="configuracoes.php">Configurações</a>
            </div>

            <p>
                FOAG — Ferramenta de Organização Acadêmica Geral
            </p>
        </footer>
    </div>

    <button class="voltar-topo" id="voltar-topo" type="button" aria-label="Voltar ao topo">
        ↑
    </button>

    <script>
        const campoBusca = document.getElementById("campo-busca");
        const itensFaq = document.querySelectorAll(".faq-item");
        const botoesCategoria = document.querySelectorAll(".categoria-btn");
        const resultadoBusca = document.getElementById("resultado-busca");
        const nenhumResultado = document.getElementById("nenhum-resultado");
        const botaoTopo = document.getElementById("voltar-topo");

        let categoriaAtual = "todas";

        function removerAcentos(texto) {
            return texto
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLowerCase()
                .trim();
        }

        function fecharItem(item) {
            const botao = item.querySelector(".faq-pergunta");
            const resposta = item.querySelector(".faq-resposta");

            item.classList.remove("aberto");
            botao.setAttribute("aria-expanded", "false");
            resposta.style.maxHeight = null;
        }

        function abrirItem(item) {
            const botao = item.querySelector(".faq-pergunta");
            const resposta = item.querySelector(".faq-resposta");

            item.classList.add("aberto");
            botao.setAttribute("aria-expanded", "true");
            resposta.style.maxHeight = resposta.scrollHeight + "px";
        }

        document.querySelectorAll(".faq-pergunta").forEach((botao) => {
            botao.addEventListener("click", () => {
                const itemAtual = botao.closest(".faq-item");
                const estaAberto = itemAtual.classList.contains("aberto");

                itensFaq.forEach((item) => {
                    if (item !== itemAtual) {
                        fecharItem(item);
                    }
                });

                if (estaAberto) {
                    fecharItem(itemAtual);
                } else {
                    abrirItem(itemAtual);
                }
            });
        });

        function filtrarPerguntas() {
            const busca = removerAcentos(campoBusca.value);
            let quantidadeVisivel = 0;

            itensFaq.forEach((item) => {
                const categoriaItem = item.dataset.categoria;
                const termosExtras = item.dataset.termos || "";
                const textoItem = removerAcentos(
                    item.textContent + " " + termosExtras
                );

                const pertenceCategoria =
                    categoriaAtual === "todas" ||
                    categoriaItem === categoriaAtual;

                const correspondeBusca =
                    busca === "" ||
                    textoItem.includes(busca);

                const deveMostrar =
                    pertenceCategoria &&
                    correspondeBusca;

                item.style.display = deveMostrar ? "block" : "none";

                if (deveMostrar) {
                    quantidadeVisivel++;
                } else {
                    fecharItem(item);
                }
            });

            if (busca !== "") {
                resultadoBusca.textContent =
                    quantidadeVisivel === 1
                        ? "1 resposta encontrada para \"" + campoBusca.value.trim() + "\"."
                        : quantidadeVisivel + " respostas encontradas para \"" + campoBusca.value.trim() + "\".";

                resultadoBusca.classList.add("visivel");
            } else {
                resultadoBusca.classList.remove("visivel");
            }

            nenhumResultado.classList.toggle(
                "visivel",
                quantidadeVisivel === 0
            );
        }

        botoesCategoria.forEach((botao) => {
            botao.addEventListener("click", () => {
                botoesCategoria.forEach((item) => {
                    item.classList.remove("ativa");
                });

                botao.classList.add("ativa");
                categoriaAtual = botao.dataset.categoria;

                filtrarPerguntas();

                document.querySelector(".conteudo-ajuda").scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            });
        });

        campoBusca.addEventListener("input", filtrarPerguntas);

        campoBusca.addEventListener("keydown", (evento) => {
            if (evento.key === "Escape") {
                campoBusca.value = "";
                filtrarPerguntas();
            }
        });

        window.addEventListener("resize", () => {
            document.querySelectorAll(".faq-item.aberto").forEach((item) => {
                const resposta = item.querySelector(".faq-resposta");
                resposta.style.maxHeight = resposta.scrollHeight + "px";
            });
        });

        window.addEventListener("scroll", () => {
            if (window.scrollY > 500) {
                botaoTopo.classList.add("visivel");
            } else {
                botaoTopo.classList.remove("visivel");
            }
        });

        botaoTopo.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    </script>
    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>