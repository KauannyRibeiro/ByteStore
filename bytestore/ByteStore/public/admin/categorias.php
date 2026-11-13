<?php
// =====================================================================
// PÁGINA: CATEGORIAS (painel administrativo)
// =====================================================================
// O que faz: lista as categorias (com a quantidade de produtos de cada
// uma) e permite EXCLUIR. Criar e editar ficam em categoria_form.php.
// =====================================================================

require_once __DIR__ . '/../../app/includes/iniciar.php';
Auth::exigirAdmin();

$titulo     = 'Categorias';
$secao      = 'categorias';
$categorias = [];
$erro       = null;

// ---------------------------------------------------------------------
// EXCLUIR (formulário POST)
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    try {
        if (!Csrf::validar($_POST['csrf'] ?? null) || $id <= 0) {
            throw new InvalidArgumentException('Requisição inválida. Recarregue a página e tente novamente.');
        }
        if ((new CategoriaDAO())->excluir($id)) {
            Util::flash('sucesso', 'Categoria excluída.');
        } else {
            Util::flash('erro', 'Categoria não encontrada.');
        }
    } catch (Exception $e) {
        // Ex.: "Não é possível excluir: existem produtos nesta categoria."
        Util::flash('erro', $e->getMessage());
    }
    Util::redirecionar(Util::url('admin/categorias.php'));
}

// ---------------------------------------------------------------------
// LISTAR
// ---------------------------------------------------------------------
try {
    $categorias = (new CategoriaDAO())->listarTodos();
} catch (Exception $e) {
    $erro = $e->getMessage();
}

include __DIR__ . '/../../app/includes/layout_admin_topo.php';
?>

<div class="barra-acoes">
    <p><?= count($categorias) ?> categoria(s) cadastrada(s)</p>
    <a class="btn" href="<?= Util::url('admin/categoria_form.php') ?>">+ Nova categoria</a>
</div>

<?php if ($erro): ?>
    <div class="alerta alerta-erro"><?= Util::e($erro) ?></div>
<?php endif; ?>

<?php if (empty($categorias) && !$erro): ?>
    <div class="vazio">
        <p>Nenhuma categoria cadastrada ainda.</p>
        <a class="btn" href="<?= Util::url('admin/categoria_form.php') ?>">Cadastrar a primeira categoria</a>
    </div>

<?php elseif (!empty($categorias)): ?>
    <div class="tabela-rolagem">
        <table class="tabela">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th class="num">Produtos</th>
                    <th class="col-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td><?= (int) $c->getId() ?></td>
                        <td><strong><?= Util::e($c->getNome()) ?></strong></td>
                        <td><?= Util::e($c->getDescricao()) ?></td>
                        <td class="num"><?= (int) $c->contarProdutos() ?></td>
                        <td class="col-acoes">
                            <a class="btn btn-sec btn-pequeno" href="<?= Util::url('admin/categoria_form.php?id=' . (int) $c->getId()) ?>">Editar</a>

                            <form method="post" action="<?= Util::url('admin/categorias.php') ?>"
                                  onsubmit="return confirm('Excluir esta categoria?')">
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $c->getId() ?>">
                                <!-- Categoria com produtos não pode ser excluída (o banco também impede). -->
                                <button class="btn btn-perigo btn-pequeno" type="submit"
                                        <?= $c->contarProdutos() > 0 ? 'disabled title="Remova ou mude os produtos desta categoria antes."' : '' ?>>
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../app/includes/layout_admin_rodape.php'; ?>
