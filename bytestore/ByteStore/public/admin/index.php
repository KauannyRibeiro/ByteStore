<?php
// =====================================================================
// PÁGINA: PAINEL (início da área administrativa)
// =====================================================================
// O que faz: mostra um resumo (quantos produtos e categorias existem) e
// atalhos para as tarefas mais comuns.
// =====================================================================

require_once __DIR__ . '/../../app/includes/iniciar.php';
Auth::exigirAdmin(); // quem não é admin volta para o login

// ---------- Variáveis do layout ----------
$titulo = 'Painel';
$secao  = 'painel'; // marca o item "Painel" no menu lateral

// ---------- Dados do resumo ----------
$totalProdutos   = 0;
$totalCategorias = 0;
$erro            = null;

try {
    $totalProdutos   = (new ProdutoDAO())->contar();
    $totalCategorias = count((new CategoriaDAO())->listarTodos());
} catch (Exception $e) {
    $erro = $e->getMessage();
}

include __DIR__ . '/../../app/includes/layout_admin_topo.php';
?>

<?php if ($erro): ?>
    <div class="alerta alerta-erro"><?= Util::e($erro) ?></div>
<?php endif; ?>

<!-- ===== BOAS-VINDAS ===== -->
<section class="caixa admin-boasvindas">
    <h2>Olá, <?= Util::e(Auth::nome()) ?>!</h2>
    <p>
        Você está na <strong>área administrativa</strong>. Aqui você cadastra e edita os produtos e as
        categorias da loja. Para ver a loja como o cliente vê, use o botão
        <strong>"Ver a loja"</strong> no menu lateral.
    </p>
</section>

<!-- ===== RESUMO ===== -->
<section class="cards-resumo">
    <div class="caixa card-resumo">
        <span class="card-numero"><?= (int) $totalProdutos ?></span>
        <span class="card-rotulo">produtos cadastrados</span>
        <a class="btn btn-sec btn-pequeno" href="<?= Util::url('admin/produtos.php') ?>">Gerenciar produtos</a>
    </div>

    <div class="caixa card-resumo">
        <span class="card-numero"><?= (int) $totalCategorias ?></span>
        <span class="card-rotulo">categorias</span>
        <a class="btn btn-sec btn-pequeno" href="<?= Util::url('admin/categorias.php') ?>">Gerenciar categorias</a>
    </div>
</section>

<!-- ===== ATALHOS ===== -->
<section class="caixa">
    <h2>Atalhos</h2>
    <div class="acoes">
        <a class="btn" href="<?= Util::url('admin/produto_form.php') ?>">+ Novo produto</a>
        <a class="btn" href="<?= Util::url('admin/categoria_form.php') ?>">+ Nova categoria</a>
    </div>
</section>

<?php include __DIR__ . '/../../app/includes/layout_admin_rodape.php'; ?>
