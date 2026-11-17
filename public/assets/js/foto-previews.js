function initFotoPreviews(config) {
    const { inputSelector, containerId, contadorId, maxFotos } = config;
    const inputs = document.querySelectorAll(inputSelector);
    const container = document.getElementById(containerId);
    const contador = document.getElementById(contadorId);
    
    if (!container || inputs.length === 0) return;

    function atualizarPreviews() {
        const todosArquivos = [];
        const origem = []; 
        
        // Monta a lista de arquivos e de qual input cada um veio
        let idx = 0;
        inputs.forEach(inp => {
            Array.from(inp.files).forEach(f => {
                todosArquivos.push(f);
                origem.push(idx);
            });
            idx++;
        });

        // Trava no máximo de fotos permitido
        if (maxFotos && todosArquivos.length > maxFotos) {
            alert('Máximo de ' + maxFotos + ' fotos. As excedentes foram removidas.');
            todosArquivos.length = maxFotos;
            origem.length = maxFotos;
            
            // Limpa todos os inputs e recoloca só as fotos permitidas
            let iInput = 0;
            inputs.forEach(inp => {
                const dt = new DataTransfer();
                todosArquivos.forEach((f, i) => {
                    if (origem[i] === iInput) dt.items.add(f);
                });
                inp.files = dt.files;
                iInput++;
            });
        }

        container.innerHTML = '';
        
        // Cria as miniaturas
        todosArquivos.forEach((file, i) => {
            const div = document.createElement('div');
            div.className = 'foto-preview-item';
            
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.title = file.name;
            
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'foto-remove';
            btn.innerHTML = '×';
            btn.title = 'Remover foto';
            
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const inputOriginal = inputs[origem[i]];
                const fileARemover = todosArquivos[i];
                
                // Recria a lista do input original sem a foto removida
                const dt = new DataTransfer();
                Array.from(inputOriginal.files).forEach(f => {
                    if (f !== fileARemover) dt.items.add(f);
                });
                inputOriginal.files = dt.files;
                
                atualizarPreviews();
            });

            div.appendChild(img);
            div.appendChild(btn);
            container.appendChild(div);
        });

        // Atualiza o contador
        if (contador) {
            if (todosArquivos.length > 0) {
                contador.classList.add('ativo');
                contador.textContent = todosArquivos.length + ' foto(s) selecionada(s)';
            } else {
                contador.classList.remove('ativo');
                contador.textContent = '';
            }
        }
    }

    inputs.forEach(inp => inp.addEventListener('change', atualizarPreviews));
}
