-- =====================================================================
-- FASE 5 — Katalog: galeri foto, slug, berat
-- =====================================================================
USE maem_jadoel;
SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE menu
  ADD COLUMN IF NOT EXISTS slug VARCHAR(180) NULL AFTER nama_produk,
  ADD COLUMN IF NOT EXISTS berat_gram INT UNSIGNED NOT NULL DEFAULT 0 AFTER deskripsi;

CREATE TABLE IF NOT EXISTS produk_foto (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL,
  file VARCHAR(255) NOT NULL,
  urutan TINYINT UNSIGNED NOT NULL DEFAULT 0,
  is_utama TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_produk_foto (menu_id, urutan),
  CONSTRAINT fk_produk_foto_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;
