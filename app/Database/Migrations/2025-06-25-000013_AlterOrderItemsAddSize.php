<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOrderItemsAddSize extends Migration
{
    public function up()
    {
        $this->forge->addColumn('order_items', [
            'size' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'product_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('order_items', 'size');
    }
}
