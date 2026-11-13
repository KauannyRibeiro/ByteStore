<?php
// =====================================================================
// CLASSE ABSTRATA: BaseDAO
// =====================================================================
// DAO = Data Access Object: a classe que "conversa com o banco" para um
// tipo de dado (produtos, categorias, usuários).
//
// Os três DAOs fazem coisas parecidas (guardar a conexão, tratar erros).
// Para não repetir código, essa parte comum fica aqui, e os DAOs HERDAM
// com "extends BaseDAO".
//
// "abstract" = esta classe não pode ser usada sozinha (não existe
// "new BaseDAO"): ela só serve de base para outras.
// =====================================================================
abstract class BaseDAO
{
    // Importa o método registrarLog() (veja LogavelTrait).
    use LogavelTrait;

    // Códigos de erro do MySQL que queremos reconhecer, para dar mensagens claras:
    protected const ERRO_DUPLICADO  = 1062; // valor repetido em coluna UNIQUE (ex.: nome de categoria)
    protected const ERRO_TEM_FILHOS = 1451; // apagar algo que outra tabela ainda usa (categoria com produtos)
    protected const ERRO_SEM_PAI    = 1452; // usar algo que não existe (produto com categoria inexistente)

    // A conexão PDO. "protected" = só esta classe e as filhas (DAOs) enxergam.
    protected PDO $pdo;

    // Construtor: roda automaticamente quando fazemos "new ProdutoDAO()".
    public function __construct()
    {
        $this->pdo = Database::conectar(); // $this = "este objeto"
    }

    // Quando o banco dá erro: grava os detalhes no log e lança uma exceção
    // com uma mensagem amigável (a página decide como mostrar).
    protected function falhar(PDOException $e, string $mensagemAmigavel): void
    {
        $this->registrarLog($mensagemAmigavel . ' | ' . $e->getMessage(), 'ERRO');
        throw new RuntimeException($mensagemAmigavel, 0, $e);
    }

    // Descobre o número do erro do MySQL (para comparar com as constantes ERRO_... acima).
    protected function codigoMySQL(PDOException $e): int
    {
        return (int) ($e->errorInfo[1] ?? 0);
    }
}
