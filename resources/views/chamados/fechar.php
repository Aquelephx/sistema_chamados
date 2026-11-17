<p><a href="<?= \App\Core\Url::to('/chamados/' . $chamado->id . (!empty($voltar) ? '?voltar=' . $voltar : '')) ?>" class="voltar">← Voltar ao chamado</a></p>

<?php if ($erro = \App\Core\Session::getFlash('erro')): ?>
    <div class="aviso" style="background:var(--erro-bg); color:var(--erro-texto); border-color:var(--erro-borda);">
        <?= htmlspecialchars($erro) ?>
    </div>
<?php endif; ?>

<div class="cartao-pg">
    <h1>Encerrar Chamado #<?= $chamado->id ?></h1>
    <p class="dica" style="margin-bottom: 20px;">
        Para fechar o chamado, preencha o relatório abaixo. Essas informações geram os indicadores do painel e o histórico do equipamento.
    </p>

    <form method="POST" action="<?= \App\Core\Url::to('/chamados/' . $chamado->id . '/fechar' . (!empty($voltar) ? '?voltar=' . $voltar : '')) ?>">
        <?= \App\Core\Csrf::field() ?>
        
        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">Tipo de Problema *</label>
            <select name="tipo" id="select-tipo" required style="width:100%;">
                <option value="">Selecione a categoria...</option>
                <?php foreach ($tipos as $valor => $nome): ?>
                    <option value="<?= $valor ?>"><?= $nome ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="div-tipo-outro" style="display: none; margin-bottom: 16px; padding-left: 15px; border-left: 3px solid var(--link);">
            <label class="rot">Especifique o problema *</label>
            <input type="text" name="tipo_outro" id="input-tipo-outro" style="width:100%;" placeholder="Descreva qual é o tipo de problema...">
        </div>
        
        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">Equipamento</label>
            <select name="equipamento" id="select-equipamento" style="width:100%;">
                <option value="Nenhum">Nenhum / Não se aplica</option>
            </select>
        </div>

        <div class="campo" style="margin-bottom: 16px;">
            <label class="rot">Tempo Gasto (minutos) *</label>
            <input type="number" name="tempo_min" value="<?= $tempoCalculado ?>" required style="width:100%;" min="0">
            <small style="color: var(--texto-suave); font-size: 0.8rem;">O tempo já vem calculado. Ajuste se achar necessário.</small>
        </div>

        <!-- CAMPO PÚBLICO (O Solicitante vai ver) -->
        <div class="campo" style="margin-bottom: 20px; padding: 16px; background: var(--ok-leve-bg); border: 1px solid var(--ok-leve-borda); border-radius: 8px;">
            <label class="rot" style="color: var(--ok-leve-texto);">Mensagem para o Solicitante (Público) *</label>
            <p style="font-size: 0.8rem; color: var(--ok-leve-texto); margin: 4px 0 8px;">
                <b>Atenção:</b> Este texto será visível para o usuário que abriu o chamado. Explique de forma simples o que foi feito.
            </p>
            <textarea name="solucao_publica" rows="3" required placeholder="Ex: O Microsoft Office foi reinstalado e ativado. Por favor, reinicie o computador para concluir."></textarea>
        </div>

        <!-- CAMPO INTERNO (Só o TI vai ver) -->
        <div class="campo" style="margin-bottom: 20px; padding: 16px; background: var(--amarelo-leve-bg); border: 1px solid var(--amarelo-leve-borda); border-radius: 8px;">
            <label class="rot" style="color: var(--amarelo-leve-texto);">Notas Internas de TI (Privado) *</label>
            <p style="font-size: 0.8rem; color: var(--amarelo-leve-texto); margin: 4px 0 8px;">
                <b>Atenção:</b> Apenas técnicos e administradores verão este campo. Use para senhas, IPs ou detalhes técnicos.
            </p>
            <textarea name="solucao" rows="5" required placeholder="Descreva detalhadamente o que foi feito (Ex: IP configurado, senha do roteador alterada...)"></textarea>
        </div>
        
        <button type="submit" class="perigo" style="width: 100%;">Fechar com Relatório</button>
    </form>
</div>

<script>
    const todosEquipamentos = <?= json_encode(array_map(fn($e) => $e->nome, $equipamentos)) ?>;
    const mapaEquipamentos = <?= json_encode($mapaEquipamentos) ?>;
</script>
<!-- O selects.js deve ser carregado AQUI se você ainda não colocou no layout -->
<script src="<?= \App\Core\Asset::url('/assets/js/selects.js') ?>"></script>
<script src="<?= \App\Core\Asset::url('/assets/js/chamado-fechar.js') ?>"></script>
