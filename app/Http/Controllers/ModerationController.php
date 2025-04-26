<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModerationRequest;
use App\Services\ModerationAnalyticsService;
use App\Http\Resources\ModerationResource;
use App\Models\Content;
use App\Models\ModerationQueue;
use App\Notifications\ContentModerated;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ModerationController extends Controller
{
    public function __construct(
        protected ModerationAnalyticsService $analyticsService
    ) {}

    public function index()
    {
        $moderations = ModerationQueue::query()
            ->with(['content', 'moderator', 'currentVersion'])
            ->latest()
            ->paginate();

        return ModerationResource::collection($moderations);
    }

    public function store(ModerationRequest $request)
    {
        $moderation = DB::transaction(function () use ($request) {
            $moderation = ModerationQueue::create([
                'content_id' => $request->content_id,
                'moderator_id' => $request->user()->id,
                'status' => 'pending',
                'action' => $request->action,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'priority' => $request->priority,
            ]);

            if ($request->notify_author) {
                $moderation->content->author->notify(
                    new ContentModerated($moderation)
                );
            }

            return $moderation;
        });

        return (new ModerationResource($moderation))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ModerationQueue $moderation)
    {
        $moderation->load(['content', 'moderator', 'currentVersion']);
        return new ModerationResource($moderation);
    }

    public function update(ModerationRequest $request, ModerationQueue $moderation)
    {
        $moderation = DB::transaction(function () use ($request, $moderation) {
            $moderation->update([
                'action' => $request->action,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'priority' => $request->priority,
            ]);

            if ($request->notify_author) {
                $moderation->content->author->notify(
                    new ContentModerated($moderation)
                );
            }

            return $moderation;
        });

        return new ModerationResource($moderation);
    }

    public function approve(ModerationQueue $moderation)
    {
        $moderation = DB::transaction(function () use ($moderation) {
            $moderation->update([
                'status' => 'approved',
                'resolved_at' => now(),
            ]);

            $moderation->content->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            $this->analyticsService->trackModerationAction($moderation);
            $moderation->content->author->notify(new ContentModerated($moderation));

            return $moderation;
        });

        return new ModerationResource($moderation);
    }

    public function reject(ModerationQueue $moderation)
    {
        $moderation = DB::transaction(function () use ($moderation) {
            $moderation->update([
                'status' => 'rejected',
                'resolved_at' => now(),
            ]);

            $moderation->content->update([
                'status' => 'draft',
            ]);

            $this->analyticsService->trackModerationAction($moderation);
            $moderation->content->author->notify(new ContentModerated($moderation));

            return $moderation;
        });

        return new ModerationResource($moderation);
    }

    public function requestChanges(ModerationQueue $moderation)
    {
        $moderation = DB::transaction(function () use ($moderation) {
            $moderation->update([
                'status' => 'changes_requested',
                'resolved_at' => now(),
            ]);

            $moderation->content->update([
                'status' => 'needs_revision',
            ]);

            $this->analyticsService->trackModerationAction($moderation);
            $moderation->content->author->notify(new ContentModerated($moderation));

            return $moderation;
        });

        return new ModerationResource($moderation);
    }
}
