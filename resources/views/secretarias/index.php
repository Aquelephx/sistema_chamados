<a href="<?= \App\Core\Url::to('/chamados') ?>" class="voltar">← Voltar</a>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 16px;">
    <h1>Gerenciar Secretarias</h1>
    <a href="<?= \App\Core\Url::to('/secretarias/novo') ?>" class="btn">+ Nova Secretaria</a>
</div>

<?php if ($sucesso = \App\Core\Session::getFlash('sucesso')): ?>
    <div class="aviso" style="background:var(--ok-bg); color:var(--ok-texto); border-color:var(--ok-borda); margin-bottom: 15px;"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>
<?php if ($erro = \App\Core\Session::getFlash('erro')): ?>
    <div class="aviso" style="background:var(--erro-bg); color:var(--erro-texto); border-color:var(--erro-borda); margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="tabela cartoes">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome da Secretaria</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($secretarias)): ?>
                <tr><td colspan="4" style="text-align:center;">Nenhuma secretaria cadastrada.</td></tr>
            <?php endif; ?>

            <?php foreach ($secretarias as $s): ?>
                <tr>
                    <td data-label="ID"><?= $s->id ?></td>
                    <td data-label="Nome"><?= htmlspecialchars($s->nome) ?></td>
                    <td data-label="Status">
                        <?php if ($s->ativo === 1): ?>
                            <span class="badge s-resolvido">Ativa</span>
                        <?php else: ?>
                            <span class="badge s-fechado">Inativa</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Ações">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <form method="POST" action="<?= \App\Core\Url::to('/secretarias/' . $s->id . '/status') ?>" style="margin: 0;">
                                <?= \App\Core\Csrf::field() ?>
                                <?php if ($s->ativo === 1): ?>
                                    <button type="submit" style="background: var(--laranja); padding: 5px 10px; font-size: 0.8rem; min-height: 30px;">Desativar</button>
                                <?php else: ?>
                                    <button type="submit" style="background: var(--verde); padding: 5px 10px; font-size: 0.8rem; min-height: 30px;">Ativar</button>
                                <?php endif; ?>
                            </form>

                            <form method="POST" action="<?= \App\Core\Url::to('/secretarias/' . $s->id . '/excluir') ?>" style="margin: 0;" data-confirmar="Tem certeza que deseja excluir esta secretaria?">
                                <?= \App\Core\Csrf::field() ?>
                                <button type="submit" class="perigo" style="padding: 5px 10px; font-size: 0.8rem; min-height: 30px;">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
