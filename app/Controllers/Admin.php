<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Models\ReviewModel;
use App\Models\ProductSizeModel;
use App\Models\ProductImageModel;
use App\Models\ProductVariantModel;
use App\Libraries\Biteship;

class Admin extends BaseController
{
    private OrderModel $orderModel;
    private OrderItemModel $orderItemModel;
    private ProductModel $productModel;
    private UserModel $userModel;
    private CategoryModel $categoryModel;
    private ReviewModel $reviewModel;
    private ProductSizeModel $sizeModel;
    private ProductImageModel $productImageModel;
    private ProductVariantModel $variantModel;

    public function __construct()
    {
        $this->orderModel       = model('App\Models\OrderModel');
        $this->orderItemModel   = model('App\Models\OrderItemModel');
        $this->productModel     = model('App\Models\ProductModel');
        $this->userModel        = model('App\Models\UserModel');
        $this->categoryModel    = model('App\Models\CategoryModel');
        $this->reviewModel      = model('App\Models\ReviewModel');
        $this->sizeModel         = model('App\Models\ProductSizeModel');
        $this->productImageModel = model('App\Models\ProductImageModel');
        $this->variantModel      = model('App\Models\ProductVariantModel');
    }

    public function dashboard()
    {
        $totalOrders   = $this->orderModel->countAll();
        $totalRevenue  = $this->orderModel->selectSum('gross_amount')->where('payment_status', 'settlement')->get()->getRow()->gross_amount ?? 0;
        $totalUsers    = $this->userModel->countAll();
        $totalProducts = $this->productModel->countAll();
        $lowStock      = $this->productModel->getLowStock(5);

        $recentOrders = $this->orderModel->select('orders.*, users.name as buyer_name, users.email as buyer_email')
            ->join('users', 'users.id = orders.buyer_id')
            ->orderBy('orders.id', 'DESC')
            ->limit(10)
            ->findAll();

        $ordersByStatus = $this->orderModel->select('payment_status, COUNT(*) as count')
            ->groupBy('payment_status')
            ->findAll();

        return view('admin/dashboard', [
            'totalOrders'    => $totalOrders,
            'totalRevenue'   => $totalRevenue,
            'totalUsers'     => $totalUsers,
            'totalProducts'  => $totalProducts,
            'lowStock'       => $lowStock,
            'recentOrders'   => $recentOrders,
            'ordersByStatus' => $ordersByStatus,
        ]);
    }

    public function orders()
    {
        $status   = $this->request->getGet('status');
        $search   = $this->request->getGet('search');
        $page     = (int) ($this->request->getGet('page') ?? 1);
        $perPage  = 20;

        $this->orderModel->select('orders.*, users.name as buyer_name, users.email as buyer_email')
            ->join('users', 'users.id = orders.buyer_id');

        if ($status) {
            $this->orderModel->where('orders.payment_status', $status);
        }
        if ($search) {
            $this->orderModel->groupStart()
                ->like('orders.order_number', $search)
                ->orLike('users.name', $search)
                ->orLike('users.email', $search)
                ->groupEnd();
        }

        $total    = $this->orderModel->countAllResults(false);
        $orders   = $this->orderModel->orderBy('orders.id', 'DESC')
            ->findAll($perPage, ($page - 1) * $perPage);

        $statuses = $this->orderModel->select('payment_status, COUNT(*) as count')
            ->groupBy('payment_status')
            ->findAll();

        return view('admin/orders', [
            'orders'   => $orders,
            'statuses' => $statuses,
            'total'    => $total,
            'perPage'  => $perPage,
            'page'     => $page,
            'selectedStatus' => $status,
            'search'   => $search,
        ]);
    }

