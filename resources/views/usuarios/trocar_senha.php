<a href="<?= \App\Core\Url::to('/chamados') ?>" class="voltar">← Voltar</a>

<h1>Trocar Senha</h1>

<?php if ($senha = \App\Core\Session::getFlash('senha_temporaria')): ?>
    <div style="background: var(--alerta-bg); border: 2px solid var(--laranja); border-radius: 8px; padding: 12px; margin-bottom: 15px;">
        <?= $senha ?>
    </div>
<?php endif; ?>

<?php if (\App\Core\Auth::user()->trocar_senha === 1): ?>
    <div style="background: var(--alerta-bg); padding: 15px; border: 1px solid var(--laranja); margin-bottom: 15px;">
        <strong>Atenção:</strong> Você está usando uma senha temporária. Você precisa criar uma nova senha para continuar usando o sistema.
    </div>
<?php endif; ?>

<?php if (!empty($erro)): ?>
    <p style="color: var(--erro-texto);"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<form method="POST" action="<?= \App\Core\Url::to('/trocar-senha') ?>">
    <?= \App\Core\Csrf::field() ?>
    
    <div>
        <label>Senha Atual (ou temporária)</label>
        <input type="password" name="senha_atual" required>
    </div>
    
    <div>
        <label>Nova Senha</label>
        <input type="password" name="nova_senha" required>
    </div>

    <div>
        <label>Confirme a Nova Senha</label>
        <input type="password" name="confirmacao" required>
    </div>
    
    <button type="submit">Alterar Senha</button>
</form>
