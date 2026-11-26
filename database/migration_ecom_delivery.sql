-- =====================================================================
-- FASE 1 — Delivery & Ongkir
-- ALTER transaksi_penjualan + tabel pengiriman.
-- CHECK lama diganti agar DELIVERY (tanpa meja) sah.
-- =====================================================================
USE maem_jadoel;
SET FOREIGN_KEY_CHECKS=0;

-- 1) Jenis pesanan + status pengiriman sederhana
ALTER TABLE transaksi_penjualan
  MODIFY COLUMN jenis_pesanan ENUM('DINE_IN','TAKEAWAY','DELIVERY') NOT NULL DEFAULT 'DINE_IN',
  MODIFY COLUMN status_pesanan ENUM('MENUNGGU_PEMBAYARAN','MENUNGGU_KONFIRMASI','DIPROSES','DIKEMAS','SEDANG_DIANTAR','SIAP_DIAMBIL','SELESAI','DIBATALKAN') NOT NULL DEFAULT 'MENUNGGU_PEMBAYARAN';

-- 2) Kolom kanal & ongkir (voucher ditambahkan pada fase voucher)
ALTER TABLE transaksi_penjualan
  ADD COLUMN IF NOT EXISTS sumber_pesanan ENUM('WEBSITE','KASIR') NOT NULL DEFAULT 'WEBSITE' AFTER jenis_pesanan,
  ADD COLUMN IF NOT EXISTS alamat_id BIGINT UNSIGNED NULL AFTER meja_id,
  ADD COLUMN IF NOT EXISTS ongkir DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER total_bayar;

-- 3) CHECK lama (nama bawaan MariaDB) diganti; keharusan alamat dijaga aplikasi.
ALTER TABLE transaksi_penjualan DROP CONSTRAINT CONSTRAINT_1;
ALTER TABLE transaksi_penjualan
  ADD CONSTRAINT chk_transaksi_jenis CHECK (
    (jenis_pesanan='DINE_IN' AND meja_id IS NOT NULL)
    OR jenis_pesanan='TAKEAWAY'
    OR jenis_pesanan='DELIVERY'
  );

-- 4) FK alamat (nullable, alamat nonaktif tetap tersimpan)
ALTER TABLE transaksi_penjualan
  ADD CONSTRAINT fk_transaksi_alamat FOREIGN KEY (alamat_id) REFERENCES alamat_pelanggan(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS pengiriman (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transaksi_id BIGINT UNSIGNED NOT NULL,
  alamat_id BIGINT UNSIGNED NULL,
  nama_penerima VARCHAR(120) NOT NULL,
  no_hp_penerima VARCHAR(30) NOT NULL,
  alamat_teks VARCHAR(500) NOT NULL,
  patokan VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  jarak_km DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  biaya_ongkir DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  ongkir_status ENUM('ESTIMASI','DIKONFIRMASI') NOT NULL DEFAULT 'ESTIMASI',
  ekspedisi VARCHAR(60) NULL,
  link_lacak TEXT NULL,
  status ENUM('DIPROSES','DIKEMAS','DIKIRIM','TIBA','DIBATALKAN') NOT NULL DEFAULT 'DIPROSES',
  catatan VARCHAR(255) NULL,
  dikirim_at DATETIME NULL,
  selesai_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pengiriman_transaksi (transaksi_id),
  INDEX idx_pengiriman_status (status),
  CONSTRAINT fk_pengiriman_transaksi FOREIGN KEY (transaksi_id) REFERENCES transaksi_penjualan(id) ON DELETE CASCADE,
  CONSTRAINT fk_pengiriman_alamat FOREIGN KEY (alamat_id) REFERENCES alamat_pelanggan(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;
