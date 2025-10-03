<?php

require_once __DIR__ . '/../src/bootstrap.php';

if (is_logged_in()) {
    if (is_admin()) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: aluno_dashboard.php');
    }
} else {
    header('Location: login.php');
}
exit();
