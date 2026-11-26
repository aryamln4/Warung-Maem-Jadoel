<?php $title='Lupa Sandi';
require BASE_PATH.'/view/partials/header.php';
?>
<main class="auth-page">
  <div class="auth-card panel-card">
    <span class="eyebrow">PEMULIHAN AKUN</span>
    <h2 class="section-title mb-3">Lupa Sandi</h2>
    <p class="page-subtitle mb-4">Masukkan email akunmu, lalu kami kirim tautan untuk membuat kata sandi baru.</p>
    <?php if(!empty($success)): ?><div class="alert alert-success"><?= icon('check-circle') ?> <?=e($success)?></div><?php endif;
?>
    <form method="post">
      <?php echo csrf_field();
?>
      <label class="form-label" for="forgotEmail">Email akun</label>
      <input id="forgotEmail" class="form-control mb-4" type="email" name="email" autocomplete="email" placeholder="nama@email.com" required>
      <button class="btn btn-dark w-100 touch"><?= icon('arrow-right') ?> Kirim Link Reset</button>
    </form>
  </div>
</main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
