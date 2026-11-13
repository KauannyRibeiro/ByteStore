// Modo escuro: lembra a escolha; se nunca escolheu, segue o tema do sistema.
(function () {
    var html = document.documentElement;
    var salvo = null;
    try { salvo = localStorage.getItem('tema'); } catch (e) {}
    var escuro = salvo ? salvo === 'escuro'
                       : window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches;
    if (escuro) html.setAttribute('data-tema', 'escuro');

    document.addEventListener('DOMContentLoaded', function () {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'btn-tema'; b.setAttribute('aria-label', 'Alternar tema');
        function atualizar() { b.textContent = html.getAttribute('data-tema') === 'escuro' ? '☀️' : '🌙'; }
        b.onclick = function () {
            var vaiEscurecer = html.getAttribute('data-tema') !== 'escuro';
            if (vaiEscurecer) html.setAttribute('data-tema', 'escuro'); else html.removeAttribute('data-tema');
            try { localStorage.setItem('tema', vaiEscurecer ? 'escuro' : 'claro'); } catch (e) {}
            atualizar();
        };
        atualizar();
        document.body.appendChild(b);
    });
})();
