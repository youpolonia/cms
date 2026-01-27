<?php
// Security check
if (!defined('CMS_ADMIN')) {
    exit('Direct access not allowed');
}

// Get content data
$content_id = $_GET['id'] ?? 0;
$content = [];
$translations = [];
$default_lang = 'en';

try {
    // Get base content
    $stmt = $db->prepare("SELECT * FROM content_entries WHERE id = ?");
    $stmt->execute([$content_id]);
    $content = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get translations
    $stmt = $db->query("SELECT * FROM languages ORDER BY is_default DESC, name ASC");
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Find default language
    foreach ($languages as $lang) {
        if ($lang['is_default']) {
            $default_lang = $lang['code'];
            break;
        }
    }
    
    // Get existing translations
    $stmt = $db->prepare("SELECT * FROM content_entry_translations WHERE content_id = ?");
    $stmt->execute([$content_id]);
    $translations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Index translations by language code
    $translation_map = [];
    foreach ($translations as $trans) {
        $translation_map[$trans['language_code']] = $trans;
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        try {
            $db->beginTransaction();
            
            // Get current language
            $current_lang = $_POST['language'] ?? $default_lang;
            
            // Prepare base content data
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $content_text = trim($_POST['content'] ?? '');
            $status = $_POST['status'] ?? 'draft'; // retained for UI; not persisted here (transitions handled by ContentPublisher)
            $meta_title = trim($_POST['meta_title'] ?? '');
            $meta_description = trim($_POST['meta_description'] ?? '');
            $canonical_url = trim($_POST['canonical_url'] ?? '');
            
            // Validate base content
            if (empty($title) || empty($slug)) {
                throw new Exception("Title and slug are required");
            }
            
            // Save base content
            if ($content_id) {
                $stmt = $db->prepare("UPDATE content_entries SET title = ?, slug = ?, content = ?, meta_title = ?, meta_description = ?, canonical_url = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$title, $slug, $content_text, $meta_title, $meta_description, $canonical_url, $content_id]);
            } else {
                $stmt = $db->prepare("INSERT INTO content_entries (title, slug, content, meta_title, meta_description, canonical_url, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([$title, $slug, $content_text, $meta_title, $meta_description, $canonical_url]);
                $content_id = $db->lastInsertId();
            }
            
            // Save translations
            foreach ($languages as $lang) {
                $lang_code = $lang['code'];
                $trans_title = trim($_POST['trans_title_'.$lang_code] ?? '');
                $trans_content = trim($_POST['trans_content_'.$lang_code] ?? '');
                
                if (isset($translation_map[$lang_code])) {
                    // Update existing translation
                    $stmt = $db->prepare("UPDATE content_entry_translations SET title = ?, content = ? WHERE content_id = ? AND language_code = ?");
                    $stmt->execute([$trans_title, $trans_content, $content_id, $lang_code]);
                } else {
                    // Insert new translation
                    $stmt = $db->prepare("INSERT INTO content_entry_translations (content_id, language_code, title, content) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$content_id, $lang_code, $trans_title, $trans_content]);
                }
            }
            
            $db->commit();
            $success = "Content saved successfully";
            
            // Refresh data
            $stmt = $db->prepare("SELECT * FROM content_entries WHERE id = ?");
            $stmt->execute([$content_id]);
            $content = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("SELECT * FROM content_entry_translations WHERE content_id = ?");
            $stmt->execute([$content_id]);
            $translations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Rebuild translation map
            $translation_map = [];
            foreach ($translations as $trans) {
                $translation_map[$trans['language_code']] = $trans;
            }
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error saving content: " . $e->getMessage();
        }
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?><div class="admin-container">
    <h2><?= $content_id ? 'Edit Content' : 'Create New Content' ?></h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="mb-3">
            <label class="form-label">Language</label>
            <select name="language" id="language-selector" class="form-select">
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= $lang['code'] ?>" <?= $lang['is_default'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang['name']) ?> (<?= $lang['code'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title"
                   value="<?= htmlspecialchars($content['title'] ?? '') ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control" id="slug" name="slug"
                   value="<?= htmlspecialchars($content['slug'] ?? '') ?>"
                   required>
            <div id="slug-feedback" class="form-text"></div>
            <div class="form-text">
                <small>
                    <a href="#" id="edit-slug">Edit manually</a> |
                    <a href="#" id="reset-slug">Reset to auto-generated</a>
                </small>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" id="content" name="content" rows="10">
<?= htmlspecialchars($content['content'] ?? '') ?>
            </textarea>
        </div>
        
        <!-- Status selection removed: state transitions are handled via dedicated publish/unpublish flows -->

        <!-- SEO Settings Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">SEO Settings</h3>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#seoSettings">
                    Toggle
                </button>
            </div>
            <div class="card-body collapse show" id="seoSettings">
                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" class="form-control" id="meta_title" name="meta_title"
                           maxlength="255" value="<?= htmlspecialchars($content['meta_title'] ?? '') ?>">
                    <div class="form-text">Max 255 characters</div>
                </div>
                
                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?= htmlspecialchars($content['meta_description'] ?? '') ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="canonical_url" class="form-label">Canonical URL</label>
                    <input type="text" class="form-control" id="canonical_url" name="canonical_url"
                           value="<?= htmlspecialchars($content['canonical_url'] ?? '') ?>">
                </div>
                
                <button type="button" class="btn btn-outline-primary" id="generate-seo">
                    <i class="bi bi-magic"></i> Generate with AI
                </button>
            </div>
        </div>
        
        <!-- Translations section -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Translations</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($languages as $lang): ?>                        <?php if ($lang['code'] !== $default_lang): ?>
                            <div class="col-md-6 mb-3 translation-fields" data-lang="<?= $lang['code'] ?>">
                                <h4><?= htmlspecialchars($lang['name']) ?> (<?= $lang['code'] ?>)</h4>
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control"
                                           name="trans_title_<?= $lang['code'] ?>"
                                           value="<?= htmlspecialchars($translation_map[$lang['code']]['title'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Content</label>
                                    <textarea class="form-control"
                                              name="trans_content_<?= $lang['code'] ?>"
                                              rows="5"><?= htmlspecialchars($translation_map[$lang['code']]['content'] ?? '') ?></textarea>
                                </div>
                            </div>
                        <?php endif; ?>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Content</button>
        <a href="?page=content" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    const titleField = document.getElementById('title');
    const slugField = document.getElementById('slug');

    titleField.addEventListener('input', function() {
        if (!slugField.dataset.manualEdit) {
            const slug = titleField.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special chars
                .replace(/[\s_-]+/g, '-') // Replace spaces/underscores with hyphens
                .replace(/^-+|-+$/g, ''); // Trim hyphens from start/end
            slugField.value = slug;
            checkSlugAvailability(slug);
        }
    });

    // Track manual slug edits
    slugField.addEventListener('input', function() {
        if (slugField.value) {
            slugField.dataset.manualEdit = 'true';
            checkSlugAvailability(slugField.value);
        } else {
            delete slugField.dataset.manualEdit;
        }
    });

    // Check slug availability via AJAX
    async function checkSlugAvailability(slug) {
        const contentId = <?= $content_id ?: 'null' ?>;
        const response = await fetch('/admin/api/check-slug.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
            },
            body: JSON.stringify({
                slug: slug,
                content_id: contentId
            })
        });

        const result = await response.json();
        const feedback = document.getElementById('slug-feedback');

        if (result.available) {
            feedback.textContent = '✓ Available';
            feedback.className = 'text-success';
        } else {
            feedback.textContent = '✗ Already in use';
            feedback.className = 'text-danger';
        }
    }

    // Edit/Reset slug handlers
    document.getElementById('edit-slug').addEventListener('click', function(e) {
        e.preventDefault();
        slugField.dataset.manualEdit = 'true';
        slugField.focus();
    });

    document.getElementById('reset-slug').addEventListener('click', function(e) {
        e.preventDefault();
        delete slugField.dataset.manualEdit;
        titleField.dispatchEvent(new Event('input'));
    });

    // Show/hide translations based on selected language
    const languageSelector = document.getElementById('language-selector');
    const translationFields = document.querySelectorAll('.translation-fields');

    function updateTranslationVisibility() {
        const selectedLang = languageSelector.value;
        translationFields.forEach(field => {
            if (field.dataset.lang === selectedLang) {
                field.style.display = 'block';
            } else {
                field.style.display = 'none';
            }
        });
    }

    languageSelector.addEventListener('change', updateTranslationVisibility);
    updateTranslationVisibility(); // Initial call

    // SEO Generation Handler
    document.getElementById('generate-seo').addEventListener('click', async function() {
        const title = document.getElementById('title').value;
        const content = document.getElementById('content').value;

        if (!title && !content) {
            alert('Please enter some content first');
            return;
        }

        try {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Generating...';

            const response = await fetch('/admin/editor-ai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=generate_seo&title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}`
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('meta_title').value = result.meta_title;
                document.getElementById('meta_description').value = result.meta_description;
            } else {
                alert('Error generating SEO: ' + result.error);
            }
        } catch (error) {
            alert('Failed to generate SEO: ' + error.message);
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-magic"></i> Generate with AI';
        }
    });
});
</script>
