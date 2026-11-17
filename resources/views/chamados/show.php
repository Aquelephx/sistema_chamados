<?php 
    $urlVoltar = ($origem === 'relatorios') ? '/relatorios' : '/chamados';
    $textoVoltar = ($origem === 'relatorios') ? '← Voltar aos Relatórios' : '← Voltar';
    $queryVoltar = $origem !== 'chamados' ? '?voltar=' . $origem : '';
?>
<a href="<?= \App\Core\Url::to($urlVoltar) ?>" class="voltar" id="btn-voltar-lista"><?= $textoVoltar ?></a>

<?php if ($erro = \App\Core\Session::getFlash('erro')): ?>
    <div class="aviso" style="background:var(--erro-bg); color:var(--erro-texto); border-color:var(--erro-borda);"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>
<?php if ($sucesso = \App\Core\Session::getFlash('sucesso')): ?>
    <div class="aviso" style="background:var(--ok-bg); color:var(--ok-texto); border-color:var(--ok-borda);"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>

<section class="cartao-pg">
    <div class="cab-chamado">
        <span class="num">#<?= $chamado->id ?></span>
        <span class="badge s-<?= $chamado->status ?>"><?= $chamado->getStatusLabel() ?></span>
        <span class="badge p-<?= $chamado->prioridade ?>"><?= ucfirst($chamado->prioridade) ?></span>
        <?php $sla = $chamado->getSlaInfo(); ?>
        <span class="sla-badge sla-<?= $sla['cor'] ?>"><?= $sla['texto'] ?></span>
    </div>
    <h1><?= htmlspecialchars($chamado->titulo) ?></h1>
    <dl class="meta">
        <div>
    <dt>Solicitante</dt>
    <dd>
        <?php if (in_array($usuario->perfil, ['tecnico', 'admin'])): ?>
            <a href="#" onclick="abrirModalSolicitante(event)" style="text-decoration: underline; color: var(--link); font-weight: bold; cursor: pointer;">
                <?= htmlspecialchars($chamado->solicitante_nome) ?>
            </a>
        <?php else: ?>
            <?= htmlspecialchars($chamado->solicitante_nome) ?>
        <?php endif; ?>
    </dd>
        </div>
        <div><dt>Responsável</dt><dd><?= htmlspecialchars($chamado->responsavel_nome ?? 'Ninguém ainda') ?></dd></div>
        <div><dt>Local</dt><dd><?= htmlspecialchars($chamado->localizacao_nome ?? '—') ?></dd></div>
        <div><dt>Aberto em</dt><dd><?= date('d/m/Y H:i', strtotime($chamado->criado_em)) ?></dd></div>
    </dl>
    <p class="descricao"><?= nl2br(htmlspecialchars($chamado->descricao)) ?></p>

    <?php if ($chamado->status === 'aguardando' && $chamado->motivo_pausa): ?>
        <div class="aviso" style="margin-top: 16px; background:var(--roxo-bg); color:var(--roxo-texto); border:1px solid var(--roxo-borda);">
            <strong>⏸ Aguardando:</strong> <?= htmlspecialchars($chamado->motivo_pausa) ?>
        </div>
    <?php endif; ?>
</section>

<?php if ($relatorio && in_array($usuario->perfil, ['tecnico', 'admin'])): ?>
<section class="cartao-pg">
    <h2>Relatório de encerramento</h2>
    <dl class="meta">
        <div><dt>Tipo</dt><dd><?= htmlspecialchars($relatorio->tipo) ?></dd></div>
        <div><dt>Equipamento</dt><dd><?= htmlspecialchars($relatorio->equipamento ?? '—') ?></dd></div>
        <div><dt>Tempo gasto</dt><dd><?= $relatorio->tempo_min ?> min</dd></div>
        <div><dt>Técnico</dt><dd><?= htmlspecialchars($relatorio->tecnico) ?></dd></div>
    </dl>
    <p class="descricao"><?= nl2br(htmlspecialchars($relatorio->solucao)) ?></p>
</section>
<?php endif; ?>

