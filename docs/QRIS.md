# QRIS Midtrans

Website menggunakan Midtrans Core API untuk membuat QRIS dinamis per transaksi. QRIS statis yang diunggah admin tidak digunakan sebagai bukti pembayaran karena tidak dapat memberi tahu aplikasi apakah pembayaran sudah masuk.

## Alur pelanggan
1. Pelanggan checkout dan memilih QRIS.
2. Aplikasi menyimpan transaksi sebagai `MENUNGGU` / `MENUNGGU_PEMBAYARAN`.
3. Halaman transaksi meminta backend membuat QRIS dinamis melalui Midtrans Sandbox.
4. URL QRIS spesifik transaksi disimpan dan ditampilkan dengan tombol buka/unduh.
5. Pelanggan memindai QR menggunakan aplikasi bank/e-wallet yang mendukung QRIS dalam lingkungan pembayaran yang sesuai. Sandbox adalah simulasi, bukan transaksi bank sungguhan.
6. Midtrans mengirim notifikasi ke `index.php?r=payment/qris-webhook`.
7. Backend memverifikasi `signature_key`, `order_id`, dan `gross_amount`. Hanya status `settlement` atau `capture` dengan fraud status `accept` yang ditandai terbayar.
8. Status pelanggan diperbarui melalui polling, lalu popup hanya muncul setelah status database berubah menjadi `TERVERIFIKASI`.

## Konfigurasi
- Atur `MIDTRANS_MODE=sandbox` dan isi `MIDTRANS_SERVER_KEY` dengan Server Key Sandbox.
- Aktifkan ekstensi PHP cURL.
- Pastikan metode QRIS tersedia pada akun Midtrans.
- URL notifikasi harus dapat dijangkau melalui internet/HTTPS. Untuk localhost, gunakan tunnel HTTPS.
- Untuk database lama, jalankan `database/migration_midtrans_qris.sql` setelah backup.
- Lihat `docs/MIDTRANS_SANDBOX.md` untuk langkah konfigurasi dan pengujian.

## Tunai
Pembayaran tunai tetap memerlukan konfirmasi owner. Sistem tidak menganggap QRIS dibayar hanya karena pelanggan kembali ke halaman checkout atau menekan tombol.
