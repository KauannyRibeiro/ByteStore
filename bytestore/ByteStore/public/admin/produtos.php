<?php
// =====================================================================
// PÁGINA: PRODUTOS (painel administrativo)
// =====================================================================
// O que faz: lista todos os produtos e permite EXCLUIR (Read + Delete do CRUD).
// Criar e editar acontecem na página produto_form.php.
// =====================================================================

require_once __DIR__ . '/../../app/includes/iniciar.php';
Auth::exigirAdmin();

$titulo   = 'Produtos';
$secao    = 'produtos';
$produtos = [];
$erro     = null;

// ---------------------------------------------------------------------
// EXCLUIR (o botão "Excluir" envia um formulário POST)
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    try {
        // Confere o token CSRF e o id antes de excluir.
        if (!Csrf::validar($_POST['csrf'] ?? null) || $id <= 0) {
            throw new InvalidArgumentException('Requisição inválida. Recarregue a página e tente novamente.');
        }
        if ((new ProdutoDAO())->excluir($id)) {
            Util::flash('sucesso', 'Produto excluído.');
        } else {
            Util::flash('erro', 'Produto não encontrado.');
        }
    } catch (Exception $e) {
        Util::flash('erro', $e->getMessage());
    }
    // Redireciona para a própria lista (evita reenviar o formulário ao apertar F5).
    Util::redirecionar(Util::url('admin/produtos.php'));
}

// ---------------------------------------------------------------------
// LISTAR
// ---------------------------------------------------------------------
try {
    $produtos = (new ProdutoDAO())->listarTodos();
} catch (Exception $e) {
    $erro = $e->getMessage();
}

include __DIR__ . '/../../app/includes/layout_admin_topo.php';
?>

<!-- ===== BARRA DE AÇÕES ===== -->
<div class="barra-acoes">
    <p><?= count($produtos) ?> produto(s) cadastrado(s)</p>
    <a class="btn" href="<?= Util::url('admin/produto_form.php') ?>">+ Novo produto</a>
</div>

<?php if ($erro): ?>
    <div class="alerta alerta-erro"><?= Util::e($erro) ?></div>
<?php endif; ?>

<?php if (empty($produtos) && !$erro): ?>
    <div class="vazio">
        <p>Nenhum produto cadastrado ainda.</p>
        <a class="btn" href="<?= Util::url('admin/produto_form.php') ?>">Cadastrar o primeiro produto</a>
    </div>

<?php elseif (!empty($produtos)): ?>
    <!-- ===== TABELA ===== -->
    <div class="tabela-rolagem">
        <table class="tabela">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Tipo</th>
                    <th>Categoria</th>
                    <th class="num">Preço</th>
                    <th class="num">Estoque</th>
                    <th class="col-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $p): ?>
                    <tr>
                        <td><?= (int) $p->getId() ?></td>
                        <td><strong><?= Util::e($p->getNome()) ?></strong></td>
                        <td><span class="selo selo-azul"><?= Util::e($p->getTipoRotulo()) ?></span></td>
                        <td><?= Util::e($p->getCategoria()->getNome()) ?></td>
                        <td class="num"><?= Util::moeda($p->getPreco()) ?></td>
                        <td class="num">
                            <?php if ($p->temEstoque()): ?>
                                <?= (int) $p->getEstoque() ?>
                            <?php else: ?>
                                <span class="selo selo-vermelho">Sem estoque</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-acoes">
                            <a class="btn btn-sec btn-pequeno" href="<?= Util::url('admin/produto_form.php?id=' . (int) $p->getId()) ?>">Editar</a>

                            <!-- Excluir: pede confirmação no navegador antes de enviar -->
                            <form method="post" action="<?= Util::url('admin/produtos.php') ?>"
                                  onsubmit="return confirm('Excluir este produto? Esta ação não pode ser desfeita.')">
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $p->getId() ?>">
                                <button class="btn btn-perigo btn-pequeno" type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../app/includes/layout_admin_rodape.php'; ?>
