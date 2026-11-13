<?php
// =====================================================================
// CLASSE: ProdutoDAO  (acesso ao banco: tabela "produtos")
// =====================================================================
// É a ÚNICA classe que escreve SQL de produtos. As páginas nunca falam
// com o banco direto: elas pedem para o DAO.
//
//   extends BaseDAO                 -> herda a conexão ($this->pdo) e o tratamento de erros
//   implements RepositorioInterface -> cumpre o contrato do CRUD (os 5 métodos)
//
// SEGURANÇA - CONSULTAS PARAMETRIZADAS:
// Nunca colamos valores no texto do SQL. Usamos "marcadores" (:id, :nome)
// e passamos os valores à parte no execute(). Assim, mesmo que alguém
// digite SQL malicioso, o banco trata como simples texto (evita SQL Injection).
// =====================================================================
class ProdutoDAO extends BaseDAO implements RepositorioInterface
{
    // SELECT usado em todas as leituras. O JOIN traz junto o nome da categoria.
    // "p" e "c" são apelidos das tabelas (produtos e categorias).
    private const SELECT_BASE =
        'SELECT p.*, c.nome AS categoria_nome, c.descricao AS categoria_descricao
           FROM produtos p
     INNER JOIN categorias c ON c.id = p.categoria_id';

    // Confere se o objeto recebido é mesmo um Produto.
    // (A interface pede "object" genérico; aqui garantimos o tipo certo.)
    private function garantir(object $obj): void
    {
        if (!$obj instanceof Produto) {
            throw new InvalidArgumentException('Esperado um objeto Produto (Hardware ou Periferico).');
        }
    }

    // Transforma uma LINHA do banco em OBJETO (Hardware ou Periferico) já com a Categoria ligada.
    private function hidratar(array $linha): Produto
    {
        $produto = ProdutoFactory::criar($linha); // a fábrica escolhe a classe certa
        $produto->setCategoria(new Categoria(
            (int) $linha['categoria_id'],
            $linha['categoria_nome'],
            (string) $linha['categoria_descricao']
        ));
        return $produto;
    }

    // Parâmetros comuns ao INSERT e ao UPDATE.
    // POLIMORFISMO: getAtributosEspecificos() devolve socket/tdp (Hardware) ou dpi/conexao (Periferico).
    private function parametros(Produto $p): array
    {
        $extras = $p->getAtributosEspecificos();
        return [
            ':nome'         => $p->getNome(),
            ':descricao'    => $p->getDescricao(),
            ':preco'        => $p->getPreco(),
            ':estoque'      => $p->getEstoque(),
            ':categoria_id' => $p->getCategoriaId(),
            ':socket'       => $extras['socket'],
            ':tdp'          => $extras['tdp'],
            ':dpi'          => $extras['dpi'],
            ':conexao'      => $extras['conexao'],
        ];
    }

    // ------------------------------ CREATE ------------------------------
    // Grava um novo produto e devolve o id gerado.
    public function inserir(object $obj): int
    {
        $this->garantir($obj);
        try {
            // Os :marcadores serão trocados pelos valores no execute() (consulta parametrizada).
            $sql = 'INSERT INTO produtos (nome, descricao, preco, estoque, categoria_id, tipo, socket, tdp, dpi, conexao)
                    VALUES (:nome, :descricao, :preco, :estoque, :categoria_id, :tipo, :socket, :tdp, :dpi, :conexao)';

            $stmt = $this->pdo->prepare($sql);                                   // 1) prepara o SQL
            $stmt->execute($this->parametros($obj) + [':tipo' => $obj->getTipo()]); // 2) executa com os valores

            $id = (int) $this->pdo->lastInsertId(); // id que o banco acabou de gerar
            $this->registrarLog("Produto #$id inserido: " . $obj->getNome());
            return $id;
        } catch (PDOException $e) {
            if ($this->codigoMySQL($e) === self::ERRO_SEM_PAI) { // categoria inexistente
                throw new InvalidArgumentException('A categoria escolhida não existe.');
            }
            $this->falhar($e, 'Não foi possível salvar o produto.');
        }
    }

    // ------------------------------ READ (um) ------------------------------
    // Busca UM produto pelo id. Devolve o objeto, ou null se não existir.
    public function consultar(int $id): ?Produto
    {
        try {
            $stmt = $this->pdo->prepare(self::SELECT_BASE . ' WHERE p.id = :id');
            $stmt->execute([':id' => $id]);
            $linha = $stmt->fetch(); // false se não achou nada
            return $linha ? $this->hidratar($linha) : null;
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível consultar o produto.');
        }
    }

    // ------------------------------ READ (vários) ------------------------------
    // Lista todos os produtos.
    public function listarTodos(): array
    {
        return $this->listarFiltrado();
    }

    // Lista para a VITRINE, com filtros opcionais: por categoria e/ou por trecho do nome.
    public function listarFiltrado(?int $categoriaId = null, ?string $termo = null): array
    {
        try {
            // O SQL é montado aos poucos: cada filtro só entra se o usuário o escolheu.
            // "WHERE 1 = 1" é um truque: sempre verdadeiro, assim podemos juntar "AND ..." sem pensar.
            // Os VALORES continuam sempre em :parâmetros (nunca colados no texto do SQL).
            $sql    = self::SELECT_BASE . ' WHERE 1 = 1';
            $params = [];

            if ($categoriaId !== null) {
                $sql .= ' AND p.categoria_id = :categoria';
                $params[':categoria'] = $categoriaId;
            }

            // Busca por nome: LIKE com % procura o texto em qualquer parte do nome.
            if ($termo !== null && trim($termo) !== '') {
                $sql .= ' AND p.nome LIKE :termo';
                $params[':termo'] = '%' . trim($termo) . '%';
            }

            $sql .= ' ORDER BY p.id DESC'; // mais novos primeiro

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            // Transforma cada linha do banco em um objeto.
            $lista = [];
            foreach ($stmt->fetchAll() as $linha) {
                $lista[] = $this->hidratar($linha);
            }
            return $lista;
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível listar os produtos.');
        }
    }

    // ------------------------------ UPDATE ------------------------------
    // Altera um produto que já existe (identificado pelo id).
    public function atualizar(object $obj): bool
    {
        $this->garantir($obj);
        try {
            $sql = 'UPDATE produtos
                       SET nome = :nome, descricao = :descricao, preco = :preco, estoque = :estoque,
                           categoria_id = :categoria_id, socket = :socket, tdp = :tdp, dpi = :dpi, conexao = :conexao
                     WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($this->parametros($obj) + [':id' => $obj->getId()]);

            $this->registrarLog('Produto #' . $obj->getId() . ' atualizado.');
            return true;
        } catch (PDOException $e) {
            if ($this->codigoMySQL($e) === self::ERRO_SEM_PAI) {
                throw new InvalidArgumentException('A categoria escolhida não existe.');
            }
            $this->falhar($e, 'Não foi possível atualizar o produto.');
        }
    }

    // ------------------------------ DELETE ------------------------------
    // Apaga um produto pelo id. Devolve true se algum registro foi apagado.
    public function excluir(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM produtos WHERE id = :id');
            $stmt->execute([':id' => $id]);

            $this->registrarLog("Produto #$id excluído.");
            return $stmt->rowCount() > 0; // quantas linhas foram apagadas
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível excluir o produto.');
        }
    }

    // Quantidade total de produtos (usada no painel do admin).
    public function contar(): int
    {
        try {
            return (int) $this->pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível contar os produtos.');
        }
    }
}
