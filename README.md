# Warung Maem Jadoel â€” ERP UMKM PHP Native

## 1. Ringkasan sistem
Warung Maem Jadoel adalah aplikasi ERP/self-order UMKM berbasis PHP Native + MySQL untuk mengelola katalog, pesanan, pelanggan, voucher, stok, meja, dan keuangan.

Aplikasi dibagi menjadi empat pengalaman utama:

1. **Website publik** â€” landing page, promo, menu, FAQ, lokasi, login/daftar.
2. **Pelanggan** â€” login, pilih menu, keranjang, checkout, pilih Dine-in/Takeaway, pilih meja saat checkout, pembayaran, riwayat, profil, dan voucher.
3. **Admin** â€” dashboard operasional, Produk & Stok, Operasional, meja, pelanggan, keuangan.
4. **Database MySQL** â€” seluruh data transaksi dan master menggunakan PDO prepared statement.

## 2. Aturan QR yang sudah final
QR yang ditempel di warung adalah **QR eksternal**, dibuat di luar project dan hanya berisi URL website yang sudah di-hosting.

Contoh konsep:

`QR warung â†’ https://domain-warung.com/`

Aplikasi **tidak** membuat QR per meja, tidak meminta `qr_token`, dan tidak memvalidasi token meja.

Untuk Dine-in, pelanggan memilih meja sendiri **saat checkout**.

Default database menyediakan 4 meja:

- Meja 1
- Meja 2
- Meja 3
- Meja 4

Admin dapat menambah, edit, aktif/nonaktif, dan menghapus meja dari halaman **Meja**.

## 3. Alur pelanggan

`Landing â†’ Daftar/Login â†’ Menu â†’ Keranjang â†’ Checkout â†’ Jenis Pesanan â†’ Pilih Meja (jika Dine-in) â†’ Pembayaran â†’ Pesanan`

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

## 4. Produk & Stok â€” satu menu admin
Menu admin **Produk & Stok** menjadi pusat pengelolaan:

### Produk
CRUD melalui form modal dan tombol:
- foto
- nama produk
- kategori
- sub-kategori
- harga
- topping yang tersedia
- level pedas 0â€“5
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
- **Stok Produk** â€” menyesuaikan jumlah stok produk. Penyesuaian stok tidak membuat catatan pengeluaran.
- **Stok Bahan Baku** â€” mencatat bahan, pembelian, dan pembatalan pembelian. Setiap pembelian otomatis membuat satu baris pengeluaran di Keuangan; membatalkan pembelian mengembalikan stok dan menghapus pengeluarannya.

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
`MENUNGGU_PEMBAYARAN â†’ DIPROSES â†’ SEDANG_DIANTAR â†’ SELESAI`

### Takeaway
`MENUNGGU_PEMBAYARAN â†’ DIPROSES â†’ SIAP_DIAMBIL â†’ SELESAI`

Untuk pembayaran tunai, admin melakukan konfirmasi sebelum order diproses.

## 7. UI/UX

Frontend tidak lagi memakai framework CSS eksternal. Seluruh tampilan berdiri di atas **CSS biasa + design tokens**:

- `public/assets/css/foundation.css` â€” fondasi: design tokens (warna, font, radius, bayangan, spasi), tipografi selaras, dan komponen dasar (grid, tombol, form, tabel, alert, badge, modal, collapse, utilitas). Ingin mengubah warna brand atau font seluruh aplikasi? Ubah blok `:root` di file ini.
- `public/assets/css/app.css` â€” gaya spesifik halaman di atas fondasi tersebut.
- Ikon memakai **SVG inline** melalui helper PHP `icon('nama-ikon')` (didefinisikan di `config/config.php`). Ikon mewarisi warna teks sehingga selalu selaras; tidak ada dependensi CDN ikon.
- Modal & collapse digerakkan oleh JavaScript vanilla (`WMJ.modal.open/close`, `data-modal-open`, `data-modal-close`, `data-collapse-target`) di `public/assets/js/app.js`.

Font distandarkan (satu pasangan, dideklarasikan sebagai token `--font-sans` dan `--font-display` di `foundation.css`):
- **Inter** untuk seluruh teks: body, navigasi, form, tabel, tombol.
- **Inter** untuk heading/judul dengan tracking rapat (token `--font-display`).

Target responsive:
- Desktop: >=1024px
- Tablet: 768â€“1023px
- Mobile: <=767px

Layout dipisahkan secara visual antara:
- public/landing
- customer workspace
- admin workspace
- authentication

Admin memakai sidebar + topbar. Customer memakai header pelanggan. Landing memakai header marketing. Halaman login/daftar memakai header minimal.

## 8. Instalasi tanpa CMD

### Database
Gunakan **phpMyAdmin**:

1. Backup database lama jika diperlukan.
2. Drop database lama untuk lingkungan development.
3. Buat database baru sesuai `.env` (default: `maem_jadoel`).
4. Klik **Import**.
5. Pilih `database/database.sql`.
6. Klik **Go**.

### Project
Extract folder project ke:

`C:\xampp\htdocs\warung-maem-jadoel`

atau folder htdocs XAMPP yang digunakan.

Buka:

`http://localhost/warung-maem-jadoel/public/`

Tidak diperlukan QR meja untuk testing.

