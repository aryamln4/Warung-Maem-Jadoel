<?php
$title = $title ?? 'Warung Maem Jadoel';
$route = $_GET['r'] ?? 'landing';
$isAdminArea = str_starts_with($route,
'admin/');
$isCustomerArea = str_starts_with($route,
'order/') || str_starts_with($route,
'review/') || str_starts_with($route,
'customer/') || $route === 'chat' || str_starts_with($route,
'chat/') || $route === 'auth/profile';
$isAuthPage = str_starts_with($route,
'auth/');
$customerHeader = null;
if (!empty($_SESSION['customer_id']) && class_exists('AuthModel')) {
    $customerHeader = (new AuthModel())->findCustomer((int)$_SESSION['customer_id']);
}
$customerName = trim((string)($customerHeader['nama'] ?? $customerHeader['username'] ?? 'Pelanggan'));
$customerFirstName = trim(explode(' ',
$customerName)[0] ?? $customerName);
$customerInitial = strtoupper(substr($customerFirstName ?: 'P',
0,
1));
$customerProgress = (int)($customerHeader['total_produk_kumulatif'] ?? 0);
$cartCount = 0;
$chatUnread = 0;
$adminChatUnread = 0;
if (!empty($_SESSION['customer_id'])) {
    $cartCount = (new CartModel())->count((int)$_SESSION['customer_id']);
    $chatUnread = (new ChatModel())->unreadForCustomer((int)$_SESSION['customer_id']);
}
if ($isAdminArea) $adminChatUnread = (new ChatModel())->unreadForAdmin();
$adminNav = [
'admin/dashboard' => ['Dashboard',
'Ringkasan',
'grid'],
'admin/orders' => ['Pesanan',
'Kelola pesanan pelanggan',
'receipt'],
'admin/menu' => ['Produk & Stok',
'Produk, stok, resep, voucher, kategori & topping',
'package'],
'admin/operations' => ['Operasional',
'Pengeluaran, toko & ongkir, dan meja',
'store'],
'admin/chat' => ['Chat',
'Percakapan pelanggan',
'message'],
'admin/customers' => ['Pelanggan & Ulasan',
'Data pelanggan & moderasi ulasan',
'users'],
'admin/finance' => ['Keuangan',
'Kas & laporan',
'wallet'],
];
$navActive = static function (string $prefix) use ($route): bool {
    return $prefix === 'admin/operations' || $prefix === 'admin/menu'
    ? $route === $prefix
    : ($route === $prefix || str_starts_with($route,
    $prefix.'-'));
};
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — Warung Maem Jadoel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/foundation.css?v=<?= asset_version('assets/css/foundation.css') ?>">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css?v=<?= asset_version('assets/css/app.css') ?>">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/ecommerce.css?v=<?= asset_version('assets/css/ecommerce.css') ?>">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/revision.css?v=<?= asset_version('assets/css/revision.css') ?>">
</head>
<body class="<?= $isAdminArea ? 'admin-layout-body' : ($isCustomerArea ? 'customer-layout-body' : 'public-layout-body') ?>">

<?php if ($isAdminArea): ?>

<div class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <a href="<?= APP_URL ?>/index.php?r=admin/dashboard" class="admin-brand-link">
                <span class="brand-logo admin-brand-logo"><img src="<?= APP_URL ?>/assets/images/Logo.jpg" alt="Logo Warung Maem Jadoel"></span>
                <span>
                    <strong>Warung Maem Jadoel</strong>
                </span>
            </a>
        </div>

        <nav class="admin-nav" aria-label="Navigasi admin">
            <?php foreach ($adminNav as $href => $nav): ?>
                <a class="admin-nav-item <?= $navActive($href) ? 'active' : '' ?>"
                   href="<?= APP_URL ?>/index.php?r=<?= $href ?>">
                    <span class="nav-index">
                        <?= icon($nav[2] ?? 'grid') ?>
                    </span>
                    <span>
                        <strong><?= e($nav[0]) ?></strong>
                    </span>
                    <?php if ($href === 'admin/orders'): ?><span class="order-light-counts" id="sidebarOrderCounts" aria-label="Jumlah pesanan masuk, diproses, siap" hidden><span class="order-light red" data-count="masuk">0</span><span class="order-light yellow" data-count="proses">0</span><span class="order-light green" data-count="siap">0</span></span><?php endif;
