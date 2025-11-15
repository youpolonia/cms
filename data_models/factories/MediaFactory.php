<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition()
    {
        return [
            'id' => $this->faker->uuid,
            'filename' => $this->faker->word.'.'.$this->faker->fileExtension,
            'path' => 'media/'.$this->faker->unique()->word,
            'mime_type' => $this->faker->mimeType,
            'size' => $this->faker->numberBetween(1000, 1000000),
            'disk' => 'public',
            'alt_text' => $this->faker->sentence,
            'caption' => $this->faker->sentence,
            'is_public' => $this->faker->boolean,
            'metadata' => [
                'width' => $this->faker->numberBetween(100, 2000),
                'height' => $this->faker->numberBetween(100, 2000),
                'colors' => $this->faker->hexcolor
            ],
            'user_id' => User::factory()
        ];
    }
}
