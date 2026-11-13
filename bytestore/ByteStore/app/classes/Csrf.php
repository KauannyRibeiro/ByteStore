<?php
// =====================================================================
// CLASSE: Csrf  (o "token" de segurança dos formulários)
// =====================================================================
// O PROBLEMA (ataque CSRF): se você está logado como admin e abre um site
// malicioso, ele pode "disfarçar" um formulário que apaga produtos DO SEU
// sistema usando o seu login.
//
// A SOLUÇÃO: o servidor cria um código secreto aleatório (token), guarda
// na sessão e coloca escondido em TODO formulário. Quando o formulário
// volta, conferimos se o token é o mesmo. Um site malicioso não conhece
// esse código, então o pedido falso é recusado.
//
// COMO USAR:
//   no formulário:      chame Csrf::campo() dentro do <form> (com echo)
//   na página que recebe: if (!Csrf::validar($_POST['csrf'] ?? null)) { ... recusar ... }
// =====================================================================
final class Csrf
{
    // Cria o token (uma vez por sessão) e devolve o valor.
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            // random_bytes gera bytes aleatórios seguros; bin2hex transforma em texto legível.
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    // Devolve o campo escondido pronto para colocar dentro do <form>.
    public static function campo(): string
    {
        return '<input type="hidden" name="csrf" value="' . Util::e(self::token()) . '">';
    }

    // Confere se o token recebido é igual ao guardado na sessão.
    // hash_equals compara de um jeito seguro (resistente a ataques de tempo).
    public static function validar(?string $token): bool
    {
        return isset($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
    }
}
