<?php

require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\FileController;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $fileController = new FileController();
    $fileController->serve($id);
} else {
    http_response_code(400);
    die('ID do arquivo inválido.');
}
