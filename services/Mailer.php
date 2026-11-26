<?php
declare(strict_types=1);
use PHPMailer\PHPMailer\PHPMailer;
final class MailerService {
    public function sendReset(string $to,
    string $name,
    string $link): void {
        require_once BASE_PATH.'/vendor/autoload.php';
        $mail=new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host=env('SMTP_HOST');
        $mail->SMTPAuth=true;
        $mail->Username=env('SMTP_USERNAME');
        $mail->Password=env('SMTP_PASSWORD');
        $mail->Port=(int)env('SMTP_PORT',
        587);
        $mail->SMTPSecure=env('SMTP_ENCRYPTION',
        'tls');
        $mail->setFrom(env('MAIL_FROM',
        'no-reply@example.com'),
        env('MAIL_FROM_NAME',
        'Warung Maem Jadoel'));
        $mail->addAddress($to,
        $name);
        $mail->isHTML(true);
        $mail->Subject='Reset Sandi Warung Maem Jadoel';
        $mail->Body='<p>Halo '.e($name).',</p><p>Klik tautan berikut untuk membuat sandi baru:</p><p><a href="'.e($link).'">'.e($link).'</a></p><p>Tautan berlaku 1 jam.</p>';
        $mail->send();
    }
}
