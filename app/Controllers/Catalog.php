<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ReviewModel;
use App\Models\ProductSizeModel;
use App\Models\ProductImageModel;
use App\Models\ProductVariantModel;

class Catalog extends BaseController
{
    private ProductModel $productModel;
    private ReviewModel $reviewModel;
    private ProductSizeModel $sizeModel;
    private ProductImageModel $productImageModel;
    private ProductVariantModel $variantModel;

    public function __construct()
    {
        $this->productModel      = model('App\Models\ProductModel');
        $this->reviewModel       = model('App\Models\ReviewModel');
        $this->sizeModel         = model('App\Models\ProductSizeModel');
        $this->productImageModel = model('App\Models\ProductImageModel');
        $this->variantModel      = model('App\Models\ProductVariantModel');
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
            'grouped'     => $grouped,
            'categories'  => $categories,
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

        $sizes          = $this->sizeModel->getByProduct($product->id);
        $reviews        = $this->reviewModel->getByProduct($product->id);
        $ratingSum      = $this->reviewModel->getRatingSummary($product->id);
        $galleryImages  = $this->productImageModel->getByProduct($product->id);
        $variants       = $this->variantModel->getByProduct($product->id);
        $variantAttrs   = $this->variantModel->getDistinctAttributes($product->id);

        $features = null;
        if (!empty($product->features)) {
            $features = json_decode($product->features, true);
        }
        $specs = null;
        if (!empty($product->specifications)) {
            $specs = json_decode($product->specifications, true);
        }

        $hasReviewed = false;
        if (session()->get('isLoggedIn')) {
            $hasReviewed = $this->reviewModel->hasUserReviewed($product->id, session()->get('user_id'));
        }

        return view('catalog/detail', [
            'product'       => $product,
            'related'       => $related,
            'sizes'         => $sizes,
            'reviews'       => $reviews,
            'ratingSum'     => $ratingSum,
            'galleryImages' => $galleryImages,
            'features'      => $features,
            'specs'         => $specs,
            'hasReviewed'   => $hasReviewed,
            'variants'      => $variants,
            'variantAttrs'  => $variantAttrs,
        ]);
    }

    public function submitReview()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login to submit a review');
        }

        $rules = [
            'product_id' => 'required|integer|is_natural_no_zero',
            'rating'     => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]',
            'review'     => 'permit_empty|min_length[10]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('review_errors', $this->validator->getErrors());
        }

        $productId = (int) $this->request->getPost('product_id');
        $product   = $this->productModel->find($productId);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userId = session()->get('user_id');

        if ($this->reviewModel->hasUserReviewed($productId, $userId)) {
            return redirect()->back()->with('error', 'You have already reviewed this product');
        }

        $this->reviewModel->insert([
            'product_id' => $productId,
            'user_id'    => $userId,
            'rating'     => (int) $this->request->getPost('rating'),
            'review'     => $this->request->getPost('review'),
            'status'     => 'approved',
        ]);

        return redirect()->back()->with('message', 'Review submitted successfully!');
    }
}
