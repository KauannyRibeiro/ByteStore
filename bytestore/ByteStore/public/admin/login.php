<?php
// =====================================================================
// PÁGINA: LOGIN DO ADMINISTRADOR   (endereço: /admin/login.php)
// =====================================================================
// O que faz: valida e-mail e senha e abre a sessão do administrador.
// Tem layout próprio (não usa o topo da loja nem o do painel), porque
// ainda não estamos "dentro" de nenhum dos dois.
// =====================================================================

require_once __DIR__ . '/../../app/includes/iniciar.php';

// Se já está logado, não faz sentido ver o login: vai direto ao painel.
if (Auth::estaLogado()) {
    Util::redirecionar(Util::url('admin/index.php'));
}

$erro  = null; // mensagem de erro para mostrar
$email = '';   // guarda o e-mail digitado (para não precisar digitar de novo se errar)

// ---------- Quando o formulário é enviado (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    try {
        // 1) Token CSRF
        if (!Csrf::validar($_POST['csrf'] ?? null)) {
            throw new InvalidArgumentException('Sessão expirada. Recarregue a página e tente de novo.');
        }
        // 2) Validação da entrada
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
            throw new InvalidArgumentException('Informe um e-mail válido e a senha.');
        }
        // 3) Tenta entrar (consulta parametrizada + password_verify)
        if (Auth::login($email, $senha)) {
            Util::redirecionar(Util::url('admin/index.php'));
        }
        // Mensagem única de propósito: não revelamos se foi o e-mail ou a senha que errou.
        $erro = 'E-mail ou senha inválidos.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Painel ByteStore</title>
    <link rel="stylesheet" href="<?= Util::url('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= Util::url('assets/css/admin.css') ?>">
</head>
<body class="admin login-pagina">

    <div class="login-caixa">
        <div class="login-logo">Byte<span>Store</span></div>
        <p class="login-subtitulo">PAINEL ADMINISTRATIVO</p>
        <p class="login-aviso">Acesso restrito a administradores.</p>

        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><?= Util::e($erro) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= Util::url('admin/login.php') ?>">
            <?= Csrf::campo() ?>

            <div class="campo">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= Util::e($email) ?>" required autofocus>
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <button class="btn btn-bloco" type="submit">Entrar</button>
        </form>

        <p class="login-voltar"><a href="<?= Util::url('index.php') ?>">&larr; Voltar para a loja</a></p>
    </div>

</body>
</html>
