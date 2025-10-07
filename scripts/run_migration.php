<?php

// Roda como um script CLI, não via web server
if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando (CLI).");
}

// Inclui o bootstrap para ter acesso ao autoloader, .env e DB
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/src/Database.php';

use App\Database;

echo "Iniciando migração da tabela 'apendice5_submissions'...
";

try {
    $pdo = Database::getInstance();
    
    $sqlFile = dirname(__DIR__) . '/scripts/add_apendice5_table.sql';
    if (!file_exists($sqlFile)) {
        die("Arquivo de migração não encontrado: {$sqlFile}\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // O PDO exec pode não conseguir lidar com múltiplas queries ou a criação de tipos complexos.
    // É mais seguro executar as queries separadamente se houver problemas.
    $pdo->exec($sql);
    
    echo "Migração concluída com sucesso! Tabela 'apendice5_submissions' e tipo 'status_apendice5' criados.
";

} catch (PDOException $e) {
    die("Erro durante a migração: " . $e->getMessage() . "\n");
}