<?php if (!empty($anexos) || ($chamado->status !== 'fechado' && count($anexos) < 5)): ?>
<section class="cartao-pg">
    <h2>Fotos</h2>
    <?php if (!empty($anexos)): ?>
        <div class="galeria">
            <?php foreach ($anexos as $a): ?>
                <figure>
                    <a href="<?= \App\Core\Url::to('/anexos/' . $a->id) ?>" target="_blank" rel="noopener">
                        <img src="<?= \App\Core\Url::to('/anexos/' . $a->id) ?>" loading="lazy" alt="Foto">
                    </a>
                    <figcaption>
                        <?= htmlspecialchars($a->usuario_nome) ?> · <?= date('d/m H:i', strtotime($a->criado_em)) ?>
                        <?php if ($a->usuario_id == $usuario->id || in_array($usuario->perfil, ['tecnico', 'admin'])): ?>
                            <form method="POST" action="<?= \App\Core\Url::to('/anexos/' . $a->id . '/excluir' . $queryVoltar) ?>" data-confirmar="Remover?" style="display:inline;">
                                <?= \App\Core\Csrf::field() ?>
                                <button type="submit" class="link">Remover</button>
                            </form>
                        <?php endif; ?>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($chamado->status !== 'fechado' && count($anexos) < 5): ?>
        <form method="POST" action="<?= \App\Core\Url::to('/chamados/' . $chamado->id . '/anexos' . $queryVoltar) ?>" class="form-foto" enctype="multipart/form-data" id="form-anexo">
            <?= \App\Core\Csrf::field() ?>
            <div class="foto-upload-box">
                <label for="anexo-camera" class="foto-btn primario">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    Tirar foto agora
                </label>
                <input type="file" id="anexo-camera" name="fotos[]" accept="image/*" capture="environment" style="display:none;" class="foto-input-anexo">
                
                <label for="anexo-galeria" class="foto-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    Escolher da galeria
                </label>
                <input type="file" id="anexo-galeria" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple style="display:none;" class="foto-input-anexo">
                
                <div class="foto-previews" id="anexo-previews"></div>
                <div class="foto-contador" id="anexo-contador"></div>
            </div>
            <button type="submit" class="foto-btn primario" id="btn-anexar" style="margin-top: 10px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Anexar foto(s)
            </button>
        </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if (in_array($usuario->perfil, ['tecnico', 'admin']) && $chamado->status !== 'fechado'): ?>
<section class="cartao-pg">
    <h2>Gerenciar chamado</h2>
    <form method="POST" action="<?= \App\Core\Url::to('/chamados/' . $chamado->id . '/atualizar' . $queryVoltar) ?>" id="form-gerenciar">
        <?= \App\Core\Csrf::field() ?>
        <div class="campos">
            <div class="campo">
                <span class="rot">Status</span>
                <select name="status" id="select-status">
                    <option value="aberto" <?= $chamado->status === 'aberto' ? 'selected' : '' ?>>Aberto</option>
                    <option value="em_andamento" <?= $chamado->status === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                    <option value="aguardando" <?= $chamado->status === 'aguardando' ? 'selected' : '' ?>>Aguardando</option>
                    <option value="resolvido" <?= $chamado->status === 'resolvido' ? 'selected' : '' ?>>Resolvido</option>
                </select>
            </div>
            <div class="campo">
                <span class="rot">Prioridade</span>
                <select name="prioridade">
                    <option value="baixa" <?= $chamado->prioridade === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                    <option value="media" <?= $chamado->prioridade === 'media' ? 'selected' : '' ?>>Média</option>
                    <option value="alta" <?= $chamado->prioridade === 'alta' ? 'selected' : '' ?>>Alta</option>
                    <option value="urgente" <?= $chamado->prioridade === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                </select>
            </div>
            <div class="campo">
    <span class="rot">Responsável</span>
    <select name="responsavel_ids[]" multiple data-placeholder="Selecione o(s) técnico(s)">
        <?php 
            // Pega os IDs atuais para marcar quem já está atribuído
            $idsAtuais = [];
            if (!empty($chamado->responsaveis_ids)) {
                $idsAtuais = explode(',', $chamado->responsaveis_ids);
            } elseif (!empty($chamado->responsavel_id)) {
                $idsAtuais = [$chamado->responsavel_id];
            }
        ?>
        <?php foreach ($tecnicos as $t): ?>
            <option value="<?= $t->id ?>" <?= in_array($t->id, $idsAtuais) ? 'selected' : '' ?>><?= htmlspecialchars($t->nome) ?></option>
        <?php endforeach; ?>
    </select>
            </div>
        </div>

        <!-- CAMPO DE MOTIVO: aparece só quando status = aguardando -->
        <div class="campo" id="campo-motivo-pausa" style="display: none; margin-top: 14px; padding: 12px; background: var(--roxo-bg); border-radius: 8px; border-left: 4px solid var(--roxo-texto);">
            <span class="rot" style="color: var(--roxo-texto);">Motivo da pausa *</span>
            <input type="text" name="motivo_pausa" id="input-motivo-pausa" placeholder="Ex: Aguardando HD do fornecedor, Aguardando autorização do chefe..." style="width: 100%;" maxlength="250" value="<?= htmlspecialchars($chamado->motivo_pausa ?? '') ?>">
            <small style="color: var(--roxo-texto); font-size: 0.75rem;">O SLA ficará pausado até o chamado ser retomado.</small>
        </div>

        <button type="submit" style="margin-top: 15px;">Salvar alterações</button>
    </form>
