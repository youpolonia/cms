<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentInteraction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentInteractionFactory extends Factory
{
    protected $model = ContentInteraction::class;

    public function definition()
    {
        return [
            'content_id' => Content::factory(),
            'user_id' => User::factory(),
            'interaction_type' => $this->faker->randomElement(['view', 'click', 'share', 'save']),
            'metadata' => [
                'ip' => $this->faker->ipv4,
                'user_agent' => $this->faker->userAgent,
                'referrer' => $this->faker->optional()->url,
            ],
        ];
    }
}
