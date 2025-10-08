<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Hora;
use App\Models\User;

class HorasController
{
    private Hora $horaModel;
    private User $userModel;

    public function __construct()
    {
        $this->horaModel = new Hora();
        $this->userModel = new User();
    }

    public function create(): void
    {
        if (!validate_csrf_token()) {
            die('CSRF token validation failed.');
        }

        $tipo = $_POST['tipo'] ?? '';
        $data = $_POST['data'] ?? '';
        $quantidade_horas_raw = $_POST['quantidade_horas'] ?? '0';
        $bimestre = (int)($_POST['bimestre'] ?? 0);
        $descricao = $_POST['descricao'] ?? '';

        // Validação de horas como inteiro
        $quantidade_horas = filter_var($quantidade_horas_raw, FILTER_VALIDATE_INT);
        if ($quantidade_horas === false || $quantidade_horas <= 0) {
            $this->redirectWithError('A quantidade de horas deve ser um número inteiro e positivo.');
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $createdByAdmin = false;

        // Admin pode criar para outro usuário
        if ($userRole === 'admin' && !empty($_POST['aluno_id'])) {
            $userId = (int)$_POST['aluno_id'];
            $createdByAdmin = true;
        }

        // Validação básica
        if (empty($tipo) || empty($data) || empty($quantidade_horas) || empty($bimestre)) {
            $this->redirectWithError('Todos os campos obrigatórios devem ser preenchidos.');
        }

        // Regra de negócio: Aluno não pode cadastrar estágio
        if ($userRole === 'aluno' && $tipo === 'Estágios') {
            $this->redirectWithError('Apenas administradores podem cadastrar estágios.');
        }

        $horaData = [
            'user_id' => $userId,
            'tipo' => $tipo,
            'data' => $data,
            'quantidade_horas' => $quantidade_horas,
            'bimestre' => $bimestre,
            'ano' => date('Y', strtotime($data)),
            'descricao' => $descricao,
            'created_by_admin' => $createdByAdmin,
            'status' => $createdByAdmin ? 'Aprovado' : 'Pendente' // Horas de admin já são aprovadas
        ];

        // Lógica de Upload para tipo 'Outros'
        if ($tipo === 'Outros') {
            if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
                $fileController = new FileController();
                $uploadResult = $fileController->upload($_FILES['comprovante']);

                if (isset($uploadResult['error'])) {
                    $this->redirectWithError($uploadResult['error']);
                }

                $horaData['comprovante_path'] = $uploadResult['path'];
                $horaData['comprovante_filename'] = $uploadResult['name'];
            } else {
                $this->redirectWithError('É obrigatório enviar um comprovante para o tipo \'Outros\'.');
            }
        }

        try {
            $this->horaModel->create($horaData);
            $this->redirectWithSuccess('Horas lançadas com sucesso!');
        } catch (\Exception $e) {
            // Log do erro
            $this->redirectWithError('Erro ao lançar horas: ' . $e->getMessage());
        }
    }

    public function update(int $id): void
    {
        require_admin(); // Apenas admin pode editar
        if (!validate_csrf_token()) {
            die('CSRF token validation failed.');
        }

        $hora = $this->horaModel->findById($id);
        if (!$hora) {
            $this->redirectWithError('Lançamento não encontrado.');
        }

        $quantidade_horas_raw = $_POST['quantidade_horas'] ?? $hora['quantidade_horas'];
        $quantidade_horas = filter_var($quantidade_horas_raw, FILTER_VALIDATE_INT);
        if ($quantidade_horas === false || $quantidade_horas <= 0) {
            $this->redirectWithError('A quantidade de horas deve ser um número inteiro e positivo.');
        }

        $data = [
            'tipo' => $_POST['tipo'] ?? $hora['tipo'],
            'data' => $_POST['data'] ?? $hora['data'],
            'quantidade_horas' => $quantidade_horas,
            'bimestre' => (int)($_POST['bimestre'] ?? $hora['bimestre']),
            'ano' => date('Y', strtotime($_POST['data'] ?? $hora['data'])),
            'descricao' => $_POST['descricao'] ?? $hora['descricao'],
            'status' => $_POST['status'] ?? $hora['status'],
            'feedback_admin' => $_POST['feedback_admin'] ?? $hora['feedback_admin']
        ];

        try {
            $this->horaModel->update($id, $data);
            $this->redirectWithSuccess('Lançamento atualizado com sucesso!');
        } catch (\Exception $e) {
            $this->redirectWithError('Erro ao atualizar: ' . $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        if (!validate_csrf_token()) {
            die('CSRF token validation failed.');
        }

        $hora = $this->horaModel->findById($id);
        if (!$hora) {
            $this->redirectWithError('Lançamento não encontrado.');
        }

        // Permite que admin ou o próprio usuário delete
        if (!is_admin() && $hora['user_id'] !== $_SESSION['user_id']) {
            $this->redirectWithError('Você não tem permissão para excluir este lançamento.');
        }

        // Não permite deletar se já foi aprovado
        if ($hora['status'] === 'Aprovado') {
            $this->redirectWithError('Você não pode excluir um lançamento que já foi aprovado.');
        }

        try {
            $this->horaModel->delete($id);
            $this->redirectWithSuccess('Lançamento excluído com sucesso.');
        } catch (\Exception $e) {
            $this->redirectWithError('Erro ao excluir: ' . $e->getMessage());
        }
    }

    private function redirectWithError(string $message): void
    {
        $redirectUrl = is_admin() ? 'admin_dashboard.php' : 'aluno_dashboard.php';
        header("Location: {$redirectUrl}?error=" . urlencode($message));
        exit();
    }

    private function redirectWithSuccess(string $message): void
    {
        $redirectUrl = is_admin() ? 'admin_dashboard.php' : 'aluno_dashboard.php';
        header("Location: {$redirectUrl}?success=" . urlencode($message));
        exit();
    }
}
