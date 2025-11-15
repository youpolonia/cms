<?php

namespace Database\Seeders;

use App\Models\VersionComparisonStat;
use Illuminate\Database\Seeder;

class VersionComparisonStatSeeder extends Seeder
{
    public function run()
    {
        // Create 50 random comparison stats
        VersionComparisonStat::factory()
            ->count(50)
            ->create();

        // Create some specific test cases
        VersionComparisonStat::factory()
            ->create([
                'similarity_percentage' => 100,
                'lines_added' => 0,
                'lines_removed' => 0,
                'words_added' => 0,
                'words_removed' => 0,
            ]);

        VersionComparisonStat::factory()
            ->create([
                'similarity_percentage' => 25,
                'lines_added' => 50,
                'lines_removed' => 40,
                'words_added' => 500,
                'words_removed' => 400,
            ]);
    }
}
