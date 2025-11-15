<?php

namespace Database\Factories;

use App\Models\UserProfileData;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileDataFactory extends Factory
{
    protected $model = UserProfileData::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'demographics' => [],
            'content_preferences' => [],
            'behavioral_patterns' => [],
            'opt_in_tracking' => true,
            'last_updated_at' => now()
        ];
    }
}
