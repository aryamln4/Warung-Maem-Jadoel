# Warung Maem Jadoel — ERP UMKM PHP Native

## 1. Ringkasan sistem
Warung Maem Jadoel adalah aplikasi ERP/self-order UMKM berbasis PHP Native + MySQL untuk mengelola katalog, pesanan, pelanggan, voucher, stok, meja, dan keuangan.

Aplikasi dibagi menjadi empat pengalaman utama:

1. **Website publik** — landing page, promo, menu, FAQ, lokasi, login/daftar.
2. **Pelanggan** — login, pilih menu, keranjang, checkout, pilih Dine-in/Takeaway, pilih meja saat checkout, pembayaran, riwayat, profil, dan voucher.
3. **Admin** — dashboard operasional, Produk & Stok, Operasional, meja, pelanggan, keuangan.
4. **Database MySQL** — seluruh data transaksi dan master menggunakan PDO prepared statement.

## 2. Aturan QR yang sudah final
QR yang ditempel di warung adalah **QR eksternal**, dibuat di luar project dan hanya berisi URL website yang sudah di-hosting.

Contoh konsep:

`QR warung → https://domain-warung.com/`

Aplikasi **tidak** membuat QR per meja, tidak meminta `qr_token`, dan tidak memvalidasi token meja.

Untuk Dine-in, pelanggan memilih meja sendiri **saat checkout**.

Default database menyediakan 4 meja:

- Meja 1
- Meja 2
- Meja 3
- Meja 4

Admin dapat menambah, edit, aktif/nonaktif, dan menghapus meja dari halaman **Meja**.

## 3. Alur pelanggan

`Landing → Daftar/Login → Menu → Keranjang → Checkout → Jenis Pesanan → Pilih Meja (jika Dine-in) → Pembayaran → Pesanan`

### Dine-in
Pelanggan memilih:
- Dine-in
- Meja aktif
- QRIS/Tunai

### Takeaway
Pelanggan memilih:
- Takeaway
- QRIS/Tunai
- Tidak ada meja.

## 4. Produk & Stok — satu menu admin
Menu admin **Produk & Stok** menjadi pusat pengelolaan:

### Produk
CRUD melalui form modal dan tombol:
- foto
- nama produk
- kategori
- sub-kategori
- harga
- topping yang tersedia
- level pedas 0–5
- stok awal ketika produk baru dibuat
- aktif/nonaktif

Upload gambar memakai form `multipart/form-data`. Format yang diterima JPG, PNG, WEBP dengan ukuran maksimal 6 MB.

Produk yang sudah pernah dipakai transaksi tidak dihapus secara fisik; tombol Hapus/Nonaktifkan membuat produk tidak tampil di katalog pelanggan agar histori transaksi tetap aman.

### Topping
CRUD melalui UI:
- nama topping
- harga tambahan

Pada form produk, topping dipilih menggunakan kartu checkbox sehingga tidak perlu Ctrl/Cmd.

### Kategori
CRUD melalui UI:
- tambah
- edit
- hapus jika tidak sedang digunakan produk

### Stok Produk & Stok Bahan Baku
Kedua pengelolaan stok berada di tab **Produk & Stok**, bukan di Operasional:
- **Stok Produk** — menyesuaikan jumlah stok produk. Penyesuaian stok tidak membuat catatan pengeluaran.
- **Stok Bahan Baku** — mencatat bahan, pembelian, dan pembatalan pembelian. Setiap pembelian otomatis membuat satu baris pengeluaran di Keuangan; membatalkan pembelian mengembalikan stok dan menghapus pengeluarannya.

### Voucher
CRUD melalui UI:
- kode voucher
- tipe potongan (nominal, persen, ongkir)
- nilai dan minimal belanja
- kuota dan masa berlaku
- aktif/nonaktif

Voucher adalah satu-satunya fitur promo yang aktif di UI pelanggan dan landing page. Bonus produk sudah tidak ditampilkan di UI aktif; data historis bonus tetap disimpan agar transaksi lama tidak rusak.

