<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

final class MailerService
{
    public function sendReset(
        string $to,
        string $name,
        string $link
    ): void {
        $autoload = BASE_PATH . '/vendor/autoload.php';

        if (!is_file($autoload)) {
            throw new RuntimeException(
                'PHPMailer tidak ditemukan. Folder vendor belum terpasang.'
            );
        }

        require_once $autoload;

        $host = trim((string) env('SMTP_HOST', ''));
        $username = trim((string) env('SMTP_USERNAME', ''));
        $password = (string) env('SMTP_PASSWORD', '');

        if ($host === '' || $username === '' || $password === '') {
            throw new RuntimeException(
                'Konfigurasi SMTP belum lengkap di .env'
            );
        }

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->Port = (int) env('SMTP_PORT', 587);
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPAutoTLS = true;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 15;

        $mail->setFrom(
            env('MAIL_FROM', $username),
            env('MAIL_FROM_NAME', 'Warung Maem Jadoel')
        );

        $mail->addAddress($to, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Sandi Warung Maem Jadoel';

        $mail->Body = '
            <div style="font-family:Arial,sans-serif">
                <h2>Permintaan Reset Sandi</h2>
                <p>Halo ' . htmlspecialchars(
                    $name,
                    ENT_QUOTES,
                    'UTF-8'
                ) . ',</p>

                <p>Klik tombol berikut untuk mengatur
                ulang kata sandi akun kamu.</p>

                <p>
                    <a href="' . htmlspecialchars(
                        $link,
                        ENT_QUOTES,
                        'UTF-8'
                    ) . '"
                    style="background:#7c4b2a;color:white;
                    padding:12px 20px;text-decoration:none;
                    border-radius:8px">
                    Reset Kata Sandi
                    </a>
                </p>

                <p>Tautan berlaku selama 1 jam.</p>
                <p>Jika kamu tidak meminta reset sandi,
                abaikan email ini.</p>
            </div>
        ';

        $mail->AltBody =
            "Halo $name.\n\nReset sandi melalui tautan berikut:\n$link\n\nTautan berlaku 1 jam.";

        $mail->send();
    }
}
