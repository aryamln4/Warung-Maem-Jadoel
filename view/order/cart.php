<?php $title = 'Keranjang';
require BASE_PATH.'/view/partials/header.php';
?>
<div class="ec-page">
  <div class="ec-page-head">
    <div>
      <span class="ec-eyebrow">Keranjang belanja</span>
      <h1>Pesananmu.</h1>
      <p>Periksa kembali menu pilihanmu sebelum lanjut ke checkout. Stok dan harga sudah disesuaikan dengan katalog terbaru.</p>
    </div>
    <a class="ec-btn ec-btn-light" href="<?=APP_URL?>/index.php?r=order/menu"><?= icon('chevron-right',
['style' => 'transform:rotate(180deg)']) ?> Lanjut pilih menu</a>
  </div>

  <?php if(!$items): ?>
    <div class="ec-panel" style="text-align:center;padding:60px 24px">
      <div style="font-size:2rem;color:var(--c-accent);margin-bottom:10px"><?= icon('shopping-bag') ?></div>
      <h2 style="margin:0 0 8px;font-size:1.2rem">Keranjangmu masih kosong.</h2>
      <p style="color:var(--c-muted);margin:0 0 20px">Yuk pilih menu favoritmu dan mulai pesan.</p>
      <a class="ec-btn ec-btn-dark" href="<?=APP_URL?>/index.php?r=order/menu">Lihat menu</a>
    </div>
  <?php else: ?>
  <div class="ec-cart-layout">
    <div>
      <div class="ec-cart-list">
        <?php foreach($items as $i): $inactive = !(int)$i['status_aktif'];
?>
          <article class="ec-cart-row" style="<?= $inactive ? 'opacity:.6' : '' ?>">
            <div class="ec-cart-thumb">
              <?php if(!empty($i['foto'])): ?><img src="<?=APP_URL.'/'.e($i['foto'])?>" alt="<?=e($i['nama_produk'])?>"><?php else: ?><span><?=e($i['kategori'])?></span><?php endif;
?>
            </div>
            <div class="ec-cart-info">
              <strong><?=e($i['nama_produk'])?></strong>
              <small>
                <?=rupiah($i['harga'])?>
                <?php if(!empty($i['topping_name'])): ?> · <?=e($i['topping_name'])?> (+<?=rupiah($i['harga_tambahan'])?>)<?php endif;
?>
                <?php if($i['level_pedas'] !== null): ?> · Level <?=(int)$i['level_pedas']?><?php endif;
?>
              </small>
              <?php if($inactive): ?><small style="color:var(--c-danger);font-weight:700">Produk sedang tidak tersedia. Hapus untuk melanjutkan.</small><?php endif;
?>
              <div style="display:flex;align-items:center;gap:14px;margin-top:10px;flex-wrap:wrap">
                <form method="post" action="<?=APP_URL?>/index.php?r=cart/update" class="ec-qty">
                  <?=csrf_field()?>
                  <input type="hidden" name="item_id" value="<?=$i['id']?>">
                  <button type="submit" name="qty" value="<?=max(0,
(int)$i['qty']-1)?>" aria-label="Kurangi jumlah"><?= icon('minus') ?></button>
                  <span class="ec-qty-value"><?=(int)$i['qty']?></span>
                  <button type="submit" name="qty" value="<?=(int)$i['qty']+1?>" aria-label="Tambah jumlah"><?= icon('plus') ?></button>
                </form>
                <span class="ec-cart-price"><?=rupiah($i['unit_price'])?> / item</span>
              </div>
            </div>
            <div class="ec-cart-side">
              <b><?=rupiah($i['subtotal'])?></b>
              <form method="post" action="<?=APP_URL?>/index.php?r=cart/remove" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                <?=csrf_field()?>
                <input type="hidden" name="item_id" value="<?=$i['id']?>">
                <button class="ec-cart-remove" type="submit">Hapus</button>
              </form>
            </div>
          </article>
        <?php endforeach;
?>
      </div>

      <?php if($promo): ?>
        <div class="ec-chat-info" style="margin-top:18px">
          <?= icon('gift') ?>
          <div>
            <strong>Voucher <?=e($promo['kode'])?></strong>
            <?=e($promo['deskripsi'] ?: 'Pakai kode ini saat checkout untuk potongan harga.')?>
          </div>
          <span class="ec-voucher-tag"><?= $promo['tipe']==='PERSEN' ? (float)$promo['nilai'].'%' : rupiah($promo['nilai']) ?></span>
        </div>
      <?php endif;
?>
    </div>

    <aside class="ec-summary">
      <h2>Ringkasan pesanan</h2>
      <div class="ec-summary-row"><span>Subtotal</span><strong><?=rupiah($subtotal)?></strong></div>
      <div class="ec-summary-row"><span>Ongkir</span><strong>Dihitung saat checkout</strong></div>
      <div class="ec-summary-row"><span>Voucher</span><strong>Dipakai saat checkout</strong></div>
      <div class="ec-summary-total"><span>Total sementara</span><strong><?=rupiah($subtotal)?></strong></div>
      <a class="ec-btn ec-btn-dark ec-btn-block" href="<?=APP_URL?>/index.php?r=order/checkout">Lanjut checkout <?= icon('arrow-right') ?></a>
      <div class="ec-summary-note">Harga sudah termasuk pajak. Ongkir final untuk delivery dikonfirmasi owner sebelum QRIS dibuat.</div>
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--c-line);font-size:.74rem;color:var(--c-muted);display:flex;gap:8px;align-items:flex-start">
        <?= icon('clock') ?><span>Jam buka <?=e($settings['jam_buka'] ?? '10.00 - 22.00')?>. Pesanan delivery maksimal <?=e(rtrim(rtrim(number_format((float)($settings['batas_jarak_km'] ?? 10),
2,
'.',
''),
'0'),
'.'))?> km dari warung.</span>
      </div>
    </aside>
  </div>
  <?php endif;
?>
</div>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
