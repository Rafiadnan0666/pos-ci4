<?php

namespace App\Controllers;

use App\Models\ProductModel;

class Catalog extends BaseController
{
    private ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = model('App\Models\ProductModel');
    }

    public function index()
    {
        $category = $this->request->getGet('category');

        if ($category) {
            $products = $this->productModel->getByCategory($category);
        } else {
            $products = $this->productModel->orderBy('category', 'ASC')->orderBy('name', 'ASC')->findAll();
        }

        $categories = $this->productModel->getCategories();

        $grouped = [];
        foreach ($products as $product) {
            $grouped[$product->category][] = $product;
        }

        return view('catalog/index', [
            'grouped'    => $grouped,
            'categories' => $categories,
            'selectedCat' => $category,
        ]);
    }

    public function detail(string $slug)
    {
        $product = $this->productModel->where('slug', $slug)->first();

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $related = $this->productModel->where('category', $product->category)
            ->where('id !=', $product->id)
            ->orderBy('RAND()')
            ->limit(4)
            ->findAll();

        return view('catalog/detail', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}
