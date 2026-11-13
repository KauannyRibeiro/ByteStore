<?php
// =====================================================================
// PÁGINA: FORMULÁRIO DE PRODUTO (criar e editar)
// =====================================================================
// O que faz:
//   produto_form.php          -> cria um produto NOVO       (Create)
//   produto_form.php?id=5     -> edita o produto de id 5    (Update)
//
// O formulário envia para ESTA MESMA página (POST), que valida, grava e
// redireciona para a lista.
// =====================================================================

require_once __DIR__ . '/../../app/includes/iniciar.php';
Auth::exigirAdmin();

$secao      = 'produtos';
$erro       = null;
$categorias = [];

// O id vem escondido no formulário (POST) ou na URL (GET). 0 = produto novo.
$id       = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$editando = $id > 0;

// Valores que aparecem nos campos. Se der erro de validação, o que a pessoa
// digitou continua na tela (ela não precisa digitar tudo de novo).
$d = [
    'id' => '', 'tipo' => 'hardware', 'nome' => '', 'descricao' => '', 'preco' => '', 'estoque' => '0',
    'categoria_id' => '', 'socket' => '', 'tdp' => '0', 'dpi' => '0', 'conexao' => '',
];

// Categorias para o campo "Categoria".
try {
    $categorias = (new CategoriaDAO())->listarTodos();
} catch (Exception $e) {
    $erro = $e->getMessage();
}

// =====================================================================
// SALVAR (formulário enviado)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Copia os campos enviados para $d (trim tira espaços sobrando).
    foreach ($d as $campo => $padrao) {
        $d[$campo] = trim((string) ($_POST[$campo] ?? $padrao));
    }
    $d['id'] = $editando ? (string) $id : '';

    try {
        // 1) Token CSRF
        if (!Csrf::validar($_POST['csrf'] ?? null)) {
            throw new InvalidArgumentException('Sessão expirada. Recarregue a página e tente novamente.');
        }

        $dao = new ProdutoDAO();

        // 2) Na edição o TIPO não muda: vale o que está gravado no banco.
        if ($editando) {
            $existente = $dao->consultar($id);
            if ($existente === null) {
                throw new InvalidArgumentException('Produto não encontrado.');
            }
            $d['tipo'] = $existente->getTipo();
        }

        // 3) A fábrica cria um Hardware ou um Periferico.
        //    TODA a validação (preço > 0, estoque >= 0...) acontece nos setters das classes.
        //    Se algo for inválido, uma exceção é lançada e cai no catch abaixo.
        $produto = ProdutoFactory::criar($d);

        // 4) Grava no banco.
        if ($editando) {
            $dao->atualizar($produto);
            Util::flash('sucesso', 'Produto atualizado.');
        } else {
            $dao->inserir($produto);
            Util::flash('sucesso', 'Produto cadastrado.');
        }
        Util::redirecionar(Util::url('admin/produtos.php'));

    } catch (Exception $e) {
        $erro = $e->getMessage(); // mostra o erro e mantém o formulário preenchido
    }

