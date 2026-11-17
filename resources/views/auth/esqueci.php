<div class="auth-heading">
    <h2>Recuperar senha</h2>
    <p>Enviaremos uma solicitação ao administrador.</p>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        <span><?= htmlspecialchars($msg) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($erro)): ?>
    <div class="alert alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span><?= htmlspecialchars($erro) ?></span>
    </div>
<?php endif; ?>

<?php if (empty($msg)): ?>
    <form method="POST" action="<?= \App\Core\Url::to('/esqueci') ?>" class="auth-form" id="form-esqueci">
        <?= \App\Core\Csrf::field() ?>

        <div class="field">
            <div class="field-input-wrap">
                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 5L2 7"></path></svg>
                <input type="email" name="email" id="email-esqueci" placeholder=" " required>
                <label for="email-esqueci" class="field-label">Seu e-mail de login</label>
            </div>
        </div>

        <button type="submit" class="btn-auth" id="btn-esqueci">
            <span class="btn-text">Solicitar nova senha</span>
        </button>
    </form>
<?php endif; ?>

<div class="auth-links">
    <a href="<?= \App\Core\Url::to('/login') ?>">← Voltar ao login</a>
</div>

<script>
    const form = document.getElementById('form-esqueci');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = document.getElementById('btn-esqueci');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Enviando...';
        });
    }
</script>
