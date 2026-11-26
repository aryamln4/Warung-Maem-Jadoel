<?php
declare(strict_types=1);
/** Buku alamat pelanggan: teks + titik koordinat untuk delivery. */
class AddressModel extends BaseModel {
    public function all(int $customerId): array {
        $s = $this->db->prepare("SELECT * FROM alamat_pelanggan WHERE pelanggan_id=? AND status_aktif=1 ORDER BY is_utama DESC, id DESC");
        $s->execute([$customerId]);
        return $s->fetchAll();
    }
    public function find(int $id,
    int $customerId): ?array {
        $s = $this->db->prepare("SELECT * FROM alamat_pelanggan WHERE id=? AND pelanggan_id=? AND status_aktif=1 LIMIT 1");
        $s->execute([$id,
        $customerId]);
        return $s->fetch() ?: null;
    }
    public function primary(int $customerId): ?array {
        $s = $this->db->prepare("SELECT * FROM alamat_pelanggan WHERE pelanggan_id=? AND status_aktif=1 ORDER BY is_utama DESC, id DESC LIMIT 1");
        $s->execute([$customerId]);
        return $s->fetch() ?: null;
    }
    public function save(int $customerId,
    array $d,
    ?int $id = null): int {
        $this->db->beginTransaction();
        try {
            $count = $this->db->prepare("SELECT COUNT(*) FROM alamat_pelanggan WHERE pelanggan_id=? AND status_aktif=1");
            $count->execute([$customerId]);
            $first = (int)$count->fetchColumn() === 0;
            $isPrimary = $first || !empty($d['is_utama']) ? 1 : 0;
            $payload = [
            'label' => trim((string)($d['label'] ?? 'Rumah')) ?: 'Rumah',
            'nama_penerima' => trim((string)($d['nama_penerima'] ?? '')),
            'no_hp' => trim((string)($d['no_hp'] ?? '')),
            'alamat' => trim((string)($d['alamat'] ?? '')),
            'patokan' => trim((string)($d['patokan'] ?? '')) ?: null,
            'latitude' => $d['latitude'] !== null && $d['latitude'] !== '' ? (float)$d['latitude'] : null,
            'longitude' => $d['longitude'] !== null && $d['longitude'] !== '' ? (float)$d['longitude'] : null,
            'is_utama' => $isPrimary,
            ];
            if ($id) {
                $owned = $this->find($id,
                $customerId);
                if (!$owned) throw new RuntimeException('Alamat tidak ditemukan.');
                if ($isPrimary) {
                    $this->db->prepare("UPDATE alamat_pelanggan SET is_utama=0 WHERE pelanggan_id=?")->execute([$customerId]);
                }
                $this->db->prepare("UPDATE alamat_pelanggan SET label=?,nama_penerima=?,no_hp=?,alamat=?,patokan=?,latitude=?,longitude=?,is_utama=? WHERE id=? AND pelanggan_id=?")
                ->execute([$payload['label'],
                $payload['nama_penerima'],
                $payload['no_hp'],
                $payload['alamat'],
                $payload['patokan'],
                $payload['latitude'],
                $payload['longitude'],
                $payload['is_utama'],
                $id,
                $customerId]);
            }  else {
                if ($isPrimary) $this->db->prepare("UPDATE alamat_pelanggan SET is_utama=0 WHERE pelanggan_id=?")->execute([$customerId]);
                $this->db->prepare("INSERT INTO alamat_pelanggan(pelanggan_id,label,nama_penerima,no_hp,alamat,patokan,latitude,longitude,is_utama,status_aktif) VALUES(?,?,?,?,?,?,?,?,?,1)")
                ->execute([$customerId,
                $payload['label'],
                $payload['nama_penerima'],
                $payload['no_hp'],
                $payload['alamat'],
                $payload['patokan'],
                $payload['latitude'],
                $payload['longitude'],
                $payload['is_utama']]);
                $id = (int)$this->db->lastInsertId();
            }
            $this->db->commit();
            return (int)$id;
        }  catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
    public function setPrimary(int $id,
    int $customerId): void {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE alamat_pelanggan SET is_utama=0 WHERE pelanggan_id=?")->execute([$customerId]);
            $this->db->prepare("UPDATE alamat_pelanggan SET is_utama=1 WHERE id=? AND pelanggan_id=? AND status_aktif=1")->execute([$id,
            $customerId]);
            $this->db->commit();
        }  catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
    public function deactivate(int $id,
    int $customerId): void {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT is_utama FROM alamat_pelanggan WHERE id=? AND pelanggan_id=? FOR UPDATE");
            $s->execute([$id,
            $customerId]);
            $wasPrimary = (int)($s->fetchColumn() ?: 0) === 1;
            $this->db->prepare("UPDATE alamat_pelanggan SET status_aktif=0, is_utama=0 WHERE id=? AND pelanggan_id=?")->execute([$id,
            $customerId]);
            if ($wasPrimary) {
                $this->db->prepare("UPDATE alamat_pelanggan SET is_utama=1 WHERE pelanggan_id=? AND status_aktif=1 ORDER BY id DESC LIMIT 1")->execute([$customerId]);
            }
            $this->db->commit();
        }  catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
