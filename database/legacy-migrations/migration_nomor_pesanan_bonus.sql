USE maem_jadoel;

-- VE29: perbaikan migrasi No. Pesanan harian.
-- Jalankan SATU KALI pada database lama yang sudah dipakai project.
-- Aman untuk database yang sudah memiliki atau belum memiliki kolom nomor_pesanan.

CREATE TABLE IF NOT EXISTS nomor_pesanan_harian (
  tanggal DATE PRIMARY KEY,
  nomor_terakhir INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Pastikan kolom nomor_pesanan ada.
SET @has_nomor := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='transaksi_penjualan'
    AND COLUMN_NAME='nomor_pesanan'
);

SET @sql := IF(
  @has_nomor=0,
  'ALTER TABLE transaksi_penjualan ADD COLUMN nomor_pesanan VARCHAR(40) NULL AFTER id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Pastikan nomor_pesanan memiliki unique index, tanpa menganggap nama index tertentu.
SET @has_uq := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE()
    AND TABLE_NAME='transaksi_penjualan'
    AND COLUMN_NAME='nomor_pesanan'
    AND NON_UNIQUE=0
);

SET @sql := IF(
  @has_uq=0,
  'ALTER TABLE transaksi_penjualan ADD UNIQUE KEY uq_transaksi_nomor_pesanan (nomor_pesanan)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Gunakan nilai sementara yang unik. Jangan SET NULL karena database lama
-- bisa saja sudah memiliki nomor_pesanan NOT NULL.
UPDATE transaksi_penjualan
SET nomor_pesanan=CONCAT('TMP-', id);

-- Normalisasi transaksi lama menjadi antrean berdasarkan tanggal, waktu, lalu id.
-- Contoh hasil: MJ-20261008-001, MJ-20261008-002, dst.
UPDATE transaksi_penjualan t
JOIN (
  SELECT id,
         CONCAT(
           'MJ-',
           DATE_FORMAT(tanggal,'%Y%m%d'),
           '-',
           LPAD(
             ROW_NUMBER() OVER (PARTITION BY DATE(tanggal) ORDER BY tanggal,id),
             3,
             '0'
           )
         ) AS nomor_baru
  FROM transaksi_penjualan
) x ON x.id=t.id
SET t.nomor_pesanan=x.nomor_baru;

ALTER TABLE transaksi_penjualan
  MODIFY nomor_pesanan VARCHAR(40) NOT NULL;

-- Sinkronkan counter harian dengan nomor transaksi terakhir yang sudah ada.
DELETE FROM nomor_pesanan_harian;

INSERT INTO nomor_pesanan_harian(tanggal,nomor_terakhir)
SELECT DATE(tanggal),
       MAX(CAST(SUBSTRING_INDEX(nomor_pesanan,'-',-1) AS UNSIGNED))
FROM transaksi_penjualan
GROUP BY DATE(tanggal);
