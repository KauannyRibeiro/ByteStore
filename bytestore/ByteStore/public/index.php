<?php
// =====================================================================
// PÁGINA: VITRINE (página inicial da loja)
// =====================================================================
// O que faz: mostra os produtos, permite filtrar por categoria, buscar
// por nome e adicionar ao carrinho.
//
// FLUXO DESTA PÁGINA:
//   1) lê os filtros da URL  (?categoria=2  e  ?q=ryzen)
//   2) pede os dados aos DAOs (que consultam o banco)
//   3) mostra o HTML com um cartão para cada produto
// =====================================================================

// Carrega as classes e inicia a sessão.
require_once __DIR__ . '/../app/includes/iniciar.php';

// ---------- 1) Lembrar onde o cliente está ----------
// Guardamos este endereço na sessão. Assim o botão "Continuar comprando"
// do carrinho (e o "Voltar" da página do produto) trazem o cliente de volta
// para ESTA mesma tela, com o mesmo filtro.
$_SESSION['loja_url']  = Util::urlAtual();
$_SESSION['lista_url'] = Util::urlAtual();

// ---------- 2) Variáveis da página ----------
$titulo     = 'Vitrine';
$produtos   = [];   // lista de objetos Hardware/Periferico a mostrar
$categorias = [];   // lista de objetos Categoria (para os botões de filtro)
$erro       = null; // mensagem de erro, se algo falhar

// ---------- 3) Filtros vindos da URL ----------
// $_GET guarda o que vem depois do "?" no endereço.
// O (int) garante que só entra número (proteção contra texto malicioso).
$categoriaId = null; // null = todas as categorias
if (isset($_GET['categoria']) && (int) $_GET['categoria'] > 0) {
    $categoriaId = (int) $_GET['categoria'];
}
// Texto digitado na busca (trim tira espaços sobrando).
$busca = trim((string) ($_GET['q'] ?? ''));

// ---------- 4) Busca os dados no banco ----------
// Tudo dentro de try/catch: se o banco falhar, mostramos uma mensagem
// amigável em vez de quebrar a página.
try {
    $categoriaDAO = new CategoriaDAO();
    $categorias   = $categoriaDAO->listarTodos();

    if ($categoriaId !== null && $busca === '') {
        // AGREGAÇÃO: a categoria já vem com a lista dos SEUS produtos.
        $categoria = $categoriaDAO->consultarComProdutos($categoriaId);
        $produtos  = $categoria !== null ? $categoria->getProdutos() : [];
    } else {
        // Sem categoria, ou com busca por nome: usa o filtro combinado do ProdutoDAO.
        $produtos = (new ProdutoDAO())->listarFiltrado($categoriaId, $busca);
    }
} catch (Exception $e) {
    $erro = $e->getMessage();
}

// ---------- Fim da parte PHP: abaixo vem o cabeçalho e o HTML ----------
include __DIR__ . '/../app/includes/layout_site_topo.php';
?>

<!-- ===== TÍTULO ===== -->
<section class="vitrine-titulo">
    <h1>Vitrine</h1>
    <p>Hardware e periféricos para montar ou melhorar o seu setup.</p>
</section>

<?php if ($erro): ?>
    <div class="alerta alerta-erro"><?= Util::e($erro) ?></div>
<?php endif; ?>

<!-- ===== FILTROS: categorias (botões) + busca por nome ===== -->
<section class="barra-filtros">
    <nav class="chips" aria-label="Categorias">
        <a class="chip <?= $categoriaId === null ? 'ativo' : '' ?>" href="<?= Util::url('index.php') ?>">Todas</a>
        <?php foreach ($categorias as $c): ?>
            <a class="chip <?= $categoriaId === $c->getId() ? 'ativo' : '' ?>"
               href="<?= Util::url('index.php?categoria=' . (int) $c->getId()) ?>">
                <?= Util::e($c->getNome()) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Busca por nome (method="get": o texto vai na URL, como ?q=ryzen) -->
    <form class="busca" method="get" action="<?= Util::url('index.php') ?>">
        <?php if ($categoriaId !== null): ?>
            <!-- Mantém a categoria escolhida ao buscar -->
            <input type="hidden" name="categoria" value="<?= (int) $categoriaId ?>">
        <?php endif; ?>
        <input type="search" name="q" value="<?= Util::e($busca) ?>" placeholder="Buscar produto pelo nome...">
        <button class="btn" type="submit">Buscar</button>
    </form>
</section>

<!-- ===== RESUMO DO RESULTADO ===== -->
<p class="resultado-info">
    <?= count($produtos) ?> produto(s) encontrado(s)
    <?php if ($categoriaId !== null || $busca !== ''): ?>
        &middot; <a href="<?= Util::url('index.php') ?>">Limpar filtros</a>
    <?php endif; ?>
</p>

<!-- ===== LISTA DE PRODUTOS: um cartão para cada produto ===== -->
<?php if (!empty($produtos)): ?>
    <div class="grid-produtos">
        <?php foreach ($produtos as $p): ?>
            <article class="cartao-produto">
                <div class="cartao-corpo">
                    <div class="cartao-selos">
                        <span class="selo selo-azul"><?= Util::e($p->getTipoRotulo()) ?></span>
                        <span class="selo selo-cinza"><?= Util::e($p->getCategoria()->getNome()) ?></span>
                    </div>

                    <h3 class="cartao-nome">
                        <a href="<?= Util::url('produto.php?id=' . (int) $p->getId()) ?>"><?= Util::e($p->getNome()) ?></a>
                    </h3>

                    <!-- POLIMORFISMO: o mesmo método mostra detalhes diferentes para Hardware e Periférico -->
                    <p class="cartao-detalhes"><?= Util::e($p->exibirDetalhesEspecificos()) ?></p>
                </div>

                <div class="cartao-rodape">
                    <div class="preco"><?= Util::moeda($p->getPreco()) ?></div>

                    <?php if ($p->temEstoque()): ?>
                        <span class="selo selo-verde"><?= (int) $p->getEstoque() ?> em estoque</span>
                    <?php else: ?>
                        <span class="selo selo-vermelho">Sem estoque</span>
                    <?php endif; ?>

                    <!-- Formulário: ao clicar, o produto vai ao carrinho e o cliente CONTINUA nesta página -->
                    <form method="post" action="<?= Util::url('carrinho.php') ?>">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="acao" value="adicionar">
                        <input type="hidden" name="produto_id" value="<?= (int) $p->getId() ?>">
                        <?= Util::campoVoltar() ?>
                        <button class="btn btn-bloco" type="submit" <?= $p->temEstoque() ? '' : 'disabled' ?>>
                            Adicionar ao carrinho
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php elseif (!$erro): ?>
    <div class="vazio">
        <p>Nenhum produto encontrado.</p>
        <a class="btn" href="<?= Util::url('index.php') ?>">Ver todos os produtos</a>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../app/includes/layout_site_rodape.php'; ?>
