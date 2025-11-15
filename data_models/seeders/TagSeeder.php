<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run()
    {
        $tags = [
            ['name' => 'Technology', 'color' => '#3b82f6'],
            ['name' => 'Science', 'color' => '#10b981'],
            ['name' => 'Art', 'color' => '#f59e0b'],
            ['name' => 'Design', 'color' => '#6366f1'],
            ['name' => 'Business', 'color' => '#ef4444'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
