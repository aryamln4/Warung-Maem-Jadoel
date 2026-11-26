<?php
declare(strict_types=1);

/**
 * Ongkir berbasis jarak toko -> titik pelanggan.
 * Rumus: ongkir_dasar + ceil((jarak - jarak_dasar)/0.5) * tarif_per_500m.
 * Tarif & batas jarak disimpan di pengaturan_toko agar bisa diubah admin.
 */
class ShippingModel extends BaseModel {
    public function settings(): array {
        $row = $this->db->query("SELECT * FROM pengaturan_toko WHERE id=1 LIMIT 1")->fetch();
        if (!$row) {
            return [
                'id' => 1, 'nama_toko' => 'Warung Maem Jadoel', 'alamat' => '',
                'latitude' => -6.4024840, 'longitude' => 106.7942430,
                'ongkir_dasar' => 10000, 'jarak_dasar_km' => 1, 'tarif_per_500m' => 2000,
                'batas_jarak_km' => 10, 'jam_buka' => '10.00 - 22.00', 'catatan' => null,
            ];
        }
        return $row;
    }

    public function saveSettings(array $d): void {
        $this->db->prepare("INSERT INTO pengaturan_toko(id,nama_toko,alamat,latitude,longitude,ongkir_dasar,jarak_dasar_km,tarif_per_500m,batas_jarak_km,jam_buka,catatan)
            VALUES(1,?,?,?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE nama_toko=VALUES(nama_toko),alamat=VALUES(alamat),latitude=VALUES(latitude),longitude=VALUES(longitude),
            ongkir_dasar=VALUES(ongkir_dasar),jarak_dasar_km=VALUES(jarak_dasar_km),tarif_per_500m=VALUES(tarif_per_500m),
            batas_jarak_km=VALUES(batas_jarak_km),jam_buka=VALUES(jam_buka),catatan=VALUES(catatan)")
            ->execute([
                trim((string)($d['nama_toko'] ?? 'Warung Maem Jadoel')),
                trim((string)($d['alamat'] ?? '')) ?: null,
                (float)($d['latitude'] ?? 0), (float)($d['longitude'] ?? 0),
                max(0, (float)($d['ongkir_dasar'] ?? 0)),
                max(0, (float)($d['jarak_dasar_km'] ?? 1)),
                max(0, (float)($d['tarif_per_500m'] ?? 0)),
                max(0, (float)($d['batas_jarak_km'] ?? 10)),
                trim((string)($d['jam_buka'] ?? '')) ?: null,
                trim((string)($d['catatan'] ?? '')) ?: null,
            ]);
    }

    public function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** Hitung estimasi ongkir dari koordinat. Return ok/message/jarak/ongkir. */
    public function quote(?float $lat, ?float $lng): array {
        $s = $this->settings();
        if ($lat === null || $lng === null || ($lat == 0.0 && $lng == 0.0)) {
            return ['ok' => false, 'message' => 'Titik lokasi belum ditentukan. Gunakan tombol “Gunakan lokasi saya”.', 'jarak_km' => 0.0, 'ongkir' => 0.0];
        }
        $jarak = $this->haversineKm((float)$s['latitude'], (float)$s['longitude'], $lat, $lng);
        if ($jarak > (float)$s['batas_jarak_km']) {
            return ['ok' => false, 'message' => 'Alamat di luar jangkauan pengiriman (maks. '.rtrim(rtrim(number_format((float)$s['batas_jarak_km'], 2, '.', ''), '0'), '.').' km).', 'jarak_km' => $jarak, 'ongkir' => 0.0];
        }
        $kelipatan = max(0, (int)ceil(($jarak - (float)$s['jarak_dasar_km']) / 0.5));
        $ongkir = (float)$s['ongkir_dasar'] + $kelipatan * (float)$s['tarif_per_500m'];
        return ['ok' => true, 'message' => '', 'jarak_km' => $jarak, 'ongkir' => $ongkir];
    }

    public function create(int $txId, ?int $addressId, array $data, float $jarakKm, float $ongkir): void {
        $this->db->prepare("INSERT INTO pengiriman(transaksi_id,alamat_id,nama_penerima,no_hp_penerima,alamat_teks,patokan,latitude,longitude,jarak_km,biaya_ongkir,ongkir_status,status)
            VALUES(?,?,?,?,?,?,?,?,?,?,'ESTIMASI','DIPROSES')
            ON DUPLICATE KEY UPDATE alamat_id=VALUES(alamat_id),nama_penerima=VALUES(nama_penerima),no_hp_penerima=VALUES(no_hp_penerima),alamat_teks=VALUES(alamat_teks),patokan=VALUES(patokan),latitude=VALUES(latitude),longitude=VALUES(longitude),jarak_km=VALUES(jarak_km),biaya_ongkir=VALUES(biaya_ongkir)")
            ->execute([
                $txId, $addressId,
                trim((string)($data['nama_penerima'] ?? '')),
                trim((string)($data['no_hp'] ?? '')),
                trim((string)($data['alamat'] ?? '')),
                trim((string)($data['patokan'] ?? '')) ?: null,
                isset($data['latitude']) && $data['latitude'] !== '' ? (float)$data['latitude'] : null,
                isset($data['longitude']) && $data['longitude'] !== '' ? (float)$data['longitude'] : null,
                $jarakKm, $ongkir,
            ]);
    }

    public function forOrder(int $txId): ?array {
        $s = $this->db->prepare("SELECT * FROM pengiriman WHERE transaksi_id=? LIMIT 1");
        $s->execute([$txId]);
        return $s->fetch() ?: null;
    }

    /** Konfirmasi ongkir final oleh owner. Total transaksi dihitung ulang dari subtotal. */
    public function confirmOngkir(int $txId, float $newOngkir): void {
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT t.id,t.total_bayar,t.potongan_voucher,t.payment_reference,t.status_pembayaran,t.jenis_pesanan,p.biaya_ongkir,p.ongkir_status
                FROM transaksi_penjualan t JOIN pengiriman p ON p.transaksi_id=t.id WHERE t.id=? FOR UPDATE");
            $q->execute([$txId]);
            $row = $q->fetch();
            if (!$row) throw new RuntimeException('Data pengiriman tidak ditemukan.');
            if ($row['jenis_pesanan'] !== 'DELIVERY') throw new RuntimeException('Pesanan ini bukan delivery.');
            if ($row['status_pembayaran'] === 'TERVERIFIKASI') throw new RuntimeException('Pembayaran sudah terverifikasi; ongkir tidak dapat diubah.');
            $newOngkir = max(0, $newOngkir);
            if (!empty($row['payment_reference']) && abs($newOngkir - (float)$row['biaya_ongkir']) > 0.001) {
                throw new RuntimeException('Tagihan QRIS sudah dibuat dengan nominal lama. Batalkan pesanan lalu buat ulang bila ongkir harus berubah.');
            }
            $subtotal = $this->db->prepare("SELECT COALESCE(SUM(subtotal),0) FROM detail_transaksi WHERE transaksi_id=?");
            $subtotal->execute([$txId]);
            $sub = (float)$subtotal->fetchColumn();
            $total = max(0, $sub + $newOngkir - (float)$row['potongan_voucher']);
            $this->db->prepare("UPDATE pengiriman SET biaya_ongkir=?,ongkir_status='DIKONFIRMASI' WHERE transaksi_id=?")->execute([$newOngkir, $txId]);
            $this->db->prepare("UPDATE transaksi_penjualan SET ongkir=?,total_bayar=? WHERE id=?")->execute([$newOngkir, $total, $txId]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function setCourier(int $txId, string $ekspedisi, string $link): void {
        $this->db->prepare("UPDATE pengiriman SET ekspedisi=?,link_lacak=?,status='DIKIRIM',dikirim_at=COALESCE(dikirim_at,NOW()) WHERE transaksi_id=?")
            ->execute([trim($ekspedisi) ?: null, trim($link) ?: null, $txId]);
    }

    public function setStatus(int $txId, string $status): void {
        if (!in_array($status, ['DIPROSES', 'DIKEMAS', 'DIKIRIM', 'TIBA', 'DIBATALKAN'], true)) return;
        $selesai = $status === 'TIBA' ? ',selesai_at=COALESCE(selesai_at,NOW())' : '';
        $this->db->prepare("UPDATE pengiriman SET status=?{$selesai} WHERE transaksi_id=?")->execute([$status, $txId]);
    }

    public function pending(): array {
        return $this->db->query("SELECT p.*,t.nomor_pesanan,t.total_bayar,t.status_pesanan,COALESCE(NULLIF(TRIM(pel.nama),''),NULLIF(TRIM(pel.username),''),'Pelanggan') pelanggan
            FROM pengiriman p JOIN transaksi_penjualan t ON t.id=p.transaksi_id JOIN pelanggan pel ON pel.id=t.pelanggan_id
            WHERE p.ongkir_status='ESTIMASI' AND t.status_pesanan NOT IN('DIBATALKAN','SELESAI')
            ORDER BY p.id ASC")->fetchAll();
    }

    public function activeDeliveries(): array {
        return $this->db->query("SELECT p.*,t.nomor_pesanan,t.total_bayar,t.status_pesanan,COALESCE(NULLIF(TRIM(pel.nama),''),NULLIF(TRIM(pel.username),''),'Pelanggan') pelanggan
            FROM pengiriman p JOIN transaksi_penjualan t ON t.id=p.transaksi_id JOIN pelanggan pel ON pel.id=t.pelanggan_id
            WHERE t.status_pesanan NOT IN('SELESAI','DIBATALKAN')
            ORDER BY p.id ASC")->fetchAll();
    }
}
