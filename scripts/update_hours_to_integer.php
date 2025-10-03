<?php

require_once __DIR__ . '/../src/bootstrap.php';

use App\Database;

try {
    $db = Database::getInstance();

    // Buscar todas as horas
    $stmt = $db->query("SELECT id, quantidade_horas FROM horas");
    $horas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updateStmt = $db->prepare("UPDATE horas SET quantidade_horas = :quantidade_horas WHERE id = :id");

    $updatedCount = 0;
    foreach ($horas as $hora) {
        $originalHours = (float)$hora['quantidade_horas'];
        $roundedHours = round($originalHours);

        if ($originalHours != $roundedHours) {
            $updateStmt->execute([
                ':quantidade_horas' => $roundedHours,
                ':id' => $hora['id']
            ]);
            $updatedCount++;
        }
    }

    echo "Atualização concluída!\n";
    echo "{$updatedCount} registros de horas foram arredondados para o inteiro mais próximo.\n";

} catch (Exception $e) {
    echo "Ocorreu um erro: " . $e->getMessage() . "\n";
}