</section>
<section class="cartao-pg">
    <h2>Encerrar chamado</h2>
    <p class="dica">Para fechar, descreva o que foi feito.</p>
    <div class="barra-acoes"><a href="<?= \App\Core\Url::to('/chamados/' . $chamado->id . '/fechar' . $queryVoltar) ?>" style="text-decoration:none; flex: 1 1 100%;"><button type="button" class="perigo" style="width:100%;">Encerrar com Relatório</button></a></div>
</section>
<?php endif; ?>

<?php if ($chamado->solicitante_id === $usuario->id && !in_array($usuario->perfil, ['tecnico', 'admin'])): ?>
    <div class="barra-acoes" style="margin-top: 15px; margin-bottom: 15px;">
        <?php if (in_array($chamado->status, ['aberto', 'em_andamento', 'aguardando'])): ?>
            <form method="POST" action="<?= \App\Core\Url::to('/chamados/' . $chamado->id . '/fechar-solicitante' . $queryVoltar) ?>" style="flex: 1;"><?= \App\Core\Csrf::field() ?><button type="submit" class="perigo" style="width: 100%;">Fechar Chamado (Resolvido)</button></form>
        <?php endif; ?>
        <?php if (in_array($chamado->status, ['resolvido', 'fechado'])): ?>
            <form method="POST" action="<?= \App\Core\Url::to('/chamados/' . $chamado->id . '/reabrir' . $queryVoltar) ?>" style="flex: 1;"><?= \App\Core\Csrf::field() ?><button type="submit" style="width: 100%; background: var(--laranja);">Reabrir Chamado</button></form>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (in_array($usuario->perfil, ['tecnico', 'admin']) && in_array($chamado->status, ['resolvido', 'fechado'])): ?>
    <div class="barra-acoes"><form method="POST" action="<?= \App\Core\Url::to('/chamados/' . $chamado->id . '/reabrir' . $queryVoltar) ?>" style="flex: 1;"><?= \App\Core\Csrf::field() ?><button type="submit" style="width: 100%; background: var(--laranja);">Reabrir Chamado</button></form></div>
<?php endif; ?>

<section class="cartao-pg">
    <h2>Histórico</h2>
    <?php if (empty($historicos)): ?><p class="vazio">Nenhuma alteração ainda.</p><?php else: ?>
        <ul class="linha-tempo"><?php foreach ($historicos as $h): ?><li><small><?= date('d/m H:i', strtotime($h->criado_em)) ?></small><span><b><?= htmlspecialchars($h->usuario_nome ?? 'Sistema') ?></b>: <?= htmlspecialchars($h->texto) ?></span></li><?php endforeach; ?></ul>
    <?php endif; ?>
