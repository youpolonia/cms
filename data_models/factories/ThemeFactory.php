<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeFactory extends Factory
{
    protected $model = Theme::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'version' => $this->faker->semver,
            'author' => $this->faker->name,
            'is_active' => false,
            'screenshot' => $this->faker->imageUrl(),
        ];
    }
}
