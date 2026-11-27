<?php $title = $checkoutMode ? 'Checkout Pesanan' : 'Pesanan #'.$tx['id'];
require BASE_PATH.'/view/partials/header.php';
?>
<?php if($checkoutMode): ?>
<div class="ec-page">
  <div class="ec-page-head">
    <div>
      <span class="ec-eyebrow">Langkah terakhir</span>
      <h1>Checkout pesanan.</h1>
      <p>Pilih metode pesanan, alamat pengantaran, dan voucher. Ongkir delivery dihitung otomatis dari titik lokasi alamat.</p>
    </div>
    <a class="ec-btn ec-btn-light" href="<?=APP_URL?>/index.php?r=customer/cart"><?= icon('shopping-bag') ?> Kembali ke keranjang</a>
  </div>

  <form method="post" action="<?=APP_URL?>/index.php?r=order/create" id="checkoutForm">
    <?=csrf_field()?>
    <div class="ec-checkout-layout">
      <div>
        <section class="ec-step">
          <div class="ec-step-head">
            <span class="ec-step-num">1</span>
            <div><strong>Metode pesanan</strong><small>Pilih cara menerima pesananmu.</small></div>
          </div>
          <div class="ec-option-grid">
            <label class="ec-option <?= !$addresses ? 'is-disabled' : '' ?>">
              <input type="radio" name="jenis_pesanan" value="DELIVERY" <?= !$addresses ? 'disabled' : '' ?>>
              <?= icon('truck') ?><strong>Delivery</strong><small>Antar ke alamat</small>
            </label>
            <label class="ec-option">
              <input type="radio" name="jenis_pesanan" value="DINE_IN" checked>
              <?= icon('store') ?><strong>Dine-in</strong><small>Makan di warung</small>
            </label>
            <label class="ec-option">
              <input type="radio" name="jenis_pesanan" value="TAKEAWAY">
              <?= icon('shopping-bag') ?><strong>Takeaway</strong><small>Ambil sendiri</small>
            </label>
          </div>
        </section>

        <section class="ec-step" id="deliveryBox" hidden>
          <div class="ec-step-head">
            <span class="ec-step-num">2</span>
            <div><strong>Alamat pengantaran</strong><small>Pastikan titik dan alamat sudah sesuai.</small></div>
          </div>
          <?php if($addresses): ?>
            <div class="ec-address-pick">
              <span class="ec-address-icon"><?= icon('map-pin') ?></span>
              <div class="ec-address-pick-copy">
                <strong><?=e($selectedAddress['label'] ?? 'Alamat')?> <?= !empty($selectedAddress['is_utama']) ? '· Utama' : '' ?></strong>
                <span><?=e($selectedAddress['nama_penerima'] ?? '')?> · <?=e($selectedAddress['no_hp'] ?? '')?></span>
                <span><?=e($selectedAddress['alamat'] ?? '')?></span>
              </div>
              <a class="ec-btn ec-btn-sm ec-btn-light" href="<?=APP_URL?>/index.php?r=customer/address">Ubah</a>
            </div>
            <?php if(count($addresses) > 1): ?>
              <div class="ec-field" style="margin-top:12px">
                <label for="alamat_id">Pilih alamat lain</label>
                <select id="alamat_id" name="alamat_id" onchange="if(this.value){location.href='<?=APP_URL?>/index.php?r=order/checkout&address_id='+this.value;}">
                  <?php foreach($addresses as $a): ?>
                    <option value="<?=$a['id']?>" <?= (int)$a['id']===(int)$selectedAddressId ? 'selected' : '' ?>>
                      <?=e($a['label'])?> — <?=e(mb_strimwidth($a['alamat'],
0,
60,
'…'))?>
                    </option>
                  <?php endforeach;
?>
                </select>
              </div>
            <?php else: ?>
              <input type="hidden" name="alamat_id" value="<?=(int)$selectedAddressId?>">
            <?php endif;
?>

            <?php if($selectedAddress && $quote): ?>
              <?php if($quote['ok']): ?>
                <div class="ec-estimate">
                  <span><?= icon('truck') ?> Estimasi ongkir · <?=number_format((float)$quote['jarak_km'],
1,
',',
'.')?> km</span>
                  <strong><?=rupiah($quote['ongkir'])?></strong>
                </div>
              <?php else: ?>
                <div class="ec-estimate is-error"><span><?= icon('alert-circle') ?> <?=e($quote['message'])?></span></div>
              <?php endif;
