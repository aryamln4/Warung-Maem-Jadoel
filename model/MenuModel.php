<?php
declare(strict_types=1);
class MenuModel extends BaseModel {
    public function allForAdmin(): array {
        return $this->db->query("SELECT m.*, k.nama kategori FROM menu m JOIN kategori_menu k ON k.id=m.kategori_id ORDER BY m.status_aktif DESC, k.nama, m.nama_produk, m.id")->fetchAll();
    }
    public function setActive(int $id,
    int $active): void {
        $this->db->prepare("UPDATE menu SET status_aktif=? WHERE id=?")->execute([$active,
        $id]);
    }
    public function deletePermanently(int $id): void {
        $check=$this->db->prepare("SELECT (SELECT COUNT(*) FROM detail_transaksi WHERE menu_id=?) AS transaksi_ref, (SELECT COUNT(*) FROM bonus_produk WHERE menu_id=?) AS bonus_ref FROM menu WHERE id=?");
        $check->execute([$id,
        $id,
        $id]);
        $row=$check->fetch();
        if(!$row) throw new RuntimeException('Produk tidak ditemukan.');
        if((int)$row['transaksi_ref']>0) throw new RuntimeException('Produk sudah dipakai pada transaksi sehingga tidak dapat dihapus permanen. Gunakan Arsip.');
        if((int)$row['bonus_ref']>0) throw new RuntimeException('Produk masih dipakai sebagai produk bonus. Hapus konfigurasi bonus terlebih dahulu atau gunakan Arsip.');
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM menu WHERE id=?")->execute([$id]);
            $this->db->commit();
        }  catch(Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function saveCategory(string $name,
    ?int $id=null): void {
        if($id){
            $this->db->prepare("UPDATE kategori_menu SET nama=? WHERE id=?")->execute([$name,
            $id]);
        } else{
            $this->db->prepare("INSERT INTO kategori_menu(nama) VALUES(?)")->execute([$name]);
        }
    }
    public function deleteCategory(int $id): void {
        $this->db->prepare("DELETE FROM kategori_menu WHERE id=?")->execute([$id]);
    }
    public function saveTopping(string $name,
    float $price,
    ?int $id=null): void {
        if($id){
            $this->db->prepare("UPDATE topping SET nama=?,harga_tambahan=? WHERE id=?")->execute([$name,
            $price,
            $id]);
        } else{
            $this->db->prepare("INSERT INTO topping(nama,harga_tambahan,status_aktif) VALUES(?,?,1)")->execute([$name,
            $price]);
        }
    }
    public function deleteTopping(int $id): void {
        $this->db->prepare("DELETE FROM topping WHERE id=?")->execute([$id]);
    }
    public function allActive(): array {
        return $this->db->query("SELECT m.*, k.nama kategori, COALESCE(s.jumlah_stok,0) stok
            FROM menu m
            JOIN kategori_menu k ON k.id=m.kategori_id
            LEFT JOIN stok_produk s ON s.menu_id=m.id
            WHERE m.status_aktif=1
            ORDER BY k.nama,m.nama_produk,m.id")->fetchAll();
    }
    public function byId(int $id): ?array {
        $s=$this->db->prepare("SELECT m.*,k.nama kategori FROM menu m JOIN kategori_menu k ON k.id=m.kategori_id WHERE m.id=?");
        $s->execute([$id]);
        return $s->fetch()?:null;
    }
    public function toppingsFor(int $menuId): array {
        $s=$this->db->prepare("SELECT t.* FROM topping t JOIN menu_topping mt ON mt.topping_id=t.id WHERE mt.menu_id=? AND t.status_aktif=1 ORDER BY t.nama");
        $s->execute([$menuId]);
        return $s->fetchAll();
    }
    public function categories(): array {
        return $this->db->query("SELECT * FROM kategori_menu ORDER BY nama")->fetchAll();
    }
    public function save(array $d,
    ?int $id=null): int {
        if ($id) {
            $s=$this->db->prepare("UPDATE menu SET kategori_id=?,nama_produk=?,deskripsi=?,harga=?,punya_topping=?,punya_level_pedas=?,foto=? WHERE id=?");
            $s->execute([$d['kategori_id'],
            $d['nama_produk'],
            $d['deskripsi']?:null,
            $d['harga'],
            $d['punya_topping'],
            $d['punya_level_pedas'],
            $d['foto']?:null,
            $id]);
            return $id;
        }
        $s=$this->db->prepare("INSERT INTO menu(kategori_id,nama_produk,deskripsi,harga,punya_topping,punya_level_pedas,foto) VALUES(?,?,?,?,?,?,?)");
        $s->execute([$d['kategori_id'],
        $d['nama_produk'],
        $d['deskripsi']?:null,
        $d['harga'],
        $d['punya_topping'],
        $d['punya_level_pedas'],
        $d['foto']?:null]);
        $id=(int)$this->db->lastInsertId();
        $this->db->prepare("INSERT INTO stok_produk(menu_id,jumlah_stok,stok_minimum) VALUES(?,?,?)")->execute([$id,
        (int)$d['stok_awal'],
        5]);
        if ((int)$d['stok_awal']>0) $this->db->prepare("INSERT INTO kartu_stok(stok_produk_id,jenis,jumlah,keterangan) SELECT id,'MASUK',?, 'Stok awal produk' FROM stok_produk WHERE menu_id=?")->execute([(int)$d['stok_awal'],
        $id]);
        return $id;
    }
    public function syncToppings(int $menuId,
    array $ids): void {
        $this->db->prepare("DELETE FROM menu_topping WHERE menu_id=?")->execute([$menuId]);
        $s=$this->db->prepare("INSERT INTO menu_topping(menu_id,topping_id) VALUES(?,?)");
        foreach(array_unique(array_map('intval',
        $ids)) as $id) $s->execute([$menuId,
        $id]);
    }
    public function toppings(): array {
        return $this->db->query("SELECT * FROM topping WHERE status_aktif=1 ORDER BY nama")->fetchAll();
    }
    public function featured(): array {
        return $this->db->query("SELECT m.*, k.nama kategori, COALESCE(s.jumlah_stok,0) stok FROM menu m JOIN kategori_menu k ON k.id=m.kategori_id LEFT JOIN stok_produk s ON s.menu_id=m.id WHERE m.status_aktif=1 ORDER BY m.created_at DESC, m.id DESC LIMIT 6")->fetchAll();
    }
}
