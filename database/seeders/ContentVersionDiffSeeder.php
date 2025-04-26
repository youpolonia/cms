<?php

namespace Database\Seeders;

use App\Jobs\ModerateContentJob;
use App\Models\ContentVersionDiff;
use App\Observers\ContentObserver;
use Illuminate\Database\Seeder;

class ContentVersionDiffSeeder extends Seeder
{
    public function run()
    {
        // Temporarily disable model events
        \App\Models\Content::unsetEventDispatcher();
        \App\Models\ContentVersion::unsetEventDispatcher();
        
        try {
            // Clean up any existing test data first
            \App\Models\ContentVersionDiff::whereHas('fromVersion.content', function($q) {
                $q->where('slug', 'like', 'test-content-%');
            })->delete();
            
            \App\Models\ContentVersion::whereHas('content', function($q) {
                $q->where('slug', 'like', 'test-content-%');
            })->delete();
            
            \App\Models\Content::where('slug', 'like', 'test-content-%')->delete();
            
            // Get or create test user
            $user = \App\Models\User::where('email', 'test@example.com')->first();
            
            if (!$user) {
                $user = \App\Models\User::create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password')
                ]);
            }

            // Create test content with unique slugs
            $timestamp = now()->timestamp;
            $content1 = \App\Models\Content::create([
                'title' => 'Test Content 1',
                'content' => 'Sample content for testing',
                'slug' => 'test-content-1-' . $timestamp,
                'content_type' => 'article',
                'user_id' => $user->id,
                'created_by' => $user->id
            ]);
            
            $content2 = \App\Models\Content::create([
                'title' => 'Test Content 2',
                'content' => 'Another sample content for testing',
                'slug' => 'test-content-2-' . $timestamp,
                'content_type' => 'article',
                'user_id' => $user->id,
                'created_by' => $user->id
            ]);
        
            $version1 = \App\Models\ContentVersion::create([
                'content_id' => $content1->id,
                'user_id' => 1,
                'content' => 'This is the original content body text.',
                'content_data' => json_encode(['body' => 'This is the original content body text.']),
                'status' => 'draft',
                'version_number' => 1,
                'is_autosave' => false
            ]);
            
            $version2 = \App\Models\ContentVersion::create([
                'content_id' => $content1->id,
                'user_id' => 1,
                'content' => 'This is the updated content body with some changes.',
                'content_data' => json_encode(['body' => 'This is the updated content body with some changes.']),
                'status' => 'published',
                'version_number' => 2,
                'is_autosave' => false
            ]);
            
            $version3 = \App\Models\ContentVersion::create([
                'content_id' => $content2->id,
                'user_id' => 1,
                'content' => 'Laravel is a PHP framework.',
                'content_data' => json_encode(['body' => 'Laravel is a PHP framework.']),
                'status' => 'draft',
                'version_number' => 1,
                'is_autosave' => false
            ]);
            
            $version4 = \App\Models\ContentVersion::create([
                'content_id' => $content2->id,
                'user_id' => 1,
                'content' => 'Laravel is a modern PHP framework.',
                'content_data' => json_encode(['body' => 'Laravel is a modern PHP framework.']),
                'status' => 'published',
                'version_number' => 2,
                'is_autosave' => false
            ]);

            // Now create the diffs
            ContentVersionDiff::create([
                'from_version_id' => $version1->id,
                'to_version_id' => $version2->id,
                'diff_content' => json_encode([
                    'similarity_percentage' => 75.5,
                    'body' => [
                        'old' => 'This is the original content body text.',
                        'new' => 'This is the updated content body with some changes.',
                        'status' => 'modified'
                    ],
                    'status' => [
                        'old' => 'draft',
                        'new' => 'published',
                        'status' => 'modified'
                    ]
                ])
            ]);

            ContentVersionDiff::create([
                'from_version_id' => $version3->id,
                'to_version_id' => $version4->id,
                'diff_content' => json_encode([
                    'similarity_percentage' => 90.2,
                    'body' => [
                        'old' => 'Laravel is a PHP framework.',
                        'new' => 'Laravel is a modern PHP framework.',
                        'status' => 'modified'
                    ],
                    'author' => [
                        'old' => null,
                        'new' => 'John Doe',
                        'status' => 'added'
                    ]
                ])
            ]);

            // Re-enable foreign key constraints and model events
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
            \App\Models\Content::setEventDispatcher(app('events'));
            \App\Models\ContentVersion::setEventDispatcher(app('events'));
        } catch (\Exception $e) {
            // Re-enable model events even if seeding fails
            \App\Models\Content::setEventDispatcher(app('events'));
            \App\Models\ContentVersion::setEventDispatcher(app('events'));
            throw $e;
        }
        
        $version3 = \App\Models\ContentVersion::create([
            'content_id' => $content2->id,
            'content' => 'Laravel is a PHP framework.',
            'content_data' => json_encode(['body' => 'Laravel is a PHP framework.']),
            'status' => 'draft',
            'version_number' => 3,
            'is_autosave' => false,
            'user_id' => 1
        ]);
        
        $version4 = \App\Models\ContentVersion::create([
            'content_id' => $content2->id,
            'content' => 'Laravel is a modern PHP framework.',
            'content_data' => json_encode(['body' => 'Laravel is a modern PHP framework.']),
            'status' => 'published',
            'version_number' => 4,
            'is_autosave' => false,
            'user_id' => 1
        ]);

        // Now create the diffs
        ContentVersionDiff::create([
            'from_version_id' => $version1->id,
            'to_version_id' => $version2->id,
            'diff_content' => json_encode([
                'similarity_percentage' => 75.5,
                'title' => [
                    'old' => 'Original Content Title',
                    'new' => 'Updated Content Title',
                    'status' => 'modified'
                ],
                'body' => [
                    'old' => 'This is the original content body text.',
                    'new' => 'This is the updated content body with some changes.',
                    'status' => 'modified'
                ],
                'tags' => [
                    'old' => ['tech', 'programming'],
                    'new' => ['tech', 'development', 'web'],
                    'status' => 'modified'
                ],
                'status' => [
                    'old' => 'draft',
                    'new' => 'published',
                    'status' => 'modified'
                ]
            ])
        ]);

        ContentVersionDiff::create([
            'from_version_id' => $version3->id,
            'to_version_id' => $version4->id,
            'diff_content' => json_encode([
                'similarity_percentage' => 90.2,
                'title' => [
                    'old' => 'Getting Started with Laravel',
                    'new' => 'Getting Started with Laravel',
                    'status' => 'unchanged'
                ],
                'body' => [
                    'old' => 'Laravel is a PHP framework.',
                    'new' => 'Laravel is a modern PHP framework.',
                    'status' => 'modified'
                ],
                'author' => [
                    'old' => null,
                    'new' => 'John Doe',
                    'status' => 'added'
                ]
            ])
        ]);
    }
}
