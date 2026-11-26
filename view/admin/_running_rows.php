<?php if(!$orders): ?>
<tr><td colspan="8"><div class="empty-state"><strong>Belum ada pesanan berjalan.</strong><span>Pesanan baru akan muncul otomatis di panel ini.</span></div></td></tr>
<?php else: foreach($orders as $o): ?>
<tr data-order-id="<?=$o['id']?>">
  <td><span class="order-number">#<?=$o['id']?></span></td>
  <td><strong><?=e($o['pelanggan'])?></strong></td>
  <td><span class="type-chip <?=strtolower($o['jenis_pesanan'])?>"><?= $o['jenis_pesanan']==='TAKEAWAY'?'Takeaway':'Dine-in' ?></span></td>
  <td><?= $o['jenis_pesanan']==='TAKEAWAY'?'Ambil sendiri':'Meja '.e($o['nomor_meja']) ?></td>
  <td class="order-preview">
    <?php if(empty($o['grouped_items'])): ?>
      <span class="text-muted">Produk tidak ditemukan</span>
    <?php else: ?>
      <?php foreach(array_slice($o['grouped_items'],
0,
3) as $g): ?>
        <div><strong><?=e($g['nama_produk'])?></strong> × <?=number_format((int)$g['qty'])?></div>
      <?php endforeach;
?>
      <?php if(count($o['grouped_items'])>3): ?><small>+ <?=count($o['grouped_items'])-3?> produk lainnya</small><?php endif;
?>
    <?php endif;
?>
  </td>
  <td><strong><?=e($o['metode_bayar'])?></strong><br><small>Rp <?=number_format($o['total_bayar'],
0,
',',
'.')?></small></td>
  <td>
    <span class="status-chip status-<?=strtolower($o['status_pesanan'])?>"><?=e(str_replace('_',
' ',
$o['status_pesanan']))?></span>
    <?php if($o['metode_bayar']==='QRIS' && $o['status_pembayaran']==='TERVERIFIKASI'): ?><small class="d-block text-success mt-1">Sudah terbayar</small>
    <?php elseif($o['metode_bayar']==='QRIS'): ?><small class="d-block text-warning mt-1">Menunggu pembayaran</small>
    <?php endif;
?>
  </td>
  <td class="text-end admin-order-actions-cell">
    <div class="admin-order-actions">
    <details class="order-detail-native">
      <summary class="btn btn-sm btn-outline-dark touch order-detail-trigger"><span>Detail pesanan</span><?= icon('arrow-up-right') ?></summary>
      <div class="order-detail-popover text-start">
        <div class="order-detail-heading">
          <div class="order-detail-heading-main"><span class="order-detail-kicker">RINCIAN PESANAN</span><strong>Pesanan #<?=$o['id']?></strong><small><?=e($o['pelanggan'])?> · <?= $o['jenis_pesanan']==='TAKEAWAY'?'Takeaway':'Dine-in'.($o['nomor_meja']?' · Meja '.e($o['nomor_meja']):'') ?></small></div>
          <div class="order-detail-total-wrap"><small>Total</small><span class="order-detail-total">Rp <?=number_format($o['total_bayar'],
0,
',',
'.')?></span></div>
        </div>
        <?php if(empty($o['grouped_items'])): ?>
          <div class="order-detail-empty">Rincian produk belum ditemukan untuk pesanan ini.</div>
        <?php else: ?>
          <div class="order-detail-products">
          <?php foreach($o['grouped_items'] as $g): ?>
            <div class="order-product-group">
              <div class="order-product-main"><strong><?=e($g['nama_produk'])?></strong><strong><?=number_format((int)$g['qty'])?> pcs</strong></div>
              <?php foreach($g['variants'] as $v): ?>
                <div class="order-product-variant">
                  <span><?=number_format((int)$v['qty'])?> pcs<?php if(!empty($v['topping'])): ?> · <?=e($v['topping'])?><?php endif;
?><?php if($v['level_pedas']!==null): ?> · Level <?=e($v['level_pedas'])?><?php endif;
?></span>
                  <span>Rp <?=number_format($v['subtotal'],
0,
',',
'.')?></span>
                </div>
              <?php endforeach;
?>
            </div>
          <?php endforeach;
?>
          </div>
        <?php endif;
?>
        <div class="order-detail-meta"><span>Pembayaran: <strong><?=e($o['status_pembayaran'])?></strong> · <?=e($o['metode_bayar'])?></span><span>Status: <strong><?=e(str_replace('_',
' ',
$o['status_pesanan']))?></strong></span></div>
      </div>
    </details>
    <?php if(in_array($o['status_pesanan'],
['MENUNGGU_KONFIRMASI',
'MENUNGGU_PEMBAYARAN'],
true) && ($o['metode_bayar']==='TUNAI' || $o['status_pembayaran']==='TERVERIFIKASI')): ?>
      <form method="post" action="<?=APP_URL?>/index.php?r=admin/confirm-cash" class="d-inline"><?=csrf_field()?><input type="hidden" name="id" value="<?=$o['id']?>"><button class="btn btn-sm btn-primary touch">Terima pesanan</button></form>
    <?php elseif($o['status_pesanan']==='MENUNGGU_PEMBAYARAN'): ?>
      <span class="text-muted small">Menunggu pembayaran QRIS</span>
    <?php else: ?>
      <form method="post" action="<?=APP_URL?>/index.php?r=admin/status" class="status-form"><?=csrf_field()?><input type="hidden" name="id" value="<?=$o['id']?>"><select class="form-select form-select-sm" name="status"><option>DIPROSES</option><?php if($o['jenis_pesanan']==='TAKEAWAY'): ?><option>SIAP_DIAMBIL</option><?php else: ?><option>SEDANG_DIANTAR</option><?php endif;
?><option>SELESAI</option></select><button class="btn btn-sm btn-dark touch">Simpan</button></form>
    <?php endif;
?>
    </div>
  </td>
</tr>
<?php endforeach;
endif;
?>
