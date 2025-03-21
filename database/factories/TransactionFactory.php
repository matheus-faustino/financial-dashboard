<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
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
            'amount' => fake()->randomFloat(2, 10, 1000),
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'description' => fake()->sentence(),
            'payment_method' => fake()->randomElement(['Credit Card', 'Debit Card', 'Cash', 'Bank Transfer', 'PayPal']),
            'location' => fake()->optional(0.7)->city(),
            'is_recurring' => false,
            'recurrence_pattern' => null,
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
        ];
    }

    /**
     * Indicate that the transaction is recurring.
     */
    public function recurring(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_recurring' => true,
            'recurrence_pattern' => fake()->randomElement(['weekly', 'biweekly', 'monthly', 'quarterly', 'yearly']),
        ]);
    }

    /**
     * Set a specific date range for the transaction.
     */
    public function dateRange(string $startDate, string $endDate): static
    {
        return $this->state(fn(array $attributes) => [
            'date' => fake()->dateTimeBetween($startDate, $endDate),
        ]);
    }
}
