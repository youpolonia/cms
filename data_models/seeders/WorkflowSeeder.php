<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;

class WorkflowSeeder extends Seeder
{
    public function run()
    {
        Workflow::create([
            'name' => 'Content Publishing',
            'description' => 'Standard workflow for content moderation and publishing',
            'is_active' => true,
            'steps' => json_encode([
                'draft' => [
                    'permissions' => ['author', 'editor'],
                    'actions' => ['submit_for_review']
                ],
                'review' => [
                    'permissions' => ['reviewer'],
                    'actions' => ['approve', 'reject']
                ],
                'approved' => [
                    'permissions' => ['publisher'],
                    'actions' => ['publish', 'request_changes']
                ],
                'published' => [
                    'permissions' => ['admin'],
                    'actions' => ['archive', 'rollback']
                ],
                'rejected' => [
                    'permissions' => ['author'],
                    'actions' => ['resubmit']
                ]
            ]),
            'transitions' => json_encode([
                'to_review' => [
                    'from' => 'draft',
                    'to' => 'review',
                    'permission' => 'submit_content'
                ],
                'approve' => [
                    'from' => 'review',
                    'to' => 'approved',
                    'permission' => 'approve_content'
                ],
                'reject' => [
                    'from' => 'review',
                    'to' => 'rejected',
                    'permission' => 'reject_content',
                    'requires_reason' => true
                ],
                'publish' => [
                    'from' => 'approved',
                    'to' => 'published',
                    'permission' => 'publish_content'
                ],
                'rollback' => [
                    'from' => 'published',
                    'to' => 'draft',
                    'permission' => 'manage_content',
                    'requires_reason' => true
                ]
            ])
        ]);
    }
}
