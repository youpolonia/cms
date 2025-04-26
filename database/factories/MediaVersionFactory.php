<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\MediaVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaVersionFactory extends Factory
{
    protected $model = MediaVersion::class;

    public function definition()
    {
        return [
            'media_id' => Media::factory(),
            'user_id' => User::factory(),
            'version_number' => $this->faker->numberBetween(1, 10),
            'filename' => $this->faker->word.'.'.$this->faker->fileExtension,
            'path' => 'media/'.$this->faker->uuid,
            'metadata' => [
                'size' => $this->faker->numberBetween(1000, 1000000),
                'mime_type' => $this->faker->mimeType,
                'width' => $this->faker->numberBetween(100, 4000),
                'height' => $this->faker->numberBetween(100, 4000),
            ],
            'changes' => [
                'description' => $this->faker->sentence,
                'metadata' => [
                    'modified' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s')
                ]
            ]
        ];
    }
}
