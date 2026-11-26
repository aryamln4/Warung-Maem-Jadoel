# Integrasi Midtrans QRIS Sandbox

## Konfigurasi
1. Salin file `.env.example` (atau edit `.env` lokal) dan isi `MIDTRANS_SERVER_KEY` dengan Server Key dari lingkungan Sandbox Midtrans. Jangan pernah memasukkan key ke JavaScript/HTML atau mengunggah `.env`.
2. Pastikan `MIDTRANS_MODE=sandbox`, `APP_ENV=development`, ekstensi PHP cURL aktif, dan metode QRIS tersedia/aktif di akun Midtrans.
3. Jalankan migration `database/migration_midtrans_qris.sql` sekali pada database yang sudah digunakan. Backup database dahulu. Untuk database baru, kolom yang diperlukan sudah tercantum di `database/database.sql`.
4. Konfigurasikan URL notifikasi pembayaran di Midtrans Sandbox ke `https://DOMAIN-PUBLIK/index.php?r=payment/qris-webhook`. URL harus bisa dijangkau dari internet. Untuk localhost gunakan tunnel HTTPS saat pengujian.
5. Buat pesanan QRIS dari website. Backend memanggil Midtrans Core API, menyimpan URL QR per transaksi, dan menampilkan QR pada halaman checkout.

## Panel diagnostik admin (mengecek konfigurasi key)
Halaman **Keuangan (admin/finance)** menampilkan kartu "Midtrans QRIS" yang otomatis membaca `.env`:
badge **Provider / Mode / Server Key / cURL / Production guard** dan alamat **Webhook URL** yang siap disalin.
Kartu ini tidak pernah menampilkan isi key, hanya status terisi/tidak. Ketika semua badge hijau, gateway siap dipakai.

## Sinkronisasi manual saat webhook tidak sampai
Midtrans tidak dapat mengirim webhook ke `localhost`. Untuk pengujian di komputer lokal (atau bila notifikasi
gagal masuk), buka **Pesanan (admin/orders)** lalu klik **"Cek Status QRIS ke Midtrans"** pada pesanan yang
berstatus `MENUNGGU`. Sistem menarik status langsung dari API Midtrans (server-to-server, Basic Auth Server Key):
- `settlement` / `capture+accept` → transaksi ditandai `TERVERIFIKASI`, reward & stok diproses (logika yang sama dengan webhook).
- `expire` / `cancel` / `deny` → transaksi ditandai `DITOLAK`.
- `pending` → pesan info bahwa pembayaran belum masuk.
Aksi ini terlindungi login admin + CSRF, dan tidak pernah dipercaya tanpa cocoknya nominal.

## Status dan keamanan
- Status `settlement` (atau `capture` dengan `fraud_status=accept`) diproses sebagai pembayaran sukses setelah signature Midtrans diverifikasi.
- Nominal `gross_amount` harus cocok dengan transaksi di database.
- Redirect browser tidak pernah dianggap bukti pembayaran.
- Sandbox adalah simulasi; jangan menguji kode Sandbox dengan membayar QR dari aplikasi bank sungguhan.
- Jangan beralih ke Production sebelum uji webhook, nominal, transaksi berulang, kedaluwarsa, stok, bonus, dan laporan berhasil.

## Catatan
Integrasi ini menggunakan Midtrans Core API endpoint `/v2/charge` untuk metode `qris`. Aktivasi QRIS dan bentuk respons yang tersedia bergantung pada konfigurasi akun Midtrans. Jika API menolak charge, cek pesan error di log PHP/server, bukan menampilkan Server Key ke browser.
