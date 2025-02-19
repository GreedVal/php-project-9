<?php

namespace App\Repository;

use PDO;
use Dotenv\Dotenv;
use Exception;

final class Connection
{
    private static ?PDO $pdo = null;

    private function __construct()
    {
        if (!isset($_ENV['DATABASE_URL'])) {
            $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
            $dotenv->safeLoad();
        }

        if (isset($_ENV['DATABASE_URL'])) {
            $databaseUrl = parse_url($_ENV['DATABASE_URL']);
            if (!isset($databaseUrl['host'], $databaseUrl['path'])) {
                throw new Exception("Некорректный DATABASE_URL в .env");
            }

            $params = [
                'host' => $databaseUrl['host'],
                'port' => $databaseUrl['port'] ?? 5432,
                'dbname' => ltrim($databaseUrl['path'], '/'),
                'user' => $databaseUrl['user'] ?? null,
                'password' => $databaseUrl['pass'] ?? null
            ];
        } else {
            $params = parse_ini_file(dirname(__DIR__) . '/config/database.ini');
            if ($params === false) {
                throw new Exception("Ошибка при чтении database.ini");
            }
        }

        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $params['host'],
            $params['port'],
            $params['dbname']
        );

        try {
            self::$pdo = new PDO($dsn, $params['user'], $params['password']);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new Exception("Ошибка подключения к БД: " . $e->getMessage());
        }
    }

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            new self();
        }

        return self::$pdo;
    }
}