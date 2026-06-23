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

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['data']['error'] ?? 'Failed to get rates'];
        }

        $pricing = $result['data']['pricing'] ?? [];

        $rates = [];
        foreach ($pricing as $rate) {
            $rates[] = [
                'courier_name'    => $rate['courier_name'] ?? '',
                'courier_code'    => $rate['courier_code'] ?? '',
                'service_name'    => $rate['service_name'] ?? '',
                'service_code'    => $rate['service_code'] ?? '',
                'shipping_fee'    => (int) ($rate['price'] ?? 0),
                'duration'        => $rate['duration'] ?? '',
                'duration_text'   => $rate['duration_text'] ?? '',
            ];
        }

        return ['success' => true, 'rates' => $rates];
    }

    public function createShipment(array $params): array
    {
        $payload = [
            'origin_contact_name'       => $params['origin_contact_name'] ?? 'Store Owner',
            'origin_contact_phone'      => $params['origin_contact_phone'] ?? '02112345678',
            'origin_address'            => $params['origin_address'] ?? '',
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
