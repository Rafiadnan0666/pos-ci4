<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Authentication (Breeze-style)
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/register', 'AuthController::register');
$routes->post('/register', 'AuthController::attemptRegister');
$routes->get('/logout', 'AuthController::logout');

// Store Front (E-commerce) - public
$routes->get('/', 'Catalog::index');
$routes->get('/products', 'Catalog::index');
$routes->get('product/(:any)', 'Catalog::detail/$1');

// Cart - public
$routes->get('cart', 'Cart::index');
$routes->post('cart/add', 'Cart::add');
$routes->post('cart/update', 'Cart::update');
$routes->post('cart/remove', 'Cart::remove');
$routes->get('cart/clear', 'Cart::clear');

// Checkout - requires buyer login via AuthFilter
$routes->get('checkout', 'Checkout::index', ['filter' => 'auth:buyer']);

// Shipping & Payment API endpoints
$routes->get('shipping/getCities', 'ShippingController::getCities', ['filter' => 'auth:buyer']);
$routes->post('shipping/getRates', 'ShippingController::getRates', ['filter' => 'auth:buyer']);
$routes->post('payment/createTransaction', 'PaymentController::createTransaction', ['filter' => 'auth:buyer']);

// Orders - requires login
$routes->get('order/success/(:any)', 'OrderController::success/$1', ['filter' => 'auth']);
$routes->get('order/detail/(:any)', 'OrderController::detail/$1', ['filter' => 'auth']);

// POS Dashboard - requires owner role
$routes->get('pos/login', 'Pos::login');
$routes->post('pos/authenticate', 'Pos::authenticate');
$routes->get('pos/logout', 'Pos::logout');
$routes->get('pos', 'Pos::index', ['filter' => 'auth:owner']);
$routes->get('admin/dashboard-pos', 'Pos::index', ['filter' => 'auth:owner']);
$routes->post('pos/addToCart', 'Pos::addToCart', ['filter' => 'auth:owner']);
$routes->post('pos/updateCart', 'Pos::updateCart', ['filter' => 'auth:owner']);
$routes->post('pos/removeFromCart', 'Pos::removeFromCart', ['filter' => 'auth:owner']);
$routes->post('pos/checkout', 'Pos::checkout', ['filter' => 'auth:owner']);
$routes->post('pos/clearCart', 'Pos::clearCart', ['filter' => 'auth:owner']);

// Admin Dashboard - requires owner role
$routes->get('admin/dashboard', 'Admin::dashboard', ['filter' => 'auth:owner']);
$routes->get('admin/orders', 'Admin::orders', ['filter' => 'auth:owner']);
$routes->get('admin/order/(:any)', 'Admin::orderDetail/$1', ['filter' => 'auth:owner']);
$routes->get('admin/users', 'Admin::users', ['filter' => 'auth:owner']);
$routes->get('admin/users/edit/(:num)', 'Admin::editUser/$1', ['filter' => 'auth:owner']);
$routes->post('admin/users/edit/(:num)', 'Admin::editUser/$1', ['filter' => 'auth:owner']);
$routes->get('admin/user-orders/(:num)', 'Admin::userOrders/$1', ['filter' => 'auth:owner']);
$routes->post('admin/updateOrderStatus', 'Admin::updateOrderStatus', ['filter' => 'auth:owner']);
$routes->get('admin/products', 'Admin::products', ['filter' => 'auth:owner']);
$routes->get('admin/products/create', 'Admin::createProduct', ['filter' => 'auth:owner']);
$routes->post('admin/products/create', 'Admin::createProduct', ['filter' => 'auth:owner']);
$routes->get('admin/products/edit/(:num)', 'Admin::editProduct/$1', ['filter' => 'auth:owner']);
$routes->post('admin/products/edit/(:num)', 'Admin::editProduct/$1', ['filter' => 'auth:owner']);
$routes->post('admin/products/delete/(:num)', 'Admin::deleteProduct/$1', ['filter' => 'auth:owner']);
$routes->get('admin/categories', 'Admin::categories', ['filter' => 'auth:owner']);
$routes->get('admin/categories/create', 'Admin::createCategory', ['filter' => 'auth:owner']);
$routes->post('admin/categories/create', 'Admin::createCategory', ['filter' => 'auth:owner']);
$routes->get('admin/categories/edit/(:num)', 'Admin::editCategory/$1', ['filter' => 'auth:owner']);
$routes->post('admin/categories/edit/(:num)', 'Admin::editCategory/$1', ['filter' => 'auth:owner']);
$routes->post('admin/categories/delete/(:num)', 'Admin::deleteCategory/$1', ['filter' => 'auth:owner']);
$routes->get('admin/test-api', 'Admin::testApi', ['filter' => 'auth:owner']);

// Order History (buyer)
$routes->get('orders', 'OrderController::myOrders', ['filter' => 'auth']);

// Midtrans Webhook (unauthenticated - called by Midtrans server)
$routes->post('midtrans/callback', 'MidtransCallback::index');
