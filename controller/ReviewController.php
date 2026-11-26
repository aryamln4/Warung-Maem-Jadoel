<?php
declare(strict_types=1);
class ReviewController {
    private PDO $db;
    public function __construct(){
        $this->db=Database::connection();
    }
    private function customer(): int {
        if(empty($_SESSION['customer_id'])) redirect(APP_URL.'/index.php?r=auth/login');
        return (int)$_SESSION['customer_id'];
    }
    public function create(): void {
        $cid=$this->customer();
        $id=(int)($_GET['id']??$_POST['transaksi_id']??0);
        $q=$this->db->prepare("SELECT t.id,t.status_pesanan FROM transaksi_penjualan t WHERE t.id=? AND t.pelanggan_id=?");
        $q->execute([$id,
        $cid]);
        $tx=$q->fetch();
        if(!$tx || $tx['status_pesanan']!=='SELESAI') {
            flash('error',
            'Ulasan hanya tersedia untuk transaksi milik Anda yang sudah selesai.');
            redirect(APP_URL.'/index.php?r=order/history');
        }
        $q=$this->db->prepare('SELECT * FROM ulasan_pelanggan WHERE transaksi_id=? AND pelanggan_id=?');
        $q->execute([$id,
        $cid]);
        $review=$q->fetch();
        if($_SERVER['REQUEST_METHOD']!=='POST'){
            redirect(APP_URL.'/index.php?r=order/history&review='.$id);
        }
        if($_SERVER['REQUEST_METHOD']==='POST'){
            verify_csrf();
            $rating=(int)($_POST['rating']??0);
            $comment=trim((string)($_POST['komentar']??''));
            if($rating<1||$rating>5||mb_strlen($comment)>2000) {
                flash('error',
                'Rating harus 1–5 dan komentar maksimal 2000 karakter.');
                redirect(APP_URL.'/index.php?r=review/create&id='.$id);
            }
            if($review){
                $created=strtotime($review['created_at']);
                if(time()>$created+7*86400){
                    flash('error',
                    'Masa edit ulasan 7 hari telah berakhir.');
                    redirect(APP_URL.'/index.php?r=order/history');
                }
                $s=$this->db->prepare("UPDATE ulasan_pelanggan SET rating=?, komentar=?, status_ulasan='DITAMPILKAN', updated_at=NOW() WHERE id=? AND pelanggan_id=?");
                $s->execute([$rating,
                $comment?:null,
                $review['id'],
                $cid]);
            }  else {
                try{
                    $s=$this->db->prepare("INSERT INTO ulasan_pelanggan(transaksi_id,pelanggan_id,rating,komentar,status_ulasan) VALUES(?,?,?,?,'DITAMPILKAN')");
                    $s->execute([$id,
                    $cid,
                    $rating,
                    $comment?:null]);
                } catch(PDOException $e){
                    flash('error',
                    'Ulasan untuk transaksi ini sudah pernah dibuat.');
                    redirect(APP_URL.'/index.php?r=review/mine');
                }
            }
            flash('success',
            'Ulasan berhasil disimpan dan ditampilkan.');
            redirect(APP_URL.'/index.php?r=review/mine');
        }
        $title='Beri Ulasan';
        require BASE_PATH.'/view/review/form.php';
    }
    public function mine(): void {
        $cid=$this->customer();
        $s=$this->db->prepare('SELECT u.*,t.tanggal FROM ulasan_pelanggan u JOIN transaksi_penjualan t ON t.id=u.transaksi_id WHERE u.pelanggan_id=? ORDER BY u.created_at DESC');
        $s->execute([$cid]);
        $reviews=$s->fetchAll();
        $title='Ulasan Saya';
        require BASE_PATH.'/view/review/mine.php';
    }
    public function setStatus(): void {
        if(empty($_SESSION['admin_id']))redirect(APP_URL.'/index.php?r=auth/admin-login');
        verify_csrf();
        $id=(int)($_POST['id']??0);
        $status=$_POST['status']??'';
        if(!in_array($status,
        ['DITAMPILKAN',
        'DISEMBUNYIKAN'],
        true)) {
            flash('error',
            'Status tidak valid.');
            redirect(APP_URL.'/index.php?r=admin/customers&tab=ulasan');
        }
$s=$this->db->prepare('UPDATE ulasan_pelanggan SET status_ulasan=?,updated_at=NOW() WHERE id=?');
        $s->execute([$status,
        $id]);
        flash('success',
        'Status ulasan diperbarui.');
        redirect(APP_URL.'/index.php?r=admin/customers&tab=ulasan');
    }
    public function publicList(): array {
        return $this->db->query("SELECT u.rating,u.komentar,u.created_at,COALESCE(NULLIF(TRIM(p.nama),''),NULLIF(TRIM(p.username),''),'Pelanggan') AS nama FROM ulasan_pelanggan u JOIN pelanggan p ON p.id=u.pelanggan_id WHERE u.status_ulasan='DITAMPILKAN' ORDER BY u.created_at DESC LIMIT 6")->fetchAll();
    }
}
