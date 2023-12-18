<?php

namespace Database\Seeders;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      

        User::factory()->count(10)->create();//el seeder de usuarios
        Account::factory()->count(10)->create();//el seeder de cuentas
        Transaction::factory()->count(10)->create(); // Seeder de transactions
    }
}
