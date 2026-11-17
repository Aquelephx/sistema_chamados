(function() {
    // 1. APLICA O TEMA IMEDIATAMENTE (Antes da tela pintar)
    const raiz = document.documentElement;
    const metaCor = document.querySelector('meta[name="theme-color"]');
    
    function aplicarTemaSalvo() {
        try {
            const temaSalvo = localStorage.getItem('tema');
            const prefereEscuro = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Se salvou 'dark', ou se não salvou nada e o sistema tá no dark
            const escuro = temaSalvo === 'dark' || (temaSalvo === null && prefereEscuro);
            
            if (escuro) {
                raiz.classList.add('dark-mode');
            } else {
                raiz.classList.remove('dark-mode');
            }
            
            if (metaCor) {
                metaCor.setAttribute('content', escuro ? '#14274a' : '#1d4a8f');
            }
        } catch (e) {
            console.error('Erro ao ler o tema:', e);
        }
    }

    // Roda na hora!
    aplicarTemaSalvo();

    // 2. CONFIGURA O BOTÃO (Quando o HTML estiver pronto)
    document.addEventListener('DOMContentLoaded', () => {
        const btnTema = document.getElementById('btn-tema');
        if (!btnTema) return;

        // Atualiza o emoji do botão de acordo com o tema atual
        function atualizarBotao() {
            btnTema.textContent = raiz.classList.contains('dark-mode') ? '☀️' : '🌙';
        }

        atualizarBotao();

        // Evento de clique
        btnTema.addEventListener('click', () => {
            const ficouEscuro = raiz.classList.toggle('dark-mode');
            
            try { 
                localStorage.setItem('tema', ficouEscuro ? 'dark' : 'light'); 
            } catch (e) {}
            
            if (metaCor) {
                metaCor.setAttribute('content', ficouEscuro ? '#14274a' : '#1d4a8f');
            }
            
            atualizarBotao();
        });
    });
})();