<?php
// =====================================================================
// CLASSE ABSTRATA: Produto
// =====================================================================
// PILARES DE POO QUE APARECEM AQUI:
//
//  ABSTRAÇÃO    -> "abstract class": Produto é só um MOLDE. Não existe "um
//                  produto qualquer" na loja: todo produto é um Hardware ou
//                  um Periferico. Por isso não dá para fazer  new Produto().
//
//  ENCAPSULAMENTO -> os atributos são "protected" (ninguém de fora mexe
//                  neles direto). Para ler usamos getters (getPreco),
//                  para alterar usamos setters (setPreco) que VALIDAM antes.
//
//  POLIMORFISMO -> no final desta classe há métodos "abstract" (sem corpo).
//                  Cada filha (Hardware, Periferico) escreve a sua versão.
//                  Quem usa só chama  $produto->calcularFrete()  e cada
//                  tipo responde do seu jeito.
//
//  ASSOCIAÇÃO   -> o produto "conhece" a sua Categoria (atributo $categoria).
// =====================================================================
abstract class Produto
{
    // TRAIT: importa o método registrarLog().
    use LogavelTrait;

    // ---------- Atributos (os dados de todo produto) ----------
    // protected = visível aqui e nas classes filhas, invisível para o resto do sistema.
    // "?int" = pode ser um número inteiro OU null (null = produto novo, ainda sem id no banco).
    protected ?int $id;
    protected string $nome = '';
    protected string $descricao = '';
    protected float $preco = 0.0;
    protected int $estoque = 0;
    protected int $categoriaId = 0;
    protected ?Categoria $categoria = null; // objeto Categoria (preenchido quando vem do banco)

    // ---------- Construtor ----------
    // Roda quando criamos um objeto (new Hardware(...)). Cada setter VALIDA o valor;
    // se algo for inválido, lança uma exceção e o objeto nem chega a existir.
    public function __construct(?int $id, string $nome, float $preco, int $estoque, int $categoriaId, string $descricao = '')
    {
        $this->id = $id;                    // $this = "este objeto"; -> acessa um atributo/método dele
        $this->setNome($nome);
        $this->setPreco($preco);
        $this->setEstoque($estoque);
        $this->setCategoriaId($categoriaId);
        $this->setDescricao($descricao);
    }

    // ---------- Getters (só leem) ----------
    public function getId(): ?int          { return $this->id; }
    public function getNome(): string      { return $this->nome; }
    public function getDescricao(): string { return $this->descricao; }
    public function getPreco(): float      { return $this->preco; }
    public function getEstoque(): int      { return $this->estoque; }
    public function getCategoriaId(): int  { return $this->categoriaId; }
    public function getCategoria(): ?Categoria { return $this->categoria; }

    // true se ainda há unidades à venda
    public function temEstoque(): bool     { return $this->estoque > 0; }

    // ---------- Setters (alteram, mas VALIDANDO) ----------
    public function setNome(string $nome): void
    {
        $nome = trim($nome); // trim remove espaços do começo e do fim
        if ($nome === '' || mb_strlen($nome) > 150) {
            throw new InvalidArgumentException('O nome do produto é obrigatório (até 150 caracteres).');
        }
        $this->nome = $nome;
    }

    public function setDescricao(string $descricao): void
    {
        $this->descricao = trim($descricao);
    }

    public function setPreco(float $preco): void
    {
        if ($preco <= 0) {
            throw new InvalidArgumentException('O preço deve ser maior que zero.');
        }
        $this->preco = round($preco, 2); // 2 casas decimais
    }

    public function setEstoque(int $estoque): void
    {
        if ($estoque < 0) {
            throw new InvalidArgumentException('O estoque não pode ser negativo.');
        }
        $this->estoque = $estoque;
    }

    public function setCategoriaId(int $categoriaId): void
    {
        if ($categoriaId <= 0) {
            throw new InvalidArgumentException('Selecione uma categoria.');
        }
        $this->categoriaId = $categoriaId;
    }

    // Liga o produto ao objeto Categoria dele (ASSOCIAÇÃO).
    public function setCategoria(Categoria $categoria): void
    {
        $this->categoria   = $categoria;
        $this->categoriaId = (int) $categoria->getId();
    }

    // ---------- POLIMORFISMO ----------
    // Métodos "abstract": aqui só dizemos NOME e TIPO de retorno.
    // O corpo (o que faz) é escrito em cada filha, de forma diferente.
    abstract public function getTipo(): string;                   // 'hardware' ou 'periferico'
    abstract public function exibirDetalhesEspecificos(): string; // resumo em uma linha (vitrine)
    abstract public function getEspecificacoes(): array;          // tabela rótulo => valor (página do produto)
    abstract public function getGarantiaMeses(): int;             // regra de garantia
    abstract public function calcularFrete(): float;              // regra de frete
    abstract public function getAtributosEspecificos(): array;    // colunas extras que vão para o banco

    // Nome do tipo com a primeira letra maiúscula: "hardware" -> "Hardware"
    public function getTipoRotulo(): string
    {
        return ucfirst($this->getTipo());
    }
}
