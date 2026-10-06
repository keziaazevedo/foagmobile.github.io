document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ============================================
    // 1. ACORDEON - Expandir/Recolher
    // ============================================
    const cabecalhos = document.querySelectorAll('.card-cabecalho');

    function toggleSecao(cabecalho) {
        const card = cabecalho.closest('.configuracao-card');
        if (!card) return;
        
        const conteudo = card.querySelector('.card-conteudo');
        const toggle = cabecalho.querySelector('.card-toggle');
        
        if (!conteudo) return;
        
        const isOpen = conteudo.classList.contains('aberto');
        
        if (isOpen) {
            conteudo.classList.remove('aberto');
            if (toggle) toggle.classList.remove('ativo');
            void conteudo.offsetHeight;
        } else {
            conteudo.classList.add('aberto');
            if (toggle) toggle.classList.add('ativo');
            void conteudo.offsetHeight;
        }
    }

    cabecalhos.forEach(cabecalho => {
        cabecalho.addEventListener('click', function(e) {
            if (e.target.closest('button') || 
                e.target.closest('.switch') || 
                e.target.closest('select') || 
                e.target.closest('.cor') ||
                e.target.closest('a') ||
                e.target.closest('input')) {
                return;
            }
            toggleSecao(this);
        });
    });

    // Abrir a primeira seção por padrão
    const primeiroCard = document.querySelector('.configuracao-card');
    if (primeiroCard) {
        const conteudo = primeiroCard.querySelector('.card-conteudo');
        const toggle = primeiroCard.querySelector('.card-toggle');
        if (conteudo) {
            conteudo.classList.add('aberto');
            if (toggle) toggle.classList.add('ativo');
        }
    }

    // ============================================
    // 2. TOAST
    // ============================================
    function mostrarToast(mensagem, tipo = 'sucesso') {
        const toast = document.getElementById('toast-configuracoes');
        if (!toast) return;

        const icone = toast.querySelector('i');
        const span = toast.querySelector('span');

        if (tipo === 'sucesso') {
            icone.className = 'fa-solid fa-check-circle';
            icone.style.color = '#22c55e';
        } else if (tipo === 'erro') {
            icone.className = 'fa-solid fa-exclamation-circle';
            icone.style.color = '#ef4444';
        } else {
            icone.className = 'fa-solid fa-exclamation-circle';
            icone.style.color = '#f59e0b';
        }

        span.textContent = mensagem;
        toast.style.display = 'flex';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';

        setTimeout(() => {
            toast.style.transition = 'all 0.3s ease';
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 50);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
        }, 3500);
    }

    // ============================================
    // 3. MODAL DE CONFIRMAÇÃO
    // ============================================
    function abrirModal(titulo, texto, callback) {
        const modal = document.getElementById('confirmacao-modal');
        const tituloEl = document.getElementById('confirmacao-titulo');
        const textoEl = document.getElementById('confirmacao-texto');
        const confirmarBtn = document.getElementById('confirmar-acao');
        const cancelarBtn = document.getElementById('cancelar-acao');

        if (!modal || !tituloEl || !textoEl) return;

        tituloEl.textContent = titulo || 'Confirmar ação';
        textoEl.textContent = texto || 'Esta ação não poderá ser desfeita.';

        modal.style.display = 'flex';

        const handleConfirm = () => {
            modal.style.display = 'none';
            confirmarBtn.removeEventListener('click', handleConfirm);
            cancelarBtn.removeEventListener('click', handleCancel);
            if (callback) callback(true);
        };

        const handleCancel = () => {
            modal.style.display = 'none';
            confirmarBtn.removeEventListener('click', handleConfirm);
            cancelarBtn.removeEventListener('click', handleCancel);
            if (callback) callback(false);
        };

        confirmarBtn.addEventListener('click', handleConfirm);
        cancelarBtn.addEventListener('click', handleCancel);

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                confirmarBtn.removeEventListener('click', handleConfirm);
                cancelarBtn.removeEventListener('click', handleCancel);
                if (callback) callback(false);
            }
        });
    }

    // ============================================
    // 4. SALVAR CONFIGURAÇÕES
    // ============================================
    const form = document.getElementById('form-configuracoes');

    function salvarConfiguracoes(formData) {
        const dados = {};
        for (let [key, value] of formData.entries()) {
            dados[key] = value;
        }
        dados.acao = 'salvar';

        fetch(FOAG_CONFIG.pages.configuracoes, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(dados)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarToast('✅ ' + (data.message || 'Configurações salvas com sucesso!'), 'sucesso');
                const status = document.getElementById('status-configuracoes');
                if (status) {
                    status.innerHTML = '<i class="fa-solid fa-circle" style="color:#22c55e;font-size:0.6rem;"></i> ' +
                        new Date().toLocaleTimeString() + ' - Configurações salvas';
                }
            } else {
                mostrarToast('❌ ' + (data.message || 'Erro ao salvar configurações'), 'erro');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarToast('❌ Erro ao comunicar com o servidor', 'erro');
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            salvarConfiguracoes(formData);
        });
    }

    document.querySelectorAll('.btn-salvar-topo, .btn-principal').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.type === 'submit') return;
            e.preventDefault();
            if (form) {
                const formData = new FormData(form);
                salvarConfiguracoes(formData);
            }
        });
    });

    // ============================================
    // 5. IMPORTAR BACKUP
    // ============================================
    const btnImportar = document.getElementById('btn-importar');
    const inputBackup = document.getElementById('arquivo-backup');
    
    if (btnImportar && inputBackup) {
        btnImportar.addEventListener('click', function() {
            inputBackup.click();
        });

        inputBackup.addEventListener('change', function(e) {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('backup', file);

            mostrarToast('⏳ Importando backup...', 'info');

            fetch(FOAG_CONFIG.pages.configuracoes, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarToast('✅ ' + (data.message || 'Backup importado com sucesso!'), 'sucesso');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarToast('❌ ' + (data.message || 'Erro ao importar backup'), 'erro');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarToast('❌ Erro ao comunicar com o servidor', 'erro');
            });

            this.value = '';
        });
    }

    // ============================================
    // 6. AÇÕES DE PERIGO
    // ============================================
    document.querySelectorAll('[data-acao]').forEach(btn => {
        btn.addEventListener('click', function() {
            const acao = this.dataset.acao;
            const titulos = {
                'limpar-pomodoro': '🧹 Limpar histórico do Pomodoro',
                'limpar-atividades': '🧹 Limpar atividades concluídas',
                'apagar-dados': '⚠️ Apagar todos os dados'
            };
            const textos = {
                'limpar-pomodoro': 'Todos os ciclos do Pomodoro serão removidos permanentemente.',
                'limpar-atividades': 'Todas as atividades finalizadas serão removidas permanentemente.',
                'apagar-dados': 'TODOS os seus dados serão removidos permanentemente. Esta ação é irreversível!'
            };

            abrirModal(titulos[acao] || 'Confirmar ação', textos[acao] || 'Esta ação não poderá ser desfeita.', function(confirmado) {
                if (confirmado) {
                    mostrarToast('⏳ Executando ação...', 'info');
                    
                    fetch(FOAG_CONFIG.pages.configuracoes, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({ acao_perigo: acao })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            mostrarToast('✅ ' + (data.message || 'Ação executada com sucesso!'), 'sucesso');
                            if (acao === 'apagar-dados') {
                                setTimeout(() => location.reload(), 1500);
                            }
                        } else {
                            mostrarToast('❌ ' + (data.message || 'Erro ao executar ação'), 'erro');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        mostrarToast('❌ Erro ao comunicar com o servidor', 'erro');
                    });
                }
            });
        });
    });

    // ============================================
    // 7. TESTAR NOTIFICAÇÃO
    // ============================================
    const btnTestarNotif = document.getElementById('btn-testar-notificacao');
    if (btnTestarNotif) {
        btnTestarNotif.addEventListener('click', function() {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🔔 Teste do FOAG', {
                    body: 'Esta é uma notificação de teste das configurações.'
                });
                mostrarToast('✅ Notificação enviada!', 'sucesso');
            } else if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification('🔔 Teste do FOAG', {
                            body: 'Permissão concedida!'
                        });
                        mostrarToast('✅ Notificação enviada!', 'sucesso');
                    } else {
                        mostrarToast('❌ Permissão negada.', 'erro');
                    }
                });
            } else {
                mostrarToast('❌ Notificações não suportadas.', 'erro');
            }
        });
    }

    // ============================================
    // 8. LOGOUT
    // ============================================
    const iconSair = document.getElementById('icon-sair');
    const logoutModal = document.getElementById('logout-modal');
    const confirmLogout = document.getElementById('confirm-logout');
    const cancelLogout = document.getElementById('cancel-logout');

    if (iconSair) {
        iconSair.addEventListener('click', function(e) {
            e.stopPropagation();
            if (logoutModal) {
                logoutModal.style.display = 'flex';
            }
        });
    }

    if (confirmLogout) {
        confirmLogout.addEventListener('click', function() {
            if (logoutModal) logoutModal.style.display = 'none';
            mostrarToast('👋 Saindo... Até logo!', 'sucesso');
            setTimeout(() => {
                window.location.href = FOAG_CONFIG.pages.login;
            }, 800);
        });
    }

    if (cancelLogout) {
        cancelLogout.addEventListener('click', function() {
            if (logoutModal) logoutModal.style.display = 'none';
        });
    }

    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }

    // ============================================
    // 9. PERFIL
    // ============================================
    const iconPerfil = document.getElementById('icon-perfil');
    if (iconPerfil) {
        iconPerfil.addEventListener('click', function(e) {
            e.stopPropagation();
            mostrarToast('👤 Redirecionando para o perfil...', 'info');
            setTimeout(() => {
                window.location.href = FOAG_CONFIG.pages.perfil;
            }, 500);
        });
    }

    // ============================================
    // 10. FOGI
    // ============================================
    const iconFogi = document.getElementById('icon-fogi');
    const fogiModal = document.getElementById('fogi-modal');
    const fogiClose = document.getElementById('fogi-close');
    const fogiIframe = document.getElementById('fogi-iframe');

    if (iconFogi && fogiModal) {
        iconFogi.addEventListener('click', function(e) {
            e.stopPropagation();
            if (fogiModal.style.display === 'flex') {
                fogiModal.style.display = 'none';
                if (fogiIframe) fogiIframe.src = 'about:blank';
            } else {
                fogiModal.style.display = 'flex';
                if (fogiIframe) {
                    fogiIframe.src = 'https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=0';
                }
            }
        });
    }

    if (fogiClose && fogiModal) {
        fogiClose.addEventListener('click', function() {
            fogiModal.style.display = 'none';
            if (fogiIframe) fogiIframe.src = 'about:blank';
        });
    }

    if (fogiModal) {
        fogiModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                if (fogiIframe) fogiIframe.src = 'about:blank';
            }
        });
    }

    // ============================================
    // 11. TECLA ESC
    // ============================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal, #fogi-modal').forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    if (modal.id === 'fogi-modal' && fogiIframe) {
                        fogiIframe.src = 'about:blank';
                    }
                }
            });
        }
    });

    console.log('⚙️ Configurações FOAG carregadas!');
});