</section>
<!-- AVALIAÇÃO DE SATISFAÇÃO -->
<?php if ($chamado->solicitante_id === $usuario->id && in_array($chamado->status, ['resolvido', 'fechado'])): ?>
    <?php if ($chamado->avaliacao === null): ?>
        <section class="cartao-pg" style="border-top: 4px solid var(--laranja);">
            <h2>Avalie o Atendimento</h2>
            <p class="dica">O seu chamado foi encerrado. Que nota você dá para o atendimento que recebeu?</p>
            
            <form method="POST" action="<?= \App\Core\Url::to('/chamados/' . $chamado->id . '/avaliar' . $queryVoltar) ?>" id="form-avaliacao">
                <?= \App\Core\Csrf::field() ?>
                
                <div class="estrelas-avaliacao" style="display: flex; gap: 8px; font-size: 2.5rem; cursor: pointer; margin-bottom: 16px; color: var(--estrela-off);">
                    <span class="estrela" data-nota="1" onclick="setNota(1)">★</span>
                    <span class="estrela" data-nota="2" onclick="setNota(2)">★</span>
                    <span class="estrela" data-nota="3" onclick="setNota(3)">★</span>
                    <span class="estrela" data-nota="4" onclick="setNota(4)">★</span>
                    <span class="estrela" data-nota="5" onclick="setNota(5)">★</span>
                </div>
                <input type="hidden" name="avaliacao" id="input-nota" required>

                <div class="campo">
                    <label class="rot">Deixe um comentário (Opcional)</label>
                    <textarea name="comentario_avaliacao" rows="3" placeholder="O que você achou do atendimento?"></textarea>
                </div>

                <button type="submit" class="btn-auth" style="margin-top: 10px;">Enviar Avaliação</button>
            </form>
        </section>
        
        <script>
            function setNota(nota) {
                document.getElementById('input-nota').value = nota;
                const estrelas = document.querySelectorAll('.estrela');
                estrelas.forEach((el, index) => {
                    if (index < nota) {
                        el.style.color = '#f59e0b'; // Laranja
                    } else {
                        el.style.color = ''; // volta à cor do tema (--estrela-off)
                    }
                });
            }
        </script>
    <?php else: ?>
        <section class="cartao-pg" style="border-top: 4px solid var(--verde);">
            <h2>Sua Avaliação</h2>
            <div style="font-size: 2rem; color: #f59e0b; margin-bottom: 10px;">
                <?php for($i=1; $i<=5; $i++): ?>
                    <span><?= $i <= $chamado->avaliacao ? '★' : '☆' ?></span>
                <?php endfor; ?>
            </div>
            <?php if (!empty($chamado->comentario_avaliacao)): ?>
                <p class="descricao" style="background:var(--ok-leve-bg); border-left-color: var(--verde);">
                    "<?= nl2br(htmlspecialchars($chamado->comentario_avaliacao)) ?>"
                </p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
<?php endif; ?>
<section class="cartao-pg">
    <h2>Comentários</h2>
    <?php if (empty($comentarios)): ?><p class="vazio">Nenhum comentário ainda.</p><?php endif; ?>
    <?php foreach ($comentarios as $co): ?>
        <div class="comentario <?= $co->usuario_id == $usuario->id ? 'meu' : '' ?>">
            <div class="com-cab"><b><?= htmlspecialchars($co->usuario_nome) ?></b><small><?= date('d/m H:i', strtotime($co->criado_em)) ?></small></div>
            <div class="com-texto"><?= nl2br(htmlspecialchars($co->texto)) ?></div>
        </div>
    <?php endforeach; ?>
    <?php if ($chamado->status === 'fechado'): ?>
        <p class="vazio">Chamado fechado. Reabra para enviar novos comentários e fotos.</p>
    <?php else: ?>
        <form method="POST" action="<?= \App\Core\Url::to('/chamados/' . $chamado->id . '/comentarios' . $queryVoltar) ?>" class="form-comentar">
            <?= \App\Core\Csrf::field() ?>
            <textarea name="comentario" rows="3" placeholder="Escreva um comentário" required></textarea>
            <button type="submit">Comentar</button>
        </form>
    <?php endif; ?>
</section>
<!-- MODAL DO SOLICITANTE -->
<div id="modal-solicitante" class="modal-overlay" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Dados do Solicitante</h3>
        </div>
        <div class="modal-body">
            <p><b>Nome:</b> <?= htmlspecialchars($chamado->solicitante_nome) ?></p>
            <p><b>E-mail:</b> <?= htmlspecialchars($solicitante->email ?? 'Não informado') ?></p>
            <p><b>Secretaria:</b> <?= htmlspecialchars($solicitanteSec->nome ?? 'Não informada') ?></p>
            <p><b>Local:</b> <?= htmlspecialchars($solicitanteLoc->nome ?? 'Não informado') ?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancelar" onclick="fecharModalSolicitante()">Fechar</button>
        </div>
    </div>
</div>

<script>
    function abrirModalSolicitante(e) {
        if(e) e.preventDefault();
        document.getElementById('modal-solicitante').style.display = 'flex';
    }
    function fecharModalSolicitante() {
        document.getElementById('modal-solicitante').style.display = 'none';
    }
</script>

<script src="<?= \App\Core\Asset::url('/assets/js/form.js') ?>"></script>
