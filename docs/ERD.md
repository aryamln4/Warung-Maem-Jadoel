# ERD — Warung Maem Jadoel

```mermaid
erDiagram
 USERS ||--o{ SYSTEM_NOTIFICATIONS : creates
 PELANGGAN ||--o{ PASSWORD_RESETS : requests
 PELANGGAN ||--|| MEMBERSHIP : has
 PELANGGAN ||--o{ TRANSAKSI_PENJUALAN : makes
 MEJA o|--o{ TRANSAKSI_PENJUALAN : hosts
 KATEGORI_MENU ||--o{ MENU : contains
 MENU ||--|| STOK_PRODUK : has
 STOK_PRODUK ||--o{ KARTU_STOK : records
 MENU ||--o{ MENU_TOPPING : allows
 TOPPING ||--o{ MENU_TOPPING : included
 TRANSAKSI_PENJUALAN ||--o{ DETAIL_TRANSAKSI : contains
 MENU ||--o{ DETAIL_TRANSAKSI : sold
 TOPPING ||--o{ DETAIL_TRANSAKSI : selected
 MENU ||--o{ BONUS_PRODUK : reward
 PELANGGAN ||--o{ REWARD_LOG : earns
 TRANSAKSI_PENJUALAN ||--o{ REWARD_LOG : triggers
 BONUS_PRODUK ||--o{ REWARD_LOG : snapshots
```

## Catatan desain
- `menu` menyimpan setiap ukuran/rasa sebagai produk terpisah.
- `detail_transaksi` menyimpan topping dan level pedas per item.
- `reward_log.bonus_produk_id` menjaga histori bonus tetap menunjuk ke produk bonus yang benar.
- `pelanggan.total_produk_kumulatif` menyimpan pool kumulatif yang hanya bertambah dari transaksi dengan qty < 10.
- `transaksi_penjualan.jenis_pesanan` membedakan `DINE_IN` dan `TAKEAWAY`. Pada takeaway, `meja_id` boleh NULL; pada dine-in, `meja_id` wajib terisi.
