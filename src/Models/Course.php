<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

class Course
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, name FROM courses ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}
