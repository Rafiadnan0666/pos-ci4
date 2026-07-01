<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Libraries\Midtrans;
use App\Libraries\Biteship;

class PaymentController extends BaseController
{
    private Midtrans $midtrans;
    private OrderModel $orderModel;
    private OrderItemModel $orderItemModel;
    private ProductModel $productModel;
    private ProductVariantModel $variantModel;
    private Biteship $biteship;

    public function __construct()
    {
        $this->midtrans       = new Midtrans();
        $this->orderModel     = model('App\Models\OrderModel');
        $this->orderItemModel = model('App\Models\OrderItemModel');
        $this->productModel   = model('App\Models\ProductModel');
        $this->variantModel   = model('App\Models\ProductVariantModel');
        $this->biteship       = new Biteship();
    }

    public function verifyStatus()
    {
        $orderNumber = $this->request->getPost('order_number');

        if (!$orderNumber) {
            return $this->response->setJSON(['success' => false]);
        }

        $order = $this->orderModel->where('order_number', $orderNumber)->first();

        if (!$order) {
            return $this->response->setJSON(['success' => false]);
        }

        if ($order->payment_status === 'settlement') {
            return $this->response->setJSON(['success' => true, 'status' => 'settlement']);
        }

        $midtransStatus = $this->midtrans->getTransactionStatus($orderNumber);

        if ($midtransStatus['success']) {
            $status = $midtransStatus['payment_status'];
            if ($status === 'settlement' && $order->payment_status !== 'settlement') {
                $this->orderModel->update($order->id, ['payment_status' => $status]);
                $this->processSettlement($order->id);
            }
            return $this->response->setJSON(['success' => true, 'status' => $status]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Midtrans API unreachable']);
    }

    public function simulatePayment()
    {
        $orderNumber = $this->request->getPost('order_number');
        if (!$orderNumber) {
            return $this->response->setJSON(['success' => false, 'error' => 'Missing order number']);
        }

        $env = env('CI_ENVIRONMENT', 'production');
        $isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        if ($isProduction || $env === 'production') {
            return $this->response->setJSON(['success' => false, 'error' => 'Only available in dev/sandbox mode']);
        }

        $order = $this->orderModel->where('order_number', $orderNumber)->first();
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'error' => 'Order not found']);
        }

        if ($order->payment_status === 'settlement') {
            return $this->response->setJSON(['success' => true, 'status' => 'settlement']);
        }

        $this->orderModel->update($order->id, ['payment_status' => 'settlement']);
        $this->processSettlement($order->id);

