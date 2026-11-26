<?php $title='Login Admin';
require BASE_PATH.'/view/partials/header.php';
?>
<main class="auth-page">
  <div class="auth-card panel-card">
    <span class="eyebrow">AREA ADMINISTRATOR</span>
    <h2 class="section-title mb-3">Login Admin</h2>
    <p class="page-subtitle mb-4">Masuk untuk mengelola pesanan, stok, dan laporan warung.</p>
    <?php if(!empty($error)): ?><div class="alert alert-danger"><?= icon('alert-circle') ?> <?=e($error)?></div><?php endif;
?>
    <form method="post">
      <?php echo csrf_field();
?>
      <label class="form-label" for="adminEmail">Email</label>
      <input id="adminEmail" class="form-control mb-3" type="email" name="email" autocomplete="username" placeholder="admin@maemjadoel.id" required>
      <label class="form-label" for="adminPassword">Sandi</label>
      <input id="adminPassword" class="form-control mb-4" type="password" name="password" autocomplete="current-password" placeholder="Kata sandi admin" required>
      <button class="btn btn-dark w-100 touch"><?= icon('log-out') ?> Masuk Admin</button>
    </form>
  </div>
</main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
