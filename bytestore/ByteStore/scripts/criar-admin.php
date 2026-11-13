<?php
// =====================================================================
// SCRIPT: criar-admin.php  (roda pelo TERMINAL, não pelo navegador)
// =====================================================================
// Cria um administrador NOVO ou troca a senha de um que já existe.
// Útil quando: você esqueceu a senha, ou quer um admin com a SUA senha
// (em vez da senha de exemplo do schema.sql).
//
// COMO USAR (dentro da pasta do projeto):
//     php scripts/criar-admin.php "Seu Nome" seuemail@exemplo.com suasenha
// =====================================================================

// Este arquivo só pode rodar pelo terminal (CLI), nunca por uma página web.
if (PHP_SAPI !== 'cli') {
    exit('Este script só funciona pelo terminal.');
}

// $argv = lista do que foi digitado depois de "php scripts/criar-admin.php".
if (count($argv) < 4) {
    exit("Uso: php scripts/criar-admin.php \"Nome\" email senha\n");
}
[$arquivo, $nome, $email, $senha] = $argv;

require_once __DIR__ . '/../app/includes/iniciar.php';

try {
    // Usuario::comSenha valida os dados e já cria o hash da senha.
    $novo = Usuario::comSenha($nome, $email, $senha);
    $dao  = new UsuarioDAO();

    // Já existe alguém com esse e-mail? Então atualiza (troca nome e senha). Se não, cria.
    $existente = $dao->buscarPorEmail($email);
    if ($existente !== null) {
        $dao->atualizar(new Usuario($existente->getId(), $nome, $email, $novo->getSenhaHash(), 'admin'));
        echo "Administrador atualizado: $email\n";
    } else {
        $dao->inserir($novo);
        echo "Administrador criado: $email\n";
    }
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage() . "\n";
    exit(1);
}
