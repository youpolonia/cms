<?php

namespace Database\Factories;

use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentFactory extends Factory
{
    protected $model = Content::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'slug' => $this->faker->slug,
            'seo_title' => $this->faker->optional()->sentence,
            'seo_description' => $this->faker->optional()->paragraph,
            'seo_keywords' => json_encode($this->faker->words(5)),
            'content_type' => $this->faker->randomElement(['page', 'post', 'article']),
            'ai_metadata' => null,
            'user_id' => \App\Models\User::factory(),
            'created_by' => \App\Models\User::factory()
        ];
    }
}
