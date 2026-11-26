-- =====================================================================
-- FASE 3 — Keranjang permanen mengikuti akun
-- =====================================================================
USE maem_jadoel;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS keranjang (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_keranjang_pelanggan (pelanggan_id),
  CONSTRAINT fk_keranjang_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS keranjang_item (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  keranjang_id BIGINT UNSIGNED NOT NULL,
  menu_id BIGINT UNSIGNED NOT NULL,
  topping_id BIGINT UNSIGNED NULL,
  level_pedas TINYINT UNSIGNED NULL,
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_keranjang_item (keranjang_id, menu_id),
  CONSTRAINT fk_keranjang_item_cart FOREIGN KEY (keranjang_id) REFERENCES keranjang(id) ON DELETE CASCADE,
  CONSTRAINT fk_keranjang_item_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE,
  CONSTRAINT fk_keranjang_item_topping FOREIGN KEY (topping_id) REFERENCES topping(id) ON DELETE SET NULL,
  CONSTRAINT chk_keranjang_level CHECK (level_pedas IS NULL OR level_pedas BETWEEN 0 AND 5)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;
