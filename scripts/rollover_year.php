<?php

// Roda como um script CLI, não via web server
if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando (CLI).");
}

// Inclui o bootstrap para ter acesso ao autoloader, .env e DB
require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Controllers\YearRollController;

// Verifica por argumentos CLI, como --dry-run
$options = getopt("", ["dry-run"]);
$isDryRun = isset($options['dry-run']);

// Instancia e executa o controlador
$controller = new YearRollController();
$controller->run($isDryRun);
