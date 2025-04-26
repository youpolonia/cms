<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class QuickActionsWidget extends Component
{
    public $actions;

    public function __construct()
    {
        $this->actions = [
            [
                'title' => 'Create Content',
                'icon' => 'document-add',
                'url' => route('content.create'),
                'permission' => 'create content'
            ],
            [
                'title' => 'Manage Categories',
                'icon' => 'folder',
                'url' => route('categories.manage'),
                'permission' => 'manage categories'
            ],
            [
                'title' => 'View Analytics',
                'icon' => 'chart-bar',
                'url' => route('content.analytics'),
                'permission' => 'view analytics'
            ],
            [
                'title' => 'Generate Content',
                'icon' => 'sparkles',
                'url' => route('content.generator'),
                'permission' => 'generate content'
            ],
            [
                'title' => 'Moderation Queue',
                'icon' => 'shield-check',
                'url' => route('moderation.index'),
                'permission' => 'moderate content'
            ]
        ];

        // Filter actions based on user permissions
        $this->actions = array_filter($this->actions, function($action) {
            return Auth::user()->can($action['permission']);
        });
    }

    public function render()
    {
        return view('components.quick-actions-widget');
    }
}
