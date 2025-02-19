<?php

namespace App\Repository;

use PDO;

class UrlRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM urls ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM urls WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $url = $stmt->fetch(PDO::FETCH_ASSOC);
        return $url ?: null;
    }

    public function create(string $name, string $createdAt): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO urls (name, created_at) VALUES (:name, :created_at) RETURNING id');
        $stmt->execute(['name' => $name, 'created_at' => $createdAt]);
        return (int) $stmt->fetchColumn();
    }

    public function getLatestUrlChecks(): array
    {
        $stmt = $this->pdo->query(
            'SELECT url_id, MAX(created_at) AS created_at, status_code 
             FROM url_checks 
             GROUP BY url_id, status_code'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithLatestChecks(): array
    {
        $stmt = $this->pdo->query(
            'SELECT u.*, uc.created_at AS last_check_at, uc.status_code
             FROM urls u
             LEFT JOIN (
                 SELECT url_id, MAX(created_at) AS created_at
                 FROM url_checks
                 GROUP BY url_id
             ) latest_checks ON u.id = latest_checks.url_id
             LEFT JOIN url_checks uc ON u.id = uc.url_id AND uc.created_at = latest_checks.created_at
             ORDER BY u.id DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
