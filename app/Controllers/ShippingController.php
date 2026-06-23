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

        return $this->response->setJSON($result);
    }
}
