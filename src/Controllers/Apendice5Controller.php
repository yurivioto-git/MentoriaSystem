<?php

namespace App\Controllers;

use App\Models\Apendice5;

class Apendice5Controller
{
    public function renderUploadForm()
    {
        require_once PROJECT_ROOT . '/src/Views/aluno/enviar_apendice5.php';
    }

    public function upload()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $bimestreRef = $_POST['bimestre'];

        if (Apendice5::findByBimestre($userId, $bimestreRef)) {
            // Handle error: submission for this bimester already exists
            header('Location: aluno_dashboard.php?error=submission_exists');
            exit();
        }

        if (isset($_FILES['apendice5_file']) && $_FILES['apendice5_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['apendice5_file']['tmp_name'];
            $fileName = $_FILES['apendice5_file']['name'];
            
            // Validate file extension
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $allowedExtensions = ['doc', 'docx'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                header('Location: aluno_dashboard.php?error=invalid_file_type');
                exit();
            }

            $newFileName = 'apendice5_' . uniqid() . '.' . $fileExtension;
            $uploadFileDir = PROJECT_ROOT . '/uploads/apendice5/';

            // Check and create directory
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // The path stored in the database should be relative to the project root
                $relativePath = 'uploads/apendice5/' . $newFileName;
                Apendice5::create($userId, $bimestreRef, $relativePath, $fileName);
                header('Location: aluno_dashboard.php?success=upload_success');
                exit();
            } else {
                // Handle error: failed to move uploaded file
                header('Location: aluno_dashboard.php?error=upload_failed');
                exit();
            }
        } else {
            // Handle other upload errors
            header('Location: aluno_dashboard.php?error=upload_error');
            exit();
        }
    }

    public function download()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }

        $submissionId = $_GET['id'];
        $submission = Apendice5::findById($submissionId);

        if ($submission) {
            $filePath = PROJECT_ROOT . '/' . $submission['file_path'];
            if (file_exists($filePath)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($submission['original_filename']) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            }
        }

        // Handle error: file not found
        header('Location: admin_dashboard.php?error=file_not_found');
        exit();
    }

    public function validate()
    {
        if (!is_admin()) {
            header('Location: login.php');
            exit();
        }

        $submissionId = $_POST['id'];
        $status = $_POST['status'];
        $adminNotes = $_POST['admin_notes'];

        Apendice5::updateStatus($submissionId, $status, $adminNotes);

        header('Location: admin_dashboard.php?view=apendice5');
        exit();
    }

    public function manage()
    {
        if (!is_admin()) {
            header('Location: login.php');
            exit();
        }

        require_once PROJECT_ROOT . '/src/Views/admin/manage_apendice5.php';
    }

    public function delete()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }

        if (!validate_csrf_token()) {
            die('CSRF token validation failed.');
        }

        $submissionId = (int)$_POST['id'];
        $userId = $_SESSION['user_id'];
        $isAdmin = is_admin();

        $submission = Apendice5::findById($submissionId);

        // Alunos não podem deletar se já foi aprovado
        if (!$isAdmin && $submission && $submission['status'] === 'aprovado') {
            header('Location: aluno_dashboard.php?error=' . urlencode('Você não pode excluir um envio que já foi aprovado.'));
            exit();
        }

        // Checa se o usuário é dono do arquivo, a não ser que seja admin
        if (!$submission || (!$isAdmin && $submission['user_id'] != $userId)) {
            // Not found or not owner
            header('Location: aluno_dashboard.php?error=permission_denied');
            exit();
        }

        Apendice5::delete($submissionId);

        // Redireciona para o painel correto
        if ($isAdmin) {
            header('Location: admin_dashboard.php?view=apendice5&success=delete_success');
        } else {
            header('Location: aluno_dashboard.php?success=delete_success');
        }
        exit();
    }

    public function downloadAll($bimestreRef = null)
    {
        if (!class_exists('ZipArchive')) {
            die('A extensão ZIP do PHP não está habilitada. Por favor, habilite-a no seu arquivo php.ini.');
        }

        if (!is_admin()) {
            header('Location: login.php');
            exit();
        }

        $submissions = Apendice5::getSubmissions($bimestreRef);

        if (empty($submissions)) {
            // Or redirect with an error
            die('Nenhum arquivo para baixar.');
        }

        $zip = new \ZipArchive();
        $zipFileName = 'apendices5_' . ($bimestreRef ? $bimestreRef . '_' : '') . date('Y-m-d') . '.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            die('Não foi possível criar o arquivo zip.');
        }

        foreach ($submissions as $submission) {
            $filePath = PROJECT_ROOT . '/' . $submission['file_path'];
            if (file_exists($filePath)) {
                // Sanitize filename and add to zip
                $sanitizedFilename = preg_replace('/[^a-zA-Z0-9-_\.]/', '', basename($submission['original_filename']));
                $zip->addFile($filePath, $submission['bimestre_ref'] . '/' . $submission['user_name'] . '_' . $sanitizedFilename);
            }
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);

        // Clean up the zip file
        unlink($zipFilePath);
        exit();
    }
}