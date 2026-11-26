-- =====================================================================
-- FASE 6 — Ulasan per produk + wishlist
-- ulasan_pelanggan lama tetap sebagai riwayat POS.
-- =====================================================================
USE maem_jadoel;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS ulasan_produk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transaksi_id BIGINT UNSIGNED NOT NULL,
  detail_transaksi_id BIGINT UNSIGNED NOT NULL,
  menu_id BIGINT UNSIGNED NOT NULL,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  komentar TEXT NULL,
  foto VARCHAR(255) NULL,
  status_ulasan ENUM('MENUNGGU','DITAMPILKAN','DISEMBUNYIKAN') NOT NULL DEFAULT 'DITAMPILKAN',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ulasan_produk_detail (detail_transaksi_id),
  INDEX idx_ulasan_produk_menu (menu_id, status_ulasan),
  INDEX idx_ulasan_produk_pelanggan (pelanggan_id),
  CONSTRAINT fk_ulasan_produk_transaksi FOREIGN KEY (transaksi_id) REFERENCES transaksi_penjualan(id) ON DELETE CASCADE,
  CONSTRAINT fk_ulasan_produk_detail FOREIGN KEY (detail_transaksi_id) REFERENCES detail_transaksi(id) ON DELETE CASCADE,
  CONSTRAINT fk_ulasan_produk_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE,
  CONSTRAINT fk_ulasan_produk_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE,
  CONSTRAINT chk_ulasan_produk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wishlist (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  menu_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_wishlist (pelanggan_id, menu_id),
  CONSTRAINT fk_wishlist_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;
