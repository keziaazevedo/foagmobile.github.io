<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Política de Privacidade da plataforma FOAG - Ferramenta de Organização Acadêmica Geral.">
    <meta name="theme-color" content="#168fe8">
    <title>Política de Privacidade - FOAG</title>
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
            --verde: #198754;
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
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.86rem;
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
            background: rgba(255, 255, 255, 0.96);
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

        .destaque-verde {
            border-left: 4px solid var(--verde);
            background: #f2fbf6;
        }

        .tabela-wrapper {
            width: 100%;
            margin: 18px 0;
            overflow-x: auto;
            border: 1px solid var(--borda);
            border-radius: 12px;
        }

        table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
            background: var(--branco);
        }

        th,
        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--borda);
            text-align: left;
            vertical-align: top;
            font-size: 0.82rem;
            line-height: 1.6;
        }

        th {
            background: var(--azul-muito-claro);
            color: var(--azul-escuro);
            font-weight: 600;
        }

        td {
            color: #52616a;
        }

        tr:last-child td {
            border-bottom: none;
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

        .rodape-politica {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid var(--borda);
            text-align: center;
        }

        .rodape-politica p {
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
                    Privacidade e proteção de dados
                </span>

                <h1>Política de Privacidade</h1>

                <p>
                    Saiba quais dados o FOAG poderá coletar, como essas
                    informações são utilizadas e quais são os seus direitos.
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

            <a class="botao botao-secundario" href="termos2.php">
                Ver Termos de Uso
            </a>

            <button class="botao botao-secundario" type="button" onclick="window.print()">
                Imprimir ou salvar em PDF
            </button>
        </div>

        <div class="layout">
            <aside class="indice" aria-label="Índice da Política de Privacidade">
                <h2>Conteúdo da política</h2>

                <ul>
                    <li><a href="#introducao">1. Introdução</a></li>
                    <li><a href="#responsavel">2. Responsáveis pelo FOAG</a></li>
                    <li><a href="#dados-coletados">3. Dados coletados</a></li>
                    <li><a href="#dados-academicos">4. Dados acadêmicos</a></li>
                    <li><a href="#dados-tecnicos">5. Dados técnicos</a></li>
                    <li><a href="#finalidades">6. Como usamos os dados</a></li>
                    <li><a href="#bases-legais">7. Bases legais</a></li>
                    <li><a href="#armazenamento">8. Armazenamento</a></li>
                    <li><a href="#cookies">9. Cookies e armazenamento local</a></li>
                    <li><a href="#compartilhamento">10. Compartilhamento</a></li>
                    <li><a href="#menores">11. Menores de idade</a></li>
                    <li><a href="#seguranca">12. Segurança dos dados</a></li>
                    <li><a href="#incidentes">13. Incidentes de segurança</a></li>
                    <li><a href="#conservacao">14. Tempo de conservação</a></li>
                    <li><a href="#direitos">15. Direitos do usuário</a></li>
                    <li><a href="#exclusao">16. Exclusão da conta</a></li>
                    <li><a href="#terceiros">17. Serviços externos</a></li>
                    <li><a href="#alteracoes">18. Alterações da política</a></li>
                    <li><a href="#legislacao">19. Legislação aplicável</a></li>
                    <li><a href="#contato">20. Contato</a></li>
                </ul>
            </aside>

            <main class="conteudo">
                <div class="aviso">
                    <strong>Seu direito à privacidade</strong>

                    <p>
                        Esta política explica de forma clara como o FOAG trata
                        as informações fornecidas pelos usuários durante o
                        cadastro e a utilização da plataforma.
                    </p>
                </div>

                <div class="resumo">
                    <div class="resumo-item">
                        <strong>Coleta limitada</strong>

                        <span>
                            Coletamos apenas informações necessárias ao
                            funcionamento e à segurança da plataforma.
                        </span>
                    </div>

                    <div class="resumo-item">
                        <strong>Sem venda de dados</strong>

                        <span>
                            O FOAG não vende dados pessoais ou acadêmicos
                            dos usuários.
                        </span>
                    </div>

                    <div class="resumo-item">
                        <strong>Controle do usuário</strong>

                        <span>
                            O usuário poderá solicitar acesso, correção
                            ou exclusão de suas informações.
                        </span>
                    </div>
                </div>

                <section class="secao" id="introducao">
                    <h2>
                        <span class="numero">01</span>
                        Introdução
                    </h2>

                    <p>
                        A presente Política de Privacidade descreve como o
                        FOAG — Ferramenta de Organização Acadêmica Geral —
                        coleta, utiliza, armazena, protege e exclui dados
                        pessoais dos usuários.
                    </p>

                    <p>
                        Esta política aplica-se às páginas, ferramentas,
                        recursos e funcionalidades disponibilizados pelo FOAG.
                    </p>

                    <p>
                        Ao criar uma conta ou utilizar a plataforma, o usuário
                        declara que leu e compreendeu as práticas descritas
                        neste documento.
                    </p>

                    <div class="destaque">
                        Caso o usuário não concorde com esta política, deverá
                        interromper a utilização da plataforma e solicitar a
                        exclusão de sua conta, quando aplicável.
                    </div>
                </section>

                <section class="secao" id="responsavel">
                    <h2>
                        <span class="numero">02</span>
                        Responsáveis pelo FOAG
                    </h2>

                    <p>
                        As decisões relacionadas ao funcionamento da plataforma
                        e ao tratamento das informações dos usuários são de
                        responsabilidade da equipe do FOAG.
                    </p>

                    <div class="contato">
                        <p>
                            <strong>Plataforma:</strong>
                            FOAG — Ferramenta de Organização Acadêmica Geral
                        </p>

                        <p>
                            <strong>Responsáveis:</strong>
                            Rafaella, Ralley e Kezia
                        </p>

                        <p>
                            <strong>Canal de contato:</strong>
                            <a href="mailto:rafaella@gmail.com">
                                rafaella@gmail.com
                            </a>
                        </p>
                    </div>
                </section>

                <section class="secao" id="dados-coletados">
                    <h2>
                        <span class="numero">03</span>
                        Dados pessoais coletados
                    </h2>

                    <p>
                        Para criar e manter uma conta, o FOAG poderá solicitar
                        algumas informações pessoais.
                    </p>

                    <p>Os dados de cadastro poderão incluir:</p>

                    <ul>
                        <li>Nome completo ou nome de identificação;</li>
                        <li>Endereço de e-mail;</li>
                        <li>Data de nascimento;</li>
                        <li>Escola ou instituição de ensino;</li>
                        <li>Série, turma ou nível acadêmico;</li>
                        <li>Foto de perfil;</li>
                        <li>Senha de acesso;</li>
                        <li>Preferências de configuração da conta.</li>
                    </ul>

                    <p>
                        O usuário deverá fornecer dados verdadeiros e manter
                        suas informações atualizadas.
                    </p>

                    <p>
                        O FOAG não solicitará dados excessivos ou sem relação
                        com as funcionalidades oferecidas.
                    </p>
                </section>

                <section class="secao" id="dados-academicos">
                    <h2>
                        <span class="numero">04</span>
                        Dados pessoais e acadêmicos cadastrados
                    </h2>

                    <p>
                        Durante o uso da plataforma, o usuário poderá inserir
                        informações pessoais e acadêmicas para organizar sua
                        rotina.
                    </p>

                    <p>Essas informações poderão incluir:</p>

                    <ul>
                        <li>Disciplinas e matérias;</li>
                        <li>Horários de aula;</li>
                        <li>Tarefas e atividades;</li>
                        <li>Datas de provas e trabalhos;</li>
                        <li>Notas e médias acadêmicas;</li>
                        <li>Faltas e frequência;</li>
                        <li>Metas pessoais ou acadêmicas;</li>
                        <li>Anotações e lembretes;</li>
                        <li>Sessões de estudo e registros do Pomodoro;</li>
                        <li>Histórico e estatísticas de produtividade;</li>
                        <li>Informações adicionadas voluntariamente pelo usuário.</li>
                    </ul>

                    <p>
                        Essas informações são utilizadas para fornecer as
                        funcionalidades de organização escolhidas pelo próprio
                        usuário.
                    </p>

                    <div class="destaque destaque-verde">
                        O FOAG não comercializa notas, atividades, horários,
                        registros de produtividade ou outras informações
                        acadêmicas dos usuários.
                    </div>
                </section>

                <section class="secao" id="dados-tecnicos">
                    <h2>
                        <span class="numero">05</span>
                        Dados técnicos e de utilização
                    </h2>

                    <p>
                        Algumas informações técnicas poderão ser registradas
                        automaticamente pelo navegador, servidor ou serviço de
                        hospedagem utilizado pela plataforma.
                    </p>

                    <p>Esses dados poderão incluir:</p>

                    <ul>
                        <li>Data e horário de acesso;</li>
                        <li>Tipo de navegador;</li>
                        <li>Tipo de dispositivo e sistema operacional;</li>
                        <li>Páginas e funcionalidades acessadas;</li>
                        <li>Registros de erros e falhas técnicas;</li>
                        <li>Endereço IP, quando registrado pelo servidor;</li>
                        <li>Informações necessárias à manutenção da sessão.</li>
                    </ul>

                    <p>
                        Esses dados poderão ser utilizados para segurança,
                        diagnóstico de erros, prevenção de fraudes e melhoria
                        do funcionamento da plataforma.
                    </p>
                </section>

                <section class="secao" id="finalidades">
                    <h2>
                        <span class="numero">06</span>
                        Como utilizamos os dados
                    </h2>

                    <p>As informações poderão ser utilizadas para:</p>

                    <ul>
                        <li>Criar e administrar a conta do usuário;</li>
                        <li>Confirmar a identidade durante o acesso;</li>
                        <li>Salvar tarefas, disciplinas e compromissos;</li>
                        <li>Exibir calendários e horários personalizados;</li>
                        <li>Registrar sessões do temporizador Pomodoro;</li>
                        <li>Calcular médias e estatísticas acadêmicas;</li>
                        <li>Personalizar tema, perfil e preferências;</li>
                        <li>Recuperar ou alterar dados da conta;</li>
                        <li>Prevenir acessos não autorizados;</li>
                        <li>Identificar e corrigir falhas;</li>
                        <li>Melhorar o desempenho e a experiência do usuário;</li>
                        <li>Responder solicitações e dúvidas;</li>
                        <li>Cumprir obrigações legais aplicáveis.</li>
                    </ul>

                    <p>
                        Os dados não deverão ser utilizados para finalidades
                        incompatíveis com esta política sem que o usuário seja
                        devidamente informado.
                    </p>
                </section>

                <section class="secao" id="bases-legais">
                    <h2>
                        <span class="numero">07</span>
                        Bases legais para o tratamento
                    </h2>

                    <p>
                        O tratamento de dados pessoais deverá ocorrer de acordo
                        com as hipóteses permitidas pela legislação aplicável.
                    </p>

                    <p>Dependendo da situação, o tratamento poderá se apoiar em:</p>

                    <ul>
                        <li>
                            Consentimento fornecido pelo usuário ou responsável;
                        </li>

                        <li>
                            Execução dos serviços solicitados pelo usuário;
                        </li>

                        <li>
                            Cumprimento de obrigação legal ou regulatória;
                        </li>

                        <li>
                            Exercício regular de direitos;
                        </li>

                        <li>
                            Proteção da segurança da plataforma e dos usuários;
                        </li>

                        <li>
                            Legítimo interesse, quando aplicável e respeitados
                            os direitos e as liberdades do titular.
                        </li>
                    </ul>

                    <p>
                        Quando o tratamento depender de consentimento, o usuário
                        poderá solicitar sua revogação, observadas as
                        consequências e obrigações legais aplicáveis.
                    </p>
                </section>

                <section class="secao" id="armazenamento">
                    <h2>
                        <span class="numero">08</span>
                        Armazenamento das informações
                    </h2>

                    <p>
                        Dependendo da versão e da estrutura técnica utilizada,
                        as informações do FOAG poderão ser armazenadas:
                    </p>

                    <ul>
                        <li>Em arquivos internos no formato JSON;</li>
                        <li>Em pastas protegidas da aplicação;</li>
                        <li>No navegador ou dispositivo do usuário;</li>
                        <li>Em sessões utilizadas durante o acesso;</li>
                        <li>Em servidores ou serviços de hospedagem;</li>
                        <li>Em cópias de segurança, quando disponíveis.</li>
                    </ul>

                    <p>
                        O armazenamento em arquivos JSON não significa que os
                        dados sejam públicos. Os arquivos deverão permanecer
                        protegidos contra acesso direto e não autorizado.
                    </p>

                    <p>
                        A estrutura de armazenamento poderá ser alterada no
                        futuro, inclusive com a adoção de banco de dados, sem
                        modificar os direitos assegurados ao usuário.
                    </p>

                    <div class="destaque">
                        O usuário não deverá tentar acessar, copiar, modificar
                        ou excluir arquivos internos pertencentes a outras
                        contas.
                    </div>
                </section>

                <section class="secao" id="cookies">
                    <h2>
                        <span class="numero">09</span>
                        Cookies, sessões e armazenamento local
                    </h2>

                    <p>
                        O FOAG poderá utilizar cookies técnicos, sessões,
                        localStorage ou tecnologias semelhantes para manter
                        funcionalidades essenciais.
                    </p>

                    <p>Essas tecnologias poderão ser usadas para:</p>

                    <ul>
                        <li>Manter o usuário conectado;</li>
                        <li>Identificar uma sessão válida;</li>
                        <li>Salvar tema claro ou escuro;</li>
                        <li>Salvar preferências de interface;</li>
                        <li>Manter configurações temporárias;</li>
                        <li>Melhorar o funcionamento das páginas.</li>
                    </ul>

                    <p>
                        O usuário poderá apagar cookies e dados locais nas
                        configurações do navegador. Essa ação poderá desconectar
                        a conta, redefinir preferências ou apagar informações
                        que estejam armazenadas somente no dispositivo.
                    </p>

                    <p>
                        O FOAG não utiliza cookies publicitários próprios para
                        vender informações ou criar anúncios personalizados.
                    </p>
                </section>

                <section class="secao" id="compartilhamento">
                    <h2>
                        <span class="numero">10</span>
                        Compartilhamento de informações
                    </h2>

                    <p>
                        O FOAG não vende, aluga ou comercializa dados pessoais
                        dos usuários.
                    </p>

                    <p>
                        As informações somente poderão ser compartilhadas nas
                        seguintes situações:
                    </p>

                    <ul>
                        <li>
                            Com serviços de hospedagem ou infraestrutura
                            necessários ao funcionamento da plataforma;
                        </li>

                        <li>
                            Quando o próprio usuário solicitar ou autorizar;
                        </li>

                        <li>
                            Para cumprimento de obrigação legal;
                        </li>

                        <li>
                            Para atender ordem judicial ou determinação válida;
                        </li>

                        <li>
                            Para investigar fraude ou incidente de segurança;
                        </li>

                        <li>
                            Para proteger direitos do FOAG, dos usuários ou
                            de terceiros.
                        </li>
                    </ul>

                    <p>
                        Sempre que possível, o compartilhamento deverá ser
                        limitado às informações estritamente necessárias para
                        cada finalidade.
                    </p>
                </section>

                <section class="secao" id="menores">
                    <h2>
                        <span class="numero">11</span>
                        Dados de crianças e adolescentes
                    </h2>

                    <p>
                        Por possuir finalidade educacional, o FOAG poderá ser
                        utilizado por crianças e adolescentes.
                    </p>

                    <p>
                        O tratamento de dados de menores deverá observar seu
                        melhor interesse, sua segurança e seu desenvolvimento.
                    </p>

                    <p>
                        Usuários menores de idade deverão utilizar a plataforma
                        com conhecimento e acompanhamento dos pais ou
                        responsáveis legais.
                    </p>

                    <p>Os responsáveis deverão:</p>

                    <ul>
                        <li>Acompanhar o cadastro e a utilização da conta;</li>
                        <li>Orientar o menor a não divulgar sua senha;</li>
                        <li>Evitar o cadastro de informações desnecessárias;</li>
                        <li>Solicitar correção ou exclusão quando necessário;</li>
                        <li>Comunicar qualquer situação de risco ou uso indevido.</li>
                    </ul>

                    <p>
                        Quando exigido pela legislação, será solicitado o
                        consentimento específico de pelo menos um dos pais
                        ou do responsável legal.
                    </p>

                    <p>
                        O FOAG não deverá condicionar a participação do menor
                        ao fornecimento de dados além daqueles estritamente
                        necessários à atividade solicitada.
                    </p>
                </section>

                <section class="secao" id="seguranca">
                    <h2>
                        <span class="numero">12</span>
                        Segurança das informações
                    </h2>

                    <p>
                        O FOAG deverá adotar medidas técnicas e administrativas
                        razoáveis para proteger as informações contra:
                    </p>

                    <ul>
                        <li>Acesso não autorizado;</li>
                        <li>Perda ou destruição acidental;</li>
                        <li>Alteração indevida;</li>
                        <li>Divulgação não autorizada;</li>
                        <li>Cópia ou transferência irregular;</li>
                        <li>Fraudes e tentativas de invasão.</li>
                    </ul>

                    <p>As medidas de proteção poderão incluir:</p>

                    <ul>
                        <li>Controle de acesso por usuário e senha;</li>
                        <li>Validação das informações recebidas;</li>
                        <li>Restrição de acesso aos arquivos internos;</li>
                        <li>Proteção das sessões de login;</li>
                        <li>Atualização e correção do sistema;</li>
                        <li>Cópias de segurança, quando disponíveis;</li>
                        <li>Monitoramento e correção de falhas.</li>
                    </ul>

                    <p>
                        Nenhum sistema é completamente livre de riscos. Por
                        isso, o usuário também deverá proteger sua senha,
                        dispositivo e conta.
                    </p>

                    <div class="destaque">
                        As senhas devem ser armazenadas de forma protegida,
                        utilizando técnicas seguras de hash, e nunca como
                        texto simples acessível.
                    </div>
                </section>

                <section class="secao" id="incidentes">
                    <h2>
                        <span class="numero">13</span>
                        Incidentes de segurança
                    </h2>

                    <p>
                        Caso seja identificado um incidente que possa causar
                        risco ou dano relevante aos usuários, serão adotadas
                        medidas para investigar, controlar e reduzir seus
                        efeitos.
                    </p>

                    <p>As medidas poderão incluir:</p>

                    <ul>
                        <li>Bloqueio temporário de acessos suspeitos;</li>
                        <li>Correção da falha identificada;</li>
                        <li>Redefinição de credenciais;</li>
                        <li>Preservação dos registros necessários;</li>
                        <li>Comunicação aos usuários afetados;</li>
                        <li>Comunicação às autoridades, quando exigida.</li>
                    </ul>

                    <p>
                        O usuário deverá comunicar imediatamente qualquer
                        acesso suspeito, perda de senha ou uso indevido de
                        sua conta.
                    </p>
                </section>

                <section class="secao" id="conservacao">
                    <h2>
                        <span class="numero">14</span>
                        Tempo de conservação dos dados
                    </h2>

                    <p>
                        Os dados serão mantidos somente pelo período necessário
                        ao funcionamento da conta, à prestação dos serviços e
                        ao cumprimento das finalidades informadas.
                    </p>

                    <p>
                        Após o encerramento da conta, os dados poderão ser
                        excluídos ou anonimizados, salvo quando sua conservação
                        for necessária para:
                    </p>

                    <ul>
                        <li>Cumprimento de obrigação legal;</li>
                        <li>Prevenção de fraudes;</li>
                        <li>Segurança da plataforma;</li>
                        <li>Exercício regular de direitos;</li>
                        <li>Atendimento de determinação válida.</li>
                    </ul>

                    <p>
                        Cópias de segurança poderão permanecer armazenadas por
                        período limitado até sua substituição ou exclusão
                        segura.
                    </p>
                </section>

                <section class="secao" id="direitos">
                    <h2>
                        <span class="numero">15</span>
                        Direitos do usuário
                    </h2>

                    <p>
                        O usuário, como titular dos dados pessoais, poderá
                        solicitar:
                    </p>

                    <ul>
                        <li>Confirmação da existência de tratamento;</li>
                        <li>Acesso aos dados pessoais vinculados à conta;</li>
                        <li>Correção de dados incompletos ou incorretos;</li>
                        <li>Atualização das informações pessoais;</li>
                        <li>Informações sobre o uso e compartilhamento;</li>
                        <li>Exclusão dos dados, quando aplicável;</li>
                        <li>Anonimização ou bloqueio de dados desnecessários;</li>
                        <li>Revogação do consentimento;</li>
                        <li>Revisão de decisões automatizadas, quando existentes;</li>
                        <li>Encerramento da conta.</li>
                    </ul>

                    <p>
                        Para proteger a conta, poderá ser necessário confirmar
                        a identidade do solicitante antes do atendimento.
                    </p>

                    <p>
                        Solicitações manifestamente fraudulentas, abusivas ou
                        realizadas por pessoas não autorizadas poderão ser
                        recusadas.
                    </p>
                </section>

                <section class="secao" id="exclusao">
                    <h2>
                        <span class="numero">16</span>
                        Exclusão da conta e dos dados
                    </h2>

                    <p>
                        O usuário poderá solicitar a exclusão de sua conta por
                        meio das configurações da plataforma, quando essa opção
                        estiver disponível, ou pelo canal de contato informado
                        nesta política.
                    </p>

                    <p>
                        Antes da exclusão, recomenda-se salvar uma cópia das
                        informações consideradas importantes.
                    </p>

                    <p>
                        A exclusão poderá remover definitivamente:
                    </p>

                    <ul>
                        <li>Dados de perfil;</li>
                        <li>Disciplinas cadastradas;</li>
                        <li>Tarefas e compromissos;</li>
                        <li>Notas e registros acadêmicos;</li>
                        <li>Metas e estatísticas;</li>
                        <li>Histórico do Pomodoro;</li>
                        <li>Preferências associadas à conta.</li>
                    </ul>

                    <p>
                        A exclusão poderá ser irreversível. Informações que
                        devam ser conservadas por obrigação legal poderão
                        permanecer bloqueadas pelo período necessário.
                    </p>
                </section>

                <section class="secao" id="terceiros">
                    <h2>
                        <span class="numero">17</span>
                        Serviços, fontes e links externos
                    </h2>

                    <p>
                        A plataforma poderá utilizar serviços externos para
                        fontes, hospedagem, bibliotecas de programação ou
                        outras funcionalidades técnicas.
                    </p>

                    <p>
                        Esses serviços poderão possuir políticas de privacidade
                        próprias e independentes.
                    </p>

                    <p>
                        Ao acessar um site externo por meio de um link, o
                        usuário ficará sujeito às regras e políticas daquele
                        serviço.
                    </p>

                    <p>
                        O FOAG não se responsabiliza pelas práticas de
                        privacidade de páginas externas que não estejam sob
                        seu controle.
                    </p>
                </section>

                <section class="secao" id="alteracoes">
                    <h2>
                        <span class="numero">18</span>
                        Alterações desta Política de Privacidade
                    </h2>

                    <p>
                        Esta Política de Privacidade poderá ser atualizada em
                        razão de mudanças na plataforma, melhorias de segurança
                        ou alterações legais.
                    </p>

                    <p>
                        A data da versão mais recente ficará disponível no
                        início do documento.
                    </p>

                    <p>
                        Quando houver mudanças importantes, o usuário poderá
                        ser informado por aviso dentro do FOAG ou por outro
                        canal disponível.
                    </p>

                    <p>
                        Recomenda-se consultar esta página periodicamente.
                    </p>
                </section>

                <section class="secao" id="legislacao">
                    <h2>
                        <span class="numero">19</span>
                        Legislação aplicável
                    </h2>

                    <p>
                        Esta política deverá ser interpretada de acordo com a
                        legislação brasileira aplicável.
                    </p>

                    <div class="links-legais">
                        <a class="link-legal" href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/L13709compilado.htm" target="_blank" rel="noopener noreferrer">
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

                        <a class="link-legal" href="https://www.gov.br/anpd/pt-br" target="_blank" rel="noopener noreferrer">
                            Autoridade Nacional de Proteção de Dados
                            <span>↗</span>
                        </a>
                    </div>
                </section>

                <section class="secao" id="contato">
                    <h2>
                        <span class="numero">20</span>
                        Contato sobre privacidade
                    </h2>

                    <p>
                        Para dúvidas, correções, exclusão de conta ou
                        solicitações relacionadas a dados pessoais, entre em
                        contato com os responsáveis pelo FOAG.
                    </p>

                    <div class="contato">
                        <p>
                            <strong>E-mail:</strong>
                            <a href="mailto:rafaella@gmail.com">
                                rafaella@gmail.com
                            </a>
                        </p>

                        <p>
                            <strong>Responsáveis:</strong>
                            Rafaella, Ralley e Kezia
                        </p>

                        <p>
                            <strong>Assunto recomendado:</strong>
                            Privacidade e proteção de dados — FOAG
                        </p>

                        <p>
                            Para facilitar o atendimento, informe seu nome,
                            e-mail vinculado à conta e o tipo de solicitação.
                            Não envie sua senha.
                        </p>
                    </div>
                </section>

                <footer class="rodape-politica">
                    <p>
                        FOAG — Ferramenta de Organização Acadêmica Geral
                    </p>

                    <p>
                        Esta política deve ser utilizada em conjunto com os
                        Termos de Uso da plataforma.
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