<?php $title='Warung Maem Jadoel';
require BASE_PATH.'/view/partials/header.php';
$menuModel=new MenuModel();
$featured=$menuModel->featured();
$publicReviews=(new ReviewController())->publicList();
$promoVouchers=(new VoucherModel())->activeList(3);
$voucherValue=static function(array $v): string {
    if($v['tipe']==='PERSEN') return 'Potongan '.(float)$v['nilai'].'%';
    if($v['tipe']==='ONGKIR') return (float)$v['nilai']>0 ? 'Ongkir '.rupiah($v['nilai']) : 'Gratis ongkir';
    return 'Potongan '.rupiah($v['nilai']);
};
?>
<main class="landing-v10">
  <section class="landing-hero" id="beranda" aria-label="Banner utama Warung Maem Jadoel">
    <div class="landing-hero-inner">
      <div class="landing-hero-slides">
        <article class="landing-hero-slide is-active" data-slide="0">
          <div class="landing-hero-copy">
            <span class="landing-chip">Warung kuliner tradisional · Dine-in, Takeaway &amp; Delivery</span>
            <h1>Rasa jadoel,<br>dibikin nagih.</h1>
            <p>Pangsit, mie, seblak, dan japlak karamel ala warung tradisional — pesan dari website, pilih makan di tempat, ambil sendiri, atau minta diantar ke alamatmu.</p>
            <div class="landing-hero-actions"><a class="landing-btn landing-btn-dark" href="<?=APP_URL?>/index.php?r=order/menu">Pesan Sekarang</a><a class="landing-btn landing-btn-light" href="#tentang">Cerita kami</a></div>
          </div>
        </article>

        <article class="landing-hero-slide" data-slide="1">
          <div class="landing-hero-copy">
            <span class="landing-chip">Hemat untuk momen makan bersama</span>
            <h1>Makin Rame<br>Pesannya, Makin<br>Hemat Makannya.</h1>
            <p>Gunakan kode voucher saat checkout — potongan nominal, persen, sampai gratis ongkir untuk pesanan delivery.</p>
            <div class="landing-hero-actions"><a class="landing-btn landing-btn-dark" href="#menu">Lihat Menu</a><button class="landing-btn landing-btn-light" type="button" data-modal-open="#registerModal">Daftar Akun</button></div>
          </div>
        </article>

        <article class="landing-hero-slide" data-slide="2">
          <div class="landing-hero-copy">
            <span class="landing-chip">Promo pelanggan</span>
            <h1>Voucher Aktif<br>Bisa Langsung<br>Kamu Pakai.</h1>
            <p>Kode promo yang sedang berlaku tampil di halaman ini. Masukkan kodenya saat checkout sebelum membayar.</p>
            <div class="landing-hero-actions"><a class="landing-btn landing-btn-dark" href="#voucher">Lihat Voucher</a><button class="landing-btn landing-btn-light" type="button" data-modal-open="#registerModal">Daftar Akun</button></div>
          </div>
          <div class="landing-stamp-board" aria-hidden="true">
            <div class="landing-stamp-title">Kode<br>Promo</div>
            <div class="landing-stamps">
              <?php if($promoVouchers): foreach($promoVouchers as $pv): ?><span class="is-filled" style="width:auto;padding:0 10px;font-size:.72rem;font-weight:700"><?=e($pv['kode'])?></span><?php endforeach;
