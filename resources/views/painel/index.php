<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Gestão - TI</title>
    <link rel="stylesheet" href="<?= \App\Core\Asset::url('/assets/css/painel.css') ?>">
    <script>window.APP_BASE_URL = "<?= $_ENV['APP_BASE_PATH'] ?? '' ?>";</script>
</head>
<body>
    <header>
        <div class="header-esquerda">
            <!-- BOTÃO VOLTAR (Só para admin e técnico) -->
            <?php if (in_array($usuario->perfil, ['admin', 'tecnico'])): ?>
                <a href="<?= \App\Core\Url::to('/chamados') ?>" class="btn-voltar-painel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Voltar ao Sistema
                </a>
            <?php endif; ?>
            
            <div class="header-titulo">
                <h1>PAINEL DE GESTÃO — SUPORTE DE T.I.</h1>
                <span class="subtitulo">Coordenação de Gestão de Tecnologia e Inovação • Central de Atendimento à Suporte de TI</span>
            </div>
        </div>
        <div class="header-right">
            <div class="relogio" id="relogio-hora">00:00:00</div>
        </div>
    </header>

    <div class="cards-topo">
        <div class="card c-laranja"><div class="valor" id="card-fila">0</div><div class="titulo">EM FILA (AGUARDANDO)</div></div>
        <div class="card c-azul"><div class="valor" id="card-atendimento">0</div><div class="titulo">EM ATENDIMENTO</div></div>
        <div class="card c-verde"><div class="valor" id="card-encerrados">0</div><div class="titulo">ENCERRADOS</div></div>
        <div class="card c-azul-escuro"><div class="valor" id="card-total">0</div><div class="titulo">TOTAL DE CHAMADOS</div></div>
        <div class="card c-verde-claro"><div class="valor" id="card-sla">0%</div><div class="titulo">SLA CUMPRIDO</div></div>
        <div class="card c-roxo"><div class="valor" id="card-tempo">0:00</div><div class="titulo">TEMPO MÉDIO ATEND. (H)</div></div>
        <div class="card c-vermelho"><div class="valor" id="card-vencidos">0</div><div class="titulo">CHAMADOS VENCIDOS</div></div>
    </div>

    <div class="grid-linha">
        <div class="bloco" style="grid-column: span 2;">
            <h3>CONFIGURAÇÕES (editável pela gestão)</h3>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <h4 style="font-size: 11px; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase;">Prazo de SLA por Prioridade</h4>
                    <table><thead><tr><th>Prioridade</th><th>Prazo (horas)</th></tr></thead><tbody id="tabela-sla-corpo"></tbody></table>
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <h4 style="font-size: 11px; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase;">Equipe de TI</h4>
                    <table><thead><tr><th>Nome do Técnico</th></tr></thead><tbody id="tabela-equipe-corpo"></tbody></table>
                </div>
            </div>
        </div>
    </div>

    <div class="grid-linha">
        <div class="bloco">
            <h3>CHAMADOS POR CATEGORIA</h3>
            <table><thead><tr><th>Categoria</th><th class="center">Fila</th><th class="center">Atendimento</th><th class="center">Encerrados</th></tr></thead><tbody id="tabela-categoria-corpo"></tbody></table>
        </div>
        <div class="bloco">
            <h3>CHAMADOS POR PRIORIDADE</h3>
            <table><thead><tr><th>Prioridade</th><th class="center">Fila</th><th class="center">Atendimento</th><th class="center">Encerrados</th></tr></thead><tbody id="tabela-prioridade-corpo"></tbody></table>
        </div>
    </div>

    <div class="grid-linha">
        <div class="bloco"><div class="chart-container"><canvas id="chart-categoria"></canvas></div></div>
        <div class="bloco"><div class="chart-container"><canvas id="chart-prioridade"></canvas></div></div>
    </div>

    <div class="grid-linha">
        <div class="bloco">
            <h3>DESEMPENHO POR TÉCNICO</h3>
            <table><thead><tr><th>Técnico</th><th class="center">Ativos</th><th class="center">Encerrados</th><th class="center">Tempo Médio</th><th class="center">SLA (%)</th><th class="center">Satisfação</th></tr></thead><tbody id="tabela-tecnicos-corpo"></tbody></table>
        </div>
        <div class="bloco">
            <h3>TEMPO MÉDIO DE ATENDIMENTO POR TÉCNICO (H)</h3>
            <div class="chart-container"><canvas id="chart-tecnicos"></canvas></div>
        </div>
    </div>

  <div class="grid-linha">
    <div class="bloco" style="grid-column: span 2;">
        <h3>EVOLUÇÃO DE CHAMADOS (ÚLTIMOS 6 MESES)</h3>
        <div class="chart-container" style="height: 300px;">
            <canvas id="chart-evolucao"></canvas>
        </div>
    </div>
  </div>

    <div class="grid-linha">
        <div class="bloco">
            <h3>DISTRIBUIÇÃO GERAL DE STATUS</h3>
            <table style="max-width: 300px;"><thead><tr><th>Status</th><th class="center">Total</th></tr></thead><tbody id="tabela-status-corpo"></tbody></table>
        </div>
        <div class="bloco center-content"><div class="chart-pizza"><canvas id="chart-status"></canvas></div></div>
    </div>

    <footer>
        <span>Atualizado automaticamente às <span id="ultima-atualizacao">--:--</span></span>
        <span>Sistema de Chamados • CGTI/SEAD</span>
    </footer>

    <script src="<?= \App\Core\Asset::url('/assets/js/chart.min.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/painel.js') ?>"></script>
</body>
</html>
