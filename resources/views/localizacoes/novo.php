<a href="<?= \App\Core\Url::to('/locais') ?>" class="voltar">← Voltar</a>

<div class="cartao-pg">
    <h1>Novo Local</h1>

    <?php if (!empty($erro)): ?>
        <div class="aviso" style="background:var(--erro-bg); color:var(--erro-texto); border-color:var(--erro-borda); margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= \App\Core\Url::to('/locais') ?>">
        <?= \App\Core\Csrf::field() ?>
        
        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">Secretaria</label>
            <select name="secretaria_id" required style="width: 100%;">
                <option value="">Selecione...</option>
                <?php foreach ($secretarias as $sec): ?>
                    <option value="<?= $sec->id ?>"><?= htmlspecialchars($sec->nome) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">Nome do Local</label>
            <input type="text" name="nome" required placeholder="Ex: Recepção, Sala de TI, Arquivo..." style="width: 100%;">
        </div>
        
        <button type="submit" style="width: 100%;">Cadastrar Local</button>
    </form>
</div>
