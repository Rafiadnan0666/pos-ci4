<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Tents',    'slug' => 'tents',    'icon' => '⛺'],
            ['name' => 'Packs',    'slug' => 'packs',    'icon' => '🎒'],
            ['name' => 'Apparel',  'slug' => 'apparel',  'icon' => '🧥'],
            ['name' => 'Cooking',  'slug' => 'cooking',  'icon' => '🍳'],
        ];

        $this->db->table('categories')->insertBatch($categories);

        $catMap = [];
        foreach ($categories as $c) {
            $row = $this->db->table('categories')->where('slug', $c['slug'])->get()->getRow();
            if ($row) {
                $catMap[$c['name']] = $row->id;
            }
        }

        $products = $this->db->table('products')->get()->getResult();
        foreach ($products as $p) {
            if (isset($catMap[$p->category])) {
                $this->db->table('products')->update(['category_id' => $catMap[$p->category]], ['id' => $p->id]);
            }
        }
    }
}
