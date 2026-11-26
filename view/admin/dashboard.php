<?php $title='Dashboard Admin';
require BASE_PATH.'/view/partials/header.php';
?>
<div class="admin-content">
    <div class="stat-grid dashboard-stat-grid mb-4">
    <article class="stat-card"><span class="stat-label">Pendapatan Hari Ini</span><strong class="stat-value">Rp <?=number_format($sales,
0,
',',
'.')?></strong><span class="stat-note">Transaksi selesai hari ini</span></article>
    <article class="stat-card"><span class="stat-label">Total Transaksi Hari Ini</span><strong class="stat-value"><?=$transactionCount?></strong><span class="stat-note">Transaksi selesai hari ini</span></article>
    <article class="stat-card"><span class="stat-label">Jumlah Pelanggan di Toko</span><strong class="stat-value"><?=$customerCount?></strong><span class="stat-note">Total pelanggan aktif</span></article>
    <article class="stat-card"><span class="stat-label">Stok Terjual Hari Ini</span><strong class="stat-value"><?=$soldQty?></strong><span class="stat-note">Total unit produk terjual</span></article>
  </div>

  <section class="dashboard-top-products mb-4">
    <div class="dashboard-section-heading"><div><span class="eyebrow">Charts &amp; Visualizations</span><h2 class="h4 mb-0">Produk Terlaris</h2></div><span class="dashboard-section-note">4 produk teratas hari ini</span></div>
    <?php if($topProducts): ?>
      <?php $maxTopQty=max(array_map(static fn($p)=>(int)$p['qty_terjual'],
$topProducts));
?>
      <div class="top-products-chart" role="img" aria-label="Grafik 4 produk terlaris hari ini">
        <?php foreach($topProducts as $product): $qty=(int)$product['qty_terjual'];
$width=$maxTopQty>0?round(($qty/$maxTopQty)*100):0;
?>
          <div class="top-product-row">
            <div class="top-product-label"><span><?=e($product['nama_produk'])?></span><strong><?=$qty?></strong></div>
            <div class="top-product-track"><span class="top-product-bar" style="width:<?=$width?>%"></span></div>
          </div>
        <?php endforeach;
?>
      </div>
    <?php else: ?>
      <div class="top-products-empty">Belum ada produk terjual hari ini.</div>
    <?php endif;
?>
  </section>

  <?php if($openNotif): ?><div class="notice notice-warning mb-4"><div class="notice-title">Perlu diperhatikan</div><?php foreach($openNotif as $n): ?><div><?=e($n['pesan'])?></div><?php endforeach;
?></div><?php endif;
?>
  <section class="dashboard-running mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><div><span class="eyebrow">Pemantauan langsung</span><h2 class="h4 mb-0">Pesanan Sedang Berlangsung</h2></div></div>
    <div class="table-responsive"><table class="table align-middle dashboard-live-table"><thead><tr><th>No. Pesanan</th><th>Nama Pelanggan</th><th>Nama Produk</th><th class="text-center">Jumlah</th><th>Total Harga</th><th>Status Pesanan</th></tr></thead><tbody id="liveOrdersBody"><tr><td colspan="6" class="text-center py-4 text-muted">Memuat pesanan...</td></tr></tbody></table></div>
  </section>

</div>
<style>
.dashboard-running{background:#fff;border:1px solid #e7e9ed;border-radius:18px;padding:20px;box-shadow:0 8px 24px rgba(20,30,50,.04)}.dashboard-live-table{min-width:760px;margin:0}.dashboard-live-table thead th{font-size:.78rem;color:#68717d;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}.dashboard-live-table td{font-size:.9rem}.live-status{display:flex;align-items:center;gap:5px;min-width:245px}.live-step{font-size:.72rem;font-weight:750;padding:6px 8px;border-radius:20px;background:#edf0f3;color:#7b8490;white-space:nowrap}.live-step.is-incoming{background:#fff0f0;color:#c0392b}.live-step.is-processing{background:#fff4d8;color:#936000}.live-step.is-ready{background:#e5f6eb;color:#167342}.live-connector{height:3px;width:14px;background:#e3e7eb}.live-connector.is-done{background:#65ad7d}@media(max-width:767px){.dashboard-running{padding:14px}}
</style>
<style>
.dashboard-stat-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.dashboard-top-products{background:#fff;border:1px solid #e7e9ed;border-radius:18px;padding:20px;box-shadow:0 8px 24px rgba(20,30,50,.04)}.dashboard-section-heading{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:22px}.dashboard-section-note{font-size:.8rem;color:#68717d}.top-products-chart{display:flex;flex-direction:column;gap:18px}.top-product-row{display:grid;grid-template-columns:minmax(180px,240px) 1fr;gap:16px;align-items:center}.top-product-label{display:flex;justify-content:space-between;gap:12px;font-size:.9rem;font-weight:600}.top-product-label span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.top-product-label strong{font-variant-numeric:tabular-nums}.top-product-track{height:16px;background:#f0f1f3;border-radius:999px;overflow:hidden}.top-product-bar{display:block;height:100%;border-radius:999px;background:var(--accent)}.top-products-empty{padding:24px 8px;text-align:center;color:#68717d}@media(max-width:1100px){.dashboard-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:767px){.dashboard-stat-grid{grid-template-columns:1fr}.dashboard-section-heading{align-items:flex-start;flex-direction:column}.top-product-row{grid-template-columns:1fr;gap:7px}.top-product-label{font-size:.85rem}.dashboard-top-products{padding:14px}}
</style>
<script>(function(){
 let previousSignature='';
 async function pollOrders(){
   try{
     const r=await fetch('<?=APP_URL?>/index.php?r=admin/running',{cache:'no-store'});
     const d=await r.json();
     
     const signature=JSON.stringify((d.orders||[]).map(o=>[o.id,o.status_pesanan,o.status_pembayaran]));
     if(signature!==previousSignature){
       document.getElementById('liveOrdersBody').innerHTML=d.html||'';
       const summary=document.getElementById('orderSummary'); if(summary && d.statusCounts) summary.textContent=[d.statusCounts.masuk,d.statusCounts.proses,d.statusCounts.siap].join(' - ');
       previousSignature=signature;
     }
   }catch(e){}
 }
 pollOrders(); setInterval(pollOrders,2000);
})();
</script>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
