<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder as DatabaseSeeder;
use CodeIgniter\Model;

class Seeder extends DatabaseSeeder
{
    public function run()
    {
        $superadminExists = (new Model)
            ->setTable('auth_groups_users')
            ->where('group', 'superadmin')
            ->first();

        if (!$superadminExists) {
            // run superadmin seeder
            $this->call('SuperAdminSeeder');
        }

        // run category, rack, item & itemstock seeder
        $this->call('ItemSeeder');

        // run member seeder
        $this->call('MemberSeeder');

        // run loan & fine seeder
        $this->call('LoanSeeder');
    }
}
