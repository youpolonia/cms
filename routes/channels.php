<?php

use App\Models\Content;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('collaboration.{contentId}', function ($user, $contentId) {
    $content = Content::findOrFail($contentId);
    
    if (!Gate::allows('edit-content', $content)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->avatar_url
    ];
});

Broadcast::channel('collaboration-session.{sessionId}', function ($user, $sessionId) {
    // Verify user has access to the collaboration session
    $session = \App\Models\CollaborationSession::findOrFail($sessionId);
    
    if (!Gate::allows('participate-in-session', $session)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->getRoleInSession($sessionId)
    ];
});

Broadcast::channel('collaboration-comments.{contentId}', function ($user, $contentId) {
    $content = Content::findOrFail($contentId);
    
    if (!Gate::allows('view-content', $content)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'can_comment' => Gate::allows('comment-on-content', $content)
    ];
});

Broadcast::channel('analytics-updates.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});