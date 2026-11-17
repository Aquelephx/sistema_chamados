document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. FILTRO DE SECRETARIA -> LOCAL ---
    const selectSecretaria = document.getElementById('select-secretaria');
    const selectLocal = document.getElementById('select-local');

    if (selectSecretaria && selectLocal) {
        const filtrarLocais = () => {
            const secretariaId = selectSecretaria.value;
            Array.from(selectLocal.options).forEach(opt => {
                if (opt.value === "") return;
                if (opt.dataset.secretaria === secretariaId) {
                    opt.hidden = false; opt.disabled = false;
                } else {
                    opt.hidden = true; opt.disabled = true;
                }
            });
            if (selectLocal.value) {
                const opcaoAtual = selectLocal.options[selectLocal.selectedIndex];
                if (opcaoAtual && opcaoAtual.dataset.secretaria !== secretariaId) {
                    selectLocal.value = "";
                }
            }
            selectLocal.dispatchEvent(new Event('update-options'));
        };
        selectSecretaria.addEventListener('change', filtrarLocais);
        setTimeout(filtrarLocais, 50); // Delay para o selects.js montar
    }

    // --- 2. OLHO DA SENHA E BARRA DE FORÇA ---
    const inputSenha = document.getElementById('senha-cadastro');
    
    if (inputSenha) {
        // Achar o botão do olho perto do input
        const wrapper = inputSenha.closest('.field-input-wrap');
        const btnToggle = wrapper ? wrapper.querySelector('.field-toggle') : null;
        const fill = document.getElementById('pwd-fill');
        const txt = document.getElementById('pwd-text');

        // Ação de clicar no olho
        if (btnToggle) {
            btnToggle.addEventListener('click', function() {
                const eye = this.querySelector('.icon-eye');
                const eyeOff = this.querySelector('.icon-eye-off');
                if (inputSenha.type === 'password') {
                    inputSenha.type = 'text'; 
                    eye.style.display = 'none'; 
                    eyeOff.style.display = 'block';
                } else {
                    inputSenha.type = 'password'; 
                    eye.style.display = 'block'; 
                    eyeOff.style.display = 'none';
                }
            });
        }

        // Ação de digitar na senha (atualiza a barrinha)
        inputSenha.addEventListener('input', function() {
            if (!fill || !txt) return;
            
            const senha = this.value;
            let forca = 0;
            if (senha.length >= 6) forca++;
            if (senha.length >= 10) forca++;
            if (/[A-Z]/.test(senha) && /[a-z]/.test(senha)) forca++;
            if (/[0-9]/.test(senha)) forca++;
            if (/[^A-Za-z0-9]/.test(senha)) forca++;

            const niveis = [
                { w: '10%', cor: '#dc2626', txt: 'Muito curta' },
                { w: '30%', cor: '#dc2626', txt: 'Fraca' },
                { w: '55%', cor: '#f59e0b', txt: 'Razoável' },
                { w: '75%', cor: '#3b82f6', txt: 'Boa' },
                { w: '90%', cor: '#059669', txt: 'Forte' },
                { w: '100%', cor: '#059669', txt: 'Excelente' }
            ];
            const nivel = niveis[Math.min(forca, 5)];
            
            // Muda a largura e a cor
            fill.style.width = senha.length === 0 ? '0' : nivel.w;
            fill.style.background = nivel.cor;
            txt.style.color = senha.length === 0 ? '#64748b' : nivel.cor;
            txt.innerText = senha.length === 0 ? 'Digite sua senha' : nivel.txt;
        });
    }
});
