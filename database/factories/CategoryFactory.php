<?php

namespace Database\Factories;

use App\Enums\CategoryTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'type' => fake()->randomElement(CategoryTypeEnum::cases()),
            'color' => fake()->hexColor(),
            'is_system' => false,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the category is a system category.
     */
    public function system(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_system' => true,
            'user_id' => null,
        ]);
    }

    /**
     * Set a specific category type.
     */
    public function type(CategoryTypeEnum $type): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => $type,
        ]);
    }
}
