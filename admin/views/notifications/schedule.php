<?php require_once __DIR__.'/../layout.php'; 
?><div class="container">
    <h1>Schedule Notification</h1>
    
    <form method="post" action="/admin/notifications/schedule">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>

                <div class="form-group">
                    <label for="user_id">Recipient</label>
                    <select class="form-control" id="user_id" name="user_id" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                            </option>
                        <?php endforeach;  ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="template_id">Template (Optional)</label>
                    <select class="form-control" id="template_id" name="template_id">
                        <option value="">No Template</option>
                        <?php foreach ($templates as $template): ?>
                            <option value="<?php echo $template['id']; ?>">
                                <?php echo htmlspecialchars($template['name']);  ?>
                            </option>
                        <?php endforeach;  ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="schedule_time">Schedule Time</label>
                    <input type="datetime-local" class="form-control" id="schedule_time" name="schedule_time" required>
                </div>
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Schedule Notification</button>
                <a href="/admin/notifications" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('#schedule_time', {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today",
        time_24hr: true
    });
});
</script>