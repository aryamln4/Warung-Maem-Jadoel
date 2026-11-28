<?php
declare(strict_types=1);
/**
 * Resep produk: takaran bahan baku per 1 porsi produk.
 * Propagasi stok: selisih stok produk (manual) diteruskan ke stok bahan baku.
 */
class RecipeModel extends BaseModel {
    public function forMenu(int $menuId): array {
        $s = $this->db->prepare("SELECT r.*,b.nama_bahan,b.kode_produk,b.satuan,b.stok stok_bahan,COALESCE(r.satuan_resep,CASE b.satuan WHEN 'kilo' THEN 'kg' WHEN 'pcs' THEN 'pieces' ELSE 'gram' END) satuan_resep
            FROM resep_produk r JOIN bahan_baku b ON b.id=r.bahan_baku_id
            WHERE r.menu_id=? ORDER BY b.nama_bahan");
        $s->execute([$menuId]);
        return $s->fetchAll();
    }
    public function ingredients(): array {
        return $this->db->query("SELECT id,kode_produk,nama_bahan,satuan,stok,harga FROM bahan_baku ORDER BY nama_bahan")->fetchAll();
    }
    public function save(int $menuId,
    array $rows): void {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM resep_produk WHERE menu_id=?")->execute([$menuId]);
            $ins = $this->db->prepare("INSERT INTO resep_produk(menu_id,bahan_baku_id,jumlah,keterangan,satuan_resep) VALUES(?,?,?,?,?)");
            foreach ($rows as $row) {
                $bahanId = (int)($row['bahan_baku_id'] ?? 0);
                $jumlah = (float)($row['jumlah'] ?? 0);
                if ($bahanId <= 0 || $jumlah <= 0) continue;
                $unit = trim((string)($row['satuan_resep'] ?? ''));
                if (!in_array($unit, ['kg','gram','pieces'], true)) $unit = null;
                if ($unit !== null) {
                    $check=$this->db->prepare('SELECT satuan FROM bahan_baku WHERE id=? LIMIT 1');$check->execute([$bahanId]);$stockUnit=(string)$check->fetchColumn();
                    $stockGroup=($stockUnit==='pcs')?'pieces':(($stockUnit==='kilo')?'kg':'gram');
                    if (($unit==='pieces') !== ($stockGroup==='pieces')) throw new RuntimeException('Satuan resep tidak cocok dengan satuan stok bahan.');
                }
                $ins->execute([$menuId,
                $bahanId,
                $jumlah,
                trim((string)($row['keterangan'] ?? '')) ?: null,
                $unit]);
            }
            $this->db->commit();
        }  catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
    /** Ringkasan semua produk + jumlah bahan pada resepnya. */
    public function summary(): array {
        return $this->db->query("SELECT m.id,m.nama_produk,m.foto,m.status_aktif,km.nama kategori,COALESCE(sp.jumlah_stok,0) stok,
                (SELECT COUNT(*) FROM resep_produk r WHERE r.menu_id=m.id) bahan_count
            FROM menu m JOIN kategori_menu km ON km.id=m.kategori_id LEFT JOIN stok_produk sp ON sp.menu_id=m.id
            ORDER BY m.status_aktif DESC,m.nama_produk")->fetchAll();
    }
    /**
     * Teruskan perubahan stok produk ke stok bahan baku.
     * Dipanggil di dalam transaksi InventoryModel::setStocks().
     * delta > 0 = stok produk bertambah -> bahan baku bertambah sesuai takaran resep.
     */
    public function propagate(int $menuId,
    int $delta): void {
        if ($delta <= 0) return;
        // permintaan owner: penambahan stok produk menambah stok bahan
        $rows = $this->forMenu($menuId);
        if (!$rows) return;
        $upd = $this->db->prepare("UPDATE bahan_baku SET stok=stok+? WHERE id=?");
        $log = $this->db->prepare("INSERT INTO kartu_stok_bahan(bahan_baku_id,jenis,jumlah,keterangan) VALUES(?,?,?,?)");
        $jenis = $delta > 0 ? 'MASUK' : 'KELUAR';
        foreach ($rows as $r) {
            $unitRecipe = (string)($r['satuan_resep'] ?? $r['satuan']);
            $unitStock = (string)$r['satuan'];
            $amountRecipe = (float)$r['jumlah'] * abs($delta);
            $amount = self::convertRecipeAmount($amountRecipe, $unitRecipe, $unitStock);
            if ($amount <= 0) continue;
            $upd->execute([$delta > 0 ? $amount : -$amount,
            (int)$r['bahan_baku_id']]);
            $log->execute([(int)$r['bahan_baku_id'],
            $jenis,
            $amount,
            'Sinkron resep '.$r['nama_bahan'].' untuk produk #'.$menuId.' ('.($delta > 0 ? '+' : '').$delta.' porsi)']);
        }
    }
    /** Convert recipe units into ingredient stock unit, preserving legacy units. */
    private static function convertRecipeAmount(float $amount, string $from, string $to): float {
        $from = $from === 'kilo' ? 'kg' : ($from === 'pcs' ? 'pieces' : $from);
        $to = $to === 'kilo' ? 'kg' : ($to === 'pcs' ? 'pieces' : $to);
        if ($from === $to) return $amount;
        if ($from === 'kg' && $to === 'gram') return $amount * 1000;
        if ($from === 'gram' && $to === 'kg') return $amount / 1000;
        return $amount;
    }
}
