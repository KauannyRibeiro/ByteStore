<?php
// =====================================================================
// CLASSE: ItemCarrinho  (uma linha do carrinho: produto + quantidade)
// =====================================================================
// COMPOSIÇÃO: o ItemCarrinho só existe DENTRO de um Carrinho. Quem cria
// os itens é o próprio Carrinho (veja Carrinho::adicionar).
//
// ASSOCIAÇÃO: o item REFERENCIA um Produto, que existe por conta própria.
// =====================================================================
class ItemCarrinho
{
    private Produto $produto;
    private int $quantidade;

    public function __construct(Produto $produto, int $quantidade)
    {
        $this->produto = $produto;
        $this->setQuantidade($quantidade); // valida
    }

    public function getProduto(): Produto { return $this->produto; }
    public function getQuantidade(): int  { return $this->quantidade; }

    public function setQuantidade(int $quantidade): void
    {
        if ($quantidade < 1) {
            throw new InvalidArgumentException('A quantidade deve ser pelo menos 1.');
        }
        $this->quantidade = $quantidade;
    }

    // preço do produto x quantidade
    public function getSubtotal(): float
    {
        return $this->produto->getPreco() * $this->quantidade;
    }
}
