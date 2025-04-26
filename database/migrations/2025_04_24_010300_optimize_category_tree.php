<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('left')->nullable()->index();
            $table->unsignedInteger('right')->nullable()->index();
            $table->unsignedInteger('depth')->default(0)->index();
            $table->string('path')->nullable()->index();
        });

        // Populate initial values - skip recursive CTE for SQLite
        if (\DB::connection()->getDriverName() !== 'sqlite') {
            \DB::statement('
                WITH RECURSIVE category_tree AS (
                    SELECT id, parent_id, name, 0 AS depth, CAST(id AS CHAR(200)) AS path
                    FROM categories WHERE parent_id IS NULL
                    
                    UNION ALL
                    
                    SELECT c.id, c.parent_id, c.name, ct.depth + 1, CONCAT(ct.path, ",", c.id)
                    FROM categories c
                    JOIN category_tree ct ON c.parent_id = ct.id
                )
                UPDATE categories c
                JOIN category_tree ct ON c.id = ct.id
                SET c.depth = ct.depth, c.path = ct.path
            ');
        } else {
            // Simple fallback for SQLite
            $categories = \App\Models\Category::all();
            foreach ($categories as $category) {
                $category->update([
                    'depth' => 0,
                    'path' => (string)$category->id
                ]);
            }
        }

        // Calculate left/right values
        $categories = \App\Models\Category::with('children')->get();
        $counter = 1;
        
        $traverse = function ($categories, $depth = 0) use (&$traverse, &$counter) {
            foreach ($categories as $category) {
                $category->left = $counter++;
                $traverse($category->children, $depth + 1);
                $category->right = $counter++;
                $category->save();
            }
        };
        
        $traverse($categories->where('parent_id', null));
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['left', 'right', 'depth', 'path']);
        });
    }
};