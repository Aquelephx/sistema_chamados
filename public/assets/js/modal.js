document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal-confirmacao');
    const msgEl = document.getElementById('modal-mensagem');
    const btnCancel = document.getElementById('modal-btn-cancelar');
    const btnConfirm = document.getElementById('modal-btn-confirmar');

    if (!modal) return;
    let formPendente = null;

    // Procura qualquer formulário que tenha o atributo data-confirmar
    document.querySelectorAll('form[data-confirmar]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (form.dataset.confirmado === 'true') return;
            e.preventDefault();
            formPendente = form;
            msgEl.textContent = form.dataset.confirmar || 'Tem certeza que deseja continuar?';
            modal.style.display = 'flex';
        });
    });

    btnCancel.addEventListener('click', () => {
        modal.style.display = 'none';
        formPendente = null;
    });

    btnConfirm.addEventListener('click', () => {
        if (formPendente) {
            formPendente.dataset.confirmado = 'true';
            formPendente.submit();
        }
        modal.style.display = 'none';
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
            formPendente = null;
        }
    });
});
