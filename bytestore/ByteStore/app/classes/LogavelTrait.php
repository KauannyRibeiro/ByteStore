<?php
// =====================================================================
// TRAIT: LogavelTrait
// =====================================================================
// Uma trait é um "pedaço de código para copiar e colar" em várias classes
// SEM precisar de herança. Quem escreve  use LogavelTrait;  dentro da
// classe ganha o método registrarLog() como se tivesse escrito ele.
//
// Aqui usamos para gravar um histórico (log) das operações do sistema em
// logs/sistema.log. Útil para descobrir o que deu errado depois.
// =====================================================================
trait LogavelTrait
{
    // Grava uma linha no arquivo de log neste formato:
    //     [2026-11-10 14:30:00] [INFO] Produto #5 inserido: Mouse Gamer
    // $acao  = a mensagem
    // $nivel = INFO (normal) ou ERRO (algo falhou)
    public function registrarLog(string $acao, string $nivel = 'INFO'): void
    {
        // __DIR__ é a pasta deste arquivo (app/classes). Subimos 2 níveis até a raiz.
        $pasta = __DIR__ . '/../../logs';

        // Se a pasta de logs não existir, tenta criar.
        // O "@" na frente de uma função esconde avisos dela (um problema no log não pode quebrar a página).
        if (!is_dir($pasta)) {
            @mkdir($pasta, 0775, true);
        }

        // Troca quebras de linha por espaço: assim cada log ocupa uma linha só.
        $acao = str_replace(["\r", "\n"], ' ', $acao);

        // Monta a linha. O ponto (.) junta textos. PHP_EOL = "pular linha".
        $linha = '[' . date('Y-m-d H:i:s') . '] [' . $nivel . '] ' . $acao . PHP_EOL;

        // FILE_APPEND = escreve no FIM do arquivo, sem apagar o que já existe.
        @file_put_contents($pasta . '/sistema.log', $linha, FILE_APPEND);
    }
}
