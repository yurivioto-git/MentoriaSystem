<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Hora;

class FileController
{
    private string $uploadDir;
    private int $maxSizeMb;
    private array $allowedMimeTypes = [
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        'application/pdf' => '.pdf',
    ];

    public function __construct()
    {
        $this->uploadDir = PROJECT_ROOT . '/' . ($_ENV['UPLOAD_DIR'] ?? 'uploads/comprovantes');
        $this->maxSizeMb = (int)($_ENV['MAX_UPLOAD_MB'] ?? 5);

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(array $file): array
    {
        // 1. Validar erro de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Erro no upload do arquivo.'];
        }

        // 2. Validar tamanho
        $maxSizeBytes = $this->maxSizeMb * 1024 * 1024;
        if ($file['size'] > $maxSizeBytes) {
            return ['error' => "O arquivo excede o tamanho máximo de {$this->maxSizeMb}MB."];
        }

        // 3. Validar tipo MIME
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mimeType, $this->allowedMimeTypes)) {
            return ['error' => 'Tipo de arquivo inválido. Permitidos: JPG, PNG, PDF.'];
        }

        // 4. Gerar nome de arquivo único
        $originalName = basename($file['name']);
        $extension = $this->allowedMimeTypes[$mimeType];
        $uniqueName = uniqid('comprovante_', true) . $extension;
        $destinationPath = $this->uploadDir . '/' . $uniqueName;

        // 5. Mover o arquivo
        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            return ['error' => 'Falha ao mover o arquivo para o destino.'];
        }

        return [
            'path' => ($_ENV['UPLOAD_DIR'] ?? 'uploads/comprovantes') . '/' . $uniqueName,
            'name' => $originalName
        ];
    }

    public function serve(int $horaId): void
    {
        require_auth(); // Requer login

        $horaModel = new Hora();
        $hora = $horaModel->findById($horaId);

        if (!$hora) {
            http_response_code(404);
            die('Arquivo não encontrado.');
        }

        // Validação de permissão: Admin pode ver tudo, aluno só pode ver o seu
        if (!is_admin() && $hora['user_id'] !== $_SESSION['user_id']) {
            http_response_code(403);
            die('Acesso negado.');
        }

        if (empty($hora['comprovante_path'])) {
            http_response_code(404);
            die('Nenhum comprovante associado a este lançamento.');
        }

        $filePath = PROJECT_ROOT . '/' . $hora['comprovante_path'];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            http_response_code(404);
            die('Arquivo não encontrado no servidor.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        // header('Content-Disposition: inline; filename="' . $hora['comprovante_filename'] . '"'); // Para exibir no browser
        header('Content-Disposition: attachment; filename="' . $hora['comprovante_filename'] . '"'); // Força o download

        ob_clean();
        flush();
        readfile($filePath);
        exit();
    }
}
