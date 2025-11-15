<?php

namespace Database\Seeders;

require_once __DIR__ . '/../../config.php';

// Assuming App\Services\DatabaseConnection is available and autoloaded or required elsewhere.
// If not, it should be: require_once __DIR__ . '/../../app/Services/DatabaseConnection.php';
// Similarly for other models if not using an autoloader.
require_once __DIR__ . '/../../app/Models/User.php';
require_once __DIR__ . '/../../app/Models/Content.php';
require_once __DIR__ . '/../../app/Models/ContentVersion.php';
require_once __DIR__ . '/../../app/Models/ContentVersionDiff.php';
// The Seeder class itself might need to be defined or this class might not extend anything
// if we are removing all framework dependencies. For now, assuming a base Seeder class exists.
// use Illuminate\Database\Seeder; // This line will be removed as we are removing Laravel dependencies

class ContentVersionDiffSeeder // extends Seeder // No longer extending Laravel's Seeder
{
    public function run()
    {
        $pdo = \core\Database::connection();

        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0;'); // Disable FK checks for cleanup

            // Clean up existing test data using direct PDO
            // 1. Get content IDs for test content
            $stmt = $pdo->prepare("SELECT id FROM content_items WHERE slug LIKE :slug_pattern");
            $stmt->execute(['slug_pattern' => 'test-content-%']);
            $contentIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($contentIds)) {
                $contentIdsPlaceholder = implode(',', array_fill(0, count($contentIds), '?'));

                // 2. Get version IDs related to those content IDs
                $stmt = $pdo->prepare("SELECT id FROM content_versions WHERE content_id IN ($contentIdsPlaceholder)");
                $stmt->execute($contentIds);
                $versionIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                if (!empty($versionIds)) {
                    $versionIdsPlaceholder = implode(',', array_fill(0, count($versionIds), '?'));
                    // 3. Delete from content_version_diffs
                    $stmt = $pdo->prepare("DELETE FROM content_version_diffs WHERE from_version_id IN ($versionIdsPlaceholder) OR to_version_id IN ($versionIdsPlaceholder)");
                    $stmt->execute(array_merge($versionIds, $versionIds));
                }
                
                // 4. Delete from content_versions
                $stmt = $pdo->prepare("DELETE FROM content_versions WHERE content_id IN ($contentIdsPlaceholder)");
                $stmt->execute($contentIds);
            }
            
            // 5. Delete from content_items
            \App\Models\Content::deleteWhere($pdo, ['slug' => ['operator' => 'LIKE', 'value' => 'test-content-%']]);

            // Get or create test user
            $userData = \App\Models\User::findWhere($pdo, ['email' => 'test@example.com'], ['limit' => 1]);
            $user = $userData ? $userData[0] : null; // Assuming findWhere returns an array of User objects or null/empty array
            
            if (!$user) {
                $user = \App\Models\User::create($pdo, [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => password_hash('password', PASSWORD_DEFAULT)
                ]);
            }

            $timestamp = (new \DateTime())->getTimestamp();
            $content1 = \App\Models\Content::create($pdo, [
                'title' => 'Test Content 1',
                'content' => 'Sample content for testing',
                'slug' => 'test-content-1-' . $timestamp,
                'content_type' => 'article',
                'user_id' => $user->id, // Assumes User model instance has an 'id' property
                'created_by' => $user->id
            ]);
            
            $content2 = \App\Models\Content::create($pdo, [
                'title' => 'Test Content 2',
                'content' => 'Another sample content for testing',
                'slug' => 'test-content-2-' . $timestamp,
                'content_type' => 'article',
                'user_id' => $user->id,
                'created_by' => $user->id
            ]);
        
            $version1 = \App\Models\ContentVersion::create($pdo, [
                'content_id' => $content1->id, // Assumes Content model instance has an 'id' property
                'user_id' => $user->id,
                'content' => 'This is the original content body text.',
                'status' => 'draft',
                'version_number' => 1,
                'is_autosave' => false
            ]);
            
            $version2 = \App\Models\ContentVersion::create($pdo, [
                'content_id' => $content1->id,
                'user_id' => $user->id,
                'content' => 'This is the updated content body with some changes.',
                'status' => 'published',
                'version_number' => 2,
                'is_autosave' => false
            ]);
            
            $version3_original = \App\Models\ContentVersion::create($pdo, [
                'content_id' => $content2->id,
                'user_id' => $user->id,
                'content' => 'Laravel is a PHP framework.',
                'status' => 'draft',
                'version_number' => 1,
                'is_autosave' => false
            ]);
            
            $version4_original = \App\Models\ContentVersion::create($pdo, [
                'content_id' => $content2->id,
                'user_id' => $user->id,
                'content' => 'Laravel is a modern PHP framework.',
                'status' => 'published',
                'version_number' => 2,
                'is_autosave' => false
            ]);

            // Now create the diffs
            \App\Models\ContentVersionDiff::create($pdo, [
                'from_version_id' => $version1->id, // Assumes ContentVersion model instance has an 'id' property
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

            \App\Models\ContentVersionDiff::create($pdo, [
                'from_version_id' => $version3_original->id,
                'to_version_id' => $version4_original->id,
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

            // Re-enable foreign key constraints
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');

        } catch (\Exception $e) {
            // Ensure foreign key checks are re-enabled in case of an error
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
            error_log("Error during seeding ContentVersionDiffSeeder: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            // Optionally, re-throw or handle more gracefully
            throw $e;
        }
        
        // The following block seems to be a duplicate or continuation of creating versions and diffs.
        // It reuses $version3 and $version4 variable names which were already used for $version3_original and $version4_original.
        // Renaming to avoid confusion and potential errors if original $version3/$version4 were expected later.
        // Also, user_id was hardcoded to 1, changing to $user->id for consistency
        $version3_new_set = \App\Models\ContentVersion::create($pdo, [
            'content_id' => $content2->id,
            'content' => 'Laravel is a PHP framework.',
            'status' => 'draft',
            'version_number' => 3,
            'is_autosave' => false,
            'user_id' => $user->id
        ]);
        
        $version4_new_set = \App\Models\ContentVersion::create($pdo, [
            'content_id' => $content2->id,
            'content' => 'Laravel is a modern PHP framework.',
            'status' => 'published',
            'version_number' => 4,
            'is_autosave' => false,
            'user_id' => $user->id
        ]);

        // Now create the diffs for the new set of versions
        \App\Models\ContentVersionDiff::create($pdo, [
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

        // This diff uses $version3_new_set and $version4_new_set
        \App\Models\ContentVersionDiff::create($pdo, [
            'from_version_id' => $version3_new_set->id,
            'to_version_id' => $version4_new_set->id,
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
