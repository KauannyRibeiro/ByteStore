<?php
// =====================================================================
// LAYOUT DO SITE (a loja que o CLIENTE vê) - parte de CIMA
// =====================================================================
// Variável esperada (definida na página antes do include): $titulo
// Tudo que vem depois deste arquivo, na página, aparece dentro do <main>.
// O arquivo layout_site_rodape.php fecha o que abrimos aqui.
// =====================================================================

// Total de unidades no carrinho (soma as quantidades guardadas na sessão).
$qtdCarrinho = array_sum($_SESSION['carrinho'] ?? []);

// Se o administrador estiver logado, $admin tem os dados dele; senão fica null.
$admin = Auth::estaLogado() ? Auth::usuario() : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Util::e($titulo) ?> - ByteStore</title>
    <link rel="stylesheet" href="<?= Util::url('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= Util::url('assets/css/site.css') ?>">
    <link rel="stylesheet" href="<?= Util::url('assets/css/escuro.css') ?>">
    <script src="<?= Util::url('assets/js/tema.js') ?>"></script>
</head>
<body class="site">

<?php if ($admin): ?>
    <!-- ===== FAIXA ESCURA: só aparece quando o ADMINISTRADOR está logado =====
         Serve para você nunca confundir "vendo a loja como cliente" com "no painel". -->
    <div class="faixa-admin">
        <span>
            Você está logado como <strong>ADMINISTRADOR</strong> (<?= Util::e($admin['nome']) ?>)
            e está vendo a loja como um cliente veria.
        </span>
        <span class="faixa-admin-acoes">
            <a class="btn btn-pequeno btn-claro" href="<?= Util::url('admin/index.php') ?>">Voltar ao painel</a>
            <form method="post" action="<?= Util::url('admin/logout.php') ?>">
                <?= Csrf::campo() ?>
                <button type="submit" class="btn btn-pequeno btn-contorno">Sair</button>
            </form>
        </span>
    </div>
<?php endif; ?>

<!-- ===== CABEÇALHO DA LOJA ===== -->
<header class="site-topo">
    <div class="site-topo-interno">
        <a class="logo" href="<?= Util::url('index.php') ?>">Byte<span>Store</span></a>

        <nav class="site-menu">
            <a href="<?= Util::url('index.php') ?>">Vitrine</a>
            <a class="menu-carrinho" href="<?= Util::url('carrinho.php') ?>">
                Carrinho
                <span class="contador"><?= (int) $qtdCarrinho ?></span>
            </a>
            <?php if (!$admin): ?>
                <a class="menu-admin" href="<?= Util::url('admin/login.php') ?>">Área do administrador</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- ===== CONTEÚDO DA PÁGINA ===== -->
<main class="site-conteudo">
<?php include __DIR__ . '/mensagens.php'; ?>
