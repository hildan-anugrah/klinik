<?php

class Pasien
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT p.*, u.email FROM pasien p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function getByDokter(int $dokterId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT p.*, u.email FROM pasien p
             LEFT JOIN users u ON p.user_id = u.id
             INNER JOIN rekam_medis rm ON rm.pasien_id = p.id
             WHERE rm.dokter_id = ? AND rm.is_deleted = 0
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$dokterId]);
        return $stmt->fetchAll();
    }

    public function getByUserId(int $userId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.email FROM pasien p LEFT JOIN users u ON p.user_id = u.id WHERE p.user_id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.email FROM pasien p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(
        ?int $userId,
        string $nama,
        string $tanggalLahir,
        string $jenisKelamin,
        string $alamat,
        string $noTelp
    ): int|false {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO pasien (user_id, nama, tanggal_lahir, jenis_kelamin, alamat, no_telp)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                htmlspecialchars($nama),
                $tanggalLahir,
                $jenisKelamin,
                htmlspecialchars($alamat),
                htmlspecialchars($noTelp),
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException) {
            return false;
        }
    }

    public function update(
        int $id,
        string $nama,
        string $tanggalLahir,
        string $jenisKelamin,
        string $alamat,
        string $noTelp
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE pasien SET nama = ?, tanggal_lahir = ?, jenis_kelamin = ?, alamat = ?, no_telp = ? WHERE id = ?'
        );
        return $stmt->execute([
            htmlspecialchars($nama),
            $tanggalLahir,
            $jenisKelamin,
            htmlspecialchars($alamat),
            htmlspecialchars($noTelp),
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM pasien WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM pasien')->fetchColumn();
    }
}
