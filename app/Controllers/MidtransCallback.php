<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Libraries\Midtrans;
use App\Libraries\Biteship;

class MidtransCallback extends BaseController
{
    private OrderModel $orderModel;
    private OrderItemModel $orderItemModel;
    private ProductModel $productModel;
    private Midtrans $midtrans;
    private Biteship $biteship;

    public function __construct()
    {
        $this->orderModel     = model('App\Models\OrderModel');
        $this->orderItemModel = model('App\Models\OrderItemModel');
        $this->productModel   = model('App\Models\ProductModel');
        $this->midtrans       = new Midtrans();
        $this->biteship       = new Biteship();
    }

    public function index()
    {
        $payload = $this->request->getJSON(true);

        if (!$payload) {
            log_message('error', 'Midtrans callback: No payload received');
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No payload']);
        }

        log_message('info', 'Midtrans callback received: ' . json_encode($payload));

        $verification = $this->midtrans->verifyNotification($payload);

        if (!$verification['success']) {
            log_message('error', 'Midtrans callback: Invalid signature - ' . ($verification['error'] ?? ''));
            return $this->response->setStatusCode(400)->setJSON(['error' => $verification['error']]);
        }

        $orderNumber   = $verification['order_id'];
        $paymentStatus = $verification['payment_status'];

        $order = $this->orderModel->getWithItems($orderNumber);

        if (!$order) {
            log_message('error', "Midtrans callback: Order {$orderNumber} not found");
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Order not found']);
        }

        $oldStatus = $order->payment_status;

        if ($oldStatus === 'settlement') {
            return $this->response->setJSON(['message' => 'Already processed']);
        }

        $this->orderModel->update($order->id, [
            'payment_status' => $paymentStatus,
        ]);

        if ($paymentStatus === 'settlement' && $oldStatus !== 'settlement') {
            $this->handleSettlement($order);
        }

        if (in_array($paymentStatus, ['expire', 'deny'], true)) {
            $items = $this->orderItemModel->getByOrderId($order->id);
            foreach ($items as $item) {
                $this->productModel->set('stock', "stock + {$item->quantity}", false)
                    ->where('id', $item->product_id)
                    ->update();
            }
        }

        return $this->response->setJSON(['message' => 'OK']);
    }

    private function handleSettlement($order)
    {
        $items = $this->orderItemModel->getByOrderId($order->id);

        foreach ($items as $item) {
            $this->productModel->decrementStock($item->product_id, $item->quantity);
        }

        if (!$order->courier_name || !$order->shipping_address) {
            return;
        }

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

        $addressParts = explode(',', $order->shipping_address);
        $postalCode   = trim(end($addressParts));

        $result = $this->biteship->createShipment([
            'destination_contact_name'  => $order->buyer_name ?? 'Customer',
            'destination_contact_phone' => $order->buyer_phone ?? '-',
            'destination_address'       => $order->shipping_address,
            'destination_postal_code'   => $postalCode,
            'courier_company'           => $order->courier_name,
            'courier_type'              => $order->courier_service,
            'items'                     => $shipmentItems,
        ]);

        log_message('info', 'Biteship shipment created for order ' . $order->order_number . ': ' . json_encode($result));
    }
}
