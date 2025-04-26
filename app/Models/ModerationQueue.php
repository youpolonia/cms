<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'user_id',
        'status',
        'rejection_reason',
        'moderation_result',
        'moderator_id',
        'moderated_at',
        'is_ai_generated',
        'ai_generation_metadata',
        'openai_moderation_results',
        'moderation_policy',
        'requires_human_review'
    ];

    protected $casts = [
        'moderation_result' => 'array',
        'moderated_at' => 'datetime',
        'ai_generation_metadata' => 'array',
        'openai_moderation_results' => 'array',
        'is_ai_generated' => 'boolean',
        'requires_human_review' => 'boolean'
    ];

    public function runAutomatedModeration()
    {
        if ($this->is_ai_generated) {
            $this->openai_moderation_results = $this->getOpenAIModerationResults();
            $this->evaluateModerationResults();
        }
        return $this;
    }

    protected function getOpenAIModerationResults()
    {
        try {
            $service = app(OpenAIModerationService::class);
            $content = $this->content->body;
            return $service->moderateContent($content);
        } catch (\Exception $e) {
            \Log::error('OpenAI Moderation Failed: ' . $e->getMessage());
            return [
                'error' => $e->getMessage(),
                'requires_human_review' => true
            ];
        }
    }

    protected function evaluateModerationResults()
    {
        $results = $this->openai_moderation_results;
        
        if (isset($results['error'])) {
            $this->requires_human_review = true;
            return;
        }

        $service = app(OpenAIModerationService::class);
        $hasViolations = $service->checkForPolicyViolations(
            $results,
            $this->moderation_policy
        );

        $this->requires_human_review = $hasViolations ||
                                     ($this->is_ai_generated && $this->moderation_policy === 'strict');
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
