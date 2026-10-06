<!DOCTYPE html>
<html lang="pt-br">

<head>
    <script src="../global/js/config.js?v=<?= time() ?>"></script>
    <script src="../global/js/utils.js?v=<?= time() ?>"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro de Usuário</title>

    <link rel="stylesheet" href="estilocads.css?v=30">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet"
    >
</head>

<body>

    <div class="logo">FOAG</div>

    <div class="form-container">

        <form
            id="form-cadastro"
            method="POST"
            action="../api/auth/cadastro.php"
            autocomplete="off"
        >

            <!-- Campos isca para evitar que navegadores/gerenciadores preencham o cadastro -->
            <div
                aria-hidden="true"
                style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;"
            >
                <input
                    type="text"
                    name="usuario_salvo_ignorar"
                    autocomplete="username"
                    tabindex="-1"
                >
                <input
                    type="password"
                    name="senha_salva_ignorar"
                    autocomplete="current-password"
                    tabindex="-1"
                >
            </div>

            <!-- ==================================================
                 ETAPA 1
            =================================================== -->

            <section id="step-1" class="etapa">

                <div class="etapa-topo">

                    <div>
                        <span class="etapa-numero">Etapa 1 de 2</span>
                        <h2>Criar sua conta</h2>
                        <p>Preencha seus dados para continuar.</p>
                    </div>

                </div>


                <div class="progresso">

                    <div class="progresso-item ativo">
                        <span>1</span>
                        <small>Conta</small>
                    </div>

                    <div class="progresso-linha"></div>

                    <div class="progresso-item">
                        <span>2</span>
                        <small>Segurança</small>
                    </div>

                </div>


                <div class="campos-container">

                    <div class="campo-form">

                        <label for="nome">
                            Nome
                        </label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            placeholder="Digite seu nome"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="words"
                            spellcheck="false"
                            required
                        
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true">

                    </div>


                    <div class="campo-form">

                        <label for="email">
                            E-mail
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="exemplo@email.com"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                        
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true">

                    </div>


                    <div class="campo-form">

                        <label>
                            Data de nascimento
                        </label>

                        <div class="data-wrapper">

                            <div class="data-input-group">

                                <input
                                    type="text"
                                    id="data_dia"
                                    class="data-parte"
                                    placeholder="DD"
                                    maxlength="2"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    required
                                
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true">

                                <span class="data-separador">
                                    /
                                </span>

                                <input
                                    type="text"
                                    id="data_mes"
                                    class="data-parte"
                                    placeholder="MM"
                                    maxlength="2"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    required
                                
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true">

                                <span class="data-separador">
                                    /
                                </span>

                                <input
                                    type="text"
                                    id="data_ano"
                                    class="data-parte"
                                    placeholder="AAAA"
                                    maxlength="4"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    required
                                
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true">

                            </div>

                            <input
                                type="hidden"
                                id="data_nascimento"
                                name="data_nascimento"
                            >

                            <span
                                id="data-erro"
                                class="data-erro"
                            >
                                ⚠️ Data de nascimento inválida.
                            </span>

                        </div>

                    </div>


                    <div class="form-row">

                        <div class="password-wrapper">

                            <label for="senha">
                                Senha
                            </label>

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                placeholder="********"
                                required
                            
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true">

                            <span
                                class="toggle-visibility"
                                data-target="senha"
                            >
                                🙈
                            </span>

                        </div>


                        <div class="password-wrapper">

                            <label for="confirmar_senha">
                                Confirmar senha
                            </label>

                            <input
                                type="password"
                                id="confirmar_senha"
                                name="confirmar_senha"
                                placeholder="********"
                                required
                            
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true">

                            <span
                                class="toggle-visibility"
                                data-target="confirmar_senha"
                            >
                                🙈
                            </span>

                        </div>

                    </div>


                    <div class="senha-regras">
                        A senha deve ter pelo menos 8 caracteres,
                        uma letra maiúscula, um número e um símbolo.
                    </div>

                </div>


                <div class="acoes etapa1-acoes">

                    <button
                        type="button"
                        id="btn-proximo"
                        class="btn-principal"
                    >
                        Continuar
                        <span>→</span>
                    </button>

                </div>

            </section>


            <!-- ==================================================
                 ETAPA 2
            =================================================== -->

            <section
                id="step-2"
                class="etapa etapa-oculta"
            >

                <div class="etapa-topo">

                    <div>
                        <span class="etapa-numero">Etapa 2 de 2</span>
                        <h2>Segurança da conta</h2>
                        <p>
                            Configure uma forma simples de recuperar sua senha.
                        </p>
                    </div>

                </div>


                <div class="progresso">

                    <div class="progresso-item concluido">
                        <span>✓</span>
                        <small>Conta</small>
                    </div>

                    <div class="progresso-linha concluida"></div>

                    <div class="progresso-item ativo">
                        <span>2</span>
                        <small>Segurança</small>
                    </div>

                </div>


                <div class="seguranca-box">

                    <div class="seguranca-cabecalho">

                        <div class="seguranca-icone">
                            🔐
                        </div>

                        <div>

                            <strong>
                                Recuperação de senha
                            </strong>

                            <p>
                                Escolha uma pergunta e uma resposta
                                que você consiga lembrar.
                            </p>

                        </div>

                    </div>


                    <div class="campo-form">

                        <label for="pergunta_secreta">
                            Pergunta de segurança
                        </label>

                        <select
                            id="pergunta_secreta"
                            name="pergunta_secreta"
                            autocomplete="off"
                            required
                        >

                            <option value="">
                                Escolha uma pergunta
                            </option>

                            <option value="Qual o nome do seu primeiro animal?">
                                Qual o nome do seu primeiro animal?
                            </option>

                            <option value="Qual era seu apelido de infância?">
                                Qual era seu apelido de infância?
                            </option>

                            <option value="Qual o nome da sua primeira escola?">
                                Qual o nome da sua primeira escola?
                            </option>

                            <option value="Qual o nome do seu personagem favorito?">
                                Qual o nome do seu personagem favorito?
                            </option>

                            <option value="Qual era sua brincadeira favorita quando criança?">
                                Qual era sua brincadeira favorita quando criança?
                            </option>

                        </select>

                    </div>


                    <div class="campo-form">

                        <label for="resposta_secreta">
                            Resposta secreta
                        </label>

                        <input
                            type="text"
                            id="resposta_secreta"
                            name="resposta_secreta"
                            placeholder="Digite sua resposta"
                            autocomplete="off"
                            required
                        
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-bwignore="true">

                    </div>


                    <div class="aviso-recuperacao">

                        <span>⚠️</span>

                        <div>

                            <strong>
                                Guarde essa resposta.
                            </strong>

                            <p>
                                Caso você esqueça ou queira redefinir sua senha,
                                será necessário informar essa resposta.
                            </p>

                        </div>

                    </div>

                </div>


                <label class="termos">

                    <input
                        type="checkbox"
                        name="termos"
                        id="termos"
                        required
                    >

                    <span>
                        Aceito os
                        <a href="termos.php">
                            termos de uso
                        </a>
                    </span>

                </label>


                <div class="acoes">

                    <button
                        type="button"
                        id="btn-voltar"
                        class="btn-voltar"
                    >
                        ← Voltar
                    </button>

                    <button
                        type="submit"
                        class="btn-principal"
                    >
                        Cadastrar conta
                    </button>

                </div>

            </section>

        </form>

    </div>


    <!-- ==================================================
         VLIBRAS
    =================================================== -->

    <div vw class="enabled">

        <div
            vw-access-button
            class="active"
        ></div>

        <div vw-plugin-wrapper>

            <div class="vw-plugin-top-wrapper"></div>

        </div>

    </div>


    <script src="https://vlibras.gov.br/app.js"></script>

    <script>
        new window.VLibras.Widget(
            'https://vlibras.gov.br/app'
        );
    </script>


    <!-- ==================================================
         JAVASCRIPT
    =================================================== -->

    <script>

        const form = document.getElementById('form-cadastro');

        /* ==================================================
           BLOQUEAR / LIMPAR PREENCHIMENTO AUTOMÁTICO
        =================================================== */

        const camposSemAutofill = [
            'nome',
            'email',
            'data_dia',
            'data_mes',
            'data_ano',
            'senha',
            'confirmar_senha',
            'resposta_secreta'
        ];

        function limparPreenchimentoAutomatico() {
            camposSemAutofill.forEach(id => {
                const campo = document.getElementById(id);

                if (!campo) return;

                campo.value = '';

                campo.setAttribute(
                    'autocomplete',
                    (
                        id === 'senha' ||
                        id === 'confirmar_senha'
                    )
                        ? 'new-password'
                        : 'off'
                );
            });

            const dataHidden = document.getElementById('data_nascimento');

            if (dataHidden) {
                dataHidden.value = '';
            }
        }

        /*
         * Executa em mais de um momento porque alguns navegadores
         * tentam preencher os campos depois que a página já carregou.
         */
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                limparPreenchimentoAutomatico();

                setTimeout(
                    limparPreenchimentoAutomatico,
                    100
                );

                setTimeout(
                    limparPreenchimentoAutomatico,
                    500
                );
            }
        );

        window.addEventListener(
            'pageshow',
            function () {
                setTimeout(
                    limparPreenchimentoAutomatico,
                    50
                );
            }
        );

        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');

        const btnProximo = document.getElementById('btn-proximo');
        const btnVoltar = document.getElementById('btn-voltar');

        const diaInput = document.getElementById('data_dia');
        const mesInput = document.getElementById('data_mes');
        const anoInput = document.getElementById('data_ano');

        const dataHidden = document.getElementById('data_nascimento');
        const erroData = document.getElementById('data-erro');

        const anoAtual = new Date().getFullYear();


        /* ==================================================
           DATA
        =================================================== */

        function validarDataCompleta(dia, mes, ano) {

            const d = parseInt(dia);
            const m = parseInt(mes);
            const a = parseInt(ano);

            if (
                isNaN(d) ||
                isNaN(m) ||
                isNaN(a)
            ) {
                return false;
            }

            if (
                a < 1930 ||
                a > anoAtual
            ) {
                return false;
            }

            if (
                m < 1 ||
                m > 12
            ) {
                return false;
            }

            const diasPorMes = [
                31,
                28,
                31,
                30,
                31,
                30,
                31,
                31,
                30,
                31,
                30,
                31
            ];

            let diasNoMes = diasPorMes[m - 1];

            const bissexto =
                a % 400 === 0 ||
                (
                    a % 4 === 0 &&
                    a % 100 !== 0
                );

            if (
                m === 2 &&
                bissexto
            ) {
                diasNoMes = 29;
            }

            if (
                d < 1 ||
                d > diasNoMes
            ) {
                return false;
            }

            return true;
        }


        function atualizarData() {

            const dia = diaInput.value.trim();
            const mes = mesInput.value.trim();
            const ano = anoInput.value.trim();

            if (
                dia.length !== 2 ||
                mes.length !== 2 ||
                ano.length !== 4
            ) {

                dataHidden.value = '';

                erroData.classList.remove('visivel');

                return false;
            }

            if (
                validarDataCompleta(
                    dia,
                    mes,
                    ano
                )
            ) {

                const d = dia.padStart(2, '0');
                const m = mes.padStart(2, '0');

                dataHidden.value =
                    `${ano}-${m}-${d}`;

                erroData.classList.remove('visivel');

                [
                    diaInput,
                    mesInput,
                    anoInput
                ].forEach(input => {

                    input.classList.remove('invalido');
                    input.classList.add('valido');

                });

                return true;
            }

            dataHidden.value = '';

            erroData.classList.add('visivel');

            [
                diaInput,
                mesInput,
                anoInput
            ].forEach(input => {

                input.classList.remove('valido');
                input.classList.add('invalido');

            });

            return false;
        }


        function configurarCampoData(
            input,
            proximo,
            tamanho
        ) {

            input.addEventListener(
                'input',
                function () {

                    this.value =
                        this.value.replace(/\D/g, '');

                    if (
                        this.value.length === tamanho &&
                        proximo
                    ) {
                        proximo.focus();
                    }

                    atualizarData();

                }
            );

        }


        configurarCampoData(
            diaInput,
            mesInput,
            2
        );

        configurarCampoData(
            mesInput,
            anoInput,
            2
        );

        configurarCampoData(
            anoInput,
            null,
            4
        );


        /* ==================================================
           MOSTRAR / ESCONDER SENHA
        =================================================== */

        document
            .querySelectorAll('.toggle-visibility')
            .forEach(icon => {

                icon.addEventListener(
                    'click',
                    function () {

                        const input =
                            document.getElementById(
                                this.dataset.target
                            );

                        if (
                            input.type === 'password'
                        ) {

                            input.type = 'text';
                            this.textContent = '🙉';

                        } else {

                            input.type = 'password';
                            this.textContent = '🙈';

                        }

                    }
                );

            });


        /* ==================================================
           VALIDAR ETAPA 1
        =================================================== */

        function validarEtapa1() {

            const nome =
                document.getElementById('nome')
                    .value
                    .trim();

            const email =
                document.getElementById('email')
                    .value
                    .trim();

            const senha =
                document.getElementById('senha')
                    .value;

            const confirmar =
                document.getElementById('confirmar_senha')
                    .value;


            if (!nome) {

                alert(
                    'Digite seu nome.'
                );

                return false;
            }


            if (!email) {

                alert(
                    'Digite seu e-mail.'
                );

                return false;
            }


            const emailValido =
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (
                !emailValido.test(email)
            ) {

                alert(
                    'Digite um e-mail válido.'
                );

                return false;
            }


            if (
                !atualizarData()
            ) {

                alert(
                    'Informe uma data de nascimento válida.'
                );

                return false;
            }


            const regexSenha =
                /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/;


            if (
                !regexSenha.test(senha)
            ) {

                alert(
                    'A senha deve ter pelo menos 8 caracteres, uma letra maiúscula, um número e um símbolo.'
                );

                return false;
            }


            if (
                senha !== confirmar
            ) {

                alert(
                    'As senhas não coincidem.'
                );

                return false;
            }

            return true;
        }


        /* ==================================================
           CONTINUAR
        =================================================== */

        btnProximo.addEventListener(
            'click',
            function () {

                if (
                    !validarEtapa1()
                ) {
                    return;
                }

                step1.classList.add('etapa-oculta');
                step2.classList.remove('etapa-oculta');

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            }
        );


        /* ==================================================
           VOLTAR
        =================================================== */

        btnVoltar.addEventListener(
            'click',
            function () {

                step2.classList.add('etapa-oculta');
                step1.classList.remove('etapa-oculta');

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            }
        );


        /* ==================================================
           ENVIO FINAL
        =================================================== */

        form.addEventListener(
            'submit',
            function (event) {

                if (
                    !validarEtapa1()
                ) {

                    event.preventDefault();

                    step2.classList.add('etapa-oculta');
                    step1.classList.remove('etapa-oculta');

                    return;
                }


                const pergunta =
                    document.getElementById(
                        'pergunta_secreta'
                    ).value;

                const resposta =
                    document.getElementById(
                        'resposta_secreta'
                    ).value.trim();

                const termos =
                    document.getElementById(
                        'termos'
                    );


                if (!pergunta) {

                    event.preventDefault();

                    alert(
                        'Escolha uma pergunta de segurança.'
                    );

                    return;
                }


                if (
                    resposta.length < 2
                ) {

                    event.preventDefault();

                    alert(
                        'Digite sua resposta secreta.'
                    );

                    return;
                }


                if (
                    !termos.checked
                ) {

                    event.preventDefault();

                    alert(
                        'Você precisa aceitar os termos de uso.'
                    );

                    return;
                }

            }
        );


        /* ==================================================
           ESTADO INICIAL
        =================================================== */

        window.addEventListener(
            'load',
            function () {

                step1.classList.remove('etapa-oculta');
                step2.classList.add('etapa-oculta');

                limparPreenchimentoAutomatico();

            }
        );

    </script>

</body>

</html>