?>
                    <?php if ($href === 'admin/chat' && $adminChatUnread > 0): ?><span class="ec-badge" id="sidebarChatBadge" style="position:static;margin-left:auto;border:0"><?= $adminChatUnread > 99 ? '99+' : $adminChatUnread ?></span><?php endif;
?>
                </a>
            <?php endforeach;
?>
        </nav>

    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button class="admin-menu-toggle" type="button"
                        onclick="document.getElementById('adminSidebar').classList.toggle('open')"
                        aria-label="Buka menu admin">
                    <?= icon('menu') ?> Menu
                </button>
                <div>
                    <span class="topbar-kicker">Warung Maem Jadoel</span>
                    <strong><?= e($title) ?></strong>
                </div>
            </div>

            <div class="admin-topbar-right">
                <div class="admin-topbar-user"><strong><?= e($_SESSION['username'] ?? 'Admin') ?></strong><small>Administrator</small></div>
                <span class="topbar-date"><?= date('d M Y') ?></span>
                <a class="admin-topbar-logout" href="<?= APP_URL ?>/index.php?r=auth/admin-logout"><?= icon('log-out') ?> Keluar</a>
            </div>
        </header>
        <script>
        (function(){
          async function refreshOrderLights(){
            const wrap=document.getElementById('sidebarOrderCounts'); if(!wrap)return;
            try { const res=await fetch('<?=APP_URL?>/index.php?r=admin/running',{cache:'no-store'}); const data=await res.json(); const c=data.statusCounts||{};
              const vals=[c.masuk||0,c.proses||0,c.siap||0];
              wrap.querySelectorAll('[data-count]').forEach((el,i)=>{el.textContent=vals[i];});
              wrap.hidden=vals.every(v=>v===0);
            } catch(e){}
          }
          refreshOrderLights(); setInterval(refreshOrderLights,3000);
        })();
        </script>

<?php elseif ($isCustomerArea): ?>

<header class="ec-header">
    <div class="ec-header-inner">
        <a class="ec-brand" href="<?= APP_URL ?>/index.php?r=order/menu" aria-label="Warung Maem Jadoel">
            <span class="ec-brand-logo"><img src="<?= APP_URL ?>/assets/images/Logo.jpg" alt="Logo Warung Maem Jadoel"></span>
            <span class="ec-brand-copy">
                <strong>Warung Maem Jadoel</strong>
                <small>ANEKA JAJANAN DENGAN CITARASA YANG AUTENTIK</small>
            </span>
        </a>

        <nav class="ec-nav" aria-label="Navigasi pelanggan" data-customer-nav>
            <a class="ec-nav-link <?= $route === 'order/menu' ? 'active' : '' ?>" data-nav-section="home" href="<?= APP_URL ?>/index.php?r=order/menu"><?= icon('home') ?> Beranda</a>
            <a class="ec-nav-link" data-nav-section="products" href="<?= APP_URL ?>/index.php?r=order/menu#products"><?= icon('menu') ?> Menu</a>
            <a class="ec-nav-link <?= in_array($route,
['order/history',
'order/detail'],
true) ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php?r=order/history"><?= icon('receipt') ?> Pesanan</a>
            <a class="ec-nav-link <?= str_starts_with($route,
