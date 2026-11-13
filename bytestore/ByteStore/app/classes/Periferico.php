<?php
// =====================================================================
// CLASSE: Periferico  (mouses, teclados...)
// =====================================================================
// HERANÇA: Periferico também É UM Produto, mas tem atributos próprios:
// DPI (sensibilidade do mouse) e tipo de conexão.
// =====================================================================
class Periferico extends Produto
{
    private int $dpi;        // sensibilidade (0 = não se aplica, ex.: teclado)
    private string $conexao; // Ex.: USB, Wireless 2.4GHz, Bluetooth

    public function __construct(?int $id, string $nome, float $preco, int $estoque, int $categoriaId,
                                int $dpi, string $conexao, string $descricao = '')
    {
        parent::__construct($id, $nome, $preco, $estoque, $categoriaId, $descricao); // dados comuns (classe pai)
        $this->setDpi($dpi);
        $this->setConexao($conexao);
    }

    // ---------- Getters e setters (com validação) ----------
    public function getDpi(): int        { return $this->dpi; }
    public function getConexao(): string { return $this->conexao; }

    public function setDpi(int $dpi): void
    {
        if ($dpi < 0 || $dpi > 100000) {
            throw new InvalidArgumentException('O DPI deve estar entre 0 e 100000.');
        }
        $this->dpi = $dpi;
    }

    public function setConexao(string $conexao): void
    {
        $conexao = trim($conexao);
        if ($conexao === '' || mb_strlen($conexao) > 50) {
            throw new InvalidArgumentException('Informe o tipo de conexão (até 50 caracteres).');
        }
        $this->conexao = $conexao;
    }

    // =================================================================
    // POLIMORFISMO: os MESMOS métodos do Hardware, com comportamento diferente.
    // =================================================================
    public function getTipo(): string
    {
        return 'periferico';
    }

    public function exibirDetalhesEspecificos(): string
    {
        $texto = 'Conexão: ' . $this->conexao;
        return $this->dpi > 0 ? 'DPI: ' . $this->dpi . ' | ' . $texto : $texto;
    }

    public function getEspecificacoes(): array
    {
        $specs = [];
        if ($this->dpi > 0) {
            $specs['DPI'] = (string) $this->dpi;
        }
        $specs['Conexão'] = $this->conexao;
        return $specs;
    }

    public function getGarantiaMeses(): int
    {
        return 6; // periférico: 6 meses de garantia
    }

    // Periférico é leve: frete de R$ 15,00, grátis a partir de R$ 500.
    public function calcularFrete(): float
    {
        return $this->preco >= 500 ? 0.0 : 15.00;
    }

    public function getAtributosEspecificos(): array
    {
        return ['socket' => null, 'tdp' => null, 'dpi' => $this->dpi, 'conexao' => $this->conexao];
    }
}
