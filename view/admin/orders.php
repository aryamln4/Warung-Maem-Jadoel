<?php $title='Pesanan';
require BASE_PATH.'/view/partials/header.php';
?>
<div class="admin-content orders-board">
  <?php if(!empty($pendingOngkir)): ?>
    <section class="ec-panel" style="margin-bottom:18px;border-left:4px solid var(--c-warning)">
      <div class="ec-panel-head" style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;padding:16px 18px 8px">
        <div><span class="eyebrow">MENUNGGU KONFIRMASI</span><h2 class="section-title mb-0" style="font-size:1.05rem">Ongkir delivery belum final (<?=count($pendingOngkir)?>)</h2><p class="page-subtitle mb-0">QRIS baru dibuat setelah owner mengonfirmasi ongkir. Nominal bisa disesuaikan dari estimasi.</p></div>
        <a class="btn btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/chat"><?= icon('chat') ?> Chat pelanggan</a>
      </div>
      <div class="table-responsive" style="padding:0 18px 14px">
        <table class="table align-middle mb-0">
          <thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Alamat</th><th>Jarak</th><th>Estimasi</th><th>Konfirmasi ongkir</th></tr></thead>
          <tbody>
          <?php foreach($pendingOngkir as $p): ?>
            <tr>
              <td><strong>#<?=e($p['nomor_pesanan'])?></strong><br><small><?=rupiah($p['total_bayar'])?></small></td>
              <td><?=e($p['pelanggan'])?><br><small><?=e($p['no_hp_penerima'] ?? '')?></small></td>
              <td style="max-width:280px"><small><?=e($p['alamat_teks'] ?? '')?><?php if($p['patokan']): ?><br>Patokan: <?=e($p['patokan'])?><?php endif;
?></small></td>
              <td><?= $p['jarak_km'] ? number_format((float)$p['jarak_km'],
1,
',',
'.').' km' : '—' ?></td>
              <td><strong><?=rupiah($p['biaya_ongkir'])?></strong></td>
              <td>
                <form method="post" action="<?=APP_URL?>/index.php?r=admin/confirm-ongkir" class="d-flex gap-2" style="min-width:220px" onsubmit="return confirm('Konfirmasi ongkir final? Total pesanan dihitung ulang dan QRIS dapat dibuat.')">
                  <?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$p['transaksi_id']?>">
                  <input class="form-control" type="number" name="ongkir" min="0" step="500" required value="<?= (float)$p['biaya_ongkir'] ?>" style="max-width:130px">
                  <button class="btn btn-sm btn-primary" type="submit">Konfirmasi</button>
                </form>
              </td>
            </tr>
          <?php endforeach;
?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif;
?>

  <div class="orders-columns">
<?php foreach([['Pesanan Masuk',
$ordersIn,
'order-red'],
['Pesanan Diproses',
$ordersProcess,
'order-yellow'],
['Pesanan Siap',
$ordersReady,
'order-green']] as [$heading,
$list,
$tone]): ?>
<section class="orders-column <?=$tone?>"><header><h2><?=e($heading)?></h2><b><?=count($list)?></b></header>
<?php if(!$list): ?><div class="empty-state">Belum ada pesanan</div><?php endif;
?>
<?php foreach($list as $o): $isDelivery=$o['jenis_pesanan']==='DELIVERY';
$ongkirPending=$isDelivery && ($o['ongkir_status'] ?? '')==='ESTIMASI';
?>
  <article class="order-board-card">
    <div class="order-board-top">
      <strong>#<?=e($o['nomor_pesanan'])?> · <?=e($o['pelanggan'])?></strong>
      <small>
        <?= $isDelivery ? 'Delivery' : ($o['jenis_pesanan']==='TAKEAWAY'?'Takeaway':'Dine-in'.($o['nomor_meja']?' · Meja '.$o['nomor_meja']:'')) ?>
        · <?=e(order_status_label((string)$o['status_pesanan']))?>
      </small>
    </div>
    <?php foreach($o['grouped_items'] as $g): ?><div class="order-board-item"><strong><?=e($g['nama_produk'])?> ×<?=$g['qty']?></strong><?php foreach($g['variants'] as $v): ?><small><?=$v['qty']?> pcs<?=!empty($v['topping'])?' · '.e($v['topping']):''?><?=$v['level_pedas']!==null?' · Level '.$v['level_pedas']:''?></small><?php endforeach;
