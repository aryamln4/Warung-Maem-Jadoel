<?php $title='Detail Pesanan';
require BASE_PATH.'/view/partials/header.php';
?>
<main class="customer-simple-page order-detail-page"><div class="customer-simple-shell order-detail-shell">
  <a class="order-detail-back" href="<?=APP_URL?>/index.php?r=order/history">← Kembali ke riwayat</a>
  <div class="order-detail-heading">
    <div><span class="customer-eyebrow">DETAIL TRANSAKSI</span><h1><?=e($order['nomor_pesanan'])?></h1><p class="customer-lead"><?=date('d M Y, H:i',
strtotime($order['tanggal']))?> · <?=e($order['jenis_pesanan']==='DINE_IN'?'Dine-in':'Takeaway')?></p></div>
    <span class="history-status detail-status"><?=e($order['status_pesanan'])?></span>
  </div>
  <section class="order-detail-grid">
    <div class="order-detail-card">
      <div class="order-detail-card-head"><strong>Pesanan</strong><span><?=count($items)?> item</span></div>
      <div class="order-detail-items">
      <?php foreach($groupedItems as $g): ?>
        <div class="order-detail-item">
          <div><strong><?=e($g['nama_produk'])?></strong><small><?= (int)$g['qty'] ?> produk</small>
          <?php foreach($g['variants'] as $v): ?><span><?=e($v['topping']??'Tanpa topping')?><?=($v['level_pedas']!==null?' · Level '.(int)$v['level_pedas']:'')?> × <?= (int)$v['qty'] ?></span><?php endforeach;
?></div>
          <strong>Rp <?=number_format($g['subtotal'],
0,
',',
'.')?></strong>
        </div>
      <?php endforeach;
?>
      </div>
      <div class="order-detail-total"><span>Total</span><strong>Rp <?=number_format($order['total_bayar'],
0,
',',
'.')?></strong></div>
    </div>
    <div class="order-detail-card order-detail-meta">
      <div class="order-detail-card-head"><strong>Informasi transaksi</strong></div>
      <dl>
        <div><dt>Metode pembayaran</dt><dd><?=e($order['metode_bayar']==='TUNAI'?'Tunai':$order['metode_bayar'])?></dd></div>
        <div><dt>Status pembayaran</dt><dd><?=e($order['status_pembayaran'])?></dd></div>
        <div><dt>Jenis pesanan</dt><dd><?=e($order['jenis_pesanan']==='DINE_IN'?'Dine-in':'Takeaway')?></dd></div>
        <?php if($order['jenis_pesanan']==='DINE_IN'): ?><div><dt>Meja</dt><dd><?=e($order['nomor_meja']??'-')?></dd></div><?php endif;
?>
      </dl>
      <a class="order-detail-reorder" href="<?=APP_URL?>/index.php?r=order/menu">Pesan Lagi <?= icon('arrow-right') ?></a>
    </div>
  </section>
</div></main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
