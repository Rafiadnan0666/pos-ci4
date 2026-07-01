<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Libraries\Midtrans;

class Pos extends BaseController
{
    private ProductModel $productModel;
    private ProductVariantModel $variantModel;

    public function __construct()
    {
        $this->productModel = model('App\Models\ProductModel');
        $this->variantModel = model('App\Models\ProductVariantModel');
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

        // Load variants for all products
        $productIds = array_map(fn($p) => $p->id, $products);
        $variantsByProduct = [];
        $variantAttrsByProduct = [];
        if (!empty($productIds)) {
            $allVariants = $this->variantModel
                ->whereIn('product_id', $productIds)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();
            foreach ($allVariants as $v) {
                $variantsByProduct[$v->product_id][] = $v;
                $vAttrs = json_decode($v->attributes ?? '{}', true) ?? [];
                if (!isset($variantAttrsByProduct[$v->product_id])) {
                    $variantAttrsByProduct[$v->product_id] = [];
                }
                foreach ($vAttrs as $k => $val) {
                    if (!isset($variantAttrsByProduct[$v->product_id][$k])) {
                        $variantAttrsByProduct[$v->product_id][$k] = [];
                    }
                    if (!in_array($val, $variantAttrsByProduct[$v->product_id][$k])) {
                        $variantAttrsByProduct[$v->product_id][$k][] = $val;
                    }
                }
            }
        }

        $grouped = [];
        foreach ($products as $product) {
            $grouped[$product->category][] = $product;
        }

        return view('pos/dashboard', [
            'grouped'              => $grouped,
            'categories'           => $categories,
            'cart'                 => $cart,
            'total'                => $this->calculateCartTotal($cart),
            'lowStock'             => $lowStock,
            'outOfStock'           => $outOfStock,
            'selectedCat'          => $category,
            'search'               => $search,
            'user'                 => session()->get('name'),
            'variantsByProduct'    => $variantsByProduct,
            'variantAttrsByProduct' => $variantAttrsByProduct,
        ]);
    }

