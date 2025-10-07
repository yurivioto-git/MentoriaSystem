<?php
// Inicia o buffer de saída para evitar problemas com headers
ob_start();

// Garante que o bootstrap da aplicação seja carregado
if (!defined('PROJECT_ROOT')) {
    require_once __DIR__ . '/../bootstrap.php';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) : 'Sistema de Mentoria'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Etec Jales Mentoria</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Olá, <?php echo e($_SESSION['user_nome']); ?>!
                        </span>
                    </li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">Dashboard Admin</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="aluno_dashboard.php">Meu Dashboard</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sair</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Registrar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container">
    <?php
    // Exibe mensagens de erro ou sucesso passadas via GET
    if (isset($_GET['error'])) {
        $errorMessage = '';
        switch ($_GET['error']) {
            case 'invalid_file_type':
                $errorMessage = 'Tipo de Arquivo Inválido! Aceito somente os tipos DOC e DOCX.';
                break;
            case 'submission_exists':
                $errorMessage = 'Você já enviou o Apêndice 5 para este bimestre.';
                break;
            case 'upload_failed':
                $errorMessage = 'Ocorreu um erro ao enviar o arquivo. Tente novamente.';
                break;
            case 'upload_error':
                $errorMessage = 'Ocorreu um erro no upload. Tente novamente.';
                break;
            default:
                $errorMessage = urldecode($_GET['error']);
                break;
        }
        if ($errorMessage) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($errorMessage) . '</div>';
        }
    }
    if (isset($_GET['success'])):
        $successMessages = [
            'registered' => 'Cadastro realizado com sucesso! Faça o login.',
            'logout' => 'Você saiu do sistema.'
        ];
        $msgKey = urldecode($_GET['success']);
        $msg = $successMessages[$msgKey] ?? e($msgKey);
    ?>
        <div class="alert alert-success" role="alert">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>
