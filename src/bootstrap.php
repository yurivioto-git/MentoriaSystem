<?php

declare(strict_types=1);

// Define o root do projeto
define('PROJECT_ROOT', dirname(__DIR__));

// Autoloader do Composer
require_once PROJECT_ROOT . '/vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env
// Em um projeto real, usaríamos uma biblioteca como vlucas/phpdotenv
// Por simplicidade, vamos fazer um parse manual.
function load_env(string $path): void
{
    if (!file_exists($path) || !is_readable($path)) {
        die('.env file not found or not readable. Please create one from .env.example.');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

load_env(PROJECT_ROOT . '/.env');

// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Geração e validação de Token CSRF
function generate_csrf_token(): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function validate_csrf_token(): bool
{
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Em um app real, isso deveria ser um erro fatal ou um redirect com mensagem.
        return false;
    }
    return true;
}

// Gera o token para a sessão atual se não existir
generate_csrf_token();

// Helpers de Roteamento e Autenticação
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function is_super_admin(): bool
{
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function is_coordinator(): bool
{
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'coordinator';
}

function is_admin(): bool
{
    return is_super_admin() || is_coordinator();
}

function require_auth(): void
{
    if (!is_logged_in()) {
        header('Location: /login.php?error=auth_required');
        exit();
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: /login.php?error=admin_required');
        exit();
    }
}

function require_coordinator(): void
{
    if (!is_coordinator()) {
        header('Location: /dashboard.php?error=coordinator_required');
        exit();
    }
}

// Helper para escapar output em HTML
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
