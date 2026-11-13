<?php
// =====================================================================
// ARQUIVO DE CONFIGURAÇÃO (modelo)
// =====================================================================
// COMO USAR:
//   1) Copie este arquivo e dê o nome de  config.php  (na mesma pasta).
//   2) Abra o config.php e ajuste os dados do SEU MySQL.
//
// POR QUE EXISTE ESTE MODELO?
//   O config.php tem a sua senha do banco e por isso NÃO vai para o GitHub
//   (está no .gitignore). O config.exemplo.php vai, para quem baixar o
//   projeto saber o que precisa preencher.
//
// Este arquivo "devolve" (return) um array: uma lista de  chave => valor.
// =====================================================================

return [
    // ---------- Dados do MySQL ----------
    'db_host'    => '127.0.0.1',  // endereço do servidor MySQL (127.0.0.1 = este mesmo computador)
    'db_porta'   => 3306,         // porta padrão do MySQL
    'db_nome'    => 'bytestore',  // nome do banco (criado pelo database/schema.sql)
    'db_usuario' => 'root',       // usuário do MySQL
    'db_senha'   => '',           // senha do MySQL (a que você definiu na instalação)

    // ---------- Site ----------
    // Parte do endereço que vem ANTES das páginas.
    // Deixe '' (vazio) quando rodar com:  php -S localhost:8000 -t public
    // Se o site ficar numa subpasta (ex.: http://servidor/ByteStore), use '/ByteStore'.
    'url_base'   => '',

    // 'desenvolvimento' mostra os erros do PHP na tela (bom para estudar).
    // 'producao' esconde os erros (use quando o site estiver hospedado de verdade).
    'ambiente'   => 'desenvolvimento',
];
