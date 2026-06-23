<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Libraries\Biteship;

class OrderController extends BaseController
{
    private OrderModel $orderModel;
    private OrderItemModel $orderItemModel;
    private Biteship $biteship;

    public function __construct()
    {
        $this->orderModel     = model('App\Models\OrderModel');
        $this->orderItemModel = model('App\Models\OrderItemModel');
        $this->biteship       = new Biteship();
    }

    public function myOrders()
    {
        $userId = session()->get('user_id');

        $orders = $this->orderModel->select('orders.*, users.name as buyer_name')
            ->join('users', 'users.id = orders.buyer_id')
            ->where('orders.buyer_id', $userId)
            ->orderBy('orders.id', 'DESC')
            ->findAll();

        return view('order/my_orders', [
            'orders' => $orders,
        ]);
    }

    public function success(string $orderNumber = null)
    {
        if (!$orderNumber) {
            $orderNumber = session()->get('last_order_number');
        }

        if (!$orderNumber) {
            return redirect()->to('/');
        }

        $order = $this->orderModel->getWithItems($orderNumber);

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $items = $this->orderItemModel->getByOrderId($order->id);

        return view('order/success', [
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function detail(string $orderNumber)
    {
        $order = $this->orderModel->getWithItems($orderNumber);

        if (!$order) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'not_found']);
            }
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => $order->payment_status]);
        }

        $items = $this->orderItemModel->getByOrderId($order->id);

        return view('order/detail', [
            'order' => $order,
            'items' => $items,
        ]);
    }
}
