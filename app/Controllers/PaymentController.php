<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Libraries\Midtrans;

class PaymentController extends BaseController
{
    private Midtrans $midtrans;

    public function __construct()
    {
        $this->midtrans = new Midtrans();
    }

    public function createTransaction()
    {
        $cart = session()->get('buyer_cart') ?? [];

        if (empty($cart)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cart is empty']);
        }

        $rules = [
            'buyer_name'     => 'required',
            'buyer_phone'    => 'required',
            'buyer_email'    => 'permit_empty|valid_email',
            'address'        => 'required',
            'postal_code'    => 'required',
            'courier_name'   => 'required',
            'courier_service' => 'required',
            'shipping_cost'  => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Validation failed',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $subtotal    = 0;
        $totalWeight = 0;
        foreach ($cart as $item) {
            $subtotal    += $item['price'] * $item['quantity'];
            $totalWeight += ($item['weight'] ?? 0) * $item['quantity'];
        }

        $shippingCost = (float) $this->request->getPost('shipping_cost');
        $grossAmount  = $subtotal + $shippingCost;

        $orderModel     = model('App\Models\OrderModel');
        $orderItemModel = model('App\Models\OrderItemModel');

        $orderNumber = $orderModel->generateOrderNumber();

        $db = db_connect();
        $db->transBegin();

        $orderData = [
            'order_number'     => $orderNumber,
            'buyer_id'         => session()->get('user_id'),
            'shipping_address' => $this->request->getPost('address'),
            'city_id'          => $this->request->getPost('city_name') ?? $this->request->getPost('city_id') ?? null,
            'courier_name'     => $this->request->getPost('courier_name'),
            'courier_service'  => $this->request->getPost('courier_service'),
            'shipping_cost'    => $shippingCost,
            'gross_amount'     => $grossAmount,
            'payment_status'   => 'pending',
        ];

        $orderId = $orderModel->insert($orderData);

        if (!$orderId) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'error' => 'Failed to create order']);
        }

        $midtransItems = [];
        foreach ($cart as $item) {
            $orderItemModel->insert([
                'order_id'   => $orderId,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'subtotal'   => $item['price'] * $item['quantity'],
            ]);

            $midtransItems[] = [
                'id'       => $item['product_id'],
                'price'    => (int) $item['price'],
                'quantity' => $item['quantity'],
                'name'     => $item['name'],
            ];
        }

        if ($shippingCost > 0) {
            $midtransItems[] = [
                'id'       => 'SHIPPING',
                'price'    => (int) $shippingCost,
                'quantity' => 1,
                'name'     => 'Shipping (' . $this->request->getPost('courier_name') . ' - ' . $this->request->getPost('courier_service') . ')',
            ];
        }

        $result = $this->midtrans->createSnapToken([
            'order_id'     => $orderNumber,
            'gross_amount' => (int) $grossAmount,
            'customer' => [
                'name'  => $this->request->getPost('buyer_name') ?? session()->get('name'),
                'email' => $this->request->getPost('buyer_email') ?? session()->get('email'),
                'phone' => $this->request->getPost('buyer_phone'),
            ],
            'items' => $midtransItems,
        ]);

        if (!$result['success']) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'error'   => $result['error'] ?? 'Failed to initialize payment',
            ]);
        }

        $orderModel->update($orderId, [
            'midtrans_snap_token' => $result['token'],
        ]);

        $db->transCommit();

        session()->remove('buyer_cart');

        return $this->response->setJSON([
            'success'      => true,
            'order_number' => $orderNumber,
            'snap_token'   => $result['token'],
            'redirect_url' => $result['redirect_url'],
        ]);
    }
}