    public function getVariants()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $productId = (int) $this->request->getPost('product_id');
        $product   = $this->productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['success' => false, 'error' => 'Product not found']);
        }

        $variants = $this->variantModel->getByProduct($productId);
        $distinctAttrs = $this->variantModel->getDistinctAttributes($productId);

        $variantData = [];
        foreach ($variants as $v) {
            $variantData[] = [
                'id'         => $v->id,
                'sku'        => $v->sku,
                'price'      => $v->price !== null ? (float) $v->price : null,
                'stock'      => (int) $v->stock,
                'image'      => $v->image,
                'attributes' => json_decode($v->attributes ?? '{}', true) ?? [],
            ];
        }

        return $this->response->setJSON([
            'success'        => true,
            'product'        => [
                'id'    => $product->id,
                'name'  => $product->name,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'image' => $product->image,
            ],
            'variants'       => $variantData,
            'distinctAttrs'  => $distinctAttrs,
        ]);
    }

    public function addToCart()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $productId = (int) $this->request->getPost('product_id');
        $variantId = (int) ($this->request->getPost('variant_id') ?? 0);
        $product   = $this->productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['success' => false, 'error' => 'Product not found']);
        }

        // Determine stock, price, and cart key
        $variant     = null;
        $finalStock  = (int) $product->stock;
        $finalPrice  = (float) $product->price;
        $variantLabel = '';
        $cartKey     = (string) $productId;

        if ($variantId > 0) {
            $variant = $this->variantModel->find($variantId);
            if ($variant && $variant->product_id == $productId) {
                $finalStock = (int) $variant->stock;
                $finalPrice = $variant->price !== null ? (float) $variant->price : $finalPrice;
                $vAttrs = json_decode($variant->attributes ?? '{}', true) ?? [];
                $attrParts = [];
                foreach ($vAttrs as $k => $v) { $attrParts[] = "$k: $v"; }
                $variantLabel = implode(', ', $attrParts);
                $cartKey = $productId . '-v' . $variantId;
            } else {
                return $this->response->setJSON(['success' => false, 'error' => 'Variant not found']);
            }
        } elseif ($product->stock < 1) {
            return $this->response->setJSON(['success' => false, 'error' => 'Out of stock']);
        }

        if ($finalStock < 1) {
            return $this->response->setJSON(['success' => false, 'error' => 'Variant out of stock']);
        }

        $cart = session()->get('pos_cart') ?? [];

        if (isset($cart[$cartKey])) {
            if ($cart[$cartKey]['quantity'] >= $finalStock) {
                return $this->response->setJSON(['success' => false, 'error' => 'Not enough stock']);
            }
            $cart[$cartKey]['quantity']++;
        } else {
            $cart[$cartKey] = [
                'cart_key'      => $cartKey,
                'product_id'    => $product->id,
                'name'          => $product->name,
                'price'         => $finalPrice,
                'weight'        => (int) $product->weight_grams,
                'quantity'      => 1,
                'image'         => $variant && $variant->image ? $variant->image : $product->image,
                'stock'         => $finalStock,
                'size'          => $product->size,
                'color'         => $product->color,
                'material'      => $product->material,
                'variant_id'    => $variantId > 0 ? $variantId : null,
                'variant_label' => $variantLabel,
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

        $cartKey  = $this->request->getPost('cart_key') ?: $this->request->getPost('product_id');
        $quantity = (int) $this->request->getPost('quantity');
        $cart     = session()->get('pos_cart') ?? [];

        if (!isset($cart[$cartKey])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Item not in cart']);
        }

        if ($quantity < 1) {
            unset($cart[$cartKey]);
        } else {
            $item = $cart[$cartKey];
            $maxStock = $item['stock'] ?? 0;
            if ($item['variant_id']) {
                $variant = $this->variantModel->find($item['variant_id']);
                if ($variant) $maxStock = $variant->stock;
            } else {
                $product = $this->productModel->find($item['product_id']);
                if ($product) $maxStock = $product->stock;
            }
            if ($quantity > $maxStock) {
                return $this->response->setJSON(['success' => false, 'error' => 'Not enough stock']);
            }
            $cart[$cartKey]['quantity'] = $quantity;
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

        $cartKey = $this->request->getPost('cart_key') ?: $this->request->getPost('product_id');
        $cart    = session()->get('pos_cart') ?? [];

        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
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
        if (!in_array($paymentMethod, ['cash', 'payment_link'], true)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'error' => 'Invalid payment method']);
            }
            return redirect()->to('/pos')->with('error', 'Invalid payment method');
        }

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
                'order_id'      => $orderId,
                'product_id'    => $item['product_id'],
                'size'          => $item['size'] ?? null,
                'variant_id'    => $item['variant_id'] ?? null,
                'variant_label' => $item['variant_label'] ?? null,
                'quantity'      => $item['quantity'],
                'price'         => $item['price'],
                'subtotal'      => $item['price'] * $item['quantity'],
            ]);

            if ($item['variant_id']) {
                $this->variantModel->where('id', $item['variant_id'])
                    ->set('stock', "stock - {$item['quantity']}", false)
                    ->update();
            } else {
                $this->productModel->decrementStock($item['product_id'], $item['quantity']);
            }
        }

        if ($paymentMethod === 'payment_link') {
            $midtrans = new Midtrans();

            $items = [];
            foreach ($cart as $item) {
                $items[] = [
                    'id'       => $item['variant_id'] ? $item['product_id'] . '-v' . $item['variant_id'] : (string) $item['product_id'],
                    'price'    => (int) $item['price'],
                    'quantity' => $item['quantity'],
                    'name'     => $item['variant_label'] ? $item['name'] . ' (' . $item['variant_label'] . ')' : $item['name'],
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
