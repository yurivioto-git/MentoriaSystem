<?php

require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\AuthController;

$authController = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->register();
} else {
    $pageTitle = 'Registrar Aluno';
    include_once PROJECT_ROOT . '/src/Views/header.php';
    $authController->showRegistrationForm($_GET['error'] ?? null);
    include_once PROJECT_ROOT . '/src/Views/footer.php';
}
