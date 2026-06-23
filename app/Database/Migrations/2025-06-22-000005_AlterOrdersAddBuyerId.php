<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOrdersAddBuyerId extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'buyer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'after'      => 'order_number',
            ],
        ]);

        $this->db->table('orders')->update(['buyer_id' => 1]);

        $this->forge->dropColumn('orders', ['buyer_name', 'buyer_email', 'buyer_phone']);

        $this->forge->addForeignKey('buyer_id', 'users', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('orders', 'orders_buyer_id_foreign');
        $this->forge->addColumn('orders', [
            'buyer_name'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'buyer_email' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'buyer_phone' => ['type' => 'VARCHAR', 'constraint' => 20],
        ]);
        $this->forge->dropColumn('orders', 'buyer_id');
    }
}
