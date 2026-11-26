# Design Guide — Warung Maem Jadoel (Toko Online E-Commerce)

> Pegangan desain untuk Figma / aplikasi desain lain. Semua angka (warna, font, radius, ukuran)
> diambil dari CSS proyek yang berjalan (`foundation.css` + `app.css`) — mockup di Figma akan
> konsisten dengan kode. Prompt AI siap-tempel ada di bagian akhir.
>
> **Orientasi: E-COMMERCE DULU.** Alur utama pengalaman pengguna adalah berbelanja online:
> telusuri katalog → keranjang → checkout dengan alamat & ongkir → bayar QRIS → lacak
> pengiriman → chat dengan warung. Kasir (POS dine-in/takeaway) hanyalah **alat internal
> owner**, bukan wajah utama aplikasi.

---

## 1. Konteks & Kepribadian Brand

| Hal | Nilai |
|---|---|
| Nama | Warung Maem Jadoel — "Warung Kuliner Tradisional" |
| Produk | Makanan khas Sunda (Mie, Seblak, Japlak Karamel, Pangsit) |
| Model | **Toko online (primer)**: pesan dari web → diantar kurir (dipesan owner lewat aplikasi ojol) + kasir warung (sekunder, internal) |
| Fitur kunci e-commerce | Keranjang, alamat + titik lokasi, estimasi ongkir per jarak, QRIS Midtrans, konfirmasi ongkir owner, **chat pesanan**, status pengiriman, ulasan per produk, voucher |
| Nuansa visual | Nostalgia/toko jadul yang bersih & modern — hangat, kertas, coklat tua, bukan gelap-tech |
| Logo | Badge lingkaran vintage (ilustrasi warung + "MAKANAN KHAS SUNDA") |
| Audiens | Pembeli muda yang order online dari HP (utama) + owner/admin di dashboard (internal) |

**Rule visual utama:** tampilan terasa "kertas hangat" (paper) dengan aksen coklat; tombol utama
berwarna tinta gelap; tidak ada gradien warna-warni; ikon garis tipis (stroke 2px).

## 2. Design Tokens (siap disalin ke Figma Variables)

### Warna — Brand
| Token | Hex | Pemakaian |
|---|---|---|
| `ink` | `#2F2A26` | teks utama, tombol utama, sidebar teks |
| `ink-strong` | `#171411` | heading besar, footer gelap |
| `muted` | `#756D66` | teks sekunder, caption, placeholder |
| `paper` | `#FBFAF7` | **latar halaman utama** (bukan putih murni) |
| `surface` | `#F3EFE8` | latar sekunder, area foto produk, hover baris |
| `accent` | `#8A4F32` | aksen coklat (link, highlight, chip aktif) |
| `accent-dark` | `#603724` | aksen lebih tua (hover, overlay banner) |
| `line` | `#E5DDD3` | border kartu, garis pembatas, divider |
| `white` | `#FFFFFF` | latar kartu/panel |

### Warna — Status
| Token | Hex bg | Hex teks | Pemakaian |
|---|---|---|---|
| `success` | `#E9F0EA` | `#4C6250` | pembayaran berhasil, pesanan selesai, potongan voucher |
| `danger` | `#F9E9E6` | `#A33A2E` | gagal, stok habis, di luar jangkauan |
| `warning` | `#FAF3E2` | `#A3710F` | menunggu konfirmasi, menunggu pembayaran |
| aksen oranye | — | `#F2AD3E` | indikator slide aktif di banner (spesifik banner) |

### Hero banner (khusus landing/storefront)
- Latar foto makanan gelap `#21150D`, overlay gradien kiri→kanan `rgba(29,18,10,.95) → .08`.
- Papan stempel banner-3: coklat `#8A6039` + overlay `rgba(104,62,28,.58)`, stempel putih gading `#F5EAD6`, bintang emas `#8A5A08`.

### Tipografi — SATU pasangan font: **Inter**
| Gaya | Ukuran/line | Ket |
|---|---|---|
| Display hero | 68px / .94 (desktop), 54px (slide 3) | weight 800, tracking −5.5% |
| Judul halaman (page-title) | 2.2rem ≈ 35px | weight 750–800 |
| Judul section | 1.35–1.5rem | weight 700 |
| Body default | **15px / 1.6** | weight 400–450 |
| Teks kecil | 12–13px | muted |
| Label/eyebrow | 11px, UPPERCASE, tracking +5% | accent-dark |
| Harga/kelipatan | 15–18px | weight 700–800 |

