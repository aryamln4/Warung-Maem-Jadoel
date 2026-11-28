-- Schema Warung Maem Jadoel untuk instalasi bersih.
-- Pilih database tujuan di phpMyAdmin terlebih dahulu, lalu import file ini.
SET NAMES utf8mb4;

CREATE TABLE bahan_baku (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_produk VARCHAR(40) NOT NULL UNIQUE,
  nama_bahan VARCHAR(150) NOT NULL,
  tanggal_kadaluarsa DATE NOT NULL,
  stok DECIMAL(12,3) NOT NULL DEFAULT 0,
  satuan ENUM('gram','kilo','pcs') NOT NULL,
  harga DECIMAL(14,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_bahan_fefo (tanggal_kadaluarsa, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pembelian_bahan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bahan_baku_id BIGINT UNSIGNED NOT NULL,
  keuangan_id BIGINT UNSIGNED NULL,
  jumlah DECIMAL(12,3) NOT NULL,
  harga_satuan DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  tanggal DATE NOT NULL,
  status ENUM('AKTIF','DIBATALKAN') NOT NULL DEFAULT 'AKTIF',
  keterangan VARCHAR(255) NULL,
  created_by VARCHAR(60) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pembelian_bahan (bahan_baku_id,tanggal),
  INDEX idx_pembelian_keuangan (keuangan_id),
  CONSTRAINT fk_pembelian_bahan FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kartu_stok_bahan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bahan_baku_id BIGINT UNSIGNED NOT NULL,
  jenis ENUM('MASUK','KELUAR','PENYESUAIAN') NOT NULL,
  jumlah DECIMAL(12,3) NOT NULL,
  keterangan VARCHAR(255) NULL,
  tanggal TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_kartu_bahan_tanggal (bahan_baku_id, tanggal),
  CONSTRAINT fk_kartu_bahan FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('ADMIN') NOT NULL DEFAULT 'ADMIN',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pelanggan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  nama VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  no_hp VARCHAR(30) NULL,
  password VARCHAR(255) NOT NULL,
  total_produk_kumulatif INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('AKTIF','DIBLOKIR') NOT NULL DEFAULT 'AKTIF',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  expired_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kategori_menu (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menu (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kategori_id BIGINT UNSIGNED NOT NULL,
  nama_produk VARCHAR(150) NOT NULL,
  deskripsi TEXT NULL,
  satuan VARCHAR(30) NULL,
  harga DECIMAL(12,2) NOT NULL,
  punya_topping TINYINT(1) NOT NULL DEFAULT 0,
  punya_level_pedas TINYINT(1) NOT NULL DEFAULT 0,
  foto VARCHAR(255) NULL,
  status_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kategori_id) REFERENCES kategori_menu(id),
  INDEX idx_menu_active (status_aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE topping (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL UNIQUE,
  harga_tambahan DECIMAL(12,2) NOT NULL DEFAULT 0,
  status_aktif TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menu_topping (
  menu_id BIGINT UNSIGNED NOT NULL,
  topping_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (menu_id, topping_id),
  FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE,
  FOREIGN KEY (topping_id) REFERENCES topping(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE meja (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_meja VARCHAR(30) NOT NULL UNIQUE,
  status_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stok_produk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL UNIQUE,
  jumlah_stok INT NOT NULL DEFAULT 0,
  stok_minimum INT NOT NULL DEFAULT 5,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kartu_stok (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  stok_produk_id BIGINT UNSIGNED NOT NULL,
  jenis ENUM('MASUK','KELUAR','PENYESUAIAN') NOT NULL,
  jumlah INT NOT NULL,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  keterangan VARCHAR(255) NULL,
  FOREIGN KEY (stok_produk_id) REFERENCES stok_produk(id) ON DELETE CASCADE,
  INDEX idx_kartu_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE nomor_pesanan_harian (
  tanggal DATE PRIMARY KEY,
  nomor_terakhir INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE alamat_pelanggan (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pengaturan_toko (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE voucher (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transaksi_penjualan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_pesanan VARCHAR(40) NOT NULL UNIQUE,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  meja_id BIGINT UNSIGNED NULL,
  jenis_pesanan ENUM('DINE_IN','TAKEAWAY','DELIVERY') NOT NULL DEFAULT 'DINE_IN',
  sumber_pesanan ENUM('WEBSITE','KASIR') NOT NULL DEFAULT 'WEBSITE',
  alamat_id BIGINT UNSIGNED NULL,
  voucher_id BIGINT UNSIGNED NULL,
  status_pesanan ENUM('MENUNGGU_PEMBAYARAN','MENUNGGU_KONFIRMASI','DIPROSES','DIKEMAS','SEDANG_DIANTAR','SIAP_DIAMBIL','SELESAI','DIBATALKAN') NOT NULL DEFAULT 'MENUNGGU_PEMBAYARAN',
  metode_bayar ENUM('QRIS','TUNAI') NOT NULL,
  status_pembayaran ENUM('MENUNGGU','TERVERIFIKASI','DITOLAK') NOT NULL DEFAULT 'MENUNGGU',
  total_bayar DECIMAL(12,2) NOT NULL DEFAULT 0,
  ongkir DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  potongan_voucher DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  payment_reference VARCHAR(150) NULL,
  qris_url TEXT NULL,
  payment_transaction_id VARCHAR(150) NULL,
  stock_processed_at DATETIME NULL,
  reward_processed_at DATETIME NULL,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
  FOREIGN KEY (meja_id) REFERENCES meja(id) ON DELETE SET NULL,
  CHECK ((jenis_pesanan='DINE_IN' AND meja_id IS NOT NULL) OR jenis_pesanan IN ('TAKEAWAY','DELIVERY')),
  CONSTRAINT fk_transaksi_alamat FOREIGN KEY (alamat_id) REFERENCES alamat_pelanggan(id) ON DELETE SET NULL,
  CONSTRAINT fk_transaksi_voucher FOREIGN KEY (voucher_id) REFERENCES voucher(id) ON DELETE SET NULL,
  INDEX idx_tx_customer (pelanggan_id),
  INDEX idx_tx_status (status_pesanan),
  INDEX idx_tx_date (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE detail_transaksi (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transaksi_id BIGINT UNSIGNED NOT NULL,
  menu_id BIGINT UNSIGNED NOT NULL,
  qty INT UNSIGNED NOT NULL,
  harga_satuan DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  topping_id BIGINT UNSIGNED NULL,
  level_pedas TINYINT UNSIGNED NULL,
  is_gratis TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (transaksi_id) REFERENCES transaksi_penjualan(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_id) REFERENCES menu(id),
  FOREIGN KEY (topping_id) REFERENCES topping(id),
  CHECK (level_pedas IS NULL OR level_pedas BETWEEN 0 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pengiriman (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE voucher_redemption (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chat_thread (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transaksi_id BIGINT UNSIGNED NOT NULL,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  status ENUM('AKTIF','DITUTUP') NOT NULL DEFAULT 'AKTIF',
  last_message_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_chat_transaksi (transaksi_id),
  INDEX idx_chat_pelanggan (pelanggan_id, last_message_at),
  CONSTRAINT fk_chat_transaksi FOREIGN KEY (transaksi_id) REFERENCES transaksi_penjualan(id) ON DELETE CASCADE,
  CONSTRAINT fk_chat_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chat_pesan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  thread_id BIGINT UNSIGNED NOT NULL,
  pengirim ENUM('PELANGGAN','ADMIN') NOT NULL,
  isi TEXT NOT NULL,
  dibaca_pelanggan_at DATETIME NULL,
  dibaca_admin_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_chat_pesan_thread (thread_id, id),
  CONSTRAINT fk_chat_pesan_thread FOREIGN KEY (thread_id) REFERENCES chat_thread(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE keranjang (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_keranjang_pelanggan (pelanggan_id),
  CONSTRAINT fk_keranjang_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE keranjang_item (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE produk_foto (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL,
  file VARCHAR(255) NOT NULL,
  urutan TINYINT UNSIGNED NOT NULL DEFAULT 0,
  is_utama TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_produk_foto (menu_id, urutan),
  CONSTRAINT fk_produk_foto_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ulasan_produk (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wishlist (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  menu_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_wishlist (pelanggan_id, menu_id),
  CONSTRAINT fk_wishlist_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE membership (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id BIGINT UNSIGNED NOT NULL UNIQUE,
  level VARCHAR(50) NOT NULL DEFAULT 'REGULER',
  tanggal_gabung DATE NOT NULL,
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bonus_produk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL,
  jenis_reward ENUM('per_transaksi','kumulatif') NOT NULL,
  status_aktif TINYINT(1) NOT NULL DEFAULT 0,
  tanggal_mulai DATE NULL,
  tanggal_selesai DATE NULL,
  keterangan VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (menu_id) REFERENCES menu(id),
  INDEX idx_bonus_type_active (jenis_reward, status_aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reward_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  transaksi_id BIGINT UNSIGNED NOT NULL,
  bonus_produk_id BIGINT UNSIGNED NULL,
  jenis_reward ENUM('per_transaksi','kumulatif') NOT NULL,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reward_tx_type (transaksi_id, jenis_reward),
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
  FOREIGN KEY (transaksi_id) REFERENCES transaksi_penjualan(id),
  FOREIGN KEY (bonus_produk_id) REFERENCES bonus_produk(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE keuangan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE NOT NULL,
  jenis ENUM('PEMASUKAN','PENGELUARAN') NOT NULL,
  sumber ENUM('MANUAL','OPERASIONAL') NOT NULL DEFAULT 'MANUAL',
  kategori VARCHAR(100) NOT NULL,
  jumlah DECIMAL(12,2) NOT NULL,
  keterangan VARCHAR(255) NULL,
  bukti VARCHAR(255) NULL,
  created_by VARCHAR(60) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fin_date (tanggal),
  INDEX idx_fin_jenis_sumber (jenis, sumber, tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE resep_produk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL,
  bahan_baku_id BIGINT UNSIGNED NOT NULL,
  jumlah DECIMAL(12,3) NOT NULL DEFAULT 0,
  keterangan VARCHAR(255) NULL,
  satuan_resep VARCHAR(12) NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_resep (menu_id, bahan_baku_id),
  INDEX idx_resep_menu (menu_id),
  CONSTRAINT fk_resep_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE,
  CONSTRAINT fk_resep_bahan FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  jenis VARCHAR(50) NOT NULL,
  pesan VARCHAR(255) NOT NULL,
  status ENUM('OPEN','READ') NOT NULL DEFAULT 'OPEN',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pengaturan_toko (id, nama_toko, alamat, latitude, longitude, ongkir_dasar, jarak_dasar_km, tarif_per_500m, batas_jarak_km, jam_buka)
VALUES (1, 'Warung Maem Jadoel', 'Depok, Jawa Barat', -6.4024840, 106.7942430, 10000.00, 1.00, 2000.00, 10.00, '10.00 - 22.00');

INSERT INTO kategori_menu (nama) VALUES ('Mie'),('Seblak'),('Japlak Karamel'),('Pangsit');

INSERT INTO voucher (kode, deskripsi, tipe, nilai, min_belanja, maks_potongan, kuota_total, kuota_per_pelanggan, tanggal_mulai, tanggal_selesai, status_aktif)
VALUES ('MAEM10', 'Potongan Rp 10.000 untuk pesanan pertamamu.', 'NOMINAL', 10000.00, 50000.00, NULL, 100, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1);

-- Katalog produk sengaja kosong pada database baru. Admin menambahkan produk melalui Menu & Bonus.

INSERT INTO topping (nama, harga_tambahan) VALUES
('Pangsit',2000),('Baso',2000),('Sosis',2000);

INSERT INTO meja (nomor_meja) VALUES
('1'),('2'),('3'),('4');


INSERT INTO stok_produk (menu_id, jumlah_stok, stok_minimum)
SELECT id, 0, 5 FROM menu WHERE id NOT IN (SELECT menu_id FROM stok_produk);

-- Ganti hash ini dengan hasil password_hash('Admin123!', PASSWORD_BCRYPT)
INSERT INTO users (username,email,password,role)
VALUES ('admin','admin@maemjadoel.test','$2y$10$Zs711QCqfyc.Zhemwuc3CeJ7RpjdwoE8D5NeEBmyCfiPdylY4CY7i','ADMIN');


-- Fitur Ulasan Pelanggan
CREATE TABLE IF NOT EXISTS ulasan_pelanggan (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 transaksi_id BIGINT UNSIGNED NOT NULL,
 pelanggan_id BIGINT UNSIGNED NOT NULL,
 rating TINYINT UNSIGNED NOT NULL,
 komentar TEXT NULL,
 status_ulasan ENUM('MENUNGGU','DITAMPILKAN','DISEMBUNYIKAN') NOT NULL DEFAULT 'MENUNGGU',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_ulasan_transaksi (transaksi_id),
 INDEX idx_ulasan_status_created (status_ulasan,created_at),
 INDEX idx_ulasan_pelanggan (pelanggan_id),
 CONSTRAINT fk_ulasan_transaksi FOREIGN KEY (transaksi_id) REFERENCES transaksi_penjualan(id) ON DELETE CASCADE,
 CONSTRAINT fk_ulasan_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE,
 CONSTRAINT chk_ulasan_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
