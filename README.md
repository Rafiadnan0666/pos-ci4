# Outdoor Gear Store — POS + E-Commerce (CodeIgniter 4)

A full-featured **Point of Sale (POS)** and **E-Commerce** web application built with CodeIgniter 4, Tailwind CSS (neo-brutalist design), Midtrans payment gateway, and Biteship shipping integration. Designed for an outdoor gear store selling tents, backpacks, apparel, and cooking equipment.

---

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running Migrations](#running-migrations)
- [User Roles](#user-roles)
- [Feature Walkthrough](#feature-walkthrough)
  - [Store Front (Buyer)](#store-front-buyer)
  - [Product Detail Page](#product-detail-page)
  - [Reviews](#reviews)
  - [Checkout & Payment Flow](#checkout--payment-flow)
  - [Admin Dashboard (Owner)](#admin-dashboard-owner)
  - [Advanced Product Management](#advanced-product-management)
  - [Per-Size Stock](#per-size-stock)
  - [User Profile & Avatar](#user-profile--avatar)
  - [POS Dashboard](#pos-dashboard)
- [Neo-Brutalist Design System](#neo-brutalist-design-system)
- [API Integrations](#api-integrations)
- [File Structure](#file-structure)
- [Database Tables](#database-tables)
- [Troubleshooting](#troubleshooting)
- [Development vs Production](#development-vs-production)

---

## Architecture Overview

### Tech Stack

| Component | Technology |
|-----------|------------|
| Framework | CodeIgniter 4 |
| PHP | 8.1+ |
| Database | MySQL / MariaDB |
| Frontend | Tailwind CSS (CDN), AOS animations, Space Grotesk + Inter fonts |
| Payments | Midtrans Snap (Sandbox / Production) |
| Shipping | Biteship API |
| Maps | Leaflet + OpenStreetMap Nominatim |
| Design | Neo-brutalist (thick borders, bold shadows, uppercase headings) |

### Key Design Decisions

- **Neo-brutalist styling** throughout: 4px black borders, `box-shadow: 4px 4px 0 0 #000`, uppercase Space Grotesk headings, bold color blocks (`#FFDE4D` yellow, `#06B6D4` cyan, `#F97316` orange)
- **Cart keys** are `product_id` or `product_id-size` for size-variant items (composite keys)
- **Cart data** stored in `session('buyer_cart')` — cleared after successful order
- **Stock** is decremented on payment settlement, not on order creation
- **Features/Specs** stored as JSON in DB, entered as plain text in admin form (one per line for features, `key: value` per line for specs)
- **Product images** stored in `public/uploads/products/`; gallery images in `product_images` table
- **Payment success** now shows an overlay on checkout page (pending payments redirect to success page for QRIS polling)
- **POS dashboard** does `location.reload()` on success to reset cart state

---

## System Requirements

- **PHP 8.1+** with extensions: `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl`, `gd`
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Composer**
- **Node.js** (optional — only for Tailwind if switching from CDN)

---

## Installation

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

# 5. Edit .env with your database credentials and API keys

# 6. Run all migrations
php spark migrate --all

# 7. Create upload directories
mkdir public\uploads\products
mkdir public\uploads\avatars

# 8. Serve the application (development)
php spark serve
```

The app will be available at **http://localhost:8080/**

---

## Configuration

### `.env` File

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'
app.storeName = 'Outdoor Gear Store'
app.storeAddress = 'Jl. Petualang No. 1, Jakarta Pusat'
app.storePhone = '02112345678'

database.default.hostname = localhost
database.default.database = pos-ci4
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306

encryption.key = hex2bin:f0aa2f095c74624c68dfd1eb3051c981f15160e125723d0068bf0e75c5bc6796

# ==== API Keys ====

BITESHIP_API_KEY=biteship_test.eyJ...

MIDTRANS_CLIENT_KEY=SB-Mid-client-...
MIDTRANS_SERVER_KEY=SB-Mid-server-...
MIDTRANS_IS_PRODUCTION=false
```

### Midtrans Setup

1. Register at [Midtrans Dashboard](https://dashboard.midtrans.com/)
2. Go to **Settings → Access Keys** — copy Server Key and Client Key
3. Set `MIDTRANS_IS_PRODUCTION=false` for sandbox
4. Set **Payment Notification URL** in Midtrans Dashboard → Settings → Payment Settings to `https://yourdomain.com/midtrans/callback`
5. For local dev, use [ngrok](https://ngrok.com/): `ngrok http 8080`

### Biteship Setup

1. Register at [Biteship Dashboard](https://dashboard.biteship.com/)
2. Get API key (starts with `biteship_test.` or `biteship_live.`)
3. Update origin address in `app/Config/Biteship.php`

---

## Running Migrations

```bash
php spark migrate --all
php spark migrate:rollback
php spark migrate:status
```

### Migration List

| File | Purpose |
|------|---------|
| `001_CreateUsersTable` | Users with roles (buyer, owner) |
| `002_CreateProductsTable` | Product catalog |
| `003_CreateOrdersTable` | Orders with payment tracking |
| `004_CreateOrderItemsTable` | Order line items |
| `005_AlterOrdersAddBuyerId` | Normalize buyer relation |
| `006_CreateCategoriesTable` | Product categories |
| `007_AlterProductsAddCategoryId` | Category FK |
| `008_AlterProductsAddAttributes` | Size, color, material |
| `009_AlterUsersAddProfileFields` | Phone, address, avatar |
| `010_AlterOrdersAddBiteship` | Biteship tracking fields |
| `011_CreateProductReviewsTable` | Product reviews with admin reply |
| `012_CreateProductSizesTable` | Per-size stock management |
| `013_AlterOrderItemsAddSize` | Size column on order_items |
| `014_AlterProductsAddAdvancedFields` | Brand, dimensions, warranty, video, features, specs, care instructions |
| `015_CreateProductImagesTable` | Product gallery images |

---

## User Roles

| Role | Capabilities | Routes |
|------|-------------|--------|
| **Buyer** (default) | Browse catalog, cart, checkout, view orders, write reviews, edit profile | `/`, `/cart`, `/checkout`, `/orders`, `/product/*`, `/profile` |
| **Owner** (admin) | All buyer features + Admin dashboard, POS, product/order/user/review management | `/admin/*`, `/pos` |

### Creating an Owner Account

Register as a buyer at `/register`, then update role in DB:
```sql
UPDATE users SET role = 'owner' WHERE email = 'your@email.com';
```

---

## Feature Walkthrough

### Store Front (Buyer)

#### Catalog (`/` or `/products`)
- Products grouped by category (Tents, Packs, Apparel, Cooking)
- Filter by category
- Product cards show: image, brand badge, name, attributes, price, stock status, low-stock badge
- Quick-add cart button per product
- Links to product detail page

#### Product Detail Page (`/product/:slug`)

A rich, tabbed product detail page:

- **Main image** with gallery thumbnails — click thumbnail to switch main image
- **Brand badge** and category badge
- **Star rating** from customer reviews
- **Stock status** (with per-size availability if size variants exist)
- **Size selector** — click a size to select, shows remaining stock per size, validates before adding to cart
- **Quantity selector** with +/- buttons, respects per-size max stock
- **Add to Cart** button
- **Attributes badges**: color, material, weight, warranty, dimensions

**Tabs section:**
1. **Specifications** — key-value grid from `specifications` JSON, or fallback attribute display
2. **Features** — bullet list from `features` JSON
3. **Care Instructions** — styled text box
4. **Video** — embedded YouTube/Vimeo iframe or link

**Rating summary bar chart** — shows star distribution (5★ to 1★) with visual bars

**Reviews section** — each review shows user avatar/initial, name, star rating, date, review text, admin reply

**Write a Review form** — interactive star picker, text area, available after login (one review per product per user)

**Related products** — 4 products from same category

#### Cart (`/cart`)
- Items displayed with image, name, size (if applicable), quantity, price, subtotal
- Update quantity inline
- Remove individual items
- Clear cart
- Shows total weight and subtotal

**Cart for size-variant products:**
- Cart keys are `product_id-size` (e.g., `1-S`, `5-XL`)
- Same product in different sizes = separate cart items

### Reviews System

**For buyers:**
- Submit rating (1-5 stars) + optional review text (min 10 chars)
- One review per product per user
- Reviews require approval (status: `approved` or `pending`)
- Reviews display in product detail under "Reviews" section
- Rating summary bar chart shows distribution

**For admin (Owner):**
- **Reviews management** at `/admin/reviews`
- List all reviews with search filtering
- Reply to reviews (admin reply shown under review in green border)
- Toggle review status (approved / pending)
- Delete reviews
- Status badge color: green = approved, orange = pending

### Checkout & Payment Flow

```
Cart → Checkout → Fill Address → Search City → Select Courier → Pay → Success
```

**Flow details:**
1. **Checkout page** (`/checkout`) — Leaflet map with drag marker, click-to-place, "Use My Location" (geolocation), reverse geocoding (Nominatim), city search with autocomplete
2. **Fill buyer info**: Name, Phone, Email
3. **Search city**: Uses OpenStreetMap Nominatim to find city, auto-fills lat/lng and postal code
4. **Select courier**: JNE, TIKI, SiCepat, POS — click "Get Shipping Rates"
5. **Rates fetched** from Biteship API, displayed as selectable cards
6. **Total updates** with shipping cost
7. **Pay Now** → creates order (status: `pending`) + order_items + Midtrans Snap token
8. **Snap popup** — user selects payment method and completes payment
9. **Payment success overlay** (no redirect for instant payments; pending payments redirect to success page for QRIS polling)
10. **Polling**: success page polls `payment/verifyStatus` until settlement confirmed
11. **Biteship shipment**: created automatically when payment settles

**Payment statuses:**
| Status | Meaning |
|--------|---------|
| `pending` | Waiting for payment |
| `settlement` | Payment confirmed — stock decremented, Biteship shipped |
| `expire` | Time expired — stock restored |
| `deny` | Payment denied |

### Admin Dashboard (Owner)

Access at `/admin/dashboard`

- Total orders, revenue, users, products
- Low stock alerts (items with stock < 5)
- Recent 10 orders
- Orders by status breakdown

#### Orders Management
- List all orders with search and status filter
- Click order to view detail (items, sizes, tracking, courier)
- Change order status manually (when set to `settlement`: stock decremented + Biteship shipment created)

#### User Management
- List all users with order count
- Edit user profile: name, email, role, phone, address, avatar
- Upload / remove avatar with preview

#### Category Management
- CRUD for product categories with icons

### Advanced Product Management

**Product form** (`/admin/products/create` and `/admin/products/edit/:id`):

**Sections:**
1. **Basic Information**: name, description, category (dropdown + new category option), price, stock, weight, brand
2. **Attributes**: size, color, material
3. **Dimensions & Warranty**: length, width, height (cm), warranty text
4. **Details**:
   - **Features**: plain text, one per line (converted to JSON on save, decoded on display)
   - **Specifications**: `key: value` per line (stored as JSON object)
   - **Care Instructions**: free text
   - **Video URL**: YouTube or Vimeo
5. **Main Image**: file upload with live preview, remove checkbox
6. **Gallery Images**: multiple file upload, individual remove buttons (removed on form submit), displayed as thumbnails
7. **Manage Sizes & Stock** button (see below)

### Per-Size Stock

Products can have size variants (e.g., S, M, L, XL) each with independent stock:

- **Admin**: `/admin/products/sizes/:id` — dynamic form to add/remove/save size-stock rows
- **Storefront**: size selector on product detail shows per-size stock, disables sold-out sizes
- **Cart**: uses `product_id-size` as cart key so same product in different sizes = separate items
- **Orders**: `order_items.size` column captures the selected size

### User Profile & Avatar

**Profile page** (`/profile`):
- View current avatar or initial letter
- Edit name, email, phone, address
- Upload avatar (JPG, PNG, WebP, GIF — max 2MB)
- Remove current avatar
- Session sync: avatar and name update immediately in header

**Avatar display across site:**
- Header nav — mini avatar thumbnail next to username
- Profile page — large avatar preview
- Product review user avatars
- Admin review reply page — user avatar

### POS Dashboard

Access at `/pos` — simplified POS for walk-in customers.

- Add products to cart with quantity
- Update / remove items
- Checkout with cash payment simulation
- `location.reload()` on success to reset cart state

---

## Neo-Brutalist Design System

### CSS Classes

**Cards:**
- `neo-card` — white card, 4px border, 4px shadow
- `neo-card-yellow` — yellow variant
- `neo-card-cyan` — cyan variant (white text)
- `neo-card-orange` — orange variant (white text)
- `neo-card-green` — green variant

**Buttons:**
- `neo-btn` — base button class
- `neo-btn-yellow` — yellow background
- `neo-btn-cyan` — cyan background (white text)
- `neo-btn-orange` — orange background (white text)
- `neo-btn-green` — green background
- `neo-btn-red` — red background (white text)
- `neo-btn-white` — white background

**Other:**
- `neo-input` — input/textarea with 4px border, focus turns yellow + shadow
- `neo-badge` — inline label with 2px border
- `neo-divider` — 4px black top border

### Animations
- AOS (Animate on Scroll) fade-up/fade-down/fade-left/fade-right
- `:hover` on cards: translate(-1px, -1px), shadow increases
- `:hover` on buttons: translate(2px, 2px), shadow reduces
- `:active` on buttons: translate(4px, 4px), no shadow
- `.animate-float` — floating animation
- `.animate-wiggle:hover` — wiggle animation

---

## API Integrations

### Midtrans Snap

```
Browser → POST /payment/createTransaction → App creates order + requests Snap token → Midtrans returns token
Browser → Opens Snap popup → User pays
Browser → onSuccess callback → Redirects to order success page
Browser → Polls /payment/verifyStatus → App checks Midtrans order status
Midtrans → POST /midtrans/callback → App verifies signature → updates status → decrements stock → creates Biteship shipment
```

### Biteship Shipping

- Checkout: `POST /shipping/getRates` → Biteship API → returns courier rates
- Settlement: App calls Biteship `POST /v1/pickup/orders` with origin + destination + items
- Tracking: `biteship_order_id` and `tracking_number` saved to orders table

### OpenStreetMap / Nominatim

- City search with autocomplete on checkout
- Reverse geocoding (lat/lng → address) when marker is dragged or "Use My Location" is clicked

---

## File Structure

```
pos-ci4/
├── app/
│   ├── Config/
│   │   ├── Biteship.php          # Biteship API config
│   │   ├── Midtrans.php          # Midtrans API config
│   │   ├── Routes.php            # All URL routes
│   │   └── Filters.php           # Auth filters
│   ├── Controllers/
│   │   ├── Admin.php             # Dashboard, products, orders, users, reviews, sizes
│   │   ├── AuthController.php    # Login / Register
│   │   ├── Cart.php              # Shopping cart
│   │   ├── Catalog.php           # Product listing, detail, submit review
│   │   ├── Checkout.php          # Checkout page
│   │   ├── MidtransCallback.php  # Midtrans webhook handler
│   │   ├── OrderController.php   # Buyer order history
│   │   ├── PaymentController.php # Create order, Snap token, verify, simulate
│   │   ├── Pos.php               # POS dashboard
│   │   ├── Profile.php           # User profile & avatar
│   │   └── ShippingController.php# Biteship courier rates
│   ├── Database/Migrations/      # 15 migration files
│   ├── Libraries/
│   │   ├── Midtrans.php          # Midtrans Snap API + verification
│   │   └── Biteship.php          # Biteship API client
│   ├── Models/
│   │   ├── CategoryModel.php
│   │   ├── OrderItemModel.php
│   │   ├── OrderModel.php
│   │   ├── ProductImageModel.php # Gallery image CRUD with file cleanup
│   │   ├── ProductModel.php
│   │   ├── ProductSizeModel.php  # Per-size stock CRUD
│   │   ├── ReviewModel.php       # Reviews with rating summary
│   │   └── UserModel.php
│   └── Views/
│       ├── admin/
│       │   ├── product_form.php    # Full product form with gallery, features, specs
│       │   ├── product_sizes.php   # Per-size stock management
│       │   ├── reviews.php         # Review management list
│       │   ├── review_reply.php    # Admin reply form
│       │   └── user_form.php      # User edit with avatar upload
│       ├── catalog/
│       │   ├── index.php          # Product listing cards with brand, badges
│       │   └── detail.php         # Tabs, gallery, star picker, reviews, specs
│       ├── checkout/index.php     # Leaflet map, courier selection, payment overlay
│       ├── layout/main.php        # Neo-brutalist theme, header with avatar
│       ├── profile/index.php      # Profile edit with avatar upload
│       └── ... (cart, order, pos, auth)
├── public/uploads/
│   ├── products/                  # Product main images + gallery images
│   └── avatars/                   # User avatar photos
```

---

## Database Tables

| Table | Key Columns | Purpose |
|-------|-------------|---------|
| `users` | id, name, email, password, role, avatar, phone, address | Buyers and owners |
| `categories` | id, name, slug, icon | Product categories |
| `products` | id, name, slug, description, category, price, stock, weight_grams, image, size, color, material, brand, dimension_length/width/height, warranty, features (JSON), specifications (JSON), care_instructions, video_url | Products with all attributes |
| `product_images` | id, product_id (FK), image, sort_order | Gallery images |
| `product_sizes` | id, product_id (FK), size, stock | Per-size stock |
| `product_reviews` | id, product_id (FK), user_id (FK), rating (1-5), review, reply, replied_at, replied_by (FK), status (approved/pending) | Product reviews |
| `orders` | id, order_number, buyer_id (FK), payment_status, courier_name, shipping_address, biteship_order_id, tracking_number | Orders |
| `order_items` | id, order_id (FK), product_id (FK), size, quantity, price, subtotal | Order line items |

---

## Troubleshooting

### Photos / Images Not Showing

- Ensure upload directories exist: `public/uploads/products/` and `public/uploads/avatars/`
- Check file permissions (755 for dirs, 644 for files)
- Verify `app.baseURL` in `.env` is correct (must match server URL)
- File must be under 2MB and one of: JPG, PNG, WebP, GIF
- No image = emoji icon fallback (product detail) or initial letter (avatar)

### Payment Stuck on Pending

- **Sandbox QRIS/GoPay**: Use "Simulate Payment" button on order success page
- **Webhook not reaching localhost**: Use ngrok to expose local server
- **Manual settlement**: Admin → Orders → change status to "settlement"

### Review / Reply Not Appearing

- Reviews default to `pending` status; admin must approve them
- Only `approved` reviews show on product detail page
- Admin reply appears after submission (redirects to reviews list)
- Review form requires login; one review per product per user

### Biteship Shipment Not Created

- Address must contain valid 5-digit Indonesian postal code
- Origin config in `app/Config/Biteship.php` must be valid
- Test API key at `/admin/test-api`
- Check `app/logs/` for Biteship error messages

### Features / Specs Not Displaying

- Features and specs are stored as JSON; entered as plain text in admin form
- Features: one per line → stored as JSON array
- Specs: `key: value` per line → stored as JSON object
- If data was saved before JSON encoding fix, delete and re-enter

---

## Development vs Production

| Aspect | Development | Production |
|--------|-------------|------------|
| `CI_ENVIRONMENT` | `development` | `production` |
| `MIDTRANS_IS_PRODUCTION` | `false` | `true` |
| Biteship API Key | `biteship_test.*` | `biteship_live.*` |
| Error display | Detailed | None |
| Debug toolbar | Enabled | Disabled |
| Simulate Payment | Available | Disabled |

**Switch to production:**
```ini
CI_ENVIRONMENT = production
MIDTRANS_IS_PRODUCTION = true
BITESHIP_API_KEY = biteship_live.your_live_key
app.baseURL = 'https://yourdomain.com'
```

---

## Directory Permissions

```
writable/       # CI4 logs, cache, sessions
public/uploads/ # Product images, avatars
```

On Linux:
```bash
chmod -R 775 writable public/uploads
chown -R www-data:www-data writable public/uploads
```

---

## Key Routes Summary

| Route | Method | Auth | Description |
|-------|--------|------|-------------|
| `/products` | GET | — | Catalog listing |
| `/product/(:any)` | GET | — | Product detail with reviews, gallery, sizes |
| `/cart` | GET | — | Shopping cart |
| `/checkout` | GET | auth:buyer | Checkout with map |
| `/profile` | GET | auth | Edit profile & avatar |
| `/orders` | GET | auth | My orders |
| `/admin/dashboard` | GET | auth:owner | Admin dashboard |
| `/admin/reviews` | GET | auth:owner | Review management |
| `/admin/products/sizes/(:num)` | GET/POST | auth:owner | Per-size stock |
| `/admin/users/edit/(:num)` | GET/POST | auth:owner | Edit user with avatar |
| `/product/review` | POST | — | Submit review |
| `/profile/update` | POST | auth | Update profile |
| `/payment/createTransaction` | POST | auth:buyer | Create order + Snap token |
| `/midtrans/callback` | POST | — | Midtrans webhook |
