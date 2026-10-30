<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CarSeeder::class);
        // UserSeeder (admin + игрок) добавим на этапе с Breeze
    }
}
