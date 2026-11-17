document.addEventListener('DOMContentLoaded', function() {
    const path = window.location.pathname;

    // 1. Salva a URL da última lista acessada
    if (
        path.endsWith('/chamados') || 
        path.endsWith('/relatorios') || 
        path.endsWith('/locais') || 
        path.endsWith('/equipamentos') || 
        path.endsWith('/usuarios')
    ) {
        sessionStorage.setItem('url_ultima_lista', window.location.href);
    }

    // 2. Recarrega a página se ela vier do cache do botão voltar (evita dados velhos)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted || (typeof window.performance != "undefined" && window.performance.navigation.type === 2)) {
            window.location.reload(); 
        }
    });

    // 3. Atualiza o link "← Voltar" das telas de chamado
    const btnVoltar = document.getElementById('btn-voltar-lista');
    if (btnVoltar && !btnVoltar.href.includes('voltar=relatorios')) {
        const ultimaLista = sessionStorage.getItem('url_ultima_lista');
        if (ultimaLista) {
            btnVoltar.href = ultimaLista;
        }
    }
});
