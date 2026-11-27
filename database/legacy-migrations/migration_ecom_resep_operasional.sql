-- =====================================================================
-- FASE 7 — Resep produk + Operasional (pengeluaran)
--
-- Resep: 1 produk punya banyak bahan baku + takaran per porsi.
-- Aturan propagasi (sesuai permintaan owner):
--   ketika stok PRODUK ditambah N, stok tiap BAHAN BAKU bertambah
--   N x takaran resep. Referensi selalu produk, bukan bahan.
--
-- Pengeluaran: TIDAK membuat tabel baru. Tabel `keuangan` yang sudah
-- dipakai laporan laba/rugi ditambah kolom `sumber` + `bukti` supaya
-- pengeluaran operasional (gas, listrik, dll) langsung masuk laporan.
-- =====================================================================
USE maem_jadoel;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS resep_produk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL,
  bahan_baku_id BIGINT UNSIGNED NOT NULL,
  jumlah DECIMAL(12,3) NOT NULL DEFAULT 0,
  keterangan VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_resep (menu_id, bahan_baku_id),
  INDEX idx_resep_menu (menu_id),
  CONSTRAINT fk_resep_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE,
  CONSTRAINT fk_resep_bahan FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE keuangan
  ADD COLUMN IF NOT EXISTS sumber ENUM('MANUAL','OPERASIONAL') NOT NULL DEFAULT 'MANUAL' AFTER jenis,
  ADD COLUMN IF NOT EXISTS bukti VARCHAR(255) NULL AFTER keterangan,
  ADD COLUMN IF NOT EXISTS created_by VARCHAR(60) NULL AFTER bukti;

ALTER TABLE keuangan
  ADD INDEX IF NOT EXISTS idx_fin_jenis_sumber (jenis, sumber, tanggal);

SET FOREIGN_KEY_CHECKS=1;
