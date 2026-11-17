<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1d4a8f">
    <title>Sistema de Chamados</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= \App\Core\Asset::url('/assets/css/style.css') ?>">
    
    <!-- JS Principal (com a URL base passada via data attribute) -->
    <script data-base-url="<?= $_ENV['APP_BASE_PATH'] ?? '' ?>" src="<?= \App\Core\Asset::url('/assets/js/tema.js') ?>"></script>
</head>
<body>
    <header class="header-principal">
        <h1 class="header-titulo">CGTI - SEAD | Chamados</h1>
        <div style="display: flex; align-items: center; gap: 12px;">
            <?php $userLogado = \App\Core\Auth::user(); ?>
            <a href="<?= \App\Core\Url::to('/meu-perfil') ?>" class="avatar-link" title="Meu Perfil">
                <div class="avatar-header">
                    <?php if ($userLogado->foto): ?>
                        <img src="<?= \App\Core\Asset::url('/assets/avatars/' . $userLogado->foto) ?>" alt="Perfil">
                    <?php else: ?>
                        <span><?= $userLogado->inicial() ?></span>
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

    <!-- OUTROS SCRIPTS (Carregados no final para performance) -->
    <script src="<?= \App\Core\Asset::url('/assets/js/modal.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/foto-previews.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/menu.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/selects.js') ?>"></script>
    <script src="<?= \App\Core\Asset::url('/assets/js/historico.js') ?>"></script>
    <?php if (in_array(\App\Core\Auth::user()->perfil, ['tecnico', 'admin'])): ?>
        <script src="<?= \App\Core\Asset::url('/assets/js/notificacao.js') ?>"></script>
    <?php endif; ?>
</body>
</html>