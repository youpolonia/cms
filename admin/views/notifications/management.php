<?php
require_once __DIR__.'/../layout.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?><div class="container">
    <h1>Notification Management</h1>
    
    <ul class="nav nav-tabs mb-4" id="notificationTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="config-tab" data-bs-toggle="tab" 
                    data-bs-target="#config" type="button" role="tab">
                Channel Configuration
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="templates-tab" data-bs-toggle="tab" 
                    data-bs-target="#templates" type="button" role="tab">
                Templates
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ai-tab" data-bs-toggle="tab" 
                    data-bs-target="#ai" type="button" role="tab">
                AI Settings
            </button>
        </li>
    </ul>

    <div class="tab-content" id="notificationTabContent">
        <div class="tab-pane fade show active" id="config" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <form id="channel-config-form">
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="email-channel" checked>
                            <label class="form-check-label" for="email-channel">Email Notifications</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="sms-channel">
                            <label class="form-check-label" for="sms-channel">SMS Notifications</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="webhook-channel">
                            <label class="form-check-label" for="webhook-channel">Webhook Notifications</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="in-app-channel" checked>
                            <label class="form-check-label" for="in-app-channel">In-App Notifications</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Configuration</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="templates" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div id="template-editor-container">
                        <!-- Template editor will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="ai" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <form id="ai-settings-form">
                        <div class="mb-3">
                            <label for="ai-enable" class="form-label">AI Processing</label>
                            <select class="form-select" id="ai-enable">
                                <option value="0">Disabled</option>
                                <option value="1">Enabled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="ai-model" class="form-label">AI Model</label>
                            <input type="text" class="form-control" id="ai-model" 
                                   placeholder="Enter model identifier">
                        </div>
                        <button type="submit" class="btn btn-primary">Save AI Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
<script src="/admin/views/notifications/notification_management.js"></script>