'chat') ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php?r=chat"><?= icon('chat') ?> Chat<?php if ($chatUnread > 0): ?><span class="ec-nav-dot" aria-label="<?= $chatUnread ?> pesan baru"></span><?php endif;
?></a>
            <a class="ec-nav-link <?= $route === 'customer/address' ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php?r=customer/address"><?= icon('map-pin') ?> Alamat</a>
        </nav>

        <div class="ec-header-actions">
            <a class="ec-icon-btn ec-cart-trigger" href="<?= APP_URL ?>/index.php?r=order/menu#cart" data-cart-open aria-label="Keranjang belanja" title="Buka keranjang">
                <?= icon('shopping-bag') ?>
                <span class="ec-badge" id="headerCartBadge" <?= $cartCount > 0 ? '' : 'hidden' ?>><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
            </a>
            <a class="ec-account" href="<?= APP_URL ?>/index.php?r=auth/profile" title="Profil <?= e($customerName) ?>">
                <span class="ec-avatar"><?= e($customerInitial) ?></span>
                <span class="ec-account-copy">
                    <small>Halo,</small>
                    <strong><?= e($customerFirstName ?: 'Pelanggan') ?></strong>
                </span>
            </a>
            <a class="ec-logout" href="<?= APP_URL ?>/index.php?r=auth/logout">Keluar</a>
            <button class="ec-icon-btn ec-menu-toggle" type="button" onclick="document.getElementById('customerMobileNav').classList.toggle('open')" aria-label="Buka menu pelanggan"><?= icon('menu') ?></button>
        </div>
    </div>

    <div class="customer-mobile-nav ec-mobile-drawer" id="customerMobileNav">
        <a href="<?= APP_URL ?>/index.php?r=order/menu">Beranda</a>
        <a href="<?= APP_URL ?>/index.php?r=order/menu#cart" data-cart-open>Keranjang</a>
        <a href="<?= APP_URL ?>/index.php?r=review/mine">Ulasan Saya</a>
        <a href="<?= APP_URL ?>/index.php?r=auth/profile">Pengaturan Akun</a>
        <a href="<?= APP_URL ?>/index.php?r=auth/logout">Keluar</a>
    </div>
</header>

<?php elseif (!$isAuthPage): ?>

<header class="ec-header public-home-header">
    <div class="ec-header-inner">
        <a class="ec-brand" href="<?= APP_URL ?>/" aria-label="Warung Maem Jadoel">
            <span class="ec-brand-logo"><img src="<?= APP_URL ?>/assets/images/Logo.jpg" alt="Logo Warung Maem Jadoel"></span>
            <span class="ec-brand-copy"><strong>Warung Maem Jadoel</strong><small>ANEKA JAJANAN DENGAN CITARASA YANG AUTENTIK</small></span>
        </a>
        <nav class="ec-nav public-home-nav" aria-label="Navigasi homepage">
            <a class="ec-nav-link <?= $route === 'landing' ? 'active' : '' ?>" href="<?= APP_URL ?>/#beranda">Beranda</a>
            <a class="ec-nav-link" href="<?= APP_URL ?>/#voucher">Voucher</a>
            <a class="ec-nav-link" href="<?= APP_URL ?>/#menu">Menu</a>
            <a class="ec-nav-link" href="<?= APP_URL ?>/#tentang">Tentang Kami</a>
            <a class="ec-nav-link" href="<?= APP_URL ?>/#faq">FAQ</a>
            <a class="ec-nav-link" href="<?= APP_URL ?>/#ulasan-pelanggan">Ulasan</a>
            <a class="ec-nav-link" href="<?= APP_URL ?>/#lokasi">Lokasi</a>
        </nav>
        <div class="ec-header-actions">
            <?php if (!empty($_SESSION['customer_id'])): ?>
                <a class="ec-btn ec-btn-sm ec-btn-light" href="<?= APP_URL ?>/index.php?r=order/menu">Masuk ke Pesanan</a>
                <a class="ec-account" href="<?= APP_URL ?>/index.php?r=auth/profile"><span class="ec-avatar"><?= e($customerInitial ?? 'P') ?></span><span class="ec-account-copy"><small>Halo,</small><strong><?= e($customerFirstName ?? 'Pelanggan') ?></strong></span></a>
            <?php else: ?>
                <button class="ec-btn ec-btn-sm ec-btn-light" type="button" data-modal-open="#loginModal">Masuk</button>
                <button class="ec-btn ec-btn-sm ec-btn-dark" type="button" data-modal-open="#registerModal">Daftar</button>
            <?php endif;
