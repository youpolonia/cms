<?php

namespace Database\Factories;

use App\Models\ContentVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentVersionFactory extends Factory
{
    protected $model = ContentVersion::class;

    public function definition()
    {
        return [
            'content_id' => \App\Models\Content::factory(),
            'version_number' => $this->faker->unique()->numberBetween(1, 100),
            'content' => $this->faker->paragraphs(3, true),
            'content_data' => json_encode(['content' => $this->faker->paragraphs(3, true)]), // Temporary backward compatibility
            'created_by' => \App\Models\User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year'),
        ];
    }
}
