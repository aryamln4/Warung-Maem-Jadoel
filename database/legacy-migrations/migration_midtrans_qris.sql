-- Jalankan SATU KALI pada database yang sudah ada. Backup database terlebih dahulu.
ALTER TABLE transaksi_penjualan
  ADD COLUMN qris_url TEXT NULL AFTER payment_reference,
  ADD COLUMN payment_transaction_id VARCHAR(150) NULL AFTER qris_url,
  ADD COLUMN stock_processed_at DATETIME NULL AFTER payment_transaction_id,
  ADD COLUMN reward_processed_at DATETIME NULL AFTER stock_processed_at;