else: ?><span class="is-empty">MAEM</span><span class="is-filled">HEMAT</span><span class="is-empty">QRIS</span><?php endif;
?>
            </div>
          </div>
        </article>

        <div class="landing-dots" aria-label="Pilih banner">
          <button type="button" class="active" data-slide-to="0" aria-label="Banner 1"></button>
          <button type="button" data-slide-to="1" aria-label="Banner 2"></button>
          <button type="button" data-slide-to="2" aria-label="Banner 3"></button>
        </div>
      </div>
    </div>
    <div class="landing-stat-strip" aria-label="Keunggulan Warung Maem Jadoel">
      <div><strong>Sejak 2019</strong><span>Resep keluarga</span></div>
      <div><strong>4,9 / 5</strong><span>Rating pelanggan</span></div>
      <div><strong>Maks. 10 km</strong><span>Jangkauan antar</span></div>
      <div><strong>10.00–22.00</strong><span>Buka setiap hari</span></div>
    </div>
  </section>

  <section id="voucher" class="landing-section landing-promo">
    <div class="landing-narrow landing-center-head"><span class="landing-eyebrow">VOUCHER & PROMO</span><h2>Hemat lebih banyak setiap kali pesan.</h2><p>Voucher berlaku sesuai kuota dan masa aktif. Masukkan kode promo di halaman checkout sebelum menyelesaikan pembayaran.</p></div>
    <div class="landing-narrow landing-promo-grid">
      <article class="landing-promo-card"><span>01</span><h3>Pilih menu favoritmu</h3><p>Lihat menu terbaru, promo, dan status ketersediaan langsung dari ponselmu.</p></article>
      <article class="landing-promo-card"><span>02</span><h3>Masukkan kode voucher</h3><p>Buka checkout, tempel kode promo, lalu sistem menghitung potongan secara otomatis.</p></article>
      <article class="landing-promo-card"><span>03</span><h3>Bayar lebih hemat</h3><p>Potongan nominal, persen, atau gratis ongkir langsung mengurangi total pesananmu.</p></article>
    </div>
    <?php if($promoVouchers): ?>
    <div class="landing-narrow landing-voucher-grid">
      <?php foreach($promoVouchers as $pv): ?>
        <article class="landing-voucher-card">
          <span class="landing-voucher-type"><?=e($pv['tipe']==='ONGKIR'?'GRATIS ONGKIR':($pv['tipe']==='PERSEN'?'DISKON PERSEN':'POTONGAN NOMINAL'))?></span>
          <strong><?=e($pv['kode'])?></strong>
          <p><?=e($pv['deskripsi'] ?: $voucherValue($pv))?></p>
          <small>
            <?=e($voucherValue($pv))?>
            <?php if((float)$pv['min_belanja']>0): ?> · min. <?=rupiah($pv['min_belanja'])?><?php endif;
?>
            <?php if(!empty($pv['tanggal_selesai'])): ?> · s/d <?=e(date('d M Y',strtotime($pv['tanggal_selesai'])))?><?php endif;
?>
          </small>
        </article>
      <?php endforeach;
?>
    </div>
    <?php else: ?>
      <div class="landing-narrow landing-voucher-empty">Belum ada voucher aktif saat ini. Pantau halaman ini untuk promo berikutnya.</div>
    <?php endif;
?>
    <div class="landing-narrow landing-bonus-row"><div><strong>Voucher Nominal</strong><span>Potongan langsung sebesar nominal yang tertera, dengan minimal belanja sesuai syarat voucher.</span></div><div><strong>Voucher Persen &amp; Ongkir</strong><span>Potongan persen mengikuti batas maksimal, sedangkan voucher ongkir memotong biaya antar untuk pesanan delivery.</span></div></div>
  </section>

  <section id="menu" class="landing-section landing-menu-section">
    <div class="landing-narrow"><div class="landing-section-head"><div><span class="landing-eyebrow">MENU PILIHAN</span><h2>Menu favorit pelanggan.</h2><p>Menu yang paling sering dipilih untuk menemani waktu makanmu.</p></div><a class="landing-outline-btn" href="<?=APP_URL?>/index.php?r=order/menu">Lihat semua menu</a></div>
      <div class="landing-menu-grid">
      <?php foreach(array_slice($featured,
0,
3) as $m): ?>
        <?php $menuToppings=$menuModel->toppingsFor((int)$m['id']);
$stok=(int)($m['stok']??0);
?>
        <article class="landing-menu-card">
          <div class="landing-menu-photo">
            <?php if(!empty($m['foto'])): ?><img src="<?=APP_URL?>/<?=e($m['foto'])?>" alt="<?=e($m['nama_produk'])?>"><?php else: ?><span><?=e($m['kategori'])?></span><?php endif;
?>
          </div>
          <div class="landing-menu-info">
            <span><?=e($m['kategori'])?></span>
            <h3><?=e($m['nama_produk'])?></h3>
            <p><?=e($m['deskripsi'] ?: 'Menu favorit Warung Maem Jadoel dengan rasa khas dan pilihan sesuai selera.')?></p>
            <div class="landing-menu-meta"><strong>Rp <?=number_format($m['harga'],
0,
',',
'.')?></strong><small class="<?= $stok>0 ? 'is-available' : 'is-empty' ?>"><?= $stok>0 ? 'Tersedia' : 'Habis' ?></small></div>
            <button type="button" class="landing-menu-order-btn" data-modal-open="#landingProductModal<?= (int)$m['id'] ?>">Lihat detail &amp; pesan <?= icon('arrow-right') ?></button>
          </div>
        </article>

        <div class="modal fade landing-product-modal" id="landingProductModal<?= (int)$m['id'] ?>" tabindex="-1" aria-labelledby="landingProductTitle<?= (int)$m['id'] ?>" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
            <button type="button" class="landing-product-close" data-modal-close aria-label="Tutup"><?= icon('x') ?></button>
            <div class="landing-product-detail">
              <div class="landing-product-detail-photo">
                <?php if(!empty($m['foto'])): ?><img src="<?=APP_URL?>/<?=e($m['foto'])?>" alt="<?=e($m['nama_produk'])?>"><?php else: ?><span><?=e($m['kategori'])?></span><?php endif;
