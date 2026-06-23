<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Checkout extends BaseController
{
    private ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = model('App\Models\ProductModel');
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login to checkout.');
        }

        $cart = session()->get('buyer_cart') ?? [];

        if (empty($cart)) {
            return redirect()->to('/cart')->with('error', 'Your cart is empty');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return view('checkout/index', [
            'cart'     => $cart,
            'subtotal' => $subtotal,
            'user'     => [
                'name'  => session()->get('name'),
                'email' => session()->get('email'),
            ],
        ]);
    }
}
