<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Test\Fabricator;
use Tests\Support\Models\ItemFabricator;

class ItemSeeder extends Seeder
{
    public function run()
    {
        // populate racks & categories table first, avoid foreign key constraint fail
        // isi data tabel rak dan kategori dahulu, menghindari kegagalan fk contstraint
        $this->call('RackSeeder');
        $this->call('CategorySeeder');

        $fabricator = new Fabricator(ItemFabricator::class, locale: 'id_ID');
        // insert item data
        $fabricator->create(30);

        $this->call('ItemStockSeeder');
    }
}
