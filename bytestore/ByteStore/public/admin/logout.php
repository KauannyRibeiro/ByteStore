<?php
// =====================================================================
// PÁGINA: SAIR (logout)
// =====================================================================
// Só aceita POST com token CSRF válido. Quem clica em "Sair" (no painel
// ou na faixa escura da loja) está enviando um pequeno formulário.
// Depois de sair, o administrador volta para a tela de login.
// =====================================================================

require_once __DIR__ . '/../../app/includes/iniciar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::validar($_POST['csrf'] ?? null)) {
    Auth::logout(); // apaga a sessão e o cookie
}

Util::redirecionar(Util::url('admin/login.php'));