?>
            <?php elseif($selectedAddress): ?>
              <div class="ec-estimate is-error"><span><?= icon('alert-circle') ?> Alamat ini belum punya titik lokasi. Lengkapi lewat halaman Alamat.</span></div>
            <?php endif;
?>

            <div class="ec-warning">
              <?= icon('info-circle') ?>
              <span>Ongkir di atas masih estimasi. Owner mengonfirmasi ongkir final sebelum QRIS dibuat. Pesanan delivery diproses setelah ongkir dikonfirmasi.</span>
            </div>
          <?php else: ?>
            <div class="ec-warning">
              <?= icon('map-pin') ?>
              <span>Belum ada alamat tersimpan. <a href="<?=APP_URL?>/index.php?r=customer/address" style="text-decoration:underline;font-weight:700">Tambah alamat</a> untuk memakai delivery.</span>
            </div>
          <?php endif;
?>
        </section>

        <section class="ec-step" id="tableBox">
          <div class="ec-step-head">
            <span class="ec-step-num">2</span>
            <div><strong>Meja dine-in</strong><small>Pilih meja yang kamu tempati.</small></div>
          </div>
          <div class="ec-field">
            <label for="meja_id">Nomor meja</label>
            <select id="meja_id" name="meja_id" <?= !$tables ? 'disabled' : '' ?>>
              <option value="">— Pilih meja —</option>
              <?php foreach($tables as $t): ?><option value="<?=$t['id']?>">Meja <?=e($t['nomor_meja'])?></option><?php endforeach;
?>
            </select>
            <small>Tersedia <?=count($tables)?> meja aktif. Tidak perlu scan QR meja.</small>
          </div>
        </section>

        <section class="ec-step">
          <div class="ec-step-head">
            <span class="ec-step-num">3</span>
            <div><strong>Voucher</strong><small>Punya kode voucher? Pakai di sini.</small></div>
          </div>
          <div class="ec-voucher-row">
            <input class="form-control" type="text" name="kode" id="voucherCode" placeholder="Contoh: MAEM10" value="<?=e($voucher['kode'] ?? '')?>" autocomplete="off">
            <button class="ec-btn ec-btn-dark" type="submit" formaction="<?=APP_URL?>/index.php?r=order/apply-voucher" formnovalidate>Gunakan</button>
          </div>
          <?php if($voucher): ?>
            <div class="ec-voucher-chip">
              <?= icon('check-circle') ?> <?=e($voucher['kode'])?> · hemat <?=rupiah($potongan)?>
              <form method="post" action="<?=APP_URL?>/index.php?r=order/remove-voucher"><?=csrf_field()?><button type="submit">Lepas</button></form>
            </div>
          <?php else: ?>
            <small style="display:block;margin-top:8px;color:var(--c-muted);font-size:.72rem">Voucher berlaku sesuai minimum belanja, kuota, dan masa berlaku.</small>
          <?php endif;
?>
        </section>
      </div>

      <aside class="ec-summary">
        <h2>Ringkasan pesanan</h2>
        <div class="ec-cart-list" style="gap:10px;margin-bottom:14px">
          <?php $displayItems = $this->orders->groupItems($checkout['items']);
?>
          <?php foreach($displayItems as $g): ?>
            <div style="display:flex;gap:10px;align-items:center">
              <span class="ec-chat-item-thumb" style="width:40px;height:40px;border-radius:9px">
                <?php if(!empty($g['foto'])): ?><img src="<?=APP_URL.'/'.e($g['foto'])?>" alt=""><?php else: ?><?= icon('utensils') ?><?php endif;
?>
              </span>
              <span style="flex:1;min-width:0;font-size:.78rem"><strong style="display:block"><?=e($g['nama_produk'])?></strong><small style="color:var(--c-muted)"><?=$g['qty']?> × <?=rupiah($g['subtotal'] / max(1,
(int)$g['qty']))?></small></span>
              <strong style="font-size:.8rem"><?=rupiah($g['subtotal'])?></strong>
            </div>
          <?php endforeach;
