# Outdoor Gear Store — POS + E-Commerce (CodeIgniter 4)

A full-featured **Point of Sale (POS)** and **E-Commerce** web application built with CodeIgniter 4, Tailwind CSS, Midtrans payment gateway, and Biteship shipping integration. Designed for an outdoor gear store selling tents, backpacks, apparel, and cooking equipment.

---

## 📋 Table of Contents

- [Architecture Overview](#-architecture-overview)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
  - [Database](#database)
  - [Midtrans (Payment Gateway)](#midtrans-payment-gateway)
  - [Biteship (Shipping)](#biteship-shipping)
- [Running Migrations](#-running-migrations)
- [User Roles](#-user-roles)
- [Feature Walkthrough](#-feature-walkthrough)
  - [Store Front (Buyer)](#store-front-buyer)
  - [Checkout & Payment Flow](#checkout--payment-flow)
  - [Admin Dashboard (Owner)](#admin-dashboard-owner)
  - [POS Dashboard](#pos-dashboard)
- [API Integrations](#-api-integrations)
  - [Midtrans Snap Flow](#midtrans-snap-flow)
  - [Midtrans Webhook Callback](#midtrans-webhook-callback)
  - [Biteship Shipping Flow](#biteship-shipping-flow)
- [File Structure](#-file-structure)
- [Troubleshooting](#-troubleshooting)
  - [Payment stuck on "pending"](#payment-stuck-on-pending)
  - [Biteship shipment not created](#biteship-shipment-not-created)
  - [Product images not appearing](#product-images-not-appearing)
  - [CSRF / AJAX issues](#csrf--ajax-issues)
- [Directory Permissions](#-directory-permissions)
- [Development vs Production](#-development-vs-production)

---

## 🏗 Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                    Web Browser                          │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐ │
│  │ Catalog  │  │   Cart   │  │ Checkout │  │ Admin  │ │
│  └──────────┘  └──────────┘  └──────────┘  └────────┘ │
└──────────────────────┬──────────────────────────────────┘
                       │ HTTP
┌──────────────────────▼──────────────────────────────────┐
│              CodeIgniter 4 Backend                       │
│  ┌──────────────────────────────────────────────────┐   │
│  │  Controllers: Catalog, Cart, Checkout, Payment,  │   │
│  │  MidtransCallback, Order, Admin, Pos, Shipping   │   │
│  ├──────────────────────────────────────────────────┤   │
│  │  Libraries: Midtrans, Biteship                   │   │
│  ├──────────────────────────────────────────────────┤   │
│  │  Models: Order, OrderItem, Product, User,        │   │
│  │          Category                                 │   │
│  ├──────────────────────────────────────────────────┤   │
│  │  Views: Tailwind CSS + AOS animations            │   │
│  └──────────────────────────────────────────────────┘   │
└─────────┬──────────────────────────┬─────────────────────┘
          │                          │
          ▼                          ▼
   ┌────────────┐          ┌──────────────────┐
   │  MySQL DB   │          │  External APIs    │
   │  pos-ci4    │          │  • Midtrans Snap  │
   └────────────┘          │  • Biteship       │
                           └──────────────────┘
```

### Tech Stack
| Component | Technology |
|-----------|-----------|
| Framework | CodeIgniter 4.6 |
| PHP       | 8.2+ |
| Database  | MySQL (via MySQLi) |
| Frontend  | Tailwind CSS (CDN), AOS animations |
| Payments  | Midtrans Snap (Sandbox/Production) |
| Shipping  | Biteship API |
| Maps      | OpenStreetMap Nominatim (city search) |

### Database Tables
| Table | Purpose |
|-------|---------|
| `users` | Buyers and owners (role: `buyer` or `owner`) |
| `categories` | Product categories (name, slug, icon) |
| `products` | Products with name, price, stock, weight, image, attributes |
| `orders` | Orders with payment status, courier info, tracking |
| `order_items` | Individual items within each order |

---

## 💻 System Requirements

- **PHP 8.2+** with extensions: `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl`, `gd`
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Composer** (for PHP dependencies)
- **Node.js** (optional — only for Tailwind if you switch from CDN)

---

## 🔧 Installation

```bash
# 1. Clone the repository
git clone <repo-url> pos-ci4
cd pos-ci4

# 2. Install PHP dependencies
composer install

# 3. Copy environment config
cp env .env

# 4. Create the database
mysql -u root -p -e "CREATE DATABASE pos-ci4 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Edit .env with your database credentials and API keys (see Configuration below)

# 6. Run migrations
php spark migrate --all

# 7. Serve the application (development)
php spark serve
```

The app will be available at **http://localhost:8080/**

---

## ⚙ Configuration

### `.env` File

Edit the `.env` file in the project root:

```ini
# Environment
CI_ENVIRONMENT = development

# App
app.baseURL = 'http://localhost:8080/'
app.storeName = 'Outdoor Gear Store'
app.storeAddress = 'Jl. Petualang No. 1, Jakarta Pusat'
app.storePhone = '02112345678'

# Database
database.default.hostname = localhost
database.default.database = pos-ci4
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306

# Encryption (generated key)
encryption.key = hex2bin:f0aa2f095c74624c68dfd1eb3051c981f15160e125723d0068bf0e75c5bc6796

# ==== API Keys ====

# Biteship (shipping)
BITESHIP_API_KEY=biteship_test.eyJ...

# Midtrans (payment)
MIDTRANS_CLIENT_KEY=SB-Mid-client-...
MIDTRANS_SERVER_KEY=SB-Mid-server-...
MIDTRANS_IS_PRODUCTION=false
```

### Database

| Setting | Value |
|---------|-------|
| Host | `localhost` (default) |
| Database | `pos-ci4` |
| Username | `root` (default) |
| Password | (empty) |
| Driver | `MySQLi` |
| Port | `3306` (default) |

### Midtrans (Payment Gateway)

**Where to configure:**
- `.env` — `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`
- `app/Config/Midtrans.php` — Snap URLs, API URLs, notification URL

**Get API Keys:**
1. Register at [Midtrans Dashboard](https://dashboard.midtrans.com/)
2. Go to **Settings → Access Keys**
3. Copy **Server Key** and **Client Key**
4. Set `MIDTRANS_IS_PRODUCTION=false` for sandbox testing

**Set Payment Notification URL in Midtrans Dashboard:**
1. Go to **Settings → Payment Settings**
2. Set **Payment Notification URL** to: `https://yourdomain.com/midtrans/callback`
3. For local development, use a tunnel like [ngrok](https://ngrok.com/):
   ```bash
   ngrok http 8080
   ```
   Then set the notification URL to: `https://your-ngrok-url.ngrok.io/midtrans/callback`

**Sandbox Testing:**
- Use test cards from [Midtrans Sandbox Docs](https://docs.midtrans.com/en/technical-reference/sandbox-test)
- For QRIS/GoPay in sandbox, use the **Simulate Payment** button on the order success page
- Or use Midtrans Dashboard → Transaction → select order → Simulate Payment

### Biteship (Shipping)

**Where to configure:**
- `.env` — `BITESHIP_API_KEY`
- `app/Config/Biteship.php` — Origin address, postal code, contact info

**Get API Keys:**
1. Register at [Biteship Dashboard](https://dashboard.biteship.com/)
2. Get your API key (starts with `biteship_test.` for sandbox or `biteship_live.` for production)
3. Update origin address details in `app/Config/Biteship.php`

**Origin Address Setup:**
```php
// app/Config/Biteship.php
public string $originAddress = 'Jl. Contoh No. 123, Jakarta Pusat';
public string $originPostalCode = '10110';
public string $originContactName = 'Store Owner';
public string $originContactPhone = '02112345678';
```

> ⚠️ The origin postal code must be a valid Indonesian postal code used by Biteship.

---

## 📦 Running Migrations

Migrations create and update the database schema. Run them with:

```bash
# Run all pending migrations
php spark migrate --all

# Rollback the last batch
php spark migrate:rollback

# See migration status
php spark migrate:status
```

**Migration files are located at:** `app/Database/Migrations/`

| Migration | Purpose |
|-----------|---------|
| `001_CreateUsersTable` | Users with roles (buyer, owner) |
| `002_CreateProductsTable` | Product catalog |
| `003_CreateOrdersTable` | Orders with payment tracking |
| `004_CreateOrderItemsTable` | Order line items |
| `005_AlterOrdersAddBuyerId` | Normalize buyer relation |
| `006_CreateCategoriesTable` | Product categories |
| `007_AlterProductsAddCategoryId` | Category FK |
| `008_AlterProductsAddAttributes` | Size, color, material columns |
| `009_AlterUsersAddProfileFields` | Phone, address, avatar |
| `010_AlterOrdersAddBiteship` | **NEW** — Biteship tracking fields |

---

## 👥 User Roles

| Role | Capabilities | Routes |
|------|-------------|--------|
| **Buyer** (default) | Browse catalog, cart, checkout, view orders | `/`, `/cart`, `/checkout`, `/orders` |
| **Owner** (admin) | All buyer features + Admin dashboard, POS, product/order/user management | `/admin/*`, `/pos` |

### Creating an Owner Account
```sql
-- Run in MySQL or via spark db:seed
INSERT INTO users (name, email, password, role) VALUES (
  'Admin',
  'admin@example.com',
  -- password_hash('password123', PASSWORD_BCRYPT) — use PHP:
  '$2y$10$...',
  'owner'
);
```

Or register as a buyer via `/register`, then update the role in the database:
```sql
UPDATE users SET role = 'owner' WHERE email = 'your@email.com';
```

---

## 🛒 Feature Walkthrough

### Store Front (Buyer)

#### Catalog (`/` or `/products`)
- Browse products grouped by category (Tents, Packs, Apparel, Cooking)
- Filter by category using the button bar
- Each card shows: image, name, attributes, price, stock status
- Click product name to view detail page

**URLs:**
| Route | Method | Description | Controller Method |
|-------|--------|-------------|-------------------|
| `/` | GET | Catalog homepage | `Catalog::index` |
| `/products` | GET | All products | `Catalog::index` |
| `/product/(:any)` | GET | Product detail | `Catalog::detail` |

#### Cart (`/cart`)
- Add products with quantity
- Update quantity inline (AJAX)
- Remove individual items or clear all
- Shows subtotal and total weight

**URLs:**
| Route | Method | Description | Controller Method |
|-------|--------|-------------|-------------------|
| `/cart` | GET | View cart | `Cart::index` |
| `/cart/add` | POST | Add item | `Cart::add` |
| `/cart/update` | POST | Update quantity | `Cart::update` |
| `/cart/remove` | POST | Remove item | `Cart::remove` |
| `/cart/clear` | GET | Clear cart | `Cart::clear` |

### Checkout & Payment Flow

The complete checkout flow:

```
Cart → Checkout → Fill Address → Select Courier → Pay → Success
```

#### Step-by-step Flow:

1. **Cart** → Click "Proceed to Checkout" → `/checkout`
2. **Fill Buyer Info**: Name, Phone, Email (optional)
3. **Search City**: Uses OpenStreetMap Nominatim API to find city, auto-fills postal code
4. **Select Courier**: JNE, TIKI, SiCepat, POS — click "Get Shipping Rates"
5. **ShippingController::getRates** calls Biteship API to get courier rates
6. **Select a rate** → total updates with shipping cost
7. **Click "PAY NOW"**
8. **PaymentController::createTransaction**:
   - Creates order in DB (status: `pending`)
   - Creates order_items in DB
   - Requests Snap token from Midtrans
   - Returns token to frontend
9. **Midtrans Snap popup** opens:
   - User selects payment method (credit card, GoPay, QRIS, bank transfer, etc.)
   - User completes payment
   - Midtrans calls webhook: `/midtrans/callback`
   - On success/failure, Snap triggers JS callbacks
10. **Redirect to success page**: `/order/success/:orderNumber`
11. **Polling**: Success page polls `payment/verifyStatus` until settlement confirmed
12. **Biteship shipment**: Created automatically when payment settles

**URLs:**
| Route | Method | Description | Controller Method |
|-------|--------|-------------|-------------------|
| `/checkout` | GET | Checkout page | `Checkout::index` |
| `/shipping/getRates` | POST | Get courier rates | `ShippingController::getRates` |
| `/payment/createTransaction` | POST | Create order + Snap token | `PaymentController::createTransaction` |
| `/payment/verifyStatus` | POST | Check Midtrans status | `PaymentController::verifyStatus` |
| `/payment/simulatePayment` | POST | **NEW** — Sandbox settlement sim | `PaymentController::simulatePayment` |
| `/midtrans/callback` | POST | Midtrans webhook | `MidtransCallback::index` |

#### Payment Statuses

| Status | Meaning | Next Step |
|--------|---------|-----------|
| `pending` | Waiting for payment | Check Midtrans dashboard or simulate |
| `settlement` | Payment confirmed | Stock decremented, Biteship shipped |
| `expire` | Payment time expired | Order cancelled, stock restored |
| `deny` | Payment denied | Contact buyer for alternative payment |

### Admin Dashboard (Owner)

Access at `/admin/dashboard` after logging in as owner.

#### Dashboard (`/admin/dashboard`)
- Total orders, revenue, users, products
- Low stock alerts (items with stock < 5)
- Recent 10 orders
- Orders by status breakdown

#### Orders Management (`/admin/orders`)
- List all orders with search and status filter
- Click any order to see detail
- Change order status manually (dropdown)
- When set to `settlement`: stock is decremented AND Biteship shipment is created automatically

**URLs:**
| Route | Method | Description |
|-------|--------|-------------|
| `/admin/dashboard` | GET | Admin dashboard |
| `/admin/orders` | GET | Order list with filters |
| `/admin/order/(:any)` | GET | Order detail |
| `/admin/updateOrderStatus` | POST | Change order status (AJAX) |

#### Product Management (`/admin/products`)
- List products grouped by category
- Create, edit, delete products
- Upload product images with **live preview** before upload
- Fields: name, description, category, price, stock, weight, size, color, material

**URLs:**
| Route | Method | Description |
|-------|--------|-------------|
| `/admin/products` | GET | Product list |
| `/admin/products/create` | GET/POST | Create product |
| `/admin/products/edit/(:num)` | GET/POST | Edit product |
| `/admin/products/delete/(:num)` | POST | Delete product |

#### Category Management (`/admin/categories`)
- CRUD for product categories with icons

#### User Management (`/admin/users`)
- List all users with order count
- Edit user profile, role, avatar upload

#### API Test Tool (`/admin/test-api`)
- Test Midtrans and Biteship API connections
- Shows truncated keys and API responses

### POS Dashboard

Access at `/pos` — a simplified point-of-sale interface for walk-in customers.

**URLs:**
| Route | Method | Description |
|-------|--------|-------------|
| `/pos/login` | GET | POS login page |
| `/pos` | GET | POS dashboard |
| `/pos/addToCart` | POST | Add item to POS cart |
| `/pos/checkout` | POST | Complete POS sale |

---

## 🔌 API Integrations

### Midtrans Snap Flow

```
┌──────────┐          ┌────────────┐          ┌──────────┐
│ Browser  │          │    App     │          │ Midtrans │
└────┬─────┘          └─────┬──────┘          └────┬─────┘
     │                      │                      │
     │  POST /payment/      │                      │
     │  createTransaction   │                      │
     ├─────────────────────►│                      │
     │                      │  Request Snap Token  │
     │                      ├─────────────────────►│
     │                      │  Return token         │
     │                      │◄─────────────────────┤
     │◄── { snap_token } ───┤                      │
     │                      │                      │
     │  Open Snap Popup     │                      │
     ├────────────────────────────────────────────►│
     │                      │                      │
     │  User pays...        │                      │
     │                      │                      │
     │  onSuccess/onPending │                      │
     │◄────────────────────────────────────────────┤
     │                      │                      │
     │  POST /payment/      │                      │
     │  verifyStatus        │                      │
     ├─────────────────────►│                      │
     │                      │  GET /v2/order/      │
     │                      │  status              │
     │                      ├─────────────────────►│
     │                      │◄── status ───────────┤
     │◄── { status } ───────┤                      │
     │                      │                      │
     │  (Server-to-server)  │                      │
     │  POST /midtrans/     │                      │
     │  callback            │                      │
     │                      │◄─────────────────────┤
     │                      │  Update order status │
     │                      │  Create Biteship     │
     │                      │  shipment            │
     └──────────────────────┴──────────────────────┘
```

### Midtrans Webhook Callback

Midtrans sends a server-to-server POST to `/midtrans/callback` after any payment status change.

**Verification process:**
1. Receives JSON payload
2. Verifies signature: `SHA512(order_id + status_code + gross_amount + server_key)`
3. Validates order exists in DB
4. Updates `payment_status` in database
5. If `settlement`: decrements stock, creates Biteship shipment
6. If `expire`/`deny`: restores stock

**Important for local development:**
Midtrans cannot reach `localhost`. Use one of:
- **ngrok**: `ngrok http 8080` → use the ngrok URL as notification URL in Midtrans dashboard
- **Simulate button**: Use the "Simulate Payment" button on the success page (sandbox only)

### Biteship Shipping Flow

```
Payment settles
       │
       ▼
ProcessSettlement / HandleSettlement
       │
       ├── Decrement stock for each item
       │
       └── If courier + address set:
               │
               ▼
        Prepare shipment payload
               │
               ▼
        POST /v1/pickup/orders (Biteship API)
               │
               ▼
        Save biteship_order_id, tracking_number
        to orders table
```

**Shipment payload structure:**
```php
[
    'origin_contact_name'       => 'Store Owner',
    'origin_contact_phone'      => '02112345678',
    'origin_address'            => 'Jl. Contoh No. 123, Jakarta Pusat',
    'origin_postal_code'        => '10110',
    'destination_contact_name'  => $order->buyer_name,
    'destination_contact_phone' => $order->buyer_phone,
    'destination_address'       => $order->shipping_address,
    'destination_postal_code'   => '40115',  // Extracted from address (regex: 5-digit)
    'courier_company'           => 'jne',
    'courier_type'              => 'reg',
    'courier_service'           => 'JNE REG',
    'items'                     => [...],
]
```

**Where shipment is created:**
1. `PaymentController::processSettlement()` — when `verifyStatus` confirms settlement
2. `MidtransCallback::handleSettlement()` — when webhook receives settlement
3. `Admin::processOrderSettlement()` — when admin manually sets status to settlement

---

## 📁 File Structure

```
pos-ci4/
├── app/
│   ├── Config/
│   │   ├── Biteship.php          # Biteship API config (origin address, keys)
│   │   ├── Midtrans.php          # Midtrans API config (keys, URLs)
│   │   ├── Routes.php            # All URL routes
│   │   └── Filters.php           # Auth filters, CSRF (disabled)
│   ├── Controllers/
│   │   ├── Admin.php             # Admin dashboard, products, orders, users
│   │   ├── AuthController.php    # Login/Register
│   │   ├── Cart.php              # Shopping cart CRUD
│   │   ├── Catalog.php           # Product listing and detail
│   │   ├── Checkout.php          # Checkout page
│   │   ├── MidtransCallback.php  # Midtrans webhook handler
│   │   ├── OrderController.php   # Buyer order history
│   │   ├── PaymentController.php # Order creation, Snap token, status verify
│   │   ├── Pos.php               # Point of Sale dashboard
│   │   └── ShippingController.php # Biteship courier rates
│   ├── Database/Migrations/      # Database schema migrations
│   ├── Libraries/
│   │   ├── Midtrans.php          # Midtrans Snap API + verification
│   │   └── Biteship.php          # Biteship API (areas, rates, shipments)
│   ├── Models/
│   │   ├── OrderModel.php        # Orders table with joins
│   │   ├── OrderItemModel.php    # Order items with product join
│   │   ├── ProductModel.php      # Products with stock management
│   │   ├── UserModel.php         # Users
│   │   └── CategoryModel.php     # Categories
│   └── Views/
│       ├── admin/                # Admin pages (dashboard, orders, products)
│       ├── cart/                 # Cart page
│       ├── catalog/              # Store front (product listing, detail)
│       ├── checkout/             # Checkout page with payment
│       ├── layout/               # Main layout (header, footer, theme)
│       ├── order/                # Order success, detail, my orders
│       ├── pos/                  # POS dashboard
│       └── auth/                 # Login/Register forms
├── public/
│   └── uploads/
│       ├── products/             # Product images
│       └── avatars/              # User avatars
├── .env                          # Environment configuration
├── composer.json                 # PHP dependencies
└── package.json                  # Node.js dependencies (if any)
```

---

## 🔍 Troubleshooting

### Payment stuck on "pending"

**Symptom:** Order shows as "pending" even after user paid. Both your DB and Midtrans dashboard show "pending".

**Causes & Fixes:**

1. **Sandbox QRIS/GoPay**: In sandbox mode, QRIS/GoPay cannot be processed normally because there's no real payment gateway. Use the **"Simulate Payment"** button on the order success page (only available in sandbox mode).

2. **Midtrans webhook not received**: If Midtrans can't reach your server (common in local dev):
   - Use **ngrok**: `ngrok http 8080`
   - Set the notification URL in Midtrans dashboard to your ngrok URL
   - Or manually change the order status in Admin panel

3. **Callback URL mismatch**: Verify your Midtrans dashboard has the correct Payment Notification URL pointing to `/midtrans/callback`

4. **Manual settlement**: Go to Admin → Orders → click the order → change status dropdown to "Settlement". This will also trigger Biteship shipment.

**Files involved:**
- `app/Controllers/PaymentController.php` — `verifyStatus()`, `simulatePayment()`, `processSettlement()`
- `app/Controllers/MidtransCallback.php` — `index()`, `handleSettlement()`
- `app/Views/order/success.php` — Status polling + simulate button
- `app/Views/checkout/index.php` — Snap callbacks (`onSuccess`, `onPending`, `onError`, `onClose`)

### Biteship shipment not created

**Symptom:** Payment settled but no Biteship shipment, tracking number is empty.

**Possible causes:**

1. **No courier selected**: Order must have `courier_name` and `shipping_address` set
2. **Invalid postal code**: Biteship needs a valid 5-digit Indonesian postal code. The app extracts it from the address using regex `\b\d{5}\b`. If the address doesn't contain a 5-digit number, it falls back to `10110`.
3. **Invalid origin config**: Check `app/Config/Biteship.php` — origin postal code and address must be valid
4. **API key expired/invalid**: Test at `/admin/test-api`
5. **CURL error**: Check `app/logs/` for Biteship error messages

**Files involved:**
- `app/Libraries/Biteship.php` — `createShipment()`
- `app/Controllers/PaymentController.php` — `processSettlement()`
- `app/Controllers/MidtransCallback.php` — `handleSettlement()`
- `app/Controllers/Admin.php` — `processOrderSettlement()`

### Product images not appearing

**Symptom:** Image placeholder shows instead of uploaded image.

**Possible causes & fixes:**

1. **Upload directory doesn't exist**: The app now creates `public/uploads/products/` automatically in `Admin::handleImageUpload()`. If images still don't appear, manually create it:
   ```bash
   mkdir -p public/uploads/products
   ```

2. **File too large**: Max upload is 2MB. Check PHP config:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

3. **Wrong file type**: Only JPG, PNG, WebP, GIF are accepted.

4. **Path mismatch**: Images are stored as `uploads/products/filename.jpg` and displayed via `base_url('uploads/products/filename.jpg')`. Make sure `app.baseURL` in `.env` is correct.

5. **No preview on upload**: After selecting a file, you'll see a live preview below the file input. If no preview shows, the file may have been rejected.

**Files involved:**
- `app/Controllers/Admin.php` — `handleImageUpload()`
- `app/Views/admin/product_form.php` — Image upload with preview
- `app/Views/admin/products.php` — Product list display

### CSRF / AJAX issues

**Symptom:** AJAX requests (cart update, order status change, payment) return 400 errors.

**Cause:** CSRF filter is currently DISABLED (`app/Config/Filters.php` line 77). If you enable it:

1. Add `X-CSRF-TOKEN` header to all AJAX requests:
   ```javascript
   headers: { 'X-CSRF-TOKEN': csrf_token }
   ```
2. Include CSRF token in all forms:
   ```php
   <?= csrf_field() ?>
   ```

---

## 📂 Directory Permissions

Ensure these directories are writable by your web server:

```
writable/       # CI4 logs, cache, sessions
public/uploads/ # Uploaded images
```

On Linux:
```bash
chmod -R 775 writable public/uploads
chown -R www-data:www-data writable public/uploads
```

On Windows (XAMPP), these are usually writable by default.

---

## 🚀 Development vs Production

| Aspect | Development | Production |
|--------|------------|------------|
| `CI_ENVIRONMENT` | `development` | `production` |
| `MIDTRANS_IS_PRODUCTION` | `false` | `true` |
| Biteship API Key | `biteship_test.*` | `biteship_live.*` |
| Error display | Detailed errors | No errors shown |
| Debug toolbar | Enabled | Disabled |
| Simulate Payment | Available | Disabled |

**Switch to production:**
```ini
# .env
CI_ENVIRONMENT = production
MIDTRANS_IS_PRODUCTION = true
BITESHIP_API_KEY = biteship_live.your_live_key_here
app.baseURL = 'https://yourdomain.com'
```

---

## 📝 Key Controllers Reference

| Controller | File | Key Methods |
|-----------|------|-------------|
| **PaymentController** | `app/Controllers/PaymentController.php` | `createTransaction()` — creates order + Snap token; `verifyStatus()` — checks Midtrans status; `simulatePayment()` — sandbox only settlement; `processSettlement()` — stock + Biteship |
| **MidtransCallback** | `app/Controllers/MidtransCallback.php` | `index()` — webhook receiver with signature verification; `handleSettlement()` — stock + Biteship on settlement |
| **Admin** | `app/Controllers/Admin.php` | `updateOrderStatus()` — manual status change + Biteship trigger; `processOrderSettlement()` — stock + Biteship for admin; `handleImageUpload()` — product image upload with directory creation |
| **ShippingController** | `app/Controllers/ShippingController.php` | `getRates()` — Biteship courier rates for checkout |
| **OrderModel** | `app/Models/OrderModel.php` | `getWithItems()` — order + user JOIN; `generateOrderNumber()` — auto-incrementing INV-YYYYMMDD-XXXX format; `updateBiteship()` — save tracking data |

---

## 🔑 Important Notes

1. **Postal Code Extraction**: The app uses regex `\b\d{5}\b` to find a 5-digit postal code in the shipping address. Make sure the address includes the postal code.

2. **Order Number Format**: `INV-YYYYMMDD-XXXX` (e.g., `INV-20250623-0001`). Sequential per day.

3. **Cart Session**: Cart data is stored in `session('buyer_cart')` with product_id as key. Cleared after successful order creation.

4. **Stock Management**: Stock is decremented when payment settles (not when order is created). If payment fails/expires, stock is restored.

5. **Image Upload Path**: Products: `public/uploads/products/`, Avatars: `public/uploads/avatars/`. Both referenced as `uploads/products/file.jpg` in the database and displayed with `base_url()`.
