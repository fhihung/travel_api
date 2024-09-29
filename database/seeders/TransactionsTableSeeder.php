<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('transactions')->insert([
            [
                'title' => 'Grocery Shopping',
                'amount' => 150.00,
                'note' => 'Weekly groceries',
                'transaction_date' => now(),
                'category_id' => 1,
                'account_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'user_id' => 1,
            ],
            [
                'title' => 'Electricity Bill',
                'amount' => 75.00,
                'note' => 'Monthly electricity bill',
                'transaction_date' => now(),
                'category_id' => 2,
                'account_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
                'user_id' => 2,
            ],
        ]);
    }
}
