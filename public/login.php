<?php

require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\AuthController;

$authController = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->login();
} else {
    $pageTitle = 'Login';
    include_once PROJECT_ROOT . '/src/Views/header.php';
    $authController->showLoginForm($_GET['error'] ?? null, $_GET['success'] ?? null);
    include_once PROJECT_ROOT . '/src/Views/footer.php';
}
