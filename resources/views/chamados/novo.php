<a href="<?= \App\Core\Url::to('/chamados') ?>" class="voltar">← Voltar</a>

<div class="cartao-pg" style="max-width: 600px; margin: 0 auto;">
    <h1>Abrir Novo Chamado</h1>
    <p class="dica" style="margin-bottom: 24px;">Responda as etapas abaixo para registrar seu chamado.</p>

    <div class="wizard-progress">
        <div class="wizard-step-indicator active" data-step="1"><div class="circle">1</div><span>Local</span></div>
        <div class="wizard-line"></div>
        <div class="wizard-step-indicator" data-step="2"><div class="circle">2</div><span>Problema</span></div>
        <div class="wizard-line"></div>
        <div class="wizard-step-indicator" data-step="3"><div class="circle">3</div><span>Fotos</span></div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="aviso" style="background:var(--erro-bg); color:var(--erro-texto); border-color:var(--erro-borda); margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= \App\Core\Url::to('/chamados') ?>" enctype="multipart/form-data" id="form-wizard">
        <?= \App\Core\Csrf::field() ?>

        <div class="wizard-content active" data-step="1">
            
            <!-- SELECT SECRETARIA -->
            <div class="campo" style="margin-bottom: 16px;">
                <label class="rot">Secretaria *</label>
                <select name="secretaria_id" id="select-secretaria" required style="width:100%;">
                    <option value="">Selecione a secretaria...</option>
                    <?php foreach ($secretarias as $sec): ?>
                        <option value="<?= $sec->id ?>"><?= htmlspecialchars($sec->nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- SELECT LOCAL -->
            <div class="campo" style="margin-bottom: 16px;">
                <label class="rot">Onde você está? *</label>
                <select name="localizacao_id" id="select-local" required style="width:100%;">
                    <option value="">Selecione o local...</option>
                    <?php foreach ($locais as $local): ?>
                        <option value="<?= $local->id ?>" data-secretaria="<?= $local->secretaria_id ?>"><?= htmlspecialchars($local->nome) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--texto-suave); font-size: 0.8rem; display: block; margin-top: 8px;">Selecione a sala ou setor onde o equipamento está.</small>
                
                <div id="erro-local" class="aviso" style="display: none; background:var(--erro-bg); color:var(--erro-texto); border-color:var(--erro-borda); margin-top: 12px; padding: 10px; font-size: 0.85rem; border-radius: 8px; border-left: 4px solid var(--erro-texto);">
                    Por favor, preencha a secretaria e o local antes de avançar.
                </div>
            </div>
        </div>

        <div class="wizard-content" data-step="2">
            <div class="campo" style="margin-bottom: 16px;">
                <label class="rot">Título do Problema *</label>
                <input type="text" name="titulo" placeholder="Ex: Computador não liga" style="width:100%;">
            </div>
            <div class="campo" style="margin-bottom: 16px;">
                <label class="rot">Descrição detalhada *</label>
                <textarea name="descricao" rows="4" placeholder="O que aconteceu? Aparece alguma mensagem de erro?"></textarea>
            </div>
        </div>

        <div class="wizard-content" data-step="3">
            <div class="campo" style="margin-bottom: 16px;">
                <label class="rot">Fotos (Opcional)</label>
                <small style="color: var(--texto-suave); font-size: 0.8rem; display: block; margin-bottom: 10px;">Máximo 5 fotos.</small>
                <div class="foto-upload-box">
                    <label for="foto-camera" class="foto-btn primario">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        Tirar foto agora
                    </label>
                    <input type="file" id="foto-camera" name="fotos[]" accept="image/*" capture="environment" style="display:none;" class="foto-input">

                    <label for="foto-galeria" class="foto-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        Escolher da galeria
                    </label>
                    <input type="file" id="foto-galeria" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple style="display:none;" class="foto-input">

                    <div class="foto-previews" id="foto-previews"></div>
                    <div class="foto-contador" id="foto-contador"></div>
                </div>
            </div>
        </div>

        <div class="wizard-actions">
            <button type="button" id="btn-voltar-wizard" class="sec" style="display: none;">Voltar</button>
            <button type="button" id="btn-proximo-wizard" class="btn-auth" style="margin: 0; flex: 1;">Próximo Passo →</button>
            <button type="submit" id="btn-enviar-wizard" class="btn-auth" style="margin: 0; flex: 1; display: none;">Abrir Chamado</button>
        </div>
    </form>
</div>

<script src="<?= \App\Core\Asset::url('/assets/js/foto-previews.js') ?>"></script>

