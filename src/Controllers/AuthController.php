<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLoginForm(string $error = null, string $success = null): void
    {
        require_once PROJECT_ROOT . '/src/Views/login_form.php';
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showLoginForm();
            return;
        }

        if (!validate_csrf_token()) {
            die('CSRF token validation failed.');
        }

        $rm = $_POST['rm'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (empty($rm) || empty($senha)) {
            $this->showLoginForm('Preencha todos os campos.');
            return;
        }

        $user = $this->userModel->findByRm($rm);

        if (!$user || !password_verify($senha, $user['senha_hash'])) {
            $this->showLoginForm('RM ou senha inválidos.');
            return;
        }

        if (!$user['active']) {
            $this->showLoginForm('Esta conta está inativa.');
            return;
        }

        // Regenera o ID da sessão para prevenir session fixation
        session_regenerate_id(true);

        // Armazena dados do usuário na sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_rm'] = $user['rm'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_role'] = $user['role'];

        // Redireciona para o dashboard apropriado
        if ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: aluno_dashboard.php');
        }
        exit();
    }

    public function showRegistrationForm(string $error = null): void
    {
        require_once PROJECT_ROOT . '/src/Views/register_form.php';
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showRegistrationForm();
            return;
        }

        if (!validate_csrf_token()) {
            die('CSRF token validation failed.');
        }

        $nome = $_POST['nome'] ?? '';
        $rm = $_POST['rm'] ?? '';
        $serie = (int)($_POST['serie'] ?? 0);
        $senha = $_POST['senha'] ?? '';
        $senha_confirm = $_POST['senha_confirm'] ?? '';

        if (empty($nome) || empty($rm) || empty($serie) || empty($senha)) {
            $this->showRegistrationForm('Todos os campos são obrigatórios.');
            return;
        }

        if ($senha !== $senha_confirm) {
            $this->showRegistrationForm('As senhas não coincidem.');
            return;
        }

        if (!in_array($serie, [1, 2, 3])) {
            $this->showRegistrationForm('Série inválida.');
            return;
        }

        if ($this->userModel->findByRm($rm)) {
            $this->showRegistrationForm('Este RM já está cadastrado.');
            return;
        }

        try {
            $this->userModel->create($nome, $rm, $serie, $senha);
            header('Location: login.php?success=registered');
            exit();
        } catch (\Exception $e) {
            // Em produção, logar o erro
            $this->showRegistrationForm('Ocorreu um erro ao criar a conta.');
        }
    }

    public function logout(): void
    {
        // Limpa todas as variáveis da sessão
        $_SESSION = [];

        // Destrói a sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        header('Location: login.php?success=logout');
        exit();
    }
}
