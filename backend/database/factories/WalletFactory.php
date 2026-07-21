<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'balance' => 0,
        ];
    }

    /**
     * Indicate that the wallet has a negative balance.
     */
    public function negativeBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => fake()->numberBetween(-50000, -1),
        ]);
    }

    /**
     * Indicate that the wallet has a specific balance, in cents.
     */
    public function withBalance(int $cents): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $cents,
        ]);
    }
}