?>
            <button class="ec-icon-btn ec-menu-toggle" type="button" onclick="document.getElementById('publicMobileNav').classList.toggle('open')" aria-label="Buka menu homepage"><?= icon('menu') ?></button>
        </div>
    </div>
    <div class="customer-mobile-nav ec-mobile-drawer" id="publicMobileNav">
        <a href="<?= APP_URL ?>/#beranda">Beranda</a>
        <a href="<?= APP_URL ?>/#voucher">Voucher</a>
        <a href="<?= APP_URL ?>/#menu">Menu</a>
        <a href="<?= APP_URL ?>/#tentang">Tentang Kami</a>
        <a href="<?= APP_URL ?>/#faq">FAQ</a>
        <a href="<?= APP_URL ?>/#ulasan-pelanggan">Ulasan</a>
        <a href="<?= APP_URL ?>/#lokasi">Lokasi</a>
        <?php if (!empty($_SESSION['customer_id'])): ?><a href="<?= APP_URL ?>/index.php?r=order/menu">Masuk ke Pesanan</a><a href="<?= APP_URL ?>/index.php?r=auth/profile">Profil</a><?php else: ?><button type="button" data-modal-open="#loginModal">Masuk</button><button type="button" data-modal-open="#registerModal">Daftar</button><?php endif;
?>
    </div>
</header>

<!-- Login & registrasi pop-up: form tetap dikirim ke endpoint autentikasi asli. -->
<div class="modal fade auth-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <button type="button" class="auth-modal-close" data-modal-close aria-label="Tutup"><?= icon('x') ?></button>
  <div class="auth-modal-brand">
   <span class="auth-modal-logo"><img src="<?=APP_URL?>/assets/images/Logo.jpg" alt="Logo Warung Maem Jadoel"></span>
   <span><strong>Warung Maem Jadoel</strong><small>ANEKA JAJANAN DENGAN CITARASA YANG AUTENTIK</small></span>
  </div>
  <div class="auth-modal-eyebrow">SELAMAT DATANG KEMBALI</div><h2 id="loginModalTitle">Masuk ke akunmu</h2><p class="auth-modal-subtitle">Lanjutkan pesanan dan cek voucher promomu.</p>
  <form method="post" action="<?=APP_URL?>/index.php?r=auth/login" class="auth-modal-form">
   <?=csrf_field()?>
   <label for="modalLoginEmail">Email</label><input id="modalLoginEmail" class="form-control" type="email" name="email" autocomplete="username" placeholder="nama@email.com" required>
   <label for="modalLoginPassword">Kata sandi</label><div class="auth-password-wrap"><input id="modalLoginPassword" class="form-control" type="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required><button type="button" class="auth-password-toggle" data-target="modalLoginPassword" aria-label="Tampilkan kata sandi"><?= icon('eye',
['class' => 'ico-eye']) ?><?= icon('eye-off',
['class' => 'ico-eye-off']) ?></button></div>
   <button class="auth-modal-forgot" type="button" data-modal-close data-modal-open="#forgotModal">Lupa kata sandi?</button>
   <button class="auth-modal-submit" type="submit">Masuk ke akun</button>
  </form><div class="auth-modal-switch">Belum punya akun? <button type="button" data-modal-close data-modal-open="#registerModal">Daftar sekarang</button></div>
 </div></div>
</div>
<div class="modal fade auth-forgot-modal" id="forgotModal" tabindex="-1" aria-labelledby="forgotModalTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <button type="button" class="forgot-modal-close" data-modal-close aria-label="Tutup"><?= icon('x') ?></button>
  <div class="forgot-modal-brand">
    <span class="forgot-modal-logo"><img src="<?=APP_URL?>/assets/images/Logo.jpg" alt="Logo Warung Maem Jadoel"></span>
    <span><strong>Warung Maem Jadoel</strong><small>ANEKA JAJANAN DENGAN CITARASA YANG AUTENTIK</small></span>
  </div>
  <div class="forgot-modal-eyebrow">PULIHKAN AKUN ANDA</div>
  <h2 id="forgotModalTitle">Lupa kata sandi?</h2>
  <p class="forgot-modal-subtitle">Masukkan email yang terdaftar. Kami akan mengirimkan tautan untuk membuat kata sandi baru.</p>
  <?php if (($_GET['sent'] ?? '') === '1'): ?><div class="auth-forgot-success" role="status">Jika email terdaftar, instruksi reset telah dikirim. Silakan cek kotak masuk emailmu.</div><?php endif;
