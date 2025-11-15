<?php

namespace Database\Factories;

use App\Models\UserProfileData;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileDataFactory extends Factory
{
    protected $model = UserProfileData::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'demographics' => ['age' => 30, 'gender' => 'male', 'location' => 'US'],
            'content_preferences' => ['topics' => ['technology'], 'average_engagement' => 0.75],
            'behavioral_patterns' => ['content_interactions' => ['article_view']],
            'opt_in_tracking' => true,
            'last_updated_at' => now()
        ];
    }
}
