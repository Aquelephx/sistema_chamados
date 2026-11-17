document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('select:not(.sel-nativo)');

    selects.forEach(select => {
        const wrapper = document.createElement('div');
        wrapper.className = 'sel';

        const botao = document.createElement('button');
        botao.type = 'button';
        botao.className = 'sel-botao';

        const lista = document.createElement('ul');
        lista.className = 'sel-lista';

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.classList.add('sel-nativo');

        wrapper.appendChild(botao);
        wrapper.appendChild(lista);

        const atualizarBotao = () => {
            if (select.multiple) {
                const textos = Array.from(select.selectedOptions).map(o => o.text);
                botao.textContent = textos.length
                    ? textos.join(', ')
                    : (select.dataset.placeholder || 'Selecione...');
                return;
            }
            const opcaoSelecionada = select.options[select.selectedIndex];
            
            // Se a opção estiver vazia (placeholder), usa o texto do data-placeholder
            if (opcaoSelecionada && opcaoSelecionada.text.trim() !== '') {
                botao.textContent = opcaoSelecionada.text;
            } else {
                botao.textContent = select.dataset.placeholder || 'Selecione...';
            }
        };

        const criarLista = () => {
            lista.innerHTML = '';
            Array.from(select.options).forEach((opcao) => {
                // Pula opções vazias, escondidas ou desabilitadas
                if (opcao.value === "" || opcao.hidden || opcao.disabled) return;

                const li = document.createElement('li');
                li.textContent = opcao.text;
                li.setAttribute('data-value', opcao.value);
                if (opcao.selected) li.setAttribute('aria-selected', 'true');

                li.addEventListener('click', (e) => {
                    e.stopPropagation();

                    if (select.multiple) {
                        opcao.selected = !opcao.selected;
                        if (opcao.selected) li.setAttribute('aria-selected', 'true');
                        else li.removeAttribute('aria-selected');
                        select.dispatchEvent(new Event('change'));
                        atualizarBotao();
                        return;
                    }

                    select.value = opcao.value; 
                    select.dispatchEvent(new Event('change'));
                    atualizarBotao();
                    wrapper.classList.remove('aberto');

                    lista.querySelectorAll('li').forEach(el => el.removeAttribute('aria-selected'));
                    li.setAttribute('aria-selected', 'true');
                });

                lista.appendChild(li);
            });
        };

        botao.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.sel.aberto').forEach(s => {
                if (s !== wrapper) s.classList.remove('aberto');
            });
            wrapper.classList.toggle('aberto');
        });

        atualizarBotao();
        criarLista();

        select.addEventListener('change', atualizarBotao);

        // Ouve o filtro do cadastro.js
        select.addEventListener('update-options', () => {
            atualizarBotao();
            criarLista();
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.sel')) {
            document.querySelectorAll('.sel.aberto').forEach(s => s.classList.remove('aberto'));
        }
    });
});
