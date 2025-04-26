<?php

namespace App\Providers;

use App\Models\DiffComment;
use App\Policies\DiffCommentPolicy;

use App\Models\ContentVersion;
use App\Models\ErrorResolutionStep;
use App\Models\ErrorResolutionWorkflow;
use App\Policies\ContentPolicy;
use App\Policies\ContentVersionPolicy;
use App\Policies\StepPolicy;
use App\Policies\WorkflowPolicy;
use App\Models\VersionRestorationLog;
use App\Models\Category;
use App\Policies\RestorationLogPolicy;
use App\Policies\CategoryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        ErrorResolutionWorkflow::class => WorkflowPolicy::class,
        ErrorResolutionStep::class => StepPolicy::class,
        ContentVersion::class => ContentVersionPolicy::class,
        Content::class => ContentPolicy::class,
        VersionRestorationLog::class => RestorationLogPolicy::class,
        Category::class => CategoryPolicy::class,
        DiffComment::class => DiffCommentPolicy::class,
        Block::class => BlockPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('manage-workflows', function ($user) {
            return $user->hasRole('admin') || 
                   $user->hasRole('workflow-manager');
        });

        Gate::define('moderate-content', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-moderator');
        });

        // Recycle bin permissions
        Gate::define('content.delete', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager');
        });

        Gate::define('content.restore', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager');
        });

        Gate::define('content.force-delete', function ($user) {
            return $user->hasRole('admin');
        });

        Gate::define('content.empty-trash', function ($user) {
            return $user->hasRole('admin');
        });

        // Category permissions
        Gate::define('view categories', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager') ||
                   $user->hasRole('editor');
        });

        Gate::define('create categories', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager');
        });

        Gate::define('edit categories', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager');
        });

        Gate::define('delete categories', function ($user) {
            return $user->hasRole('admin');
        });

        // Content Version permissions
        Gate::define('view content versions', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager') ||
                   $user->hasRole('editor');
        });

        Gate::define('create content versions', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager') ||
                   $user->hasRole('editor');
        });

        Gate::define('edit content versions', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager');
        });

        Gate::define('delete content versions', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager');
        });

        Gate::define('restore content versions', function ($user) {
            return $user->hasRole('admin');
        });

        Gate::define('approve content versions', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-moderator');
        });

        Gate::define('compare content versions', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager') ||
                   $user->hasRole('editor');
        });

        // Block editing permissions
        Gate::define('edit-locked-blocks', function ($user) {
            return $user->hasRole('admin') ||
                   $user->hasRole('content-manager') ||
                   $user->hasPermissionTo('edit-locked-content');
        });
    }
}
