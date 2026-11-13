<?php
// =====================================================================
// CLASSE: Hardware  (processadores, placas de vídeo, memória...)
// =====================================================================
// HERANÇA: "extends Produto" significa que Hardware É UM Produto.
// Ele ganha de graça tudo que Produto tem (nome, preço, estoque...)
// e acrescenta só o que é específico de hardware: socket e TDP.
// =====================================================================
class Hardware extends Produto
{
    // Atributos ESPECÍFICOS de hardware ("private" = só esta classe enxerga)
    private string $socket; // compatibilidade. Ex.: AM4, LGA1700, PCIe 4.0 x16
    private int $tdp;       // consumo de energia em watts (0 = não se aplica)

    public function __construct(?int $id, string $nome, float $preco, int $estoque, int $categoriaId,
                                string $socket, int $tdp, string $descricao = '')
    {
        // parent:: chama o construtor da classe PAI (Produto) para preencher os dados comuns.
        parent::__construct($id, $nome, $preco, $estoque, $categoriaId, $descricao);
        // Depois preenchemos o que é só do Hardware.
        $this->setSocket($socket);
        $this->setTdp($tdp);
    }

    // ---------- Getters e setters (com validação) ----------
    public function getSocket(): string { return $this->socket; }
    public function getTdp(): int        { return $this->tdp; }

    public function setSocket(string $socket): void
    {
        $socket = trim($socket);
        if ($socket === '' || mb_strlen($socket) > 50) {
            throw new InvalidArgumentException('Informe o socket/compatibilidade (até 50 caracteres).');
        }
        $this->socket = $socket;
    }

    public function setTdp(int $tdp): void
    {
        if ($tdp < 0 || $tdp > 2000) {
            throw new InvalidArgumentException('O TDP deve estar entre 0 e 2000 W.');
        }
        $this->tdp = $tdp;
    }

    // =================================================================
    // POLIMORFISMO (SOBRESCRITA): aqui o Hardware escreve a SUA versão
    // dos métodos que a classe Produto deixou "abstract".
    // =================================================================
    public function getTipo(): string
    {
        return 'hardware';
    }

    // Resumo de uma linha mostrado nos cartões da vitrine.
    public function exibirDetalhesEspecificos(): string
    {
        $texto = 'Socket: ' . $this->socket;
        // Operador ternário:  condição ? valor_se_verdadeiro : valor_se_falso
        return $this->tdp > 0 ? $texto . ' | TDP: ' . $this->tdp . ' W' : $texto;
    }

    // Tabela de especificações da página do produto (rótulo => valor).
    public function getEspecificacoes(): array
    {
        $specs = ['Socket / Compatibilidade' => $this->socket];
        if ($this->tdp > 0) {
            $specs['TDP'] = $this->tdp . ' W';
        }
        return $specs;
    }

    public function getGarantiaMeses(): int
    {
        return 12; // hardware: 12 meses de garantia
    }

    // Hardware é mais pesado: frete de R$ 30,00, grátis a partir de R$ 2.000.
    public function calcularFrete(): float
    {
        return $this->preco >= 2000 ? 0.0 : 30.00;
    }

    // Colunas extras que o DAO grava no banco. As que não são de hardware ficam null.
    public function getAtributosEspecificos(): array
    {
        return ['socket' => $this->socket, 'tdp' => $this->tdp, 'dpi' => null, 'conexao' => null];
    }
}
