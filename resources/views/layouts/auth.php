<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        // Aplica o tema ANTES da página ser desenhada (evita piscar branco no modo escuro).
        // Sem escolha salva, segue o tema do sistema do usuário.
        (function () {
            try {
                var t = localStorage.getItem('tema');
                var escuro = t === 'dark' || (t === null && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (escuro) document.documentElement.classList.add('dark-mode');
            } catch (e) {}
        })();
    </script>
    <meta name="theme-color" content="#1d4a8f">
    <title>CGTI - SEAD | Sistema de Chamados</title>
    <link rel="stylesheet" href="<?= \App\Core\Asset::url('/assets/css/auth.css') ?>">
</head>
<body>
    <button type="button" class="btn-tema-auth" id="btn-tema" title="Alternar tema" aria-label="Alternar tema">🌙</button>
    <div class="auth-wrapper">
        <!-- PAINEL ESQUERDO (Branding azul) -->
        <aside class="auth-brand">
            <div class="auth-brand-content">
                <h1>Gestão de chamados, do jeito certo.</h1>
                <p>Plataforma oficial de suporte técnico do CGTI/SEAD para abertura, atendimento e acompanhamento de chamados.</p>
                <ul class="auth-features">
                    <li>
                        <span class="icon-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        Abertura e acompanhamento em tempo real
                    </li>
                    <li>
                        <span class="icon-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        SLA monitorado automaticamente
                    </li>
                    <li>
                        <span class="icon-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        Relatórios e indicadores em tempo real
                    </li>
                </ul>
            </div>
            <div class="auth-brand-footer">© <?= date('Y') ?> CGTI - SEAD. Todos os direitos reservados.</div>
        </aside>

        <!-- PAINEL DIREITO (Fundo azul-acinzentado + cartão branco) -->
        <main class="auth-panel">
            <div class="auth-panel-inner">
                <?= $content ?>
            </div>
        </main>
    </div>
    <script src="<?= \App\Core\Asset::url('/assets/js/tema.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/selects.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/cadastro.js') ?>"></script>
</body>
</html>
