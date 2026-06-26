<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductVariantModel;

class Cart extends BaseController
{
    private ProductModel $productModel;
    private ProductVariantModel $variantModel;

    public function __construct()
    {
        $this->productModel = model('App\Models\ProductModel');
        $this->variantModel = model('App\Models\ProductVariantModel');
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
        $size      = $this->request->getPost('size');
        $variantId = (int) ($this->request->getPost('variant_id') ?? 0);
        $product   = $this->productModel->find($productId);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        // Handle variant-based stock/price
        $variant = null;
        $finalPrice = (float) $product->price;
        $finalStock = $product->stock;
        $variantLabel = '';

        if ($variantId > 0) {
            $variant = $this->variantModel->find($variantId);
            if ($variant && $variant->product_id == $productId) {
                $finalStock = $variant->stock;
                $finalPrice = $variant->price ? (float) $variant->price : $finalPrice;
                $vAttrs = json_decode($variant->attributes, true) ?? [];
                $attrParts = [];
                foreach ($vAttrs as $k => $v) { $attrParts[] = "$k: $v"; }
                $variantLabel = implode(', ', $attrParts);
            }
        }

        if ($finalStock < $quantity) {
            return redirect()->back()->with('error', 'Not enough stock available');
        }

        $cart = session()->get('buyer_cart') ?? [];

        $cartKey = $productId . ($variantId > 0 ? '-v' . $variantId : ($size ? '-' . $size : ''));

        if (isset($cart[$cartKey])) {
            $newQty = $cart[$cartKey]['quantity'] + $quantity;
            if ($newQty > $finalStock) {
                return redirect()->back()->with('error', 'Not enough stock available');
            }
            $cart[$cartKey]['quantity'] = $newQty;
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $finalPrice,
                'weight'     => (int) $product->weight_grams,
                'quantity'   => $quantity,
                'image'      => $variant && $variant->image ? $variant->image : $product->image,
                'slug'       => $product->slug,
                'stock'      => $finalStock,
                'size'       => $size ?: $product->size,
                'color'      => $product->color,
                'material'   => $product->material,
                'variant_id' => $variantId > 0 ? $variantId : null,
                'variant_label' => $variantLabel,
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

        $cartKey = $this->request->getPost('cart_key') ?: $this->request->getPost('product_id');
        $quantity  = (int) $this->request->getPost('quantity');
        $cart      = session()->get('buyer_cart') ?? [];

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
        $cartKey = $this->request->getPost('cart_key');
        $productId = (int) $this->request->getPost('product_id');
        if (!$cartKey && $productId) {
            $cartKey = (string) $productId;
        }
        $cart = session()->get('buyer_cart') ?? [];

        if ($cartKey && isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
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
