document.addEventListener('DOMContentLoaded', function() {
    const inputOriginal = document.getElementById('input-foto-original');
    const inputFinal = document.getElementById('input-foto-final');
    const modal = document.getElementById('modal-crop');
    const cropImage = document.getElementById('crop-imagem');
    const btnCancelar = document.getElementById('crop-cancelar');
    const btnSalvar = document.getElementById('crop-salvar');
    const avatarPreview = document.getElementById('avatar-preview');

    if (!inputOriginal || !modal) return;

    let cropper = null;

    inputOriginal.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecione uma imagem (JPG, PNG ou WebP).');
            inputOriginal.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(ev) {
            cropImage.src = ev.target.result;
            modal.style.display = 'flex';

            // Pequeno delay para o navegador processar a imagem antes de iniciar o cropper
            setTimeout(function() {
                if (cropper) cropper.destroy();
                cropper = new Cropper(cropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    dragMode: 'move',
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                    guides: false,
                    center: false,
                    highlight: false,
                    background: false,
                    responsive: true,
                    restore: false,
                    checkCrossOrigin: false,
                    zoomOnWheel: true,
                    zoomOnTouch: true,
                    movable: true,
                    scalable: false,
                    rotatable: false
                });
            }, 150);
        };
        reader.readAsDataURL(file);
    });

    btnCancelar.addEventListener('click', function() {
        if (cropper) { cropper.destroy(); cropper = null; }
        modal.style.display = 'none';
        inputOriginal.value = '';
    });

    btnSalvar.addEventListener('click', function() {
        if (!cropper) return;

        // Gera uma imagem quadrada de 400x400 (o backend depois reduz para 200)
        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        canvas.toBlob(function(blob) {
            const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(file);
            inputFinal.files = dt.files;

            // Atualiza o preview imediatamente
            const url = URL.createObjectURL(blob);
            avatarPreview.innerHTML = '<img src="' + url + '" alt="Nova foto" style="width:100%;height:100%;object-fit:cover;">';

            cropper.destroy();
            cropper = null;
            modal.style.display = 'none';
            inputOriginal.value = '';
        }, 'image/jpeg', 0.9);
    });
});
