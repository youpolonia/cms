<?php
/**
 * User Notification Preferences Form
 * Allows users to configure their notification settings
 */

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/db.php';

// Get current user's preferences
$userId = $_SESSION['user_id'];
$preferences = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Error fetching preferences: " . $e->getMessage());
}

// Set defaults if no preferences exist
$defaults = [
    'email_notifications' => true,
    'push_notifications' => true,
    'sms_notifications' => false,
    'digest_frequency' => 'daily'
];

$preferences = array_merge($defaults, $preferences);

?><div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Notification Preferences</h6>
                </div>
                <div class="card-body">
                    <form id="notificationPreferencesForm">
                        <div id="formMessages" class="alert" style="display: none;"></div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="emailNotifications" 
                                    name="email_notifications" <?= $preferences['email_notifications'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="emailNotifications">Email Notifications</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="pushNotifications" 
                                    name="push_notifications" <?= $preferences['push_notifications'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="pushNotifications">Push Notifications</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="smsNotifications" 
                                    name="sms_notifications" <?= $preferences['sms_notifications'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="smsNotifications">SMS Notifications</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="digestFrequency">Digest Frequency</label>
                            <select class="form-control" id="digestFrequency" name="digest_frequency">
                                <option value="immediate" <?= $preferences['digest_frequency'] === 'immediate' ? 'selected' : '' ?>>Immediate</option>
                                <option value="daily" <?= $preferences['digest_frequency'] === 'daily' ? 'selected' : '' ?>>Daily</option>
                                <option value="weekly" <?= $preferences['digest_frequency'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                    </form>

                    <script>
                    document.getElementById('notificationPreferencesForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const form = e.target;
                        const formData = new FormData(form);
                        const messages = document.getElementById('formMessages');
                        
                        fetch('/admin/notifications/save_preferences.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            messages.style.display = 'block';
                            if (data.success) {
                                messages.className = 'alert alert-success';
                                messages.textContent = data.message;
                            } else {
                                messages.className = 'alert alert-danger';
                                messages.textContent = data.message;
                            }
                            
                            // Scroll to show message
                            messages.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        })
                        .catch(error => {
                            messages.style.display = 'block';
                            messages.className = 'alert alert-danger';
                            messages.textContent = 'An error occurred while saving preferences';
                            console.error('Error:', error);
                        });
                    });
?>                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
