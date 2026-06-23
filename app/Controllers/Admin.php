<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Libraries\Biteship;

class Admin extends BaseController
{
    private OrderModel $orderModel;
    private OrderItemModel $orderItemModel;
    private ProductModel $productModel;
    private UserModel $userModel;
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->orderModel     = model('App\Models\OrderModel');
        $this->orderItemModel = model('App\Models\OrderItemModel');
        $this->productModel   = model('App\Models\ProductModel');
        $this->userModel      = model('App\Models\UserModel');
        $this->categoryModel  = model('App\Models\CategoryModel');
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
            $file = $this->request->getFile('avatar');
            if ($file && $file->isValid() && in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true)) {
                if ($file->getSize() <= 2 * 1024 * 1024) {
                    $name = $file->getRandomName();
                    $file->move(ROOTPATH . 'public/uploads/avatars', $name);
                    $avatar = 'uploads/avatars/' . $name;
                    if ($user->avatar && file_exists(ROOTPATH . 'public/' . $user->avatar)) {
                        @unlink(ROOTPATH . 'public/' . $user->avatar);
                    }
                }
            }

            $this->userModel->update($id, [
                'name'    => $this->request->getPost('name'),
                'email'   => $this->request->getPost('email'),
                'role'    => $this->request->getPost('role'),
                'phone'   => $this->request->getPost('phone') ?: null,
                'address' => $this->request->getPost('address') ?: null,
                'avatar'  => $avatar,
            ]);

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

    private function handleImageUpload($existingImage = null): ?string
    {
        $file = $this->request->getFile('image_file');

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

        $path = 'uploads/products/' . $name;

        if ($existingImage && file_exists(ROOTPATH . 'public/' . $existingImage)) {
            @unlink(ROOTPATH . 'public/' . $existingImage);
        }

        return $path;
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

            $image = $this->handleImageUpload();

            $this->productModel->insert([
                'name'         => $this->request->getPost('name'),
                'slug'         => $slug,
                'description'  => $this->request->getPost('description'),
                'category'     => $category,
                'price'        => (float) $this->request->getPost('price'),
                'stock'        => (int) $this->request->getPost('stock'),
                'weight_grams' => (int) $this->request->getPost('weight_grams'),
                'size'         => $this->request->getPost('size') ?: null,
                'color'        => $this->request->getPost('color') ?: null,
                'material'     => $this->request->getPost('material') ?: null,
                'image'        => $image,
            ]);

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
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $category = $this->request->getPost('category');
            $newCat   = $this->request->getPost('new_category');
            if ($category === 'new' && !empty($newCat)) {
                $category = $newCat;
            }

            if ($this->request->getPost('remove_image')) {
                $image = null;
                if ($product->image && file_exists(ROOTPATH . 'public/' . $product->image)) {
                    @unlink(ROOTPATH . 'public/' . $product->image);
                }
            } else {
                $image = $this->handleImageUpload($product->image);
            }

            $data = [
                'name'         => $this->request->getPost('name'),
                'description'  => $this->request->getPost('description'),
                'category'     => $category,
                'price'        => (float) $this->request->getPost('price'),
                'stock'        => (int) $this->request->getPost('stock'),
                'weight_grams' => (int) $this->request->getPost('weight_grams'),
                'size'         => $this->request->getPost('size') ?: null,
                'color'        => $this->request->getPost('color') ?: null,
                'material'     => $this->request->getPost('material') ?: null,
                'image'        => $image,
            ];

            $newSlug = url_title($this->request->getPost('name'), '-', true);
            $existing = $this->productModel->where('slug', $newSlug)->where('id !=', $id)->first();
            $data['slug'] = $existing ? $newSlug . '-' . random_string('numeric', 4) : $newSlug;

            $this->productModel->update($id, $data);

            return redirect()->to('/admin/products')->with('message', 'Product updated successfully');
        }

        return view('admin/product_form', [
            'product'    => $product,
            'categories' => $this->productModel->getCategories(),
        ]);
    }

    public function deleteProduct(int $id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            return $this->response->setJSON(['success' => false, 'error' => 'Not found']);
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
