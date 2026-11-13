<?php
// =====================================================================
// CLASSE: Carrinho  (carrinho de compras SIMULADO)
// =====================================================================
// COMPOSIÇÃO: o Carrinho CRIA e CONTROLA os seus ItemCarrinho. Sem
// carrinho, não existem itens. Ninguém de fora faz "new ItemCarrinho".
//
// O carrinho NÃO usa tabela no banco: entre uma página e outra ele fica
// guardado na SESSÃO do navegador (veja salvarNaSessao / carregarDaSessao).
// Na sessão guardamos só  [id do produto => quantidade].
// =====================================================================
class Carrinho
{
    // Lista dos itens no formato  [idDoProduto => ItemCarrinho].
    private array $itens = [];

    // ---------------------------------------------------------------
    // Adicionar (ou somar) unidades de um produto
    // ---------------------------------------------------------------
    public function adicionar(Produto $produto, int $quantidade = 1): void
    {
        if ($quantidade < 1) {
            throw new InvalidArgumentException('A quantidade deve ser pelo menos 1.');
        }

        $id      = (int) $produto->getId();
        // Quantas unidades já estão no carrinho? (0 se o produto ainda não está)
        $atual   = isset($this->itens[$id]) ? $this->itens[$id]->getQuantidade() : 0;
        $novaQtd = $atual + $quantidade;

        // Não deixa passar do estoque disponível.
        if ($novaQtd > $produto->getEstoque()) {
            throw new InvalidArgumentException(
                'Estoque insuficiente para "' . $produto->getNome() . '" (disponível: ' . $produto->getEstoque() . ').'
            );
        }

        // O item é criado AQUI DENTRO (composição).
        $this->itens[$id] = new ItemCarrinho($produto, $novaQtd);
    }

    // ---------------------------------------------------------------
    // Diminuir 1 unidade (se chegar a zero, o item sai do carrinho)
    // ---------------------------------------------------------------
    public function diminuir(int $produtoId): void
    {
        if (!isset($this->itens[$produtoId])) {
            return; // não está no carrinho: nada a fazer
        }
        $item     = $this->itens[$produtoId];
        $novaQtd  = $item->getQuantidade() - 1;

        if ($novaQtd < 1) {
            unset($this->itens[$produtoId]); // zerou: remove o item
        } else {
            $item->setQuantidade($novaQtd);
        }
    }

    // Remove o produto do carrinho por completo.
    public function remover(int $produtoId): void
    {
        unset($this->itens[$produtoId]); // unset apaga a posição do array
    }

    // Esvazia o carrinho.
    public function limpar(): void
    {
        $this->itens = [];
    }

    public function estaVazio(): bool { return count($this->itens) === 0; }
    public function getItens(): array { return $this->itens; }

    // ---------------------------------------------------------------
    // Valores
    // ---------------------------------------------------------------
    // Soma o subtotal de todos os itens.
    public function getSubtotal(): float
    {
        $total = 0.0;
        foreach ($this->itens as $item) { // foreach = "para cada item da lista"
            $total += $item->getSubtotal();
        }
        return $total;
    }

    // POLIMORFISMO em ação: cada produto calcula o PRÓPRIO frete
    // (Hardware e Periferico respondem diferente ao mesmo método).
    // O carrinho cobra o maior frete entre os itens.
    public function getFrete(): float
    {
        $frete = 0.0;
        foreach ($this->itens as $item) {
            $frete = max($frete, $item->getProduto()->calcularFrete());
        }
        return $frete;
    }

    public function getTotal(): float
    {
        return $this->getSubtotal() + $this->getFrete();
    }

    // Quantidade total de unidades (somando todos os itens). Aparece no menu: "Carrinho (3)".
    public function getQuantidadeTotal(): int
    {
        $qtd = 0;
        foreach ($this->itens as $item) {
            $qtd += $item->getQuantidade();
        }
        return $qtd;
    }

    // ---------------------------------------------------------------
    // Guardar e recuperar da SESSÃO
    // ---------------------------------------------------------------
    // Guarda na sessão só [id => quantidade].
    public function salvarNaSessao(): void
    {
        $dados = [];
        foreach ($this->itens as $id => $item) {
            $dados[$id] = $item->getQuantidade();
        }
        $_SESSION['carrinho'] = $dados;
    }

    // Reconstrói o carrinho a partir da sessão, buscando os produtos ATUAIS no banco
    // (assim preço e estoque estão sempre atualizados).
    // "static" = chamamos com  Carrinho::carregarDaSessao($dao)  sem precisar de um objeto.
    public static function carregarDaSessao(ProdutoDAO $dao): self
    {
        $carrinho = new self(); // "self" = esta própria classe (Carrinho)

        foreach (($_SESSION['carrinho'] ?? []) as $id => $qtd) {
            $produto = $dao->consultar((int) $id);

            if ($produto === null) {
                continue; // o produto foi excluído enquanto estava no carrinho: ignora
            }
            try {
                $carrinho->adicionar($produto, (int) $qtd);
            } catch (InvalidArgumentException $e) {
                // o estoque diminuiu e essa quantidade não cabe mais: ignora o item
            }
        }
        return $carrinho;
    }
}
