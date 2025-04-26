<?php

namespace Database\Seeders;

use App\Models\ErrorCategory;
use Illuminate\Database\Seeder;

class ErrorCategorySeeder extends Seeder
{
    public function run()
    {
        foreach (ErrorCategory::systemCategories() as $category) {
            ErrorCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}