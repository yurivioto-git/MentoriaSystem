<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Hora;

class AdminController
{
    private User $userModel;
    private Hora $horaModel;
    private ?array $current_user = null;

    public function __construct()
    {
        require_auth(); // Protege todo o controlador
        $this->userModel = new User();
        $this->horaModel = new Hora();
        if (is_logged_in()) {
            $this->current_user = $this->userModel->findById($_SESSION['user_id']);
        }
    }

    public function handleAction(): void
    {
        $action = $_GET['action'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token()) {
                die('CSRF token validation failed.');
            }
            switch ($action) {
                case 'create_user':
                    $this->createUser();
                    break;
                case 'update_user':
                    $this->updateUser();
                    break;
                case 'delete_user':
                    $this->deleteUser();
                    break;
                case 'update_hora_status':
                    $this->updateHoraStatus();
                    break;
                case 'update_password':
                    $this->updatePassword();
                    break;
            }
        }
    }

    private function updatePassword(): void
    {
        if (!is_admin()) {
            $this->redirectWithError('Você não tem permissão para alterar a senha.');
        }

        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->redirectWithError('Todos os campos são obrigatórios.');
        }

        if ($newPassword !== $confirmPassword) {
            $this->redirectWithError('A nova senha e a confirmação não correspondem.');
        }

        $user = $this->userModel->findById($userId);

        if (!$user || !password_verify($currentPassword, $user['senha_hash'])) {
            $this->redirectWithError('A senha atual está incorreta.');
        }

        try {
            $this->userModel->update($userId, ['senha' => $newPassword]);
            $this->redirectWithSuccess('Senha alterada com sucesso.');
        } catch (\Exception $e) {
            $this->redirectWithError('Erro ao alterar a senha: ' . $e->getMessage());
        }
    }

    private function createUser(): void
    {
        // Log all POST data
        error_log(print_r($_POST, true));

        if (!is_super_admin()) {
            // Log permission error
            error_log('Permission denied for creating user.');
            $this->redirectWithError('Você não tem permissão para criar usuários.');
        }

        $nome = $_POST['nome'] ?? '';
        $rm = $_POST['rm'] ?? '';
        $serie = (int)($_POST['serie'] ?? 0);
        $senha = $_POST['senha'] ?? '';
        $role = $_POST['role'] ?? 'student';
        $course_id = null;

        if (is_super_admin()) {
            $course_id = (int)($_POST['course_id'] ?? null);
        }

        if ($role === 'coordinator') {
            $serie = 0;
        }

        // Log parsed data
        error_log("nome: $nome, rm: $rm, serie: $serie, role: $role, course_id: $course_id");

        if (empty($nome) || empty($rm) || empty($senha) || !in_array($role, ['student', 'coordinator'])) {
            // Log validation error
            error_log('Validation failed: empty fields or invalid role.');
            $this->redirectWithError('Dados inválidos para criar usuário.');
        }
        if ($role === 'student' && !in_array($serie, [1, 2, 3])) {
            // Log validation error
            error_log("Validation failed: invalid serie for student. serie: $serie");
            $this->redirectWithError('Série inválida para aluno.');
        }
        if ($this->userModel->findByRm($rm)) {
            // Log validation error
            error_log("Validation failed: RM already exists. rm: $rm");
            $this->redirectWithError('RM já cadastrado.');
        }
        if ($course_id === null) {
            // Log validation error
            error_log('Validation failed: course_id is null.');
            $this->redirectWithError('Curso é obrigatório.');
        }

        try {
            // Log before create
            error_log("Creating user...");
            $this->userModel->create($nome, $rm, $serie, $senha, $course_id, $role);
            // Log after create
            error_log("User created successfully.");
            $this->redirectWithSuccess('Usuário criado com sucesso.');
        } catch (\Exception $e) {
            // Log exception
            error_log('Exception while creating user: ' . $e->getMessage());
            $this->redirectWithError('Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    private function updateUser(): void
    {
        if (!is_admin()) {
            $this->redirectWithError('Você não tem permissão para atualizar usuários.');
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$userId) {
            $this->redirectWithError('ID de usuário inválido.');
        }

        $userToUpdate = $this->userModel->findById($userId);
        if (!$userToUpdate) {
            $this->redirectWithError('Usuário não encontrado.');
        }

        if (is_coordinator() && $this->current_user['course_id'] !== $userToUpdate['course_id']) {
            $this->redirectWithError('Você não tem permissão para atualizar usuários de outro curso.');
        }

        $data = [
            'nome' => $_POST['nome'],
            'rm' => $_POST['rm'],
            'serie' => (int)$_POST['serie'],
            'role' => $_POST['role'],
            'active' => isset($_POST['active']) ? true : false,
        ];

        if (is_super_admin()) {
            $data['course_id'] = (int)($_POST['course_id'] ?? $userToUpdate['course_id']);
        }

        if (!empty($_POST['senha'])) {
            $data['senha'] = $_POST['senha'];
        }

        try {
            $this->userModel->update($userId, $data);
            $this->redirectWithSuccess('Usuário atualizado com sucesso.');
        } catch (\Exception $e) {
            $this->redirectWithError('Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    private function deleteUser(): void
    {
        if (!is_admin()) {
            $this->redirectWithError('Você não tem permissão para excluir usuários.');
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$userId) {
            $this->redirectWithError('ID de usuário inválido.');
        }

        $userToDelete = $this->userModel->findById($userId);
        if (!$userToDelete) {
            $this->redirectWithError('Usuário não encontrado.');
        }

        if (is_coordinator() && $this->current_user['course_id'] !== $userToDelete['course_id']) {
            $this->redirectWithError('Você não tem permissão para excluir usuários de outro curso.');
        }

        try {
            if ($this->userModel->delete($userId)) {
                $this->redirectWithSuccess('Usuário excluído com sucesso.');
            } else {
                $this->redirectWithError('Não é possível excluir o administrador padrão.');
            }
        } catch (\Exception $e) {
            $this->redirectWithError('Erro ao excluir usuário. Verifique se ele tem registros associados.');
        }
    }

    private function updateHoraStatus(): void
    {
        $horaId = (int)($_POST['hora_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $feedback = $_POST['feedback_admin'] ?? '';

        if (!$horaId || !in_array($status, ['Aprovado', 'Rejeitado'])) {
            $this->redirectWithError('Dados inválidos.');
        }

        $hora = $this->horaModel->findById($horaId);
        if (!$hora) {
            $this->redirectWithError('Lançamento não encontrado.');
        }

        try {
            $this->horaModel->update($horaId, [
                'status' => $status,
                'feedback_admin' => $feedback,
                // Manter os dados existentes
                'tipo' => $hora['tipo'],
                'data' => $hora['data'],
                'quantidade_horas' => $hora['quantidade_horas'],
                'bimestre' => $hora['bimestre'],
                'ano' => $hora['ano'],
                'descricao' => $hora['descricao'],
            ]);
            $this->redirectWithSuccess('Status do lançamento atualizado.');
        } catch (\Exception $e) {
            $this->redirectWithError('Erro ao atualizar status: ' . $e->getMessage());
        }
    }

    public function generateReport(array $filters): void
    {
        $horas = $this->horaModel->findAllWithDetails($filters);
        
        $reportData = [];
        foreach ($horas as $hora) {
            if ($hora['status'] !== 'Aprovado') {
                continue; // Pular horas não aprovadas
            }

            $rm = $hora['rm'];
            if (!isset($reportData[$rm])) {
                $reportData[$rm] = [
                    'aluno_nome' => $hora['aluno_nome'],
                    'rm' => $rm,
                    'serie' => $hora['serie'],
                    'total_horas' => 0
                ];
            }
            $reportData[$rm]['total_horas'] += (float)str_replace(',', '.', $hora['quantidade_horas']);
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_horas_consolidadas_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        
        // Cabeçalho do CSV
        fputcsv($output, ['Aluno', 'RM', 'Série', 'Total de Horas Aprovadas']);

        // Dados
        foreach ($reportData as $row) {
            fputcsv($output, [
                $row['aluno_nome'],
                $row['rm'],
                $row['serie'],
                number_format($row['total_horas'], 2, ',', '.')
            ]);
        }

        fclose($output);
        exit();
    }

    private function redirectWithError(string $message): void
    {
        $view = $_POST['view'] ?? $_GET['view'] ?? 'alunos';
        header("Location: admin_dashboard.php?view={$view}&error=" . urlencode($message));
        exit();
    }

    private function redirectWithSuccess(string $message): void
    {
        $view = $_POST['view'] ?? $_GET['view'] ?? 'alunos';
        header("Location: admin_dashboard.php?view={$view}&success=" . urlencode($message));
        exit();
    }
}
