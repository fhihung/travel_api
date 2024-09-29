<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name' => 'Groceries',
                'type' => 1,
                'icon_path' => 'icons/groceries.png',
                'description' => 'Expenses for food and household supplies',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Utilities',
                'type' => 1,
                'icon_path' => 'icons/utilities.png',
                'description' => 'Expenses for electricity, water, and gas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