### Radius, Bayangan, Spasi
| Token | Nilai |
|---|---|
| Radius kecil | 8px (input, chip, tombol kecil) |
| Radius sedang | 14px (kartu, panel) |
| Radius besar | 18px (panel besar, modal, banner) |
| Shadow | `0 10px 30px rgba(55,38,25,.06)` — sangat lembut, hangat |
| Shadow hover | `0 14px 34px rgba(55,38,25,.10)` + kartu naik 2–3px |
| Spasi | 4 / 8 / 16 / 24 / 48px; gutter konten 24px |
| Lebar konten | publik maks 1320px, admin maks 1440px |
| Motion | transisi 180–700ms ease; slide banner 3,5s; hormati reduced-motion |

## 3. NAVBAR E-COMMERCE (desain baru — jelas fiturnya)

### Navbar pelanggan yang sudah login (desktop)
```
[Logo badge] Warung Maem Jadoel     Beranda  Menu  Pesanan  Chat  Alamat     [ikon keranjang+badge] [avatar akun ▾]
```
| Elemen | Bentuk & perilaku |
|---|---|
| Beranda | link biasa (halaman storefront) |
| Menu | → katalog produk + keranjang (halaman inti) |
| Pesanan | → daftar pesanan saya + status tiap pesanan (dengan badge jumlah aktif) |
| Chat | → daftar ruang chat per pesanan (**badge titik/angka bila ada balasan belum dibaca**) |
| Alamat | → kelola buku alamat (tambah/pilih/utama) |
| Ikon keranjang | ikon tas belanja + **badge angka qty** (selalu terlihat — standar e-commerce); klik → keranjang |
| Avatar akun ▾ | dropdown: Profil, Pesanan Saya, Alamat, Ulasan Saya, Keluar |

Item nav aktif: latar `surface` + teks `ink` (pill). Hover: latar surface transparan.

### Navbar tamu (belum login)
`Beranda | Menu | Ulasan | Lokasi` + tombol `Masuk` & `Daftar` — tanpa ikon e-commerce
(keranjang/chat muncul setelah login; klik keranjang di menu mengarah ke daftar).

### Bottom navigation (MOBILE — standar aplikasi e-commerce)
Bar bawah tetap 5 tab, tinggi 60px, ikon + label 10px:
```
[Beranda]  [Menu]  [Pesanan]  [Chat•]  [Akun]
```
- Ikon keranjang **mengambang (FAB)** di kanan atas bottom-nav, dengan badge qty — selalu bisa
  buka keranjang dari halaman mana pun.
- Chat punya titik merah kecil bila ada pesan belum dibaca.
- Header mobile menyusut jadi: logo + ikon keranjang (badge) saja.

### Sidebar admin (internal — dijaga sederhana)
`Dashboard | Pesanan | Chat | Produk | Voucher | Stok & Meja | Pelanggan & Ulasan | Keuangan | Operasional`
— "Pesanan" memuat antrean konfirmasi ongkir & status pengiriman; "Chat" memuat semua ruang
chat aktif dengan badge belum dibaca.

## 4. Komponen Inti (buatkan sebagai Components di Figma)