        return $this->response->setJSON(['success' => true, 'status' => 'settlement']);
    }

    private function processSettlement(int $orderId)
    {
        $plain = $this->orderModel->find($orderId);
        if (!$plain) return;
        $order = $this->orderModel->getWithItems($plain->order_number);
        if (!$order) return;

        $items = $this->orderItemModel->getByOrderId($orderId);
        foreach ($items as $item) {
            if (!empty($item->variant_id)) {
                $this->variantModel->where('id', $item->variant_id)
                    ->set('stock', "stock - {$item->quantity}", false)
                    ->update();
            }
            $this->productModel->decrementStock($item->product_id, $item->quantity);
        }

        if (!$order->courier_name || !$order->shipping_address) return;

        $shipmentItems = [];
        foreach ($items as $item) {
            $shipmentItems[] = [
                'name'        => $item->name,
                'description' => '',
                'value'       => (int) ($item->price * $item->quantity),
                'weight'      => $item->weight_grams * $item->quantity,
                'quantity'    => $item->quantity,
            ];
        }

        preg_match('/\b\d{5}\b/', $order->shipping_address, $matches);
        $postalCode = $matches[0] ?? '10110';
        $biteshipConfig = config('Biteship');

        $result = $this->biteship->createShipment([
            'origin_contact_name'       => $biteshipConfig->originContactName ?? 'Store Owner',
            'origin_contact_phone'      => $biteshipConfig->originContactPhone ?? '02112345678',
            'origin_address'            => $biteshipConfig->originAddress,
            'origin_postal_code'        => $biteshipConfig->originPostalCode,
            'destination_contact_name'  => $order->buyer_name ?? 'Customer',
            'destination_contact_phone' => $order->buyer_phone ?? '-',
            'destination_address'       => $order->shipping_address,
            'destination_postal_code'   => $postalCode,
            'courier_company'           => $order->courier_name,
            'courier_type'              => $order->courier_service,
            'items'                     => $shipmentItems,
        ]);

        if ($result['success']) {
            $data = $result['data'] ?? [];
            $update = [];
            if (!empty($data['id'])) {
                $update['biteship_order_id'] = $data['id'];
            }
            if (!empty($data['tracking_number'])) {
                $update['tracking_number'] = $data['tracking_number'];
            }
            if (!empty($data['tracking_url'])) {
                $update['tracking_url'] = $data['tracking_url'];
            } elseif (!empty($data['tracking_id'])) {
                $update['tracking_url'] = 'https://biteship.com/tracking/' . $data['tracking_id'];
            }
            if (!empty($update)) {
                $this->orderModel->update($orderId, $update);
            }
        }

        log_message('info', "Biteship shipment result for order {$plain->order_number}: " . json_encode($result));
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

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
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
                'order_id'      => $orderId,
                'product_id'    => $item['product_id'],
                'size'          => $item['size'] ?? null,
                'variant_id'    => $item['variant_id'] ?? null,
                'variant_label' => $item['variant_label'] ?? null,
                'quantity'      => $item['quantity'],
                'price'         => $item['price'],
                'subtotal'      => $item['price'] * $item['quantity'],
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

    public function getPayToken()
    {
        $orderNumber = $this->request->getPost('order_number');
        if (!$orderNumber) {
            return $this->response->setJSON(['success' => false, 'error' => 'Missing order number']);
        }

        $order = $this->orderModel->where('order_number', $orderNumber)->first();
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'error' => 'Order not found']);
        }

        if ($order->payment_status === 'settlement') {
            return $this->response->setJSON(['success' => false, 'error' => 'Order already settled']);
        }

        if ($order->midtrans_snap_token) {
            // Return existing token — Midtrans snap tokens are valid for the transaction duration
            return $this->response->setJSON([
                'success'    => true,
                'snap_token' => $order->midtrans_snap_token,
            ]);
        }

        // No existing token — regenerate (shouldn't normally happen)
        $items = $this->orderItemModel->getByOrderId($order->id);
        $midtransItems = [];
        foreach ($items as $item) {
            $midtransItems[] = [
                'id'       => (string) $item->product_id,
                'price'    => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name'     => $item->name ?? "Item #{$item->product_id}",
            ];
        }
        if ($order->shipping_cost > 0) {
            $midtransItems[] = [
                'id'       => 'SHIPPING',
                'price'    => (int) $order->shipping_cost,
                'quantity' => 1,
                'name'     => 'Shipping (' . ($order->courier_name ?? '') . ' - ' . ($order->courier_service ?? '') . ')',
            ];
        }

        $result = $this->midtrans->createSnapToken([
            'order_id'     => $order->order_number,
            'gross_amount' => (int) $order->gross_amount,
            'customer' => [
                'name'  => $order->buyer_name ?? session()->get('name'),
                'email' => $order->buyer_email ?? session()->get('email'),
                'phone' => $order->buyer_phone ?? '',
            ],
            'items' => $midtransItems,
        ]);

        if (!$result['success']) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => $result['error'] ?? 'Failed to create payment token',
            ]);
        }

        $this->orderModel->update($order->id, [
            'midtrans_snap_token' => $result['token'],
        ]);

        return $this->response->setJSON([
            'success'    => true,
            'snap_token' => $result['token'],
        ]);
    }
}
