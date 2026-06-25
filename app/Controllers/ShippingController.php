<?php

namespace App\Controllers;

use App\Libraries\Biteship;

class ShippingController extends BaseController
{
    private Biteship $biteship;

    public function __construct()
    {
        $this->biteship = new Biteship();
    }

    public function getCities()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $search = $this->request->getGet('search') ?? '';
        $areas  = $this->biteship->getAreas($search);

        return $this->response->setJSON([
            'success' => true,
            'areas'   => $areas,
        ]);
    }

    public function getRates()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $cart       = session()->get('buyer_cart') ?? [];
        $postalCode = $this->request->getPost('postal_code');
        $courier    = $this->request->getPost('courier') ?? 'jne,tiki,pos,sicepat';

        if (empty($cart)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cart is empty']);
        }

        $items = [];
        foreach ($cart as $item) {
            $items[] = [
                'name'     => $item['name'],
                'value'    => (int) ($item['price'] * $item['quantity']),
                'weight'   => $item['weight'] * $item['quantity'],
                'quantity' => $item['quantity'],
            ];
        }

        $result = $this->biteship->getRates([
            'destination_postal_code' => $postalCode,
            'couriers'                => $courier,
            'items'                   => $items,
        ]);

        // Fallback: if Biteship returns balance error in dev mode, return mock rates
        if (!$result['success'] && str_contains($result['error'] ?? '', 'balance')) {
            $isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
            if (!$isProduction) {
                log_message('info', 'Biteship balance insufficient — returning mock rates for development');
                return $this->response->setJSON($this->getMockRates($courier, $cart));
            }
        }

        return $this->response->setJSON($result);
    }

    private function getMockRates(string $courier, array $cart): array
    {
        $subtotal = 0;
        $totalWeight = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $totalWeight += ($item['weight'] ?? 0) * $item['quantity'];
        }

        $courierList = explode(',', $courier);
        $mockServices = [
            'jne'     => [['name' => 'JNE REG', 'fee' => 15000, 'dur' => '2-3 days'], ['name' => 'JNE OKE', 'fee' => 10000, 'dur' => '4-5 days']],
            'tiki'    => [['name' => 'TIKI Reg', 'fee' => 16000, 'dur' => '2-3 days'], ['name' => 'TIKI ONS', 'fee' => 30000, 'dur' => '1 day']],
            'sicepat' => [['name' => 'SiCepat REG', 'fee' => 14000, 'dur' => '2-3 days'], ['name' => 'SiCepat BEST', 'fee' => 25000, 'dur' => '1-2 days']],
            'pos'     => [['name' => 'POS Reguler', 'fee' => 12000, 'dur' => '3-5 days'], ['name' => 'POS Kilat', 'fee' => 20000, 'dur' => '2-3 days']],
        ];

        $rates = [];
        foreach ($courierList as $code) {
            $code = trim($code);
            $services = $mockServices[$code] ?? [];
            foreach ($services as $svc) {
                $rates[] = [
                    'courier_name'  => strtoupper($code),
                    'courier_code'  => $code,
                    'service_name'  => $svc['name'],
                    'service_code'  => strtolower(str_replace(' ', '_', $svc['name'])),
                    'shipping_fee'  => $svc['fee'],
                    'duration'      => $svc['dur'],
                    'duration_text' => $svc['dur'],
                ];
            }
        }

        return ['success' => true, 'rates' => $rates, 'mock' => true];
    }
}
