<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed two demo users for the interview walkthrough (password:
     * "password" for both). Model events must stay enabled here — the
     * wallet is created by User::booted()'s `created` listener, not by
     * this seeder.
     */
    public function run(): void
    {
        $alice = User::factory()->create([
            'name' => 'Alice Demo',
            'email' => 'alice@example.com',
        ]);

        User::factory()->create([
            'name' => 'Bob Demo',
            'email' => 'bob@example.com',
        ]);

        $alice->wallet->update(['balance' => 100000]);

        Transaction::factory()->deposit()->for($alice->wallet, 'wallet')->create([
            'amount' => 100000,
        ]);
    }
}
