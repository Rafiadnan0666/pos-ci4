<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterProductsAddAdvancedFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'brand' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'material',
            ],
            'dimension_length' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'brand',
            ],
            'dimension_width' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'dimension_length',
            ],
            'dimension_height' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'dimension_width',
            ],
            'warranty' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'dimension_height',
            ],
            'care_instructions' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'warranty',
            ],
            'features' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'care_instructions',
            ],
            'specifications' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'features',
            ],
            'video_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'specifications',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('products', [
            'brand', 'dimension_length', 'dimension_width', 'dimension_height',
            'warranty', 'care_instructions', 'features', 'specifications', 'video_url'
        ]);
    }
}
