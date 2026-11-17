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
    <title>Sistema de Chamados</title>
    <link rel="stylesheet" href="<?= \App\Core\Asset::url('/assets/css/style.css') ?>">
    <script>window.APP_BASE_URL = "<?= $_ENV['APP_BASE_PATH'] ?? '' ?>";</script>
</head>
<body>
    <header class="header-principal">
        <h1 class="header-titulo">CGTI - SEAD | Chamados</h1>
        <div style="display: flex; align-items: center; gap: 12px;">
            <?php $userLogado = \App\Core\Auth::user(); ?>
            <a href="<?= \App\Core\Url::to('/meu-perfil') ?>" class="avatar-link" title="Meu Perfil">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.2); overflow: hidden; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.6); cursor: pointer;">
                    <?php if ($userLogado->foto): ?>
                        <img src="<?= \App\Core\Asset::url('/assets/avatars/' . $userLogado->foto) ?>" alt="Perfil" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="color: white; font-weight: bold; font-size: 1rem;"><?= $userLogado->inicial() ?></span>
                    <?php endif; ?>
                </div>
            </a>
                <button class="btn-tema" id="btn-tema" title="Alternar tema" aria-label="Alternar tema">🌙</button>
    <button class="btn-menu" id="btn-menu">☰</button>
    </div>

        <nav class="menu-suspenso" id="menu-suspenso">
            <a href="<?= \App\Core\Url::to('/chamados') ?>">Início</a>
            <?php if (in_array(\App\Core\Auth::user()->perfil, ['admin', 'tecnico'])): ?>
                <a href="<?= \App\Core\Url::to('/relatorios') ?>">Relatórios</a>
                <a href="<?= \App\Core\Url::to('/painel') ?>" target="_blank">Painel TV</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::isAdmin()): ?>
                <a href="<?= \App\Core\Url::to('/locais') ?>">Locais</a>
                <a href="<?= \App\Core\Url::to('/equipamentos') ?>">Equipamentos</a>
                <a href="<?= \App\Core\Url::to('/usuarios') ?>">Usuários</a>
            <?php endif; ?>
            <a href="<?= \App\Core\Url::to('/meu-perfil') ?>">Meu Perfil</a>
            <a href="<?= \App\Core\Url::to('/trocar-senha') ?>">Trocar Senha</a>
            <a href="<?= \App\Core\Url::to('/logout') ?>" class="sair">Sair</a>
        </nav>
    </header>

    <div class="fundo-menu" id="fundo-menu"></div>

    <main>
        <?= $content ?>
    </main>

    <!-- MODAL DE CONFIRMAÇÃO -->
    <div id="modal-confirmacao" class="modal-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modal-titulo">Atenção</h3>
            </div>
            <div class="modal-body">
                <p id="modal-mensagem">Tem certeza?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" id="modal-btn-cancelar">Cancelar</button>
                <button type="button" class="btn-perigo" id="modal-btn-confirmar">Sim, tenho certeza</button>
            </div>
        </div>
    </div>

    <script src="<?= \App\Core\Asset::url('/assets/js/modal.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/foto-previews.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/menu.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/tema.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/selects.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/historico.js') ?>"></script>
    <?php if (in_array(\App\Core\Auth::user()->perfil, ['tecnico', 'admin'])): ?>
        <script src="<?= \App\Core\Asset::url('/assets/js/notificacao.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
