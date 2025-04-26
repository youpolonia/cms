<?php

namespace Database\Factories;

use App\Models\Block;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlockFactory extends Factory
{
    protected $model = Block::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word,
            'content' => ['type' => 'text', 'value' => $this->faker->sentence],
            'meta' => ['locked' => false],
            'is_template' => false
        ];
    }

    public function locked()
    {
        return $this->state(function (array $attributes) {
            return ['meta' => ['locked' => true]];
        });
    }
}