?>
  <form method="post" action="<?=APP_URL?>/index.php?r=auth/forgot" class="forgot-modal-form">
   <?=csrf_field()?>
   <label for="modalForgotEmail">Email pengguna</label>
   <input id="modalForgotEmail" class="form-control" type="email" name="email" autocomplete="email" placeholder="nama@email.com" required>
   <button class="forgot-modal-submit" type="submit">Kirim tautan pemulihan</button>
  </form>
  <div class="forgot-modal-switch">Sudah ingat kata sandi? <button type="button" data-modal-close data-modal-open="#loginModal">Kembali masuk</button></div>
 </div></div>
</div>
<div class="modal fade auth-modal" id="registerModal" tabindex="-1" aria-labelledby="registerModalTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content auth-register-content">
  <button type="button" class="auth-modal-close" data-modal-close aria-label="Tutup"><?= icon('x') ?></button>
  <div class="auth-modal-brand">
   <span class="auth-modal-logo"><img src="<?=APP_URL?>/assets/images/Logo.jpg" alt="Logo Warung Maem Jadoel"></span>
   <span><strong>Warung Maem Jadoel</strong><small>ANEKA JAJANAN DENGAN CITARASA YANG AUTENTIK</small></span>
  </div>
  <div class="auth-modal-eyebrow">GABUNG BERSAMA KAMI</div><h2 id="registerModalTitle">Buat akun baru</h2><p class="auth-modal-subtitle">Simpan riwayat pesanan dan nikmati promo voucher.</p>
  <form method="post" action="<?=APP_URL?>/index.php?r=auth/register" class="auth-modal-form auth-register-form">
   <?=csrf_field()?>
   <div><label for="modalRegUsername">Username</label><input id="modalRegUsername" class="form-control" name="username" autocomplete="username" placeholder="Buat username" required></div>
   <div><label for="modalRegNama">Nama lengkap</label><input id="modalRegNama" class="form-control" name="nama" autocomplete="name" placeholder="Nama lengkap" required></div>
   <div class="auth-field-wide"><label for="modalRegEmail">Email</label><input id="modalRegEmail" class="form-control" type="email" name="email" autocomplete="email" placeholder="nama@email.com" required></div>
   <div><label for="modalRegPhone">No. HP <small>(opsional)</small></label><input id="modalRegPhone" class="form-control" name="no_hp" autocomplete="tel" placeholder="Nomor HP"></div>
   <div><label for="modalRegPassword">Kata sandi</label><div class="auth-password-wrap"><input id="modalRegPassword" class="form-control" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" required><button type="button" class="auth-password-toggle" data-target="modalRegPassword" aria-label="Tampilkan kata sandi"><?= icon('eye',
['class' => 'ico-eye']) ?><?= icon('eye-off',
['class' => 'ico-eye-off']) ?></button></div></div>
   <div class="auth-field-wide"><label for="modalRegConfirm">Konfirmasi kata sandi</label><div class="auth-password-wrap"><input id="modalRegConfirm" class="form-control" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi" required><button type="button" class="auth-password-toggle" data-target="modalRegConfirm" aria-label="Tampilkan kata sandi"><?= icon('eye',
['class' => 'ico-eye']) ?><?= icon('eye-off',
['class' => 'ico-eye-off']) ?></button></div></div>
   <button class="auth-modal-submit auth-field-wide" type="submit">Daftar sekarang</button>
  </form><div class="auth-modal-switch">Sudah punya akun? <button type="button" data-modal-close data-modal-open="#loginModal">Masuk</button></div>
 </div></div>
</div>

<?php else: ?>

<header class="auth-header">
    <a class="public-brand" href="<?= APP_URL ?>/">
        <span class="brand-logo public-brand-logo"><img src="<?= APP_URL ?>/assets/images/Logo.jpg" alt="Logo Warung Maem Jadoel"></span>
        <span>
            <strong>Warung Maem Jadoel</strong>
            <small>ANEKA JAJANAN DENGAN CITARASA YANG AUTENTIK</small>
        </span>
    </a>
    <a href="<?= APP_URL ?>/">Kembali ke website</a>
</header>

<?php endif;
?>

<?php if ($m = flash('success')): ?>
    <div class="alert alert-success app-flash m-0 text-center"><?= e($m) ?></div>
<?php endif;
?>

<?php if ($m = flash('error')): ?>
    <div class="alert alert-danger app-flash m-0 text-center"><?= e($m) ?></div>
<?php endif;
?>
