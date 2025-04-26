<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\MediaBranch;
use Illuminate\Console\Command;

class TestMediaBranching extends Command
{
    protected $signature = 'media:test-branching';
    protected $description = 'Test media version branching functionality';

    public function handle()
    {
        // Ensure we have a test user and authenticate them
        $user = \App\Models\User::first();
        if (!$user) {
            $user = \App\Models\User::factory()->create();
        }
        auth()->login($user);

        $media = Media::first();
        if (!$media) {
            $this->info('Creating test media record...');
            // Ensure we have a test user
            $user = \App\Models\User::first();
            if (!$user) {
                $user = \App\Models\User::factory()->create();
            }

            $mediaId = \Illuminate\Support\Str::uuid();
            $media = new Media();
            $media->id = $mediaId;
            $media->filename = 'test.jpg';
            $media->path = 'media/test.jpg';
            $media->metadata = ['size' => 1024, 'mime_type' => 'image/jpeg'];
            $media->user_id = $user->id;
            $media->description = 'Test media for branching';
            $media->current_version = 0;
            $media->version_count = 0;
            $media->save();
        }

        // Create initial version
        $v1 = $media->createVersion(
            ['initial' => true],
            'Initial version'
        );
        $this->info("Created version {$v1->version_number}");

        // Create main branch
        $mainBranch = $media->createBranch('main', 'Main branch', $v1->id, true);
        $this->info("Created branch: {$mainBranch->name}");

        // Create feature branch
        $featureBranch = $media->createBranch('feature', 'Feature branch', $v1->id);
        $this->info("Created branch: {$featureBranch->name}");

        // Add version to feature branch
        $v2 = $media->createVersion(['modified' => true], 'Feature change', 'feature', $v1->id);
        $this->info("Created version {$v2->version_number} on feature branch");

        // Merge feature into main
        $merged = $v2->mergeIntoBranch('main');
        if ($merged) {
            $this->info("Successfully merged feature branch into main");
        } else {
            $this->error("Failed to merge branches");
        }

        return 0;
    }
}
