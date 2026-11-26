# MASTER PROMPT REVISI DAN PERBAIKAN PROYEK

## Website UMKM "Warung Maem Jadoel"

Lakukan pemeriksaan dan perbaikan menyeluruh pada tampilan desain
(UI/UX) serta sistem/fungsionalitas proyek. Sebelum mengubah kode,
pelajari struktur proyek, database, route, controller, model, view,
JavaScript, CSS, dan konfigurasi yang benar-benar tersedia.

## 1. Aturan Umum

-   Pertahankan pola MVC dan sistem yang sudah digunakan; jangan membuat
    fitur duplikat.
-   Pertahankan fungsi, relasi database, validasi, autentikasi, dan
    transaksi yang masih diperlukan.
-   Jangan mengarang nama file, tabel, kolom, atau route.
-   Hindari perubahan database destruktif. Jika perlu, buat
    perubahan/migrasi yang aman dan jelaskan dampaknya.
-   Perbaiki akar masalah, bukan hanya gejala tampilan.
-   Pastikan desain responsif untuk desktop, tablet, dan ponsel.
-   Setelah selesai, laporkan file yang diubah, perbaikan, hasil tes,
    dan hal yang belum dapat diuji.

## 2. Identitas dan Landing Page

Gunakan identitas secara konsisten di seluruh website: - Nama toko:
**Warung Maem Jadoel** - Slogan: **ANEKA JAJANAN DENGAN CITARASA YANG
AUTENTIK**

Perbaiki navigasi landing page agar setiap menu menuju section yang
benar, bukan selalu berhenti di Beranda. Menu yang relevan: Beranda,
Menu, Tentang Kami, FAQ, Ulasan, Lokasi, dan Voucher/promosi jika
tersedia. - Klik menu harus scroll/navigasi ke section yang sesuai. -
Perhitungkan tinggi navbar agar judul section tidak tertutup. -
Indikator menu aktif mengikuti section yang terlihat. - Pastikan
navigasi berjalan di desktop dan mobile. - Sistem Bonus sudah dihapus;
jangan menampilkan menu atau section Bonus. Jika bagian promosi
diperlukan, gunakan Voucher sesuai fitur yang benar-benar tersedia.

## 3. Navbar Customer

Pada navbar customer desktop, menu **Menu** belum memiliki indikator
aktif yang konsisten. Perbaiki indikator aktif agar jelas, mengikuti
halaman/section, dan konsisten dengan menu lainnya serta responsif.

## 4. Desain CRUD Admin

Gunakan halaman **Produk & Stok** sebagai referensi desain CRUD. -
Halaman utama menampilkan tabel/daftar data. - Tombol Tambah membuka
form melalui modal/panel. - Tombol Edit membuka form data terpilih. -
Form tidak terus-menerus tampil berdampingan dengan tabel. - Modal
memiliki overlay, ukuran proporsional, posisi baik, dan dapat ditutup. -
Pertahankan CRUD, validasi, pencarian, filter, pagination, dan proses
simpan yang sudah ada.

Terapkan pada modul yang masih digunakan: **Produk & Stok:** Resep,
Voucher, Kategori & Topping, Stok Produk, Stok Bahan Baku.
**Operasional:** Pengeluaran, Ongkir, Nomor Meja.

Pindahkan menu **Stok Produk** dan **Stok Bahan Baku** dari Operasional
ke Produk & Stok. Jangan membuat sistem stok duplikat.

## 5. Kategori & Topping

Perbaiki tombol/interaksi dan popup/modal yang tidak berfungsi: - Event
handler tambah, edit, hapus, dan aksi topping. - Selector JavaScript dan
ID modal yang mungkin bentrok. - Pembukaan/penutupan modal, overlay,
z-index, dan konflik CSS. - Pengisian data saat edit, validasi,
penyimpanan, dan pembaruan tampilan. Pastikan tombol menjalankan fungsi
sebenarnya, bukan sekadar terlihat dapat diklik.

## 6. Pembelian Stok dan Pengeluaran

Saat bahan baku ditambah melalui pembelian, biaya harus tercatat
otomatis pada **Pengeluaran**. - Catat tanggal, bahan baku, jumlah,
harga satuan, dan total sesuai struktur database. - Hubungkan transaksi
stok dan pengeluaran. - Cegah pencatatan ganda saat halaman dimuat ulang
atau request terkirim ulang. - Tangani pembatalan/koreksi secara
konsisten. - Penyesuaian stok tanpa pembelian/biaya tidak otomatis
menjadi pengeluaran. - Jangan mencatat nilai stok lama sebagai
pengeluaran setiap kali halaman dibuka.

## 7. Login dan Register

Perbaiki pergantian Login ↔ Daftar. Saat ini dari Daftar ke Masuk form
bisa bertumpuk atau tidak berganti. Periksa status form, event tombol,
display/visibility/opacity/z-index, modal/container bertumpuk, validasi,
dan submit. Pastikan hanya form terpilih yang tampil dan autentikasi
tetap berjalan.

## 8. Hapus Sistem Bonus, Pertahankan Voucher

