<?php

namespace App\Repository;

use PDO;

class UrlRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getCheckUrlByUrlId($urlId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM url_checks WHERE url_id = :urlId ORDER BY id DESC');
        $stmt->execute(['urlId' => $urlId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getIdByName(string $name): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM urls WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $id = $stmt->fetchColumn();
        
        return $id !== false ? (int) $id : null;
    }
    
    public function createOrGetId(string $name, string $createdAt): array
    {
        $id = $this->getIdByName($name);
        
        if ($id !== null) {
            return ['status' => true, 'id' => $id];
        }
    
        $stmt = $this->pdo->prepare('INSERT INTO urls (name, created_at) VALUES (:name, :created_at) RETURNING id');
        $stmt->execute(['name' => $name, 'created_at' => $createdAt]);
        
        return ['status' => false, 'id' => (int) $stmt->fetchColumn()];
    }

    public function createUrlCheck(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO url_checks (url_id, status_code, title, description, h1, created_at)
            VALUES(:url_id, :status, :title, :description, :h1, :time)');
        
        $stmt->execute([
            'url_id' => $data['url_id'],
            'status' => $data['status_code'],
            'title' => $data['title'],
            'description' => $data['description'],
            'h1' => $data['h1'],
            'time' => $data['created_at']
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }

    public function getRowById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM urls WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
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
