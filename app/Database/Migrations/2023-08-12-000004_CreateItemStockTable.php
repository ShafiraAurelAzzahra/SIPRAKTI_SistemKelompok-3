<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemStockTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'item_id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
            ],
            'quantity' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL',
            'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL',
            'deleted_at TIMESTAMP NULL',
        ]);

        // primary key
        $this->forge->addKey('id', primary: TRUE);

        // item id foreign key
        $this->forge->addForeignKey('item_id', 'items', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('item_stock', TRUE);
    }

    public function down()
    {
        $this->forge->dropTable('item_stock');
    }
}
