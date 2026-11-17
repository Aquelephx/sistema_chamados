<a href="<?= \App\Core\Url::to('/chamados') ?>" class="voltar">← Voltar</a>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 16px;">
    <h1>Relatórios de Atendimento</h1>
    <a href="<?= \App\Core\Url::to('/relatorios/exportar') . '?' . http_build_query($filtros) ?>" class="btn">Exportar para Excel (CSV)</a>
</div>

<form method="GET" action="<?= \App\Core\Url::to('/relatorios') ?>" class="filtros cartao-pg">
    <div class="filtros-linha">
        <div>
            <label class="rot" style="display:block; margin-bottom:4px; font-size:0.8rem; color:var(--neutro-texto);">Data Início</label>
            <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
        </div>
        <div>
            <label class="rot" style="display:block; margin-bottom:4px; font-size:0.8rem; color:var(--neutro-texto);">Data Fim</label>
            <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
        </div>
        <div>
            <label class="rot" style="display:block; margin-bottom:4px; font-size:0.8rem; color:var(--neutro-texto);">Tipo</label>
            <select name="tipo" style="width:100%;">
                <option value="">Todos os tipos</option>
                <?php foreach ($tipos as $valor => $nome): ?>
                    <option value="<?= $valor ?>" <?= $filtros['tipo'] === $valor ? 'selected' : '' ?>><?= $nome ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="rot" style="display:block; margin-bottom:4px; font-size:0.8rem; color:var(--neutro-texto);">Técnico</label>
            <select name="tecnico_id" style="width:100%;">
                <option value="0">Todos os técnicos</option>
                <?php foreach ($tecnicos as $t): ?>
                    <option value="<?= $t->id ?>" <?= $filtros['tecnico_id'] === $t->id ? 'selected' : '' ?>><?= htmlspecialchars($t->nome) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="filtros-botoes">
        <button type="submit">Filtrar</button>
        <a href="<?= \App\Core\Url::to('/relatorios') ?>" class="limpar">Limpar</a>
    </div>
</form>

<p id="contador" style="margin-top:20px;"><?= count($relatorios) ?> relatório(s) encontrado(s)</p>

<div class="tabela cartoes">
    <table>
        <thead>
            <tr>
                <th>Chamado</th>
                <th>Fechado em</th>
                <th>Solicitante</th>
                <th>Técnico</th>
                <th>Tipo</th>
                <th>Tempo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($relatorios)): ?>
                <tr><td colspan="7" style="text-align:center;">Nenhum relatório encontrado com esses filtros.</td></tr>
            <?php endif; ?>

            <?php foreach ($relatorios as $r): ?>
                <tr>
                    <td data-label="Chamado">
                        <strong>#<?= $r['chamado_id'] ?></strong> - <?= htmlspecialchars($r['chamado_titulo']) ?>
                    </td>
                    <td data-label="Fechado em"><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></td>
                    <td data-label="Solicitante"><?= htmlspecialchars($r['solicitante_nome']) ?></td>
                    <td data-label="Técnico"><?= htmlspecialchars($r['tecnico_nome']) ?></td>
                    <td data-label="Tipo"><?= htmlspecialchars($r['tipo']) ?></td>
                    <td data-label="Tempo"><?= $r['tempo_min'] ?> min</td>
                    <td data-label="Ações">
                        <a href="<?= \App\Core\Url::to('/chamados/' . $r['chamado_id'] . '?voltar=relatorios') ?>" class="btn" style="padding:4px 8px; font-size:0.8rem;">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
