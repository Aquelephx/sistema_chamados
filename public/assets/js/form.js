document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Lógica das miniaturas de fotos (tela de chamado)
    if (typeof initFotoPreviews === 'function') {
        initFotoPreviews({ 
            inputSelector: '.foto-input-anexo', 
            containerId: 'anexo-previews', 
            contadorId: 'anexo-contador', 
            maxFotos: 5 
        });
    }

    // 2. Lógica do campo "Motivo da Pausa" (Gerenciar Chamado)
    const selectStatus = document.getElementById('select-status');
    const campoMotivo = document.getElementById('campo-motivo-pausa');
    const inputMotivo = document.getElementById('input-motivo-pausa');
    
    if (selectStatus && campoMotivo && inputMotivo) {
        const verificarStatus = () => {
            if (selectStatus.value === 'aguardando') {
                campoMotivo.style.display = 'block';
                inputMotivo.required = true;
            } else {
                campoMotivo.style.display = 'none';
                inputMotivo.required = false;
            }
        };
        
        selectStatus.addEventListener('change', verificarStatus);
        verificarStatus(); // Executa ao carregar a página
    }
});
