<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->insert([
            [
                'name' => 'Featured Category',
                'slug' => 'featured-category',
                'description' => 'This is a featured category',
                'is_active' => true,
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Regular Category 1',
                'slug' => 'regular-category-1',
                'description' => 'This is a regular category',
                'is_active' => true,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Regular Category 2',
                'slug' => 'regular-category-2',
                'description' => 'This is another regular category',
                'is_active' => true,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
