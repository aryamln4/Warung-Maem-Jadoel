<?php
declare(strict_types=1);
/** Midtrans QRIS adapter. Never silently falls back to a fake successful payment. */
interface QrisGatewayInterface {
    public function createPayment(string $orderId,
    int $amount,
    string $customerEmail,
    string $customerName): array;
    public function verifyWebhook(array $payload): bool;
}
final class MidtransQrisGateway implements QrisGatewayInterface {
    private string $serverKey;
    private string $baseUrl;
    public function __construct() {
        $this->serverKey = trim((string)env('MIDTRANS_SERVER_KEY',
        ''));
        $mode = strtolower(trim((string)env('MIDTRANS_MODE',
        'sandbox')));
        if (!in_array($mode,
        ['sandbox',
        'production'],
        true)) {
            throw new RuntimeException('MIDTRANS_MODE harus sandbox atau production.');
        }
        if ($mode === 'production' && strtolower((string)env('APP_ENV',
        'development')) !== 'production') {
            throw new RuntimeException('Mode Production ditolak ketika APP_ENV bukan production.');
        }
        $this->baseUrl = $mode === 'production'
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com';
        if ($this->serverKey === '') throw new RuntimeException('MIDTRANS_SERVER_KEY belum diisi pada file .env.');
        if (!function_exists('curl_init')) throw new RuntimeException('Ekstensi PHP cURL belum aktif.');
    }
    public function createPayment(string $orderId,
    int $amount,
    string $customerEmail,
    string $customerName): array {
        if ($amount < 1) throw new RuntimeException('Nominal QRIS tidak valid.');
        $payload = [
        'payment_type' => 'qris',
        'transaction_details' => ['order_id' => $orderId,
        'gross_amount' => $amount],
        'customer_details' => ['first_name' => substr($customerName ?: 'Pelanggan',
        0,
        50),
        'email' => $customerEmail],
        'qris' => ['acquirer' => 'gopay']
        ];
        $response = $this->request('POST',
        '/v2/charge',
        $payload);
        $qrUrl = '';
        foreach (($response['actions'] ?? []) as $action) {
            if (($action['name'] ?? '') === 'generate-qr-code' && !empty($action['url'])) {
                $qrUrl = (string)$action['url'];
                break;
            }
        }
        if ($qrUrl === '') throw new RuntimeException('Midtrans tidak mengembalikan URL QRIS. Periksa aktivasi metode QRIS pada akun Sandbox.');
        return ['order_id' => $orderId,
        'reference' => (string)($response['transaction_id'] ?? $orderId),
        'qr_url' => $qrUrl,
        'status' => (string)($response['transaction_status'] ?? 'pending'),
        'expires_at' => null];
    }
    public function verifyWebhook(array $payload): bool {
        $orderId = (string)($payload['order_id'] ?? '');
        $statusCode = (string)($payload['status_code'] ?? '');
        $grossAmount = (string)($payload['gross_amount'] ?? '');
        $signature = (string)($payload['signature_key'] ?? '');
        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signature === '') return false;
        $expected = hash('sha512',
        $orderId . $statusCode . $grossAmount . $this->serverKey);
        return hash_equals($expected,
        $signature);
    }
    public function status(string $orderId): array {
        return $this->request('GET',
        '/v2/' . rawurlencode($orderId) . '/status');
    }
    private function request(string $method,
    string $path,
    ?array $payload = null): array {
        $ch = curl_init($this->baseUrl . $path);
        $headers = ['Accept: application/json',
        'Content-Type: application/json'];
        curl_setopt_array($ch,
        [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_USERPWD => $this->serverKey . ':',
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($payload !== null) curl_setopt($ch,
        CURLOPT_POSTFIELDS,
        json_encode($payload,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch,
        CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($body === false) throw new RuntimeException('Koneksi ke Midtrans gagal: ' . $error);
        $data = json_decode((string)$body,
        true);
        if (!is_array($data)) throw new RuntimeException('Respons Midtrans bukan JSON yang valid.');
        if ($code < 200 || $code >= 300) {
            $message = (string)($data['status_message'] ?? 'Permintaan pembayaran ditolak');
            throw new RuntimeException('Midtrans HTTP ' . $code . ': ' . $message);
        }
        return $data;
    }
}
final class QrisGatewayFactory {
    public static function make(): MidtransQrisGateway {
        $provider = strtolower(trim((string)env('PAYMENT_PROVIDER',
        'midtrans')));
        if ($provider !== 'midtrans') {
            throw new RuntimeException('PAYMENT_PROVIDER "' . $provider . '" tidak dikenal. Nilai yang didukung: midtrans.');
        }
        return new MidtransQrisGateway();
    }
    /** Diagnostik konfigurasi untuk panel admin. Tidak pernah mengembalikan isi key, hanya status terisi/tidak. */
    public static function diagnostics(): array {
        $provider = strtolower(trim((string)env('PAYMENT_PROVIDER',
        'midtrans')));
        $mode = strtolower(trim((string)env('MIDTRANS_MODE',
        'sandbox')));
        $keySet = trim((string)env('MIDTRANS_SERVER_KEY',
        '')) !== '';
        $clientKeySet = trim((string)env('MIDTRANS_CLIENT_KEY',
        '')) !== '';
        $modeValid = in_array($mode,
        ['sandbox',
        'production'],
        true);
        $curlOk = function_exists('curl_init');
        $envOk = !($mode === 'production' && strtolower((string)env('APP_ENV',
        'development')) !== 'production');
        return [
        'provider' => $provider,
        'provider_ok' => $provider === 'midtrans',
        'mode' => $mode,
        'mode_valid' => $modeValid,
        'server_key_set' => $keySet,
        'client_key_set' => $clientKeySet,
        'curl_ok' => $curlOk,
        'env_ok' => $envOk,
        'ready' => $provider === 'midtrans' && $modeValid && $envOk && $keySet && $curlOk,
        'webhook_url' => APP_URL . '/index.php?r=payment/qris-webhook',
        ];
    }
}
