<?php
/**
 * SEO Metadata Edit View
 * Form for editing page/article SEO settings
 */

// $data contains: entity_type, entity_id, entity_title, metadata, defaults
$entityType = $data['entity_type'] ?? 'page';
$entityId = $data['entity_id'] ?? 0;
$entityTitle = $data['entity_title'] ?? 'Unknown';
$metadata = $data['metadata'] ?? [];
$defaults = $data['defaults'] ?? [];
$errors = $data['errors'] ?? [];
$success = $data['success'] ?? '';

function esc($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

function val($arr, $key, $default = '') {
    return isset($arr[$key]) && $arr[$key] !== null ? $arr[$key] : $default;
}
?>
<div class="seo-edit">
    <div class="page-header">
        <h1>Edit SEO Settings</h1>
        <p class="muted">
            Editing SEO for: <strong><?php echo esc($entityTitle); ?></strong>
            <span class="badge"><?php echo esc(ucfirst($entityType)); ?></span>
        </p>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert success"><?php echo esc($success); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="alert error">
        <ul>
            <?php foreach ($errors as $error): ?>
            <li><?php echo esc($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- SEO Score Preview -->
    <?php if (!empty($metadata['seo_score'])): ?>
    <div class="score-preview">
        <div class="score-circle <?php echo $metadata['seo_score'] >= 70 ? 'good' : ($metadata['seo_score'] >= 40 ? 'fair' : 'poor'); ?>">
            <span class="score-value"><?php echo (int) $metadata['seo_score']; ?></span>
            <span class="score-label">SEO</span>
        </div>
        <?php if (!empty($metadata['readability_score'])): ?>
        <div class="score-circle <?php echo $metadata['readability_score'] >= 60 ? 'good' : ($metadata['readability_score'] >= 40 ? 'fair' : 'poor'); ?>">
            <span class="score-value"><?php echo (int) $metadata['readability_score']; ?></span>
            <span class="score-label">Readability</span>
        </div>
        <?php endif; ?>
        <?php if (!empty($metadata['last_analyzed_at'])): ?>
        <span class="last-analyzed">Last analyzed: <?php echo esc($metadata['last_analyzed_at']); ?></span>
        <?php endif; ?>
        <button type="button" class="btn small" onclick="analyzeNow()">Re-analyze</button>
    </div>
    <?php endif; ?>

    <form method="post" action="" id="seo-form">
        <?php csrf_field(); ?>
        <input type="hidden" name="entity_type" value="<?php echo esc($entityType); ?>">
        <input type="hidden" name="entity_id" value="<?php echo (int) $entityId; ?>">

        <!-- Basic SEO -->
        <div class="card">
            <h2>Basic SEO</h2>

            <div class="form-row">
                <label for="focus_keyword">Focus Keyword</label>
                <input type="text" id="focus_keyword" name="focus_keyword" class="form-control"
                       value="<?php echo esc(val($metadata, 'focus_keyword')); ?>"
                       placeholder="e.g., best coffee maker">
                <small class="muted">The main keyword you want this page to rank for.</small>
            </div>

            <div class="form-row">
                <label for="meta_title">Meta Title</label>
                <input type="text" id="meta_title" name="meta_title" class="form-control"
                       value="<?php echo esc(val($metadata, 'meta_title')); ?>"
                       placeholder="Page title for search engines"
                       maxlength="255">
                <div class="char-counter"><span id="title-count">0</span>/60 characters (recommended)</div>
                <small class="muted">Leave empty to use the page title.</small>
            </div>

            <div class="form-row">
                <label for="meta_description">Meta Description</label>
                <textarea id="meta_description" name="meta_description" class="form-control" rows="3"
                          placeholder="Brief description for search results"
                          maxlength="500"><?php echo esc(val($metadata, 'meta_description')); ?></textarea>
                <div class="char-counter"><span id="desc-count">0</span>/160 characters (recommended)</div>
            </div>

            <div class="form-row">
                <label for="meta_keywords">Meta Keywords</label>
                <input type="text" id="meta_keywords" name="meta_keywords" class="form-control"
                       value="<?php echo esc(val($metadata, 'meta_keywords')); ?>"
                       placeholder="keyword1, keyword2, keyword3">
                <small class="muted">Comma-separated keywords (less important for modern SEO).</small>
            </div>

            <div class="form-row">
                <label for="canonical_url">Canonical URL</label>
                <input type="url" id="canonical_url" name="canonical_url" class="form-control"
                       value="<?php echo esc(val($metadata, 'canonical_url')); ?>"
                       placeholder="https://example.com/page">
                <small class="muted">Leave empty for auto-generated canonical URL.</small>
            </div>
        </div>

        <!-- Robots Settings -->
        <div class="card">
            <h2>Search Engine Robots</h2>

            <div class="form-row-inline">
                <div class="form-group">
                    <label for="robots_index">Index Setting</label>
                    <select id="robots_index" name="robots_index" class="form-control">
                        <option value="index" <?php echo val($metadata, 'robots_index', $defaults['robots_index']) === 'index' ? 'selected' : ''; ?>>Index (allow in search)</option>
                        <option value="noindex" <?php echo val($metadata, 'robots_index', $defaults['robots_index']) === 'noindex' ? 'selected' : ''; ?>>NoIndex (hide from search)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="robots_follow">Follow Setting</label>
                    <select id="robots_follow" name="robots_follow" class="form-control">
                        <option value="follow" <?php echo val($metadata, 'robots_follow', $defaults['robots_follow']) === 'follow' ? 'selected' : ''; ?>>Follow (crawl links)</option>
                        <option value="nofollow" <?php echo val($metadata, 'robots_follow', $defaults['robots_follow']) === 'nofollow' ? 'selected' : ''; ?>>NoFollow (don't crawl links)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Open Graph -->
        <div class="card">
            <h2>Social Sharing (Open Graph)</h2>
            <p class="muted">Controls how this page appears when shared on Facebook, LinkedIn, etc.</p>

            <div class="form-row">
                <label for="og_title">OG Title</label>
                <input type="text" id="og_title" name="og_title" class="form-control"
                       value="<?php echo esc(val($metadata, 'og_title')); ?>"
                       placeholder="Leave empty to use meta title">
            </div>

            <div class="form-row">
                <label for="og_description">OG Description</label>
                <textarea id="og_description" name="og_description" class="form-control" rows="2"
                          placeholder="Leave empty to use meta description"><?php echo esc(val($metadata, 'og_description')); ?></textarea>
            </div>

            <div class="form-row">
                <label for="og_image">OG Image URL</label>
                <input type="url" id="og_image" name="og_image" class="form-control"
                       value="<?php echo esc(val($metadata, 'og_image')); ?>"
                       placeholder="https://example.com/image.jpg">
                <small class="muted">Recommended size: 1200x630 pixels.</small>
            </div>

            <div class="form-row">
                <label for="og_type">OG Type</label>
                <select id="og_type" name="og_type" class="form-control">
                    <option value="website" <?php echo val($metadata, 'og_type', 'website') === 'website' ? 'selected' : ''; ?>>Website</option>
                    <option value="article" <?php echo val($metadata, 'og_type') === 'article' ? 'selected' : ''; ?>>Article</option>
                    <option value="product" <?php echo val($metadata, 'og_type') === 'product' ? 'selected' : ''; ?>>Product</option>
                    <option value="video" <?php echo val($metadata, 'og_type') === 'video' ? 'selected' : ''; ?>>Video</option>
                </select>
            </div>
        </div>

        <!-- Twitter Card -->
        <div class="card">
            <h2>Twitter Card</h2>
            <p class="muted">Controls how this page appears when shared on Twitter/X.</p>

            <div class="form-row">
                <label for="twitter_card">Card Type</label>
                <select id="twitter_card" name="twitter_card" class="form-control">
                    <option value="summary" <?php echo val($metadata, 'twitter_card') === 'summary' ? 'selected' : ''; ?>>Summary</option>
                    <option value="summary_large_image" <?php echo val($metadata, 'twitter_card', 'summary_large_image') === 'summary_large_image' ? 'selected' : ''; ?>>Summary with Large Image</option>
                    <option value="player" <?php echo val($metadata, 'twitter_card') === 'player' ? 'selected' : ''; ?>>Player (video)</option>
                </select>
            </div>

            <div class="form-row">
                <label for="twitter_title">Twitter Title</label>
                <input type="text" id="twitter_title" name="twitter_title" class="form-control"
                       value="<?php echo esc(val($metadata, 'twitter_title')); ?>"
                       placeholder="Leave empty to use OG/meta title">
            </div>

            <div class="form-row">
                <label for="twitter_description">Twitter Description</label>
                <textarea id="twitter_description" name="twitter_description" class="form-control" rows="2"
                          placeholder="Leave empty to use OG/meta description"><?php echo esc(val($metadata, 'twitter_description')); ?></textarea>
            </div>

            <div class="form-row">
                <label for="twitter_image">Twitter Image URL</label>
                <input type="url" id="twitter_image" name="twitter_image" class="form-control"
                       value="<?php echo esc(val($metadata, 'twitter_image')); ?>"
                       placeholder="Leave empty to use OG image">
            </div>
        </div>

        <!-- Schema.org -->
        <div class="card">
            <h2>Structured Data (Schema.org)</h2>
            <p class="muted">Rich snippets and structured data for search engines.</p>

            <div class="form-row">
                <label for="schema_type">Schema Type</label>
                <select id="schema_type" name="schema_type" class="form-control">
                    <option value="" <?php echo empty($metadata['schema_type']) ? 'selected' : ''; ?>>Auto-detect</option>
                    <option value="Article" <?php echo val($metadata, 'schema_type') === 'Article' ? 'selected' : ''; ?>>Article</option>
                    <option value="Product" <?php echo val($metadata, 'schema_type') === 'Product' ? 'selected' : ''; ?>>Product</option>
                    <option value="LocalBusiness" <?php echo val($metadata, 'schema_type') === 'LocalBusiness' ? 'selected' : ''; ?>>Local Business</option>
                    <option value="FAQPage" <?php echo val($metadata, 'schema_type') === 'FAQPage' ? 'selected' : ''; ?>>FAQ Page</option>
                    <option value="HowTo" <?php echo val($metadata, 'schema_type') === 'HowTo' ? 'selected' : ''; ?>>How-To</option>
                    <option value="Custom" <?php echo val($metadata, 'schema_type') === 'Custom' ? 'selected' : ''; ?>>Custom JSON-LD</option>
                </select>
            </div>

            <div class="form-row" id="schema-data-row" style="display: none;">
                <label for="schema_data">Custom Schema JSON-LD</label>
                <textarea id="schema_data" name="schema_data" class="form-control code" rows="8"
                          placeholder='{"@type": "Product", "name": "..."}'><?php
                    $schemaData = val($metadata, 'schema_data');
                    if (is_string($schemaData)) {
                        echo esc($schemaData);
                    } elseif (is_array($schemaData)) {
                        echo esc(json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    }
                ?></textarea>
                <small class="muted">Enter valid JSON-LD (without @context, it will be added automatically).</small>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn primary">Save SEO Settings</button>
            <a href="seo-dashboard.php" class="btn">Cancel</a>
            <?php if (!empty($metadata['id'])): ?>
            <button type="button" class="btn danger" onclick="deleteSeo()">Delete SEO Data</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
// Character counters
document.getElementById('meta_title').addEventListener('input', function() {
    document.getElementById('title-count').textContent = this.value.length;
});
document.getElementById('meta_description').addEventListener('input', function() {
    document.getElementById('desc-count').textContent = this.value.length;
});

// Initial count
document.getElementById('title-count').textContent = document.getElementById('meta_title').value.length;
document.getElementById('desc-count').textContent = document.getElementById('meta_description').value.length;

// Schema type toggle
document.getElementById('schema_type').addEventListener('change', function() {
    document.getElementById('schema-data-row').style.display = this.value === 'Custom' ? 'block' : 'none';
});
// Initial state
if (document.getElementById('schema_type').value === 'Custom') {
    document.getElementById('schema-data-row').style.display = 'block';
}

function analyzeNow() {
    var type = '<?php echo esc($entityType); ?>';
    var id = <?php echo (int) $entityId; ?>;

    fetch('api/seo-actions.php?action=analyze&type=' + type + '&id=' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            var msg = 'Analysis Complete!\n\nSEO Score: ' + data.seo_score + '%\nReadability: ' + data.readability_score + '%\n\n';
            if (data.analysis) {
                if (data.analysis.issues && data.analysis.issues.length) {
                    msg += 'Issues:\n- ' + data.analysis.issues.join('\n- ') + '\n\n';
                }
                if (data.analysis.suggestions && data.analysis.suggestions.length) {
                    msg += 'Suggestions:\n- ' + data.analysis.suggestions.join('\n- ');
                }
            }
            alert(msg);
            location.reload();
        } else {
            alert('Error: ' + (data.errors?.join(', ') || 'Unknown error'));
        }
    })
    .catch(err => alert('Request failed: ' + err.message));
}

function deleteSeo() {
    if (!confirm('Delete all SEO data for this page? This cannot be undone.')) return;

    var form = document.getElementById('seo-form');
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'delete';
    input.value = '1';
    form.appendChild(input);
    form.submit();
}
</script>

<style>
.page-header {
    margin-bottom: 1.5rem;
}
.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: #e9ecef;
    border-radius: 4px;
    font-size: 0.75rem;
    text-transform: uppercase;
}
.score-preview {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}
.score-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 4px solid #ddd;
}
.score-circle.good { border-color: #28a745; background: #d4edda; }
.score-circle.fair { border-color: #ffc107; background: #fff3cd; }
.score-circle.poor { border-color: #dc3545; background: #f8d7da; }
.score-value {
    font-size: 1.5rem;
    font-weight: bold;
}
.score-label {
    font-size: 0.75rem;
    color: #666;
}
.last-analyzed {
    color: #666;
    font-size: 0.875rem;
}
.char-counter {
    font-size: 0.875rem;
    color: #666;
    margin-top: 0.25rem;
}
.form-row-inline {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
textarea.code {
    font-family: monospace;
    font-size: 0.875rem;
}
.btn.danger {
    background: #dc3545;
    color: #fff;
    margin-left: auto;
}
</style>
