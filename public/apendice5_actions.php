<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\Apendice5Controller;

$action = $_GET['action'] ?? 'filter';
$bimestre = $_GET['bimestre_ap5'] ?? null;
$status = $_GET['status_ap5'] ?? null;

if ($action === 'download_all') {
    $controller = new Apendice5Controller();
    $controller->downloadAll($bimestre);
} else {
    // Redirect back to the filtered view
    $queryParams = [
        'view' => 'apendice5',
        'bimestre_ap5' => $bimestre,
        'status_ap5' => $status
    ];

    // Remove null values to keep the URL clean
    $queryParams = array_filter($queryParams);

    $queryString = http_build_query($queryParams);
    header("Location: admin_dashboard.php?{$queryString}");
    exit();
}
