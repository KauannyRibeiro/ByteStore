<?php
// =====================================================================
// PÁGINA: DETALHES DE UM PRODUTO   (endereço: produto.php?id=NUMERO)
// =====================================================================
// O que faz: mostra as especificações técnicas, garantia e frete do
// produto e permite escolher a quantidade e adicionar ao carrinho.
// =====================================================================

require_once __DIR__ . '/../app/includes/iniciar.php';

// ---------- 1) Lembrar onde o cliente está ----------
// loja_url  = última página da loja visitada (usada pelo "Continuar comprando" do carrinho)
// lista_url = última LISTA de produtos (usada pelo botão "Voltar" desta página)
$_SESSION['loja_url'] = Util::urlAtual();
$voltarLista = Util::urlSegura((string) ($_SESSION['lista_url'] ?? ''), Util::url('index.php'));

// ---------- 2) Busca o produto ----------
$produto = null;
$erro    = null;
$id      = (int) ($_GET['id'] ?? 0); // (int) garante que é número

try {
    if ($id > 0) {
        $produto = (new ProdutoDAO())->consultar($id); // null se não existir
    }
} catch (Exception $e) {
    $erro = $e->getMessage();
}

$titulo = $produto !== null ? $produto->getNome() : 'Produto não encontrado';

// Quantidade de unidades deste produto que já estão no carrinho (para avisar o cliente).
$noCarrinho = (int) ($_SESSION['carrinho'][$id] ?? 0);

include __DIR__ . '/../app/includes/layout_site_topo.php';
?>

<p><a class="btn btn-sec btn-pequeno" href="<?= Util::e($voltarLista) ?>">&larr; Voltar para a vitrine</a></p>

<?php if ($erro): ?>
    <div class="alerta alerta-erro"><?= Util::e($erro) ?></div>

<?php elseif ($produto === null): ?>
    <div class="vazio">
        <p>Produto não encontrado.</p>
        <a class="btn" href="<?= Util::url('index.php') ?>">Ir para a vitrine</a>
    </div>

<?php else: ?>
    <?php $frete = $produto->calcularFrete(); // POLIMORFISMO: cada tipo calcula do seu jeito ?>

    <div class="produto-pagina">

        <!-- ===== COLUNA ESQUERDA: informações ===== -->
        <section class="caixa produto-info">
            <div class="cartao-selos">
                <span class="selo selo-azul"><?= Util::e($produto->getTipoRotulo()) ?></span>
                <span class="selo selo-cinza"><?= Util::e($produto->getCategoria()->getNome()) ?></span>
            </div>

            <h1><?= Util::e($produto->getNome()) ?></h1>

            <?php if ($produto->getDescricao() !== ''): ?>
                <!-- nl2br transforma quebras de linha em <br>. O texto é escapado antes (Util::e). -->
                <p class="produto-descricao"><?= nl2br(Util::e($produto->getDescricao())) ?></p>
            <?php endif; ?>

            <h2>Especificações técnicas</h2>
            <table class="tabela tabela-specs">
                <?php foreach ($produto->getEspecificacoes() as $rotulo => $valor): ?>
                    <tr><th><?= Util::e($rotulo) ?></th><td><?= Util::e($valor) ?></td></tr>
                <?php endforeach; ?>
                <tr><th>Garantia</th><td><?= (int) $produto->getGarantiaMeses() ?> meses</td></tr>
                <tr><th>Frete</th><td><?= $frete > 0 ? Util::moeda($frete) : 'Grátis' ?></td></tr>
            </table>
        </section>

        <!-- ===== COLUNA DIREITA: compra ===== -->
        <aside class="caixa produto-compra">
            <div class="preco preco-grande"><?= Util::moeda($produto->getPreco()) ?></div>

            <?php if ($produto->temEstoque()): ?>
                <span class="selo selo-verde"><?= (int) $produto->getEstoque() ?> unidades em estoque</span>

                <form method="post" action="<?= Util::url('carrinho.php') ?>" class="form-compra">
                    <?= Csrf::campo() ?>
                    <input type="hidden" name="acao" value="adicionar">
                    <input type="hidden" name="produto_id" value="<?= (int) $produto->getId() ?>">
                    <?= Util::campoVoltar() ?>

                    <label for="quantidade">Quantidade</label>
                    <input type="number" id="quantidade" name="quantidade" value="1" min="1"
                           max="<?= (int) $produto->getEstoque() ?>">

                    <button class="btn btn-bloco" type="submit">Adicionar ao carrinho</button>
                </form>
            <?php else: ?>
                <span class="selo selo-vermelho">Sem estoque</span>
            <?php endif; ?>

            <?php if ($noCarrinho > 0): ?>
                <p class="aviso-carrinho">
                    Você já tem <strong><?= (int) $noCarrinho ?></strong> unidade(s) deste produto no carrinho.
                </p>
            <?php endif; ?>

            <a class="btn btn-sec btn-bloco" href="<?= Util::url('carrinho.php') ?>">Ver meu carrinho</a>
        </aside>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../app/includes/layout_site_rodape.php'; ?>
