<?php

namespace App\Libraries;

use Config\Midtrans as MidtransConfig;

class Midtrans
{
    private MidtransConfig $config;

    public function __construct()
    {
        $this->config = config('Midtrans');
    }

    private function getAuthHeader(): string
    {
        return 'Basic ' . base64_encode($this->config->serverKey . ':');
    }

    public function createSnapToken(array $params): array
    {
        $transactionDetails = [
            'order_id'     => $params['order_id'],
            'gross_amount' => (int) $params['gross_amount'],
        ];

        $customerDetails = [
            'first_name' => $params['customer']['name'] ?? '',
            'email'      => $params['customer']['email'] ?? '',
            'phone'      => $params['customer']['phone'] ?? '',
        ];

        $itemDetails = [];
        foreach ($params['items'] ?? [] as $item) {
            $itemDetails[] = [
                'id'       => (string) $item['id'],
                'price'    => (int) $item['price'],
                'quantity' => (int) $item['quantity'],
                'name'     => $item['name'],
            ];
        }

        $payload = [
            'transaction_details' => $transactionDetails,
            'customer_details'    => $customerDetails,
            'item_details'        => $itemDetails,
            'callbacks' => [
                'finish'   => base_url('order/success'),
                'unfinish' => base_url('checkout'),
                'error'    => base_url('checkout'),
            ],
            'enabled_payments' => [
                'credit_card',
                'gopay',
                'shopeepay',
                'qris',
                'bank_transfer',
                'echannel',
                'cstore',
                'bca_klikbca',
                'bca_klikpay',
                'bri_epay',
                'cimb_clicks',
                'danamon_online',
                'kredivo',
                'akulaku',
            ],
            'credit_card' => [
                'secure' => true,
            ],
        ];

        $url = $this->config->getSnapUrl();

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: ' . $this->getAuthHeader(),
            ],
            CURLOPT_TIMEOUT      => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $decoded = json_decode($response, true);

        if ($httpCode !== 201) {
            return [
                'success'  => false,
                'error'    => $decoded['error_messages'][0] ?? 'Failed to create Snap token',
                'response' => $decoded,
            ];
        }

        return [
            'success'      => true,
            'token'        => $decoded['token'] ?? '',
            'redirect_url' => $decoded['redirect_url'] ?? '',
        ];
    }

    public function verifyNotification(array $payload): array
    {
        $orderId      = $payload['order_id'] ?? '';
        $statusCode   = $payload['status_code'] ?? '';
        $grossAmount  = $payload['gross_amount'] ?? '';
        $serverKey    = $this->config->serverKey;
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== ($payload['signature_key'] ?? '')) {
            return ['success' => false, 'error' => 'Invalid signature key'];
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status'] ?? '';
        $paymentStatus     = 'pending';

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $paymentStatus = 'settlement';
            }
        } elseif ($transactionStatus === 'settlement') {
            $paymentStatus = 'settlement';
        } elseif ($transactionStatus === 'pending') {
            $paymentStatus = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
            $paymentStatus = $transactionStatus === 'expire' ? 'expire' : 'deny';
        }

        return [
            'success'          => true,
            'order_id'         => $orderId,
            'payment_status'   => $paymentStatus,
            'transaction_time' => $payload['transaction_time'] ?? '',
            'payment_type'     => $payload['payment_type'] ?? '',
        ];
    }

    public function getTransactionStatus(string $orderId): array
    {
        $url = $this->config->getApiUrl() . '/' . $orderId . '/status';

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: ' . $this->getAuthHeader(),
            ],
            CURLOPT_TIMEOUT      => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $decoded = json_decode($response, true);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Failed to get transaction status'];
        }

        return $this->verifyNotification($decoded);
    }
}
