<a href="<?= \App\Core\Url::to('/chamados') ?>" class="voltar">← Voltar</a>

<link rel="stylesheet" href="<?= \App\Core\Asset::url('/assets/css/cropper.min.css') ?>">

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
    <h1>Meu Perfil</h1>
</div>

<div class="cartao-pg">
    <?php if ($erro = \App\Core\Session::getFlash('erro')): ?>
        <div class="aviso" style="background:var(--erro-bg); color:var(--erro-texto); border-color:var(--erro-borda); margin-bottom: 15px;">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($sucesso = \App\Core\Session::getFlash('sucesso')): ?>
        <div class="aviso" style="background:var(--ok-bg); color:var(--ok-texto); border-color:var(--ok-borda); margin-bottom: 15px;">
            <?= htmlspecialchars($sucesso) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= \App\Core\Url::to('/meu-perfil') ?>" enctype="multipart/form-data">
        <?= \App\Core\Csrf::field() ?>
        
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px;">
            
            <!-- ÁREA DO AVATAR COM BOTÃO DE REMOVER -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <div class="avatar-preview" id="avatar-preview">
                    <?php if ($usuario->foto && file_exists(dirname(__DIR__, 3) . '/public/assets/avatars/' . $usuario->foto)): ?>
                        <img src="<?= \App\Core\Asset::url('/assets/avatars/' . $usuario->foto) ?>?v=<?= time() ?>" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="font-size: 2rem; font-weight: bold; color: var(--texto-suave);"><?= $usuario->inicial() ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if ($usuario->foto && file_exists(dirname(__DIR__, 3) . '/public/assets/avatars/' . $usuario->foto)): ?>
                    <!-- Formulário separado para remover a foto sem salvar o resto -->
                    <button type="submit" formaction="<?= \App\Core\Url::to('/meu-perfil/remover-foto') ?>" onclick="return confirm('Deseja realmente remover sua foto de perfil?');" style="background: none; border: none; color: #dc2626; font-size: 0.75rem; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">Remover foto</button>
                <?php endif; ?>
            </div>
            
            <div style="flex: 1;">
                <label class="rot" style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--azul-texto); text-transform: uppercase; margin-bottom: 6px;">Alterar Foto</label>
                <label for="input-foto-original" class="btn-escolher-foto">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                        <circle cx="12" cy="13" r="4"></circle>
                    </svg>
                    Escolher foto
                </label>
                <input type="file" id="input-foto-original" accept="image/jpeg,image/png,image/webp" style="display: none;">
                <input type="file" name="foto" id="input-foto-final" style="display: none;">
                <small style="color: var(--texto-suave); font-size: 0.75rem; display: block; margin-top: 8px;">JPG, PNG ou WebP. Você poderá ajustar o enquadramento antes de salvar.</small>
            </div>
        </div>

        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">Nome Completo</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($usuario->nome) ?>" required style="width: 100%;">
        </div>

        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario->email) ?>" readonly style="width: 100%; background: var(--campo-desab); cursor: not-allowed; color: var(--neutro-texto);">
            <small style="color: var(--texto-suave); font-size: 0.8rem;">Para alterar seu e-mail, contate o administrador.</small>
        </div>
      <div class="campo">
    <label class="rot">Secretaria</label>
    <input type="text" value="<?= htmlspecialchars($secretaria_nome) ?>" disabled style="background: var(--superficie-3); color: var(--texto-suave); cursor: not-allowed;">
    <small style="color: var(--texto-suave); font-size: 0.8rem;">Para alterar, solicite ao administrador.</small>
</div>

<div class="campo">
    <label class="rot">Local de Origem</label>
    <input type="text" value="<?= htmlspecialchars($local_nome) ?>" disabled style="background: var(--superficie-3); color: var(--texto-suave); cursor: not-allowed;">
    <small style="color: var(--texto-suave); font-size: 0.8rem;">Para alterar, solicite ao administrador.</small>
</div>

        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">Sobre você (Bio)</label>
            <textarea name="bio" rows="3" maxlength="500" placeholder="Ex: Técnico especialista em redes e servidores." style="width: 100%; padding: 10px; border: 1px solid var(--borda); border-radius: 8px;"><?= htmlspecialchars($usuario->bio ?? '') ?></textarea>
            <small style="color: var(--texto-suave); font-size: 0.8rem;">Uma breve descrição sobre sua atuação (máx. 500 caracteres).</small>
        </div>

        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">Perfil de Acesso</label>
            <input type="text" value="<?= htmlspecialchars($usuario->perfil) ?>" disabled style="width: 100%; background: var(--campo-desab); cursor: not-allowed;">
        </div>
        
        <button type="submit" style="width: 100%;">Salvar Alterações</button>
    </form>
</div>

<br>
<p><a href="<?= \App\Core\Url::to('/trocar-senha') ?>">Deseja alterar sua senha?</a></p>

<!-- MODAL DE CROP (estilo WhatsApp) -->
<div class="modal-crop" id="modal-crop" style="display: none;">
    <div class="modal-crop-content">
        <div class="modal-crop-header">
            <button type="button" id="crop-cancelar">Cancelar</button>
            <h3>Ajustar foto</h3>
            <button type="button" id="crop-salvar" class="primary">Salvar</button>
        </div>
        <div class="modal-crop-body">
            <img id="crop-imagem" alt="Preview">
        </div>
        <div class="modal-crop-footer">
            <small>Arraste para ajustar • Use dois dedos para ampliar</small>
        </div>
    </div>
</div>

<script src="<?= \App\Core\Asset::url('/assets/js/cropper.min.js') ?>"></script>
<script src="<?= \App\Core\Asset::url('/assets/js/perfil.js') ?>"></script>
