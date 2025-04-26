<?php

namespace Database\Factories;

use App\Models\ThemeVersionComparisonStat;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeVersionComparisonStatFactory extends Factory
{
    protected $model = ThemeVersionComparisonStat::class;

    public function definition()
    {
        return [
            'theme_version_id' => \App\Models\ThemeVersion::factory(),
            'compared_version_id' => \App\Models\ThemeVersion::factory(),
            'total_size_diff_kb' => $this->faker->numberBetween(-500, 500),
            'css_size_diff_kb' => $this->faker->numberBetween(-200, 200),
            'js_size_diff_kb' => $this->faker->numberBetween(-200, 200),
            'image_size_diff_kb' => $this->faker->numberBetween(-100, 100),
            'file_count_diff' => $this->faker->numberBetween(-10, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
