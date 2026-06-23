<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersAddProfileFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'avatar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'role',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'avatar',
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'phone',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['avatar', 'phone', 'address']);
    }
}
