<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

class Hora
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO horas (user_id, tipo, data, quantidade_horas, bimestre, ano, descricao, comprovante_path, comprovante_filename, created_by_admin, status)
                VALUES (:user_id, :tipo, :data, :quantidade_horas, :bimestre, :ano, :descricao, :comprovante_path, :comprovante_filename, :created_by_admin, :status)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':tipo', $data['tipo']);
        $stmt->bindValue(':data', $data['data']);
        $stmt->bindValue(':quantidade_horas', $data['quantidade_horas']);
        $stmt->bindValue(':bimestre', $data['bimestre']);
        $stmt->bindValue(':ano', $data['ano']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':comprovante_path', $data['comprovante_path'] ?? null);
        $stmt->bindValue(':comprovante_filename', $data['comprovante_filename'] ?? null);
        $stmt->bindValue(':created_by_admin', $data['created_by_admin'] ?? false, PDO::PARAM_BOOL);
        $stmt->bindValue(':status', $data['status'] ?? 'Pendente');
        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT h.*, u.nome as aluno_nome FROM horas h JOIN users u ON h.user_id = u.id WHERE h.id = :id");
        $stmt->execute(['id' => $id]);
        $hora = $stmt->fetch();
        return $hora ?: null;
    }

    public function findByUserId(int $userId, array $filters = []): array
    {
        $sql = "SELECT * FROM horas WHERE user_id = :user_id";
        $params = ['user_id' => $userId];

        if (!empty($filters['bimestre'])) {
            $sql .= " AND bimestre = :bimestre";
            $params[':bimestre'] = $filters['bimestre'];
        }
        if (!empty($filters['ano'])) {
            $sql .= " AND ano = :ano";
            $params[':ano'] = $filters['ano'];
        }
        if (!empty($filters['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }

        $sql .= " ORDER BY data DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findAllWithDetails(array $filters = []): array
    {
        $sql = "SELECT h.*, u.nome as aluno_nome, u.rm, u.serie 
                FROM horas h 
                JOIN users u ON h.user_id = u.id";
        
        $conditions = [];
        $params = [];

        if (!empty($filters['aluno_id'])) {
            $conditions[] = "h.user_id = :aluno_id";
            $params[':aluno_id'] = $filters['aluno_id'];
        }
        if (!empty($filters['tipo'])) {
            $conditions[] = "h.tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }
        if (!empty($filters['data_inicio'])) {
            $conditions[] = "h.data >= :data_inicio";
            $params[':data_inicio'] = $filters['data_inicio'];
        }
        if (!empty($filters['data_fim'])) {
            $conditions[] = "h.data <= :data_fim";
            $params[':data_fim'] = $filters['data_fim'];
        }
        if (!empty($filters['serie'])) {
            $conditions[] = "u.serie = :serie";
            $params[':serie'] = $filters['serie'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = "h.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['course_id'])) {
            $conditions[] = "u.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY h.data DESC, u.nome ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE horas SET 
                    tipo = :tipo, 
                    data = :data, 
                    quantidade_horas = :quantidade_horas, 
                    bimestre = :bimestre, 
                    ano = :ano, 
                    descricao = :descricao, 
                    status = :status, 
                    feedback_admin = :feedback_admin
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':tipo' => $data['tipo'],
            ':data' => $data['data'],
            ':quantidade_horas' => $data['quantidade_horas'],
            ':bimestre' => $data['bimestre'],
            ':ano' => $data['ano'],
            ':descricao' => $data['descricao'] ?? null,
            ':status' => $data['status'] ?? 'Pendente',
            ':feedback_admin' => $data['feedback_admin'] ?? null
        ]);
    }

    public function delete(int $id): bool
    {
        // Opcional: remover o arquivo de comprovante associado, se houver.
        $hora = $this->findById($id);
        if ($hora && !empty($hora['comprovante_path'])) {
            $filePath = PROJECT_ROOT . '/' . $hora['comprovante_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM horas WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}