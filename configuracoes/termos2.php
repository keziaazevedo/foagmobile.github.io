<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Termos de Uso da plataforma FOAG - Ferramenta de Organização Acadêmica Geral.">
    <meta name="theme-color" content="#168fe8">
    <title>Termos de Uso - FOAG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --azul-principal: #168fe8;
            --azul-escuro: #0874c4;
            --azul-claro: #eaf6ff;
            --azul-muito-claro: #f4faff;
            --texto-principal: #263238;
            --texto-secundario: #68757e;
            --branco: #ffffff;
            --borda: #dcecf7;
            --sucesso: #198754;
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
            scroll-padding-top: 30px;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #dff2ff 0%, #f8fcff 45%, #ffffff 100%);
            color: var(--texto-principal);
        }

        button,
        a {
            font: inherit;
        }

        a {
            color: var(--azul-principal);
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid rgba(22, 143, 232, 0.3);
            outline-offset: 3px;
        }

        .barra-progresso {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            width: 0;
            height: 4px;
            background: linear-gradient(90deg, #168fe8, #57b8ff);
            transition: width 0.1s linear;
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
            padding: 42px;
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
            top: -80px;
            right: -40px;
            width: 230px;
            height: 230px;
        }

        .cabecalho::after {
            right: 180px;
            bottom: -110px;
            width: 190px;
            height: 190px;
        }

        .cabecalho-conteudo {
            position: relative;
            z-index: 1;
            max-width: 780px;
        }

        .identificacao {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
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
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            line-height: 1.7;
        }

        .data-atualizacao {
            display: inline-block;
            margin-top: 18px;
            font-size: 0.86rem;
            color: rgba(255, 255, 255, 0.82);
        }

        .acoes {
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

        .layout {
            display: grid;
            grid-template-columns: 275px minmax(0, 1fr);
            gap: 25px;
            align-items: start;
        }

        .indice {
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            padding: 24px 18px;
            border: 1px solid var(--borda);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--sombra);
        }

        .indice::-webkit-scrollbar {
            width: 6px;
        }

        .indice::-webkit-scrollbar-thumb {
            border-radius: 10px;
            background: #c4e1f5;
        }

        .indice h2 {
            margin-bottom: 15px;
            padding: 0 8px 13px;
            border-bottom: 1px solid var(--borda);
            color: var(--texto-principal);
            font-size: 1rem;
        }

        .indice ul {
            list-style: none;
        }

        .indice a {
            display: block;
            margin-bottom: 3px;
            padding: 9px 10px;
            border-radius: 8px;
            color: var(--texto-secundario);
            text-decoration: none;
            font-size: 0.78rem;
            line-height: 1.4;
            transition: 0.2s;
        }

        .indice a:hover,
        .indice a.ativo {
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-weight: 600;
        }

        .conteudo {
            min-width: 0;
            padding: 42px;
            border: 1px solid var(--borda);
            border-radius: 20px;
            background: var(--branco);
            box-shadow: var(--sombra);
        }

        .aviso {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #bce1fa;
            border-left: 5px solid var(--azul-principal);
            border-radius: 12px;
            background: var(--azul-muito-claro);
        }

        .aviso strong {
            display: block;
            margin-bottom: 6px;
            color: var(--azul-escuro);
        }

        .aviso p {
            margin: 0;
        }

        .resumo {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 35px;
        }

        .resumo-item {
            padding: 18px;
            border: 1px solid var(--borda);
            border-radius: 13px;
            background: #fbfdff;
        }

        .resumo-item strong {
            display: block;
            margin-bottom: 5px;
            color: var(--azul-escuro);
            font-size: 0.9rem;
        }

        .resumo-item span {
            color: var(--texto-secundario);
            font-size: 0.78rem;
            line-height: 1.5;
        }

        .secao {
            padding: 30px 0;
            border-bottom: 1px solid #e8f0f5;
        }

        .secao:first-of-type {
            padding-top: 5px;
        }

        .secao:last-of-type {
            border-bottom: none;
        }

        .secao h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: var(--azul-escuro);
            font-size: 1.25rem;
            line-height: 1.4;
        }

        .numero {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .secao h3 {
            margin: 20px 0 8px;
            color: var(--texto-principal);
            font-size: 1rem;
        }

        .secao p,
        .secao li {
            color: #46545d;
            font-size: 0.93rem;
            line-height: 1.8;
        }

        .secao p {
            margin-bottom: 12px;
        }

        .secao ul {
            margin: 10px 0 15px 22px;
        }

        .secao li {
            margin-bottom: 7px;
            padding-left: 4px;
        }

        .secao li::marker {
            color: var(--azul-principal);
        }

        .destaque {
            margin: 18px 0;
            padding: 16px 18px;
            border-radius: 10px;
            background: #f5faff;
            color: #3e535f;
            font-size: 0.88rem;
            line-height: 1.7;
        }

        .contato {
            margin-top: 20px;
            padding: 22px;
            border: 1px solid var(--borda);
            border-radius: 13px;
            background: var(--azul-muito-claro);
        }

        .contato p:last-child {
            margin-bottom: 0;
        }

        .contato a {
            font-weight: 600;
            text-decoration: none;
        }

        .links-legais {
            display: grid;
            gap: 10px;
            margin-top: 15px;
        }

        .link-legal {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 14px 16px;
            border: 1px solid var(--borda);
            border-radius: 10px;
            background: #fbfdff;
            color: var(--texto-principal);
            text-decoration: none;
            font-size: 0.86rem;
            font-weight: 500;
            transition: 0.2s;
        }

        .link-legal:hover {
            border-color: #9ed3f6;
            background: var(--azul-muito-claro);
            color: var(--azul-escuro);
        }

        .rodape-termos {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid var(--borda);
            text-align: center;
        }

        .rodape-termos p {
            color: var(--texto-secundario);
            font-size: 0.78rem;
            line-height: 1.7;
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
            border: none;
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

        @media (max-width: 950px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .indice {
                position: static;
                max-height: none;
            }

            .indice ul {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 4px;
            }

            .resumo {
                grid-template-columns: 1fr;
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
                font-size: 0.9rem;
            }

            .acoes {
                display: grid;
                grid-template-columns: 1fr;
            }

            .botao {
                width: 100%;
            }

            .indice {
                padding: 20px 15px;
            }

            .indice ul {
                grid-template-columns: 1fr;
            }

            .conteudo {
                padding: 27px 20px;
                border-radius: 16px;
            }

            .secao {
                padding: 25px 0;
            }

            .secao h2 {
                align-items: flex-start;
                font-size: 1.08rem;
            }

            .secao p,
            .secao li {
                font-size: 0.88rem;
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

            .barra-progresso,
            .acoes,
            .indice,
            .voltar-topo {
                display: none !important;
            }

            .pagina {
                max-width: none;
                padding: 0;
            }

            .cabecalho {
                padding: 25px;
                border-radius: 0;
                background: #ffffff;
                color: #000000;
                box-shadow: none;
            }

            .cabecalho p,
            .data-atualizacao {
                color: #444444;
            }

            .identificacao {
                border-color: #cccccc;
                background: #f5f5f5;
                color: #000000;
            }

            .layout {
                display: block;
            }

            .conteudo {
                padding: 0 25px;
                border: none;
                box-shadow: none;
            }

            .secao {
                break-inside: avoid;
            }
        }
    </style>
    <link rel="stylesheet" href="../global/css/cursor.css">
</head>

<body>
    <div class="barra-progresso" id="barra-progresso"></div>

    <div class="pagina">
        <header class="cabecalho">
            <div class="cabecalho-conteudo">
                <span class="identificacao">
                    <span class="identificacao-ponto"></span>
                    Documento oficial do FOAG
                </span>

                <h1>Termos de Uso</h1>

                <p>
                    Regras, direitos e responsabilidades relacionados ao uso
                    da plataforma FOAG — Ferramenta de Organização Acadêmica Geral.
                </p>

                <span class="data-atualizacao">
                    Última atualização: 06 de agosto de 2026
                </span>
            </div>
        </header>

        <div class="acoes">
            <a class="botao botao-principal" href="configuracoes.php">
                ← Voltar às configurações
            </a>

            <button class="botao botao-secundario" type="button" onclick="window.print()">
                Imprimir ou salvar em PDF
            </button>
        </div>

        <div class="layout">
            <aside class="indice" aria-label="Índice dos Termos de Uso">
                <h2>Conteúdo dos termos</h2>

                <ul>
                    <li><a href="#aceitacao">1. Aceitação dos termos</a></li>
                    <li><a href="#sobre">2. Sobre o FOAG</a></li>
                    <li><a href="#cadastro">3. Cadastro e conta</a></li>
                    <li><a href="#seguranca">4. Segurança da conta</a></li>
                    <li><a href="#uso-permitido">5. Uso permitido</a></li>
                    <li><a href="#condutas-proibidas">6. Condutas proibidas</a></li>
                    <li><a href="#conteudo-usuario">7. Conteúdo do usuário</a></li>
                    <li><a href="#dados-pessoais">8. Dados pessoais</a></li>
                    <li><a href="#armazenamento">9. Armazenamento e registros</a></li>
                    <li><a href="#menores">10. Menores de idade</a></li>
                    <li><a href="#notificacoes">11. Notificações</a></li>
                    <li><a href="#disponibilidade">12. Disponibilidade</a></li>
                    <li><a href="#backup">13. Cópias de segurança</a></li>
                    <li><a href="#propriedade">14. Propriedade intelectual</a></li>
                    <li><a href="#terceiros">15. Serviços de terceiros</a></li>
                    <li><a href="#suspensao">16. Suspensão da conta</a></li>
                    <li><a href="#responsabilidade">17. Responsabilidades</a></li>
                    <li><a href="#alteracoes">18. Alterações dos termos</a></li>
                    <li><a href="#encerramento">19. Encerramento da conta</a></li>
                    <li><a href="#legislacao">20. Legislação aplicável</a></li>
                    <li><a href="#contato">21. Contato</a></li>
                </ul>
            </aside>

            <main class="conteudo">
                <div class="aviso">
                    <strong>Leia este documento com atenção</strong>

                    <p>
                        Ao criar uma conta, acessar ou utilizar o FOAG, o usuário
                        declara que leu, compreendeu e concorda com estes Termos
                        de Uso. Caso não concorde, deverá interromper o uso da
                        plataforma.
                    </p>
                </div>

                <div class="resumo">
                    <div class="resumo-item">
                        <strong>Uso responsável</strong>
                        <span>
                            Utilize o FOAG apenas para finalidades pessoais,
                            acadêmicas e permitidas por lei.
                        </span>
                    </div>

                    <div class="resumo-item">
                        <strong>Proteção da conta</strong>
                        <span>
                            Mantenha sua senha protegida e não compartilhe
                            seu acesso com outras pessoas.
                        </span>
                    </div>

                    <div class="resumo-item">
                        <strong>Seus dados</strong>
                        <span>
                            Você poderá solicitar correção ou exclusão dos
                            dados vinculados à sua conta.
                        </span>
                    </div>
                </div>

                <section class="secao" id="aceitacao">
                    <h2>
                        <span class="numero">01</span>
                        Aceitação dos Termos de Uso
                    </h2>

                    <p>
                        Estes Termos de Uso regulam o acesso e a utilização do
                        FOAG por todos os usuários da plataforma.
                    </p>

                    <p>
                        A utilização do FOAG representa a concordância livre e
                        informada com as condições apresentadas neste documento.
                    </p>

                    <p>
                        Caso o usuário esteja utilizando a plataforma em nome de
                        uma instituição, turma ou organização, declara possuir
                        autorização para aceitar estes termos em nome dela.
                    </p>
                </section>

                <section class="secao" id="sobre">
                    <h2>
                        <span class="numero">02</span>
                        Sobre o FOAG
                    </h2>

                    <p>
                        O FOAG — Ferramenta de Organização Acadêmica Geral — é
                        uma plataforma de apoio à organização pessoal e acadêmica.
                    </p>

                    <p>Entre seus recursos, o FOAG poderá oferecer:</p>

                    <ul>
                        <li>Agenda pessoal e acadêmica;</li>
                        <li>Calendário de atividades e compromissos;</li>
                        <li>Cadastro de matérias e horários;</li>
                        <li>Registro de tarefas, trabalhos e avaliações;</li>
                        <li>Controle de notas e frequência;</li>
                        <li>Temporizador Pomodoro;</li>
                        <li>Metas e acompanhamento de produtividade;</li>
                        <li>Lembretes, histórico e estatísticas;</li>
                        <li>Configurações e personalização do perfil.</li>
                    </ul>

                    <p>
                        As funcionalidades poderão ser alteradas, removidas ou
                        ampliadas conforme o desenvolvimento da plataforma.
                    </p>

                    <div class="destaque">
                        O FOAG é uma ferramenta auxiliar. Ele não substitui
                        sistemas oficiais de instituições de ensino, documentos
                        acadêmicos, calendários oficiais ou orientações de
                        professores e responsáveis.
                    </div>
                </section>

                <section class="secao" id="cadastro">
                    <h2>
                        <span class="numero">03</span>
                        Cadastro e conta do usuário
                    </h2>

                    <p>
                        Algumas funcionalidades poderão exigir a criação de uma
                        conta individual.
                    </p>

                    <p>No cadastro, poderão ser solicitadas informações como:</p>

                    <ul>
                        <li>Nome completo ou nome de identificação;</li>
                        <li>Endereço de e-mail;</li>
                        <li>Data de nascimento;</li>
                        <li>Escola, instituição, série ou turma;</li>
                        <li>Foto de perfil;</li>
                        <li>Senha de acesso;</li>
                        <li>Outros dados necessários ao funcionamento do sistema.</li>
                    </ul>

                    <p>
                        O usuário deverá fornecer informações verdadeiras,
                        atualizadas e completas. Não é permitido criar conta
                        utilizando dados de outra pessoa sem autorização.
                    </p>

                    <p>
                        Cada usuário deverá utilizar sua própria conta, salvo nos
                        casos em que o acesso compartilhado seja expressamente
                        autorizado pelos responsáveis pelo FOAG.
                    </p>
                </section>

                <section class="secao" id="seguranca">
                    <h2>
                        <span class="numero">04</span>
                        Senha e segurança da conta
                    </h2>

                    <p>
                        A senha é pessoal, confidencial e não deverá ser
                        compartilhada com terceiros.
                    </p>

                    <p>O usuário é responsável por:</p>

                    <ul>
                        <li>Criar uma senha segura;</li>
                        <li>Não divulgar sua senha;</li>
                        <li>Encerrar a sessão em computadores compartilhados;</li>
                        <li>Impedir o acesso não autorizado à sua conta;</li>
                        <li>Comunicar qualquer atividade suspeita;</li>
                        <li>Manter seus dados de recuperação atualizados.</li>
                    </ul>

                    <p>
                        Caso identifique acesso não autorizado, o usuário deverá
                        alterar sua senha imediatamente e comunicar os
                        responsáveis pelo FOAG.
                    </p>

                    <p>
                        O FOAG nunca solicitará a senha completa do usuário por
                        e-mail, mensagens ou outros canais de atendimento.
                    </p>
                </section>

                <section class="secao" id="uso-permitido">
                    <h2>
                        <span class="numero">05</span>
                        Uso permitido da plataforma
                    </h2>

                    <p>O FOAG poderá ser utilizado para:</p>

                    <ul>
                        <li>Organizar atividades pessoais e acadêmicas;</li>
                        <li>Cadastrar tarefas, provas, trabalhos e compromissos;</li>
                        <li>Registrar anotações e informações próprias;</li>
                        <li>Acompanhar notas, frequência e produtividade;</li>
                        <li>Planejar sessões de estudo;</li>
                        <li>Personalizar a experiência de organização;</li>
                        <li>Utilizar os recursos disponibilizados de forma legítima.</li>
                    </ul>

                    <p>
                        O usuário deverá utilizar a plataforma com respeito,
                        responsabilidade e observância da legislação brasileira.
                    </p>
                </section>

                <section class="secao" id="condutas-proibidas">
                    <h2>
                        <span class="numero">06</span>
                        Condutas proibidas
                    </h2>

                    <p>Não é permitido:</p>

                    <ul>
                        <li>Fornecer dados falsos ou enganosos;</li>
                        <li>Acessar a conta de outra pessoa sem autorização;</li>
                        <li>Compartilhar, vender ou ceder uma conta;</li>
                        <li>Tentar invadir, alterar ou prejudicar o sistema;</li>
                        <li>Explorar falhas de segurança ou vulnerabilidades;</li>
                        <li>Inserir vírus, códigos maliciosos ou arquivos prejudiciais;</li>
                        <li>Realizar ataques, sobrecargas ou acessos automatizados abusivos;</li>
                        <li>Tentar obter códigos, dados ou informações protegidas;</li>
                        <li>Utilizar a plataforma para atividades ilegais ou fraudulentas;</li>
                        <li>Armazenar conteúdo ofensivo, discriminatório ou criminoso;</li>
                        <li>Violar direitos autorais, privacidade ou direitos de terceiros;</li>
                        <li>Copiar ou comercializar o sistema sem autorização;</li>
                        <li>Fingir representar o FOAG ou seus responsáveis;</li>
                        <li>Prejudicar o funcionamento ou a segurança da plataforma.</li>
                    </ul>

                    <p>
                        A identificação de uma dessas condutas poderá resultar
                        em advertência, restrição, suspensão ou encerramento da
                        conta.
                    </p>
                </section>

                <section class="secao" id="conteudo-usuario">
                    <h2>
                        <span class="numero">07</span>
                        Conteúdo cadastrado pelo usuário
                    </h2>

                    <p>
                        O usuário permanece responsável pelos textos, tarefas,
                        anotações, imagens, horários, notas, documentos e demais
                        conteúdos inseridos na plataforma.
                    </p>

                    <p>Ao cadastrar um conteúdo, o usuário declara que:</p>

                    <ul>
                        <li>Possui autorização para utilizá-lo;</li>
                        <li>O conteúdo não viola direitos de terceiros;</li>
                        <li>O conteúdo não possui finalidade ilegal;</li>
                        <li>As informações não prejudicam outros usuários;</li>
                        <li>Não está inserindo dados pessoais de terceiros sem autorização.</li>
                    </ul>

                    <p>
                        O FOAG não se torna proprietário do conteúdo pessoal
                        cadastrado pelo usuário.
                    </p>

                    <p>
                        O conteúdo poderá ser processado apenas na medida
                        necessária para salvar, organizar, exibir e disponibilizar
                        as funcionalidades solicitadas pelo próprio usuário.
                    </p>
                </section>

                <section class="secao" id="dados-pessoais">
                    <h2>
                        <span class="numero">08</span>
                        Proteção e tratamento de dados pessoais
                    </h2>

                    <p>
                        Os dados pessoais poderão ser tratados para permitir o
                        cadastro, identificar o usuário, autenticar o acesso,
                        manter a conta e disponibilizar as funcionalidades do
                        FOAG.
                    </p>

                    <p>Os dados poderão ser utilizados para:</p>

                    <ul>
                        <li>Criar e administrar a conta;</li>
                        <li>Salvar preferências e configurações;</li>
                        <li>Exibir informações acadêmicas cadastradas;</li>
                        <li>Prevenir acessos indevidos e fraudes;</li>
                        <li>Corrigir erros e melhorar a plataforma;</li>
                        <li>Atender solicitações do usuário;</li>
                        <li>Cumprir obrigações legais aplicáveis.</li>
                    </ul>

                    <p>
                        O FOAG deverá adotar medidas técnicas e administrativas
                        razoáveis para proteger os dados contra perda, acesso
                        indevido, alteração, vazamento ou destruição.
                    </p>

                    <h3>Direitos do usuário</h3>

                    <p>
                        O usuário poderá solicitar, conforme aplicável:
                    </p>

                    <ul>
                        <li>Confirmação sobre a existência do tratamento;</li>
                        <li>Acesso aos seus dados pessoais;</li>
                        <li>Correção de informações incompletas ou incorretas;</li>
                        <li>Informações sobre a utilização dos dados;</li>
                        <li>Exclusão dos dados, quando legalmente possível;</li>
                        <li>Revogação de consentimento, quando essa for a base utilizada;</li>
                        <li>Encerramento da conta.</li>
                    </ul>

                    <p>
                        Algumas informações poderão ser mantidas pelo período
                        necessário ao cumprimento de obrigações legais,
                        prevenção de fraudes, segurança ou exercício regular
                        de direitos.
                    </p>
                </section>

                <section class="secao" id="armazenamento">
                    <h2>
                        <span class="numero">09</span>
                        Armazenamento local e registros
                    </h2>

                    <p>
                        Dependendo da versão e da configuração técnica utilizada,
                        determinadas informações poderão ser armazenadas
                        localmente no dispositivo, no navegador, em arquivos
                        internos ou na estrutura de armazenamento da plataforma.
                    </p>

                    <p>
                        O armazenamento local poderá ser utilizado para manter
                        sessões, preferências, tema visual, configurações e
                        informações necessárias ao funcionamento dos recursos.
                    </p>

                    <p>
                        A limpeza dos dados do navegador, a troca de dispositivo
                        ou a exclusão de arquivos locais poderá causar a perda
                        de informações que não tenham sido sincronizadas ou
                        copiadas.
                    </p>

                    <p>
                        Registros técnicos mínimos poderão ser utilizados para
                        segurança, diagnóstico de erros e prevenção de acessos
                        indevidos, observados os limites legais aplicáveis.
                    </p>
                </section>

                <section class="secao" id="menores">
                    <h2>
                        <span class="numero">10</span>
                        Uso por crianças e adolescentes
                    </h2>

                    <p>
                        Considerando a finalidade acadêmica da plataforma, o
                        FOAG poderá ser utilizado por usuários menores de idade.
                    </p>

                    <p>
                        Crianças e adolescentes deverão utilizar a plataforma
                        com o conhecimento e acompanhamento dos pais ou
                        responsáveis legais.
                    </p>

                    <p>Os responsáveis deverão:</p>

                    <ul>
                        <li>Orientar sobre o uso seguro da plataforma;</li>
                        <li>Acompanhar o cadastro e as informações inseridas;</li>
                        <li>Ensinar o menor a proteger sua senha;</li>
                        <li>Evitar o compartilhamento de informações sensíveis;</li>
                        <li>Comunicar qualquer uso indevido ou situação de risco.</li>
                    </ul>

                    <p>
                        Quando exigido pela legislação, o tratamento de dados
                        de menores dependerá da autorização do responsável
                        legal e deverá observar o melhor interesse da criança
                        ou do adolescente.
                    </p>
                </section>

                <section class="secao" id="notificacoes">
                    <h2>
                        <span class="numero">11</span>
                        Lembretes e notificações
                    </h2>

                    <p>
                        O FOAG poderá disponibilizar lembretes, alertas e
                        notificações sobre tarefas, provas, trabalhos,
                        compromissos e sessões de estudo.
                    </p>

                    <p>
                        O funcionamento das notificações poderá depender das
                        permissões do navegador, das configurações do dispositivo,
                        da conexão com a internet e das informações cadastradas
                        pelo usuário.
                    </p>

                    <p>
                        O usuário não deverá depender exclusivamente das
                        notificações do FOAG para cumprir compromissos importantes.
                    </p>
                </section>

                <section class="secao" id="disponibilidade">
                    <h2>
                        <span class="numero">12</span>
                        Disponibilidade e atualizações
                    </h2>

                    <p>
                        O FOAG poderá passar por manutenção, atualização,
                        correção de erros ou indisponibilidade temporária.
                    </p>

                    <p>
                        Embora sejam adotadas medidas para manter a plataforma
                        funcionando, não é possível garantir que o serviço
                        estará disponível continuamente, sem interrupções ou
                        totalmente livre de falhas.
                    </p>

                    <p>
                        Recursos poderão ser modificados, substituídos,
                        adicionados ou removidos para melhorar o funcionamento,
                        a segurança ou a experiência do usuário.
                    </p>
                </section>

                <section class="secao" id="backup">
                    <h2>
                        <span class="numero">13</span>
                        Cópias de segurança
                    </h2>

                    <p>
                        O usuário deverá manter cópias próprias de informações
                        acadêmicas, documentos e registros considerados
                        importantes.
                    </p>

                    <p>
                        Não é recomendado utilizar o FOAG como único meio de
                        armazenamento de documentos, notas, datas de provas,
                        trabalhos ou informações essenciais.
                    </p>

                    <p>
                        Sempre que a plataforma disponibilizar ferramentas de
                        exportação, o usuário deverá utilizá-las periodicamente
                        para manter uma cópia de segurança.
                    </p>
                </section>

                <section class="secao" id="propriedade">
                    <h2>
                        <span class="numero">14</span>
                        Propriedade intelectual
                    </h2>

                    <p>
                        O nome FOAG, sua identidade visual, código-fonte,
                        estrutura, layout, elementos gráficos, textos, recursos
                        e demais componentes da plataforma são protegidos pela
                        legislação aplicável.
                    </p>

                    <p>Sem autorização prévia, não é permitido:</p>

                    <ul>
                        <li>Copiar ou reproduzir o sistema;</li>
                        <li>Modificar ou distribuir o código;</li>
                        <li>Utilizar a identidade visual comercialmente;</li>
                        <li>Criar versões derivadas da plataforma;</li>
                        <li>Remover marcas, créditos ou avisos de autoria;</li>
                        <li>Vender, licenciar ou explorar comercialmente o FOAG.</li>
                    </ul>

                    <p>
                        O uso da plataforma não transfere ao usuário qualquer
                        direito de propriedade sobre o sistema.
                    </p>
                </section>

                <section class="secao" id="terceiros">
                    <h2>
                        <span class="numero">15</span>
                        Serviços e conteúdos de terceiros
                    </h2>

                    <p>
                        O FOAG poderá utilizar ou apresentar links, fontes,
                        bibliotecas, serviços ou conteúdos fornecidos por terceiros.
                    </p>

                    <p>
                        Cada serviço externo poderá possuir seus próprios termos,
                        políticas de privacidade e condições de funcionamento.
                    </p>

                    <p>
                        O FOAG não controla a disponibilidade, segurança ou
                        conteúdo de páginas externas acessadas voluntariamente
                        pelo usuário.
                    </p>
                </section>

                <section class="secao" id="suspensao">
                    <h2>
                        <span class="numero">16</span>
                        Suspensão ou restrição de acesso
                    </h2>

                    <p>
                        O acesso do usuário poderá ser temporariamente restringido
                        ou suspenso quando houver:
                    </p>

                    <ul>
                        <li>Violação destes Termos de Uso;</li>
                        <li>Tentativa de fraude ou invasão;</li>
                        <li>Risco à segurança da plataforma;</li>
                        <li>Uso ilegal ou abusivo;</li>
                        <li>Prejuízo a outros usuários;</li>
                        <li>Fornecimento de informações falsas;</li>
                        <li>Determinação legal ou administrativa válida.</li>
                    </ul>

                    <p>
                        Sempre que possível e adequado, o usuário poderá ser
                        informado sobre o motivo da restrição.
                    </p>
                </section>

                <section class="secao" id="responsabilidade">
                    <h2>
                        <span class="numero">17</span>
                        Limitação de responsabilidade
                    </h2>

                    <p>
                        O FOAG funciona como ferramenta auxiliar de organização.
                        O usuário continua responsável pelo cumprimento de suas
                        tarefas, provas, horários, compromissos e demais obrigações.
                    </p>

                    <p>O FOAG não garante:</p>

                    <ul>
                        <li>Aprovação ou melhoria do desempenho acadêmico;</li>
                        <li>Exatidão de informações cadastradas pelo usuário;</li>
                        <li>Funcionamento ininterrupto em todos os dispositivos;</li>
                        <li>Entrega de todas as notificações e lembretes;</li>
                        <li>Recuperação de dados apagados pelo próprio usuário;</li>
                        <li>Compatibilidade com todos os navegadores e sistemas.</li>
                    </ul>

                    <p>
                        O FOAG não será responsável por prejuízos causados por
                        informações cadastradas incorretamente, perda de senha,
                        compartilhamento de conta, falhas do dispositivo,
                        exclusão de dados locais ou uso contrário a estes termos.
                    </p>

                    <p>
                        Esta limitação será aplicada somente dentro dos limites
                        permitidos pela legislação brasileira.
                    </p>
                </section>

                <section class="secao" id="alteracoes">
                    <h2>
                        <span class="numero">18</span>
                        Alterações destes Termos de Uso
                    </h2>

                    <p>
                        Estes Termos de Uso poderão ser atualizados para
                        acompanhar mudanças na plataforma, correções, melhorias
                        de segurança ou alterações legais.
                    </p>

                    <p>
                        A data da versão mais recente ficará disponível no
                        início deste documento.
                    </p>

                    <p>
                        Quando houver mudanças relevantes, o usuário poderá ser
                        informado por aviso dentro da plataforma ou por outro
                        canal de contato disponível.
                    </p>

                    <p>
                        A continuidade do uso após a atualização representará
                        a concordância com a nova versão, respeitados os direitos
                        assegurados pela legislação.
                    </p>
                </section>

                <section class="secao" id="encerramento">
                    <h2>
                        <span class="numero">19</span>
                        Encerramento e exclusão da conta
                    </h2>

                    <p>
                        O usuário poderá solicitar o encerramento de sua conta
                        e a exclusão dos dados vinculados a ela.
                    </p>

                    <p>
                        Antes da exclusão, recomenda-se salvar uma cópia das
                        informações importantes, pois a remoção poderá ser
                        definitiva e irreversível.
                    </p>

                    <p>
                        Alguns registros poderão ser mantidos pelo período
                        necessário para cumprimento de obrigação legal,
                        segurança, prevenção de fraudes ou exercício regular
                        de direitos.
                    </p>
                </section>

                <section class="secao" id="legislacao">
                    <h2>
                        <span class="numero">20</span>
                        Legislação aplicável
                    </h2>

                    <p>
                        Estes Termos de Uso deverão ser interpretados de acordo
                        com a legislação brasileira aplicável.
                    </p>

                    <p>Entre as normas relacionadas ao uso da plataforma estão:</p>

                    <div class="links-legais">
                        <a class="link-legal" href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm" target="_blank" rel="noopener noreferrer">
                            Lei Geral de Proteção de Dados — Lei nº 13.709/2018
                            <span>↗</span>
                        </a>

                        <a class="link-legal" href="https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2014/lei/l12965.htm" target="_blank" rel="noopener noreferrer">
                            Marco Civil da Internet — Lei nº 12.965/2014
                            <span>↗</span>
                        </a>

                        <a class="link-legal" href="https://www.planalto.gov.br/ccivil_03/leis/l8069.htm" target="_blank" rel="noopener noreferrer">
                            Estatuto da Criança e do Adolescente — Lei nº 8.069/1990
                            <span>↗</span>
                        </a>
                    </div>

                    <p style="margin-top: 18px;">
                        Eventuais conflitos deverão ser solucionados pelos
                        meios previstos na legislação, respeitados os direitos
                        do usuário.
                    </p>
                </section>

                <section class="secao" id="contato">
                    <h2>
                        <span class="numero">21</span>
                        Contato e solicitações
                    </h2>

                    <p>
                        Dúvidas, solicitações relacionadas à conta, comunicação
                        de problemas de segurança ou pedidos referentes a dados
                        pessoais poderão ser encaminhados aos responsáveis pelo
                        FOAG.
                    </p>

                    <div class="contato">
                        <p>
                            <strong>E-mail:</strong>
                            <a href="mailto:rafaella@gmail.com">
                                rafaella@gmail.com
                            </a>
                        </p>

                        <p>
                            <strong>Responsáveis pelo FOAG:</strong>
                            Rafaella, Ralley e Kezia
                        </p>

                        <p>
                            <strong>Assunto recomendado:</strong>
                            Solicitação relacionada ao FOAG
                        </p>
                    </div>
                </section>

                <footer class="rodape-termos">
                    <p>
                        FOAG — Ferramenta de Organização Acadêmica Geral
                    </p>

                    <p>
                        Ao utilizar a plataforma, o usuário reconhece que leu
                        e concorda com estes Termos de Uso.
                    </p>
                </footer>
            </main>
        </div>
    </div>

    <button class="voltar-topo" id="voltar-topo" type="button" aria-label="Voltar ao topo">
        ↑
    </button>

    <script>
        const barraProgresso = document.getElementById("barra-progresso");
        const botaoTopo = document.getElementById("voltar-topo");
        const secoes = document.querySelectorAll(".secao");
        const linksIndice = document.querySelectorAll(".indice a");

        function atualizarPagina() {
            const alturaRolada = document.documentElement.scrollTop;
            const alturaTotal =
                document.documentElement.scrollHeight -
                document.documentElement.clientHeight;

            const progresso = alturaTotal > 0
                ? (alturaRolada / alturaTotal) * 100
                : 0;

            barraProgresso.style.width = progresso + "%";

            if (alturaRolada > 500) {
                botaoTopo.classList.add("visivel");
            } else {
                botaoTopo.classList.remove("visivel");
            }

            let secaoAtual = "";

            secoes.forEach((secao) => {
                const topoSecao = secao.offsetTop - 150;

                if (alturaRolada >= topoSecao) {
                    secaoAtual = secao.getAttribute("id");
                }
            });

            linksIndice.forEach((link) => {
                link.classList.remove("ativo");

                if (link.getAttribute("href") === "#" + secaoAtual) {
                    link.classList.add("ativo");
                }
            });
        }

        botaoTopo.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });

        window.addEventListener("scroll", atualizarPagina);
        window.addEventListener("load", atualizarPagina);
    </script>
    <script src="../global/js/cursor.js?v=<?= time() ?>"></script>

</body>

</html>