?>
              </div>
              <div class="landing-product-detail-copy">
                <span class="landing-eyebrow"><?=e($m['kategori'])?></span>
                <h2 id="landingProductTitle<?= (int)$m['id'] ?>"><?=e($m['nama_produk'])?></h2>
                <p class="landing-product-detail-desc"><?=e($m['deskripsi'] ?: 'Menu favorit Warung Maem Jadoel dengan rasa khas dan pilihan sesuai selera.')?></p>
                <div class="landing-product-price">Rp <?=number_format($m['harga'],
0,
',',
'.')?></div>
                <div class="landing-product-stock <?= $stok>0 ? 'is-available' : 'is-empty' ?>"><?= $stok>0 ? 'Stok tersedia' : 'Stok habis' ?></div>

                <?php if((int)$m['punya_level_pedas']===1): ?>
                  <div class="landing-product-option"><label>Level Seuhah</label><div class="landing-level-grid"><?php for($level=0;
$level<=5;
$level++): ?><label><input type="radio" name="landing_level_<?= (int)$m['id'] ?>" value="<?= $level ?>" <?= $level===0?'checked':'' ?>><span><?= $level ?></span></label><?php endfor;
?></div></div>
                <?php endif;
?>

                <?php if((int)$m['punya_topping']===1 && $menuToppings): ?>
                  <div class="landing-product-option"><label for="landingTopping<?= (int)$m['id'] ?>">Topping</label><select id="landingTopping<?= (int)$m['id'] ?>" class="landing-product-select" data-base-price="<?=e((string)$m['harga'])?>"><option value="0" data-extra="0">Tanpa topping</option><?php foreach($menuToppings as $t): ?><option value="<?= (int)$t['id'] ?>" data-extra="<?=e((string)$t['harga_tambahan'])?>"><?=e($t['nama'])?> + Rp <?=number_format($t['harga_tambahan'],
0,
',',
'.')?></option><?php endforeach;
?></select></div>
                <?php endif;
?>

                <div class="landing-product-option"><label for="landingQty<?= (int)$m['id'] ?>">Jumlah</label><div class="landing-qty-control"><button type="button" data-qty-minus aria-label="Kurangi jumlah"><?= icon('minus',
['class' => 'qty-ico']) ?></button><input id="landingQty<?= (int)$m['id'] ?>" type="number" min="1" max="<?=max(1,
$stok)?>" value="1"><button type="button" data-qty-plus aria-label="Tambah jumlah"><?= icon('plus',
['class' => 'qty-ico']) ?></button></div></div>

                <button type="button" class="landing-product-cart-btn" <?= $stok<1 ? 'disabled' : '' ?> data-landing-login-after>Tambah ke keranjang <?= icon('arrow-right') ?></button>
                <small class="landing-product-login-note">Kamu perlu masuk terlebih dahulu sebelum menambahkan menu ke keranjang.</small>
              </div>
            </div>
          </div></div>
        </div>
      <?php endforeach;
