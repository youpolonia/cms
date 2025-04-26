<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // Other seeders...
            TagSeeder::class,
            RoadmapTaskSeeder::class,
            VersionComparisonStatSeeder::class,
        ]);
    }
}
