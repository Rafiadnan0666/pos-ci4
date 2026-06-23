<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Cart extends BaseController
{
    private ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = model('App\Models\ProductModel');
    }

    public function index()
    {
        $cart = session()->get('buyer_cart') ?? [];

        $totalWeight = 0;
        $subtotal    = 0;

        foreach ($cart as &$item) {
            $totalWeight += ($item['weight'] ?? 0) * $item['quantity'];
            $subtotal    += $item['price'] * $item['quantity'];
        }

        return view('cart/index', [
            'cart'        => $cart,
            'totalWeight' => $totalWeight,
            'subtotal'    => $subtotal,
        ]);
    }

    public function add()
    {
        $productId = (int) $this->request->getPost('product_id');
        $quantity  = (int) ($this->request->getPost('quantity') ?? 1);
        $product   = $this->productModel->find($productId);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        if ($product->stock < $quantity) {
            return redirect()->back()->with('error', 'Not enough stock available');
        }

        $cart = session()->get('buyer_cart') ?? [];

        if (isset($cart[$productId])) {
            $newQty = $cart[$productId]['quantity'] + $quantity;
            if ($newQty > $product->stock) {
                return redirect()->back()->with('error', 'Not enough stock available');
            }
            $cart[$productId]['quantity'] = $newQty;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => (float) $product->price,
                'weight'     => (int) $product->weight_grams,
                'quantity'   => $quantity,
                'image'      => $product->image,
                'slug'       => $product->slug,
                'stock'      => $product->stock,
                'size'       => $product->size,
                'color'      => $product->color,
                'material'   => $product->material,
            ];
        }

        session()->set('buyer_cart', $cart);

        return redirect()->to('/cart')->with('message', 'Product added to cart');
    }

    public function update()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $productId = (int) $this->request->getPost('product_id');
        $quantity  = (int) $this->request->getPost('quantity');
        $cart      = session()->get('buyer_cart') ?? [];

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

        session()->set('buyer_cart', $cart);

        $totalWeight = 0;
        $subtotal    = 0;
        foreach ($cart as $item) {
            $totalWeight += ($item['weight'] ?? 0) * $item['quantity'];
            $subtotal    += $item['price'] * $item['quantity'];
        }

        return $this->response->setJSON([
            'success'     => true,
            'cart'        => $cart,
            'subtotal'    => $subtotal,
            'totalWeight' => $totalWeight,
            'count'       => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    public function remove()
    {
        $productId = (int) $this->request->getPost('product_id');
        $cart      = session()->get('buyer_cart') ?? [];

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }

        session()->set('buyer_cart', $cart);

        return redirect()->to('/cart')->with('message', 'Item removed from cart');
    }

    public function clear()
    {
        session()->remove('buyer_cart');
        return redirect()->to('/cart')->with('message', 'Cart cleared');
    }
}
