-- =====================================================================
-- FASE 4 — Voucher (NOMINAL / PERSEN / ONGKIR)
-- =====================================================================
USE maem_jadoel;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS voucher (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(40) NOT NULL,
  deskripsi VARCHAR(255) NULL,
  tipe ENUM('PERSEN','NOMINAL','ONGKIR') NOT NULL,
  nilai DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  min_belanja DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  maks_potongan DECIMAL(12,2) NULL,
  kuota_total INT UNSIGNED NOT NULL DEFAULT 0,
  kuota_per_pelanggan INT UNSIGNED NOT NULL DEFAULT 1,
  tanggal_mulai DATE NULL,
  tanggal_selesai DATE NULL,
  status_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_voucher_kode (kode)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS voucher_redemption (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  voucher_id BIGINT UNSIGNED NOT NULL,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  transaksi_id BIGINT UNSIGNED NOT NULL,
  potongan DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  tanggal TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_voucher_transaksi (transaksi_id, voucher_id),
  INDEX idx_voucher_pelanggan (voucher_id, pelanggan_id),
  CONSTRAINT fk_redemption_voucher FOREIGN KEY (voucher_id) REFERENCES voucher(id) ON DELETE CASCADE,
  CONSTRAINT fk_redemption_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE,
  CONSTRAINT fk_redemption_transaksi FOREIGN KEY (transaksi_id) REFERENCES transaksi_penjualan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE transaksi_penjualan
  ADD COLUMN IF NOT EXISTS voucher_id BIGINT UNSIGNED NULL AFTER alamat_id,
  ADD COLUMN IF NOT EXISTS potongan_voucher DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER ongkir;

ALTER TABLE transaksi_penjualan
  ADD CONSTRAINT fk_transaksi_voucher FOREIGN KEY (voucher_id) REFERENCES voucher(id) ON DELETE SET NULL;

-- Contoh voucher pertama sesuai banner desain.
INSERT INTO voucher (kode, deskripsi, tipe, nilai, min_belanja, maks_potongan, kuota_total, kuota_per_pelanggan, tanggal_mulai, tanggal_selesai, status_aktif)
SELECT 'MAEM10', 'Potongan Rp 10.000 untuk pesanan pertamamu.', 'NOMINAL', 10000.00, 50000.00, NULL, 100, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1
WHERE NOT EXISTS (SELECT 1 FROM voucher WHERE kode = 'MAEM10');

SET FOREIGN_KEY_CHECKS=1;
