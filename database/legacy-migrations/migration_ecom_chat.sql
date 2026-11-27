-- =====================================================================
-- FASE 2 — Chat per pesanan (di dalam sistem, tanpa WA)
-- =====================================================================
USE maem_jadoel;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS chat_thread (
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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_pesan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  thread_id BIGINT UNSIGNED NOT NULL,
  pengirim ENUM('PELANGGAN','ADMIN') NOT NULL,
  isi TEXT NOT NULL,
  dibaca_pelanggan_at DATETIME NULL,
  dibaca_admin_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_chat_pesan_thread (thread_id, id),
  CONSTRAINT fk_chat_pesan_thread FOREIGN KEY (thread_id) REFERENCES chat_thread(id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;
