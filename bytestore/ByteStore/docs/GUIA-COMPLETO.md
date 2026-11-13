# Guia completo do ByteStore

Este guia foi escrito para quem está começando. Leia na ordem; cada parte termina com algo que você consegue testar.

**Índice**

0. [Git, GitHub e "rodar o sistema": o que é cada coisa](#0-git-github-e-rodar-o-sistema-o-que-é-cada-coisa)
1. [Preparar o computador (PHP em C:\php, MySQL, Git, VS Code)](#1-preparar-o-computador-windows)
2. [Rodar o projeto no seu computador](#2-rodar-o-projeto-no-seu-computador)
3. [GitHub passo a passo](#3-github-passo-a-passo)
4. [Rodar sem depender do seu notebook](#4-rodar-sem-depender-do-seu-notebook)
5. [Como o código funciona](#5-como-o-código-funciona)
6. [O que mudou nesta versão](#6-o-que-mudou-nesta-versão)
7. [Checklist de testes](#7-checklist-de-testes)
8. [Problemas comuns](#8-problemas-comuns)

---

## 0. Git, GitHub e "rodar o sistema": o que é cada coisa

| Coisa | O que é | O que faz |
|---|---|---|
| **Git** | Programa instalado no seu PC | Guarda o **histórico** do código (cada "commit" é um ponto salvo) |
| **GitHub** | Site | Guarda uma **cópia do repositório na nuvem**: backup, compartilhamento com o grupo, entrega ao professor |
| **Servidor PHP + MySQL** | Programas que **executam** o sistema | Fazem o site funcionar de verdade |

**Ponto importante:** o GitHub **guarda** o código, mas **não executa PHP nem MySQL**. O recurso de sites do GitHub
(GitHub Pages) só serve páginas estáticas (HTML/CSS/JS), e o ByteStore precisa de PHP e de banco de dados.
Por isso "subir para o GitHub" resolve o **backup** (se o notebook quebrar, o código está salvo), mas para o sistema
**funcionar sem o seu notebook** é preciso um lugar que rode PHP + MySQL. As opções estão na [seção 4](#4-rodar-sem-depender-do-seu-notebook).

---

## 1. Preparar o computador (Windows)

### 1.1 PHP em `C:\php`

**Conferir se funciona.** Abra o terminal do VS Code (`Ctrl+'`) ou o PowerShell e digite:

```
C:\php\php.exe -v
```

Deve aparecer a versão (use **PHP 8.0 ou superior**).

**Colocar o PHP no PATH** (para digitar só `php` em vez de `C:\php\php.exe`):

1. Tecla Windows, digite **"variáveis de ambiente"** e abra *Editar as variáveis de ambiente do sistema*.
2. Clique em **Variáveis de Ambiente**.
3. Em *Variáveis do usuário*, selecione **Path** → **Editar** → **Novo** → digite `C:\php` → **OK** em tudo.
4. **Feche e abra de novo** o VS Code/terminal e teste: `php -v`.

**Ativar as extensões** (o PHP baixado vem com elas desligadas):

1. Na pasta `C:\php`, copie o arquivo `php.ini-development` e nomeie a cópia como **`php.ini`**.
2. Abra o `php.ini` e procure estas linhas. Se tiverem `;` no começo, **apague o `;`**:

   ```
   extension_dir = "ext"
   extension=mbstring
   extension=pdo_mysql
   ```
3. Salve e confira no terminal:

   ```
   php -m
   ```

   Na lista precisam aparecer **`mbstring`**, **`PDO`** e **`pdo_mysql`**.

### 1.2 MySQL

Confira se o MySQL está instalado e **ligado** (no Windows ele costuma rodar como serviço chamado `MySQL80`;
você pode ver em `services.msc`). Anote o **usuário** (geralmente `root`) e a **senha** que você definiu na instalação.

Para usar o comando `mysql` no terminal, a pasta `bin` do MySQL precisa estar no PATH (do mesmo jeito do PHP), por exemplo
`C:\Program Files\MySQL\MySQL Server 8.0\bin`. Se preferir, use o **MySQL Workbench** (interface gráfica) para rodar o script do banco.

### 1.3 Git

1. Baixe e instale em https://git-scm.com (pode deixar as opções padrão).
2. Reabra o terminal e teste: `git --version`.
3. Diga ao Git quem é você (uma vez só):

   ```
   git config --global user.name "Seu Nome"
   git config --global user.email "seu-email-do-github@exemplo.com"
   ```

### 1.4 VS Code

1. Abra a pasta do projeto: **File → Open Folder** → escolha a pasta `ByteStore`.
2. O VS Code vai sugerir instalar extensões (por causa do arquivo `.vscode/extensions.json`). Aceite pelo menos a
   **PHP Intelephense** (ajuda a entender e achar erros no código).
3. Para rodar o servidor: **Terminal → Run Build Task** (`Ctrl+Shift+B`), ou digite no terminal o comando da seção 2.

---

## 2. Rodar o projeto no seu computador

Faça na ordem. Todos os comandos são dados **dentro da pasta do projeto** (no VS Code o terminal já abre nela).

### Passo 1: criar o arquivo de configuração

O arquivo `config/config.php` guarda a senha do seu banco e **não vai para o GitHub**. Você cria a partir do modelo:

```
copy config\config.exemplo.php config\config.php        (no cmd)
Copy-Item config\config.exemplo.php config\config.php   (no PowerShell)
```

Abra o `config/config.php` e ajuste **`db_senha`** (e `db_usuario`, se não for `root`).

### Passo 2: criar o banco de dados

```
mysql -u root -p < database/schema.sql
```

Digite a senha quando pedir. Isso cria o banco `bytestore`, as tabelas e dados de exemplo.

> **Atenção (PowerShell):** o símbolo `<` **não funciona** no PowerShell. Use uma destas alternativas:
>
> * abra o **Prompt de Comando** (cmd) e rode o comando acima; ou
> * no PowerShell: `cmd /c "mysql -u root -p < database\schema.sql"`; ou
> * entre no MySQL (`mysql -u root -p`) e digite: `source C:/caminho/para/ByteStore/database/schema.sql` (use `/` e não `\`); ou
> * use o MySQL Workbench: *File → Open SQL Script*, escolha o `schema.sql` e clique no raio.

### Passo 3: ligar o servidor

```
php -S localhost:8000 -t public
```

* `php -S` liga o servidor embutido do PHP (não precisa de XAMPP).
* `-t public` diz que a **pasta pública é `public`**: o navegador só enxerga o que está lá. As pastas `app/` e `config/`
  (código e senha) ficam inacessíveis pelo navegador. Isso é uma proteção de segurança.
* Deixe o terminal aberto. Para parar: `Ctrl+C`.

No Windows, dar dois cliques em **`iniciar-servidor.bat`** faz o mesmo.

### Passo 4: abrir no navegador

| O que | Endereço |
|---|---|
| Loja | http://localhost:8000 |
| Painel do administrador | http://localhost:8000/admin/login.php |

Login de exemplo: **admin@bytestore.com** / **admin123**.

### Passo 5 (recomendado): criar o SEU administrador

```
php scripts/criar-admin.php "Seu Nome" seu@email.com suasenha
```

Se o e-mail já existir, o comando troca a senha. Serve também se você esquecer a senha.

---

## 3. GitHub passo a passo

### 3.1 Criar a conta e o repositório

1. Crie a conta em https://github.com (se ainda não tiver).
2. Clique em **New** (novo repositório). Nome: `ByteStore`.
3. Escolha **Public** (o professor abre sem convite) ou **Private** (depois você adiciona o professor em *Settings → Collaborators*).
4. **Não marque** "Add a README file" (o projeto já tem um). Clique em **Create repository**.

### 3.2 Enviar o projeto pela primeira vez

No terminal, **dentro da pasta ByteStore**:

```
git init
git add .
git status
```

Leia o resultado do `git status`: **`config/config.php` NÃO deve aparecer** na lista (o `.gitignore` o esconde de propósito).
Se aparecer, pare e confira se o arquivo `.gitignore` está na pasta.

```
git commit -m "ByteStore: versão inicial"
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/ByteStore.git
git push -u origin main
```

Troque `SEU_USUARIO` pelo seu usuário. Na primeira vez abre uma janela do navegador pedindo para entrar no GitHub: faça login e autorize.
(Se o Git pedir "senha" no terminal, a senha da conta **não funciona**; use o login pelo navegador ou crie um *Personal Access Token*
em *Settings → Developer settings → Personal access tokens*.)

Atualize a página do repositório no GitHub: os arquivos devem estar lá.

### 3.3 Dia a dia

Depois de mexer no código:

```
git add .
git commit -m "Descreva o que mudou"
git push
```

No VS Code você pode fazer o mesmo pelo painel **Source Control** (`Ctrl+Shift+G`): escreva a mensagem, clique em ✓ (commit) e depois em *Sync/Push*.

Antes de começar a trabalhar (principalmente em grupo):

```
git pull
```

### 3.4 Trabalhar em grupo

* No repositório: *Settings → Collaborators → Add people* (cada colega aceita o convite por e-mail).
* Cada colega baixa o projeto uma vez: `git clone https://github.com/SEU_USUARIO/ByteStore.git`
* Para evitar conflitos: sempre `git pull` antes de começar, e avisem o grupo de qual arquivo cada um vai editar.

### 3.5 Usar em outro computador

```
git clone https://github.com/SEU_USUARIO/ByteStore.git
cd ByteStore
```

Depois repita os **passos 1 a 4 da seção 2** (cada computador tem o seu `config/config.php` e o seu banco).

### 3.6 Por que o `config.php` fica de fora?

Ele tem a senha do banco. Se subir para o GitHub (principalmente em repositório público), qualquer pessoa vê.
Por isso o repositório leva só o **modelo** `config.exemplo.php`. Se você subir a senha por engano, **troque a senha do MySQL**.

### 3.7 Erros comuns do Git

| Mensagem | O que fazer |
|---|---|
| `fatal: not a git repository` | Você não está na pasta do projeto (ou esqueceu o `git init`). |
| `remote origin already exists` | `git remote set-url origin https://github.com/SEU_USUARIO/ByteStore.git` |
| `rejected ... fetch first` no push | Alguém enviou algo antes: rode `git pull --rebase origin main` e depois `git push`. |
| `Permission denied` / `Authentication failed` | Refaça o login no GitHub (ou use um Personal Access Token). |

---

## 4. Rodar sem depender do seu notebook

Como explicado na seção 0, o GitHub guarda o código, mas quem **executa** é um servidor com PHP e MySQL. Três caminhos:

### Opção A: GitHub Codespaces (um computador na nuvem, ligado ao seu repositório)

* É um VS Code no navegador, rodando numa máquina da nuvem, com o seu projeto dentro. Qualquer computador com internet serve.
* **Custo:** contas pessoais gratuitas têm uma cota mensal (120 core-hours e 15 GB de armazenamento por mês, no momento em que escrevi
  isto; isso dá cerca de 60 horas numa máquina de 2 núcleos). Estudantes que têm o *GitHub Student Developer Pack* recebem o plano Pro
  (180 core-hours). Confira os números atuais na página de preços do Codespaces.
* **Limite:** não é hospedagem 24 horas. O Codespace desliga depois de um tempo sem uso e o sistema sai do ar até você abri-lo de novo.
  Serve para **desenvolver e demonstrar** de qualquer lugar.

Passos (eu **não consegui testar** estes comandos no seu ambiente; se algum falhar, copie a mensagem de erro e me envie):

1. No repositório do GitHub: **Code → Codespaces → Create codespace on main**.
2. No terminal do Codespace (Linux), instale o banco e as extensões do PHP:

   ```
   sudo apt-get update
   sudo apt-get install -y mariadb-server php-mysql php-mbstring
   sudo service mariadb start
   php -m | grep -i -E "pdo_mysql|mbstring"
   ```
   O último comando precisa mostrar `mbstring` e `pdo_mysql`.
3. Crie o banco e um usuário para o sistema:

   ```
   sudo mysql < database/schema.sql
   sudo mysql -e "CREATE USER IF NOT EXISTS 'bytestore'@'localhost' IDENTIFIED BY 'bytestore123'; CREATE USER IF NOT EXISTS 'bytestore'@'127.0.0.1' IDENTIFIED BY 'bytestore123'; GRANT ALL PRIVILEGES ON bytestore.* TO 'bytestore'@'localhost'; GRANT ALL PRIVILEGES ON bytestore.* TO 'bytestore'@'127.0.0.1'; FLUSH PRIVILEGES;"
   ```
4. Crie e ajuste a configuração:

   ```
   cp config/config.exemplo.php config/config.php
   ```
   Abra `config/config.php` e coloque `'db_usuario' => 'bytestore'` e `'db_senha' => 'bytestore123'`.
5. Ligue o servidor:

   ```
   php -S 0.0.0.0:8000 -t public
   ```
   O Codespaces mostra um aviso de "porta 8000": clique em **Open in Browser**. Por padrão só **você** acessa. Para mostrar ao professor,
   na aba **Ports** mude a visibilidade da porta para **Public** (qualquer pessoa com o link entra; volte para Private depois).
6. Quando o Codespace é religado, o banco precisa ser ligado de novo: `sudo service mariadb start`.

### Opção B: hospedagem gratuita com PHP + MySQL

Existem hospedagens gratuitas que oferecem PHP e MySQL (uma delas, bastante citada, é a InfinityFree). Elas têm limites e as condições
mudam, então leia as regras do provedor antes. Para o ByteStore funcionar numa hospedagem assim você precisa de alguns ajustes:

* Importar o `schema.sql` pelo phpMyAdmin do provedor. Lá o nome do banco costuma vir com **prefixo** do provedor, então remova do script as linhas
  `CREATE DATABASE` e `USE` e selecione o banco certo antes de importar.
* No `config/config.php`, usar o **host, usuário, senha e nome do banco** que o painel do provedor mostrar (o host normalmente **não** é `127.0.0.1`),
  e mudar `'ambiente'` para `'producao'`.
* Como o provedor usa uma pasta pública própria (geralmente `htdocs`), é preciso decidir onde colocar `public/` e acertar o `url_base` do config.
  Todas as pastas fora de `public/` já têm um `.htaccess` que bloqueia o acesso direto em servidores Apache.

Eu **não testei** o ByteStore em nenhuma hospedagem. Se você escolher essa opção, me diga qual provedor e eu monto o passo a passo específico.

### Opção C: entregar o repositório ao professor

Para a entrega, o mais comum é o professor **baixar o repositório e rodar na própria máquina**. Por isso o `README.md` e este guia
trazem o passo a passo completo. Se quiser, grave um vídeo curto ou tire prints do sistema funcionando e anexe à entrega.

---

## 5. Como o código funciona

### 5.1 Mapa das pastas

```
public/   -> o que o navegador acessa (páginas .php e CSS)
app/      -> o "cérebro": classes (app/classes) e layouts (app/includes)
config/   -> configuração do banco (config.php, que não vai ao Git)
database/ -> script SQL do banco
```

### 5.2 O caminho de uma página (exemplo: abrir a vitrine)

```
Navegador pede  /index.php
   -> public/index.php começa e carrega app/includes/iniciar.php
        (iniciar.php: confere o config, carrega as classes e abre a sessão)
   -> index.php pede os produtos ao ProdutoDAO
        ProdutoDAO -> PDO -> MySQL (consulta parametrizada)
        MySQL devolve linhas -> ProdutoFactory transforma cada linha em objeto Hardware ou Periferico
   -> index.php monta o HTML (layout_site_topo.php + cartões + layout_site_rodape.php)
   -> o navegador recebe a página pronta
```

Regra de ouro do projeto: **a página nunca escreve SQL**. Ela pede ao DAO. E **só o DAO** fala com o banco.

### 5.3 Por onde ler o código (nesta ordem)

1. `app/includes/iniciar.php`: o que toda página carrega.
2. `app/classes/Produto.php`, `Hardware.php`, `Periferico.php`: o coração da POO (encapsulamento, herança, polimorfismo).
3. `app/classes/ProdutoDAO.php` e `BaseDAO.php`: como o PDO é usado com segurança.
4. `public/index.php` e `public/produto.php`: a loja usando os objetos.
5. `public/carrinho.php` e `app/classes/Carrinho.php`: o carrinho.
6. `public/admin/login.php` e `public/admin/produto_form.php`: login e CRUD.

Todo arquivo tem um comentário no topo dizendo **o que é** e **o que faz**, e os trechos difíceis têm comentários linha a linha.

### 5.4 Glossário rápido (as "esquisitices" do PHP)

| Você vê | Significa |
|---|---|
| `<?php ... ?>` | Começo/fim de código PHP dentro de um arquivo |
| `<?= $x ?>` | Atalho para "imprima o valor de `$x` aqui" |
| `$nome` | Variável (sempre começa com `$`) |
| `$obj->metodo()` | Chamar um método (ou ler um atributo) de um **objeto** |
| `Classe::metodo()` | Chamar um método **estático** (da classe, sem criar objeto) |
| `$this` | "Este próprio objeto", dentro da classe |
| `new Produto(...)` | Criar um objeto a partir de uma classe |
| `extends` | Herança: "é um" (Hardware **é um** Produto) |
| `implements` | Assinar um contrato (interface) |
| `use LogavelTrait;` (dentro da classe) | Importar os métodos de uma trait |
| `abstract` | Classe/método que serve de molde e não é usado diretamente |
| `public / protected / private` | Quem pode acessar: todos / a classe e suas filhas / só a classe |
| `?int`, `?string` | O valor pode ser desse tipo **ou** `null` |
| `try { } catch (Exception $e) { }` | Tenta executar; se der erro, trata sem quebrar a página |
| `throw new InvalidArgumentException(...)` | Lança um erro de propósito (ex.: dado inválido) |
| `$_GET`, `$_POST` | Dados que vêm da URL / de um formulário |
| `$_SESSION` | Memória do servidor para cada visitante (login e carrinho ficam aqui) |
| `require_once` | Carregar outro arquivo PHP uma única vez |
| `__DIR__` | Pasta onde o arquivo atual está |
| `??` | "Use o valor da esquerda; se não existir, use o da direita" |
| `===` | Igualdade estrita (compara valor **e** tipo) |

### 5.5 Onde está cada proteção de segurança

| Proteção | O que evita | Onde |
|---|---|---|
| Consultas parametrizadas (`prepare` + `execute`) | SQL Injection | `app/classes/*DAO.php` |
| `Util::e()` (htmlspecialchars) | XSS (script malicioso na tela) | Toda impressão de dados nas páginas |
| `password_hash` / `password_verify` | Vazamento de senhas | `Usuario.php` |
| Token CSRF (`Csrf::campo()` / `Csrf::validar()`) | Formulários forjados por outro site | Todo `<form method="post">` e as páginas que recebem POST |
| `session_regenerate_id` | Fixação de sessão | `Auth::login()` |
| Cookie `httponly` + `samesite` | Roubo/uso indevido do cookie de sessão | `app/includes/iniciar.php` |
| `Util::urlSegura()` | Redirecionar o cliente para site falso | `Util.php`, usado no carrinho |
| `Auth::exigirAdmin()` | Acesso ao painel sem login | Topo de toda página de `public/admin/` |
| `try/catch` + log | Mostrar erros técnicos ao usuário | DAOs e páginas |
| Pasta `public/` separada + `.htaccess` | Acesso direto ao código e à senha do banco | Estrutura do projeto |

---

## 6. O que mudou nesta versão

**Rodar sem XAMPP**
* Novo `config/config.exemplo.php` (a senha do banco não fica mais dentro do código).
* Estrutura com `public/` (o que o navegador vê) e `app/` (código). Servidor: `php -S localhost:8000 -t public`.
* Novos: `iniciar-servidor.bat`, `.vscode/` (tarefa para ligar o servidor) e `scripts/criar-admin.php`.

**Carrinho (bugs corrigidos)**
* **Antes:** ao clicar em "Adicionar", o cliente era jogado para a tela do carrinho e o botão "Voltar" do navegador passava por várias telas repetidas.
  **Agora:** adicionar mantém o cliente **na mesma página** (vitrine ou produto), com a mensagem "adicionado" e um link "Ver carrinho".
* **Antes:** não havia como voltar às compras. **Agora:** o botão **"Continuar comprando"** (no topo e no resumo) volta para a **última página da loja** que o cliente viu, com o mesmo filtro.
* **Antes:** só dava para remover o item todo. **Agora:** botões **−** e **+** para mudar a quantidade, **Remover** e **Esvaziar carrinho** (com confirmação).
* **Finalizar compra** leva de volta à vitrine com uma mensagem de sucesso, em vez de deixar o cliente num carrinho vazio.
* Todas as ações seguem o padrão "Post/Redirect/Get": apertar F5 não reenvia o formulário por engano.

**Painel administrativo (reorganizado)**
* Layout **totalmente separado** da loja: menu lateral escuro, cor verde-azulada, selo **ADMINISTRADOR** e o nome de quem está logado.
* Na loja, quando o administrador está logado, aparece uma **faixa escura** no topo ("Você está logado como ADMINISTRADOR... vendo a loja como cliente") com os botões **Voltar ao painel** e **Sair**.
* No painel, **"Ver a loja (como cliente)"** abre a loja em outra aba.
* Botão **Sair** em lugar fixo. Página de login própria.
* Painel inicial com boas-vindas, resumo e atalhos; formulários divididos em blocos; o botão de excluir categoria fica desativado se ela ainda tem produtos.

**Código**
* Todos os arquivos comentados e indentados; novos comentários explicam cada conceito.
* **Mantido:** todas as funcionalidades (busca, filtro, especificações, contagem por categoria, carrinho, CRUD), todos os requisitos e toda a segurança (incluindo o token CSRF).

---

## 7. Checklist de testes

Faça depois de rodar o projeto. Se algo falhar, anote a tela e a mensagem.

**Loja**
- [ ] A vitrine mostra os produtos de exemplo.
- [ ] Clicar numa categoria filtra; "Todas" e "Limpar filtros" voltam ao normal.
- [ ] Buscar por "ryzen" mostra só o processador.
- [ ] Abrir um produto mostra a tabela de especificações, garantia e frete.
- [ ] "Adicionar ao carrinho" **mantém você na mesma página** e o contador do menu sobe.
- [ ] No carrinho: **−**, **+**, **Remover**, **Esvaziar** e **Continuar comprando** funcionam.
- [ ] Tentar passar do estoque mostra a mensagem de estoque insuficiente.
- [ ] **Finalizar compra** volta à vitrine com mensagem de sucesso.

**Administrador**
- [ ] `admin/login.php` com senha errada mostra erro; com a senha certa entra no painel.
- [ ] O painel mostra o menu lateral escuro e o selo ADMINISTRADOR.
- [ ] Criar, editar e excluir **categoria** (excluir uma com produtos deve ser bloqueado).
- [ ] Criar, editar e excluir **produto** (testar um Hardware e um Periférico).
- [ ] "Ver a loja" abre a loja em outra aba, com a faixa escura do administrador.
- [ ] "Sair" volta para o login; abrir `admin/produtos.php` sem login manda para o login.

---

## 8. Problemas comuns

| Mensagem / sintoma | Causa e solução |
|---|---|
| `could not find driver` | A extensão `pdo_mysql` não está ativa. Veja a seção 1.1 (`php.ini`) e confira com `php -m`. |
| `Call to undefined function mb_strlen()` | Ative `extension=mbstring` no `php.ini`. |
| `Access denied for user 'root'` | Senha errada em `db_senha` no `config/config.php`. |
| `Unknown database 'bytestore'` | O script `database/schema.sql` ainda não foi executado. |
| `Connection refused` / não conecta | O MySQL não está ligado (serviço `MySQL80` em `services.msc`) ou a porta/host do config estão errados. |
| "Não foi possível conectar ao banco de dados." na tela | Mesmas causas acima; os detalhes técnicos aparecem no terminal onde o `php -S` está rodando. |
| `Failed to listen on localhost:8000` | A porta está em uso. Use outra: `php -S localhost:8001 -t public`. |
| Erro 404 em todas as páginas | O servidor foi ligado sem `-t public`. |
| Página sem CSS (feia) | Mesma causa do 404, ou `url_base` do config está errado. |
| Falta o arquivo de configuração | Você ainda não criou o `config/config.php` (seção 2, passo 1). |
| Login do admin não funciona | Rode `php scripts/criar-admin.php "Nome" email senha`. |
| "Sessão expirada" ao enviar formulário | A página ficou aberta muito tempo ou o cookie foi apagado. Recarregue e envie de novo. |
| Tela branca | Em `config.php` deixe `'ambiente' => 'desenvolvimento'` e olhe o terminal do `php -S` e o arquivo `logs/sistema.log`. |
