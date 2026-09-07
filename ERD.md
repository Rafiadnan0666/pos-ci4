# Entity Relationship Diagram - POS CI4 (Outdoor Gear Store)

## Mermaid ERD

```mermaid
erDiagram
    USERS ||--o{ ORDERS : "places"
    USERS ||--o{ PRODUCT_REVIEWS : "writes"
    USERS ||--o{ PRODUCT_IMAGES : "uploads"
    CATEGORIES ||--o{ PRODUCTS : "contains"
    PRODUCTS ||--o{ PRODUCT_IMAGES : "has"
    PRODUCTS ||--o{ PRODUCT_SIZES : "has sizes"
    PRODUCTS ||--o{ PRODUCT_VARIANTS : "has variants"
    PRODUCTS ||--o{ PRODUCT_REVIEWS : "receives"
    ORDERS ||--o{ ORDER_ITEMS : "contains"
    PRODUCTS ||--o{ ORDER_ITEMS : "ordered as"
    PRODUCT_VARIANTS ||--o{ ORDER_ITEMS : "variant of"

    USERS {
        int id PK
        string name
        string email UK
        string password_hash
        enum role [buyer, owner]
        string avatar
        string phone
        text address
        timestamp created_at
        timestamp updated_at
    }

    CATEGORIES {
        int id PK
        string name
        string slug UK
        string icon
        timestamp created_at
        timestamp updated_at
    }

    PRODUCTS {
        int id PK
        string name
        string slug UK
        text description
        string category
        int category_id FK
        decimal price
        int stock
        int weight_grams
        string image
        string size
        string color
        string material
        string brand
        json dimensions
        string warranty
        json features
        json specifications
        text care_instructions
        string video_url
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_IMAGES {
        int id PK
        int product_id FK
        string image
        int sort_order
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_SIZES {
        int id PK
        int product_id FK
        string size
        int stock
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_VARIANTS {
        int id PK
        int product_id FK
        string sku UK
        decimal price
        int stock
        string image
        int sort_order
        json attributes
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_REVIEWS {
        int id PK
        int product_id FK
        int user_id FK
        tinyint rating
        text review
        text reply
        timestamp replied_at
        int replied_by FK
        enum status [approved, pending]
        timestamp created_at
        timestamp updated_at
    }

    ORDERS {
        int id PK
        string order_number UK
        int buyer_id FK
        text shipping_address
        decimal shipping_cost
        string courier_name
        string courier_service
        decimal gross_amount
        enum payment_status [pending, settlement, expire, deny]
        text midtrans_snap_token
        string biteship_order_id
        string tracking_number
        string tracking_url
        timestamp created_at
        timestamp updated_at
    }

    ORDER_ITEMS {
        int id PK
        int order_id FK
        int product_id FK
        string size
        int variant_id FK
        string variant_label
        int quantity
        decimal price
        decimal subtotal
        timestamp created_at
        timestamp updated_at
    }
```

## Table Relationships Summary

### Core Entities
| Table | Primary Key | Foreign Keys | Description |
|-------|-------------|--------------|-------------|
| `users` | `id` (int) | - | Authentication & profiles (buyer/owner roles) |
| `categories` | `id` (int) | - | Product categorization |
| `products` | `id` (int) | `category_id` → `categories.id` | Product catalog with all attributes |
| `product_images` | `id` (int) | `product_id` → `products.id` | Gallery images per product |
| `product_sizes` | `id` (int) | `product_id` → `products.id` | Legacy per-size stock |
| `product_variants` | `id` (int) | `product_id` → `products.id` | Multi-attribute variants with independent stock/price |
| `product_reviews` | `id` (int) | `product_id`, `user_id`, `replied_by` → `users.id` | Customer reviews with admin replies |

### Order Management
| Table | Primary Key | Foreign Keys | Description |
|-------|-------------|--------------|-------------|
| `orders` | `id` (int) | `buyer_id` → `users.id` | Orders with payment & shipping tracking |
| `order_items` | `id` (int) | `order_id` → `orders.id`, `product_id` → `products.id`, `variant_id` → `product_variants.id` | Order line items (variant-aware) |

## Key Relationships

1. **User → Orders** (One-to-Many): Buyers place orders
2. **User → Reviews** (One-to-Many): Users write product reviews
3. **Category → Products** (One-to-Many): Products belong to categories
4. **Product → Images** (One-to-Many): Gallery images
5. **Product → Sizes** (One-to-Many): Legacy size-based stock
6. **Product → Variants** (One-to-Many): Multi-attribute variants (Color + Size + Material)
7. **Product → Reviews** (One-to-Many): Customer reviews
8. **Order → Order Items** (One-to-Many): Order line items
9. **Product → Order Items** (One-to-Many): Products in orders
10. **Variant → Order Items** (One-to-Many): Variant-specific order items

## Variant System Architecture

