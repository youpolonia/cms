<?php
/**
 * Article Categories Management
 */

declare(strict_types=1);

define('CMS_ROOT', realpath(__DIR__ . '/..'));

require_once __DIR__ . '/../includes/init.php'; // Session init
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

require_once CMS_ROOT . '/core/database.php';

$db = \core\Database::connection();

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name)) {
                $message = 'Category name is required';
                $messageType = 'error';
            } else {
                if (empty($slug)) {
                    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
                }
                
                $stmt = $db->prepare("INSERT INTO article_categories (name, slug, description) VALUES (?, ?, ?)");
                $stmt->execute([$name, $slug, $description]);
                $message = 'Category created successfully!';
                $messageType = 'success';
            }
            break;
            
        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if ($id > 0 && !empty($name)) {
                $stmt = $db->prepare("UPDATE article_categories SET name = ?, slug = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $id]);
                $message = 'Category updated successfully!';
                $messageType = 'success';
            }
            break;
            
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                // Set articles to no category
                $stmt = $db->prepare("UPDATE articles SET category_id = NULL WHERE category_id = ?");
                $stmt->execute([$id]);
                
                // Delete category
                $stmt = $db->prepare("DELETE FROM article_categories WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Category deleted successfully!';
                $messageType = 'success';
            }
            break;
    }
}

// Get categories with article counts
$categories = $db->query("
    SELECT c.*, COUNT(a.id) as article_count 
    FROM article_categories c 
    LEFT JOIN articles a ON a.category_id = c.id AND a.status != 'trash'
    GROUP BY c.id 
    ORDER BY c.name
")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Article Categories';
require_once CMS_ROOT . '/admin/includes/header.php';
?>

<style>
.categories-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.categories-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 1.5rem;
}

@media (max-width: 900px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
}

.category-form {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.5rem;
    height: fit-content;
}

.category-form h3 {
    margin-bottom: 1rem;
    font-size: 1rem;
}

.categories-list {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}

.category-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
}

.category-item:last-child {
    border-bottom: none;
}

.category-item:hover {
    background: #f8fafc;
}

.category-info h4 {
    margin: 0 0 0.25rem 0;
    font-size: 0.9375rem;
}

.category-info .slug {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-family: monospace;
}

.category-info .count {
    font-size: 0.75rem;
    color: var(--primary);
    margin-top: 0.25rem;
}

.category-actions {
    display: flex;
    gap: 0.5rem;
}

.category-actions button {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
}

.category-actions .edit {
    background: var(--primary);
    color: white;
}

.category-actions .delete {
    background: #fee2e2;
    color: #dc2626;
}

.empty-state {
    padding: 2rem;
    text-align: center;
    color: var(--text-muted);
}
</style>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="categories-header">
    <h1>📁 Article Categories</h1>
    <a href="/admin/articles.php" class="btn btn-secondary">← Back to Articles</a>
</div>

<div class="categories-grid">
    <div class="category-form">
        <h3 id="form-title">➕ Add New Category</h3>
        <form method="POST" id="category-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" id="form-action" value="create">
            <input type="hidden" name="id" id="form-id" value="">
            
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" id="form-name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" id="form-slug" class="form-control" placeholder="auto-generated">
                <div class="form-hint">Leave empty to auto-generate from name</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="form-description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn primary" id="form-submit">Create Category</button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()" style="display: none;" id="form-cancel">Cancel</button>
            </div>
        </form>
    </div>
    
    <div class="categories-list">
        <?php if (empty($categories)): ?>
        <div class="empty-state">
            <p>No categories yet. Create your first category.</p>
        </div>
        <?php else: ?>
        <?php foreach ($categories as $cat): ?>
        <div class="category-item">
            <div class="category-info">
                <h4><?= htmlspecialchars($cat['name']) ?></h4>
                <div class="slug">/category/<?= htmlspecialchars($cat['slug']) ?></div>
                <div class="count"><?= $cat['article_count'] ?> article<?= $cat['article_count'] != 1 ? 's' : '' ?></div>
            </div>
            <div class="category-actions">
                <button class="edit" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)">Edit</button>
                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this category? Articles will be moved to uncategorized.')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <button type="submit" class="delete">Delete</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function editCategory(cat) {
    document.getElementById('form-title').textContent = '✏️ Edit Category';
    document.getElementById('form-action').value = 'update';
    document.getElementById('form-id').value = cat.id;
    document.getElementById('form-name').value = cat.name;
    document.getElementById('form-slug').value = cat.slug;
    document.getElementById('form-description').value = cat.description || '';
    document.getElementById('form-submit').textContent = 'Update Category';
    document.getElementById('form-cancel').style.display = 'inline-block';
    
    document.querySelector('.category-form').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').textContent = '➕ Add New Category';
    document.getElementById('form-action').value = 'create';
    document.getElementById('form-id').value = '';
    document.getElementById('form-name').value = '';
    document.getElementById('form-slug').value = '';
    document.getElementById('form-description').value = '';
    document.getElementById('form-submit').textContent = 'Create Category';
    document.getElementById('form-cancel').style.display = 'none';
}
</script>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
