<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'related_wallet_id' => null,
            'type' => TransactionType::Deposit,
            'status' => TransactionStatus::Completed,
            'amount' => fake()->numberBetween(100, 100000),
            'reference_id' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the transaction is a deposit.
     */
    public function deposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Deposit,
            'related_wallet_id' => null,
        ]);
    }

    /**
     * Indicate that the transaction is an outgoing transfer.
     */
    public function transferOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::TransferOut,
            'related_wallet_id' => Wallet::factory(),
        ]);
    }

    /**
     * Indicate that the transaction is an incoming transfer.
     */
    public function transferIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::TransferIn,
            'related_wallet_id' => Wallet::factory(),
        ]);
    }

    /**
     * Indicate that the transaction is a reversal of another transaction.
     */
    public function reversal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Reversal,
            'reference_id' => Transaction::factory(),
        ]);
    }

    /**
     * Indicate that the transaction is still pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Pending,
        ]);
    }

    /**
     * Indicate that the transaction has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Failed,
        ]);
    }
}
