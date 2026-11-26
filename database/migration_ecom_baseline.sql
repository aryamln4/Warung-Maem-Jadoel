-- =====================================================================
-- FASE 0 — Baseline E-Commerce
-- Tabel baru murni: buku alamat pelanggan + pengaturan toko/ongkir.
-- Aman dijalankan berulang (IF NOT EXISTS). Tidak mengubah tabel POS.
-- =====================================================================
USE maem_jadoel;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS alamat_pelanggan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  label VARCHAR(50) NOT NULL DEFAULT 'Rumah',
  nama_penerima VARCHAR(120) NOT NULL,
  no_hp VARCHAR(30) NOT NULL,
  alamat VARCHAR(255) NOT NULL,
  patokan VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  is_utama TINYINT(1) NOT NULL DEFAULT 0,
  status_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_alamat_pelanggan (pelanggan_id, status_aktif, is_utama),
  CONSTRAINT fk_alamat_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pengaturan_toko (
  id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
  nama_toko VARCHAR(120) NOT NULL DEFAULT 'Warung Maem Jadoel',
  alamat VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NOT NULL DEFAULT -6.4024840,
  longitude DECIMAL(10,7) NOT NULL DEFAULT 106.7942430,
  ongkir_dasar DECIMAL(12,2) NOT NULL DEFAULT 10000.00,
  jarak_dasar_km DECIMAL(5,2) NOT NULL DEFAULT 1.00,
  tarif_per_500m DECIMAL(12,2) NOT NULL DEFAULT 2000.00,
  batas_jarak_km DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  jam_buka VARCHAR(60) NULL DEFAULT '10.00 - 22.00',
  catatan VARCHAR(255) NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO pengaturan_toko (id, nama_toko, alamat, latitude, longitude, ongkir_dasar, jarak_dasar_km, tarif_per_500m, batas_jarak_km, jam_buka)
SELECT 1, 'Warung Maem Jadoel', 'Depok, Jawa Barat', -6.4024840, 106.7942430, 10000.00, 1.00, 2000.00, 10.00, '10.00 - 22.00'
WHERE NOT EXISTS (SELECT 1 FROM pengaturan_toko WHERE id = 1);

SET FOREIGN_KEY_CHECKS=1;
