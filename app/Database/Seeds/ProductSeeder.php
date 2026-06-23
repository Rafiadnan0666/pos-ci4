<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['name' => 'Summit Pro 2-Person Tent', 'slug' => 'summit-pro-2-tent', 'category' => 'Tents', 'description' => 'Lightweight 2-person tent with waterproof flysheet and aluminum poles.', 'price' => 2899000, 'stock' => 12, 'weight_grams' => 2500],
            ['name' => 'Basecamp 4-Person Family Tent', 'slug' => 'basecamp-4-family-tent', 'category' => 'Tents', 'description' => 'Spacious 4-person cabin tent with divided rooms and large vestibule.', 'price' => 4599000, 'stock' => 8, 'weight_grams' => 6500],
            ['name' => 'Ultralight Bivy Sack', 'slug' => 'ultralight-bivy-sack', 'category' => 'Tents', 'description' => 'Minimalist bivy sack for solo adventures. Weighs only 500g.', 'price' => 1299000, 'stock' => 3, 'weight_grams' => 500],
            ['name' => 'Expedition Dome Tent 3-Season', 'slug' => 'expedition-dome-tent-3s', 'category' => 'Tents', 'description' => 'Durable 3-season dome tent for 3 people with mesh ventilation.', 'price' => 3499000, 'stock' => 6, 'weight_grams' => 3800],

            ['name' => 'Trailblazer 65L Backpack', 'slug' => 'trailblazer-65l-backpack', 'category' => 'Packs', 'description' => '65-liter internal frame backpack with adjustable torso and hip belt.', 'price' => 2199000, 'stock' => 15, 'weight_grams' => 1800],
            ['name' => 'Summit Daypack 25L', 'slug' => 'summit-daypack-25l', 'category' => 'Packs', 'description' => 'Lightweight 25-liter daypack with hydration sleeve and rain cover.', 'price' => 799000, 'stock' => 22, 'weight_grams' => 600],
            ['name' => 'Expedition Hauler 90L Duffel', 'slug' => 'expedition-hauler-90l', 'category' => 'Packs', 'description' => 'Rugged 90-liter duffel with backpack straps and waterproof TPU coating.', 'price' => 1599000, 'stock' => 7, 'weight_grams' => 1400],
            ['name' => 'Hydration Vest 8L', 'slug' => 'hydration-vest-8l', 'category' => 'Packs', 'description' => 'Ultra-running hydration vest with 8L capacity and 2 soft flasks.', 'price' => 599000, 'stock' => 4, 'weight_grams' => 350],

            ['name' => 'Hardshell Rain Jacket', 'slug' => 'hardshell-rain-jacket', 'category' => 'Apparel', 'description' => 'Breathable 3-layer waterproof jacket with sealed seams and hood.', 'price' => 2499000, 'stock' => 18, 'weight_grams' => 450],
            ['name' => 'Fleece Mid-Layer Hoodie', 'slug' => 'fleece-mid-layer-hoodie', 'category' => 'Apparel', 'description' => 'Warm 300-weight fleece hoodie with zippered pockets and thumb loops.', 'price' => 899000, 'stock' => 25, 'weight_grams' => 500],
            ['name' => 'Convertible Hiking Pants', 'slug' => 'convertible-hiking-pants', 'category' => 'Apparel', 'description' => 'Zip-off pants to shorts with UPF 50+ and quick-dry fabric.', 'price' => 699000, 'stock' => 2, 'weight_grams' => 400],
            ['name' => 'Merino Wool Base Layer', 'slug' => 'merino-wool-base-layer', 'category' => 'Apparel', 'description' => '200g/m² merino wool long sleeve for temperature regulation.', 'price' => 599000, 'stock' => 30, 'weight_grams' => 250],

            ['name' => 'Titanium Camping Stove', 'slug' => 'titanium-camping-stove', 'category' => 'Cooking', 'description' => 'Ultra-light titanium stove. Boils 1L in 3 minutes. 80g only.', 'price' => 499000, 'stock' => 11, 'weight_grams' => 80],
            ['name' => 'Mess Kit 4-Piece Set', 'slug' => 'mess-kit-4-piece', 'category' => 'Cooking', 'description' => 'Anodized aluminum pot, pan, bowl, and mug set. Nests compactly.', 'price' => 299000, 'stock' => 20, 'weight_grams' => 600],
            ['name' => 'Portable Camping Grill', 'slug' => 'portable-camping-grill', 'category' => 'Cooking', 'description' => 'Foldable stainless steel grill with carry case for campfire cooking.', 'price' => 399000, 'stock' => 9, 'weight_grams' => 1200],
            ['name' => 'Insulated Water Bottle 1L', 'slug' => 'insulated-water-bottle-1l', 'category' => 'Cooking', 'description' => 'Double-wall vacuum insulated. Cold 24h or hot 12h.', 'price' => 349000, 'stock' => 45, 'weight_grams' => 400],
        ];

        $categoryIds = [];
        $cats = $this->db->table('categories')->get()->getResult();
        foreach ($cats as $c) {
            $categoryIds[$c->name] = $c->id;
        }

        foreach ($products as $product) {
            $catId = $categoryIds[$product['category']] ?? null;
            $product['category_id'] = $catId;
            $existing = $this->db->table('products')->where('slug', $product['slug'])->get()->getRow();
            if ($existing) {
                $this->db->table('products')->where('slug', $product['slug'])->update(['category_id' => $catId]);
            } else {
                $this->db->table('products')->insert($product);
            }
        }
    }
}