?></div><?php endforeach;
?>

    <?php if($isDelivery): ?>
      <div class="order-board-delivery">
        <div><span><?= icon('map-pin') ?> <?=e($o['nama_penerima'] ?? 'Penerima')?></span><small><?=e($o['alamat_teks'] ?? '')?></small></div>
        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
          <small><?= $o['jarak_km'] ? number_format((float)$o['jarak_km'],
1,
',',
'.').' km' : 'Jarak —' ?> · <?=e($o['no_hp_penerima'] ?? '')?></small>
          <span class="ec-status-chip <?= $ongkirPending ? 'st-menunggu_pembayaran' : 'st-selesai' ?>"><?= $ongkirPending ? 'Ongkir estimasi' : 'Ongkir final' ?></span>
        </div>
        <div class="order-board-money"><span>Ongkir</span><strong><?=rupiah($o['biaya_ongkir'] ?? 0)?></strong></div>
        <?php if($o['ekspedisi'] || $o['link_lacak']): ?>
          <small style="display:block;margin-top:6px"><?= icon('truck') ?> <?=e($o['ekspedisi'] ?: 'Kurir')?><?php if($o['link_lacak']): ?> · <a href="<?=e($o['link_lacak'])?>" target="_blank" rel="noopener noreferrer">Lacak</a><?php endif;
?></small>
        <?php endif;
?>
      </div>
    <?php endif;
?>

    <div class="order-board-total"><?=rupiah($o['total_bayar'])?></div><small><?=e($o['metode_bayar'])?> · <?=e($o['status_pembayaran'])?><?php if($o['voucher_kode']): ?> · <?=e($o['voucher_kode'])?><?php endif;
?></small>

    <?php if($ongkirPending && $o['status_pesanan']!=='DIBATALKAN'): ?>
      <form method="post" action="<?=APP_URL?>/index.php?r=admin/confirm-ongkir" class="order-board-inline" onsubmit="return confirm('Konfirmasi ongkir final untuk pesanan ini?')">
        <?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$o['id']?>">
        <input class="form-control" type="number" name="ongkir" min="0" step="500" required value="<?= (float)($o['biaya_ongkir'] ?? 0) ?>">
        <button class="btn btn-sm btn-primary" type="submit">Konfirmasi ongkir</button>
      </form>
    <?php endif;
?>

    <?php if($o['status_pesanan']==='MENUNGGU_KONFIRMASI' && ($o['metode_bayar']==='TUNAI' || $o['status_pembayaran']==='TERVERIFIKASI') && !$ongkirPending): ?>
      <form method="post" action="<?=APP_URL?>/index.php?r=admin/confirm-cash" onsubmit="return confirm('Pastikan pembayaran tunai sudah diterima, atau pembayaran QRIS sudah terverifikasi. Lanjutkan memproses pesanan?')"><?=csrf_field()?><input type="hidden" name="id" value="<?=$o['id']?>"><button class="btn btn-sm btn-primary w-100"><?=($o['metode_bayar']==='TUNAI' && $o['status_pembayaran']!=='TERVERIFIKASI')?'Konfirmasi Tunai & Proses':'Proses Pesanan'?></button></form>
    <?php endif;
?>

    <?php if($o['metode_bayar']==='QRIS' && $o['status_pembayaran']==='MENUNGGU' && !empty($o['payment_reference'])): ?>
      <small style="display:block;margin:8px 0 6px;color:#92400e;font-weight:700">Menunggu pembayaran QRIS</small>
      <form method="post" action="<?=APP_URL?>/index.php?r=admin/check-payment"><?=csrf_field()?><input type="hidden" name="id" value="<?=$o['id']?>"><button class="btn btn-sm btn-secondary-soft w-100" title="Tarik status pembayaran langsung dari Midtrans"><?= icon('wallet') ?> Cek Status QRIS ke Midtrans</button></form>
    <?php endif;
