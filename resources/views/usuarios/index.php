<style>@media(max-width:640px){.tabela.cartoes select{min-height:40px;font-size:0.9rem;padding:8px;width:100%;}.tabela.cartoes form{width:100%;}.tabela.cartoes td[data-label="Perfil"] form{gap:8px;}}</style>
<a href="<?= \App\Core\Url::to('/chamados') ?>" class="voltar">← Voltar</a>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 16px;">
    <h1>Gerenciar Usuários</h1>
    <span style="color: var(--texto-suave); font-size: 0.9rem;"><?= $total ?> usuário(s) cadastrado(s)</span>
</div>

<?php if ($sucesso = \App\Core\Session::getFlash('sucesso')): ?>
    <div class="aviso" style="background:var(--ok-bg); color:var(--ok-texto); border-color:var(--ok-borda); margin-bottom: 15px;"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>
<?php if ($erro = \App\Core\Session::getFlash('erro')): ?>
    <div class="aviso" style="background:var(--erro-bg); color:var(--erro-texto); border-color:var(--erro-borda); margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>
<?php if ($senha = \App\Core\Session::getFlash('senha_temporaria')): ?>
    <div class="aviso" style="background:var(--alerta-bg); border-color:var(--laranja); color:var(--alerta-texto); margin-bottom: 15px;"><?= $senha ?><br><small>Ela não aparece de novo. O usuário será obrigado a trocá-la no próximo login.</small></div>
<?php endif; ?>

<?php if (!empty($pendentes) && $pendentes > 0): ?>
    <div class="aviso" style="background:var(--info-bg); border-color:var(--info-borda); color:var(--azul-texto); margin-bottom: 15px;"><b><?= $pendentes ?> pedido(s) de nova senha.</b></div>
<?php endif; ?>

<div class="tabela cartoes">
    <table>
        <thead>
            <tr>
                <th>Nome / E-mail</th>
                <th>Perfil / Secretaria / Local</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr style="<?= !$u->ativo ? 'opacity: 0.6;' : '' ?>">
                    <td data-label="Nome">
                        <b><?= htmlspecialchars($u->nome) ?></b>
                        <?php if ($u->id === \App\Core\Auth::id()): ?><span style="font-size: 0.7rem; color: var(--azul-texto); font-weight: bold;">(você)</span><?php endif; ?>
                        <br><small><?= htmlspecialchars($u->email) ?></small>
                    </td>
                    
                    <td data-label="Perfil">
                        <?php if ($u->id === \App\Core\Auth::id() || !$u->ativo): ?>
                            <span style="text-transform: capitalize;"><?= htmlspecialchars($u->perfil) ?></span>
                        <?php else: ?>
                            <form method="POST" action="<?= \App\Core\Url::to('/usuarios/' . $u->id . '/atualizar') ?>" style="margin: 0; display: flex; flex-direction: column; gap: 5px;">
                                <?= \App\Core\Csrf::field() ?>
                                <select name="perfil" style="padding: 5px; font-size: 0.8rem;">
                                    <option value="solicitante" <?= $u->perfil === 'solicitante' ? 'selected' : '' ?>>Solicitante</option>
                                    <option value="tecnico" <?= $u->perfil === 'tecnico' ? 'selected' : '' ?>>Técnico</option>
                                    <option value="admin" <?= $u->perfil === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="painel" <?= $u->perfil === 'painel' ? 'selected' : '' ?>>Painel TV</option>
                                </select>

                                <select name="secretaria_id" style="padding: 5px; font-size: 0.8rem;">
                                    <option value="">Sem secretaria</option>
                                    <?php foreach ($secretarias as $sec): ?>
                                        <option value="<?= $sec->id ?>" <?= $u->secretaria_id == $sec->id ? 'selected' : '' ?>><?= htmlspecialchars($sec->nome) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <select name="localizacao_id" style="padding: 5px; font-size: 0.8rem;">
                                    <option value="">Sem local</option>
                                    <?php foreach ($locais as $loc): ?>
                                        <option value="<?= $loc->id ?>" <?= $u->localizacao_id == $loc->id ? 'selected' : '' ?>><?= htmlspecialchars($loc->nome) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit" style="padding: 4px 8px; font-size: 0.8rem; min-height: 30px;">Salvar</button>
                            </form>
                        <?php endif; ?>
                    </td>

                    <td data-label="Status">
                        <?php if ($u->pediu_senha_em && $u->ativo): ?>
                            <span class="badge s-em_andamento">Pediu senha</span>
                        <?php elseif ($u->ativo): ?>
                            <span class="badge s-resolvido">Ativo</span>
                        <?php else: ?>
                            <span class="badge s-fechado">Desativado</span>
                        <?php endif; ?>
                    </td>

                    <td data-label="Ações">
                        <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end;">
                            <?php if (!$u->ativo): ?>
                                <form method="POST" action="<?= \App\Core\Url::to('/usuarios/' . $u->id . '/status') ?>" style="margin: 0;">
                                    <?= \App\Core\Csrf::field() ?>
                                    <input type="hidden" name="acao" value="ativar">
                                    <button type="submit" style="padding: 4px 8px; font-size: 0.8rem; min-height: 30px; background: var(--verde);">Reativar</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= \App\Core\Url::to('/usuarios/' . $u->id . '/resetar-senha') ?>" style="margin: 0;">
                                    <?= \App\Core\Csrf::field() ?>
                                    <button type="submit" style="padding: 4px 8px; font-size: 0.8rem; min-height: 30px;" data-confirmar="Gerar nova senha para este usuário?">Redefinir</button>
                                </form>

                                <?php if ($u->id !== \App\Core\Auth::id()): ?>
                                    <form method="POST" action="<?= \App\Core\Url::to('/usuarios/' . $u->id . '/status') ?>" style="margin: 0;" data-confirmar="Desativar esta conta?">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="acao" value="desativar">
                                        <button type="submit" class="perigo" style="padding: 4px 8px; font-size: 0.8rem; min-height: 30px;">Desativar</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPaginas > 1): ?>
<div class="paginacao">
    <a href="?page=<?= $pagina - 1 ?>" class="<?= $pagina <= 1 ? 'desabilitado' : '' ?>">&laquo; Anterior</a>
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <?php if ($i == $pagina): ?><span class="atual"><?= $i ?></span>
        <?php else: ?><a href="?page=<?= $i ?>"><?= $i ?></a><?php endif; ?>
    <?php endfor; ?>
    <a href="?page=<?= $pagina + 1 ?>" class="<?= $pagina >= $totalPaginas ? 'desabilitado' : '' ?>">Próxima &raquo;</a>
</div>
<?php endif; ?>
