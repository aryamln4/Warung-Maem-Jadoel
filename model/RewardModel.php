<?php
declare(strict_types=1);
class RewardModel extends BaseModel {
    public function activeBonus(string $type): ?array {
        $s=$this->db->prepare("SELECT b.*,m.nama_produk FROM bonus_produk b JOIN menu m ON m.id=b.menu_id WHERE b.jenis_reward=? AND b.status_aktif=1 AND (b.tanggal_mulai IS NULL OR b.tanggal_mulai<=CURDATE()) AND (b.tanggal_selesai IS NULL OR b.tanggal_selesai>=CURDATE()) ORDER BY b.id DESC LIMIT 1");
        $s->execute([$type]);
        return $s->fetch()?:null;
    }
    public function logExists(int $tx,
    string $type): bool {
        $s=$this->db->prepare("SELECT 1 FROM reward_log WHERE transaksi_id=? AND jenis_reward=? LIMIT 1");
        $s->execute([$tx,
        $type]);
        return (bool)$s->fetchColumn();
    }
    public function addLog(int $customer,
    int $tx,
    ?int $bonus,
    string $type): void {
        $this->db->prepare("INSERT INTO reward_log(pelanggan_id,transaksi_id,bonus_produk_id,jenis_reward) VALUES(?,?,?,?)")->execute([$customer,
        $tx,
        $bonus,
        $type]);
    }
    public function customerPool(int $customer): int {
        $s=$this->db->prepare("SELECT total_produk_kumulatif FROM pelanggan WHERE id=? FOR UPDATE");
        $s->execute([$customer]);
        return (int)$s->fetchColumn();
    }
    public function updatePool(int $customer,
    int $pool): void {
        $this->db->prepare("UPDATE pelanggan SET total_produk_kumulatif=? WHERE id=?")->execute([$pool,
        $customer]);
    }
    public function rewardsForCustomer(int $customer): array {
        $s=$this->db->prepare("SELECT r.*,b.menu_id,m.nama_produk FROM reward_log r LEFT JOIN bonus_produk b ON b.id=r.bonus_produk_id LEFT JOIN menu m ON m.id=b.menu_id WHERE r.pelanggan_id=? ORDER BY r.id DESC");
        $s->execute([$customer]);
        return $s->fetchAll();
    }
    public function listBonuses(): array {
        return $this->db->query("SELECT b.*,m.nama_produk FROM bonus_produk b JOIN menu m ON m.id=b.menu_id ORDER BY b.id DESC")->fetchAll();
    }
    public function saveBonus(array $d,
    ?int $id=null): int {
        $this->db->beginTransaction();
        try {
            if($d['status_aktif']){
                $this->db->prepare("UPDATE bonus_produk SET status_aktif=0 WHERE jenis_reward=? AND id<>?")->execute([$d['jenis_reward'],
                $id??0]);
            }
            if($id){
                $this->db->prepare("UPDATE bonus_produk SET menu_id=?,jenis_reward=?,status_aktif=?,tanggal_mulai=?,tanggal_selesai=?,keterangan=? WHERE id=?")->execute([$d['menu_id'],
                $d['jenis_reward'],
                $d['status_aktif'],
                $d['tanggal_mulai']?:null,
                $d['tanggal_selesai']?:null,
                $d['keterangan']?:null,
                $id]);
            }  else {
                $this->db->prepare("INSERT INTO bonus_produk(menu_id,jenis_reward,status_aktif,tanggal_mulai,tanggal_selesai,keterangan) VALUES(?,?,?,?,?,?)")->execute([$d['menu_id'],
                $d['jenis_reward'],
                $d['status_aktif'],
                $d['tanggal_mulai']?:null,
                $d['tanggal_selesai']?:null,
                $d['keterangan']?:null]);
                $id=(int)$this->db->lastInsertId();
            }
            $this->db->commit();
            return $id;
        } catch(Throwable $e){
            $this->db->rollBack();
            throw $e;
        }
    }
    public function deleteBonus(int $id): void {
        $this->db->prepare("DELETE FROM bonus_produk WHERE id=?")->execute([$id]);
    }
    public function findBonus(int $id): ?array {
        $s=$this->db->prepare("SELECT * FROM bonus_produk WHERE id=?");
        $s->execute([$id]);
        return $s->fetch()?:null;
    }
}
