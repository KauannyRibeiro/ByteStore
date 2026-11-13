<?php
// =====================================================================
// CLASSE: CategoriaDAO  (acesso ao banco: tabela "categorias")
// =====================================================================
// Segue o MESMO contrato (RepositorioInterface) do ProdutoDAO.
// Todas as consultas são parametrizadas (prepare + execute).
// =====================================================================
class CategoriaDAO extends BaseDAO implements RepositorioInterface
{
    // Garante que o objeto recebido é uma Categoria.
    private function garantir(object $obj): void
    {
        if (!$obj instanceof Categoria) {
            throw new InvalidArgumentException('Esperado um objeto Categoria.');
        }
    }

    // ------------------------------ CREATE ------------------------------
    public function inserir(object $obj): int
    {
        $this->garantir($obj);
        try {
            $stmt = $this->pdo->prepare('INSERT INTO categorias (nome, descricao) VALUES (:nome, :descricao)');
            $stmt->execute([':nome' => $obj->getNome(), ':descricao' => $obj->getDescricao()]);

            $id = (int) $this->pdo->lastInsertId();
            $this->registrarLog("Categoria #$id criada: " . $obj->getNome());
            return $id;
        } catch (PDOException $e) {
            // A coluna "nome" é UNIQUE no banco: nomes repetidos geram o erro 1062.
            if ($this->codigoMySQL($e) === self::ERRO_DUPLICADO) {
                throw new InvalidArgumentException('Já existe uma categoria com esse nome.');
            }
            $this->falhar($e, 'Não foi possível salvar a categoria.');
        }
    }

    // ------------------------------ READ (um) ------------------------------
    public function consultar(int $id): ?Categoria
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM categorias WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $r = $stmt->fetch();
            return $r ? new Categoria((int) $r['id'], $r['nome'], (string) $r['descricao']) : null;
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível consultar a categoria.');
        }
    }

    // Categoria COM a lista dos seus produtos carregada (AGREGAÇÃO).
    public function consultarComProdutos(int $id): ?Categoria
    {
        $categoria = $this->consultar($id);
        if ($categoria !== null) {
            foreach ((new ProdutoDAO())->listarFiltrado($id) as $produto) {
                $categoria->adicionarProduto($produto);
            }
        }
        return $categoria;
    }

    // ------------------------------ READ (vários) ------------------------------
    public function listarTodos(): array
    {
        try {
            // LEFT JOIN + COUNT: traz cada categoria com a QUANTIDADE de produtos dela
            // (inclusive as que têm zero). GROUP BY agrupa as linhas por categoria.
            $sql = 'SELECT c.*, COUNT(p.id) AS total
                      FROM categorias c
                      LEFT JOIN produtos p ON p.categoria_id = c.id
                  GROUP BY c.id, c.nome, c.descricao
                  ORDER BY c.nome';

            $lista = [];
            foreach ($this->pdo->query($sql) as $r) {
                $c = new Categoria((int) $r['id'], $r['nome'], (string) $r['descricao']);
                $c->setTotalProdutos((int) $r['total']);
                $lista[] = $c;
            }
            return $lista;
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível listar as categorias.');
        }
    }

    // ------------------------------ UPDATE ------------------------------
    public function atualizar(object $obj): bool
    {
        $this->garantir($obj);
        try {
            $stmt = $this->pdo->prepare('UPDATE categorias SET nome = :nome, descricao = :descricao WHERE id = :id');
            $stmt->execute([
                ':nome'      => $obj->getNome(),
                ':descricao' => $obj->getDescricao(),
                ':id'        => $obj->getId(),
            ]);
            $this->registrarLog('Categoria #' . $obj->getId() . ' atualizada.');
            return true;
        } catch (PDOException $e) {
            if ($this->codigoMySQL($e) === self::ERRO_DUPLICADO) {
                throw new InvalidArgumentException('Já existe uma categoria com esse nome.');
            }
            $this->falhar($e, 'Não foi possível atualizar a categoria.');
        }
    }

    // ------------------------------ DELETE ------------------------------
    public function excluir(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM categorias WHERE id = :id');
            $stmt->execute([':id' => $id]);

            $this->registrarLog("Categoria #$id excluída.");
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            // CHAVE ESTRANGEIRA: o banco impede apagar uma categoria que ainda tem produtos.
            if ($this->codigoMySQL($e) === self::ERRO_TEM_FILHOS) {
                throw new InvalidArgumentException('Não é possível excluir: existem produtos nesta categoria.');
            }
            $this->falhar($e, 'Não foi possível excluir a categoria.');
        }
    }
}
