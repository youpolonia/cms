<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModerationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content_id' => $this->content_id,
            'status' => $this->status,
            'action' => $this->action,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'priority' => $this->priority,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'moderator' => $this->whenLoaded('moderator', fn() => [
                'id' => $this->moderator->id,
                'name' => $this->moderator->name
            ]),
            'content' => $this->whenLoaded('content', fn() => [
                'id' => $this->content->id,
                'title' => $this->content->title,
                'type' => $this->content->type
            ]),
            'current_version' => $this->whenLoaded('currentVersion', fn() => [
                'id' => $this->currentVersion->id,
                'version_number' => $this->currentVersion->version_number
            ]),
            'links' => [
                'self' => route('moderation.show', $this->id),
                'content' => route('content.show', $this->content_id),
                'approve' => route('moderation.approve', $this->id),
                'reject' => route('moderation.reject', $this->id),
                'request_changes' => route('moderation.request-changes', $this->id),
            ]
        ];
    }
}