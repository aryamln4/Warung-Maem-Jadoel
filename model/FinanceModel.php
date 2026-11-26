<?php
declare(strict_types=1);
class FinanceModel extends BaseModel {
    public function add(array $d): void {
        $this->db->prepare("INSERT INTO keuangan(tanggal,jenis,sumber,kategori,jumlah,keterangan) VALUES(?,?,'MANUAL',?,?,?)")
            ->execute([$d['tanggal'], $d['jenis'], $d['kategori'], $d['jumlah'], $d['keterangan'] ?: null]);
    }

    public function summary(string $from, string $to): array {
        $s = $this->db->prepare("SELECT jenis,SUM(jumlah) total FROM keuangan WHERE tanggal BETWEEN ? AND ? GROUP BY jenis");
        $s->execute([$from, $to]);
        $r = ['PEMASUKAN' => 0, 'PENGELUARAN' => 0];
        foreach ($s as $x) $r[$x['jenis']] = (float)$x['total'];
        // Penjualan selesai juga pendapatan, walau tidak dicatat manual ke tabel keuangan.
        $sales = $this->db->prepare("SELECT COALESCE(SUM(total_bayar),0) FROM transaksi_penjualan WHERE status_pembayaran='TERVERIFIKASI' AND status_pesanan='SELESAI' AND DATE(tanggal) BETWEEN ? AND ?");
        $sales->execute([$from, $to]);
        $r['PENJUALAN'] = (float)$sales->fetchColumn();
        $r['PEMASUKAN_TOTAL'] = $r['PEMASUKAN'] + $r['PENJUALAN'];
        $r['LABA_RUGI'] = $r['PEMASUKAN_TOTAL'] - $r['PENGELUARAN'];
        return $r;
    }

    public function salesSummary(string $from, string $to): array {
        $s = $this->db->prepare("SELECT DATE(tanggal) tanggal,COUNT(*) transaksi,SUM(total_bayar) total FROM transaksi_penjualan WHERE status_pembayaran='TERVERIFIKASI' AND status_pesanan='SELESAI' AND DATE(tanggal) BETWEEN ? AND ? GROUP BY DATE(tanggal) ORDER BY tanggal");
        $s->execute([$from, $to]);
        return $s->fetchAll();
    }

    public function transactionSales(string $from, string $to): array {
        $s = $this->db->prepare("SELECT t.nomor_pesanan,t.tanggal,COALESCE(NULLIF(TRIM(p.nama),''),NULLIF(TRIM(p.username),''),'Pelanggan') pelanggan,t.jenis_pesanan,t.metode_bayar,t.status_pesanan,t.total_bayar FROM transaksi_penjualan t JOIN pelanggan p ON p.id=t.pelanggan_id WHERE t.status_pembayaran='TERVERIFIKASI' AND t.status_pesanan='SELESAI' AND DATE(t.tanggal) BETWEEN ? AND ? ORDER BY t.tanggal DESC,t.id DESC");
        $s->execute([$from, $to]);
        return $s->fetchAll();
    }

    public function topProducts(string $from, string $to): array {
        $s = $this->db->prepare("SELECT m.nama_produk,SUM(d.qty) qty,SUM(d.subtotal) omzet FROM detail_transaksi d JOIN transaksi_penjualan t ON t.id=d.transaksi_id JOIN menu m ON m.id=d.menu_id WHERE t.status_pembayaran='TERVERIFIKASI' AND t.status_pesanan='SELESAI' AND DATE(t.tanggal) BETWEEN ? AND ? GROUP BY d.menu_id ORDER BY qty DESC LIMIT 10");
        $s->execute([$from, $to]);
        return $s->fetchAll();
    }

