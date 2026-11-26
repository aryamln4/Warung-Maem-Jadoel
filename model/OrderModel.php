<?php
declare(strict_types=1);
class OrderModel extends BaseModel {
    public function activeTables(): array {
        return $this->db->query("SELECT * FROM meja WHERE status_aktif=1 ORDER BY nomor_meja+0,nomor_meja")->fetchAll();
    }
    public function findActiveTable(int $id): ?array {
        $s=$this->db->prepare("SELECT * FROM meja WHERE id=? AND status_aktif=1 LIMIT 1");
        $s->execute([$id]);
        return $s->fetch()?:null;
    }
    /**
     * Membuat transaksi + detail + (bila delivery) snapshot pengiriman + thread chat.
     * $extra: sumber_pesanan, alamat_id, ongkir, voucher_id, potongan_voucher,
     *         status (override), shipping (array data alamat), jarak_km.
     */
    public function createOrder(int $customerId,
    ?int $tableId,
    string $orderType,
    string $method,
    array $items,
    float $total,
    array $extra = []): int {
        if (!in_array($orderType,
        ['DINE_IN',
        'TAKEAWAY',
        'DELIVERY'],
        true)) throw new InvalidArgumentException('Jenis pesanan tidak valid.');
        $this->db->beginTransaction();
        try {
            // Nomor pesanan permanen: MJ-YYYYMMDD-NNN (antrean harian).
            $status = $extra['status'] ?? ($method === 'QRIS' ? 'MENUNGGU_PEMBAYARAN' : 'MENUNGGU_KONFIRMASI');
            $sumber = in_array($extra['sumber_pesanan'] ?? 'WEBSITE',
            ['WEBSITE',
            'KASIR'],
            true) ? $extra['sumber_pesanan'] : 'WEBSITE';
            $ongkir = max(0,
            (float)($extra['ongkir'] ?? 0));
            $potongan = max(0,
            (float)($extra['potongan_voucher'] ?? 0));
            $alamatId = !empty($extra['alamat_id']) ? (int)$extra['alamat_id'] : null;
            $voucherId = !empty($extra['voucher_id']) ? (int)$extra['voucher_id'] : null;
            $s = $this->db->prepare("INSERT INTO transaksi_penjualan(nomor_pesanan,pelanggan_id,meja_id,alamat_id,voucher_id,jenis_pesanan,sumber_pesanan,status_pesanan,metode_bayar,status_pembayaran,total_bayar,ongkir,potongan_voucher) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $s->execute(['TEMP-'.bin2hex(random_bytes(8)),
            $customerId,
            $tableId,
            $alamatId,
            $voucherId,
            $orderType,
            $sumber,
            $status,
            $method,
            'MENUNGGU',
            $total,
            $ongkir,
            $potongan]);
            $tx = (int)$this->db->lastInsertId();
            $dateStmt = $this->db->prepare("SELECT DATE(tanggal) FROM transaksi_penjualan WHERE id=? FOR UPDATE");
            $dateStmt->execute([$tx]);
            $orderDate = (string)$dateStmt->fetchColumn();
            if ($orderDate === '') throw new RuntimeException('Tanggal transaksi tidak tersedia.');
            $this->db->prepare("INSERT IGNORE INTO nomor_pesanan_harian(tanggal,nomor_terakhir) VALUES(?,0)")->execute([$orderDate]);
            $seqStmt = $this->db->prepare("SELECT nomor_terakhir FROM nomor_pesanan_harian WHERE tanggal=? FOR UPDATE");
            $seqStmt->execute([$orderDate]);
            $sequence = (int)$seqStmt->fetchColumn() + 1;
            if ($sequence > 999) throw new RuntimeException('Nomor antrean transaksi hari ini sudah mencapai batas 999.');
            $this->db->prepare("UPDATE nomor_pesanan_harian SET nomor_terakhir=? WHERE tanggal=?")->execute([$sequence,
            $orderDate]);
            $nomor = 'MJ-'.str_replace('-',
            '',
            $orderDate).'-'.str_pad((string)$sequence,
            3,
            '0',
            STR_PAD_LEFT);
            $this->db->prepare("UPDATE transaksi_penjualan SET nomor_pesanan=? WHERE id=?")->execute([$nomor,
            $tx]);
            $d = $this->db->prepare("INSERT INTO detail_transaksi(transaksi_id,menu_id,qty,harga_satuan,subtotal,topping_id,level_pedas,is_gratis) VALUES(?,?,?,?,?,?,?,?)");
            foreach ($items as $i) $d->execute([$tx,
            $i['menu_id'],
            $i['qty'],
            $i['unit_price'],
            $i['subtotal'],
            $i['topping_id'] ?: null,
            $i['level_pedas'] !== null ? $i['level_pedas'] : null,
            $i['is_gratis'] ? 1 : 0]);
            if ($orderType === 'DELIVERY' && !empty($extra['shipping'])) {
                (new ShippingModel())->create($tx,
                $alamatId,
                $extra['shipping'],
                (float)($extra['jarak_km'] ?? 0),
                $ongkir);
            }
            if ($voucherId) {
                (new VoucherModel())->record($voucherId,
                $customerId,
                $tx,
                $potongan);
            }
            // Setiap pesanan punya ruang chat (dibuat kosong; pesan pertama membuatnya aktif).
            $this->db->prepare("INSERT IGNORE INTO chat_thread(transaksi_id,pelanggan_id) VALUES(?,?)")->execute([$tx,
            $customerId]);
            $this->db->commit();
            return $tx;
        }  catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
    private function orderSelect(): string {
        return "SELECT t.*,COALESCE(NULLIF(TRIM(pel.nama),''),NULLIF(TRIM(pel.username),''),'Pelanggan') AS pelanggan,pel.nama AS nama_pelanggan,pel.username AS username_pelanggan,pel.no_hp AS hp_pelanggan,
            m.nomor_meja,v.kode voucher_kode,v.tipe voucher_tipe,
            s.id pengiriman_id,s.ongkir_status,s.biaya_ongkir,s.jarak_km,s.ekspedisi,s.link_lacak,
            s.status pengiriman_status,s.alamat_teks,s.nama_penerima,s.no_hp_penerima,s.patokan,
            s.latitude pengiriman_lat,s.longitude pengiriman_lng,s.dikirim_at,s.selesai_at
            FROM transaksi_penjualan t
            JOIN pelanggan pel ON pel.id=t.pelanggan_id
            LEFT JOIN meja m ON m.id=t.meja_id
            LEFT JOIN voucher v ON v.id=t.voucher_id
            LEFT JOIN pengiriman s ON s.transaksi_id=t.id";
    }
    public function customerOrder(int $id,
    int $customerId): ?array {
        $s = $this->db->prepare($this->orderSelect()." WHERE t.id=? AND t.pelanggan_id=? LIMIT 1");
        $s->execute([$id,
        $customerId]);
        return $s->fetch() ?: null;
    }
    public function items(int $tx): array {
        $s = $this->db->prepare("SELECT d.*,m.nama_produk,m.foto,t.nama topping FROM detail_transaksi d JOIN menu m ON m.id=d.menu_id LEFT JOIN topping t ON t.id=d.topping_id WHERE d.transaksi_id=? ORDER BY d.id");
        $s->execute([$tx]);
        return $s->fetchAll();
    }
    public function customerOrders(int $customerId): array {
        $s = $this->db->prepare($this->orderSelect()." WHERE t.pelanggan_id=? ORDER BY t.id DESC");
        $s->execute([$customerId]);
        return $s->fetchAll();
    }
    public function updatePayment(int $tx,
    string $status,
    string $ref = ''): void {
        $newOrder = $status === 'TERVERIFIKASI' ? 'MENUNGGU_KONFIRMASI' : 'MENUNGGU_PEMBAYARAN';
        $s = $this->db->prepare("UPDATE transaksi_penjualan SET status_pembayaran=?,status_pesanan=?,payment_reference=? WHERE id=?");
        $s->execute([$status,
        $newOrder,
        $ref ?: null,
        $tx]);
    }
    public function allRunning(): array {
        // Papan admin: pesanan berjalan + delivery yang menunggu konfirmasi ongkir.
        return $this->db->query($this->orderSelect()."
            WHERE (
                (t.metode_bayar='TUNAI' AND t.status_pesanan='MENUNGGU_KONFIRMASI')
                OR (t.metode_bayar='QRIS' AND t.status_pembayaran='TERVERIFIKASI' AND t.status_pesanan='MENUNGGU_KONFIRMASI')
                OR (t.metode_bayar='QRIS' AND t.status_pembayaran='MENUNGGU' AND t.payment_reference IS NOT NULL AND t.status_pesanan IN('MENUNGGU_PEMBAYARAN','MENUNGGU_KONFIRMASI'))
                OR (t.jenis_pesanan='DELIVERY' AND t.status_pesanan='MENUNGGU_KONFIRMASI')
                OR t.status_pesanan IN('DIPROSES','DIKEMAS','SEDANG_DIANTAR','SIAP_DIAMBIL')
            )
            ORDER BY t.id ASC")->fetchAll();
    }
    public function adminOrderDetail(int $tx): ?array {
        $s = $this->db->prepare($this->orderSelect()." WHERE t.id=? LIMIT 1");
        $s->execute([$tx]);
        $order = $s->fetch();
        if (!$order) return null;
        $order['items'] = $this->items($tx);
        $order['grouped_items'] = $this->groupItems($order['items']);
        $order['chat_thread'] = (new ChatModel())->threadByOrder($tx);
        return $order;
    }
    public function groupItems(array $items): array {
        $groups = [];
        foreach ($items as $item) {
            $menuId = (int)$item['menu_id'];
            if (!isset($groups[$menuId])) {
                $groups[$menuId] = ['menu_id' => $menuId,
                'nama_produk' => $item['nama_produk'],
                'foto' => $item['foto'] ?? '',
                'qty' => 0,
                'subtotal' => 0,
                'variants' => []];
            }
            $groups[$menuId]['qty'] += (int)$item['qty'];
            $groups[$menuId]['subtotal'] += (float)$item['subtotal'];
            $toppingId = $item['topping_id'] !== null ? (string)$item['topping_id'] : '0';
            $level = $item['level_pedas'] !== null ? (string)$item['level_pedas'] : '-';
            $key = $toppingId.'|'.$level;
            if (!isset($groups[$menuId]['variants'][$key])) {
                $groups[$menuId]['variants'][$key] = ['topping' => $item['topping'] ?? ($item['topping_name'] ?? null),
                'level_pedas' => $item['level_pedas'],
                'qty' => 0,
                'subtotal' => 0];
            }
            $groups[$menuId]['variants'][$key]['qty'] += (int)$item['qty'];
            $groups[$menuId]['variants'][$key]['subtotal'] += (float)$item['subtotal'];
        }
        foreach ($groups as &$g) $g['variants'] = array_values($g['variants']);
        unset($g);
        return array_values($groups);
    }
    public function adminSetStatus(int $tx,
    string $status): void {
        $q = $this->db->prepare("SELECT id,jenis_pesanan,status_pesanan,pelanggan_id,nomor_pesanan FROM transaksi_penjualan WHERE id=? LIMIT 1");
        $q->execute([$tx]);
        $row = $q->fetch();
        if (!$row) throw new RuntimeException('Pesanan tidak ditemukan.');
        if ((string)$row['status_pesanan'] === $status) return;
        $type = $row['jenis_pesanan'];
        $allowed = match ($type) {
            'TAKEAWAY' => ['DIPROSES',
            'SIAP_DIAMBIL',
            'SELESAI',
            'DIBATALKAN'],
            'DELIVERY' => ['DIPROSES',
            'DIKEMAS',
            'SEDANG_DIANTAR',
            'SELESAI',
            'DIBATALKAN'],
            default => ['DIPROSES',
            'SEDANG_DIANTAR',
            'SELESAI',
            'DIBATALKAN'],
        };
        if (!in_array($status,
        $allowed,
        true)) throw new InvalidArgumentException('Status tidak sesuai jenis pesanan.');
        $this->db->prepare("UPDATE transaksi_penjualan SET status_pesanan=? WHERE id=?")->execute([$status,
        $tx]);
        if ($type === 'DELIVERY') {
            $map = ['DIKEMAS' => 'DIKEMAS',
            'SEDANG_DIANTAR' => 'DIKIRIM',
            'SELESAI' => 'TIBA',
            'DIBATALKAN' => 'DIBATALKAN',
            'DIPROSES' => 'DIPROSES'];
            if (isset($map[$status])) (new ShippingModel())->setStatus($tx,
            $map[$status]);
        }
        $this->notifyStatusChange($row, $status);
    }

    /**
     * Kirim satu pesan chat otomatis saat status pesanan benar-benar berubah.
     * Dipanggil hanya setelah status lama berbeda dengan status baru sehingga aman dari duplikasi.
     */
    private function notifyStatusChange(array $row, string $status): void {
        $labels = [
            'DIPROSES' => 'sedang diproses oleh Warung Maem Jadoel.',
            'DIKEMAS' => 'sedang dikemas oleh Warung Maem Jadoel.',
            'SEDANG_DIANTAR' => 'sedang diantar oleh Warung Maem Jadoel.',
            'SIAP_DIAMBIL' => 'sudah siap diambil di Warung Maem Jadoel.',
            'SELESAI' => 'sudah selesai. Terima kasih sudah memesan di Warung Maem Jadoel.',
            'DIBATALKAN' => 'telah dibatalkan. Hubungi Warung Maem Jadoel bila membutuhkan bantuan.',
        ];
        $label = $labels[$status] ?? null;
        if ($label === null) return;
        $tx = (int)($row['id'] ?? 0);
        $customerId = (int)($row['pelanggan_id'] ?? 0);
        if ($tx < 1 || $customerId < 1) return;
        try {
            $this->db->prepare("INSERT IGNORE INTO chat_thread(transaksi_id,pelanggan_id) VALUES(?,?)")->execute([$tx, $customerId]);
            $thread = $this->db->prepare("SELECT id FROM chat_thread WHERE transaksi_id=? LIMIT 1");
            $thread->execute([$tx]);
            $threadId = (int)$thread->fetchColumn();
            if ($threadId < 1) return;
            $nomor = trim((string)($row['nomor_pesanan'] ?? ''));
            $nomor = $nomor !== '' ? $nomor : '#'.$tx;
            $message = 'Pesanan '.$nomor.' '.$label;
            $this->db->prepare("INSERT INTO chat_pesan(thread_id,pengirim,isi,dibaca_admin_at) VALUES(?,'ADMIN',?,NOW())")->execute([$threadId, $message]);
            $this->db->prepare("UPDATE chat_thread SET last_message_at=NOW() WHERE id=?")->execute([$threadId]);
        } catch (Throwable $e) {
            error_log('[Chat status pesanan #'.$tx.'] '.$e->getMessage());
        }
    }
    public function confirmCash(int $tx): void {
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT id,nomor_pesanan,metode_bayar,status_pembayaran,status_pesanan,jenis_pesanan,pelanggan_id FROM transaksi_penjualan WHERE id=? LIMIT 1 FOR UPDATE");
            $q->execute([$tx]);
            $row = $q->fetch();
            if (!$row || $row['status_pesanan'] !== 'MENUNGGU_KONFIRMASI') {
                throw new RuntimeException('Pesanan tidak berada pada antrean konfirmasi.');
            }
            if ($row['jenis_pesanan'] === 'DELIVERY') {
                $ship = $this->db->prepare("SELECT ongkir_status FROM pengiriman WHERE transaksi_id=? LIMIT 1");
                $ship->execute([$tx]);
                if ((string)$ship->fetchColumn() !== 'DIKONFIRMASI') throw new RuntimeException('Konfirmasi ongkir delivery terlebih dahulu.');
            }
            if ($row['metode_bayar'] === 'QRIS') {
                if ($row['status_pembayaran'] !== 'TERVERIFIKASI') throw new RuntimeException('Pembayaran QRIS belum terverifikasi.');
                $u = $this->db->prepare("UPDATE transaksi_penjualan SET status_pesanan='DIPROSES' WHERE id=? AND status_pembayaran='TERVERIFIKASI'");
                $u->execute([$tx]);
            }  elseif ($row['metode_bayar'] === 'TUNAI') {
                $ref = 'CASH-'.date('YmdHis').'-'.$tx;
                $u = $this->db->prepare("UPDATE transaksi_penjualan SET status_pembayaran='TERVERIFIKASI',payment_reference=?,status_pesanan='DIPROSES' WHERE id=? AND metode_bayar='TUNAI' AND status_pembayaran='MENUNGGU'");
                $u->execute([$ref,
                $tx]);
                if ($u->rowCount() !== 1) throw new RuntimeException('Pembayaran tunai sudah diproses atau statusnya berubah.');
            }  else {
                throw new RuntimeException('Metode pembayaran tidak dikenal.');
            }
            $this->db->commit();
            $this->notifyStatusChange($row, 'DIPROSES');
        }  catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
    public function lowStock(): array {
        return $this->db->query("SELECT s.*,m.nama_produk FROM stok_produk s JOIN menu m ON m.id=s.menu_id WHERE s.jumlah_stok<=s.stok_minimum ORDER BY s.jumlah_stok ASC")->fetchAll();
    }
    public function stockOutForOrder(int $tx): void {
        $this->db->beginTransaction();
        try {
            $lock = $this->db->prepare("SELECT stock_processed_at FROM transaksi_penjualan WHERE id=? FOR UPDATE");
            $lock->execute([$tx]);
            $processed = $lock->fetchColumn();
            if ($processed !== null && $processed !== false) {
                $this->db->commit();
                return;
            }
            $s = $this->db->prepare("SELECT menu_id,SUM(qty) qty FROM detail_transaksi WHERE transaksi_id=? GROUP BY menu_id");
            $s->execute([$tx]);
            foreach ($s->fetchAll() as $r) {
                $u = $this->db->prepare("UPDATE stok_produk SET jumlah_stok=jumlah_stok-? WHERE menu_id=? AND jumlah_stok>=?");
                $u->execute([(int)$r['qty'],
                (int)$r['menu_id'],
                (int)$r['qty']]);
                if ($u->rowCount() !== 1) throw new RuntimeException('Stok tidak mencukupi.');
                $this->db->prepare("INSERT INTO kartu_stok(stok_produk_id,jenis,jumlah,keterangan) SELECT id,'KELUAR',?,? FROM stok_produk WHERE menu_id=?")->execute([(int)$r['qty'],
                'Penjualan #'.$tx,
                (int)$r['menu_id']]);
            }
            $this->db->prepare('UPDATE transaksi_penjualan SET stock_processed_at=NOW() WHERE id=?')->execute([$tx]);
            $this->db->commit();
        }  catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
