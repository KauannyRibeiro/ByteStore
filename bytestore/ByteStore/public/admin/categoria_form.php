<?php
// =====================================================================
// PÁGINA: FORMULÁRIO DE CATEGORIA (criar e editar)
// =====================================================================
//   categoria_form.php        -> cria uma categoria NOVA   (Create)
//   categoria_form.php?id=3   -> edita a categoria de id 3 (Update)
// =====================================================================

require_once __DIR__ . '/../../app/includes/iniciar.php';
Auth::exigirAdmin();

$secao     = 'categorias';
$erro      = null;
$id        = (int) ($_POST['id'] ?? $_GET['id'] ?? 0); // 0 = categoria nova
$editando  = $id > 0;
$nome      = '';
$descricao = '';

// =====================================================================
// SALVAR (formulário enviado)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim((string) ($_POST['nome'] ?? ''));
    $descricao = trim((string) ($_POST['descricao'] ?? ''));

    try {
        // 1) Token CSRF
        if (!Csrf::validar($_POST['csrf'] ?? null)) {
            throw new InvalidArgumentException('Sessão expirada. Recarregue a página e tente novamente.');
        }

        // 2) ENCAPSULAMENTO: a própria classe Categoria valida os dados nos setters
        //    (nome obrigatório, até 100 caracteres). Se inválido, lança exceção.
        $categoria = new Categoria($editando ? $id : null, $nome, $descricao);

        // 3) Grava no banco
        $dao = new CategoriaDAO();
        if ($editando) {
            $dao->atualizar($categoria);
            Util::flash('sucesso', 'Categoria atualizada.');
        } else {
            $dao->inserir($categoria);
            Util::flash('sucesso', 'Categoria cadastrada.');
        }
        Util::redirecionar(Util::url('admin/categorias.php'));

    } catch (Exception $e) {
        $erro = $e->getMessage();
    }

// =====================================================================
// CARREGAR os dados para EDITAR
// =====================================================================
} elseif ($editando) {
    try {
        $categoria = (new CategoriaDAO())->consultar($id);
        if ($categoria === null) {
            Util::flash('erro', 'Categoria não encontrada.');
            Util::redirecionar(Util::url('admin/categorias.php'));
        }
        $nome      = $categoria->getNome();
        $descricao = $categoria->getDescricao();
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$titulo      = $editando ? 'Editar categoria' : 'Nova categoria';
$voltarUrl   = Util::url('admin/categorias.php');
$voltarTexto = 'Voltar para a lista de categorias';

include __DIR__ . '/../../app/includes/layout_admin_topo.php';
?>

<?php if ($erro): ?>
    <div class="alerta alerta-erro"><?= Util::e($erro) ?></div>
<?php endif; ?>

<form class="caixa formulario" method="post" action="<?= Util::url('admin/categoria_form.php') ?>">
    <?= Csrf::campo() ?>
    <input type="hidden" name="id" value="<?= $editando ? $id : '' ?>">

    <fieldset>
        <legend>Dados da categoria</legend>

        <div class="campo">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" maxlength="100" value="<?= Util::e($nome) ?>" required>
        </div>

        <div class="campo">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" rows="4"><?= Util::e($descricao) ?></textarea>
        </div>
    </fieldset>

    <div class="acoes">
        <button class="btn" type="submit">Salvar categoria</button>
        <a class="btn btn-sec" href="<?= Util::url('admin/categorias.php') ?>">Cancelar</a>
    </div>
</form>

<?php include __DIR__ . '/../../app/includes/layout_admin_rodape.php'; ?>
