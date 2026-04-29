<?php

require_once __DIR__ . '/../config/auth.php';

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function login(string $email, string $password): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([htmlspecialchars($email)]);
        $user = $stmt->fetch();
        if ($user && verifyPassword($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function register(string $nama, string $email, string $password): int|false
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                htmlspecialchars($nama),
                htmlspecialchars($email),
                hashPassword($password),
                'pasien',
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException) {
            return false;
        }
    }

    public function tambahDokter(string $nama, string $email, string $password): int|false
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                htmlspecialchars($nama),
                htmlspecialchars($email),
                hashPassword($password),
                'dokter',
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException) {
            return false;
        }
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT id, nama, email, role, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT id, nama, email, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update(int $id, string $nama, string $email, ?string $password = null): bool
    {
        if ($password) {
            $stmt = $this->db->prepare('UPDATE users SET nama = ?, email = ?, password = ? WHERE id = ?');
            return $stmt->execute([htmlspecialchars($nama), htmlspecialchars($email), hashPassword($password), $id]);
        }
        $stmt = $this->db->prepare('UPDATE users SET nama = ?, email = ? WHERE id = ?');
        return $stmt->execute([htmlspecialchars($nama), htmlspecialchars($email), $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
