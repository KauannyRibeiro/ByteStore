<?php
// =====================================================================
// ARQUIVO DE INICIALIZAÇÃO
// =====================================================================
// TODA página do sistema começa com:
//     require_once __DIR__ . '/../app/includes/iniciar.php';
//
// Ele faz 4 coisas:
//   1) Confere se o config/config.php existe
//   2) Carrega todas as classes (um arquivo por classe)
//   3) Ajusta o fuso horário e a exibição de erros
//   4) Inicia a sessão (onde ficam o login e o carrinho)
// =====================================================================

// ---------- 1) Arquivo de configuração ----------
// __DIR__ = pasta deste arquivo. Se config.php não existir, mostramos
// uma mensagem clara (em vez de um erro confuso) e paramos.
require_once __DIR__ . '/../classes/Config.php';
if (!Config::existe()) {
    http_response_code(500); // 500 = erro do servidor
    exit('<h1>Falta o arquivo de configuração</h1>'
       . '<p>Copie <code>config/config.exemplo.php</code> para <code>config/config.php</code> '
       . 'e ajuste os dados do MySQL. Veja o passo a passo no README.</p>');
}

// ---------- 2) Carrega as classes ----------
// A ORDEM importa: primeiro o que as outras classes usam (interface, trait),
// depois as classes-pai, depois as filhas.
require_once __DIR__ . '/../classes/RepositorioInterface.php'; // contrato dos DAOs
require_once __DIR__ . '/../classes/LogavelTrait.php';         // trait de log
require_once __DIR__ . '/../classes/Database.php';             // conexão PDO
require_once __DIR__ . '/../classes/BaseDAO.php';              // classe-pai dos DAOs
require_once __DIR__ . '/../classes/Util.php';                 // funções de apoio
require_once __DIR__ . '/../classes/Csrf.php';                 // token de segurança dos formulários
require_once __DIR__ . '/../classes/Categoria.php';
require_once __DIR__ . '/../classes/Produto.php';              // abstrata: vem ANTES de Hardware e Periferico
require_once __DIR__ . '/../classes/Hardware.php';
require_once __DIR__ . '/../classes/Periferico.php';
require_once __DIR__ . '/../classes/ProdutoFactory.php';
require_once __DIR__ . '/../classes/ItemCarrinho.php';
require_once __DIR__ . '/../classes/Carrinho.php';
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../classes/CategoriaDAO.php';
require_once __DIR__ . '/../classes/ProdutoDAO.php';
require_once __DIR__ . '/../classes/UsuarioDAO.php';
require_once __DIR__ . '/../classes/Auth.php';                 // login/logout

// ---------- 3) Fuso horário e erros ----------
date_default_timezone_set('America/Sao_Paulo');

if (Config::get('ambiente', 'desenvolvimento') === 'producao') {
    ini_set('display_errors', '0'); // site no ar: nunca mostrar erros técnicos ao visitante
} else {
    ini_set('display_errors', '1'); // estudando: mostra os erros na tela
    error_reporting(E_ALL);
}

// ---------- 4) Sessão ----------
// PHP_SAPI === 'cli' significa "rodando pelo terminal" (sem navegador, sem sessão).
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,  // JavaScript não consegue ler o cookie de sessão (protege contra roubo via XSS)
        'samesite' => 'Lax', // o cookie não vai em pedidos vindos de outros sites (proteção extra contra CSRF)
    ]);
    session_start();
}
