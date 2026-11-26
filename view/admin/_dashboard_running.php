<?php if(empty($orders)): ?>
<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada pesanan yang sedang berlangsung.</td></tr>
<?php else: foreach($orders as $o):
$qty=0;
$products=[];
foreach(($o['grouped_items']??[]) as $g){
    $qty+=(int)$g['qty'];
    $products[]=e($g['nama_produk']).' × '.(int)$g['qty'];
}
$st=$o['status_pesanan'];
if(in_array($st,
['MENUNGGU_PEMBAYARAN',
'MENUNGGU_KONFIRMASI'],
true)){
    $label='Masuk';
    $step=1;
}  elseif($st==='DIPROSES'){
    $label='Diproses';
    $step=2;
}  else {
    $label='Siap';
    $step=3;
}
?>
<tr>
 <td><strong>#<?= (int)$o['id'] ?></strong></td>
 <td><?=e($o['pelanggan'])?></td>
 <td><?=implode('<br>',
$products)?></td>
 <td class="text-center"><?= $qty ?></td>
 <td><strong>Rp <?=number_format((float)$o['total_bayar'],
0,
',',
'.')?></strong></td>
 <td><div class="live-status" aria-label="Status <?=e($label)?>"><span class="live-step <?= $step>=1?'is-active is-incoming':'' ?>">Masuk</span><span class="live-connector <?= $step>=2?'is-done':'' ?>"></span><span class="live-step <?= $step>=2?'is-active is-processing':'' ?>">Diproses</span><span class="live-connector <?= $step>=3?'is-done':'' ?>"></span><span class="live-step <?= $step>=3?'is-active is-ready':'' ?>">Siap</span></div></td>
</tr>
<?php endforeach;
endif;
?>
