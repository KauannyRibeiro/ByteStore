<?php
// =====================================================================
// MENSAGENS (flash) - usado pelos dois layouts (site e painel)
// =====================================================================
// Mostra as mensagens guardadas na sessão por Util::flash() e as apaga
// (cada mensagem aparece uma única vez).
// Tipos: 'sucesso' (verde), 'erro' (vermelho) e 'info' (azul).
foreach (Util::pegarFlash() as $m): ?>
    <div class="alerta alerta-<?= Util::e($m['tipo']) ?>" role="alert">
        <span><?= Util::e($m['mensagem']) ?></span>
        <?php if (!empty($m['link_texto']) && !empty($m['link_url'])): ?>
            <a class="alerta-link" href="<?= Util::e($m['link_url']) ?>"><?= Util::e($m['link_texto']) ?></a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
