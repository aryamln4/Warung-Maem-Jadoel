<?php
$title='Profil Pelanggan';
require BASE_PATH.'/view/partials/header.php';
?>
<main class="customer-simple-page profile-page-v10"><div class="customer-simple-shell profile-shell"><span class="customer-eyebrow">AKUN PELANGGAN</span><div class="profile-heading-row"><div><h1>Profil akunmu.</h1><p class="customer-lead">Kelola informasi akun dan keamanan kata sandi. Voucher promo dapat dipakai saat checkout pesanan.</p></div><div class="profile-user-chip"><span><?=strtoupper(substr((string)$user['nama'],
0,
1))?></span><div><strong><?=e($user['nama'])?></strong><small>Member Warung Maem Jadoel</small></div></div></div>
<?php if(!empty($errors)): ?><div class="alert alert-danger profile-alert"><ul class="mb-0"><?php foreach($errors as $x): ?><li><?=e($x)?></li><?php endforeach;
?></ul></div><?php endif;
?>
<div class="profile-v10-grid">
  <div class="profile-account-column">
    <section class="profile-form-card">
      <h2>Informasi profil</h2>
      <p>Perbarui informasi dasar yang digunakan pada akunmu.</p>
      <form method="post"><?=csrf_field()?>
        <div class="profile-fields">
          <label>Nama lengkap<input class="form-control" name="nama" value="<?=e($user['nama'])?>" required></label>
          <label>Username<input class="form-control" value="<?=e($user['username'])?>" disabled></label>
          <label>Email<input class="form-control" type="email" name="email" value="<?=e($user['email'])?>" required></label>
          <label>Nomor HP<input class="form-control" name="no_hp" value="<?=e($user['no_hp']??'')?>"></label>
        </div>
        <button class="landing-btn landing-btn-dark" type="submit">Simpan perubahan</button>
      </form>
    </section>

    <section class="profile-form-card profile-password-card">
      <div class="password-heading"><div><h2>Ganti kata sandi</h2><p>Gunakan minimal delapan karakter untuk menjaga keamanan akunmu.</p></div><span><?= icon('key') ?></span></div>
      <form method="post"><?=csrf_field()?>
        <div class="profile-fields"><label class="wide">Kata sandi baru<input class="form-control" type="password" name="password" minlength="8" placeholder="Minimal 8 karakter"></label></div>
        <button class="landing-btn landing-btn-dark" type="submit">Perbarui kata sandi</button>
      </form>
    </section>
  </div>

  <div class="profile-bonus-column">
    <section class="profile-form-card">
      <span class="customer-eyebrow">PROMO PELANGGAN</span>
      <h2>Voucher &amp; pesanan</h2>
      <p>Kode voucher yang sedang aktif bisa langsung dipakai di halaman checkout. Potongan dihitung otomatis sesuai syarat voucher.</p>
      <div class="d-flex flex-wrap gap-2">
        <a class="landing-btn landing-btn-dark" href="<?=APP_URL?>/index.php?r=order/menu">Pesan menu</a>
        <a class="landing-btn landing-btn-light" href="<?=APP_URL?>/index.php?r=order/history">Riwayat pesanan</a>
      </div>
    </section>
  </div>
</div></main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
