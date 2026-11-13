<?php
// =====================================================================
// CLASSE: Database
// =====================================================================
// Cria a conexão com o MySQL usando PDO (PHP Data Objects).
//
// PDO é a ferramenta padrão do PHP para falar com bancos de dados.
// A conexão é criada UMA vez e reaproveitada por todo o sistema
// (isso se chama Singleton): abrir conexão toda hora seria lento.
// =====================================================================
class Database
{
    // A conexão fica guardada aqui. null = ainda não conectou.
    private static ?PDO $conexao = null;

    // Devolve a conexão (cria na primeira vez que for chamado).
    public static function conectar(): PDO
    {
        if (self::$conexao === null) {
            // try/catch: tenta executar; se der erro, cai no catch SEM quebrar a página.
            try {
                // DSN = "endereço" do banco: driver mysql, servidor, porta, nome do banco e codificação.
                $dsn = 'mysql:host=' . Config::get('db_host', '127.0.0.1')
                     . ';port=' . Config::get('db_porta', 3306)
                     . ';dbname=' . Config::get('db_nome', 'bytestore')
                     . ';charset=utf8mb4'; // utf8mb4 = aceita acentos e emojis

                self::$conexao = new PDO(
                    $dsn,
                    Config::get('db_usuario', 'root'),
                    Config::get('db_senha', ''),
                    [
                        // Se o banco der erro, o PHP lança uma EXCEÇÃO (que podemos capturar com try/catch).
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        // Cada linha do banco vem como array associativo: ['nome' => 'Mouse', ...]
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        // false = usa prepared statements de verdade (mais seguro contra SQL Injection).
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                // O erro técnico (com detalhes) vai para o log do servidor...
                error_log('ByteStore - falha de conexão: ' . $e->getMessage());
                // ...e o usuário recebe só uma mensagem simples (sem expor senha/servidor).
                throw new RuntimeException('Não foi possível conectar ao banco de dados.', 0, $e);
            }
        }
        return self::$conexao;
    }
}
