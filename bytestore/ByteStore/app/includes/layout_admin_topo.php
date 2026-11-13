<?php
// =====================================================================
// LAYOUT DO PAINEL ADMINISTRATIVO - parte de CIMA
// =====================================================================
// É PROPOSITALMENTE diferente do layout do site: menu lateral escuro,
// selo "ADMINISTRADOR" e outra cor, para você saber que está no painel.
//
// Variáveis esperadas (definidas na página antes do include):
//   $titulo     -> título da página (obrigatório)
//   $secao      -> qual item do menu fica marcado: 'painel', 'produtos' ou 'categorias'
//   $voltarUrl  -> (opcional) endereço do botão "Voltar"
//   $voltarTexto-> (opcional) texto do botão "Voltar"
// =====================================================================

// Itens do menu lateral:  chave => [texto, endereço]
$itensMenu = [
    'painel'     => ['Painel',     'admin/index.php'],
    'produtos'   => ['Produtos',   'admin/produtos.php'],
    'categorias' => ['Categorias', 'admin/categorias.php'],
];
$secao = $secao ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Util::e($titulo) ?> - Painel ByteStore</title>
    <link rel="stylesheet" href="<?= Util::url('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= Util::url('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= Util::url('assets/css/escuro.css') ?>">
    <script src="<?= Util::url('assets/js/tema.js') ?>"></script>
</head>
<body class="admin">

<div class="admin-layout">

    <!-- ===== MENU LATERAL ===== -->
    <aside class="admin-menu">
        <div class="admin-logo">
            Byte<span>Store</span>
            <small>PAINEL ADMINISTRATIVO</small>
        </div>

        <nav class="admin-nav">
            <?php foreach ($itensMenu as $chave => [$texto, $endereco]): ?>
                <a href="<?= Util::url($endereco) ?>" class="<?= $secao === $chave ? 'ativo' : '' ?>">
                    <?= Util::e($texto) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-menu-rodape">
            <!-- Abre a loja em OUTRA aba: o painel continua aberto -->
            <a class="btn btn-bloco btn-contorno" href="<?= Util::url('index.php') ?>" target="_blank" rel="noopener">
                Ver a loja (como cliente)
            </a>
            <!-- Sair é um formulário POST com token (não um link simples) -->
            <form method="post" action="<?= Util::url('admin/logout.php') ?>">
                <?= Csrf::campo() ?>
                <button type="submit" class="btn btn-bloco btn-perigo-claro">Sair do painel</button>
            </form>
        </div>
    </aside>

    <!-- ===== ÁREA PRINCIPAL ===== -->
    <div class="admin-principal">

        <!-- Barra superior: título da página + quem está logado -->
        <header class="admin-topo">
            <h1><?= Util::e($titulo) ?></h1>
            <div class="admin-usuario">
                <span class="selo-admin">ADMINISTRADOR</span>
                <?= Util::e(Auth::nome()) ?>
            </div>
        </header>

        <main class="admin-conteudo">
            <?php if (!empty($voltarUrl)): ?>
                <p><a class="btn btn-sec btn-pequeno" href="<?= Util::e($voltarUrl) ?>">&larr; <?= Util::e($voltarTexto ?? 'Voltar') ?></a></p>
            <?php endif; ?>

            <?php include __DIR__ . '/mensagens.php'; ?>
