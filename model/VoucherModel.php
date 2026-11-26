<?php
declare(strict_types=1);
/** Voucher NOMINAL / PERSEN / ONGKIR dengan kuota total & per pelanggan. */
class VoucherModel extends BaseModel {
    public function all(): array {
        return $this->db->query("SELECT v.*,(SELECT COUNT(*) FROM voucher_redemption r WHERE r.voucher_id=v.id) terpakai
            FROM voucher v ORDER BY v.status_aktif DESC, v.id DESC")->fetchAll();
    }
    public function find(int $id): ?array {
        $s = $this->db->prepare("SELECT * FROM voucher WHERE id=? LIMIT 1");
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
    public function findByCode(string $code): ?array {
        $s = $this->db->prepare("SELECT * FROM voucher WHERE kode=? LIMIT 1");
        $s->execute([strtoupper(trim($code))]);
        return $s->fetch() ?: null;
    }
    /**
     * Validasi kode dan hitung potongan.
     * @return array{ok:bool,message:string,voucher:?array,potongan:float}
     */
    public function validate(string $code,
    int $customerId,
    float $subtotal,
    float $ongkir): array {
        $fail = static fn(string $msg) => ['ok' => false,
        'message' => $msg,
        'voucher' => null,
        'potongan' => 0.0];
        $code = strtoupper(trim($code));
        if ($code === '') return $fail('Masukkan kode voucher terlebih dahulu.');
        $v = $this->findByCode($code);
        if (!$v) return $fail('Kode voucher tidak ditemukan.');
        if (!(int)$v['status_aktif']) return $fail('Voucher sedang tidak aktif.');
        $today = date('Y-m-d');
        if (!empty($v['tanggal_mulai']) && $v['tanggal_mulai'] > $today) return $fail('Voucher belum memasuki masa berlaku.');
        if (!empty($v['tanggal_selesai']) && $v['tanggal_selesai'] < $today) return $fail('Voucher sudah kedaluwarsa.');
        if ($subtotal < (float)$v['min_belanja']) return $fail('Minimal belanja Rp '.number_format((float)$v['min_belanja'],
        0,
        ',',
        '.').' untuk memakai voucher ini.');
        if ((int)$v['kuota_total'] > 0) {
            $q = $this->db->prepare("SELECT COUNT(*) FROM voucher_redemption WHERE voucher_id=?");
            $q->execute([(int)$v['id']]);
            if ((int)$q->fetchColumn() >= (int)$v['kuota_total']) return $fail('Kuota voucher sudah habis.');
        }
        $q = $this->db->prepare("SELECT COUNT(*) FROM voucher_redemption WHERE voucher_id=? AND pelanggan_id=?");
        $q->execute([(int)$v['id'],
        $customerId]);
        if ((int)$q->fetchColumn() >= max(1,
        (int)$v['kuota_per_pelanggan'])) return $fail('Kamu sudah pernah memakai voucher ini.');
        $potongan = 0.0;
        if ($v['tipe'] === 'NOMINAL') {
            $potongan = min((float)$v['nilai'],
            $subtotal);
        }  elseif ($v['tipe'] === 'PERSEN') {
            $potongan = $subtotal * ((float)$v['nilai'] / 100);
            if (!empty($v['maks_potongan']) && (float)$v['maks_potongan'] > 0) $potongan = min($potongan,
            (float)$v['maks_potongan']);
        }  elseif ($v['tipe'] === 'ONGKIR') {
            if ($ongkir <= 0) return $fail('Voucher gratis ongkir hanya berlaku untuk pesanan delivery.');
            $potongan = (float)$v['nilai'] > 0 ? min((float)$v['nilai'],
            $ongkir) : $ongkir;
        }
        $potongan = round(max(0,
        min($potongan,
        $subtotal + $ongkir)),
        2);
        if ($potongan <= 0) return $fail('Voucher tidak menghasilkan potongan untuk pesanan ini.');
        return ['ok' => true,
        'message' => 'Voucher '.$code.' berhasil dipakai.',
        'voucher' => $v,
        'potongan' => $potongan];
    }
    public function record(int $voucherId,
    int $customerId,
    int $transactionId,
    float $potongan): void {
        $this->db->prepare("INSERT IGNORE INTO voucher_redemption(voucher_id,pelanggan_id,transaksi_id,potongan) VALUES(?,?,?,?)")
        ->execute([$voucherId,
        $customerId,
        $transactionId,
        $potongan]);
    }
    public function save(array $d,
    ?int $id = null): int {
        $kode = strtoupper(trim((string)($d['kode'] ?? '')));
        if ($kode === '') throw new RuntimeException('Kode voucher wajib diisi.');
        $tipe = in_array($d['tipe'] ?? '',
        ['NOMINAL',
        'PERSEN',
        'ONGKIR'],
        true) ? $d['tipe'] : 'NOMINAL';
        $vals = [
        $kode,
        trim((string)($d['deskripsi'] ?? '')) ?: null,
        $tipe,
        max(0,
        (float)($d['nilai'] ?? 0)),
        max(0,
        (float)($d['min_belanja'] ?? 0)),
        ($d['maks_potongan'] ?? '') !== '' ? max(0,
        (float)$d['maks_potongan']) : null,
        max(0,
        (int)($d['kuota_total'] ?? 0)),
        max(1,
        (int)($d['kuota_per_pelanggan'] ?? 1)),
        ($d['tanggal_mulai'] ?? '') ?: null,
        ($d['tanggal_selesai'] ?? '') ?: null,
        !empty($d['status_aktif']) ? 1 : 0,
        ];
        if ($id) {
            $this->db->prepare("UPDATE voucher SET kode=?,deskripsi=?,tipe=?,nilai=?,min_belanja=?,maks_potongan=?,kuota_total=?,kuota_per_pelanggan=?,tanggal_mulai=?,tanggal_selesai=?,status_aktif=? WHERE id=?")
            ->execute(array_merge($vals,
            [$id]));
            return $id;
        }
        $this->db->prepare("INSERT INTO voucher(kode,deskripsi,tipe,nilai,min_belanja,maks_potongan,kuota_total,kuota_per_pelanggan,tanggal_mulai,tanggal_selesai,status_aktif) VALUES(?,?,?,?,?,?,?,?,?,?,?)")
        ->execute($vals);
        return (int)$this->db->lastInsertId();
    }
    public function delete(int $id): void {
        $this->db->prepare("DELETE FROM voucher WHERE id=?")->execute([$id]);
    }
    public function activePromo(): ?array {
        $s = $this->db->query("SELECT * FROM voucher WHERE status_aktif=1 AND (tanggal_mulai IS NULL OR tanggal_mulai<=CURDATE()) AND (tanggal_selesai IS NULL OR tanggal_selesai>=CURDATE()) ORDER BY id DESC LIMIT 1");
        return $s->fetch() ?: null;
    }
    /** Daftar voucher yang sedang berlaku untuk etalase landing page. */
    public function activeList(int $limit = 6): array {
        $limit = max(1, min(12, $limit));
        return $this->db->query("SELECT * FROM voucher WHERE status_aktif=1 AND (tanggal_mulai IS NULL OR tanggal_mulai<=CURDATE()) AND (tanggal_selesai IS NULL OR tanggal_selesai>=CURDATE()) ORDER BY id DESC LIMIT {$limit}")->fetchAll();
    }
}
