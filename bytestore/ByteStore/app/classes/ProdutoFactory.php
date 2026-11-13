<?php
// =====================================================================
// CLASSE: ProdutoFactory  ("fábrica" de produtos)
// =====================================================================
// Recebe um array de dados (uma linha do banco OU os campos do formulário)
// e devolve o objeto certo: Hardware ou Periferico.
//
// Por que existe? Dois lugares precisam fazer isso (o ProdutoDAO, ao ler
// do banco, e o formulário do admin, ao salvar). Com a fábrica, a regra
// "qual classe criar?" fica escrita em UM lugar só.
// =====================================================================
final class ProdutoFactory
{
    // O array $d tem chaves como: id, tipo, nome, preco, estoque, categoria_id, socket...
    public static function criar(array $d): Produto
    {
        // Converte cada campo para o tipo certo. "??" usa um valor padrão se a chave não existir.
        $id        = (isset($d['id']) && $d['id'] !== '') ? (int) $d['id'] : null; // sem id = produto novo
        $nome      = (string) ($d['nome'] ?? '');
        $preco     = (float) str_replace(',', '.', (string) ($d['preco'] ?? 0));   // aceita "10,50" e "10.50"
        $estoque   = (int) ($d['estoque'] ?? 0);
        $categoria = (int) ($d['categoria_id'] ?? 0);
        $descricao = (string) ($d['descricao'] ?? '');

        // O campo "tipo" decide qual classe filha criar.
        switch ($d['tipo'] ?? '') {
            case 'hardware':
                return new Hardware($id, $nome, $preco, $estoque, $categoria,
                    (string) ($d['socket'] ?? ''), (int) ($d['tdp'] ?? 0), $descricao);

            case 'periferico':
                return new Periferico($id, $nome, $preco, $estoque, $categoria,
                    (int) ($d['dpi'] ?? 0), (string) ($d['conexao'] ?? ''), $descricao);

            default:
                throw new InvalidArgumentException('Selecione o tipo do produto (Hardware ou Periférico).');
        }
    }
}
