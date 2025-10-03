<?php

require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\AuthController;

// Apenas usuários logados podem deslogar
require_auth();

$authController = new AuthController();
$authController->logout();
