document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('chart-categoria')) return;

    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 11;
    
    // Otimização: impede lag
    Chart.defaults.animation = false; 
    Chart.defaults.devicePixelRatio = 1; 

    const palette = {
        fila: '#f59e0b',
        atendimento: '#3b82f6',
        encerrado: '#10b981'
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, pointStyle: 'circle' } },
            tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.95)', padding: 10, cornerRadius: 8 }
        },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148, 163, 184, 0.08)' } }
        }
    };

    // Adicionado o 'evolucao' aqui
    let charts = { categoria: null, prioridade: null, tecnicos: null, status: null, evolucao: null };

    function atualizarRelogio() {
        const agora = new Date();
        const hora = document.getElementById('relogio-hora');
        if (hora) hora.innerText = agora.toLocaleTimeString('pt-BR');
    }
    setInterval(atualizarRelogio, 1000);
    atualizarRelogio();

    async function carregarDados() {
        try {
            const res = await fetch(window.APP_BASE_URL + '/api/indicadores');
            const data = await res.json();

            // Cards
            document.getElementById('card-fila').innerText = data.gerais.fila;
            document.getElementById('card-atendimento').innerText = data.gerais.atendimento;
            document.getElementById('card-encerrados').innerText = data.gerais.encerrados;
            document.getElementById('card-total').innerText = data.gerais.total;
            document.getElementById('card-sla').innerText = data.gerais.sla_cumprido + '%';
            document.getElementById('card-vencidos').innerText = data.gerais.vencidos;
            const h = Math.floor(data.gerais.tempo_medio / 60);
            const m = data.gerais.tempo_medio % 60;
            document.getElementById('card-tempo').innerText = `${h}:${m.toString().padStart(2, '0')}`;

            // Tabelas
            let htmlSla = '';
            data.sla_prazos.forEach(s => htmlSla += `<tr><td style="text-transform: capitalize;">${s.prioridade}</td><td>${s.prazo_horas} h</td></tr>`);
            document.getElementById('tabela-sla-corpo').innerHTML = htmlSla;

            let htmlEquipe = '';
            data.equipe.forEach(e => htmlEquipe += `<tr><td>${e}</td></tr>`);
            document.getElementById('tabela-equipe-corpo').innerHTML = htmlEquipe;

            let htmlCat = '';
            data.tabela_categorias.forEach(c => htmlCat += `<tr><td>${c.categoria}</td><td class="center">${c.fila}</td><td class="center">${c.atendimento}</td><td class="center">${c.encerrados}</td></tr>`);
            document.getElementById('tabela-categoria-corpo').innerHTML = htmlCat;

            let htmlPrio = '';
            data.tabela_prioridades.forEach(p => htmlPrio += `<tr><td style="text-transform: capitalize;">${p.prioridade}</td><td class="center">${p.fila}</td><td class="center">${p.atendimento}</td><td class="center">${p.encerrados}</td></tr>`);
            document.getElementById('tabela-prioridade-corpo').innerHTML = htmlPrio;

            let htmlTec = '';
            data.tecnicos.forEach(t => {
                const nota = t.satisfacao > 0 ? "⭐ " + Number(t.satisfacao).toFixed(1) : "-";
                htmlTec += `<tr><td>${t.nome}</td><td class="center">${t.fila}</td><td class="center">${t.encerrados}</td><td class="center">${Math.round(t.tempo_medio)} min</td><td class="center">100%</td><td class="center">${nota}</td></tr>`;
            });
            document.getElementById('tabela-tecnicos-corpo').innerHTML = htmlTec;

            let htmlStatus = '';
            data.status_totais.forEach(s => htmlStatus += `<tr><td style="text-transform: capitalize;">${s.status.replace('_', ' ')}</td><td class="center">${s.qtd}</td></tr>`);
            document.getElementById('tabela-status-corpo').innerHTML = htmlStatus;

            document.getElementById('ultima-atualizacao').innerText = new Date().toLocaleTimeString('pt-BR');

            renderizarGraficos(data);
        } catch (e) { console.error('Erro:', e); }
    }

    function renderizarGraficos(data) {
        // Categoria
        if (!charts.categoria) {
            charts.categoria = new Chart(document.getElementById('chart-categoria'), {
                type: 'bar',
                data: {
                    labels: data.tabela_categorias.map(c => c.categoria),
                    datasets: [
                        { label: 'Em Fila', data: data.tabela_categorias.map(c => c.fila), backgroundColor: palette.fila, borderRadius: 2 },
                        { label: 'Em Atendimento', data: data.tabela_categorias.map(c => c.atendimento), backgroundColor: palette.atendimento, borderRadius: 2 },
                        { label: 'Encerrados', data: data.tabela_categorias.map(c => c.encerrados), backgroundColor: palette.encerrado, borderRadius: 2 }
                    ]
                },
                options: chartOptions
            });
        } else {
            charts.categoria.data.labels = data.tabela_categorias.map(c => c.categoria);
            charts.categoria.data.datasets[0].data = data.tabela_categorias.map(c => c.fila);
            charts.categoria.data.datasets[1].data = data.tabela_categorias.map(c => c.atendimento);
            charts.categoria.data.datasets[2].data = data.tabela_categorias.map(c => c.encerrados);
            charts.categoria.update('none'); 
        }

        // Prioridade
        if (!charts.prioridade) {
            charts.prioridade = new Chart(document.getElementById('chart-prioridade'), {
                type: 'bar',
                data: {
                    labels: data.tabela_prioridades.map(p => p.prioridade.toUpperCase()),
                    datasets: [
                        { label: 'Em Fila', data: data.tabela_prioridades.map(p => p.fila), backgroundColor: palette.fila, borderRadius: 2 },
                        { label: 'Em Atendimento', data: data.tabela_prioridades.map(p => p.atendimento), backgroundColor: palette.atendimento, borderRadius: 2 },
                        { label: 'Encerrados', data: data.tabela_prioridades.map(p => p.encerrados), backgroundColor: palette.encerrado, borderRadius: 2 }
                    ]
                },
                options: chartOptions
            });
        } else {
            charts.prioridade.data.labels = data.tabela_prioridades.map(p => p.prioridade.toUpperCase());
            charts.prioridade.data.datasets[0].data = data.tabela_prioridades.map(p => p.fila);
            charts.prioridade.data.datasets[1].data = data.tabela_prioridades.map(p => p.atendimento);
            charts.prioridade.data.datasets[2].data = data.tabela_prioridades.map(p => p.encerrados);
            charts.prioridade.update('none');
        }

        // Técnicos
        if (!charts.tecnicos) {
            charts.tecnicos = new Chart(document.getElementById('chart-tecnicos'), {
                type: 'bar',
                data: { labels: data.tecnicos.map(t => t.nome), datasets: [{ data: data.tecnicos.map(t => Math.round(t.tempo_medio)), backgroundColor: palette.atendimento, borderRadius: 2, maxBarThickness: 30 }] },
                options: {
                    ...chartOptions,
                    plugins: { ...chartOptions.plugins, legend: { display: false } },
                    scales: { 
                        ...chartOptions.scales, 
                        x: { ticks: { maxRotation: 45, minRotation: 45, font: { size: 9 } } },
                        y: { ...chartOptions.scales.y, ticks: { precision: 0, callback: v => v + ' min' } } 
                    }
                }
            });
        } else {
            charts.tecnicos.data.labels = data.tecnicos.map(t => t.nome);
            charts.tecnicos.data.datasets[0].data = data.tecnicos.map(t => Math.round(t.tempo_medio));
            charts.tecnicos.update('none');
        }

        // Status (Pizza)
        const statusFormatado = { 'aberto': 'Aberto', 'em_andamento': 'Em Atendimento', 'aguardando': 'Aguardando', 'resolvido': 'Resolvido', 'fechado': 'Fechado' };
        const stLabels = data.status_totais.map(s => statusFormatado[s.status] || s.status);
        const stData = data.status_totais.map(s => s.qtd);
        const stCores = data.status_totais.map(s => {
            if (s.status === 'aberto') return palette.fila;
            if (s.status === 'em_andamento') return palette.atendimento;
            if (s.status === 'aguardando') return '#a855f7';
            if (s.status === 'resolvido') return '#06b6d4';
            return palette.encerrado;
        });

        if (!charts.status) {
            charts.status = new Chart(document.getElementById('chart-status'), {
                type: 'doughnut',
                data: { labels: stLabels, datasets: [{ data: stData, backgroundColor: stCores, borderWidth: 2, borderColor: '#0a1628' }] },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '60%',
                    plugins: { legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, font: {size: 10} } } }
                }
            });
        } else {
            charts.status.data.labels = stLabels;
            charts.status.data.datasets[0].data = stData;
            charts.status.data.datasets[0].backgroundColor = stCores;
            charts.status.update('none');
        }

        // Evolução Mensal (Gráfico de Linhas)
        const chartEvolucaoEl = document.getElementById('chart-evolucao');
        if (chartEvolucaoEl) { // Só roda se o canvas existir no HTML
            const evoLabels = data.evolucao_mensal.map(e => e.label);
            const evoAbertos = data.evolucao_mensal.map(e => e.abertos);
            const evoFechados = data.evolucao_mensal.map(e => e.fechados);

            if (!charts.evolucao) {
                charts.evolucao = new Chart(chartEvolucaoEl, {
                    type: 'line',
                    data: {
                        labels: evoLabels,
                        datasets: [
                            {
                                label: 'Abertos',
                                data: evoAbertos,
                                borderColor: palette.atendimento,
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Fechados',
                                data: evoFechados,
                                borderColor: palette.encerrado,
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        ...chartOptions,
                        plugins: {
                            ...chartOptions.plugins,
                            legend: { position: 'top', labels: { boxWidth: 10, usePointStyle: true, pointStyle: 'circle' } }
                        }
                    }
                });
            } else {
                charts.evolucao.data.labels = evoLabels;
                charts.evolucao.data.datasets[0].data = evoAbertos;
                charts.evolucao.data.datasets[1].data = evoFechados;
                charts.evolucao.update('none');
            }
        }
    }

    carregarDados();
    setInterval(carregarDados, 15000); 
});
