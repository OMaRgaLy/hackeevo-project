<?php
namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=utf8mb4",
                    $_ENV['DB_HOST'],
                    $_ENV['DB_DATABASE']
                );

                self::$instance = new PDO(
                    $dsn,
                    $_ENV['DB_USERNAME'],
                    $_ENV['DB_PASSWORD'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
                    ]
                );
            } catch (PDOException $e) {
                throw new PDOException("DB connection failed: " . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}