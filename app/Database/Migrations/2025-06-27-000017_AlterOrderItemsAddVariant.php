<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOrderItemsAddVariant extends Migration
{
    public function up()
    {
        $this->forge->addColumn('order_items', [
            'variant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'size',
            ],
            'variant_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'variant_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('order_items', 'variant_id');
        $this->forge->dropColumn('order_items', 'variant_label');
    }
}
