<?php $title='Daftar Akun — Warung Maem Jadoel';
require BASE_PATH.'/view/partials/header.php';
?>
<main class="combined-login-page register-page">
  <div class="login-photo-shade"></div>
  <section class="login-panel register-panel" aria-labelledby="registerTitle">
<div class="login-intro"><h1 id="registerTitle">Daftar Akun</h1></div>
    <?php if(!empty($errors)): ?><div class="register-errors" role="alert"><ul><?php foreach($errors as $x): ?><li><?=e($x)?></li><?php endforeach;
?></ul></div><?php endif;
?>
    <form method="post" class="login-form register-form">
      <?=csrf_field()?>
      <div class="register-fields">
        <div><label for="regUsername">Username</label><input id="regUsername" class="form-control" name="username" autocomplete="username" placeholder="Buat username" value="<?=e($_POST['username'] ?? '')?>" required></div>
        <div><label for="regNama">Nama</label><input id="regNama" class="form-control" name="nama" autocomplete="name" placeholder="Nama lengkap" value="<?=e($_POST['nama'] ?? '')?>" required></div>
        <div class="register-full"><label for="regEmail">Email</label><input id="regEmail" class="form-control" type="email" name="email" autocomplete="email" placeholder="nama@email.com" value="<?=e($_POST['email'] ?? '')?>" required></div>
        <div><label for="regPhone">No HP (opsional)</label><input id="regPhone" class="form-control" name="no_hp" autocomplete="tel" placeholder="Nomor HP" value="<?=e($_POST['no_hp'] ?? '')?>"></div>
        <div><label for="regPassword">Sandi</label><input id="regPassword" class="form-control" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" required></div>
        <div class="register-full"><label for="regConfirm">Konfirmasi Sandi</label><input id="regConfirm" class="form-control" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi" required></div>
      </div>
      <button type="submit" class="login-submit">Daftar</button>
    </form>
    <p class="login-register">Sudah punya akun? <a href="<?=APP_URL?>/index.php?r=auth/login">Masuk</a></p>
</section>
</main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
