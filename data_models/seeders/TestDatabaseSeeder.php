<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Content;
use App\Models\VersionAnalytics;

class TestDatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        // Create test content
        $content = Content::factory()->create([
            'title' => 'Test Content',
            'slug' => 'test-content',
            'content' => 'Test content body',
            'user_id' => $user->id,
            'created_by' => $user->id
        ]);

        // Create test analytics data
        VersionAnalytics::factory()->count(5)->create([
            'version_id' => 1,
            'user_id' => $user->id
        ]);
    }
}
