<?php
class ApprovalWorkflowController {
    public static function getStats() {
        // Verify admin permissions
        if (!Auth::hasPermission('content_approval')) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'pending' => Content::countPendingApprovals(),
            'approved' => Content::countApproved(),
            'rejected' => Content::countRejected()
        ];

        return Response::json($stats);
    }

    public static function processApproval($id, $action) {
        if (!Auth::hasPermission('content_approval')) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $content = Content::find($id);
        if (!$content) {
            return Response::json(['error' => 'Content not found'], 404);
        }

        try {
            if ($action === 'approve') {
                $content->approve();
                AuditLog::log(Auth::user()->id, 'content_approved', $id);
            } else {
                $content->reject();
                AuditLog::log(Auth::user()->id, 'content_rejected', $id);
            }

            return Response::json(['success' => true]);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public static function getHistory() {
        if (!Auth::hasPermission('content_approval_view')) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $status = Request::get('status', 'all');
        $date = Request::get('date', null);

        $query = Content::getApprovalHistory();
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('updated_at', $date);
        }

        return Response::json($query->paginate(20));
    }
}
