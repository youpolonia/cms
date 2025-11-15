<?php

namespace Database\Factories;

use App\Models\ContentSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentScheduleFactory extends Factory
{
    protected $model = ContentSchedule::class;

    public function definition(): array
    {
        return [
            'content_id' => \App\Models\Content::factory(),
            'publish_at' => $this->faker->dateTimeBetween('now', '+1 year'),
            'unpublish_at' => $this->faker->dateTimeBetween('+1 year', '+2 years'),
            'recurrence' => null,
            'status' => 'pending',
            'processed_at' => null,
            'user_id' => \App\Models\User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status' => 'published',
            'processed_at' => now(),
        ]);
    }

    public function withRecurrence(): static
    {
        return $this->state([
            'recurrence' => [
                'frequency' => 'weekly',
                'interval' => 1,
                'count' => 5
            ]
        ]);
    }
}
