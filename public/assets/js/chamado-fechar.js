document.addEventListener('DOMContentLoaded', function() {
    const selectTipo = document.getElementById('select-tipo');
    const selectEquipamento = document.getElementById('select-equipamento');
    const divOutro = document.getElementById('div-tipo-outro');
    const inputOutro = document.getElementById('input-tipo-outro');

    if (!selectTipo || !selectEquipamento) return;

    function atualizarTela() {
        const tipoSelecionado = selectTipo.value;

        // 1. Lógica do Campo "Outros"
        if (tipoSelecionado === 'Outros') {
            divOutro.style.display = 'block';
            inputOutro.required = true;
        } else {
            divOutro.style.display = 'none';
            inputOutro.required = false;
            inputOutro.value = ''; 
        }

        // 2. Lógica do Select de Equipamentos
        selectEquipamento.innerHTML = '';
        selectEquipamento.add(new Option('Nenhum / Não se aplica', 'Nenhum'));

        if (tipoSelecionado && mapaEquipamentos[tipoSelecionado]) {
            const permitidos = mapaEquipamentos[tipoSelecionado];
            todosEquipamentos.forEach(eq => {
                if (permitidos.includes(eq)) {
                    selectEquipamento.add(new Option(eq, eq));
                }
            });
        } else {
            todosEquipamentos.forEach(eq => {
                if (eq !== 'Nenhum') {
                    selectEquipamento.add(new Option(eq, eq));
                }
            });
        }

        // Avisa o selects.js para redesenhar o menu bonito
        selectEquipamento.dispatchEvent(new Event('update-options'));
    }

    atualizarTela();
    selectTipo.addEventListener('change', atualizarTela);
});
