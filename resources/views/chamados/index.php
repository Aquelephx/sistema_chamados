<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 16px;">
    <h1>Chamados</h1>
    <a href="<?= \App\Core\Url::to('/chamados/novo') ?>" class="btn">+ Novo chamado</a>
</div>

<?php if (!empty($resumo)): ?>
<div class="resumo-tecnico">
    <div class="resumo-card resumo-laranja" onclick="window.location.href='?status=aberto'">
        <div class="resumo-valor"><?= $resumo['aguardando'] ?></div>
        <div class="resumo-titulo">Aguardando</div>
        <div class="resumo-sub">Sem responsável</div>
    </div>
    <div class="resumo-card resumo-azul" onclick="window.location.href='?apenas_meus=1&status=em_andamento'">
        <div class="resumo-valor"><?= $resumo['meus_atendimento'] ?></div>
        <div class="resumo-titulo">Em Atendimento</div>
        <div class="resumo-sub">Sob sua responsabilidade</div>
    </div>
    <div class="resumo-card resumo-vermelho" onclick="window.location.href='?status=aberto'">
        <div class="resumo-valor"><?= $resumo['vencidos'] ?></div>
        <div class="resumo-titulo">Vencidos</div>
        <div class="resumo-sub">SLA estourado</div>
    </div>
    <div class="resumo-card resumo-verde">
        <div class="resumo-valor"><?= $resumo['encerrados_semana'] ?></div>
        <div class="resumo-titulo">Fechados</div>
        <div class="resumo-sub">Últimos 7 dias</div>
    </div>
</div>
<?php endif; ?>

<form method="GET" action="<?= \App\Core\Url::to('/chamados') ?>" class="filtros cartao-pg">
    <input type="text" name="busca" placeholder="Buscar por título ou descrição..." value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>">
    
    <div class="filtros-linha">
        <select name="status">
            <option value="">Todos os status</option>
            <option value="aberto" <?= ($filtros['status'] ?? '') === 'aberto' ? 'selected' : '' ?>>Aberto</option>
            <option value="em_andamento" <?= ($filtros['status'] ?? '') === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
            <option value="aguardando" <?= ($filtros['status'] ?? '') === 'aguardando' ? 'selected' : '' ?>>Aguardando</option>
            <option value="resolvido" <?= ($filtros['status'] ?? '') === 'resolvido' ? 'selected' : '' ?>>Resolvido</option>
            <option value="fechado" <?= ($filtros['status'] ?? '') === 'fechado' ? 'selected' : '' ?>>Fechado</option>
        </select>

        <select name="prioridade">
            <option value="">Todas as prioridades</option>
            <option value="baixa" <?= ($filtros['prioridade'] ?? '') === 'baixa' ? 'selected' : '' ?>>Baixa</option>
            <option value="media" <?= ($filtros['prioridade'] ?? '') === 'media' ? 'selected' : '' ?>>Média</option>
            <option value="alta" <?= ($filtros['prioridade'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
            <option value="urgente" <?= ($filtros['prioridade'] ?? '') === 'urgente' ? 'selected' : '' ?>>Urgente</option>
        </select>

        <?php if (in_array($usuario->perfil, ['tecnico', 'admin'])): ?>
        <div style="display:flex; align-items:center; gap:8px; padding-top: 10px;">
            <input type="checkbox" name="apenas_meus" value="1" id="apenas_meus" <?= !empty($filtros['apenas_meus']) ? 'checked' : '' ?> style="width: auto; margin:0;">
            <label for="apenas_meus" style="font-size:0.9rem; cursor:pointer;">Apenas meus chamados</label>
        </div>
        <?php endif; ?>
    </div>

    <div class="filtros-botoes">
        <button type="submit">Filtrar</button>
        <a href="<?= \App\Core\Url::to('/chamados') ?>" class="limpar">Limpar</a>
    </div>
</form>

<p id="contador" style="margin-top:20px;"><?= $total ?> chamado(s) encontrados</p>

<div class="lista-chamados">
    <?php if (empty($chamados)): ?>
        <p class="vazio-lista">Nenhum chamado encontrado.</p>
    <?php endif; ?>

    <?php foreach ($chamados as $chamado): ?>
        <?php $sla = $chamado->getSlaInfo(); ?>
        <a href="<?= \App\Core\Url::to('/chamados/' . $chamado->id) ?>" class="chamado-item pb-<?= $chamado->prioridade ?>">
            <div class="ci-topo">
                <span class="ci-num">#<?= $chamado->id ?></span>
                <span class="badge s-<?= $chamado->status ?>"><?= $chamado->getStatusLabel() ?></span>
                <span class="badge p-<?= $chamado->prioridade ?>"><?= ucfirst($chamado->prioridade) ?></span>
                <span class="sla-badge sla-<?= $sla['cor'] ?>"><?= $sla['texto'] ?></span>
            </div>
            <div class="ci-titulo"><?= htmlspecialchars($chamado->titulo) ?></div>
            <div class="ci-meta">
                <div><small>Solicitante</small><strong><?= htmlspecialchars($chamado->solicitante_nome ?? '') ?></strong></div>
                <div><small>Responsável</small><strong><?= htmlspecialchars($chamado->responsaveis_nomes ?? '—') ?></strong></div>
                <div><small>Aberto em</small><strong><?= date('d/m/Y H:i', strtotime($chamado->criado_em)) ?></strong></div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($totalPaginas > 1): ?>
<div class="paginacao">
    <?php $queryParams = array_filter($filtros); ?>
    <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $pagina - 1])) ?>" class="<?= $pagina <= 1 ? 'desabilitado' : '' ?>">&laquo; Anterior</a>
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <?php if ($i == $pagina): ?>
            <span class="atual"><?= $i ?></span>
        <?php else: ?>
            <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $pagina + 1])) ?>" class="<?= $pagina >= $totalPaginas ? 'desabilitado' : '' ?>">Próxima &raquo;</a>
</div>
<?php endif; ?>
