<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminAccountSeeder::class,
            PermissionsSeeder::class,
            DepartmentsSeeder::class,
            PositionsSeeder::class,
            AdminAccountSeeder::class, // update department
            MenusSeeder::class,
            DistrictsSeeder::class,
            WardsSeeder::class,
            UnitsSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                UsersSeeder::class,
                PartyMembersSeeder::class,
            ]);
        }
    }
}
