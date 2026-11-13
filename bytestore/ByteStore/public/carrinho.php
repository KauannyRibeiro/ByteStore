<?php
// =====================================================================
// PÁGINA: CARRINHO DE COMPRAS (simulado: fica na SESSÃO, não no banco)
// =====================================================================
// Esta página tem DUAS partes:
//
//   PARTE 1 - AÇÕES (quando um formulário é enviado por POST):
//             adicionar, aumentar, diminuir, remover, limpar, finalizar.
//             Depois de agir, redireciona para uma página (padrão
//             Post/Redirect/Get). Isso evita reenviar o formulário se o
//             cliente apertar F5 ou o botão Voltar do navegador.
//
//   PARTE 2 - EXIBIÇÃO (quando abrimos a página normalmente, por GET):
//             mostra os itens, o resumo e os botões.
//
// PARA ONDE CADA AÇÃO LEVA O CLIENTE (para ele nunca ficar perdido):
//   adicionar  -> volta para a página onde ele estava (vitrine ou produto)
//   aumentar / diminuir / remover / limpar -> continua no carrinho
//   finalizar  -> vai para a vitrine com uma mensagem de sucesso
// =====================================================================

require_once __DIR__ . '/../app/includes/iniciar.php';

// =====================================================================
// PARTE 1: AÇÕES (POST)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao      = (string) ($_POST['acao'] ?? '');
    $produtoId = (int) ($_POST['produto_id'] ?? 0);

    // Destino padrão: o próprio carrinho.
    $destino = Util::url('carrinho.php');

    // Ao ADICIONAR, o cliente volta para a página de onde veio.
    // urlSegura só aceita endereços do nosso site (evita redirecionar para sites falsos).
    if ($acao === 'adicionar') {
        $destino = Util::urlSegura((string) ($_POST['voltar'] ?? ''), $destino);
    }

    try {
        // Confere o token CSRF (protege contra formulários enviados por outro site).
        if (!Csrf::validar($_POST['csrf'] ?? null)) {
            throw new InvalidArgumentException('Sessão expirada. Recarregue a página e tente novamente.');
        }

        // Monta o carrinho a partir do que está na sessão.
        $dao      = new ProdutoDAO();
        $carrinho = Carrinho::carregarDaSessao($dao);

        switch ($acao) {

            case 'adicionar':
                $produto = $dao->consultar($produtoId);
                if ($produto === null) {
                    throw new InvalidArgumentException('Produto não encontrado.');
                }
                $quantidade = max(1, (int) ($_POST['quantidade'] ?? 1)); // pelo menos 1
                $carrinho->adicionar($produto, $quantidade);             // confere o estoque
                Util::flash('sucesso', '"' . $produto->getNome() . '" foi adicionado ao carrinho.',
                            'Ver carrinho', Util::url('carrinho.php'));
                break;

            case 'aumentar': // botão "+" (soma 1 unidade)
                $produto = $dao->consultar($produtoId);
                if ($produto === null) {
                    throw new InvalidArgumentException('Produto não encontrado.');
                }
                $carrinho->adicionar($produto, 1);
                break;

            case 'diminuir': // botão "-" (tira 1 unidade; se zerar, remove)
                $carrinho->diminuir($produtoId);
                break;

            case 'remover':
                $carrinho->remover($produtoId);
                Util::flash('info', 'Item removido do carrinho.');
                break;

            case 'limpar':
                $carrinho->limpar();
                Util::flash('info', 'Carrinho esvaziado.');
                break;

            case 'finalizar':
                if ($carrinho->estaVazio()) {
                    throw new InvalidArgumentException('Seu carrinho está vazio.');
                }
                $carrinho->limpar();
                Util::flash('sucesso', 'Compra simulada com sucesso! Este projeto não processa pagamentos de verdade.');
                $destino = Util::url('index.php'); // volta para a vitrine
                break;

            default:
                throw new InvalidArgumentException('Ação inválida.');
        }

        // Guarda o carrinho atualizado de volta na sessão.
        $carrinho->salvarNaSessao();

    } catch (Exception $e) {
        // Qualquer erro vira uma mensagem na tela (sem quebrar a página).
        Util::flash('erro', $e->getMessage());
    }

    Util::redirecionar($destino); // vai para o destino e encerra o script
}

// =====================================================================
// PARTE 2: EXIBIÇÃO (GET)
// =====================================================================
$titulo   = 'Meu carrinho';
$erro     = null;
$carrinho = new Carrinho(); // começa vazio

try {
    $carrinho = Carrinho::carregarDaSessao(new ProdutoDAO());
} catch (Exception $e) {
    $erro = $e->getMessage();
}

