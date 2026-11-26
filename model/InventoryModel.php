<?php
declare(strict_types=1);
class InventoryModel extends BaseModel {
    public function all(): array {
        return $this->db->query("SELECT s.*,m.nama_produk,m.satuan,m.foto,m.status_aktif FROM stok_produk s JOIN menu m ON m.id=s.menu_id ORDER BY m.nama_produk")->fetchAll();
    }
    /**
     * Menetapkan stok akhir secara batch dari halaman inventori.
     * Hanya produk yang nilainya berubah yang dicatat sebagai PENYESUAIAN.
     */
    public function setStocks(array $stocks): void {
        $this->db->beginTransaction();
        try {
            $select=$this->db->prepare("SELECT jumlah_stok,menu_id FROM stok_produk WHERE id=? FOR UPDATE");
            $update=$this->db->prepare("UPDATE stok_produk SET jumlah_stok=? WHERE id=?");
            $log=$this->db->prepare("INSERT INTO kartu_stok(stok_produk_id,jenis,jumlah,keterangan) VALUES(?, 'PENYESUAIAN', ?, ?)");
            foreach($stocks as $stockId=>$value){
                $id=(int)$stockId;
                if($id<=0) continue;
                $new=(int)$value;
                if($new<0) throw new RuntimeException('Stok tidak boleh negatif.');
                $select->execute([$id]);
                $row=$select->fetch();
                if($row===false) throw new RuntimeException('Data stok produk tidak ditemukan.');
                $current=(int)$row['jumlah_stok'];
                $menuId=(int)$row['menu_id'];
                if($new===$current) continue;
                $update->execute([$new,
                $id]);
                $log->execute([$id,
                abs($new-$current),
                'Penyesuaian stok dari halaman inventori']);
                // Sinkron resep: tambahan stok produk diteruskan ke stok bahan baku.
                (new RecipeModel())->propagate($menuId,
                $new-$current);
            }
            $this->db->commit();
        } catch(Throwable $e){
            if($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
    /** Riwayat pembelian bahan terakhir (untuk tab Stok Bahan Baku). */
    public function purchases(int $limit = 20): array {
        $limit = max(1, min(100, $limit));
        return $this->db->query("SELECT p.*,b.nama_bahan,b.satuan FROM pembelian_bahan p JOIN bahan_baku b ON b.id=p.bahan_baku_id ORDER BY p.tanggal DESC,p.id DESC LIMIT {$limit}")->fetchAll();
    }

    /**
     * Catat pembelian bahan: stok bertambah, kartu stok MASUK dibuat,
     * dan satu baris pengeluaran masuk ke tabel keuangan.
     */
    public function recordPurchase(int $bahanId,
    float $qty,
    string $tanggal,
    string $keterangan = '',
    string $createdBy = 'admin',
    ?float $hargaSatuan = null): int {
        $this->db->beginTransaction();
        try {
            $id = $this->insertPurchase($bahanId,
            $qty,
            $tanggal,
            $keterangan,
            $createdBy,
            $hargaSatuan);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /** Buat bahan baru sekaligus mencatat stok awal sebagai pembelian. */
    public function createBahan(array $d, string $createdBy = 'admin'): int {
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare('INSERT INTO bahan_baku(kode_produk,nama_bahan,tanggal_kadaluarsa,stok,satuan,harga) VALUES(?,?,?,0,?,?)');
            $q->execute([
                trim((string)$d['kode_produk']),
                trim((string)$d['nama_bahan']),
                (string)$d['tanggal_kadaluarsa'],
                (string)$d['satuan'],
                max(0, (float)$d['harga']),
            ]);
            $id = (int)$this->db->lastInsertId();
            $stok = max(0, (float)$d['stok']);
            if ($stok > 0) {
                $this->insertPurchase($id,
                $stok,
                (string)$d['tanggal_kadaluarsa'],
                'Stok awal bahan baru',
                $createdBy,
                (float)$d['harga']);
            }
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /** Batalkan pembelian: stok dikurangi dan pengeluaran terkait dihapus. */
    public function cancelPurchase(int $purchaseId, string $createdBy = 'admin'): void {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT p.*,b.nama_bahan,b.satuan,b.stok FROM pembelian_bahan p JOIN bahan_baku b ON b.id=p.bahan_baku_id WHERE p.id=? LIMIT 1 FOR UPDATE");
            $s->execute([$purchaseId]);
            $row = $s->fetch();
            if (!$row) throw new RuntimeException('Data pembelian tidak ditemukan.');
            if (($row['status'] ?? 'AKTIF') !== 'AKTIF') throw new RuntimeException('Pembelian sudah dibatalkan sebelumnya.');
            $qty = (float)$row['jumlah'];
            if ((float)$row['stok'] + 0.0001 < $qty) {
                throw new RuntimeException('Stok bahan sudah terpakai sehingga pembelian tidak dapat dibatalkan. Lakukan penyesuaian stok manual.');
            }
            $this->db->prepare('UPDATE bahan_baku SET stok=stok-? WHERE id=?')->execute([$qty, (int)$row['bahan_baku_id']]);
            $this->db->prepare("INSERT INTO kartu_stok_bahan(bahan_baku_id,jenis,jumlah,keterangan) VALUES(?,'KELUAR',?,?)")
                ->execute([(int)$row['bahan_baku_id'], $qty, 'Pembatalan pembelian #'.$purchaseId]);
            if (!empty($row['keuangan_id'])) {
                $this->db->prepare('DELETE FROM keuangan WHERE id=?')->execute([(int)$row['keuangan_id']]);
            }
            $this->db->prepare("UPDATE pembelian_bahan SET status='DIBATALKAN' WHERE id=?")->execute([$purchaseId]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /** Internal: dijalankan di dalam transaksi aktif. */
    private function insertPurchase(int $bahanId,
    float $qty,
    string $tanggal,
    string $keterangan,
    string $createdBy,
    ?float $hargaSatuan = null): int {
        $qty = round($qty, 3);
        if ($bahanId < 1 || $qty <= 0) throw new RuntimeException('Jumlah pembelian harus lebih dari nol.');
        $b = $this->db->prepare('SELECT id,nama_bahan,satuan,harga FROM bahan_baku WHERE id=? LIMIT 1 FOR UPDATE');
        $b->execute([$bahanId]);
        $bahan = $b->fetch();
        if (!$bahan) throw new RuntimeException('Bahan baku tidak ditemukan.');
        $harga = round($hargaSatuan !== null ? max(0, $hargaSatuan) : (float)$bahan['harga'], 2);
        $total = round($qty * $harga, 2);
        $this->db->prepare("INSERT INTO pembelian_bahan(bahan_baku_id,jumlah,harga_satuan,total,tanggal,status,keterangan,created_by) VALUES(?,?,?,?,?,'AKTIF',?,?)")
            ->execute([$bahanId, $qty, $harga, $total, $tanggal, trim($keterangan) ?: null, $createdBy]);
        $purchaseId = (int)$this->db->lastInsertId();
        $this->db->prepare('UPDATE bahan_baku SET stok=stok+? WHERE id=?')->execute([$qty, $bahanId]);
        $this->db->prepare("INSERT INTO kartu_stok_bahan(bahan_baku_id,jenis,jumlah,keterangan) VALUES(?,'MASUK',?,?)")
            ->execute([$bahanId, $qty, 'Pembelian #'.$purchaseId]);
        $note = 'Pembelian '.$bahan['nama_bahan'].' '.$qty.' '.$bahan['satuan'];
        if (trim($keterangan) !== '') $note .= ' — '.trim($keterangan);
        $this->db->prepare("INSERT INTO keuangan(tanggal,jenis,sumber,kategori,jumlah,keterangan,bukti,created_by) VALUES(?,'PENGELUARAN','OPERASIONAL','Bahan Baku',?,?,?,?)")
            ->execute([$tanggal, $total, $note, 'PEMBELIAN-BAHAN#'.$purchaseId, $createdBy]);
        $keuanganId = (int)$this->db->lastInsertId();
        $this->db->prepare('UPDATE pembelian_bahan SET keuangan_id=? WHERE id=?')->execute([$keuanganId, $purchaseId]);
        return $purchaseId;
    }

    // Dipertahankan untuk kompatibilitas dengan route/form lama.
    public function adjust(int $stockId,
    int $amount,
    string $type,
    string $note): void {
        if(!in_array($type,
        ['MASUK',
        'KELUAR',
        'PENYESUAIAN'],
        true)) throw new InvalidArgumentException('Jenis stok salah');
        $this->db->beginTransaction();
        try {
            $s=$this->db->prepare("SELECT jumlah_stok FROM stok_produk WHERE id=? FOR UPDATE");
            $s->execute([$stockId]);
            $current=(int)$s->fetchColumn();
            $new=$type==='MASUK'?$current+$amount:($type==='KELUAR'?$current-$amount:$amount);
            if($new<0)throw new RuntimeException('Stok tidak boleh negatif.');
            $this->db->prepare("UPDATE stok_produk SET jumlah_stok=? WHERE id=?")->execute([$new,
            $stockId]);
            $this->db->prepare("INSERT INTO kartu_stok(stok_produk_id,jenis,jumlah,keterangan) VALUES(?,?,?,?)")->execute([$stockId,
            $type,
            abs($amount),
            $note?:null]);
            $this->db->commit();
        } catch(Throwable $e){
            $this->db->rollBack();
            throw $e;
        }
    }
}
