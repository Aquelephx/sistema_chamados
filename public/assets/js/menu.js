document.addEventListener('DOMContentLoaded', function() {
    const btnMenu = document.getElementById('btn-menu');
    const menu = document.getElementById('menu-suspenso');
    const fundo = document.getElementById('fundo-menu');

    function toggleMenu() {
        menu.classList.toggle('aberto');
        fundo.classList.toggle('aberto');
    }

    function fecharMenu() {
        menu.classList.remove('aberto');
        fundo.classList.remove('aberto');
    }

    if (btnMenu) {
        btnMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
        });
    }

    if (fundo) {
        fundo.addEventListener('click', fecharMenu);
    }

    // Fecha o menu se clicar em algum link dentro dele
    const links = menu.querySelectorAll('a');
    links.forEach(link => link.addEventListener('click', fecharMenu));
});