    public function orderDetail(string $orderNumber)
    {
        $order = $this->orderModel->getWithItems($orderNumber);

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $items = $this->orderItemModel->getByOrderId($order->id);

        return view('admin/order_detail', [
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function users()
    {
        $users = $this->userModel->select('users.*, (SELECT COUNT(*) FROM orders WHERE orders.buyer_id = users.id) as order_count')
            ->orderBy('users.id', 'ASC')
            ->findAll();

        return view('admin/users', [
            'users' => $users,
        ]);
    }

    public function editUser(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'name'    => 'required|max_length[100]',
                'email'   => 'required|valid_email',
                'role'    => 'required|in_list[buyer,owner]',
                'phone'   => 'permit_empty|max_length[20]',
                'address' => 'permit_empty',
            ];

            if ($user->email !== $this->request->getPost('email')) {
                $rules['email'] .= "|is_unique[users.email,id,{$id}]";
            }

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $avatar = $user->avatar;
            $file   = $this->request->getFile('avatar');
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if ($file && $file->isValid() && $file->getSize() > 0) {
                if (in_array($file->getMimeType(), $allowedMimes, true)) {
                    if ($file->getSize() <= 2 * 1024 * 1024) {
                        $uploadDir = ROOTPATH . 'public/uploads/avatars';
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0755, true);
                        }
                        $name = $file->getRandomName();
                        $file->move($uploadDir, $name);
                        $avatar = 'uploads/avatars/' . $name;
                        if ($user->avatar && file_exists(ROOTPATH . 'public/' . $user->avatar)) {
                            @unlink(ROOTPATH . 'public/' . $user->avatar);
                        }
                    } else {
                        return redirect()->back()->withInput()->with('error', 'Avatar image must be under 2MB');
                    }
                } else {
                    return redirect()->back()->withInput()->with('error', 'Avatar must be JPG, PNG, WebP, or GIF');
                }
            }

            if ($this->request->getPost('remove_avatar')) {
                if ($user->avatar && file_exists(ROOTPATH . 'public/' . $user->avatar)) {
                    @unlink(ROOTPATH . 'public/' . $user->avatar);
                }
                $avatar = null;
            }

            $this->userModel->update($id, [
                'name'    => $this->request->getPost('name'),
                'email'   => $this->request->getPost('email'),
                'role'    => $this->request->getPost('role'),
                'phone'   => $this->request->getPost('phone') ?: null,
                'address' => $this->request->getPost('address') ?: null,
                'avatar'  => $avatar,
            ]);

            if ($id === (int) session()->get('user_id')) {
                session()->set('name', $this->request->getPost('name'));
                session()->set('avatar', $avatar);
            }

            return redirect()->to('/admin/users')->with('message', 'User updated successfully');
        }