// =====================================================================
// CARREGAR os dados para EDITAR (abrir a página com ?id=...)
// =====================================================================
} elseif ($editando) {
    try {
        $produto = (new ProdutoDAO())->consultar($id);
        if ($produto === null) {
            Util::flash('erro', 'Produto não encontrado.');
            Util::redirecionar(Util::url('admin/produtos.php'));
        }

        $d['id']           = (string) $produto->getId();
        $d['tipo']         = $produto->getTipo();
        $d['nome']         = $produto->getNome();
        $d['descricao']    = $produto->getDescricao();
        $d['preco']        = number_format($produto->getPreco(), 2, '.', ''); // 1399.90
        $d['estoque']      = (string) $produto->getEstoque();
        $d['categoria_id'] = (string) $produto->getCategoriaId();

        // POLIMORFISMO: Hardware devolve socket/TDP; Periférico devolve DPI/conexão.
        foreach ($produto->getAtributosEspecificos() as $campo => $valor) {
            if ($valor !== null) {
                $d[$campo] = (string) $valor;
            }
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// ---------- Variáveis do layout ----------
$titulo      = $editando ? 'Editar produto' : 'Novo produto';
$voltarUrl   = Util::url('admin/produtos.php');
$voltarTexto = 'Voltar para a lista de produtos';

include __DIR__ . '/../../app/includes/layout_admin_topo.php';
?>

<?php if ($erro): ?>
    <div class="alerta alerta-erro"><?= Util::e($erro) ?></div>
<?php endif; ?>

<?php if (empty($categorias)): ?>
    <!-- Sem categoria não dá para cadastrar produto: avisa e oferece o atalho. -->
    <div class="alerta alerta-info">
        Cadastre ao menos uma categoria antes de criar produtos.
        <a class="alerta-link" href="<?= Util::url('admin/categoria_form.php') ?>">Nova categoria</a>
    </div>

<?php else: ?>
<form class="caixa formulario" method="post" action="<?= Util::url('admin/produto_form.php') ?>">
    <?= Csrf::campo() ?>
    <input type="hidden" name="id" value="<?= Util::e($d['id']) ?>">

    <!-- ===== BLOCO 1: informações básicas ===== -->
    <fieldset>
        <legend>Informações básicas</legend>

        <div class="campo">
            <label for="tipo">Tipo de produto</label>
            <!-- Na edição o tipo fica travado (disabled) e vai num campo escondido. -->
            <select id="tipo" name="tipo" <?= $editando ? 'disabled' : '' ?>>
                <option value="hardware" <?= $d['tipo'] === 'hardware' ? 'selected' : '' ?>>Hardware (socket / TDP)</option>
                <option value="periferico" <?= $d['tipo'] === 'periferico' ? 'selected' : '' ?>>Periférico (DPI / conexão)</option>
            </select>
            <?php if ($editando): ?>
                <input type="hidden" name="tipo" value="<?= Util::e($d['tipo']) ?>">
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" maxlength="150" value="<?= Util::e($d['nome']) ?>" required>
        </div>

        <div class="campo">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" rows="4"><?= Util::e($d['descricao']) ?></textarea>
        </div>

        <div class="campo">
            <label for="categoria_id">Categoria</label>
            <select id="categoria_id" name="categoria_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int) $c->getId() ?>" <?= (string) $c->getId() === $d['categoria_id'] ? 'selected' : '' ?>>
                        <?= Util::e($c->getNome()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>

    <!-- ===== BLOCO 2: preço e estoque ===== -->
    <fieldset>
        <legend>Preço e estoque</legend>
        <div class="linha-dupla">
            <div class="campo">
                <label for="preco">Preço (R$)</label>
                <input type="number" id="preco" name="preco" step="0.01" min="0.01" value="<?= Util::e($d['preco']) ?>" required>
            </div>
            <div class="campo">
                <label for="estoque">Estoque (unidades)</label>
                <input type="number" id="estoque" name="estoque" min="0" value="<?= Util::e($d['estoque']) ?>" required>
            </div>
        </div>
    </fieldset>

    <!-- ===== BLOCO 3: especificações (muda conforme o tipo escolhido) ===== -->
    <fieldset id="campos-hardware">
        <legend>Especificações de Hardware</legend>
        <div class="linha-dupla">
            <div class="campo">
                <label for="socket">Socket / compatibilidade</label>
                <input type="text" id="socket" name="socket" maxlength="50" value="<?= Util::e($d['socket']) ?>"
                       placeholder="Ex.: AM4, LGA1700, PCIe 4.0">
            </div>
            <div class="campo">
                <label for="tdp">TDP em watts (0 se não se aplica)</label>
                <input type="number" id="tdp" name="tdp" min="0" max="2000" value="<?= Util::e($d['tdp']) ?>">
            </div>
        </div>
    </fieldset>

    <fieldset id="campos-periferico">
        <legend>Especificações de Periférico</legend>
        <div class="linha-dupla">
            <div class="campo">
                <label for="dpi">DPI (0 se não se aplica)</label>
                <input type="number" id="dpi" name="dpi" min="0" max="100000" value="<?= Util::e($d['dpi']) ?>">
            </div>
            <div class="campo">
                <label for="conexao">Conexão</label>
                <input type="text" id="conexao" name="conexao" maxlength="50" value="<?= Util::e($d['conexao']) ?>"
                       placeholder="Ex.: USB, Bluetooth">
            </div>
        </div>
    </fieldset>

    <!-- ===== BOTÕES ===== -->
    <div class="acoes">
        <button class="btn" type="submit">Salvar produto</button>
        <a class="btn btn-sec" href="<?= Util::url('admin/produtos.php') ?>">Cancelar</a>
    </div>
</form>

<script>
    // Mostra só os campos do tipo escolhido. É só para facilitar a tela:
    // a validação de verdade acontece no servidor (nas classes PHP).
    const tipo = document.getElementById('tipo');
    function alternar() {
        document.getElementById('campos-hardware').style.display   = tipo.value === 'hardware'   ? '' : 'none';
        document.getElementById('campos-periferico').style.display = tipo.value === 'periferico' ? '' : 'none';
    }
    tipo.addEventListener('change', alternar);
    alternar();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../../app/includes/layout_admin_rodape.php'; ?>
