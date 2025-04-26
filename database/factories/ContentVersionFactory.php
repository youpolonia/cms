<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentVersionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'created_by' => User::factory(),
            'version_number' => $this->faker->randomNumber(2),
            'content' => $this->faker->paragraphs(3, true),
            'change_description' => $this->faker->sentence,
            'is_autosave' => $this->faker->boolean(20),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'approval_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approval_notes' => $this->faker->optional()->sentence,
            'tags' => $this->faker->optional()->words(3),
            'times_compared' => $this->faker->randomNumber(1),
            'restore_count' => $this->faker->randomNumber(1),
        ];
    }

    public function approved(): static
    {
        return $this->state([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => User::factory(),
        ]);
    }

    public function restored(): static
    {
        return $this->state([
            'is_restored' => true,
            'restored_at' => now(),
            'restored_by' => User::factory(),
            'restored_from_version_id' => fn() => self::factory(),
        ]);
    }

    public function merged(): static
    {
        return $this->state([
            'is_merged' => true,
            'merged_at' => now(),
            'parent_version_id' => fn() => self::factory(),
        ]);
    }
}
