# Revisi terbaru Warung Jadoel

## Perubahan
- Nilai harga bahan aktif dijumlahkan langsung per bahan; tidak dikalikan jumlah stok.
- Form resep memiliki kolom Bahan, Jumlah, Satuan (kg/gram/pieces), dan Catatan. Satuan resep disimpan pada kolom tambahan dan konversi kg/gram dilakukan saat sinkronisasi stok.
- Tab stok bahan baku: Stok Aktif, Riwayat, Kadaluarsa.
- Tabel admin menampilkan maksimal 10 baris per halaman dengan kontrol pagination.
- Pembelian bahan tetap dicatat satu kali sebagai pengeluaran di Keuangan (jumlah pembelian × harga per unit) melalui catatan pembelian yang sudah ada. Ringkasan harga master di kartu stok tidak ditambahkan lagi ke kas keluar untuk mencegah duplikasi biaya.
- Drawer sidebar admin mobile dapat ditutup lewat backdrop, tombol menu, atau memilih navigasi.
- Menu mobile landing tidak lagi menggandakan tombol Masuk/Daftar; tombol utama tetap di header.
- Penataan jarak dan form responsif mobile ditambahkan.

## Database
- Tidak ada tabel yang dihapus atau data lama yang diubah.
- Ada penambahan kolom nullable `resep_produk.satuan_resep`.
- Untuk database lama, kolom ditambahkan otomatis secara idempoten ketika admin membuka halaman Produk & Stok. Data resep lama tetap dipertahankan dan menggunakan satuan bahan baku yang ada.
- Impor ulang `database/database.sql` ke database hosting yang sudah berisi data tidak diperlukan dan tidak disarankan. Backup database terlebih dahulu sebelum mengganti file aplikasi.