// Para onde o botão "Continuar comprando" leva: a última página da loja que o cliente viu.
$continuar = Util::urlSegura((string) ($_SESSION['loja_url'] ?? ''), Util::url('index.php'));

include __DIR__ . '/../app/includes/layout_site_topo.php';
?>

<!-- ===== TOPO: título + botão para voltar às compras (sempre visível) ===== -->
<div class="carrinho-cabecalho">
    <h1>Meu carrinho</h1>
    <a class="btn btn-sec" href="<?= Util::e($continuar) ?>">&larr; Continuar comprando</a>
</div>

<?php if ($erro): ?>
    <div class="alerta alerta-erro"><?= Util::e($erro) ?></div>

<?php elseif ($carrinho->estaVazio()): ?>
    <!-- ===== CARRINHO VAZIO ===== -->
    <div class="vazio">
        <p>Seu carrinho está vazio.</p>
        <a class="btn" href="<?= Util::e($continuar) ?>">Ver produtos</a>
    </div>

<?php else: ?>
    <div class="carrinho-pagina">

        <!-- ===== COLUNA ESQUERDA: lista de itens ===== -->
        <section class="carrinho-itens">
            <?php foreach ($carrinho->getItens() as $item): ?>
                <?php $p = $item->getProduto(); ?>
                <article class="caixa carrinho-item">

                    <!-- Nome e preço unitário -->
                    <div class="item-info">
                        <a class="item-nome" href="<?= Util::url('produto.php?id=' . (int) $p->getId()) ?>"><?= Util::e($p->getNome()) ?></a>
                        <span class="item-unitario"><?= Util::moeda($p->getPreco()) ?> cada</span>
                    </div>

                    <!-- Controle de quantidade:  [-]  2  [+]  -->
                    <div class="item-quantidade">
                        <form method="post" action="<?= Util::url('carrinho.php') ?>">
                            <?= Csrf::campo() ?>
                            <input type="hidden" name="acao" value="diminuir">
                            <input type="hidden" name="produto_id" value="<?= (int) $p->getId() ?>">
                            <button class="qtd-botao" type="submit" aria-label="Diminuir quantidade">&minus;</button>
                        </form>

                        <span class="qtd-valor"><?= (int) $item->getQuantidade() ?></span>

                        <form method="post" action="<?= Util::url('carrinho.php') ?>">
                            <?= Csrf::campo() ?>
                            <input type="hidden" name="acao" value="aumentar">
                            <input type="hidden" name="produto_id" value="<?= (int) $p->getId() ?>">
                            <button class="qtd-botao" type="submit" aria-label="Aumentar quantidade"
                                    <?= $item->getQuantidade() >= $p->getEstoque() ? 'disabled title="Estoque máximo atingido"' : '' ?>>+</button>
                        </form>
                    </div>

                    <!-- Subtotal do item -->
                    <div class="item-subtotal"><?= Util::moeda($item->getSubtotal()) ?></div>

                    <!-- Remover o item inteiro -->
                    <form method="post" action="<?= Util::url('carrinho.php') ?>">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="acao" value="remover">
                        <input type="hidden" name="produto_id" value="<?= (int) $p->getId() ?>">
                        <button class="btn btn-sec btn-pequeno" type="submit">Remover</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- ===== COLUNA DIREITA: resumo do pedido ===== -->
        <aside class="caixa carrinho-resumo">
            <h2>Resumo do pedido</h2>

            <div class="resumo-linha"><span>Itens</span><span><?= (int) $carrinho->getQuantidadeTotal() ?></span></div>
            <div class="resumo-linha"><span>Subtotal</span><span><?= Util::moeda($carrinho->getSubtotal()) ?></span></div>
            <div class="resumo-linha">
                <span>Frete</span>
                <span><?= $carrinho->getFrete() > 0 ? Util::moeda($carrinho->getFrete()) : 'Grátis' ?></span>
            </div>
            <div class="resumo-linha resumo-total"><span>Total</span><span><?= Util::moeda($carrinho->getTotal()) ?></span></div>

            <form method="post" action="<?= Util::url('carrinho.php') ?>">
                <?= Csrf::campo() ?>
                <input type="hidden" name="acao" value="finalizar">
                <button class="btn btn-bloco" type="submit">Finalizar compra (simulado)</button>
            </form>

            <a class="btn btn-sec btn-bloco" href="<?= Util::e($continuar) ?>">Continuar comprando</a>

            <form method="post" action="<?= Util::url('carrinho.php') ?>" onsubmit="return confirm('Esvaziar o carrinho?')">
                <?= Csrf::campo() ?>
                <input type="hidden" name="acao" value="limpar">
                <button class="btn btn-link-perigo btn-bloco" type="submit">Esvaziar carrinho</button>
            </form>
        </aside>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../app/includes/layout_site_rodape.php'; ?>
