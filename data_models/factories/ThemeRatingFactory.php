<?php

namespace Database\Factories;

use App\Models\Theme;
use App\Models\ThemeRating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeRatingFactory extends Factory
{
    protected $model = ThemeRating::class;

    public function definition(): array
    {
        return [
            'theme_id' => Theme::factory(),
            'user_id' => User::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'review' => $this->faker->paragraph,
        ];
    }
}
