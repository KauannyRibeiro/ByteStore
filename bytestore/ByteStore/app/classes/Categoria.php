<?php
// =====================================================================
// CLASSE: Categoria  (Processadores, Mouses, Teclados...)
// =====================================================================
// AGREGAÇÃO: uma Categoria AGRUPA vários Produtos (lista $produtos),
// mas os produtos existem por conta própria: se a categoria sair da
// memória, os produtos continuam existindo.
// (No banco, a ligação é a chave estrangeira produtos.categoria_id.)
// =====================================================================
class Categoria
{
    // ENCAPSULAMENTO: tudo "private"; acesso só por getters/setters.
    private ?int $id;
    private string $nome = '';
    private string $descricao = '';
    private array $produtos = [];        // lista de objetos Produto (agregação)
    private ?int $totalProdutos = null;  // quantidade de produtos vinda do banco (sem carregar a lista)

    public function __construct(?int $id, string $nome, string $descricao = '')
    {
        $this->id = $id;
        $this->setNome($nome);
        $this->setDescricao($descricao);
    }

    // ---------- Getters ----------
    public function getId(): ?int          { return $this->id; }
    public function getNome(): string      { return $this->nome; }
    public function getDescricao(): string { return $this->descricao; }
    public function getProdutos(): array   { return $this->produtos; }

    // ---------- Setters (com validação) ----------
    public function setNome(string $nome): void
    {
        $nome = trim($nome);
        if ($nome === '' || mb_strlen($nome) > 100) {
            throw new InvalidArgumentException('O nome da categoria é obrigatório (até 100 caracteres).');
        }
        $this->nome = $nome;
    }

    public function setDescricao(string $descricao): void
    {
        $this->descricao = trim($descricao);
    }

    // Coloca um produto dentro da categoria (AGREGAÇÃO).
    public function adicionarProduto(Produto $produto): void
    {
        $this->produtos[] = $produto; // [] = adiciona no fim da lista
    }

    // O CategoriaDAO informa o total vindo do banco (consulta com COUNT).
    public function setTotalProdutos(int $total): void
    {
        $this->totalProdutos = $total;
    }

    // Quantos produtos a categoria tem: usa o número do banco; se não houver, conta a lista.
    public function contarProdutos(): int
    {
        return $this->totalProdutos ?? count($this->produtos);
    }
}