Untuk pengelolaan produk, kategori, topping, voucher, meja, stok, pelanggan, dan keuangan gunakan menu admin melalui browser. Database baru memulai katalog produk dalam keadaan kosong; produk ditambahkan sendiri oleh admin melalui Produk & Stok.

## 9. Akun admin seed

Username: `admin`

Email: `admin@maemjadoel.test`

Password: `Admin123!`

## 10. Database development

Project ini sekarang memakai **satu file SQL saja** sebagai sumber database utama:

`database/database.sql`

File tersebut sudah berisi pembuatan database, seluruh tabel, foreign key, index, kategori awal, topping awal, 4 meja default, dan akun admin seed.

Untuk instalasi development, cukup import **satu file tersebut** melalui phpMyAdmin setelah membuat/memilih database `maem_jadoel`. Jika database lama berisi data yang ingin dipertahankan, lakukan backup terlebih dahulu. Untuk instalasi bersih, import `database/database.sql` sebagai satu-satunya file SQL project.

Project tidak lagi menyertakan file migration atau reset transaksi terpisah agar tidak terjadi kebingungan versi schema. Jika membutuhkan reset data testing, lakukan backup lalu gunakan database development yang bersih dengan mengimpor ulang `database/database.sql`.

## 10. Struktur utama

```text
config/
controller/
model/
services/
view/
  admin/
  auth/
  order/
  partials/
assets/
public/
database/
docs/
```

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
- Instalasi baru: jalankan `database/database.sql` (skrip instalasi bersih dan menghapus tabel lama).
- Database yang sudah berisi data: backup terlebih dahulu, lalu jalankan `database/migration_ulasan.sql` saja. Jangan jalankan ulang SQL instalasi bersih pada database berisi data.
- Pelanggan dapat mengulas transaksi miliknya setelah status `SELESAI`, satu ulasan per transaksi, rating 1â€“5, komentar opsional. Edit dibatasi 7 hari dari waktu pembuatan pertama.
- Admin hanya dapat melihat ulasan pelanggan dan mengubah statusnya antara `DITAMPILKAN` dan `DISEMBUNYIKAN`. Admin tidak dapat mengedit atau menghapus isi ulasan. Hanya `DITAMPILKAN` yang muncul pada Landing Page di bagian **Ulasan Pelanggan**. Tidak ada ulasan contoh.

## VE29 â€” Perbaikan No. Pesanan, Profil, dan Riwayat

Jika database lama berasal dari VE27/VE28, wajib menjalankan `database/migration_nomor_pesanan_bonus.sql` satu kali. Error `Unknown column 't.nomor_pesanan'` pada Profil dan `Undefined array key "nomor_pesanan"` pada Riwayat terjadi ketika database lama belum memiliki kolom `transaksi_penjualan.nomor_pesanan`.

Migrasi VE29 diperbaiki agar aman untuk database yang kolom `nomor_pesanan`-nya sudah `NOT NULL`: migrasi menggunakan nilai sementara unik `TMP-{id}`, bukan mengosongkan kolom menjadi `NULL`.


## Integrasi Midtrans QRIS Sandbox

Untuk setup payment gateway QRIS, baca `docs/MIDTRANS_SANDBOX.md`. Gunakan `MIDTRANS_MODE=sandbox` dan isi `MIDTRANS_SERVER_KEY` di `.env` lokal. Jangan membagikan file `.env`. Jika memakai database lama, jalankan migration `database/migration_midtrans_qris.sql` setelah backup. URL webhook wajib dapat diakses dari internet melalui HTTPS.


## Deploy ke Vercel (PHP Native)

Project ini bukan aplikasi Node.js; konfigurasi Vercel menggunakan runtime PHP komunitas `vercel-php`. File konfigurasi dan entrypoint sudah disediakan: `vercel.json` dan `api/index.php`.

**Langkah deploy:**
1. Push folder project (tanpa `.env`) ke repository GitHub.
2. Import repository di Vercel, Framework Preset pilih **Other**, Root Directory gunakan root project.
3. Tambahkan Environment Variables di Vercel (Production dan Preview sesuai kebutuhan): `APP_ENV=production`, `APP_URL=https://domain-deployment-anda`, `APP_TIMEZONE=Asia/Jakarta`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_ENCRYPTION`, `MAIL_FROM`, `MAIL_FROM_NAME`, `PAYMENT_PROVIDER`, `MIDTRANS_MODE`, `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`.
4. Gunakan database MySQL/MariaDB yang dapat diakses dari internet oleh server Vercel (bukan `127.0.0.1`/database XAMPP lokal). Import `database/database.sql` pada database hosting.
5. Deploy. Routing Vercel meneruskan `/` dan endpoint `?r=...` ke PHP front controller; aset `/assets/...` diarahkan ke `public/assets`.

**Batasan hosting serverless:** file gambar yang diupload aplikasi disimpan ke folder project dan filesystem serverless Vercel tidak permanen. Upload foto menu/QRIS tidak akan terjamin tersimpan setelah deployment baru atau cold instance. Untuk produksi, pindahkan upload ke object storage (misalnya Vercel Blob atau Cloudinary) dan ubah helper upload agar menyimpan di sana. Database dan email juga perlu layanan eksternal.

Jangan commit `.env`; file itu sudah dikecualikan oleh `.gitignore` dan `.vercelignore`. Jika kunci Midtrans dalam file `.env` yang pernah dibagikan adalah kunci aktif, segera rotate/regenerate di dashboard Midtrans.
