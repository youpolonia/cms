<?php

namespace Database\Factories;

use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VersionAnalyticsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'version_id' => ContentVersion::factory(),
            'content_id' => \App\Models\Content::factory(),
            'user_id' => User::factory(),
            'operation_type' => $this->faker->randomElement(['view', 'compare', 'restore']),
            'metrics' => [
                'sample_metric' => $this->faker->randomNumber(2)
            ],
            'notes' => $this->faker->optional()->sentence(),
            'view_count' => $this->faker->numberBetween(0, 100),
            'restore_count' => $this->faker->numberBetween(0, 20),
            'last_viewed_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'last_restored_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now')
        ];
    }

    public function comparison()
    {
        return $this->state(function (array $attributes) {
            return [
                'operation_type' => 'compare',
                'metrics' => array_merge($attributes['metrics'] ?? [], [
                    'diff_count' => $this->faker->numberBetween(1, 50),
                    'word_changes' => $this->faker->numberBetween(0, 500),
                    'duration_seconds' => $this->faker->randomFloat(3, 0.1, 10)
                ])
            ];
        });
    }

    public function restoration()
    {
        return $this->state(function (array $attributes) {
            return [
                'operation_type' => 'restore',
                'metrics' => array_merge($attributes['metrics'] ?? [], [
                    'reason' => $this->faker->sentence(),
                    'from_autosave' => $this->faker->boolean(),
                    'versions_skipped' => $this->faker->numberBetween(0, 10)
                ])
            ];
        });
    }
}