## 5. Aturan voucher
Kode voucher dimasukkan pada halaman checkout. Potongan hanya berlaku jika voucher aktif, berada dalam masa berlaku, kuota tersedia, dan subtotal memenuhi minimal belanja. Voucher ongkir hanya memotong biaya kirim pada pesanan delivery.

## 6. Status pesanan

### Dine-in
`MENUNGGU_PEMBAYARAN → DIPROSES → SEDANG_DIANTAR → SELESAI`

### Takeaway
`MENUNGGU_PEMBAYARAN → DIPROSES → SIAP_DIAMBIL → SELESAI`

Untuk pembayaran tunai, admin melakukan konfirmasi sebelum order diproses.

## 7. UI/UX

Frontend tidak lagi memakai framework CSS eksternal. Seluruh tampilan berdiri di atas **CSS biasa + design tokens**:

- `public/assets/css/foundation.css` — fondasi: design tokens (warna, font, radius, bayangan, spasi), tipografi selaras, dan komponen dasar (grid, tombol, form, tabel, alert, badge, modal, collapse, utilitas). Ingin mengubah warna brand atau font seluruh aplikasi? Ubah blok `:root` di file ini.
- `public/assets/css/app.css` — gaya spesifik halaman di atas fondasi tersebut.
- Ikon memakai **SVG inline** melalui helper PHP `icon('nama-ikon')` (didefinisikan di `config/config.php`). Ikon mewarisi warna teks sehingga selalu selaras; tidak ada dependensi CDN ikon.
- Modal & collapse digerakkan oleh JavaScript vanilla (`WMJ.modal.open/close`, `data-modal-open`, `data-modal-close`, `data-collapse-target`) di `public/assets/js/app.js`.

Font distandarkan (satu pasangan, dideklarasikan sebagai token `--font-sans` dan `--font-display` di `foundation.css`):
- **Inter** untuk seluruh teks: body, navigasi, form, tabel, tombol.
- **Inter** untuk heading/judul dengan tracking rapat (token `--font-display`).

Target responsive:
- Desktop: >=1024px
- Tablet: 768–1023px
- Mobile: <=767px

Layout dipisahkan secara visual antara:
- public/landing
- customer workspace
- admin workspace
- authentication

Admin memakai sidebar + topbar. Customer memakai header pelanggan. Landing memakai header marketing. Halaman login/daftar memakai header minimal.

## 8. Instalasi lokal (XAMPP)

1. Buat database kosong bernama `maem_jadoel` di phpMyAdmin.
2. Pilih database tersebut, buka tab **Import**, lalu impor `database/database.sql`.
3. Salin `.env.example` menjadi `.env` dan sesuaikan konfigurasi MySQL lokal.
4. Buka `http://localhost/warung-maem-jadoel/` jika folder project berada di `htdocs/warung-maem-jadoel`.

## 9. Akun admin awal

Username: `admin`

Email: `admin@maemjadoel.test`

Password awal: `Admin123!`

Segera ganti password setelah login pertama.

## 10. Database

`database/database.sql` adalah satu-satunya SQL untuk instalasi bersih. File ini berisi schema inti POS dan modul e-commerce yang dipakai aplikasi, termasuk alamat, pengiriman, voucher, keranjang, chat, resep, pembelian bahan, dan ulasan produk.

File SQL final tidak membuat database, tidak memilih nama database, dan tidak menghapus tabel. Buat/pilih database terlebih dahulu di phpMyAdmin. Untuk instalasi baru, impor satu file tersebut saja.

File terdahulu disimpan di `database/legacy-migrations/` untuk referensi historis; jangan impor file-file tersebut setelah mengimpor SQL final.

## 10A. Hosting InfinityFree

Baca `INSTALL_INFINITYFREE.md` untuk panduan lengkap pembuatan database, upload `htdocs`, konfigurasi `.env`, dependensi SMTP, dan Midtrans.

## 11. Teknologi
- PHP Native 8.2+
- MySQL/MariaDB
- PDO prepared statements
- CSS custom + design tokens (tanpa framework CSS eksternal; pengganti Bootstrap ada di `foundation.css`)
- JavaScript/AJAX polling
- Session authentication
- CSRF protection
- PHPMailer untuk forgot password
- QRIS gateway abstraction untuk integrasi pembayaran

