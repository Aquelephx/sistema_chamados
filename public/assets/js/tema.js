// 1. Configuração da URL Base (antes estava inline no HTML)
window.APP_BASE_URL = document.currentScript.getAttribute('data-base-url') || '';

// 2. Aplica o tema ANTES da tela pintar (evita piscar branco no modo escuro)
(function() {
    const raiz = document.documentElement;
    const metaCor = document.querySelector('meta[name="theme-color"]');
    
    function aplicarTemaSalvo() {
        try {
            const temaSalvo = localStorage.getItem('tema');
            const prefereEscuro = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const escuro = temaSalvo === 'dark' || (temaSalvo === null && prefereEscuro);
            
            if (escuro) {
                raiz.classList.add('dark-mode');
            } else {
                raiz.classList.remove('dark-mode');
            }
            
            if (metaCor) {
                metaCor.setAttribute('content', escuro ? '#14274a' : '#1d4a8f');
            }
        } catch (e) {}
    }

    aplicarTemaSalvo();

    // 3. Configura o botão de alternar tema
    document.addEventListener('DOMContentLoaded', () => {
        const btnTema = document.getElementById('btn-tema');
        if (!btnTema) return;

        function atualizarBotao() {
            const escuro = raiz.classList.contains('dark-mode');
            btnTema.textContent = escuro ? '☀️' : '🌙';
        }

        atualizarBotao();

        btnTema.addEventListener('click', () => {
            const ficouEscuro = raiz.classList.toggle('dark-mode');
            try { localStorage.setItem('tema', ficouEscuro ? 'dark' : 'light'); } catch (e) {}
            
            if (metaCor) {
                metaCor.setAttribute('content', ficouEscuro ? '#14274a' : '#1d4a8f');
            }
            atualizarBotao();
        });
    });
})();