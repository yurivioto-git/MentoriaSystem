<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Hora;

class AdminController
{
    private User $userModel;
    private Hora $horaModel;

    public function __construct()
    {
        require_admin(); // Protege todo o controlador
        $this->userModel = new User();
        $this->horaModel = new Hora();
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
            }
        }
    }

    private function createUser(): void
    {
        // Lógica para admin criar um novo usuário (aluno ou admin)
        $nome = $_POST['nome'] ?? '';
        $rm = $_POST['rm'] ?? '';
        $serie = (int)($_POST['serie'] ?? 0);
        $senha = $_POST['senha'] ?? '';
        $role = $_POST['role'] ?? 'aluno';

        if (empty($nome) || empty($rm) || empty($senha) || !in_array($role, ['aluno', 'admin'])) {
            $this->redirectWithError('Dados inválidos para criar usuário.');
        }
        if ($role === 'aluno' && !in_array($serie, [1, 2, 3])) {
            $this->redirectWithError('Série inválida para aluno.');
        }
        if ($this->userModel->findByRm($rm)) {
            $this->redirectWithError('RM já cadastrado.');
        }

        try {
            // Reutilizando o método de criação, mas precisaria de um mais flexível para roles
            // Idealmente, o model teria um método create com role
            $userId = $this->userModel->create($nome, $rm, $serie, $senha);
            if ($role === 'admin') {
                $this->userModel->update($userId, ['role' => 'admin', 'serie' => 0]);
            }
            $this->redirectWithSuccess('Usuário criado com sucesso.');
        } catch (\Exception $e) {
            $this->redirectWithError('Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    private function updateUser(): void
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$userId) {
            $this->redirectWithError('ID de usuário inválido.');
        }

        $data = [
            'nome' => $_POST['nome'],
            'rm' => $_POST['rm'],
            'serie' => (int)$_POST['serie'],
            'role' => $_POST['role'],
            'active' => isset($_POST['active']) ? true : false,
        ];

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
        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$userId) {
            $this->redirectWithError('ID de usuário inválido.');
        }

        try {
            if ($this->userModel->delete($userId)) {
                $this->redirectWithSuccess('Usuário excluído com sucesso.');
            }
            else {
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
        $view = $_GET['view'] ?? 'alunos';
        header("Location: admin_dashboard.php?view={$view}&error=" . urlencode($message));
        exit();
    }

    private function redirectWithSuccess(string $message): void
    {
        $view = $_GET['view'] ?? 'alunos';
        header("Location: admin_dashboard.php?view={$view}&success=" . urlencode($message));
        exit();
    }
}