?>

    <?php if($o['status_pesanan']==='DIPROSES'): $nextStatus=$isDelivery?'DIKEMAS':($o['jenis_pesanan']==='TAKEAWAY'?'SIAP_DIAMBIL':'SEDANG_DIANTAR');
?>
      <form method="post" action="<?=APP_URL?>/index.php?r=admin/status"><?=csrf_field()?><input type="hidden" name="id" value="<?=$o['id']?>"><input type="hidden" name="status" value="<?=$nextStatus?>"><button class="btn btn-sm btn-secondary-soft w-100"><?= $isDelivery ? 'Pesanan Dikemas' : 'Pesanan Siap' ?></button></form>
    <?php endif;
?>

    <?php if($isDelivery && in_array($o['status_pesanan'],
['DIPROSES',
'DIKEMAS'],
true) && !$ongkirPending): ?>
      <details class="order-board-courier">
        <summary><?= icon('truck') ?> Catat kurir &amp; link lacak</summary>
        <form method="post" action="<?=APP_URL?>/index.php?r=admin/set-courier">
          <?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$o['id']?>">
          <input class="form-control" name="ekspedisi" maxlength="80" placeholder="Aplikasi kurir (GoSend, Grab…)" value="<?=e($o['ekspedisi'] ?? '')?>">
          <input class="form-control" name="link_lacak" maxlength="255" placeholder="Tautan pelacakan (opsional)" value="<?=e($o['link_lacak'] ?? '')?>">
          <button class="btn btn-sm btn-dark" type="submit">Simpan &amp; kirim</button>
        </form>
      </details>
    <?php endif;
?>

    <?php if(in_array($o['status_pesanan'],
['SIAP_DIAMBIL',
'SEDANG_DIANTAR'],
true)): ?><form method="post" action="<?=APP_URL?>/index.php?r=admin/status" onsubmit="return confirm('Tandai pesanan ini selesai?')"><?=csrf_field()?><input type="hidden" name="id" value="<?=$o['id']?>"><input type="hidden" name="status" value="SELESAI"><button class="btn btn-sm btn-success w-100 mt-2">Pesanan Selesai</button></form><?php endif;
?>

    <a class="order-board-chat" href="<?=APP_URL?>/index.php?r=admin/chat"><?= icon('chat') ?> Chat pesanan</a>
  </article>
<?php endforeach;
?></section><?php endforeach;
?></div></div>
<style>
.orders-columns{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.orders-column{background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:16px;min-width:0}
.orders-column>header{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.orders-column h2{font-size:1rem;margin:0;font-weight:750}
.orders-column>header b{border-radius:30px;padding:4px 10px}
.order-red{border-top:4px solid #dc3545}.order-yellow{border-top:4px solid #e3a008}.order-green{border-top:4px solid #198754}
.order-board-card{border:1px solid #e5e7eb;border-radius:14px;padding:13px;margin-bottom:12px;color:#20242a}
.order-board-top{display:flex;flex-direction:column;gap:3px}
.order-board-card small{color:#59616b}
.order-board-item{padding:9px 0;border-bottom:1px solid #eee;display:flex;flex-direction:column}
.order-board-delivery{margin-top:10px;padding:10px;border-radius:12px;background:#f8f5ef;display:flex;flex-direction:column;gap:6px;font-size:.78rem}
.order-board-delivery>div:first-child{display:flex;flex-direction:column;gap:2px}
.order-board-money{display:flex;justify-content:space-between;align-items:center;border-top:1px dashed #ded5c8;padding-top:6px}
.order-board-total{font-weight:800;margin:10px 0}
.order-board-inline{display:flex;gap:6px;margin:8px 0}
.order-board-inline input{max-width:120px}
.order-board-courier{margin:8px 0;border:1px solid #e5e7eb;border-radius:10px;padding:8px 10px}
.order-board-courier summary{cursor:pointer;font-weight:700;font-size:.8rem;color:#5d4534}
.order-board-courier form{display:flex;flex-direction:column;gap:6px;margin-top:8px}
.order-board-chat{display:inline-flex;align-items:center;gap:6px;margin-top:10px;font-size:.76rem;font-weight:700;color:#8a4f32}
@media(max-width:900px){.orders-columns{grid-template-columns:1fr}}
</style>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
