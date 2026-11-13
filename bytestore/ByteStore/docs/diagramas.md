# Diagramas do ByteStore

Os diagramas estão em **Mermaid** (o GitHub desenha sozinho ao abrir este arquivo).
Para entregar como imagem, cole o código em https://mermaid.live e exporte em PNG ou SVG.
O arquivo `mermaid-diagram.png` (nesta pasta) mostra o modelo principal: produtos, categoria, carrinho, DAOs, interface e trait.

## Diagrama de classes (modelo principal)

```mermaid
classDiagram
    direction TB

    class RepositorioInterface {
        <<interface>>
        +inserir(obj) int
        +consultar(id) object
        +listarTodos() array
        +atualizar(obj) bool
        +excluir(id) bool
    }
    class LogavelTrait {
        <<trait>>
        +registrarLog(acao, nivel)
    }

    class Produto {
        <<abstract>>
        #id
        #nome
        #preco
        #estoque
        #categoriaId
        +getTipo()* string
        +exibirDetalhesEspecificos()* string
        +getEspecificacoes()* array
        +getGarantiaMeses()* int
        +calcularFrete()* float
        +getAtributosEspecificos()* array
    }
    class Hardware {
        -socket
        -tdp
    }
    class Periferico {
        -dpi
        -conexao
    }
    class Categoria {
        -id
        -nome
        -descricao
        -produtos[]
        +adicionarProduto(p)
        +contarProdutos() int
    }
    class Carrinho {
        -itens[]
        +adicionar(p, qtd)
        +diminuir(id)
        +remover(id)
        +limpar()
        +getFrete() float
        +getTotal() float
    }
    class ItemCarrinho {
        -quantidade
        +getSubtotal() float
    }
    class Usuario {
        -nome
        -email
        -senhaHash
        -nivel
        +verificarSenha(senha) bool
    }

    class BaseDAO {
        <<abstract>>
        #pdo
        #falhar(e, msg)
    }
    class ProdutoDAO
    class CategoriaDAO
    class UsuarioDAO

    Produto <|-- Hardware : herança
    Produto <|-- Periferico : herança

    Produto "*" --> "1" Categoria : associação (pertence a)
    Categoria "1" o-- "*" Produto : agregação
    Carrinho "1" *-- "*" ItemCarrinho : composição
    ItemCarrinho "*" --> "1" Produto : associação

    BaseDAO <|-- ProdutoDAO : herança
    BaseDAO <|-- CategoriaDAO : herança
    BaseDAO <|-- UsuarioDAO : herança
    RepositorioInterface <|.. ProdutoDAO : implementa
    RepositorioInterface <|.. CategoriaDAO : implementa
    RepositorioInterface <|.. UsuarioDAO : implementa
    LogavelTrait <.. Produto : usa
    LogavelTrait <.. BaseDAO : usa
    UsuarioDAO ..> Usuario : cria
```

## Classes de apoio

```mermaid
classDiagram
    class Config {
        +get(chave, padrao)$
        +existe()$ bool
    }
    class Database {
        +conectar()$ PDO
    }
    class Auth {
        +login(email, senha)$ bool
        +logout()$
        +estaLogado()$ bool
        +exigirAdmin()$
    }
    class Csrf {
        +token()$ string
        +campo()$ string
        +validar(token)$ bool
    }
    class Util {
        +e(texto)$ string
        +moeda(valor)$ string
        +url(caminho)$ string
        +urlSegura(url, padrao)$ string
        +flash(tipo, msg)$
    }
    class ProdutoFactory {
        +criar(dados)$ Produto
    }

    Database ..> Config : lê
    Auth ..> UsuarioDAO : usa
    ProdutoDAO ..> ProdutoFactory : usa
```

## Modelo entidade-relacionamento (banco de dados)

```mermaid
erDiagram
    CATEGORIAS ||--o{ PRODUTOS : "possui"
    CATEGORIAS {
        int id PK
        varchar nome UK
        text descricao
    }
    PRODUTOS {
        int id PK
        varchar nome
        text descricao
        decimal preco
        int estoque
        int categoria_id FK
        enum tipo "hardware | periferico"
        varchar socket
        int tdp
        int dpi
        varchar conexao
    }
    USUARIOS {
        int id PK
        varchar nome
        varchar email UK
        varchar senha "hash bcrypt"
        varchar nivel
    }
```

## Onde cada relacionamento está no código

| Relacionamento | Onde |
|---|---|
| Associação | `Produto::$categoria` (`app/classes/Produto.php`) e `ItemCarrinho::$produto` |
| Agregação | `Categoria::$produtos` e `adicionarProduto()` (`app/classes/Categoria.php`) |
| Composição | `Carrinho` cria e controla os `ItemCarrinho` (`app/classes/Carrinho.php`) |
