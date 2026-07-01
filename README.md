# Outdoor Gear Store — POS + E-Commerce

**A full-featured Point of Sale and E-Commerce web application** built for an outdoor gear store selling tents, backpacks, apparel, and cooking equipment. Supports multi-attribute product variants (Color + Size + Material), Midtrans payment gateway, Biteship shipping, and a dedicated POS dashboard for walk-in customers.

---

## Table of Contents

1. [What It Is](#what-it-is)
2. [Why This Architecture](#why-this-architecture)
3. [How It Works](#how-it-works)
   - [Tech Stack](#tech-stack)
   - [Request Flow](#request-flow)
   - [Payment Flow](#payment-flow)
   - [Variant System](#variant-system)
   - [Stock Management](#stock-management)
4. [Where Everything Lives](#where-everything-lives)
   - [Directory Structure](#directory-structure)
   - [Database Tables](#database-tables)
   - [Key Routes](#key-routes)
5. [How to Set It Up](#how-to-set-it-up)
   - [Requirements](#requirements)
   - [Installation](#installation)
   - [Configuration](#configuration)
   - [Migrations](#migrations)
   - [Seeding](#seeding)
6. [How to Use It](#how-to-use-it)
   - [As a Buyer (Storefront)](#as-a-buyer-storefront)
   - [As an Owner (Admin/POS)](#as-an-owner-adminpos)
   - [Product Variants](#product-variants)
7. [Security](#security)
8. [Production Deployment](#production-deployment)
9. [Troubleshooting](#troubleshooting)

---

## What It Is

This is a **dual-interface** application:

- **E-Commerce Storefront** (`/`) — Public-facing catalog, cart, checkout, and order tracking for online buyers.
- **Point of Sale** (`/pos`) — Restricted POS dashboard for walk-in purchases with cash or QRIS payment.
- **Admin Dashboard** (`/admin`) — Product, order, user, category, review, and variant management for store owners.

It supports **multi-attribute product variants** (e.g., a tent available in Green/Blue × 2-Person/4-Person), where each variant combination has its own stock and optional price override. This goes beyond simple size-based variants — you can create combinations across Color, Size, Material, or any attribute.

---

## Why This Architecture

### Framework Choice: CodeIgniter 4

- **Lightweight** — No heavy bootstrapping; fast page loads for a POS where cashiers need instant responses.
- **No ORM overhead** — CI4's Model is thin; we use raw queries for stock operations and JSON data.
- **Built-in CSRF protection** — Critical for handling payments.
- **Session-based carts** — Both `pos_cart` and `buyer_cart` are stored server-side in sessions, avoiding the complexity of database carts for a store of this scale.

### Design: Neo-Brutalist UI

- Thick borders (4px), bold shadows (`4px 4px 0 0 #000`), uppercase headings, and high-contrast colors.
- Works without JavaScript for basic operations; JS enhances the experience (variant selectors, payment popups, maps).
- Tailwind CSS via CDN — no build step needed; swap to compiled CSS for production.

### Variant System: JSON Attributes (not EAV)

- Instead of a complex Entity-Attribute-Value pattern, all variant attributes are stored as a single JSON column: `{"Color":"Red","Size":"XL"}`.
- **Why**: Simpler queries, fewer joins, easier to understand. For a store with <10k variants, JSON performs well and avoids the join explosion of EAV.
- Matching a variant by attributes is a linear scan in PHP — fast enough for a single product's variants (usually <50 combinations).

### Cart Key Strategy

```
Simple product:  {productId}          → "5"
Size variant:    {productId}-{size}   → "5-XL"
Advanced variant: {productId}-v{variantId} → "5-v3"
```

Composite string keys allow the same product to appear multiple times in the cart with different variant selections, without needing a separate cart_items table.

### Stock Deferral

Stock is decremented **on payment settlement** (not on order creation). This prevents:
- Cart abandonment from holding inventory
- Multiple users seeing stale stock during checkout

For POS cash transactions, settlement is immediate. For online payments, stock decrements when Midtrans sends the settlement webhook.

---

## How It Works

### Tech Stack

| Layer | Technology | Why |
|-------|-----------|-----|
| Backend | PHP 8.2+ / CodeIgniter 4.7 | Lightweight, fast, built-in security |
| Database | MySQL 5.7+ / MariaDB 10.3+ | Reliable, JSON column support |
| Frontend | Tailwind CSS (CDN) | No build step, rapid styling |
| Payments | Midtrans Snap | Supports QRIS, GoPay, bank transfer, CC |
| Shipping | Biteship API | Multi-courier (JNE, TIKI, SiCepat, POS) |
| Maps | Leaflet + OpenStreetMap | Free, no API key needed |
| Fonts | Space Grotesk + Inter | Neo-brutalist aesthetic |

### Request Flow

```
Browser → Apache/Nginx → index.php (front controller) → Routing → Filter chain → Controller → Model → View → Response
```

**Filter chain (in order):**
1. `forcehttps` — Redirect HTTP to HTTPS
2. `pagecache` — Serve cached pages if available
3. `csrf` — Validate CSRF token on POST/PUT/DELETE
4. Route-specific auth filters (`auth:owner`, `auth:buyer`, `auth`)
5. `toolbar` (after) — Debug toolbar (development only)

### Payment Flow

```
STORE CHECKOUT:
  Cart → Checkout form → Create order + Get Snap token → Midtrans Snap popup
  → User pays → onSuccess → Success page (with polling)
  → Midtrans sends webhook → Update status → Decrement stock → Create shipment

POS CHECKOUT (Cash):
  Select items → Click CASH → Confirm → Create order → Decrement stock → Reload

POS CHECKOUT (QRIS):
  Select items → Click QRIS → Create order + Get Snap token → Snap popup
  → User scans QRIS → Webhook updates status
```

### Variant System

**Data model:**
```
product_variants table:
  id          INT           Primary key
  product_id  INT           FK → products.id
  sku         VARCHAR(100)  Optional unique SKU
  price       DECIMAL(15,2) Nullable; null = use base product price
  stock       INT           Independent stock count
  image       VARCHAR(255)  Optional variant-specific image
  sort_order  INT           Display order
  attributes  JSON          Key-value pairs: {"Color":"Red","Size":"XL"}
```

**How matching works:**

1. Admin defines variants with attribute pairs (e.g., Color=Red, Size=XL).
2. Storefront/POS reads all variants for a product, extracts distinct attribute names/values.
3. UI renders attribute buttons (e.g., Color: [Red] [Blue], Size: [S] [M] [L]).
4. When user selects values, JS iterates variants to find one where ALL attributes match.
5. If found, displays the variant's stock and price (or base price if variant price is null).
6. On add-to-cart, `variant_id` is sent to server, which stores it and uses variant stock for validation.
7. Cart key format: `{productId}-v{variantId}` (e.g., `5-v3`).

**Cart and order storage:**
- Cart items store `variant_id` and `variant_label` (display string like "Color: Red, Size: XL").
- On checkout, these are saved to `order_items.variant_id` and `order_items.variant_label`.
- Stock is deducted from `product_variants.stock` (not `products.stock`) for variant items.

### Stock Management

| Scenario | What gets decremented | When |
|----------|----------------------|------|
| POS Cash checkout | Base `products.stock` or `product_variants.stock` | Immediately on checkout |
| POS QRIS checkout | Same | On Midtrans settlement webhook |
| Storefront checkout | Same | On Midtrans settlement webhook |
| Payment expired/denied | Stock restored (+= quantity) | On Midtrans webhook |
| Order manually settled (Admin) | Stock decremented | On admin action |

**Important:** For variant items, only `product_variants.stock` is modified — `products.stock` is NOT decremented. This avoids double-deduction and keeps base product stock as a "theoretical maximum" when variants exist.

---

## Where Everything Lives

### Directory Structure

```
pos-ci4/
├── app/
│   ├── Config/
│   │   ├── App.php              # Base URL, timezone, app settings
│   │   ├── Biteship.php         # Biteship API config (origin address)
│   │   ├── Database.php         # Database connection settings
│   │   ├── Filters.php          # Global filter chain (CSRF, toolbar, auth)
│   │   ├── Midtrans.php         # Midtrans API keys, URLs
│   │   ├── Routes.php           # All URL → Controller routing
│   │   └── Security.php         # CSRF token settings (name, cookie, regeneration)
│   │
│   ├── Controllers/
│   │   ├── Admin.php            # 950+ lines — Dashboard, CRUD for products/orders/users/categories/reviews/sizes/variants
│   │   ├── AuthController.php   # Login, register, logout
│   │   ├── BaseController.php   # Base class for all controllers
│   │   ├── Cart.php             # Storefront cart — add, update, remove, clear (variant-aware)
│   │   ├── Catalog.php          # Product listing, detail page, review submission
│   │   ├── Checkout.php         # Checkout page with map, city search, courier selection
│   │   ├── Home.php             # Root redirect → Catalog::index
│   │   ├── MidtransCallback.php # Webhook handler — signature verification, stock updates, shipment creation
│   │   ├── OrderController.php  # Buyer order history, success page, detail
│   │   ├── PaymentController.php # Snap token generation, status verification, payment simulation
│   │   ├── Pos.php              # POS dashboard — 450+ lines, variant modal, cart, checkout
│   │   ├── Profile.php          # User profile edit, avatar upload/remove
│   │   └── ShippingController.php # Biteship city search, courier rates
│   │
│   ├── Database/
│   │   ├── Migrations/          # 17 migration files (see table below)
│   │   └── Seeds/
│   │       └── ProductSeeder.php # 16 sample products across 4 categories
│   │
│   ├── Filters/
│   │   └── AuthFilter.php       # Role-based access (buyer, owner)
│   │
│   ├── Libraries/
│   │   ├── Biteship.php         # Biteship API client (rates, shipment creation)
│   │   └── Midtrans.php         # Midtrans Snap API (token, status, notification verification)
│   │
│   ├── Models/
│   │   ├── CategoryModel.php
│   │   ├── OrderItemModel.php
│   │   ├── OrderModel.php       # Order CRUD, order number generation, status-based queries
│   │   ├── ProductImageModel.php
│   │   ├── ProductModel.php     # Product CRUD, category queries, stock alerts, stock decrement
│   │   ├── ProductSizeModel.php # Legacy per-size stock
│   │   ├── ProductVariantModel.php # Variant CRUD, attribute matching, distinct attributes, stock queries
│   │   ├── ReviewModel.php      # Reviews with rating summary, admin reply, status management
│   │   └── UserModel.php
│   │
│   └── Views/
│       ├── admin/               # 15+ view files for admin CRUD
│       │   ├── product_form.php     # Full product form with tabs, gallery, features/specs
│       │   ├── product_sizes.php    # Per-size stock management (legacy)
│       │   ├── product_variants.php # Multi-attribute variant management
│       │   ├── reviews.php          # Review list with status toggles
│       │   └── review_reply.php     # Admin reply form
│       ├── auth/                # login.php, register.php
│       ├── cart/                # index.php — cart display
│       ├── catalog/             # index.php (listing), detail.php (full product page with tabs, gallery, reviews, variants)
│       ├── checkout/            # index.php — Leaflet map, courier selection, payment overlay
│       ├── layout/              # main.php — neo-brutalist theme with avatar nav
│       ├── layouts/             # pos_layout.php — POS-specific layout
│       ├── order/               # detail.php, my_orders.php, success.php
│       ├── pos/                 # dashboard.php (438 lines — product grid, cart, variant modal), login.php
│       └── profile/             # index.php — avatar upload, name/email/phone/address edit
│
├── public/
│   ├── index.php                # Front controller
│   ├── js/
│   │   ├── pos.js               # 142 lines — POS product clicks, cart operations, AJAX with CSRF
│   │   └── store.js             # Minimal storefront JS
│   └── uploads/
│       ├── products/            # Product main images + gallery images
│       └── avatars/             # User profile photos
│
├── writable/                    # CI4 cache, logs, sessions
├── .env                         # Environment config (NOT committed — in .gitignore)
├── .env.example                 # Template with placeholder values
├── composer.json
├── spark                        # CI4 CLI entry point
└── tailwind.config.js
```

### Database Tables

| # | Table | Key Columns | Purpose |
|---|-------|-------------|---------|
| 1 | `users` | id, name, email, password_hash, role (buyer/owner), avatar, phone, address | Authentication & profiles |
| 2 | `categories` | id, name, slug, icon | Product categorization |
| 3 | `products` | id, name, slug, description, category, category_id, price, stock, weight_grams, image, size, color, material, brand, dims, warranty, features (JSON), specifications (JSON), care_instructions, video_url | Product catalog with all attributes |
| 4 | `product_images` | id, product_id (FK), image, sort_order | Gallery images per product |
| 5 | `product_sizes` | id, product_id (FK), size, stock | Legacy per-size stock (when no advanced variants) |
| 6 | `product_variants` | id, product_id (FK), sku, price (nullable), stock, image, sort_order, attributes (JSON) | Multi-attribute variants with independent stock/price |
| 7 | `product_reviews` | id, product_id (FK), user_id (FK), rating (1-5), review, reply, replied_at, replied_by, status (approved/pending) | Customer reviews with admin replies |
| 8 | `orders` | id, order_number, buyer_id (FK), shipping_address, shipping_cost, courier_name, courier_service, gross_amount, payment_status (pending/settlement/expire/deny), midtrans_snap_token, biteship_order_id, tracking_number, tracking_url | Orders with payment & shipping tracking |
| 9 | `order_items` | id, order_id (FK), product_id (FK), size, variant_id, variant_label, quantity, price, subtotal | Order line items (variant-aware) |

### Key Routes

| Method | Route | Auth | Controller::Method | Purpose |
|--------|-------|------|-------------------|---------|
| GET | `/` | — | `Catalog::index` | Home / product listing |
| GET | `/product/(:any)` | — | `Catalog::detail/$1` | Product detail with reviews, gallery, variants |
| GET/POST | `/cart` | — | `Cart::index/add/update/remove` | Shopping cart |
| GET | `/checkout` | buyer | `Checkout::index` | Checkout with map & courier selection |
| POST | `/payment/createTransaction` | buyer | `PaymentController::createTransaction` | Create order + Snap token |
| POST | `/midtrans/callback` | — | `MidtransCallback::index` | Payment webhook |
| POST | `/pos/checkout` | owner | `Pos::checkout` | POS checkout (cash or QRIS) |
| GET/POST | `/admin/products/variants/(:num)` | owner | `Admin::productVariants/$1` | Multi-attribute variant management |
| GET | `/admin/test-api` | owner | `Admin::testApi` | API integration tester |

---

## How to Set It Up

### Requirements

- **PHP 8.1+** with extensions: `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl`, `gd`
- **MySQL 5.7+** or **MariaDB 10.3+** (for JSON column support)
- **Composer**
- **Apache** with `mod_rewrite` or **nginx**
- **Node.js** (optional — only if you want to compile Tailwind locally instead of using CDN)

### Installation

```bash
# 1. Clone
git clone <repo-url> pos-ci4
cd pos-ci4

# 2. Install PHP dependencies (CodeIgniter 4 + libraries)
composer install

# 3. Create environment config
cp .env.example .env

# 4. Create the database
mysql -u root -p -e "CREATE DATABASE pos-ci4 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Edit .env with your settings (see Configuration below)

# 6. Run all database migrations
php spark migrate --all

# 7. (Optional) Seed sample products
php spark db:seed ProductSeeder

# 8. Create upload directories
mkdir -p public/uploads/products
mkdir -p public/uploads/avatars

# 9. Start development server
php spark serve

# Server runs at http://localhost:8080
```

### Configuration

Edit `.env` with your values:

```ini
# Application
CI_ENVIRONMENT = development                # Change to 'production' for live site
app.baseURL = 'http://localhost:8080/'      # Must match your server URL (NO trailing slash)
app.storeName = 'Outdoor Gear Store'

# Database
database.default.hostname = localhost
database.default.database = pos-ci4
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306

# Encryption (generate with: php spark key:generate)
encryption.key = hex2bin:<64-char-hex-string>

# Midtrans (sandbox)
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxx
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxx
MIDTRANS_IS_PRODUCTION=false

# Biteship (test)
BITESHIP_API_KEY=biteship_test.xxxxxxxx
```

#### Setting Up Midtrans

1. Register at [Midtrans Dashboard](https://dashboard.midtrans.com/)
2. Go to **Settings → Access Keys** — copy Server Key and Client Key
3. Set `MIDTRANS_IS_PRODUCTION=false` for sandbox testing
4. Set **Payment Notification URL** in Midtrans Dashboard → Settings → Payment Settings → `https://yourdomain.com/midtrans/callback`
5. For local development, use [ngrok](https://ngrok.com/): `ngrok http 8080`

#### Setting Up Biteship

1. Register at [Biteship Dashboard](https://dashboard.biteship.com/)
2. Get API key (starts with `biteship_test.`)
3. Update origin address in `app/Config/Biteship.php` (or use the admin API tester)
4. Test connectivity at `/admin/test-api` (owner access)

### Migrations

```bash
# Run all pending migrations
php spark migrate --all

# Check migration status
php spark migrate:status

# Rollback last batch
php spark migrate:rollback
```

**Migration order (17 files):**

| # | File | What it Creates |
|---|------|----------------|
| 001 | `CreateUsersTable` | `users` table with roles |
| 002 | `CreateProductsTable` | `products` table |
| 003 | `CreateOrdersTable` | `orders` table |
| 004 | `CreateOrderItemsTable` | `order_items` table |
| 005 | `AlterOrdersAddBuyerId` | `buyer_id` FK on orders |
| 006 | `CreateCategoriesTable` | `categories` table |
| 007 | `AlterProductsAddCategoryId` | `category_id` FK on products |
| 008 | `AlterProductsAddAttributes` | `size`, `color`, `material` columns |
| 009 | `AlterUsersAddProfileFields` | `phone`, `address`, `avatar` columns |
| 010 | `AlterOrdersAddBiteship` | Shipping tracking columns |
| 011 | `CreateProductReviewsTable` | `product_reviews` table |
| 012 | `CreateProductSizesTable` | Legacy per-size stock |
| 013 | `AlterOrderItemsAddSize` | `size` column on order_items |
| 014 | `AlterProductsAddAdvancedFields` | Brand, dimensions, warranty, video, features, specs |
| 015 | `CreateProductImagesTable` | Gallery images |
| 016 | `CreateProductVariantsTable` | Multi-attribute variant system |
| 017 | `AlterOrderItemsAddVariant` | `variant_id`, `variant_label` on order_items |

### Seeding

```bash
php spark db:seed ProductSeeder
```

Creates 16 sample products across 4 categories (Tents, Packs, Apparel, Cooking) with realistic Indonesian prices.

---

## How to Use It

### As a Buyer (Storefront)

**Browse products** at `/` or `/products` — grouped by category, filterable, with brand badges and stock status.

**Product detail** (`/product/:slug`):
- Gallery thumbnails — click to switch main image
- Size selector — for products with legacy per-size stock
- **Variant selector** — for products with multi-attribute variants: click attribute values (e.g., "Red" then "XL"), price and stock update automatically
- Tabbed details: Specifications, Features, Care Instructions, Video
- Star rating from customer reviews with distribution bar chart
- Review form (login required, one review per product per user)
- Related products from same category

**Cart** (`/cart`):
- Items listed with image, variant label, quantity controls, subtotal
- Cart keys: `{productId}` (simple), `{productId}-{size}` (size), `{productId}-v{variantId}` (variant)
- Total weight and subtotal displayed

**Checkout** (`/checkout`):
1. Fill buyer name, phone, email
2. Place pin on map (drag, click, or "Use My Location")
3. Search city (OSM Nominatim autocomplete)
4. Select courier (JNE, TIKI, SiCepat, POS) — click "Get Shipping Rates"
5. Choose a rate → total updates with shipping cost
6. Click "Pay Now" → Midtrans Snap popup → select payment method
7. Success overlay for instant payments; polling page for QRIS

**Orders** (`/orders`): View history, see order details with tracking info.

### As an Owner (Admin/POS)

**Create an owner account:**
```sql
UPDATE users SET role = 'owner' WHERE email = 'your@email.com';
```

**Admin Dashboard** (`/admin/dashboard`):
- Metrics: total orders, revenue, users, products
- Low stock alerts (stock < 5)
- Recent 10 orders
- Orders by payment status

**Product Management** (`/admin/products`):
- Full CRUD with rich form (brand, dimensions, warranty, features/specs, gallery, video)
- Features: one per line → stored as JSON array
- Specifications: `key: value` per line → stored as JSON object
- **Per-size stock** link → legacy size management
- **Variants** link → multi-attribute variant management

**Variant Management** (`/admin/products/variants/:id`):
- Dynamic form — add/remove variant rows
- Each row: 3 attribute pairs (name + value), price override, stock, SKU, sort order
- ATMOST 3 attribute dimensions (e.g., Color + Size + Material)
- On save: deletes all existing variants and re-inserts
- Attributes stored as JSON: `{"Color":"Red","Size":"XL"}`

**Order Management** (`/admin/orders`):
- List with search and status filter
- Detail view with items, variant info, courier tracking
- Manual status change (e.g., mark as settlement to trigger stock deduction + shipment)

**User Management** (`/admin/users`):
- List with order count
- Edit: name, email, role, phone, address, avatar upload/remove

**POS Dashboard** (`/pos`):
- Product grid grouped by category — search and category filter
- **Simple products**: click to add to cart (quantity 1)
- **Variant products**: shows "VARIANTS" badge; click opens modal to select attribute values and quantity before adding to cart
- Cart panel: inline +/- quantity, remove, clear
- Checkout: **Cash** (instant settlement) or **QRIS** (Midtrans payment link)
- Inventory alerts for low stock / out of stock

### Product Variants

**Creating variants:**
1. Go to Admin → Products → click "Variants" on a product
2. Add variant rows (e.g., Color=Red + Size=XL, Color=Blue + Size=XL)
3. Set stock per combination; optionally set a price override (leave blank to use base price)
4. Save — all previous variants are replaced

**How it behaves:**

| Where | What user sees | What happens |
|-------|---------------|--------------|
| Product detail page | Attribute buttons (Color, Size) | Selecting values finds matching variant, updates price/stock |
| POS dashboard | Product card has "VARIANTS" badge | Clicking opens modal with same attribute selector |
| Cart | Item shows variant label ("Color: Red, Size: XL") | Cart key is `{productId}-v{variantId}` |
| Checkout / Order | Variant label is displayed | `variant_id` and `variant_label` stored in `order_items` |
| Stock deduction | — | Only `product_variants.stock` is decremented (not `products.stock`) |

---

## Security

### CSRF Protection

Enabled globally via **cookie-based tokens** (`app/Config/Security.php`):

- Token name: `csrf_test_name`
- Cookie name: `csrf_cookie_name`
- Header name: `X-CSRF-TOKEN`
- Token regenerates on every submission (`$regenerate = true`)
- All POST forms must include `<?= csrf_field() ?>`
- AJAX POST requests must send `X-CSRF-TOKEN` header or include token in body
- The POS layout overrides `window.fetch` to automatically inject `X-CSRF-TOKEN` on all POST requests
- All POS AJAX functions (`addToCart`, `updateCartItem`, `removeCartItem`, `clearCart`) include the CSRF token in the request body as a fallback

### XSS Prevention

All user-generated content is escaped in views using `<?= esc($var) ?>`:
- Product names, descriptions, attributes
- User names, emails, reviews
- Session flash messages
- Order numbers, courier names
- Form `old()` values

### Authentication & Authorization

- Passwords hashed with `password_hash()` (bcrypt)
- Session-based authentication
- `AuthFilter` checks route-level authorization:
  - `auth:owner` — Admin and POS routes (owner role only)
  - `auth:buyer` — Checkout routes (buyer role)
  - `auth` — Profile and order history (any logged-in user)
- POS has a separate login page (owner-only access)

### API Key Security

- Keys stored in `.env` (excluded from Git via `.gitignore`)
- Midtrans webhook verified cryptographically via `verifyNotification()`
- Biteship API key sent as Bearer token in Authorization header
- Test API page (`/admin/test-api`) protected by `auth:owner` filter

---

## Production Deployment

### Pre-Deployment Checklist

- [ ] Set `CI_ENVIRONMENT = production` in `.env`
- [ ] Set `app.baseURL` to your production domain
- [ ] Set `MIDTRANS_IS_PRODUCTION = true`
- [ ] Replace Midtrans sandbox keys with production keys (start with `Mid-`)
- [ ] Replace Biteship test key with live key (start with `biteship_live.`)
- [ ] Run `php spark migrate --all` on production database
- [ ] Set proper directory permissions: `chmod -R 775 writable/ public/uploads/`
- [ ] Configure cron job for session cleanup if using file-based sessions
- [ ] Set up HTTPS (required for Midtrans, Biteship, and secure checkout)
- [ ] Disable `route:list` commands in production
- [ ] Review error logging threshold (default: `4` for production)

### Production .env

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://yourdomain.com'
MIDTRANS_IS_PRODUCTION = true
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxx
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxx
BITESHIP_API_KEY=biteship_live.xxxxxxxx
```

### Performance Notes

- Tailwind is loaded via CDN — consider compiling to a static CSS file for production
- Product images should be optimized (compress JPG/PNG, use WebP where supported)
- Session cleanup: CI4 file-based sessions accumulate in `writable/session/` — configure `session.gc_probability` or a cron job
- Database queries are simple; for >10k products, add indexes on `category`, `slug`, and `stock`

### Server Requirements

- PHP 8.1+ with extensions: `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl`, `gd`
- MySQL 5.7+ or MariaDB 10.3+
- Apache (`mod_rewrite`) or nginx
- SSL certificate (HTTPS)

### Apache .htaccess

The `public/.htaccess` file handles URL rewriting (removes `index.php` from URLs). Ensure `mod_rewrite` is enabled:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

### Nginx Configuration

```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;

    root /path/to/pos-ci4/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }

    # Deny access to .env file
    location ~ /\.env {
        deny all;
    }
}
```

---

## Troubleshooting

### "Undefined" text in POS variant modal

**Cause:** JavaScript variable `match.stock` was `undefined` when setting element textContent. This happened when variant data was missing the `stock` property.

**Fix:** All stock references now use `|| 0` fallback. If you still see it, check that variants have `stock` values in the database.

### Variants not showing in POS

1. Verify variants exist in `product_variants` table for the product
2. Check that migration `016_CreateProductVariantsTable` has been run: `php spark migrate:status`
3. Product cards must have `data-has-variants="true"` — this is set when `$variantsByProduct` has entries for that product
4. Check browser console for JS errors

### Checkout fails with variant products

1. Run migration `017_AlterOrderItemsAddVariant`: `php spark migrate --all`
2. This adds `variant_id` and `variant_label` columns to `order_items` table
3. Without this migration, inserting order items with variant data fails with MySQL column error

### Payment webhook not reaching your server

- Use [ngrok](https://ngrok.com/) for local development: `ngrok http 8080`
- Update Midtrans Payment Notification URL with your ngrok URL
- Check `writable/logs/` for Midtrans callback logs
- Manually test: POST to `/midtrans/callback` with sample payload

### Stock not decrementing

For variant products, stock is deducted from `product_variants.stock`, NOT `products.stock`. Check:
- `product_variants.stock` for the matching variant
- MidtransCallback `handleSettlement()` - ensure it processes variant items
- For POS cash: check `Pos::checkout()` stock deduction logic

### Images not showing

- Verify upload directories exist: `public/uploads/products/` and `public/uploads/avatars/`
- Check file permissions: 755 for directories, 644 for files
- Verify `app.baseURL` matches your server URL (image URLs are generated relative to baseURL)
- File must be under 2MB and one of: JPG, PNG, WebP, GIF

### CSRF errors on AJAX requests

- The `X-CSRF-TOKEN` header should be sent with all POST requests
- The POS layout automatically injects this header via `window.fetch` override
- If the meta tag `csrf-token` is missing, CSRF validation will fail
- Check that `csrf_field()` is included in forms

### Biteship shipment not created

- Address must contain a valid 5-digit Indonesian postal code
- Origin config in `app/Config/Biteship.php` must have valid values
- Test API key at `/admin/test-api` (owner access)
- Check `writable/logs/` for Biteship error messages

---

## Development vs Production Comparison

| Aspect | Development | Production |
|--------|-------------|------------|
| `CI_ENVIRONMENT` | `development` | `production` |
| Error display | Detailed HTML errors | None (logged) |
| Debug toolbar | Enabled (after filter) | Disabled automatically |
| Logging threshold | 9 (all messages) | 4 (errors only) |
| Midtrans mode | Sandbox | Live |
| Biteship key | `biteship_test.*` | `biteship_live.*` |
| HTTPS | Optional | Required |
| Tailwind CSS | CDN (fast iteration) | CDN or compiled static |
| Session cleanup | Manual | Cron job recommended |

---

## License

This project is developed for the Outdoor Gear Store. All rights reserved.
