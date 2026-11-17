<div class="auth-heading">
    <h2>Criar nova conta</h2>
    <p>Preencha os dados para solicitar seu acesso.</p>
</div>

<?php if (!empty($erro)): ?>
    <div class="alert alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span><?= htmlspecialchars($erro) ?></span>
    </div>
<?php endif; ?>

<form method="POST" action="<?= \App\Core\Url::to('/cadastro') ?>" class="auth-form" id="form-cadastro">
    <?= \App\Core\Csrf::field() ?>

    <div class="field">
        <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <input type="text" name="nome" id="nome" placeholder=" " value="<?= htmlspecialchars($nome ?? '') ?>" required>
            <label for="nome" class="field-label">Nome completo</label>
        </div>
    </div>

    <div class="field">
        <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 5L2 7"></path></svg>
            <input type="email" name="email" id="email-cadastro" placeholder=" " value="<?= htmlspecialchars($email ?? '') ?>" required>
            <label for="email-cadastro" class="field-label">E-mail institucional</label>
        </div>
    </div>

    <div class="field">
        <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <input type="password" name="senha" id="senha-cadastro" placeholder=" " required oninput="checarForca(this.value)">
            <label for="senha-cadastro" class="field-label">Senha</label>
            <button type="button" class="field-toggle" onclick="toggleSenha('senha-cadastro', this)">
                <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
            </button>
        </div>
    </div>
    
    <div class="pwd-strength">
        <div class="pwd-strength-bar"><div class="pwd-strength-fill" id="pwd-fill"></div></div>
        <div class="pwd-strength-text" id="pwd-text">Digite sua senha</div>
    </div>

    <!-- SELECT SECRETARIA -->
    <div class="field">
        <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path><path d="M16 10h.01"></path><path d="M16 14h.01"></path><path d="M8 10h.01"></path><path d="M8 14h.01"></path></svg>
            
            <select name="secretaria_id" id="select-secretaria" required data-placeholder="Secretaria">
                <option value="" disabled <?= empty($secretaria_id) ? 'selected' : '' ?> hidden></option>
                <?php foreach ($secretarias as $sec): ?>
                    <option value="<?= $sec->id ?>" <?= (isset($secretaria_id) && $secretaria_id == $sec->id) ? 'selected' : '' ?>><?= htmlspecialchars($sec->nome) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- SELECT LOCAL -->
    <div class="field">
        <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            
            <select name="localizacao_id" id="select-local" required data-placeholder="Local de Origem">
                <option value="" disabled <?= empty($localizacao_id) ? 'selected' : '' ?> hidden></option>
                <?php foreach ($locais as $loc): ?>
                    <option value="<?= $loc->id ?>" data-secretaria="<?= $loc->secretaria_id ?>" <?= (isset($localizacao_id) && $localizacao_id == $loc->id) ? 'selected' : '' ?>><?= htmlspecialchars($loc->nome) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="field" style="margin-top: 16px;">
        <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 20v-4M12 20v-4M17 20v-4M7 8h.01M12 8h.01M17 8h.01"></path></svg>
            <input type="text" name="codigo_empresa" id="codigo" placeholder=" " value="<?= htmlspecialchars($codigo_empresa ?? '') ?>" required>
            <label for="codigo" class="field-label">Código da empresa</label>
        </div>
    </div>

    <button type="submit" class="btn-auth" id="btn-cadastro">
        <span class="btn-text">Criar minha conta</span>
    </button>
</form>

<div class="auth-links">
    Já tem uma conta? <a href="<?= \App\Core\Url::to('/login') ?>">Fazer login</a>
</div>

<script>
    function toggleSenha(id, btn) {
        const input = document.getElementById(id);
        const eye = btn.querySelector('.icon-eye');
        const eyeOff = btn.querySelector('.icon-eye-off');
        if (input.type === 'password') {
            input.type = 'text'; eye.style.display = 'none'; eyeOff.style.display = 'block';
        } else {
            input.type = 'password'; eye.style.display = 'block'; eyeOff.style.display = 'none';
        }
    }

    function checarForca(senha) {
        const fill = document.getElementById('pwd-fill');
        const txt = document.getElementById('pwd-text');
        let forca = 0;
        if (senha.length >= 6) forca++;
        if (senha.length >= 10) forca++;
        if (/[A-Z]/.test(senha) && /[a-z]/.test(senha)) forca++;
        if (/[0-9]/.test(senha)) forca++;
        if (/[^A-Za-z0-9]/.test(senha)) forca++;
        const niveis = [
            { w: '10%', cor: '#dc2626', txt: 'Muito curta' },
            { w: '30%', cor: '#dc2626', txt: 'Fraca' },
            { w: '55%', cor: '#f59e0b', txt: 'Razoável' },
            { w: '75%', cor: '#3b82f6', txt: 'Boa' },
            { w: '90%', cor: '#059669', txt: 'Forte' },
            { w: '100%', cor: '#059669', txt: 'Excelente' }
        ];
        const nivel = niveis[Math.min(forca, 5)];
        fill.style.width = senha.length === 0 ? '0' : nivel.w;
        fill.style.background = nivel.cor;
        txt.style.color = senha.length === 0 ? '#64748b' : nivel.cor;
        txt.innerText = senha.length === 0 ? 'Digite sua senha' : nivel.txt;
    }

    document.getElementById('form-cadastro').addEventListener('submit', function() {
        const btn = document.getElementById('btn-cadastro');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Criando conta...';
    });
</script>
