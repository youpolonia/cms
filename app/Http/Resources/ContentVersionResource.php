<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContentVersionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'version_number' => $this->version_number,
            'content_id' => $this->content_id,
            'content_summary' => str_limit($this->content, 100),
            'change_description' => $this->change_description,
            'status' => $this->status,
            'approval_status' => $this->approval_status,
            'is_autosave' => $this->is_autosave,
            'is_restored' => $this->is_restored,
            'restored_from_version_id' => $this->restored_from_version_id,
            'parent_version_id' => $this->parent_version_id,
            'branch_name' => $this->branch_name,
            'tags' => $this->tags,
            'times_compared' => $this->times_compared,
            'restore_count' => $this->restore_count,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name
            ]),
            'approved_by' => $this->whenLoaded('approvedBy', fn() => [
                'id' => $this->approvedBy->id,
                'name' => $this->approvedBy->name
            ]),
            'restored_by' => $this->whenLoaded('restoredBy', fn() => [
                'id' => $this->restoredBy->id,
                'name' => $this->restoredBy->name
            ]),
            'parent_version' => $this->whenLoaded('parentVersion', fn() => [
                'id' => $this->parentVersion->id,
                'version_number' => $this->parentVersion->version_number
            ]),
            'links' => [
                'self' => route('content.versions.show', [
                    'content' => $this->content_id,
                    'version' => $this->id
                ]),
                'compare' => route('content.versions.compare', [
                    'version1' => $this->id
                ]),
                'restore' => route('content.versions.restore', [
                    'version' => $this->id
                ]),
            ]
        ];
    }
}