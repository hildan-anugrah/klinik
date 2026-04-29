<?php

class RekamMedis
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT rm.*, p.nama AS nama_pasien, COALESCE(u.nama, \'None\') AS nama_dokter
             FROM rekam_medis rm
             LEFT JOIN pasien p ON rm.pasien_id = p.id
             LEFT JOIN users u ON rm.dokter_id = u.id
             WHERE rm.is_deleted = 0
             ORDER BY rm.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function getByDokter(int $dokterId): array
    {
        $stmt = $this->db->prepare(
            'SELECT rm.*, p.nama AS nama_pasien, COALESCE(u.nama, \'None\') AS nama_dokter
             FROM rekam_medis rm
             LEFT JOIN pasien p ON rm.pasien_id = p.id
             LEFT JOIN users u ON rm.dokter_id = u.id
             WHERE rm.dokter_id = ? AND rm.is_deleted = 0
             ORDER BY rm.created_at DESC'
        );
        $stmt->execute([$dokterId]);
        return $stmt->fetchAll();
    }

    public function getByPasien(int $pasienId): array
    {
        $stmt = $this->db->prepare(
            'SELECT rm.*, p.nama AS nama_pasien, COALESCE(u.nama, \'None\') AS nama_dokter
             FROM rekam_medis rm
             LEFT JOIN pasien p ON rm.pasien_id = p.id
             LEFT JOIN users u ON rm.dokter_id = u.id
             WHERE rm.pasien_id = ? AND rm.is_deleted = 0
             ORDER BY rm.created_at DESC'
        );
        $stmt->execute([$pasienId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT rm.*, p.nama AS nama_pasien, COALESCE(u.nama, \'None\') AS nama_dokter
             FROM rekam_medis rm
             LEFT JOIN pasien p ON rm.pasien_id = p.id
             LEFT JOIN users u ON rm.dokter_id = u.id
             WHERE rm.id = ? AND rm.is_deleted = 0'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(
        int $pasienId,
        ?int $dokterId,
        string $keluhan,
        string $diagnosa,
        string $obat,
        string $catatan
    ): int|false {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO rekam_medis (pasien_id, dokter_id, keluhan, diagnosa, obat, catatan)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $pasienId,
                $dokterId,
                htmlspecialchars($keluhan),
                htmlspecialchars($diagnosa),
                htmlspecialchars($obat),
                htmlspecialchars($catatan),
            ]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException) {
            return false;
        }
    }

    public function update(
        int $id,
        string $keluhan,
        string $diagnosa,
        string $obat,
        string $catatan
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE rekam_medis SET keluhan = ?, diagnosa = ?, obat = ?, catatan = ? WHERE id = ?'
        );
        return $stmt->execute([
            htmlspecialchars($keluhan),
            htmlspecialchars($diagnosa),
            htmlspecialchars($obat),
            htmlspecialchars($catatan),
            $id,
        ]);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE rekam_medis SET is_deleted = 1, deleted_at = NOW() WHERE id = ?'
        );
        return $stmt->execute([$id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM rekam_medis WHERE is_deleted = 0')->fetchColumn();
    }

    public function countByDokter(int $dokterId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM rekam_medis WHERE dokter_id = ? AND is_deleted = 0');
        $stmt->execute([$dokterId]);
        return (int) $stmt->fetchColumn();
    }

    public function countByPasien(int $pasienId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM rekam_medis WHERE pasien_id = ? AND is_deleted = 0');
        $stmt->execute([$pasienId]);
        return (int) $stmt->fetchColumn();
    }
}
