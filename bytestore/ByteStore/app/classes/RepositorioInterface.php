<?php
// =====================================================================
// INTERFACE: RepositorioInterface
// =====================================================================
// Uma interface é um CONTRATO: ela só lista os métodos que uma classe
// é OBRIGADA a ter, sem dizer COMO fazer cada um.
//
// Toda classe que conversa com o banco (os "DAOs") assina este contrato
// com "implements RepositorioInterface". Assim, ProdutoDAO, CategoriaDAO
// e UsuarioDAO têm sempre os mesmos 5 métodos do CRUD.
//
// CRUD = Create (criar), Read (ler), Update (atualizar), Delete (excluir).
// =====================================================================
interface RepositorioInterface
{
    // CREATE: grava um novo registro e devolve o id que o banco gerou
    public function inserir(object $obj): int;

    // READ: busca UM registro pelo id (devolve null se não existir)
    public function consultar(int $id): ?object;

    // READ: devolve a lista de todos os registros
    public function listarTodos(): array;

    // UPDATE: altera um registro que já existe
    public function atualizar(object $obj): bool;

    // DELETE: apaga o registro com o id informado
    public function excluir(int $id): bool;
}