<script>
    const steps = document.querySelectorAll('.wizard-content');
    const indicators = document.querySelectorAll('.wizard-step-indicator');
    const btnVoltar = document.getElementById('btn-voltar-wizard');
    const btnProximo = document.getElementById('btn-proximo-wizard');
    const btnEnviar = document.getElementById('btn-enviar-wizard');
    
    const selectSecretaria = document.getElementById('select-secretaria');
    const selectLocal = document.getElementById('select-local');
    const erroLocal = document.getElementById('erro-local');
    
    let currentStep = 1;

    // === LÓGICA DE FILTRO SECRETARIA -> LOCAL ===
    if (selectSecretaria && selectLocal) {
        const filtrarLocais = () => {
            const secretariaId = selectSecretaria.value;
            
            // Esconde/Mostra as opções
            Array.from(selectLocal.options).forEach(opt => {
                if (opt.value === "") return;
                if (opt.dataset.secretaria === secretariaId) {
                    opt.hidden = false; opt.disabled = false;
                } else {
                    opt.hidden = true; opt.disabled = true;
                }
            });

            // Limpa o local se pertencer a outra secretaria
            if (selectLocal.value) {
                const opcaoAtual = selectLocal.options[selectLocal.selectedIndex];
                if (opcaoAtual && opcaoAtual.dataset.secretaria !== secretariaId) {
                    selectLocal.value = "";
                }
            }
            
            // Manda o selects.js reconstruir a lista visual
            selectLocal.dispatchEvent(new Event('update-options'));
        };
        
        selectSecretaria.addEventListener('change', filtrarLocais);
        setTimeout(filtrarLocais, 100); // Executa ao carregar para esconder os locais
    }

    function updateWizard() {
        steps.forEach(s => s.classList.remove('active'));
        document.querySelector(`.wizard-content[data-step="${currentStep}"]`).classList.add('active');
        indicators.forEach(ind => {
            const stepNum = parseInt(ind.dataset.step);
            ind.classList.remove('active', 'completed');
            if (stepNum === currentStep) ind.classList.add('active');
            if (stepNum < currentStep) ind.classList.add('completed');
        });
        if (currentStep === 1) {
            btnVoltar.style.display = 'none'; btnProximo.style.display = 'block'; btnEnviar.style.display = 'none';
        } else if (currentStep === 3) {
            btnVoltar.style.display = 'block'; btnProximo.style.display = 'none'; btnEnviar.style.display = 'block';
        } else {
            btnVoltar.style.display = 'block'; btnProximo.style.display = 'block'; btnEnviar.style.display = 'none';
        }
    }

    btnProximo.addEventListener('click', () => {
        const currentStepContent = document.querySelector(`.wizard-content[data-step="${currentStep}"]`);
        const currentInputs = currentStepContent.querySelectorAll('input:not([type=file]), select, textarea');
        let valid = true;

        currentInputs.forEach(input => {
            if (!input.checkValidity()) {
                valid = false;
                
                // Se for o select customizado
                if (input.tagName === 'SELECT') {
                    erroLocal.style.display = 'block'; 
                    const wrapper = input.closest('.sel');
                    if (wrapper) {
                        const btn = wrapper.querySelector('.sel-botao');
                        if (btn) btn.style.borderColor = 'var(--erro-texto)'; 
                    }
                } else {
                    input.reportValidity(); 
                }
            }
        });

        if (valid && currentStep < 3) { 
            currentStep++; 
            updateWizard(); 
        }
    });

    // Limpa o erro quando a secretaria ou o local são preenchidos
    if (selectSecretaria) {
        selectSecretaria.addEventListener('change', () => {
            if (selectSecretaria.value !== "" && selectLocal.value !== "") {
                erroLocal.style.display = 'none';
                const wrappers = document.querySelectorAll('.sel .sel-botao');
                wrappers.forEach(btn => btn.style.borderColor = '');
            }
        });
    }

    if (selectLocal) {
        selectLocal.addEventListener('change', () => {
            if (selectSecretaria.value !== "" && selectLocal.value !== "") {
                erroLocal.style.display = 'none';
                const wrappers = document.querySelectorAll('.sel .sel-botao');
                wrappers.forEach(btn => btn.style.borderColor = '');
            }
        });
    }

    btnVoltar.addEventListener('click', () => { if (currentStep > 1) { currentStep--; updateWizard(); } });
    
    document.getElementById('form-wizard').addEventListener('submit', function() {
        btnEnviar.disabled = true; btnEnviar.innerHTML = 'Abrindo...'; btnEnviar.style.opacity = '0.7';
    });

    initFotoPreviews({ inputSelector: '.foto-input', containerId: 'foto-previews', contadorId: 'foto-contador', maxFotos: 5 });
</script>