?>
        </div>
        <div class="ec-summary-row"><span>Subtotal</span><strong><?=rupiah($checkout['total'])?></strong></div>
        <div class="ec-summary-row"><span>Ongkir</span><strong id="summaryOngkir"><?= $ongkir > 0 ? rupiah($ongkir) : 'Rp 0' ?></strong></div>
        <?php if($potongan > 0): ?><div class="ec-summary-row is-discount"><span>Potongan voucher</span><strong>− <?=rupiah($potongan)?></strong></div><?php endif;
?>
        <div class="ec-summary-total"><span>Total</span><strong id="summaryTotal"><?=rupiah($grandTotal)?></strong></div>

        <div class="ec-warning" id="ongkirWarning" hidden>
          <?= icon('alert-circle') ?><span><strong>Konfirmasi ongkir.</strong> QRIS tersedia setelah ongkir dikonfirmasi warung.</span>
        </div>

        <div class="ec-field" style="margin-top:14px">
          <label for="metode_bayar">Metode pembayaran</label>
          <select id="metode_bayar" name="metode_bayar">
            <option value="QRIS">QRIS</option>
            <option value="TUNAI">Tunai (bayar di warung / saat antar)</option>
          </select>
        </div>

        <button class="ec-btn ec-btn-dark ec-btn-block" type="submit" id="createOrderBtn">Buat pesanan <?= icon('arrow-right') ?></button>
        <div class="ec-summary-note">Dengan melanjutkan, kamu menyetujui syarat &amp; layanan kami.</div>
      </aside>
    </div>
  </form>
</div>
<script>
(function(){
  const form=document.getElementById('checkoutForm');
  const deliveryBox=document.getElementById('deliveryBox');
  const tableBox=document.getElementById('tableBox');
  const table=document.getElementById('meja_id');
  const submit=document.getElementById('createOrderBtn');
  const warning=document.getElementById('ongkirWarning');
  const hasTables=<?= $tables ? 'true' : 'false' ?>;
  const hasAddress=<?= $addresses ? 'true' : 'false' ?>;
  const quoteOk=<?= ($selectedAddress && $quote && $quote['ok']) ? 'true' : 'false' ?>;
  function currentType(){return document.querySelector('input[name="jenis_pesanan"]:checked')?.value||'DINE_IN';}
  function sync(){
    const type=currentType();
    deliveryBox.hidden=type!=='DELIVERY';
    tableBox.hidden=type!=='DINE_IN';
    if(table) table.required=type==='DINE_IN';
    if(warning) warning.hidden=type!=='DELIVERY';
    submit.disabled=(type==='DINE_IN'&&!hasTables)||(type==='DELIVERY'&&(!hasAddress||!quoteOk));
  }
  document.querySelectorAll('input[name="jenis_pesanan"]').forEach(r=>r.addEventListener('change',sync));
  sync();
  const code=document.getElementById('voucherCode');
  if(code) code.addEventListener('keydown',e=>{ if(e.key==='Enter'){e.preventDefault();code.form.querySelector('[formaction*="apply-voucher"]').click();} });
})();
</script>
<?php else: ?>
<div class="ec-page ec-page-narrow">
  <div class="ec-page-head">
    <div>
      <span class="ec-eyebrow">Pesanan berhasil dibuat</span>
      <h1>Pesanan #<?=e($tx['nomor_pesanan'])?>.</h1>
      <p>Pantau status dan lanjutkan percakapan dengan warung dari halaman ini.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a class="ec-btn ec-btn-light" href="<?=APP_URL?>/index.php?r=order/history"><?= icon('receipt') ?> Pesanan saya</a>
      <a class="ec-btn ec-btn-dark" href="<?=APP_URL?>/index.php?r=order/menu">Pesan lagi</a>
    </div>
  </div>

  <?php if(!empty($paymentError)): ?><div class="alert alert-danger"><?=e($paymentError)?></div><?php endif;
