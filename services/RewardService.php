<?php
declare(strict_types=1);
final class RewardService {
    public function __construct(private PDO $db, private RewardModel $rewards) {}

    /**
     * Dipanggil hanya setelah pembayaran terverifikasi.
     * Reward gratis dicatat sebagai detail transaksi tambahan.
     */
    public function apply(int $customerId,int $txId): array {
        $this->db->beginTransaction();
        try {
            $q=$this->db->prepare("SELECT SUM(qty) FROM detail_transaksi WHERE transaksi_id=? AND is_gratis=0");
            $q->execute([$txId]); $qty=(int)$q->fetchColumn();
            $grants=[];

            if($qty>=10 && !$this->rewards->logExists($txId,'per_transaksi')){
                $bonus=$this->rewards->activeBonus('per_transaksi');
                if($bonus){
                    $this->insertFreeItem($txId,(int)$bonus['menu_id']);
                    $this->rewards->addLog($customerId,$txId,(int)$bonus['id'],'per_transaksi');
                    $grants[]=['jenis'=>'per_transaksi','nama'=>$bonus['nama_produk']];
                } else $this->notify('REWARD_TANPA_BONUS','Reward per transaksi terpicu tetapi bonus produk belum dikonfigurasi.');
            } elseif($qty<10) {
                $pool=$this->rewards->customerPool($customerId)+$qty;
                if($pool>=15){
                    $bonus=$this->rewards->activeBonus('kumulatif');
                    if($bonus){
                        $this->insertFreeItem($txId,(int)$bonus['menu_id']);
                        $this->rewards->addLog($customerId,$txId,(int)$bonus['id'],'kumulatif');
                        $pool-=15;
                        $grants[]=['jenis'=>'kumulatif','nama'=>$bonus['nama_produk']];
                    } else $this->notify('REWARD_TANPA_BONUS','Reward kumulatif terpicu tetapi bonus produk belum dikonfigurasi.');
                }
                $this->rewards->updatePool($customerId,$pool);
            }
            $this->db->commit(); return $grants;
        } catch(Throwable $e){$this->db->rollBack();throw $e;}
    }

    private function insertFreeItem(int $tx,int $menuId): void {
        $s=$this->db->prepare("SELECT harga FROM menu WHERE id=?");$s->execute([$menuId]);$price=(float)$s->fetchColumn();
        $this->db->prepare("INSERT INTO detail_transaksi(transaksi_id,menu_id,qty,harga_satuan,subtotal,is_gratis) VALUES(?,?,?,?,?,1)")
            ->execute([$tx,$menuId,1,$price,0]);
    }
    private function notify(string $jenis,string $pesan): void {
        $this->db->prepare("INSERT INTO system_notifications(jenis,pesan) VALUES(?,?)")->execute([$jenis,$pesan]);
    }
}
