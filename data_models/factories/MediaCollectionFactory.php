<?php

namespace Database\Factories;

use App\Models\MediaCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaCollectionFactory extends Factory
{
    protected $model = MediaCollection::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug,
            'description' => $this->faker->sentence(),
            'is_private' => $this->faker->boolean(),
            'user_id' => User::factory(),
        ];
    }
}
