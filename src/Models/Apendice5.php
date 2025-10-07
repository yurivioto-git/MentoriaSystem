<?php

namespace App\Models;

use App\Database;

class Apendice5
{
    public static function create($userId, $bimestreRef, $filePath, $originalFilename)
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO apendice5_submissions (user_id, bimestre_ref, file_path, original_filename) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$userId, $bimestreRef, $filePath, $originalFilename]);
    }

    public static function findByBimestre($userId, $bimestreRef)
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM apendice5_submissions WHERE user_id = ? AND bimestre_ref = ?'
        );
        $stmt->execute([$userId, $bimestreRef]);
        return $stmt->fetch();
    }

    public static function findByUserId($userId)
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM apendice5_submissions WHERE user_id = ? ORDER BY submission_date DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getSubmissions($bimestreRef = null, $status = null)
    {
        $pdo = Database::getInstance();
        $sql = 'SELECT ap.*, u.nome as user_name FROM apendice5_submissions ap JOIN users u ON ap.user_id = u.id';
        $conditions = [];
        $params = [];

        if ($bimestreRef) {
            $conditions[] = 'ap.bimestre_ref = ?';
            $params[] = $bimestreRef;
        }

        if ($status) {
            $conditions[] = 'ap.status = ?';
            $params[] = $status;
        }

        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY ap.submission_date DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function updateStatus($id, $status, $adminNotes)
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE apendice5_submissions SET status = ?, admin_notes = ?, validation_date = CURRENT_TIMESTAMP WHERE id = ?'
        );
        return $stmt->execute([$status, $adminNotes, $id]);
    }

    public static function findById($id)
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM apendice5_submissions WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function delete($id)
    {
        $submission = self::findById($id);
        if ($submission) {
            // Delete the file
            $filePath = PROJECT_ROOT . '/' . $submission['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete the record
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM apendice5_submissions WHERE id = ?');
            return $stmt->execute([$id]);
        }
        return false;
    }

    public static function getDistinctBimestres()
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query('SELECT DISTINCT bimestre_ref FROM apendice5_submissions ORDER BY bimestre_ref DESC');
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}