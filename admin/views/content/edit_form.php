<?php require_once __DIR__ . '/../../includes/admin_header.php'; ?>
<div class="content-edit-form">
    <h1><?= $id ? 'Edit' : 'Create' ?> Content Item</h1>

    <form method="post" action="/admin/content/save">
        <input type="hidden" name="id" value="<?= $contentItem->id ?? '' ?>">
        <?= csrf_field(); ?>

        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title"
                   value="<?= htmlspecialchars($contentItem->title ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" required><?= htmlspecialchars($contentItem->content ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="access_level">Access Level</label>
            <select id="access_level" name="access_level" required>
                <option value="public" <?= ($contentItem->access_level ?? '') === 'public' ? 'selected' : '' ?>>Public</option>
                <option value="private" <?= ($contentItem->access_level ?? '') === 'private' ? 'selected' : '' ?>>Private</option>
                <option value="admin" <?= ($contentItem->access_level ?? '') === 'admin' ? 'selected' : '' ?>>Admin Only</option>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="draft" <?= ($contentItem->status ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= ($contentItem->status ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="archived" <?= ($contentItem->status ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <div class="form-group">
            <label for="author_id">Author</label>
            <select id="author_id" name="author_id">
                <option value="">-- Select Author --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user->id ?>" <?= ($contentItem->author_id ?? '') == $user->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($id): ?>
        <div class="form-group">
            <label>Created At</label>
            <div class="form-control-static"><?= $contentItem->created_at ?? 'N/A' ?></div>
        </div>
        <div class="form-group">
            <label>Updated At</label>
            <div class="form-control-static"><?= $contentItem->updated_at ?? 'N/A' ?></div>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php';