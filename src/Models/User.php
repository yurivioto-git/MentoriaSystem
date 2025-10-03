<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByRm(string $rm): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE rm = :rm");
        $stmt->execute(['rm' => $rm]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(string $nome, string $rm, int $serie, string $senha): int
    {
        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
        $ano_ingresso = date('Y');

        $stmt = $this->db->prepare(
            "INSERT INTO users (nome, rm, serie, senha_hash, ano_ingresso, role) VALUES (:nome, :rm, :serie, :senha_hash, :ano_ingresso, 'aluno')"
        );

        $stmt->execute([
            ':nome' => $nome,
            ':rm' => $rm,
            ':serie' => $serie,
            ':senha_hash' => $senha_hash,
            ':ano_ingresso' => $ano_ingresso
        ]);

        return (int)$this->db->lastInsertId();
    }
    
    public function getAll(string $role = null, bool $active = null): array
    {
        $sql = "SELECT id, nome, rm, serie, role, active, ano_ingresso FROM users";
        $conditions = [];
        $params = [];

        if ($role !== null) {
            $conditions[] = "role = :role";
            $params[':role'] = $role;
        }
        if ($active !== null) {
            $conditions[] = "active = :active";
            $params[':active'] = $active;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY nome ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        // Campos permitidos para atualização
        $allowedFields = ['nome', 'rm', 'serie', 'role', 'active'];
        $setClauses = [];
        $params = ['id' => $id];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $setClauses[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (isset($data['senha']) && !empty($data['senha'])) {
            $setClauses[] = "senha_hash = :senha_hash";
            $params['senha_hash'] = password_hash($data['senha'], PASSWORD_BCRYPT);
        }

        if (empty($setClauses)) {
            return false; // Nada para atualizar
        }

        $sql = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        // Garante que o admin padrão não seja excluído
        $user = $this->findById($id);
        if ($user && $user['rm'] === 'admin') {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
