<?php
// =====================================================================
// CLASSE: Auth  (autenticação = "quem é você?")
// =====================================================================
// Cuida do login e logout do administrador. O login fica guardado na
// SESSÃO: um espaço no servidor que lembra o usuário entre uma página e
// outra (o navegador só guarda um cookie com o número da sessão).
// =====================================================================
final class Auth
{
    // Tenta entrar. Devolve true se o e-mail e a senha conferem.
    public static function login(string $email, string $senha): bool
    {
        // Busca o usuário pelo e-mail (consulta parametrizada dentro do DAO).
        $usuario = (new UsuarioDAO())->buscarPorEmail(trim($email));

        // password_verify compara a senha digitada com o HASH guardado no banco.
        // (A senha original nunca é guardada: só o hash, que não dá para "desfazer".)
        if ($usuario === null || !$usuario->verificarSenha($senha)) {
            return false;
        }

        // Gera um novo número de sessão. Evita o ataque de "fixação de sessão".
        session_regenerate_id(true);

        // Guarda na sessão QUEM está logado. Nunca guardamos a senha aqui.
        $_SESSION['usuario'] = [
            'id'    => $usuario->getId(),
            'nome'  => $usuario->getNome(),
            'nivel' => $usuario->getNivel(),
        ];
        return true;
    }

    // Sai: apaga os dados da sessão E o cookie dela.
    public static function logout(): void
    {
        $_SESSION = []; // esvazia os dados

        // Apaga o cookie da sessão no navegador (coloca uma data de validade no passado).
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy(); // destrói a sessão no servidor
    }

    // Dados de quem está logado (array) ou null se ninguém estiver.
    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    // Nome de quem está logado (ou texto vazio).
    public static function nome(): string
    {
        return (string) (self::usuario()['nome'] ?? '');
    }

    // true se há alguém logado E essa pessoa é administradora.
    public static function estaLogado(): bool
    {
        return self::usuario() !== null && self::usuario()['nivel'] === 'admin';
    }

    // Coloque no TOPO de toda página restrita: quem não é admin volta para o login.
    public static function exigirAdmin(): void
    {
        if (!self::estaLogado()) {
            Util::redirecionar(Util::url('admin/login.php'));
        }
    }
}