?>
      </div>
    </div>
  </section>

  <section id="tentang" class="landing-section landing-about">
    <div class="landing-narrow landing-about-grid"><div class="landing-about-photo"><img src="<?=APP_URL?>/assets/images/banner.jpeg" alt="Suasana Warung Maem Jadoel"></div><div class="landing-about-copy"><span class="landing-eyebrow">TENTANG KAMI</span><h2>Menghadirkan rasa rumahan di era yang lebih modern.</h2><p>“Maem Jadoel” membawa kesan warung rumahan yang sederhana: menu familiar, porsi bersahabat, dan rasa yang mengingatkan suasana makan dulu.</p><div class="landing-about-stats"><div><strong>100%</strong><span>Rasa rumahan</span></div><div><strong>15 menit</strong><span>Estimasi penyajian</span></div><div><strong>4,9</strong><span>Rating pelanggan</span></div></div><a href="#faq" class="landing-btn landing-btn-dark">Kenali cara pesan</a></div></div>
  </section>

  <section id="faq" class="landing-section landing-faq"><div class="landing-narrow landing-faq-grid"><div><span class="landing-eyebrow">FAQ</span><h2>Yang sering ditanyakan.</h2><p>Hal penting sebelum kamu pesan di Warung Maem Jadoel.</p></div><div class="landing-faq-list"><?php $faqs=[['Apakah saya wajib login untuk memesan?',
'Ya, wajib. Login diperlukan supaya pesanan, riwayat, dan penggunaan voucher tercatat dengan benar ke akunmu — tidak ada mode pesan sebagai tamu.'],
['Bagaimana cara memesan?',
'Login atau daftar akun, pilih menu, lalu saat checkout pilih Dine-in dan nomor meja yang kamu tempati, atau pilih Takeaway.'],
['Metode pembayaran apa saja?',
'Tersedia QRIS self-payment dan tunai. Untuk QRIS, pembayaran dikonfirmasi melalui alur checkout aplikasi.'],
['Bagaimana cara memakai voucher?',
'Masukkan kode voucher pada halaman checkout sebelum membayar. Potongan hanya berlaku jika syarat minimal belanja, kuota, dan masa aktif voucher terpenuhi.'],
['Kapan bisa memberi ulasan?',
'Setelah pesanan berstatus Selesai, buka Riwayat Pesanan lalu pilih Beri Ulasan. Satu transaksi hanya dapat memiliki satu ulasan.'],
['Apakah ulasan bisa diedit?',
'Bisa, selama 7 hari sejak pertama dikirim. Setelah itu ulasan tidak dapat diubah.'],
['Mengapa ulasan belum tampil?',
'Ulasan diperiksa admin terlebih dahulu. Hanya ulasan yang ditampilkan admin yang muncul di website.']];
foreach($faqs as $i=>$f): ?><details><summary><?=e($f[0])?><?= icon('plus') ?></summary><p><?=e($f[1])?></p></details><?php endforeach;
?></div></div></section>

  <section id="ulasan-pelanggan" class="landing-section landing-reviews"><div class="landing-narrow"><div class="landing-center-head"><span class="landing-eyebrow">ULASAN PELANGGAN</span><h2>Rasa yang membuat mereka kembali.</h2><p>Ulasan asli dari pelanggan yang telah menyelesaikan pesanan.</p></div><?php $reviewItems=array_slice($publicReviews,
0,
8);
if(!$reviewItems): ?><div class="landing-empty-review">Belum ada ulasan pelanggan yang ditampilkan.</div><?php else: ?><div class="landing-review-carousel" data-review-carousel><div class="landing-review-viewport"><div class="landing-review-grid landing-review-track"><?php foreach($reviewItems as $r): ?><article><div class="landing-stars" aria-label="Rating <?= (int)$r['rating'] ?> dari 5"><?=str_repeat(icon('star'),
(int)$r['rating'])?><?=str_repeat(icon('star',
['class'=>'is-empty']),
5-(int)$r['rating'])?></div><p>“<?=nl2br(e($r['komentar']?:'Pelanggan memberikan rating tanpa komentar.'))?>”</p><strong><?=e($r['nama'])?></strong><small><?=date('d M Y',
strtotime($r['created_at']))?></small></article><?php endforeach;
?></div></div></div><?php endif;
?></div></section>

  <section id="lokasi" class="landing-section landing-location"><div class="landing-narrow landing-location-card"><div class="landing-location-copy"><span class="landing-eyebrow">LOKASI & JAM BUKA</span><h2>Datang, duduk, lalu pilih meja.</h2><p>Alamat lengkap dan jam operasional dapat disesuaikan dengan lokasi usaha sebenarnya.</p><div class="landing-location-list"><span><?= icon('map-pin') ?> <b>Depok, Jawa Barat</b></span><span><?= icon('clock') ?> <b>10.00–22.00 setiap hari</b></span><span><?= icon('utensils') ?> <b>Dine-in & Takeaway</b></span></div><a class="landing-outline-btn" href="https://www.google.com/maps/search/?api=1&amp;query=-6.402484,106.794243" target="_blank" rel="noopener">Buka di Google Maps</a></div><div class="landing-map"><iframe src="https://www.google.com/maps?q=-6.402484,106.794243&amp;z=16&amp;output=embed" loading="lazy" title="Peta lokasi Warung Maem Jadoel" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div></div></section>
</main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
