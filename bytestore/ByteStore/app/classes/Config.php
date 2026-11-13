<?php
// =====================================================================
// CLASSE: Config
// =====================================================================
// Lê o arquivo config/config.php (dados do banco, url_base, ambiente).
// Qualquer classe pode pedir um valor assim:
//     Config::get('db_nome')
//
// "static" significa que o método pertence à CLASSE, não a um objeto:
// por isso chamamos com  Config::get()  (dois-pontos duplo) em vez de
// criar um objeto com "new".
// =====================================================================
final class Config
{
    // Guarda o conteúdo do arquivo depois de lido (para ler o arquivo só uma vez).
    // "?array" = pode ser um array ou null (null = ainda não foi lido).
    private static ?array $dados = null;

    // Devolve o valor de uma chave. Se a chave não existir, devolve $padrao.
    public static function get(string $chave, $padrao = null)
    {
        if (self::$dados === null) {
            self::carregar();
        }
        // "??" = use o valor da esquerda; se não existir, use o da direita.
        return self::$dados[$chave] ?? $padrao;
    }

    // Verifica se o arquivo de configuração existe (usado pelo iniciar.php).
    public static function existe(): bool
    {
        return is_file(self::caminho());
    }

    // Caminho do arquivo: raiz do projeto + /config/config.php
    private static function caminho(): string
    {
        return __DIR__ . '/../../config/config.php';
    }

    // Lê o arquivo. Ele faz "return [ ... ]", então "require" devolve esse array.
    private static function carregar(): void
    {
        if (!self::existe()) {
            throw new RuntimeException(
                'Arquivo config/config.php não encontrado. '
                . 'Copie config/config.exemplo.php para config/config.php e ajuste os dados do MySQL.'
            );
        }
        self::$dados = require self::caminho();
    }
}
