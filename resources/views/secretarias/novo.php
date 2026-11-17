<a href="<?= \App\Core\Url::to('/secretarias') ?>" class="voltar">← Voltar</a>

<div class="cartao-pg">
    <h1>Nova Secretaria</h1>

    <?php if (!empty($erro)): ?>
        <div class="aviso" style="background:var(--erro-bg); color:var(--erro-texto); border-color:var(--erro-borda); margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= \App\Core\Url::to('/secretarias') ?>">
        <?= \App\Core\Csrf::field() ?>
        
        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">Nome da Secretaria</label>
            <input type="text" name="nome" required placeholder="Ex: SEAD, SEFIN, SEMED..." style="width: 100%;">
        </div>
        
        <button type="submit" style="width: 100%;">Cadastrar</button>
    </form>
</div>
