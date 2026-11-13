<?php
// =====================================================================
// CLASSE: UsuarioDAO  (acesso ao banco: tabela "usuarios")
// =====================================================================
// Guarda os administradores. O login usa buscarPorEmail().
// Também segue o contrato RepositorioInterface (CRUD completo).
// =====================================================================
class UsuarioDAO extends BaseDAO implements RepositorioInterface
{
    // Garante que o objeto recebido é um Usuario.
    private function garantir(object $obj): void
    {
        if (!$obj instanceof Usuario) {
            throw new InvalidArgumentException('Esperado um objeto Usuario.');
        }
    }

    // Transforma uma linha do banco em objeto Usuario.
    private function hidratar(array $r): Usuario
    {
        return new Usuario((int) $r['id'], $r['nome'], $r['email'], $r['senha'], $r['nivel']);
    }

    // ------------------------------ CREATE ------------------------------
    public function inserir(object $obj): int
    {
        $this->garantir($obj);
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha, nivel) VALUES (:nome, :email, :senha, :nivel)'
            );
            $stmt->execute([
                ':nome'  => $obj->getNome(),
                ':email' => $obj->getEmail(),
                ':senha' => $obj->getSenhaHash(), // grava o HASH, nunca a senha
                ':nivel' => $obj->getNivel(),
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if ($this->codigoMySQL($e) === self::ERRO_DUPLICADO) {
                throw new InvalidArgumentException('Já existe um usuário com esse e-mail.');
            }
            $this->falhar($e, 'Não foi possível salvar o usuário.');
        }
    }

    // ------------------------------ READ (um, pelo id) ------------------------------
    public function consultar(int $id): ?Usuario
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $r = $stmt->fetch();
            return $r ? $this->hidratar($r) : null;
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível consultar o usuário.');
        }
    }

    // ------------------------------ READ (um, pelo e-mail) ------------------------------
    // Usado no LOGIN (Auth::login).
    public function buscarPorEmail(string $email): ?Usuario
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $r = $stmt->fetch();
            return $r ? $this->hidratar($r) : null;
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível consultar o usuário.');
        }
    }

    // ------------------------------ READ (vários) ------------------------------
    public function listarTodos(): array
    {
        try {
            $lista = [];
            foreach ($this->pdo->query('SELECT * FROM usuarios ORDER BY nome')->fetchAll() as $linha) {
                $lista[] = $this->hidratar($linha);
            }
            return $lista;
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível listar os usuários.');
        }
    }

    // ------------------------------ UPDATE ------------------------------
    public function atualizar(object $obj): bool
    {
        $this->garantir($obj);
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, nivel = :nivel WHERE id = :id'
            );
            return $stmt->execute([
                ':nome'  => $obj->getNome(),
                ':email' => $obj->getEmail(),
                ':senha' => $obj->getSenhaHash(),
                ':nivel' => $obj->getNivel(),
                ':id'    => $obj->getId(),
            ]);
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível atualizar o usuário.');
        }
    }

    // ------------------------------ DELETE ------------------------------
    public function excluir(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM usuarios WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->falhar($e, 'Não foi possível excluir o usuário.');
        }
    }
}
