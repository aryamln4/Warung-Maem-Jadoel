USE maem_jadoel;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS kartu_stok_bahan, bahan_baku, reward_log, bonus_produk, keuangan, detail_transaksi, transaksi_penjualan,
    kartu_stok, stok_produk, meja, menu_topping, topping, menu, kategori_menu,
    password_resets, membership, pelanggan, users, system_notifications;
SET FOREIGN_KEY_CHECKS=1;


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
) ENGINE=InnoDB;

CREATE TABLE kartu_stok_bahan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bahan_baku_id BIGINT UNSIGNED NOT NULL,
  jenis ENUM('MASUK','KELUAR','PENYESUAIAN') NOT NULL,
  jumlah DECIMAL(12,3) NOT NULL,
  keterangan VARCHAR(255) NULL,
  tanggal TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_kartu_bahan_tanggal (bahan_baku_id, tanggal),
  CONSTRAINT fk_kartu_bahan FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('ADMIN') NOT NULL DEFAULT 'ADMIN',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_pesanan VARCHAR(40) NOT NULL UNIQUE,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  expired_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE kategori_menu (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE topping (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL UNIQUE,
  harga_tambahan DECIMAL(12,2) NOT NULL DEFAULT 0,
  status_aktif TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE menu_topping (
  menu_id BIGINT UNSIGNED NOT NULL,
  topping_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (menu_id, topping_id),
  FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE,
  FOREIGN KEY (topping_id) REFERENCES topping(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE meja (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_meja VARCHAR(30) NOT NULL UNIQUE,
  status_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE stok_produk (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL UNIQUE,
  jumlah_stok INT NOT NULL DEFAULT 0,
  stok_minimum INT NOT NULL DEFAULT 5,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE kartu_stok (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  stok_produk_id BIGINT UNSIGNED NOT NULL,
  jenis ENUM('MASUK','KELUAR','PENYESUAIAN') NOT NULL,
  jumlah INT NOT NULL,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  keterangan VARCHAR(255) NULL,
  FOREIGN KEY (stok_produk_id) REFERENCES stok_produk(id) ON DELETE CASCADE,
  INDEX idx_kartu_tanggal (tanggal)
) ENGINE=InnoDB;

CREATE TABLE nomor_pesanan_harian (
  tanggal DATE PRIMARY KEY,
  nomor_terakhir INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE transaksi_penjualan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_pesanan VARCHAR(40) NOT NULL UNIQUE,
  pelanggan_id BIGINT UNSIGNED NOT NULL,
  meja_id BIGINT UNSIGNED NULL,
  jenis_pesanan ENUM('DINE_IN','TAKEAWAY') NOT NULL DEFAULT 'DINE_IN',
  status_pesanan ENUM('MENUNGGU_PEMBAYARAN','MENUNGGU_KONFIRMASI','DIPROSES','SEDANG_DIANTAR','SIAP_DIAMBIL','SELESAI','DIBATALKAN') NOT NULL DEFAULT 'MENUNGGU_PEMBAYARAN',
  metode_bayar ENUM('QRIS','TUNAI') NOT NULL,
  status_pembayaran ENUM('MENUNGGU','TERVERIFIKASI','DITOLAK') NOT NULL DEFAULT 'MENUNGGU',
  total_bayar DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_reference VARCHAR(150) NULL,
  qris_url TEXT NULL,
  payment_transaction_id VARCHAR(150) NULL,
  stock_processed_at DATETIME NULL,
  reward_processed_at DATETIME NULL,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
  FOREIGN KEY (meja_id) REFERENCES meja(id) ON DELETE SET NULL,
  CHECK ((jenis_pesanan='DINE_IN' AND meja_id IS NOT NULL) OR (jenis_pesanan='TAKEAWAY')),
  INDEX idx_tx_customer (pelanggan_id),
  INDEX idx_tx_status (status_pesanan),
  INDEX idx_tx_date (tanggal)
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE membership (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id BIGINT UNSIGNED NOT NULL UNIQUE,
  level VARCHAR(50) NOT NULL DEFAULT 'REGULER',
  tanggal_gabung DATE NOT NULL,
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

CREATE TABLE keuangan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE NOT NULL,
  jenis ENUM('PEMASUKAN','PENGELUARAN') NOT NULL,
  kategori VARCHAR(100) NOT NULL,
  jumlah DECIMAL(12,2) NOT NULL,
  keterangan VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fin_date (tanggal)
) ENGINE=InnoDB;

CREATE TABLE system_notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  jenis VARCHAR(50) NOT NULL,
  pesan VARCHAR(255) NOT NULL,
  status ENUM('OPEN','READ') NOT NULL DEFAULT 'OPEN',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO kategori_menu (nama) VALUES ('Mie'),('Seblak'),('Japlak Karamel'),('Pangsit');

-- Katalog produk sengaja kosong pada database baru. Admin menambahkan produk melalui Menu & Bonus.

INSERT INTO topping (nama, harga_tambahan) VALUES
('Pangsit',2000),('Baso',2000),('Sosis',2000);

INSERT INTO meja (nomor_meja) VALUES
('1'),('2'),('3'),('4');


INSERT INTO stok_produk (menu_id, jumlah_stok, stok_minimum)
SELECT id, 0, 5 FROM menu WHERE id NOT IN (SELECT menu_id FROM stok_produk);

-- Ganti hash ini dengan hasil password_hash('Admin123!', PASSWORD_BCRYPT)
INSERT INTO users (username,email,password,role)
VALUES ('admin','admin@maemjadoel.test','$2y$12$8xkY9eK/slZpsxKIVqobC.qfC8KLkVm3uG9gACq3OSfAqRcGYy742','ADMIN');


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
) ENGINE=InnoDB;
