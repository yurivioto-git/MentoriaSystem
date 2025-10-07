<?php

// Inclui o bootstrap para ter acesso ao autoloader, .env e DB
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/src/Database.php';

use App\Database;

echo "<h1>Iniciando migração...</h1>";

try {
    $pdo = Database::getInstance();
    
    $sqlFile = dirname(__DIR__) . '/scripts/add_apendice5_table.sql';
    if (!file_exists($sqlFile)) {
        die("<p>Arquivo de migração não encontrado: {$sqlFile}</p>");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Tenta criar o tipo ENUM primeiro, ignorando o erro se já existir.
    try {
        $pdo->exec("CREATE TYPE status_apendice5 AS ENUM ('pendente', 'aprovado', 'rejeitado');");
        echo "<p>Tipo 'status_apendice5' criado.</p>";
    } catch (PDOException $e) {
        if ($e->getCode() == '42710') { // 42710 is for duplicate_object in PostgreSQL
            echo "<p>Tipo 'status_apendice5' já existe, pulando.</p>";
        } else {
            throw $e; // Re-throw other errors
        }
    }

    // Executa a criação da tabela. O script original foi dividido para melhor tratamento de erro.
    $tableSql = "CREATE TABLE IF NOT EXISTS apendice5_submissions (
      id SERIAL PRIMARY KEY,
      user_id INT NOT NULL,
      bimestre_ref VARCHAR(10) NOT NULL, 
      file_path VARCHAR(255) NOT NULL,
      original_filename VARCHAR(255) NOT NULL,
      status status_apendice5 NOT NULL DEFAULT 'pendente',
      submission_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      validation_date TIMESTAMP NULL DEFAULT NULL,
      admin_notes TEXT DEFAULT NULL,
      CONSTRAINT apendice5_submissions_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    );";
    $pdo->exec($tableSql);
    echo "<p>Tabela 'apendice5_submissions' verificada/criada.</p>";

    // Cria o índice
    $indexSql = "CREATE INDEX IF NOT EXISTS idx_user_id_apendice5 ON apendice5_submissions (user_id);";
    $pdo->exec($indexSql);
    echo "<p>Índice 'idx_user_id_apendice5' verificado/criado.</p>";
    
    echo "<h2>Migração concluída com sucesso!</h2>";

} catch (PDOException $e) {
    die("<h2>Erro durante a migração:</h2><p>" . $e->getMessage() . "</p>");
}

echo "<p style='color:red;'><strong>AVISO:</strong> Este é um arquivo temporário e deve ser removido após o uso.</p>";
