<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Perangkat presentasi',
            ],
            [
                'name' => 'Perangkat konektivitas',
            ],
            [
                'name' => 'Perangkat jaringan',
            ],
            [
                'name' => 'Perangkat multimedia dan dokumentasi',
            ],
            [
                'name' => 'Perangkat penyimpanan',
            ]
        ];

        $this->db->table('categories')->insertBatch($data);
    }
}
