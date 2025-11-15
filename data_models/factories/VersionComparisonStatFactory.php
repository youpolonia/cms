<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\User;
use App\Models\VersionComparisonStat;
use Illuminate\Database\Eloquent\Factories\Factory;

class VersionComparisonStatFactory extends Factory
{
    protected $model = VersionComparisonStat::class;

    public function definition()
    {
        return [
            'version_a_id' => ContentVersion::factory(),
            'version_b_id' => ContentVersion::factory(),
            'content_id' => Content::factory(),
            'user_id' => User::factory(),
            'similarity_percentage' => $this->faker->numberBetween(50, 100),
            'lines_added' => $this->faker->numberBetween(0, 20),
            'lines_removed' => $this->faker->numberBetween(0, 15),
            'lines_unchanged' => $this->faker->numberBetween(10, 100),
            'words_added' => $this->faker->numberBetween(0, 200),
            'words_removed' => $this->faker->numberBetween(0, 150),
            'words_unchanged' => $this->faker->numberBetween(100, 1000),
            'frequent_changes' => [
                'headings' => $this->faker->numberBetween(0, 5),
                'paragraphs' => $this->faker->numberBetween(0, 10),
                'images' => $this->faker->numberBetween(0, 3),
                'metadata' => $this->faker->numberBetween(0, 2)
            ],
            'change_distribution' => [
                'introduction' => $this->faker->numberBetween(0, 30),
                'body' => $this->faker->numberBetween(0, 60),
                'conclusion' => $this->faker->numberBetween(0, 20),
                'sidebar' => $this->faker->numberBetween(0, 10)
            ]
        ];
    }
}
