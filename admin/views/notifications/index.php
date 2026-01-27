<?php
require_once __DIR__.'/../layout.php';

// Pagination parameters
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$perPage = 20;

// Fetch notifications via API
$notifications = [];
$totalCount = 0;
$apiUrl = "/api/notifications?page=$page&per_page=$perPage";

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?><div class="container">
    <h1>Notifications</h1>
    
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Your Notifications</h5>
                <div class="notification-actions">
                    <button class="btn btn-sm btn-outline-secondary" id="mark-all-read">
                        Mark All as Read
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div id="loading-indicator" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <div id="notifications-container" class="list-group list-group-flush">
                <!-- Notifications will be loaded here via JS -->
            </div>
            
            <div id="pagination-container" class="mt-3">
                <!-- Pagination will be loaded here via JS -->
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="action-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
<script src="/admin/views/notifications/notification_list.js"></script>
