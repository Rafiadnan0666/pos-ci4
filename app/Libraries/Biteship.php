<?php

namespace App\Libraries;

use Config\Biteship as BiteshipConfig;

class Biteship
{
    private BiteshipConfig $config;
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->config  = config('Biteship');
        $this->apiKey  = $this->config->apiKey;
        $this->baseUrl = $this->config->baseUrl;
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT      => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $decoded = json_decode($response, true);

        return [
            'success'  => $httpCode >= 200 && $httpCode < 300,
            'httpCode' => $httpCode,
            'data'     => $decoded ?? [],
        ];
    }

    public function getAreas(string $search = ''): array
    {
        $query = http_build_query([
            'countries' => 'ID',
            'input'     => $search,
            'type'      => 'single',
        ]);

        $result = $this->request('GET', '/areas?' . $query);

        if (!$result['success']) {
            return [];
        }

        return $result['data']['areas'] ?? [];
    }

    public function getRates(array $params): array
    {
        $payload = [
            'origin_postal_code'      => $params['origin_postal_code'] ?? $this->config->originPostalCode,
            'destination_postal_code' => $params['destination_postal_code'] ?? '',
            'couriers'                => $params['couriers'] ?? 'jne,tiki,pos,sicepat',
            'items'                   => $params['items'] ?? [],
        ];

        $result = $this->request('POST', '/rates/couriers', $payload);

        log_message('info', 'Biteship rates request payload: ' . json_encode($payload));
        log_message('info', 'Biteship rates response: ' . json_encode($result));

        if (!$result['success']) {
            $errMsg = $result['data']['error'] ?? $result['data']['message'] ?? 'Failed to get rates';
            if (!empty($result['data'])) {
                $errMsg .= ' | ' . json_encode($result['data']);
            }
            return ['success' => false, 'error' => $errMsg];
        }

        $pricing = $result['data']['pricing'] ?? [];

        if (empty($pricing)) {
            return ['success' => false, 'error' => 'No courier rates found for this destination. Try a different postal code.'];
        }

        $rates = [];
        foreach ($pricing as $rate) {
            $durationRange = $rate['shipment_duration_range'] ?? '';
            $durationUnit  = $rate['shipment_duration_unit'] ?? 'days';
            $durationText  = $durationRange ? "{$durationRange} {$durationUnit}" : '';

            $rates[] = [
                'courier_name'    => $rate['courier_name'] ?? $rate['company'] ?? '',
                'courier_code'    => $rate['courier_code'] ?? $rate['company'] ?? '',
                'service_name'    => $rate['courier_service_name'] ?? $rate['service_name'] ?? '',
                'service_code'    => $rate['courier_service_code'] ?? $rate['service_code'] ?? '',
                'shipping_fee'    => (int) ($rate['shipping_fee'] ?? $rate['price'] ?? 0),
                'duration'        => $durationRange,
                'duration_text'   => $durationText,
            ];
        }

        return ['success' => true, 'rates' => $rates];
    }

    public function createShipment(array $params): array
    {
        $payload = [
            'origin_contact_name'       => $params['origin_contact_name'] ?? 'Store Owner',
            'origin_contact_phone'      => $params['origin_contact_phone'] ?? '02112345678',
            'origin_address'            => $params['origin_address'] ?? $this->config->originAddress,
            'origin_note'               => $params['origin_note'] ?? '',
            'origin_postal_code'        => $params['origin_postal_code'] ?? $this->config->originPostalCode,
            'destination_contact_name'  => $params['destination_contact_name'] ?? '',
            'destination_contact_phone' => $params['destination_contact_phone'] ?? '',
            'destination_address'       => $params['destination_address'] ?? '',
            'destination_postal_code'   => $params['destination_postal_code'] ?? '',
            'destination_note'          => $params['destination_note'] ?? '',
            'courier_company'           => $params['courier_company'] ?? '',
            'courier_type'              => $params['courier_type'] ?? '',
            'delivery_type'             => 'now',
            'items'                     => $params['items'] ?? [],
        ];

        return $this->request('POST', '/pickup/orders', $payload);
    }
}
