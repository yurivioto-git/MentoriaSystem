<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dbHost = getenv('DB_HOST');
            $dbPort = getenv('DB_PORT');
            $dbName = getenv('DB_NAME');
            $dbUser = getenv('DB_USER');
            $dbPass = getenv('DB_PASS');

            $dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}";

            try {
                self::$instance = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Em um ambiente de produção, logar o erro em vez de exibi-lo.
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}