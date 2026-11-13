<?php
// =====================================================================
// CLASSE: Usuario  (o administrador do painel)
// =====================================================================
// ENCAPSULAMENTO E SEGURANÇA DE SENHA:
// A senha NUNCA é guardada em texto puro, só o "hash" (um embaralhado de
// mão única, gerado por password_hash). Por isso NÃO existe getSenha():
// só dá para perguntar "esta senha confere?" (verificarSenha).
// =====================================================================
class Usuario
{
    private ?int $id;
    private string $nome;
    private string $email;
    private string $senhaHash; // o hash, nunca a senha original
    private string $nivel;     // 'admin' pode entrar no painel

    public function __construct(?int $id, string $nome, string $email, string $senhaHash, string $nivel = 'admin')
    {
        $nome  = trim($nome);
        $email = trim($email);

        // Validações: o objeto não aceita dados inválidos.
        if ($nome === '') {
            throw new InvalidArgumentException('O nome do usuário é obrigatório.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // filter_var checa o formato do e-mail
            throw new InvalidArgumentException('E-mail inválido.');
        }

        $this->id        = $id;
        $this->nome      = $nome;
        $this->email     = $email;
        $this->senhaHash = $senhaHash;
        $this->nivel     = $nivel;
    }

    // Cria um usuário NOVO a partir da senha digitada, já aplicando o hash.
    public static function comSenha(string $nome, string $email, string $senha, string $nivel = 'admin'): self
    {
        if (strlen($senha) < 6) {
            throw new InvalidArgumentException('A senha deve ter pelo menos 6 caracteres.');
        }
        // password_hash cria o hash seguro (bcrypt). PASSWORD_DEFAULT = o algoritmo recomendado.
        return new self(null, $nome, $email, password_hash($senha, PASSWORD_DEFAULT), $nivel);
    }

    // Compara a senha digitada com o hash guardado. Devolve true/false.
    public function verificarSenha(string $senha): bool
    {
        return password_verify($senha, $this->senhaHash);
    }

    // ---------- Getters ----------
    public function getId(): ?int          { return $this->id; }
    public function getNome(): string      { return $this->nome; }
    public function getEmail(): string     { return $this->email; }
    public function getSenhaHash(): string { return $this->senhaHash; } // é o HASH (usado pelo DAO para gravar)
    public function getNivel(): string     { return $this->nivel; }
}