        return view('admin/user_form', ['user' => $user]);
    }

    public function userOrders(int $userId)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $orders = $this->orderModel->select('orders.*, users.name as buyer_name')
            ->join('users', 'users.id = orders.buyer_id')
            ->where('orders.buyer_id', $userId)
            ->orderBy('orders.id', 'DESC')
            ->findAll();

        return view('admin/user_orders', [
            'user'   => $user,
            'orders' => $orders,
        ]);
    }

    // ─── Product CRUD ───────────────────────────────────────

    public function products()
    {
        $search = $this->request->getGet('search');
        $cat    = $this->request->getGet('category');

        if ($cat) {
            $this->productModel->where('category', $cat);
        }
        if ($search) {
            $this->productModel->groupStart()
                ->like('name', $search)
                ->orLike('slug', $search)
                ->groupEnd();
        }

        $products  = $this->productModel->orderBy('category', 'ASC')->orderBy('name', 'ASC')->findAll();
        $grouped   = [];
        foreach ($products as $p) {
            $grouped[$p->category][] = $p;
        }
        $categories = $this->productModel->getCategories();

        return view('admin/products', [
            'grouped'    => $grouped,
            'categories' => $categories,
            'selectedCat' => $cat,
            'search'     => $search,
        ]);
    }

    private function parseKeyValueLines(string $text): array
    {
        $lines = explode("\n", trim($text));
        $result = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            if (str_contains($line, ':')) {
                $parts = explode(':', $line, 2);
                $result[trim($parts[0])] = trim($parts[1]);
            } else {
                $result[$line] = '';
            }
        }
        return $result;
    }

    private function uploadImage($file, $existingImage = null): ?string
    {
        if (!$file || !$file->isValid()) {
            return $existingImage;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowed, true)) {
            return $existingImage;
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return $existingImage;
        }

        $uploadDir = ROOTPATH . 'public/uploads/products';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $name = $file->getRandomName();
        $file->move($uploadDir, $name);

        return 'uploads/products/' . $name;
    }

    private function handleMainImage($existingImage = null): ?string
    {
        $file = $this->request->getFile('image_file');
        $path = $this->uploadImage($file);

        if ($path && $path !== $existingImage) {
            if ($existingImage && file_exists(ROOTPATH . 'public/' . $existingImage)) {
                @unlink(ROOTPATH . 'public/' . $existingImage);
            }
            return $path;
        }

        if ($this->request->getPost('remove_image')) {
            if ($existingImage && file_exists(ROOTPATH . 'public/' . $existingImage)) {
                @unlink(ROOTPATH . 'public/' . $existingImage);
            }
            return null;
        }

        return $existingImage;
    }

    private function handleGalleryUploads(int $productId): void
    {
        $files = $this->request->getFiles();
        if (!isset($files['gallery_images'])) return;

        $galleryFiles = $files['gallery_images'];
        if (!is_array($galleryFiles)) {
            $path = $this->uploadImage($galleryFiles);
            if ($path) {
                $this->productImageModel->insert([
                    'product_id' => $productId,
                    'image'      => $path,
                    'sort_order' => 0,
                ]);
            }
            return;
        }

        $sortOrder = 0;
        foreach ($galleryFiles as $file) {
            $path = $this->uploadImage($file);
            if ($path) {
                $this->productImageModel->insert([
                    'product_id' => $productId,
                    'image'      => $path,
                    'sort_order' => $sortOrder,
                ]);
                $sortOrder++;
            }
        }
    }

    private function getProductFormData()
    {
        return [
            'name'              => $this->request->getPost('name'),
            'description'       => $this->request->getPost('description'),
            'category'          => $this->request->getPost('category'),
            'price'             => (float) $this->request->getPost('price'),
            'stock'             => (int) $this->request->getPost('stock'),
            'weight_grams'      => (int) $this->request->getPost('weight_grams'),
            'size'              => $this->request->getPost('size') ?: null,
            'color'             => $this->request->getPost('color') ?: null,
            'material'          => $this->request->getPost('material') ?: null,
            'brand'             => $this->request->getPost('brand') ?: null,
            'dimension_length'  => $this->request->getPost('dimension_length') ? (int) $this->request->getPost('dimension_length') : null,
            'dimension_width'   => $this->request->getPost('dimension_width') ? (int) $this->request->getPost('dimension_width') : null,
            'dimension_height'  => $this->request->getPost('dimension_height') ? (int) $this->request->getPost('dimension_height') : null,
            'warranty'          => $this->request->getPost('warranty') ?: null,
            'care_instructions' => $this->request->getPost('care_instructions') ?: null,
            'features'          => $this->request->getPost('features') ? json_encode(array_map('trim', explode("\n", trim($this->request->getPost('features')))), JSON_UNESCAPED_UNICODE) : null,
            'specifications'    => $this->request->getPost('specifications') ? json_encode($this->parseKeyValueLines($this->request->getPost('specifications')), JSON_UNESCAPED_UNICODE) : null,
            'video_url'         => $this->request->getPost('video_url') ?: null,
        ];
    }

    public function createProduct()
    {
        helper('text');

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'name'         => 'required|max_length[200]',
                'description'  => 'permit_empty',
                'category'     => 'required|max_length[50]',
                'price'        => 'required|numeric|greater_than[0]',
                'stock'        => 'required|integer|greater_than_equal_to[0]',
                'weight_grams' => 'required|integer|greater_than[0]',
                'size'         => 'permit_empty|max_length[50]',
                'color'        => 'permit_empty|max_length[50]',
                'material'     => 'permit_empty|max_length[100]',
                'brand'        => 'permit_empty|max_length[100]',
                'warranty'     => 'permit_empty|max_length[100]',
                'video_url'    => 'permit_empty|valid_url_strict',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $slug = url_title($this->request->getPost('name'), '-', true);

            $existing = $this->productModel->where('slug', $slug)->first();
            if ($existing) {
                $slug .= '-' . random_string('numeric', 4);
            }

            $category = $this->request->getPost('category');
            $newCat   = $this->request->getPost('new_category');
            if ($category === 'new' && !empty($newCat)) {
                $category = $newCat;
            }

            $data = $this->getProductFormData();
            $data['slug'] = $slug;
            $data['category'] = $category;
            $data['image'] = $this->handleMainImage();

            $productId = $this->productModel->insert($data);

            if ($productId) {
                $this->handleGalleryUploads($productId);
            }

            return redirect()->to('/admin/products')->with('message', 'Product created successfully');
        }

        return view('admin/product_form', [
            'product'    => null,
            'categories' => $this->productModel->getCategories(),
        ]);
    }

    public function editProduct(int $id)
    {
        helper('text');

        $product = $this->productModel->find($id);
        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'name'         => 'required|max_length[200]',
                'description'  => 'permit_empty',
                'category'     => 'required|max_length[50]',
                'price'        => 'required|numeric|greater_than[0]',
                'stock'        => 'required|integer|greater_than_equal_to[0]',
                'weight_grams' => 'required|integer|greater_than[0]',
                'size'         => 'permit_empty|max_length[50]',
                'color'        => 'permit_empty|max_length[50]',
                'material'     => 'permit_empty|max_length[100]',
                'brand'        => 'permit_empty|max_length[100]',
                'warranty'     => 'permit_empty|max_length[100]',
                'video_url'    => 'permit_empty|valid_url_strict',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $category = $this->request->getPost('category');
            $newCat   = $this->request->getPost('new_category');
            if ($category === 'new' && !empty($newCat)) {
                $category = $newCat;
            }

            $data = $this->getProductFormData();
            $data['category'] = $category;
            $data['image'] = $this->handleMainImage($product->image);

            $newSlug = url_title($this->request->getPost('name'), '-', true);
            $existing = $this->productModel->where('slug', $newSlug)->where('id !=', $id)->first();
            $data['slug'] = $existing ? $newSlug . '-' . random_string('numeric', 4) : $newSlug;

            $this->productModel->update($id, $data);

            if ($this->request->getPost('remove_gallery')) {
                $removeIds = explode(',', $this->request->getPost('remove_gallery'));
                foreach ($removeIds as $rid) {
                    $img = $this->productImageModel->find((int) $rid);
                    if ($img && $img->product_id == $id) {
                        if ($img->image && file_exists(ROOTPATH . 'public/' . $img->image)) {
                            @unlink(ROOTPATH . 'public/' . $img->image);
                        }
                        $this->productImageModel->delete($img->id);
                    }
                }
            }

            $this->handleGalleryUploads($id);

            return redirect()->to('/admin/products')->with('message', 'Product updated successfully');
        }

        $galleryImages = $this->productImageModel->getByProduct($id);

        return view('admin/product_form', [
            'product'       => $product,
            'categories'    => $this->productModel->getCategories(),
            'galleryImages' => $galleryImages,
        ]);
    }

    public function deleteProduct(int $id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            return $this->response->setJSON(['success' => false, 'error' => 'Not found']);
        }

        $this->productImageModel->deleteByProduct($id);

        if ($product->image && file_exists(ROOTPATH . 'public/' . $product->image)) {
            @unlink(ROOTPATH . 'public/' . $product->image);
        }

        $this->productModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->to('/admin/products')->with('message', 'Product deleted');
    }

    // ─── Category CRUD ──────────────────────────────────────

    public function categories()
    {
        $categories = $this->categoryModel->orderBy('name', 'ASC')->findAll();

        return view('admin/categories', [
            'categories' => $categories,
        ]);
    }

    public function createCategory()
    {
        helper('text');

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'name' => 'required|max_length[50]|is_unique[categories.name]',
                'icon' => 'permit_empty|max_length[20]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $name = $this->request->getPost('name');
            $slug = url_title($name, '-', true);

            $existing = $this->categoryModel->where('slug', $slug)->first();
            if ($existing) {
                $slug .= '-' . random_string('numeric', 3);
            }

            $this->categoryModel->insert([
                'name' => $name,
                'slug' => $slug,
                'icon' => $this->request->getPost('icon') ?? '📦',
            ]);

            return redirect()->to('/admin/categories')->with('message', 'Category created');
        }

        return view('admin/category_form', ['category' => null]);
    }

    public function editCategory(int $id)
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'name' => 'required|max_length[50]',
                'icon' => 'permit_empty|max_length[20]',
            ];

            if ($category->name !== $this->request->getPost('name')) {
                $rules['name'] .= '|is_unique[categories.name]';
            }

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $slug = url_title($this->request->getPost('name'), '-', true);
            $existing = $this->categoryModel->where('slug', $slug)->where('id !=', $id)->first();
            if ($existing) {
                $slug .= '-' . random_string('numeric', 3);
            }

            $this->categoryModel->update($id, [
                'name' => $this->request->getPost('name'),
                'slug' => $slug,
                'icon' => $this->request->getPost('icon') ?? '📦',
            ]);

            return redirect()->to('/admin/categories')->with('message', 'Category updated');
        }

        return view('admin/category_form', ['category' => $category]);
    }

    public function deleteCategory(int $id)
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return $this->response->setJSON(['success' => false, 'error' => 'Not found']);
        }

        $this->categoryModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->to('/admin/categories')->with('message', 'Category deleted');
    }

    public function updateOrderStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $orderId = (int) $this->request->getPost('order_id');
        $status  = $this->request->getPost('status');

        $allowed = ['pending', 'settlement', 'expire', 'deny'];
        if (!in_array($status, $allowed)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid status']);
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'error' => 'Order not found']);
        }

        $this->orderModel->update($orderId, ['payment_status' => $status]);

        if ($status === 'settlement' && $order->payment_status !== 'settlement') {
            $this->processOrderSettlement($order);
        }

        return $this->response->setJSON(['success' => true]);
    }

    private function processOrderSettlement($order)
    {
        $items = $this->orderItemModel->getByOrderId($order->id);

        foreach ($items as $item) {
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
        $biteship = new Biteship();

        $result = $biteship->createShipment([
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
                $this->orderModel->update($order->id, $update);
            }
        }

        log_message('info', "Admin Biteship shipment for order {$order->order_number}: " . json_encode($result));
    }

    // ─── Review Management ─────────────────────────────────

    public function reviews()
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $reviews = $this->reviewModel->getAllWithProduct(20, $page);
        $pager   = $this->reviewModel->pager;

        return view('admin/reviews', [
            'reviews' => $reviews,
            'pager'   => $pager,
        ]);
    }

    public function replyReview(int $id)
    {
        $review = $this->reviewModel
            ->select('product_reviews.*, users.name as user_name, users.avatar as user_avatar')
            ->join('users', 'users.id = product_reviews.user_id')
            ->where('product_reviews.id', $id)
            ->get()
            ->getRow();

        if (!$review) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'POST') {
            $reply = $this->request->getPost('reply');
            if (empty(trim($reply))) {
                return redirect()->back()->with('error', 'Reply cannot be empty');
            }

            $this->reviewModel->update($id, [
                'reply'      => $reply,
                'replied_at' => date('Y-m-d H:i:s'),
                'replied_by' => session()->get('user_id'),
            ]);

            return redirect()->to('/admin/reviews')->with('message', 'Reply submitted successfully');
        }

        return view('admin/review_reply', [
            'review' => $review,
        ]);
    }

    public function toggleReviewStatus(int $id)
    {
        $review = $this->reviewModel->find($id);
        if (!$review) {
            return $this->response->setJSON(['success' => false, 'error' => 'Not found']);
        }

        $newStatus = $review->status === 'approved' ? 'pending' : 'approved';
        $this->reviewModel->update($id, ['status' => $newStatus]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'status' => $newStatus]);
        }

        return redirect()->back()->with('message', 'Review status updated');
    }

    public function deleteReview(int $id)
    {
        $review = $this->reviewModel->find($id);
        if (!$review) {
            return $this->response->setJSON(['success' => false, 'error' => 'Not found']);
        }

        $this->reviewModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->to('/admin/reviews')->with('message', 'Review deleted');
    }

    // ─── Product Size Management ────────────────────────────

    public function productSizes(int $productId)
    {
        $product = $this->productModel->find($productId);
        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'POST') {
            $sizes = $this->request->getPost('sizes');

            $this->sizeModel->deleteByProduct($productId);

            if (!empty($sizes) && is_array($sizes)) {
                foreach ($sizes as $sizeData) {
                    if (!empty($sizeData['size']) && isset($sizeData['stock'])) {
                        $this->sizeModel->insert([
                            'product_id' => $productId,
                            'size'       => $sizeData['size'],
                            'stock'      => (int) $sizeData['stock'],
                        ]);
                    }
                }
            }

            return redirect()->to('/admin/products')->with('message', 'Sizes updated successfully');
        }

        $sizes   = $this->sizeModel->getByProduct($productId);
        $hasSizes = !empty($sizes);

        return view('admin/product_sizes', [
            'product'  => $product,
            'sizes'    => $sizes,
            'hasSizes' => $hasSizes,
        ]);
    }

    // ─── Product Variant Management (Advanced) ───────────────

    public function productVariants(int $productId)
    {
        $product = $this->productModel->find($productId);
        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'POST') {
            $variants = $this->request->getPost('variants');

            $this->variantModel->deleteByProduct($productId);

            if (!empty($variants) && is_array($variants)) {
                foreach ($variants as $v) {
                    $attrs = [];
                    if (!empty($v['attr_names']) && !empty($v['attr_values'])) {
                        $names  = $v['attr_names'];
                        $values = $v['attr_values'];
                        foreach ($names as $i => $name) {
                            if (!empty($name) && isset($values[$i])) {
                                $attrs[trim($name)] = trim($values[$i]);
                            }
                        }
                    }
                    if (!empty($attrs)) {
                        $this->variantModel->insert([
                            'product_id' => $productId,
                            'sku'        => $v['sku'] ?? null,
                            'price'      => !empty($v['price']) ? (float) $v['price'] : null,
                            'stock'      => (int) ($v['stock'] ?? 0),
                            'image'      => $v['image'] ?? null,
                            'sort_order' => (int) ($v['sort_order'] ?? 0),
                            'attributes' => json_encode($attrs, JSON_UNESCAPED_UNICODE),
                        ]);
                    }
                }
            }

            return redirect()->to('/admin/products')->with('message', 'Variants updated successfully');
        }

        $variants = $this->variantModel->getByProduct($productId);
        $hasVariants = !empty($variants);

        // Collect all attribute names across variants
        $allAttrNames = [];
        foreach ($variants as $v) {
            $attrs = json_decode($v->attributes, true) ?? [];
            foreach (array_keys($attrs) as $k) {
                if (!in_array($k, $allAttrNames)) $allAttrNames[] = $k;
            }
        }

        return view('admin/product_variants', [
            'product'      => $product,
            'variants'     => $variants,
            'hasVariants'  => $hasVariants,
            'allAttrNames' => $allAttrNames,
        ]);
    }

    public function testApi()
    {
        $midtransConfig = config('Midtrans');
        $biteshipConfig = config('Biteship');

        $midtransKey = $midtransConfig->serverKey;
        $biteshipKey = $biteshipConfig->apiKey;

        $midtransResult = ['tested' => false, 'error' => null];
        $biteshipResult = ['tested' => false, 'error' => null];

        if (!empty($midtransKey)) {
            $midtrans = new \App\Libraries\Midtrans();
            $result = $midtrans->createSnapToken([
                'order_id'     => 'TEST-' . date('YmdHis'),
                'gross_amount' => 10000,
                'customer'     => ['name' => 'Test', 'email' => 'test@test.com', 'phone' => '08123456789'],
                'items'        => [['id' => 'TEST', 'price' => 10000, 'quantity' => 1, 'name' => 'Test Item']],
            ]);
            $midtransResult = $result;
        }

        if (!empty($biteshipKey)) {
            $biteship = new \App\Libraries\Biteship();
            $areas = $biteship->getAreas('Jakarta');
            $biteshipResult = ['tested' => !empty($areas), 'count' => is_array($areas) ? count($areas) : 0, 'areas' => $areas];
        }

        return view('admin/test_api', [
            'midtransKey'   => $midtransKey ? substr($midtransKey, 0, 10) . '...' : '(empty)',
            'midtransClient' => $midtransConfig->clientKey ? substr($midtransConfig->clientKey, 0, 10) . '...' : '(empty)',
            'midtransResult' => $midtransResult,
            'biteshipKey'   => $biteshipKey ? substr($biteshipKey, 0, 15) . '...' : '(empty)',
            'biteshipResult' => $biteshipResult,
            'envPath'        => ROOTPATH . '.env',
            'envExists'      => file_exists(ROOTPATH . '.env'),
        ]);
    }
}
