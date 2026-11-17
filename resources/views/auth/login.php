<?php 
    $flashErro = \App\Core\Session::getFlash('erro'); 
    $msgErro = $erro ?? $flashErro; // Pega o erro do Controller ou da Sessão
?>

<div class="auth-heading">
    <h2>Entrar no sistema</h2>
    <p>Acesse com suas credenciais institucionais.</p>
</div>

<?php if (!empty($msgErro)): ?>
    <div class="alert alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span><?= htmlspecialchars($msgErro) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($sucesso)): ?>
    <div class="alert alert-success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span><?= htmlspecialchars($sucesso) ?></span>
    </div>
<?php endif; ?>

<form method="POST" action="<?= \App\Core\Url::to('/login') ?>" class="auth-form" id="form-login">
    <?= \App\Core\Csrf::field() ?>

    <div class="field">
        <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 5L2 7"></path></svg>
            <!-- CORREÇÃO: Adicionado o value para manter o e-mail digitado -->
            <input type="email" name="email" id="email" placeholder=" " value="<?= htmlspecialchars($email ?? '') ?>" required autocomplete="email">
            <label for="email" class="field-label">E-mail institucional</label>
        </div>
    </div>

    <div class="field">
        <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <!-- A senha fica em branco por padrão (segurança) -->
            <input type="password" name="senha" id="senha-login" placeholder=" " required>
            <label for="senha-login" class="field-label">Senha</label>
            <button type="button" class="field-toggle" onclick="toggleSenha('senha-login', this)">
                <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
            </button>
        </div>
    </div>

    <label class="lembrar">
        <input type="checkbox" name="lembrar" value="1" id="lembrar">
        <span>
            Manter conectado por 30 dias
            <small>Use só em computador ou celular seu. Em máquina compartilhada, não marque.</small>
        </span>
    </label>

    <button type="submit" class="btn-auth" id="btn-login">
        <span class="btn-text">Entrar</span>
    </button>
</form>

<div class="auth-links">
    <a href="<?= \App\Core\Url::to('/cadastro') ?>">Criar conta</a>
    <span class="divider">|</span>
    <a href="<?= \App\Core\Url::to('/esqueci') ?>">Esqueci a senha</a>
</div>

<script>
    function toggleSenha(id, btn) {
        const input = document.getElementById(id);
        const eye = btn.querySelector('.icon-eye');
        const eyeOff = btn.querySelector('.icon-eye-off');
        if (input.type === 'password') {
            input.type = 'text';
            eye.style.display = 'none';
            eyeOff.style.display = 'block';
        } else {
            input.type = 'password';
            eye.style.display = 'block';
            eyeOff.style.display = 'none';
        }
    }
    document.getElementById('form-login').addEventListener('submit', function() {
        const btn = document.getElementById('btn-login');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Entrando...';
    });
</script>
