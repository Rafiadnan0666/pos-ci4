<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterProductsAddAttributes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'size' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'weight_grams',
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'size',
            ],
            'material' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'color',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('products', ['size', 'color', 'material']);
    }
}
