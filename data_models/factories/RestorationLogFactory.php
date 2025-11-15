<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RestorationLog>
 */
class RestorationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content_version_id' => \App\Models\ContentVersion::factory(),
            'restored_by' => \App\Models\User::factory(),
            'original_version_id' => \App\Models\ContentVersion::factory(),
            'restoration_notes' => $this->faker->sentence(),
            'metadata' => [
                'restoration_method' => $this->faker->randomElement(['auto', 'manual']),
                'changes_count' => $this->faker->numberBetween(1, 20)
            ],
            'completed_at' => $this->faker->optional()->dateTimeThisMonth()
        ];
    }
}
