<?php
declare(strict_types=1);

/** Chat per pesanan — 1 thread per transaksi, polling ringan. */
class ChatModel extends BaseModel {
    public function threadForOrder(int $transactionId, int $customerId): array {
        $this->db->prepare("INSERT IGNORE INTO chat_thread(transaksi_id,pelanggan_id) VALUES(?,?)")->execute([$transactionId, $customerId]);
        $s = $this->db->prepare("SELECT * FROM chat_thread WHERE transaksi_id=? LIMIT 1");
        $s->execute([$transactionId]);
        return $s->fetch();
    }

    public function thread(int $threadId): ?array {
        $s = $this->db->prepare("SELECT ct.*,t.nomor_pesanan,t.status_pesanan,t.jenis_pesanan,t.total_bayar,COALESCE(NULLIF(TRIM(p.nama),''),NULLIF(TRIM(p.username),''),'Pelanggan') pelanggan,
                (SELECT m.foto FROM detail_transaksi d JOIN menu m ON m.id=d.menu_id WHERE d.transaksi_id=ct.transaksi_id ORDER BY d.id LIMIT 1) foto,
                (SELECT GROUP_CONCAT(CONCAT(m.nama_produk,' × ',d.qty) SEPARATOR ', ') FROM detail_transaksi d JOIN menu m ON m.id=d.menu_id WHERE d.transaksi_id=ct.transaksi_id) ringkasan
            FROM chat_thread ct JOIN transaksi_penjualan t ON t.id=ct.transaksi_id JOIN pelanggan p ON p.id=ct.pelanggan_id
            WHERE ct.id=? LIMIT 1");
        $s->execute([$threadId]);
        return $s->fetch() ?: null;
    }

    public function threadByOrder(int $transactionId): ?array {
        $s = $this->db->prepare("SELECT * FROM chat_thread WHERE transaksi_id=? LIMIT 1");
        $s->execute([$transactionId]);
        return $s->fetch() ?: null;
    }

    public function messages(int $threadId, int $afterId = 0): array {
        $s = $this->db->prepare("SELECT * FROM chat_pesan WHERE thread_id=? AND id>? ORDER BY id ASC");
        $s->execute([$threadId, $afterId]);
        return $s->fetchAll();
    }

    public function send(int $threadId, string $sender, string $message): int {
        $message = trim($message);
        if ($message === '') throw new RuntimeException('Pesan tidak boleh kosong.');
        if (mb_strlen($message) > 2000) $message = mb_substr($message, 0, 2000);
        $sender = $sender === 'ADMIN' ? 'ADMIN' : 'PELANGGAN';
        $readCol = $sender === 'ADMIN' ? 'dibaca_admin_at' : 'dibaca_pelanggan_at';
        $this->db->prepare("INSERT INTO chat_pesan(thread_id,pengirim,isi,{$readCol}) VALUES(?,?,?,NOW())")->execute([$threadId, $sender, $message]);
        $id = (int)$this->db->lastInsertId();
        $this->db->prepare("UPDATE chat_thread SET last_message_at=NOW() WHERE id=?")->execute([$threadId]);
        return $id;
    }

    public function markRead(int $threadId, string $reader): void {
        $col = $reader === 'ADMIN' ? 'dibaca_admin_at' : 'dibaca_pelanggan_at';
        $from = $reader === 'ADMIN' ? 'PELANGGAN' : 'ADMIN';
        $this->db->prepare("UPDATE chat_pesan SET {$col}=NOW() WHERE thread_id=? AND pengirim=? AND {$col} IS NULL")->execute([$threadId, $from]);
    }

    public function threadsForCustomer(int $customerId): array {
        $s = $this->db->prepare("SELECT ct.*,t.nomor_pesanan,t.status_pesanan,t.total_bayar,
                (SELECT isi FROM chat_pesan WHERE thread_id=ct.id ORDER BY id DESC LIMIT 1) last_message,
                (SELECT created_at FROM chat_pesan WHERE thread_id=ct.id ORDER BY id DESC LIMIT 1) last_at,
                (SELECT m.foto FROM detail_transaksi d JOIN menu m ON m.id=d.menu_id WHERE d.transaksi_id=ct.transaksi_id ORDER BY d.id LIMIT 1) foto,
                (SELECT COUNT(*) FROM chat_pesan WHERE thread_id=ct.id AND pengirim='ADMIN' AND dibaca_pelanggan_at IS NULL) unread
            FROM chat_thread ct JOIN transaksi_penjualan t ON t.id=ct.transaksi_id
            WHERE ct.pelanggan_id=? ORDER BY COALESCE(ct.last_message_at,ct.created_at) DESC, ct.id DESC");
        $s->execute([$customerId]);
        return $s->fetchAll();
    }

    public function allThreads(): array {
        return $this->db->query("SELECT ct.*,t.nomor_pesanan,t.status_pesanan,t.total_bayar,COALESCE(NULLIF(TRIM(p.nama),''),NULLIF(TRIM(p.username),''),'Pelanggan') pelanggan,
                (SELECT isi FROM chat_pesan WHERE thread_id=ct.id ORDER BY id DESC LIMIT 1) last_message,
                (SELECT created_at FROM chat_pesan WHERE thread_id=ct.id ORDER BY id DESC LIMIT 1) last_at,
                (SELECT m.foto FROM detail_transaksi d JOIN menu m ON m.id=d.menu_id WHERE d.transaksi_id=ct.transaksi_id ORDER BY d.id LIMIT 1) foto,
                (SELECT COUNT(*) FROM chat_pesan WHERE thread_id=ct.id AND pengirim='PELANGGAN' AND dibaca_admin_at IS NULL) unread
            FROM chat_thread ct JOIN transaksi_penjualan t ON t.id=ct.transaksi_id JOIN pelanggan p ON p.id=ct.pelanggan_id
            ORDER BY COALESCE(ct.last_message_at,ct.created_at) DESC, ct.id DESC")->fetchAll();
    }

    public function unreadForCustomer(int $customerId): int {
        $s = $this->db->prepare("SELECT COUNT(*) FROM chat_pesan cp JOIN chat_thread ct ON ct.id=cp.thread_id WHERE ct.pelanggan_id=? AND cp.pengirim='ADMIN' AND cp.dibaca_pelanggan_at IS NULL");
        $s->execute([$customerId]);
        return (int)$s->fetchColumn();
    }

    public function unreadForAdmin(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM chat_pesan WHERE pengirim='PELANGGAN' AND dibaca_admin_at IS NULL")->fetchColumn();
    }
}
