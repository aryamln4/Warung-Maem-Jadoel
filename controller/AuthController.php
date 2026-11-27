<?php
declare(strict_types=1);
class AuthController {
    public function __construct(private AuthModel $model) {
    }
    public function register(): void {
        if($_SERVER['REQUEST_METHOD']==='POST'){
            verify_csrf();
            $d=$_POST;
            $errors=[];
            foreach(['username',
            'nama',
            'email',
            'password',
            'password_confirmation'] as $f) if(trim($d[$f]??'')==='')$errors[]="$f wajib diisi.";
            if(!filter_var($d['email']??'',
            FILTER_VALIDATE_EMAIL))$errors[]='Email tidak valid.';
            if(strlen($d['password']??'')<8)$errors[]='Sandi minimal 8 karakter.';
            if(($d['password']??'')!==($d['password_confirmation']??''))$errors[]='Konfirmasi sandi tidak sama.';
            if($this->model->findCustomerByEmail($d['email']??''))$errors[]='Email sudah terdaftar.';
            if(!$errors){
                try{
                    $id=$this->model->createCustomer($d);
                    $_SESSION['customer_id']=$id;
                    redirect(APP_URL.'/index.php?r=order/menu');
                } catch(Throwable $e){
                    error_log('[register] '.$e->getMessage());
                    $errors[]='Registrasi gagal. Coba beberapa saat lagi atau gunakan email lain.';
                }
            }
            require BASE_PATH.'/view/auth/register.php';
            return;
        }
        require BASE_PATH.'/view/auth/register.php';
    }
    public function login(): void {
        if (!empty($_SESSION['customer_id'])) redirect(APP_URL.'/index.php?r=order/menu');
        if($_SERVER['REQUEST_METHOD']!=='POST') {
            redirect(APP_URL.'/index.php?r=landing&login=1');
        }
        if($_SERVER['REQUEST_METHOD']==='POST'){
            verify_csrf();
            // Rem sederhana: maks 5 percobaan gagal beruntun, lalu rehat 30 detik.
            $fail=$_SESSION['_login_fail']??['n'=>0,
            't'=>0];
            if($fail['n']>=5 && time()-$fail['t']<30){
                flash('error',
                'Terlalu banyak percobaan masuk yang gagal. Tunggu 30 detik, lalu coba lagi.');
                redirect(APP_URL.'/index.php?r=landing&login=1');
            }
            $email=trim($_POST['email']??'');
            $password=$_POST['password']??'';
            $u=$this->model->findCustomerByEmail($email);
            if($u && ($u['status']??'AKTIF')==='AKTIF' && password_verify($password,
            $u['password'])){
                unset($_SESSION['_login_fail']);
                session_regenerate_id(true);
                $_SESSION['customer_id']=$u['id'];
                redirect(APP_URL.'/index.php?r=order/menu');
            }
            $_SESSION['_login_fail']=['n'=>($fail['n']??0)+1,
            't'=>time()];
            flash('error',
            'Email atau sandi pelanggan salah. Periksa kembali data login Anda.');
            redirect(APP_URL.'/index.php?r=landing&login=1');
        }
    }
    public function adminLogin(): void {
        if (!empty($_SESSION['admin_id'])) redirect(APP_URL.'/index.php?r=admin/dashboard');
        if($_SERVER['REQUEST_METHOD']==='POST'){
            verify_csrf();
            $fail=$_SESSION['_admin_login_fail']??['n'=>0,
            't'=>0];
            if($fail['n']>=5 && time()-$fail['t']<30){
                $error='Terlalu banyak percobaan masuk yang gagal. Tunggu 30 detik, lalu coba lagi.';
                require BASE_PATH.'/view/auth/admin_login.php';
                return;
            }
            $email=trim($_POST['email']??'');
            $password=$_POST['password']??'';
            $s=Database::connection()->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
            $s->execute([$email]);
            $admin=$s->fetch();
            if($admin && password_verify($password,
            $admin['password'])){
                unset($_SESSION['_admin_login_fail']);
                session_regenerate_id(true);
                $_SESSION['admin_id']=$admin['id'];
                $_SESSION['username']=$admin['username']??$admin['email']??'Admin';
                redirect(APP_URL.'/index.php?r=admin/dashboard');
            }
            $_SESSION['_admin_login_fail']=['n'=>($fail['n']??0)+1,
            't'=>time()];
            $error='Email atau sandi admin salah. Periksa kembali data login Anda.';
        }
        require BASE_PATH.'/view/auth/admin_login.php';
    }
    public function profile(): void {
        if(empty($_SESSION['customer_id'])) redirect(APP_URL.'/index.php?r=auth/login');
        $id=(int)$_SESSION['customer_id'];
        $user=$this->model->findCustomer($id);
        if($_SERVER['REQUEST_METHOD']==='POST'){
            verify_csrf();
            $email=trim($_POST['email']??'');
            $nama=trim($_POST['nama']??'');
            $hp=trim($_POST['no_hp']??'');
            $errors=[];
            if($nama==='') $errors[]='Nama wajib diisi.';
            if(!filter_var($email,
            FILTER_VALIDATE_EMAIL)) $errors[]='Email tidak valid.';
            $other=$this->model->findCustomerByEmail($email);
            if($other && (int)$other['id']!==$id) $errors[]='Email sudah dipakai akun lain.';
            if(!$errors){
                $this->model->updateCustomer($id,
                ['nama'=>$nama,
                'email'=>$email,
                'no_hp'=>$hp,
                'password'=>trim($_POST['password']??'')]);
                flash('success',
                'Profil berhasil diperbarui.');
                redirect(APP_URL.'/index.php?r=auth/profile');
            }
        }
        require BASE_PATH.'/view/auth/profile.php';
    }
    public function logout(): void {
        unset($_SESSION['customer_id']);
        redirect(APP_URL.'/index.php?r=landing');
    }
    public function adminLogout(): void {
        unset($_SESSION['admin_id'],
        $_SESSION['username']);
        redirect(APP_URL.'/index.php?r=landing');
    }
    public function forgot(): void {
        if($_SERVER['REQUEST_METHOD']==='POST'){
            verify_csrf();
            $email=trim($_POST['email']??'');
            $u=$this->model->findCustomerByEmail($email);
            if($u){
                $raw=bin2hex(random_bytes(32));
                $this->model->createReset((int)$u['id'],
                hash('sha256',
                $raw),
                date('Y-m-d H:i:s',
                time()+3600));
                $link=APP_URL.'/index.php?r=auth/reset&token='.urlencode($raw);
                try {
    				(new MailerService())->sendReset(
        				$u['email'],
        				$u['nama'],
        				$link
    				);

    			error_log('[Password Reset] Email berhasil dikirim.');
			} catch (Throwable $e) {
    			error_log(
        		'[Password Reset SMTP] ' . $e->getMessage()
    			);
			}
            }
            // PRG: tetap berada di Landing Page dan buka modal lupa sandi.
            redirect(APP_URL.'/index.php?r=landing&forgot=1&sent=1');
        }
        // Akses GET langsung juga diarahkan ke Landing Page, bukan halaman auth terpisah.
        redirect(APP_URL.'/index.php?r=landing&forgot=1');
    }
    public function reset(): void {
        $raw=$_GET['token']??$_POST['token']??'';
        $reset=$this->model->findValidReset(hash('sha256',
        $raw));
        if(!$reset) {
            flash('error',
            'Token reset tidak valid atau sudah kedaluwarsa. Ajukan lupa sandi kembali.');
            redirect(APP_URL.'/index.php?r=auth/forgot');
        }
        if($_SERVER['REQUEST_METHOD']==='POST'){
            verify_csrf();
            $p=$_POST['password']??'';
            if(strlen($p)<8)$error='Sandi minimal 8 karakter.';
            elseif($p!==($_POST['password_confirmation']??''))$error='Konfirmasi sandi tidak sama.';
            else{
                $this->model->resetPassword((int)$reset['id'],
                (int)$reset['pelanggan_id'],
                $p);
                flash('success',
                'Sandi berhasil diubah.');
                redirect(APP_URL.'/index.php?r=auth/login');
            }
        }
        require BASE_PATH.'/view/auth/reset.php';
    }
}
