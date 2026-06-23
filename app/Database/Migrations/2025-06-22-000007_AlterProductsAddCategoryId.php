<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterProductsAddCategoryId extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'category',
            ],
        ]);

        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('products', 'products_category_id_foreign');
        $this->forge->dropColumn('products', 'category_id');
    }
}