1. **Tombol** — 4 varian: `primary` (tinta gelap #2F2A26, teks putih), `secondary-soft` (putih, border `line`), `success`, `danger`; ukuran md 40px / sm 34px; radius 8–10px; ikon 16px + gap 8px.
2. **Kartu produk** — foto 185px atas, nama bold, deskripsi 1–2 baris muted, **rating bintang + jumlah ulasan**, harga bold, tombol "+". Hover: naik + shadow hover. Badge "Habis" & chip topping.
3. **Stat card (admin)** — label eyebrow, angka besar bold, catatan muted; 3–4 per baris.
4. **Badge/chip status** — pill 999px soft-color; payment pill "SUDAH TERBAYAR" (success) / "MENUNGGU PEMBAYARAN" (warning); stepper delivery: DIKEMAS → DIKIRIM → TIBA.
5. **Alert** — 3 warna status, radius 14, ikon kiri.
6. **Navbar pelanggan** — sesuai §3 (desktop + bottom-nav mobile).
7. **Admin shell** — sidebar putih 258px (ikon 17px, chip 32px), topbar (tanggal, admin, Keluar), konten `#F5F2ED`.
8. **Form** — input 40px, radius 8, border `line`, focus border `accent`; label 13px semibold.
9. **Tabel admin** — header surface, hover surface, aksi = tombol ikon.
10. **Komponen e-commerce khas** — kartu alamat (pin mini), panel estimasi ongkir, kartu "menunggu konfirmasi ongkir", ruang chat, kolom voucher, keranjang drawer/FAB.

## 5. Inventaris Halaman — urut alur e-commerce

### A. Alur pelanggan (UTAMA — desain paling detail)
| # | Halaman | Isi khas |
|---|---|---|
| 1 | **Storefront (landing)** | hero banner 3 slide (3,5s) dengan CTA "Pesan Sekarang", kategori unggulan, kartu menu populer (dengan rating), promo/voucher berjalan, ulasan marquee, lokasi, footer |
| 2 | **Katalog menu** | chip kategori, pencarian, kartu produk + rating, keranjang samping/panel bawah |
| 3 | **Detail produk** (opsional) | galeri foto, deskripsi, pilih topping & level, rating + ulasan per produk, tombol "Tambah ke Keranjang" |
| 4 | **Keranjang** | item + qty stepper, ringkasan harga, lanjut checkout |
| 5 | **Checkout** | pilih metode: **Delivery** (pilih/ketik alamat + pin lokasi → estimasi ongkir: jarak km + rincian 10rb+2rb/500m; >10 km = "di luar jangkauan") / Dine-in / Takeaway; kolom voucher; ringkasan total |
| 6 | **Pembayaran QRIS** | QR + tombol simpan gambar + status pill; menunggu konfirmasi ongkir owner (kartu kuning) bila delivery |
| 7 | **Lacak pesanan** | timeline status + stepper pengiriman DIKEMAS→DIKIRIM→TIBA + link lacak kurir |
| 8 | **Chat pesanan** | ruang chat per pesanan, gelembung kiri/kanan, badge belum baca |
| 9 | **Pesanan saya / riwayat** | daftar semua pesanan + status + tombol "Pesan Lagi" |
| 10 | **Alamat saya** | kartu alamat (utama), form dengan peta/geolocation |
| 11 | **Ulasan & wishlist** | ulas per item (bukti beli), produk tersimpan |
| 12 | **Auth** | masuk/daftar (modal di storefront) |

### B. Alur owner/admin (internal)
| # | Halaman | Isi khas |
|---|---|---|
| 1 | Dashboard | stat + grafik + papan singkat |
| 2 | **Pesanan** | antrean + **kartu kuning "Perlu Konfirmasi Ongkir"** (jarak, alamat, nominal + Setujui/ubah) + status pengiriman + tempel link lacak |
| 3 | **Chat** | semua ruang chat aktif, badge belum dibaca |
| 4 | Produk & Voucher | kelola katalog (galeri multi-foto) + kelola voucher (NOMINAL/PERSEN/ONGKIR) |
| 5 | Stok, meja, pelanggan, keuangan (panel Midtrans), operasional | seperti sekarang |

### C. Kasir warung (POS — sekunder, internal)
Dine-in/takeaway via kasir: antrean tunai, konfirmasi uang. Di desain: cukup 1 halaman ringkas;
bukan fokus mockup.

## 6. Wireframe per Breakpoint

| Frame | Lebar | Keterangan |
|---|---|---|
| Desktop | 1440×auto | konten maks 1320px, sidebar admin 258px |
| Tablet | 820×auto | keranjang jadi panel bawah, kolom admin 1 kolom |
| Mobile | 390×auto | **bottom-nav 5 tab + FAB keranjang**, kartu 1 kolom, QRIS full-width, chat full-screen |

## 7. Do & Don't

- ✅ Latar `paper` hangat, kartu putih, garis tipis `line` — bukan dark mode, bukan neon.
- ✅ Harga & angka penting selalu **bold**; label pakai eyebrow uppercase.
- ✅ Ikon keranjang & chat **selalu terjangkau satu ketukan** (navbar desktop / bottom-nav mobile).
- ❌ Jangan gradien ungu-biru, glassmorphism berlebihan, atau font selain Inter.
- ❌ Jangan status pakai warna jenuh — selalu versi soft (bg pastel + teks gelap).
- ❌ Jangan tampilkan alur kasir (POS) di navbar pelanggan — itu internal.

---

## 8. PROMPT SIAP-TEMPEL (Figma AI / Figma Make / v0 / dsb.)

### Prompt A — Design system (buat dulu)
> Buatkan design system untuk **website toko online makanan** Indonesia bernama "Warung
> Maem Jadoel" (makanan khas Sunda, nuansa nostalgia-jadul yang bersih dan hangat). Token:
> latar halaman krem hangat #FBFAF7, kartu putih #FFFFFF, teks utama #2F2A26, teks muted
> #756D66, aksen coklat #8A4F32, border #E5DDD3, status: success #E9F0EA/#4C6250,
> danger #F9E9E6/#A33A2E, warning #FAF3E2/#A3710F. Font tunggal Inter; body 15px/1.6;
> heading weight 700–800; label kecil uppercase 11px tracking 5%. Radius 8/14/18px, bayangan
> sangat lembut `0 10px 30px rgba(55,38,25,.06)`. Komponen yang WAJIB ada: navbar dengan ikon
> keranjang berbadge & ikon chat berbadge, bottom navigation mobile 5 tab (Beranda, Menu,
> Pesanan, Chat, Akun) + FAB keranjang mengambang, tombol primary/secondary/success/danger,
> kartu produk dengan rating bintang & tombol +, kartu alamat dengan pin, panel estimasi
> ongkir, kartu "menunggu konfirmasi ongkir", ruang chat gelembung, stepper pengiriman
> (DIKEMAS→DIKIRIM→TIBA), kolom voucher, stat card admin, tabel admin, alert. Ikon garis
> tipis (stroke 2px). Latar jangan putih murni — selalu krem hangat.

### Prompt B — Storefront/landing (desktop 1440 + mobile 390)
> Landing page toko online warung makan "Warung Maem Jadoel" (khas Sunda, nostalgia jadul
> modern). Navbar desktop: logo badge vintage kiri; menu: Beranda, Menu, Pesanan, Chat
> (dengan titik notifikasi), Alamat; ikon tas belanja dengan badge angka; avatar akun
> dropdown. Hero slider 3 slide (auto 3,5s) berlatar foto makanan gelap #21150D gradien
> kiri: chip tag putih, judul Inter 68px w800 putih maks 3 baris, paragraf pendek, CTA
> utama "Pesan Sekarang" (putih) + sekunder; indikator bawah tengah: pil gelap blur
> berisi 3 garis 34×5px, aktif oranye #F2AD3E dengan progres berjalan. Slide ke-3 punya
> papan stempel miring -1° coklat #8A6039 dengan 15 lingkaran bintang (3 emas terisi).
> Di bawah: kategori unggulan (chip ikon), grid kartu menu populer (foto, nama bold,
> rating bintang, harga bold, tombol +), strip promo voucher berjalan, ulasan marquee
> horizontal, lokasi + peta, FAQ, footer gelap #171411. Versi mobile 390: header ringkas
> (logo + ikon keranjang berbadge), hero lebih pendek, bottom navigation 5 tab
> (Beranda, Menu, Pesanan, Chat, Akun) dengan FAB keranjang mengambang.

### Prompt C — Katalog + keranjang (desktop + mobile)
> Halaman "Menu" toko online warung makan. Desktop: kiri kolom chip kategori + pencarian +
> grid kartu produk (foto 185px, nama bold, rating bintang + jumlah ulasan, harga bold,
> tombol "+"); kanan sticky panel keranjang putih: daftar item (nama, qty stepper, harga),
> subtotal, tombol hitam "Lanjut ke Checkout". Mobile 390: grid 2 kolom kartu lebih kecil,
> keranjang menjadi FAB mengambang dengan badge yang membuka panel bawah (bottom sheet)
> berisi item + tombol "Lanjut ke Checkout". Bottom navigation 5 tab tetap terlihat.
> Token warna: latar #FBFAF7, kartu putih radius 14, border #E5DDD3, aksen #8A4F32.

### Prompt D — Checkout delivery + QRIS + lacak (mobile-first)
> Halaman checkout toko online warung makan (mobile 390, juga versi desktop):
> (1) Pilih metode: kartu radio Delivery / Dine-in / Takeaway. (2) Untuk Delivery: kartu
> alamat tersimpan (label, penerima, alamat, pin) + tombol "+ gunakan lokasi saya (peta)";
> panel estimasi ongkir menampilkan jarak km dan rincian: Dasar Rp 10.000 (≤1 km) +
> Rp 2.000 per 500 m, total bold; jika > 10 km tampil state danger "di luar jangkauan
> pengiriman (maks 10 km)" dengan ilustrasi motor. (3) Kolom "Punya kode voucher?" dengan
> ringkasan potongan hijau. (4) Ringkasan total: subtotal + ongkir − potongan. (5) Kartu
> "Bayar dengan QRIS": QR putih rata tengah, tombol "Simpan Gambar QRIS", pill kuning
> "MENUNGGU PEMBAYARAN" berubah hijau "SUDAH TERBAYAR"; jika ongkir belum dikonfirmasi
> owner, tampil kartu kuning "Menunggu konfirmasi ongkir dari warung" (QR menyusul).
> (6) Halaman lacak: stepper pesanan (Diterima → Diproses → DIKEMAS → DIKIRIM → TIBA) +
> link lacak kurir + tombol "Chat Warung" membuka ruang chat gelembung kiri/kanan dengan
> input bawah. Semua warna token hangat (krem #FBFAF7, aksen coklat #8A4F32, status soft).

### Prompt E — Dashboard & antrean admin (desktop 1440)
> Dashboard admin toko online warung makan: sidebar putih tetap 258px (logo, menu: Dashboard,
> Pesanan, Chat, Produk, Voucher, Stok & Meja, Pelanggan & Ulasan, Keuangan, Operasional;
> item ikon garis 17px dalam chip 32px, aktif latar krem + border kiri coklat), topbar
> tanggal + admin + tombol Keluar. Konten latar #F5F2ED: 4 stat card (label uppercase,
> angka besar bold, catatan muted), kartu grafik, lalu antrean pesanan: kartu pesanan
> (nomor, pelanggan, item, total bold, chip QRIS/TUNAI) termasuk **kartu kuning "Perlu
> Konfirmasi Ongkir"** berisi jarak km, alamat, nominal estimasi + tombol Setujui + input
> ubah nominal + tombol Chat; kartu delivery menampilkan stepper DIKEMAS → DIKIRIM → TIBA
> dan kolom tempel link lacak kurir. Sertakan halaman/panel Chat: daftar ruang chat kiri
> (badge belum dibaca) + percakapan gelembung kanan.

### Prompt F — Kelola produk, voucher, keuangan (desktop)
> Halaman admin toko online warung makan (sidebar 258px sama): (1) Kelola Produk: tabel +
> form modal (nama, kategori, harga, stok, **galeri multi-foto**, topping) + kolom rating; 
> (2) Kelola Voucher: tabel (kode, tipe NOMINAL/PERSEN/ONGKIR, nilai, min belanja, maks
> potongan, kuota, kuota per pelanggan, masa aktif, status) + form modal + laporan pemakaian;
> (3) Keuangan: kartu "Midtrans QRIS" dengan badge status (Provider/Mode/Server Key/cURL)
> dan Webhook URL monospace + ringkasan pendapatan. Token warna hangat: latar #F5F2ED,
> kartu putih radius 14–18, aksen coklat #8A4F32, status soft pastel, tanpa warna jenuh.

---

## 9. Cara pakai di Figma (alur yang disarankan)

1. Buat **Figma Variables** dari tabel §2 (Collection "Brand" + "Status").
2. Jalankan **Prompt A** → jadikan halaman "Design System" (komponen + styles).
3. Jalankan **Prompt B–F** satu per satu → rapikan di frame 1440, duplikat ke 390 dan sesuaikan
   (bottom-nav, FAB keranjang, bottom-sheet).
4. Nama frame mengikuti alur belanja: `01-storefront`, `02-katalog`, `03-produk`, `04-keranjang`,
   `05-checkout-delivery`, `06-qris`, `07-lacak`, `08-chat`, `09-pesanan-saya`, `10-alamat`,
   `11-ulasan`, lalu `A1-admin-dashboard`, `A2-admin-orders`, `A3-admin-chat`, `A4-admin-produk`,
   `A5-admin-voucher`, `A6-admin-keuangan`.
5. Semua angka di dokumen ini sudah dipakai kode aslinya — kalau mockup dan website
   terlihat beda, anggap **dokumen ini yang benar**.
