<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('users')->insert([
            'name'     => 'Admin Outdoor',
            'email'    => 'admin@outdoor.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role'     => 'owner',
        ]);

        $this->db->table('users')->insert([
            'name'     => 'Demo Buyer',
            'email'    => 'buyer@outdoor.com',
            'password' => password_hash('buyer123', PASSWORD_BCRYPT),
            'role'     => 'buyer',
        ]);
    }
}
