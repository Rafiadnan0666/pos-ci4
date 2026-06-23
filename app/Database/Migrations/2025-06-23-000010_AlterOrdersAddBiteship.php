<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOrdersAddBiteship extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'biteship_order_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'midtrans_snap_token',
            ],
            'tracking_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'biteship_order_id',
            ],
            'tracking_url' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'tracking_number',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['biteship_order_id', 'tracking_number', 'tracking_url']);
    }
}