    // ---------------------------------------------------------------
    // Operasional: pengeluaran (gas, listrik, kemasan, dll).
    // Satu sumber data dengan laporan laba/rugi: tabel keuangan.
    // ---------------------------------------------------------------
    public function expenses(string $from, string $to): array {
        $s = $this->db->prepare("SELECT * FROM keuangan WHERE jenis='PENGELUARAN' AND sumber='OPERASIONAL' AND tanggal BETWEEN ? AND ? AND id NOT IN (SELECT keuangan_id FROM pembelian_bahan WHERE keuangan_id IS NOT NULL) ORDER BY tanggal DESC,id DESC");
        $s->execute([$from, $to]);
        return $s->fetchAll();
    }

    public function recentExpenses(int $limit = 8): array {
        $limit = max(1, min(50, $limit));
        return $this->db->query("SELECT * FROM keuangan WHERE jenis='PENGELUARAN' AND sumber='OPERASIONAL' ORDER BY tanggal DESC,id DESC LIMIT {$limit}")->fetchAll();
    }

    public function expenseSummary(string $from, string $to): array {
        $s = $this->db->prepare("SELECT COUNT(*) jumlah,COALESCE(SUM(jumlah),0) total FROM keuangan WHERE jenis='PENGELUARAN' AND sumber='OPERASIONAL' AND tanggal BETWEEN ? AND ?");
        $s->execute([$from, $to]);
        $row = $s->fetch() ?: ['jumlah' => 0, 'total' => 0];
        $by = $this->db->prepare("SELECT kategori,SUM(jumlah) total FROM keuangan WHERE jenis='PENGELUARAN' AND sumber='OPERASIONAL' AND tanggal BETWEEN ? AND ? GROUP BY kategori ORDER BY total DESC");
        $by->execute([$from, $to]);
        return ['jumlah' => (int)$row['jumlah'], 'total' => (float)$row['total'], 'kategori' => $by->fetchAll()];
    }

    public function expenseCategories(): array {
        return $this->db->query("SELECT DISTINCT kategori FROM keuangan WHERE jenis='PENGELUARAN' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function addExpense(array $d): void {
        $this->db->prepare("INSERT INTO keuangan(tanggal,jenis,sumber,kategori,jumlah,keterangan,bukti,created_by) VALUES(?,'PENGELUARAN','OPERASIONAL',?,?,?,?,?)")
            ->execute([
                $d['tanggal'], trim((string)$d['kategori']), max(0, (float)$d['jumlah']),
                trim((string)($d['keterangan'] ?? '')) ?: null,
                trim((string)($d['bukti'] ?? '')) ?: null,
                trim((string)($d['created_by'] ?? '')) ?: null,
            ]);
    }

    public function updateExpense(int $id, array $d): void {
        $this->db->prepare("UPDATE keuangan SET tanggal=?,kategori=?,jumlah=?,keterangan=?,bukti=? WHERE id=? AND jenis='PENGELUARAN' AND sumber='OPERASIONAL' AND id NOT IN (SELECT keuangan_id FROM pembelian_bahan WHERE keuangan_id IS NOT NULL)")
            ->execute([
                $d['tanggal'], trim((string)$d['kategori']), max(0, (float)$d['jumlah']),
                trim((string)($d['keterangan'] ?? '')) ?: null,
                trim((string)($d['bukti'] ?? '')) ?: null, $id,
            ]);
    }

    public function deleteExpense(int $id): void {
        $this->db->prepare("DELETE FROM keuangan WHERE id=? AND jenis='PENGELUARAN' AND sumber='OPERASIONAL' AND id NOT IN (SELECT keuangan_id FROM pembelian_bahan WHERE keuangan_id IS NOT NULL)")->execute([$id]);
    }

    public function findExpense(int $id): ?array {
        $s = $this->db->prepare("SELECT * FROM keuangan WHERE id=? AND jenis='PENGELUARAN' AND sumber='OPERASIONAL' AND id NOT IN (SELECT keuangan_id FROM pembelian_bahan WHERE keuangan_id IS NOT NULL) LIMIT 1");
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function isPurchaseExpense(int $id): bool {
        $s = $this->db->prepare('SELECT 1 FROM pembelian_bahan WHERE keuangan_id=? LIMIT 1');
        $s->execute([$id]);
        return (bool)$s->fetchColumn();
    }
}