?>

  <section class="ec-step">
    <div style="display:flex;justify-content:space-between;gap:14px;align-items:center;flex-wrap:wrap;margin-bottom:14px">
      <div>
        <span class="ec-status-chip st-<?=strtolower(e($tx['status_pesanan']))?>" id="statusPill"><?=e(order_status_label((string)$tx['status_pesanan']))?></span>
        <p style="margin:8px 0 0;color:var(--c-muted);font-size:.8rem"><?=e(order_type_label((string)$tx['jenis_pesanan']))?><?php if($tx['jenis_pesanan']==='DINE_IN' && $tx['nomor_meja']): ?> · Meja <?=e($tx['nomor_meja'])?><?php endif;
?> · <?=date('d M Y H:i',
strtotime($tx['tanggal']))?></p>
      </div>
      <strong style="font-size:1.15rem"><?=rupiah($tx['total_bayar'])?></strong>
    </div>

    <div class="ec-cart-list">
      <?php foreach($groupedItems as $g): ?>
        <div class="ec-cart-row" style="grid-template-columns:64px minmax(0,1fr) auto;padding:12px">
          <div class="ec-cart-thumb" style="width:64px;height:64px">
            <?php if(!empty($g['foto'])): ?><img src="<?=APP_URL.'/'.e($g['foto'])?>" alt=""><?php else: ?><?= icon('utensils') ?><?php endif;
?>
          </div>
          <div class="ec-cart-info">
            <strong><?=e($g['nama_produk'])?> × <?=$g['qty']?></strong>
            <?php foreach($g['variants'] as $v): ?><small><?=$v['qty']?> pcs<?php if(!empty($v['topping'])): ?> · <?=e($v['topping'])?><?php endif;
?><?php if($v['level_pedas']!==null): ?> · Level <?=(int)$v['level_pedas']?><?php endif;
?></small><?php endforeach;
?>
          </div>
          <b style="font-size:.86rem"><?=rupiah($g['subtotal'])?></b>
        </div>
      <?php endforeach;
?>
    </div>

    <div class="ec-summary" style="position:static;margin-top:16px;box-shadow:none">
      <div class="ec-summary-row"><span>Subtotal</span><strong><?=rupiah(array_sum(array_column($items,
'subtotal')))?></strong></div>
      <div class="ec-summary-row"><span>Ongkir</span><strong><?=rupiah($tx['ongkir'] ?? 0)?></strong></div>
      <?php if((float)($tx['potongan_voucher'] ?? 0) > 0): ?><div class="ec-summary-row is-discount"><span>Voucher <?=e($tx['voucher_kode'] ?? '')?></span><strong>− <?=rupiah($tx['potongan_voucher'])?></strong></div><?php endif;
?>
      <div class="ec-summary-total"><span>Total</span><strong><?=rupiah($tx['total_bayar'])?></strong></div>
    </div>

    <?php if($tx['jenis_pesanan']==='DELIVERY'): ?>
      <div class="ec-address-pick" style="margin-top:16px">
        <span class="ec-address-icon"><?= icon('truck') ?></span>
        <div class="ec-address-pick-copy">
          <strong><?=e($tx['nama_penerima'] ?? 'Penerima')?> · <?=e($tx['no_hp_penerima'] ?? '')?></strong>
          <span><?=e($tx['alamat_teks'] ?? '')?></span>
          <span><?= $tx['jarak_km'] ? number_format((float)$tx['jarak_km'],
1,
',',
'.').' km · ' : '' ?><?= ($tx['ongkir_status'] ?? '') === 'DIKONFIRMASI' ? 'Ongkir dikonfirmasi' : 'Ongkir menunggu konfirmasi owner' ?></span>
        </div>
      </div>
    <?php endif;
?>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px">
      <?php if($chatThread): ?><a class="ec-btn ec-btn-light" href="<?=APP_URL?>/index.php?r=chat/thread&id=<?=(int)$chatThread['id']?>"><?= icon('chat') ?> Buka chat pesanan</a><?php endif;
