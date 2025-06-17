<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class ItemStockSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('id_ID');

        $items = $this->db->table('items')->get()->getResultArray();

        foreach ($items as $item) {
            $this->db->table('item_stock')->insert([
                'item_id' => $item['id'],
                'quantity' => $faker->numberBetween(5, 100)
            ]);
        }
    }
}