## 12. Catatan development

Project ini sengaja tidak menggunakan QR per meja. QR warung adalah shortcut eksternal menuju URL website.

Semua aktivitas operasional admin dibuat melalui halaman/form di browser. Database hanya perlu diimpor saat setup/reset development.

### Struktur kategori menu (revisi)
- Produk sekarang hanya memiliki **satu kategori** melalui `kategori_id`.
- Konsep `sub_kategori` sudah dihapus karena sebelumnya membuat tampilan kategori terasa dobel.
- Contoh: kategori **Mie** berisi produk **Mie Yamin**, **Mie Jebew**, dan seterusnya. Kategori **Seblak** berisi **Seblak Ceker**, **Seblak Sosis**, dan seterusnya.
- Database baru memakai kategori awal Mie, Seblak, Japlak Karamel, dan Pangsit; data produk tetap kosong agar admin mengisi sendiri.


## Fitur Ulasan Pelanggan
- Instalasi baru: jalankan `database/database.sql` pada database kosong (skrip membuat seluruh tabel yang dipakai aplikasi).
- Database yang sudah berisi data: backup terlebih dahulu, lalu jalankan migrasi yang sesuai. Jangan jalankan ulang SQL instalasi bersih pada database berisi data.
- Pelanggan dapat mengulas transaksi miliknya setelah status `SELESAI`, satu ulasan per transaksi, rating 1–5, komentar opsional. Edit dibatasi 7 hari dari waktu pembuatan pertama.
- Admin hanya dapat melihat ulasan pelanggan dan mengubah statusnya antara `DITAMPILKAN` dan `DISEMBUNYIKAN`. Admin tidak dapat mengedit atau menghapus isi ulasan. Hanya `DITAMPILKAN` yang muncul pada Landing Page di bagian **Ulasan Pelanggan**. Tidak ada ulasan contoh.

## VE29 — Perbaikan No. Pesanan, Profil, dan Riwayat

Jika database lama berasal dari VE27/VE28, wajib menjalankan `database/migration_nomor_pesanan_bonus.sql` satu kali. Error `Unknown column 't.nomor_pesanan'` pada Profil dan `Undefined array key "nomor_pesanan"` pada Riwayat terjadi ketika database lama belum memiliki kolom `transaksi_penjualan.nomor_pesanan`.

Migrasi VE29 diperbaiki agar aman untuk database yang kolom `nomor_pesanan`-nya sudah `NOT NULL`: migrasi menggunakan nilai sementara unik `TMP-{id}`, bukan mengosongkan kolom menjadi `NULL`.


## Integrasi Midtrans QRIS Sandbox

Untuk setup payment gateway QRIS, baca `docs/MIDTRANS_SANDBOX.md`. Gunakan `MIDTRANS_MODE=sandbox` dan isi `MIDTRANS_SERVER_KEY` di `.env` lokal. Jangan membagikan file `.env`. Jika memakai database lama, jalankan migration `database/migration_midtrans_qris.sql` setelah backup. URL webhook wajib dapat diakses dari internet melalui HTTPS.


## Catatan deploy InfinityFree

- File schema final tersedia di `database/database.sql`. Import file ini ke database **kosong** yang dibuat dari panel InfinityFree. File tidak memilih/membuat database dan tidak menghapus tabel, jadi jangan impor ulang di database yang sudah berisi data.
- File `.env` berisi kredensial dan tidak disertakan dalam paket. Buat `.env` dari `.env.example`, lalu isi host/nama/user/password database dari panel InfinityFree.
- `APP_URL` sebaiknya dikosongkan. Aplikasi mendeteksi domain, HTTPS, dan folder instalasi secara otomatis; nilai `http://localhost/...` yang tertinggal akan membuat link mengarah ke komputer lokal.
- Saat `APP_ENV=production`, halaman error tidak memaparkan stack trace atau path server; rincian ditulis ke error log.
- `public/index.php` meneruskan request ke entry point utama supaya tidak ada dua sistem routing berbeda.
- Panduan lengkap ada di `INSTALL_INFINITYFREE.md`.
