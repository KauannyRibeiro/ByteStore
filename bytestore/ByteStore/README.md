# ByteStore

E-commerce simplificado de hardware e periféricos, feito em **PHP puro (POO) + PDO + MySQL**.
Projeto da disciplina de Programação II (UNOESC). Integrantes: Kauanny, Matheus, Maria.

- **Loja (cliente):** vitrine com filtro por categoria e busca por nome, página de detalhes com especificações,
  carrinho simulado (na sessão).
- **Painel (administrador):** login seguro e CRUD completo de produtos e categorias.

> **Primeira vez aqui?** Leia o [`docs/GUIA-COMPLETO.md`](docs/GUIA-COMPLETO.md): ele explica passo a passo
> como preparar o computador, rodar o projeto, usar o GitHub e entender o código.

## Como rodar (resumo)

Você precisa de **PHP 8.0+** (com as extensões `pdo_mysql` e `mbstring`) e **MySQL** instalados.

```bash
# 1) Configuração: copie o modelo e coloque a senha do SEU MySQL em db_senha
copy config\config.exemplo.php config\config.php        # Windows (cmd)
# cp config/config.exemplo.php config/config.php        # Linux / Mac

# 2) Banco de dados (cria o banco "bytestore" com dados de exemplo)
mysql -u root -p < database/schema.sql                   # no cmd (no PowerShell, veja o guia)

# 3) Servidor (a pasta pública é "public")
php -S localhost:8000 -t public
```

Abra **http://localhost:8000**.
Painel: **http://localhost:8000/admin/login.php** com `admin@bytestore.com` / `admin123` (troque depois:
`php scripts/criar-admin.php "Seu Nome" seu@email.com suasenha`).

No Windows você também pode dar dois cliques em `iniciar-servidor.bat`.

## Estrutura de pastas

```
ByteStore/
├── public/                 ÚNICA pasta acessível pelo navegador
│   ├── index.php           Vitrine
│   ├── produto.php         Detalhes do produto
│   ├── carrinho.php        Carrinho
│   ├── admin/              Painel (login, painel, produtos, categorias)
│   └── assets/css/         base.css (comum), site.css (loja), admin.css (painel)
├── app/
│   ├── classes/            Todas as classes (uma por arquivo)
│   └── includes/           iniciar.php e os layouts (site e painel)
├── config/                 config.exemplo.php (modelo) e config.php (SUA senha - não vai ao Git)
├── database/schema.sql     Tabelas + dados de exemplo
├── docs/                   Guia completo e diagramas
├── scripts/criar-admin.php Cria/atualiza administrador pelo terminal
└── logs/                   Log do sistema (gerado em execução)
```

## Requisitos da disciplina x onde estão no código

| Requisito | Onde |
|---|---|
| Encapsulamento | Atributos `protected`/`private` + getters/setters com validação em `Produto`, `Hardware`, `Periferico`, `Categoria`, `ItemCarrinho`, `Usuario` |
| Herança | `Hardware` e `Periferico` estendem `Produto`; os DAOs estendem `BaseDAO` |
| Polimorfismo | `exibirDetalhesEspecificos()`, `getEspecificacoes()`, `getGarantiaMeses()`, `calcularFrete()` e `getAtributosEspecificos()` sobrescritos nas filhas; usados em `index.php`, `produto.php`, `Carrinho::getFrete()` e `ProdutoDAO` |
| Abstração | `Produto` e `BaseDAO` são classes abstratas |
| Interface | `RepositorioInterface`, implementada por `ProdutoDAO`, `CategoriaDAO` e `UsuarioDAO` |
| Trait | `LogavelTrait`, usada em `Produto` e `BaseDAO` |
| Associação | `Produto` → `Categoria`; `ItemCarrinho` → `Produto` |
| Agregação | `Categoria` agrega `Produto`s (`consultarComProdutos()`) |
| Composição | `Carrinho` cria e controla os `ItemCarrinho` |
| PDO + MySQL + CRUD | `app/classes/*DAO.php` e telas em `public/admin/` |
| Consultas parametrizadas | Todo acesso ao banco usa `prepare()` + `execute()` |
| Validação de entradas | Setters das classes, `filter_var` no login, `(int)` nos parâmetros da URL |
| Exceções | `try/catch` nos DAOs e nas páginas; erro técnico vai para `logs/sistema.log`, o usuário vê mensagem amigável |
| Senhas | `password_hash()` / `password_verify()` |
| Segurança extra | Token CSRF em todos os formulários, `htmlspecialchars` (XSS), `session_regenerate_id`, cookie de sessão `httponly` + `samesite`, redirecionamento só para endereços internos |
| Documentação | Este README, `docs/GUIA-COMPLETO.md`, `docs/diagramas.md` e `database/schema.sql` |

## Git

O `.gitignore` já impede que `config/config.php` (sua senha) e os logs sejam enviados.
O passo a passo completo do GitHub está no guia, seção 3.