### Data Model
```sql
product_variants:
  - id: INT PK
  - product_id: INT FK → products.id
  - sku: VARCHAR(100) UK (optional)
  - price: DECIMAL(15,2) NULL (null = use base product price)
  - stock: INT (independent stock count)
  - image: VARCHAR(255) NULL (variant-specific image)
  - sort_order: INT (display order)
  - attributes: JSON (e.g., {"Color":"Red","Size":"XL"})
```

### Matching Logic
1. Admin defines variants with attribute pairs (Color=Red, Size=XL)
2. Frontend reads all variants, extracts distinct attribute names/values
3. UI renders attribute buttons (Color: [Red] [Blue], Size: [S] [M] [L])
4. User selection finds variant where ALL attributes match
5. Displays variant's stock and price (or base price if null)
6. Cart key: `{productId}-v{variantId}` (e.g., `5-v3`)

### Stock Deferral
- **Stock decremented on payment settlement** (not order creation)
- **POS Cash**: Immediate decrement
- **Online (Midtrans)**: Decrement on settlement webhook
- **Variant Items**: Only `product_variants.stock` decremented (NOT `products.stock`)
- **Payment Expired/Denied**: Stock restored

## Indexes

```sql
-- Users
CREATE UNIQUE INDEX idx_users_email ON users (email);
CREATE INDEX idx_users_role ON users (role);

-- Categories
CREATE UNIQUE INDEX idx_categories_slug ON categories (slug);

-- Products
CREATE UNIQUE INDEX idx_products_slug ON products (slug);
CREATE INDEX idx_products_category ON products (category_id);
CREATE INDEX idx_products_stock ON products (stock);

-- Product Images
CREATE INDEX idx_product_images_product ON product_images (product_id);

-- Product Sizes
CREATE INDEX idx_product_sizes_product ON product_sizes (product_id);

-- Product Variants
CREATE INDEX idx_product_variants_product ON product_variants (product_id);
CREATE UNIQUE INDEX idx_product_variants_sku ON product_variants (sku);

-- Product Reviews
CREATE INDEX idx_reviews_product ON product_reviews (product_id);
CREATE INDEX idx_reviews_user ON product_reviews (user_id);
CREATE INDEX idx_reviews_status ON product_reviews (status);

-- Orders
CREATE UNIQUE INDEX idx_orders_number ON orders (order_number);
CREATE INDEX idx_orders_buyer ON orders (buyer_id);
CREATE INDEX idx_orders_payment_status ON orders (payment_status);
CREATE INDEX idx_orders_created ON orders (created_at);

-- Order Items
CREATE INDEX idx_order_items_order ON order_items (order_id);
CREATE INDEX idx_order_items_product ON order_items (product_id);
CREATE INDEX idx_order_items_variant ON order_items (variant_id);
```

## Payment Flow

```
STORE CHECKOUT:
  Cart → Checkout Form → Create Order + Get Snap Token → Midtrans Snap Popup
  → User Pays → onSuccess → Success Page (with polling)
  → Midtrans Sends Webhook → Update Status → Decrement Stock → Create Shipment

POS CHECKOUT (Cash):
  Select Items → Click CASH → Confirm → Create Order → Decrement Stock → Reload

POS CHECKOUT (QRIS):
  Select Items → Click QRIS → Create Order + Get Snap Token → Snap Popup
  → User Scans QRIS → Webhook Updates Status
```

## Stock Management Rules

| Scenario | What Gets Decremented | When |
|----------|----------------------|------|
| POS Cash Checkout | `products.stock` OR `product_variants.stock` | Immediately on checkout |
| POS QRIS Checkout | Same | On Midtrans settlement webhook |
| Storefront Checkout | Same | On Midtrans settlement webhook |
| Payment Expired/Denied | Stock restored (+= quantity) | On Midtrans webhook |
| Order Manually Settled (Admin) | Stock decremented | On admin action |

**Important**: For variant items, only `product_variants.stock` is modified — `products.stock` is NOT decremented. This avoids double-deduction and keeps base product stock as a "theoretical maximum" when variants exist.

## Cart Key Strategy

```
Simple product:    {productId}          → "5"
Size variant:      {productId}-{size}   → "5-XL"
Advanced variant:  {productId}-v{variantId} → "5-v3"
```

Composite string keys allow the same product to appear multiple times in the cart with different variant selections, without needing a separate cart_items table.

## Data Flow

```
Browser → Apache/Nginx → index.php (front controller)
  → Routing → Filter Chain (CSRF, Auth, HTTPS, Cache)
  → Controller → Model → View → Response

Session-based Carts:
  - pos_cart (POS dashboard)
  - buyer_cart (Storefront)
  Stored server-side in PHP sessions
```

## Notes

- **MySQL/MariaDB**: Requires JSON column support (MySQL 5.7+, MariaDB 10.3+)
- **No ORM Overhead**: CI4's Model is thin; raw queries used for stock operations
- **CSRF Protection**: Cookie-based tokens, regenerated on every submission
- **Session-Based Auth**: No JWT/API tokens; role-based via `AuthFilter`
- **Midtrans Webhook**: Cryptographically verified via `verifyNotification()`
- **Biteship Integration**: Multi-courier shipping (JNE, TIKI, SiCepat, POS)