<?php /** @var array $workers */ 
?><div class="container">
    <h1>New Message</h1>
    <a href="/admin/communications" class="btn btn-secondary mb-3">Back to Messages</a>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="/admin/communications/create">
                <div class="mb-3">
                    <label for="recipient" class="form-label">Recipient</label>
                    <select class="form-select" id="recipient" name="recipient_id"
 required>
                        <option value="">Select a recipient</option>
                        <?php foreach ($workers as $worker): ?>                            <option value="<?= htmlspecialchars($worker['id']) ?>">
                                <?= htmlspecialchars($worker['name'])  ?>
                            </option>
                        <?php endforeach;  ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject"
 required>
?>                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5"
 required></textarea>
?>                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>