Hapus fitur **Bonus** dari sistem website; **Voucher** menjadi
satu-satunya fitur promosi. - Hilangkan menu, tombol, form, dan alur
Bonus yang masih aktif pada sisi Admin dan pelanggan. - Periksa
ketergantungan Bonus pada controller, model, view, query, dan database
sebelum menonaktifkannya. - Jangan menghapus data historis Bonus secara
sembarangan jika masih dipakai laporan, riwayat, atau relasi. - Pastikan
Voucher tetap berfungsi. - Jangan menampilkan menu/section Bonus pada
landing page; gunakan Voucher/promosi bila sesuai. - Jangan mengubah
transaksi lama tanpa mekanisme aman.

## 9. Chat Tidak Boleh Duplikat

Pesan chat terkadang tampil dua kali pada Admin maupun pelanggan.
Periksa pengiriman dan polling: - Pesan baru tidak ditambahkan lagi oleh
polling. - ID pesan terakhir diperbarui dengan benar. - Gunakan
identitas pesan unik dan deduplikasi. - Cegah pengiriman berulang saat
tombol diklik berkali-kali. - Pesan muncul satu kali dan urutannya
benar. Perbaiki akar masalah, bukan hanya menyembunyikan duplikat di
tampilan.

## 10. Pesan Otomatis Status Pesanan

Ketika Admin menerima atau mengubah status pesanan, kirim pesan otomatis
ke chat pelanggan yang terkait. - Pesan berdasarkan pesanan dan status
aktual. - Contoh: "Pesanan #123 sedang diproses oleh Warung Maem
Jadoel." - Jangan mengirim pesan generik seperti "ya". - Kirim sekali
untuk setiap perubahan status valid; request berulang tidak boleh
membuat duplikat. - Chat manual tetap berfungsi.

## 11. Midtrans

Pembayaran Midtrans belum berjalan meskipun key sudah disediakan.
Periksa: - Nama dan pembacaan variabel `.env`. - Server Key dan Client
Key serta Sandbox/Production. - Pembuatan Snap token dan pemanggilan
Snap JS. - Request API, respons/error, notification/webhook. - Pemetaan
transaksi Midtrans ke pesanan. - Validasi nominal, order ID, status
transaksi, dan perubahan status pesanan.

Keamanan: - Jangan tampilkan Server Key di frontend atau hardcode
secret. - Jangan menganggap pembayaran sukses hanya karena tombol bayar
diklik atau pengguna kembali ke website. - Verifikasi status melalui
respons server/notification Midtrans yang valid. - Uji Sandbox jika
konfigurasi dan akses tersedia. - Pertahankan metode pembayaran lain
yang masih digunakan, termasuk tunai dan QRIS.

## 12. Nama Pelanggan

Di beberapa bagian Admin, nama pelanggan masih tampil sebagai kode/ID.
Periksa seluruh tampilan terkait: Dashboard, daftar/detail pesanan,
transaksi, chat, pengiriman, laporan keuangan, riwayat Voucher, ulasan,
serta export/cetak laporan. - Gunakan JOIN/relasi sesuai database aktual
untuk mengambil nama pelanggan. - ID tetap menjadi foreign key, tetapi
tampilan mengutamakan nama. - Jika nama tidak tersedia, gunakan fallback
seperti username atau label pelanggan. - Jangan tampilkan kode internal
sebagai nama jika nama sebenarnya tersedia.

## 13. Keuangan -- Pencatatan Kas

Perbaiki tombol **Pencatatan Kas** dan modalnya: - Gaya tombol konsisten
dengan tombol aksi pada Produk. - Modal berada di tengah layar, overlay
dan z-index benar. - Ukuran proporsional, label/input rapi, jarak antar
elemen baik. - Tombol Simpan/Batal jelas dan tampilan responsif.
Pastikan validasi, penyimpanan, dan perhitungan kas tetap berfungsi.

## 14. Standarisasi Kode

Rapikan kode secara profesional agar mudah dibaca dan dipelihara. -
Gunakan standar PHP konsisten, misalnya PSR-12 jika sesuai. - Format
HTML, CSS, dan JavaScript secara konsisten. - Utamakan susunan vertikal,
indentasi jelas, dan baris yang tidak terlalu panjang. - Hindari banyak
perintah dalam satu baris. - Jangan mengubah logika hanya demi
formatting. - Jangan menggunakan regex sederhana untuk memformat file
campuran PHP/HTML yang berisiko merusak struktur. - Gunakan formatter
sesuai bahasa/file jika tersedia. - Periksa diff dan validasi sintaks. -
Jangan menghapus komentar atau kode yang masih diperlukan.

## 15. Pengujian dan Laporan

Uji sesuai lingkungan yang tersedia: - Navigasi landing page dan
indikator aktif. - Login/register. - CRUD dan modal Kategori &
Topping. - Penambahan stok dan pencatatan pengeluaran. - Voucher. - Chat
dan deduplikasi. - Pesan otomatis perubahan status. - Midtrans Sandbox
jika memungkinkan. - Nama pelanggan, pencatatan kas, dan
responsivitas. - Sintaks PHP dan konsol JavaScript jika memungkinkan.

Laporan akhir harus menyebutkan masalah yang ditemukan, perbaikan, file
yang diubah, perubahan database/konfigurasi, tes yang berhasil, serta
tes yang belum dapat dilakukan beserta alasannya.

**Jangan menyatakan fitur telah berhasil diuji jika pengujian belum
benar-benar dilakukan.**