?>
      <a class="ec-btn ec-btn-light" href="<?=APP_URL?>/index.php?r=order/detail&id=<?=$tx['id']?>"><?= icon('receipt') ?> Detail pesanan</a>
    </div>
  </section>

  <?php if($tx['metode_bayar']==='QRIS'): ?>
    <section class="ec-step">
      <div class="ec-step-head">
        <span class="ec-step-num"><?= icon('qr-code') ?></span>
        <div><strong>Pembayaran QRIS</strong><small>Nominal otomatis sesuai total pesanan.</small></div>
        <span class="ec-status-chip <?= $tx['status_pembayaran']==='TERVERIFIKASI'?'st-selesai':'st-menunggu_pembayaran' ?>" id="paymentStatusPill" style="margin-left:auto"><?= $tx['status_pembayaran']==='TERVERIFIKASI'?'Sudah terbayar':'Menunggu pembayaran' ?></span>
      </div>

      <?php if($ongkirPending): ?>
        <div class="ec-warning"><?= icon('alert-circle') ?><span><strong>Menunggu konfirmasi ongkir.</strong> QRIS akan muncul setelah owner mengonfirmasi ongkir final. Kamu akan melihat pembaruan otomatis di halaman ini.</span></div>
      <?php elseif($qrisImageUrl): ?>
        <div style="text-align:center;padding:10px 0">
          <img src="<?=e(APP_URL)?>/index.php?r=order/qris-image&id=<?=$tx['id']?>" alt="QRIS pembayaran" style="width:min(300px,100%);border-radius:14px;border:1px solid var(--c-line);background:#fff">
          <div style="margin-top:14px"><a class="ec-btn ec-btn-light" href="<?=e(APP_URL)?>/index.php?r=order/qris-image&id=<?=$tx['id']?>&dl=1" download="QRIS-<?=e($tx['nomor_pesanan'])?>.png"><?= icon('download') ?> Simpan gambar QRIS</a></div>
          <p style="color:var(--c-muted);font-size:.76rem;margin-top:10px">Bayar tepat <strong><?=rupiah($tx['total_bayar'])?></strong>. Status berubah otomatis setelah konfirmasi resmi penyedia QRIS diterima.</p>
        </div>
      <?php else: ?>
        <div class="ec-warning"><?= icon('alert-circle') ?><span>QRIS belum berhasil dibuat. Muat ulang halaman setelah konfigurasi payment gateway diperbaiki. Jangan membayar ke QRIS statis lain untuk transaksi ini.</span></div>
      <?php endif;
?>
    </section>
  <?php else: ?>
    <section class="ec-step">
      <div class="ec-step-head">
        <span class="ec-step-num"><?= icon('wallet') ?></span>
        <div><strong>Pembayaran tunai</strong><small>Bayar langsung ke owner/kasir atau saat pesanan diantar.</small></div>
      </div>
      <p style="margin:0;color:var(--c-muted);font-size:.84rem">Pesanan diproses setelah owner mengonfirmasi pembayaran diterima.</p>
    </section>
  <?php endif;
?>
</div>
<script>
(function(){
  let previousPaymentStatus = <?=json_encode($tx['status_pembayaran'])?>;
  let reloadedForQris = false;
  let previousOrderStatus = <?=json_encode($tx['status_pesanan'])?>;
  async function pollOrder(){
    try{
      const r=await fetch('<?=APP_URL?>/index.php?r=order/status&id=<?=$tx['id']?>',{cache:'no-store'});
      const d=await r.json();
      if(d.error) return;
      const pill=document.getElementById('statusPill');
      if(pill && d.status_pesanan){
        pill.textContent=(d.status_label||d.status_pesanan).replaceAll('_',' ');
        pill.className='ec-status-chip st-'+String(d.status_pesanan).toLowerCase();
      }
      const pay=document.getElementById('paymentStatusPill');
      if(pay && d.status_pembayaran){
        const paid=d.status_pembayaran==='TERVERIFIKASI';
        pay.textContent=paid?'Sudah terbayar':'Menunggu pembayaran';
        pay.className='ec-status-chip '+(paid?'st-selesai':'st-menunggu_pembayaran');
        previousPaymentStatus=d.status_pembayaran;
      }
      // Ongkir dikonfirmasi atau QRIS baru tersedia -> muat ulang sekali.
      if(!reloadedForQris && <?= $ongkirPending ? 'true' : 'false' ?> && d.status_pesanan && d.status_pesanan!=='MENUNGGU_PEMBAYARAN'){
        reloadedForQris=true; location.reload();
      }
    }catch(e){}
  }
  pollOrder(); setInterval(function(){ if(!document.hidden) pollOrder(); },10000);
})();
</script>
<?php endif;
?>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
