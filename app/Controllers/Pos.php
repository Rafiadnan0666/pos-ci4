<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Libraries\Midtrans;

class Pos extends BaseController
{
    private ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = model('App\Models\ProductModel');
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/pos');
        }

        return view('pos/login');
    }

    public function authenticate()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = model('App\Models\UserModel');
        $user      = $userModel->findByEmail($email);

        if (!$user || !$userModel->verifyPassword($password, $user->password)) {
            return redirect()->back()->withInput()->with('error', 'Invalid credentials.');
        }

        if ($user->role !== 'owner') {
            return redirect()->back()->with('error', 'You do not have POS access.');
        }

        session()->set([
            'isLoggedIn' => true,
            'user_id'    => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
        ]);

        return redirect()->to('/pos');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/pos/login');
    }

    public function index()
    {
        $category = $this->request->getGet('category');
        $search   = $this->request->getGet('search');

        if ($category) {
            $products = $this->productModel->getByCategory($category);
        } elseif ($search) {
            $products = $this->productModel->like('name', $search)->findAll();
        } else {
            $products = $this->productModel->orderBy('category', 'ASC')->orderBy('name', 'ASC')->findAll();
        }

        $categories   = $this->productModel->getCategories();
        $lowStock     = $this->productModel->getLowStock(5);
        $outOfStock   = $this->productModel->getOutOfStock();
        $cart         = session()->get('pos_cart') ?? [];

        $grouped = [];
        foreach ($products as $product) {
            $grouped[$product->category][] = $product;
        }

        return view('pos/dashboard', [
            'grouped'     => $grouped,
            'categories'  => $categories,
            'cart'        => $cart,
            'lowStock'    => $lowStock,
            'outOfStock'  => $outOfStock,
            'selectedCat' => $category,
            'search'      => $search,
            'user'        => session()->get('name'),
        ]);
    }

    public function addToCart()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $productId = (int) $this->request->getPost('product_id');
        $product   = $this->productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['success' => false, 'error' => 'Product not found']);
        }

        if ($product->stock < 1) {
            return $this->response->setJSON(['success' => false, 'error' => 'Out of stock']);
        }

        $cart = session()->get('pos_cart') ?? [];

        if (isset($cart[$productId])) {
            if ($cart[$productId]['quantity'] >= $product->stock) {
                return $this->response->setJSON(['success' => false, 'error' => 'Not enough stock']);
            }
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => (float) $product->price,
                'weight'     => (int) $product->weight_grams,
                'quantity'   => 1,
                'image'      => $product->image,
                'stock'      => $product->stock,
            ];
        }

        session()->set('pos_cart', $cart);

        return $this->response->setJSON([
            'success' => true,
            'cart'    => $cart,
            'total'   => $this->calculateCartTotal($cart),
            'count'   => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    public function updateCart()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $productId = (int) $this->request->getPost('product_id');
        $quantity  = (int) $this->request->getPost('quantity');
        $cart      = session()->get('pos_cart') ?? [];

        if (!isset($cart[$productId])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Item not in cart']);
        }

        if ($quantity < 1) {
            unset($cart[$productId]);
        } else {
            $product = $this->productModel->find($productId);
            if ($product && $quantity > $product->stock) {
                return $this->response->setJSON(['success' => false, 'error' => 'Not enough stock']);
            }
            $cart[$productId]['quantity'] = $quantity;
        }

        session()->set('pos_cart', $cart);

        return $this->response->setJSON([
            'success' => true,
            'cart'    => $cart,
            'total'   => $this->calculateCartTotal($cart),
            'count'   => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    public function removeFromCart()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $productId = (int) $this->request->getPost('product_id');
        $cart      = session()->get('pos_cart') ?? [];

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }

        session()->set('pos_cart', $cart);

        return $this->response->setJSON([
            'success' => true,
            'cart'    => $cart,
            'total'   => $this->calculateCartTotal($cart),
            'count'   => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    public function checkout()
    {
        $cart = session()->get('pos_cart') ?? [];

        if (empty($cart)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'error' => 'Cart is empty']);
            }
            return redirect()->to('/pos')->with('error', 'Cart is empty');
        }

        $paymentMethod = $this->request->getPost('payment_method');

        $orderModel     = model('App\Models\OrderModel');
        $orderItemModel = model('App\Models\OrderItemModel');

        $orderNumber = $orderModel->generateOrderNumber();
        $subtotal    = $this->calculateCartTotal($cart);

        $orderData = [
            'order_number'   => $orderNumber,
            'buyer_id'       => session()->get('user_id'),
            'shipping_address' => null,
            'shipping_cost'  => 0,
            'gross_amount'   => $subtotal,
            'payment_status' => $paymentMethod === 'cash' ? 'settlement' : 'pending',
        ];

        $db = db_connect();
        $db->transBegin();

        $orderId = $orderModel->insert($orderData);

        if (!$orderId) {
            $db->transRollback();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'error' => 'Failed to create order']);
            }
            return redirect()->to('/pos')->with('error', 'Failed to create order');
        }

        foreach ($cart as $item) {
            $orderItemModel->insert([
                'order_id'   => $orderId,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'subtotal'   => $item['price'] * $item['quantity'],
            ]);

            $this->productModel->decrementStock($item['product_id'], $item['quantity']);
        }

        if ($paymentMethod === 'payment_link') {
            $midtrans = new Midtrans();

            $items = [];
            foreach ($cart as $item) {
                $items[] = [
                    'id'       => $item['product_id'],
                    'price'    => (int) $item['price'],
                    'quantity' => $item['quantity'],
                    'name'     => $item['name'],
                ];
            }

            $result = $midtrans->createSnapToken([
                'order_id'     => $orderNumber,
                'gross_amount' => (int) $subtotal,
                'customer'     => [
                    'name'  => $this->request->getPost('buyer_name') ?? session()->get('name'),
                    'email' => $this->request->getPost('buyer_email') ?? session()->get('email'),
                    'phone' => $this->request->getPost('buyer_phone') ?? '-',
                ],
                'items' => $items,
            ]);

            if (!$result['success']) {
                $db->transRollback();
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'error' => $result['error'] ?? 'Payment initialization failed']);
                }
                return redirect()->to('/pos')->with('error', 'Payment failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            $orderModel->update($orderId, [
                'midtrans_snap_token' => $result['token'],
            ]);

            $db->transCommit();
            session()->remove('pos_cart');

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success'      => true,
                    'order_number' => $orderNumber,
                    'snap_token'   => $result['token'],
                ]);
            }

            return redirect()->to('/order/success/' . $orderNumber);
        }

        $db->transCommit();
        session()->remove('pos_cart');

        return redirect()->to('/pos')->with('message', "Order {$orderNumber} completed (Cash)");
    }

    private function calculateCartTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function clearCart()
    {
        session()->remove('pos_cart');
        return $this->response->setJSON(['success' => true]);
    }
}
