<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$isEdit = $product !== null;
$pageTitle = $isEdit ? 'Edit Product' : 'Create Product';
$variants = $variants ?? [];
ob_start();
$v = fn($key, $default = '') => h($isEdit ? ($product[$key] ?? $default) : $default);
?>
<style>
.shop-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.shop-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.shop-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.shop-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.shop-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#6366f1}
.form-group textarea{min-height:100px;resize:vertical;font-family:inherit}
.form-group .hint{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.btn-primary{background:#6366f1;color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-primary:hover{background:#4f46e5}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.toggle-wrap{display:flex;align-items:center;gap:10px}
.toggle-wrap input[type="checkbox"]{width:18px;height:18px;accent-color:#6366f1}
.form-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:24px}
.img-preview{max-width:200px;max-height:150px;border-radius:8px;margin-top:8px;display:none;border:1px solid var(--border,#334155)}

/* AI Panel */
.ai-panel{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%);border:1px solid #4338ca;border-radius:12px;padding:20px 24px;margin-bottom:20px;position:relative;overflow:hidden}
.ai-panel::before{content:'';position:absolute;top:-50%;right:-30%;width:200px;height:200px;background:radial-gradient(circle,rgba(129,140,248,.15) 0%,transparent 70%);pointer-events:none}
.ai-panel-toggle{display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none}
.ai-panel-toggle h3{font-size:.95rem;font-weight:700;color:#c7d2fe;margin:0;display:flex;align-items:center;gap:8px;text-transform:none;letter-spacing:0;border:none;padding:0}
.ai-panel-toggle .chevron{transition:transform .2s;color:#818cf8;font-size:1.1rem}
.ai-panel-toggle .chevron.open{transform:rotate(180deg)}
.ai-panel-body{display:none;margin-top:16px}
.ai-panel-body.open{display:block}
.ai-panel .form-group input,.ai-panel .form-group select,.ai-panel .form-group textarea{background:rgba(15,23,42,.6);border-color:#4338ca}
.ai-panel .form-group input:focus,.ai-panel .form-group select:focus,.ai-panel .form-group textarea:focus{border-color:#818cf8}
.ai-panel .form-group label{color:#c7d2fe}
.ai-panel .hint{color:#818cf8}
.btn-ai{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s}
.btn-ai:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(99,102,241,.4)}
.btn-ai:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
.btn-ai-sm{padding:6px 14px;font-size:.78rem;border-radius:6px}
.ai-spinner{display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:aispin .6s linear infinite}
@keyframes aispin{to{transform:rotate(360deg)}}
.ai-results{margin-top:16px;display:none}
.ai-results.show{display:block}
.ai-result-item{background:rgba(15,23,42,.5);border:1px solid #4338ca;border-radius:8px;padding:12px;margin-bottom:10px;position:relative}
.ai-result-item label{color:#c7d2fe;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;display:block}
.ai-result-item .ai-preview{color:#e2e8f0;font-size:.85rem;max-height:120px;overflow-y:auto;line-height:1.5}
.ai-result-item .ai-preview::-webkit-scrollbar{width:4px}
.ai-result-item .ai-preview::-webkit-scrollbar-thumb{background:#4338ca;border-radius:2px}
.btn-apply{position:absolute;top:10px;right:10px;background:#4338ca;color:#c7d2fe;border:none;padding:4px 12px;border-radius:6px;font-size:.75rem;font-weight:600;cursor:pointer;transition:background .2s}
.btn-apply:hover{background:#6366f1}
.ai-error{background:rgba(239,68,68,.15);border:1px solid #ef4444;color:#fca5a5;padding:10px 14px;border-radius:8px;font-size:.85rem;margin-top:10px;display:none}
.ai-error.show{display:block}
.btn-ai-inline{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:4px 10px;border-radius:6px;font-size:.72rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:4px;margin-left:8px;vertical-align:middle;transition:all .2s}
.btn-ai-inline:hover{box-shadow:0 2px 8px rgba(99,102,241,.3)}
.btn-ai-inline:disabled{opacity:.5;cursor:not-allowed}
.ai-price-popover{display:none;background:var(--bg-card,#1e293b);border:1px solid #4338ca;border-radius:10px;padding:14px;margin-top:8px;font-size:.85rem;color:var(--text,#e2e8f0)}
.ai-price-popover.show{display:block}
.ai-price-popover .price-val{font-size:1.3rem;font-weight:700;color:#a5b4fc}
.ai-price-popover .price-reasoning{color:var(--muted,#94a3b8);margin-top:6px;font-size:.8rem;line-height:1.5}
.ai-price-popover .price-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:10px}
.ai-price-popover .price-stat{text-align:center;padding:6px;background:rgba(99,102,241,.1);border-radius:6px}
.ai-price-popover .price-stat small{display:block;color:#818cf8;font-size:.7rem;margin-bottom:2px}
.ai-price-popover .price-stat span{font-weight:600;font-size:.9rem}
.btn-apply-price{background:#4338ca;color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;margin-top:8px}
.btn-apply-price:hover{background:#6366f1}

/* AI Tabs */
.ai-tab{background:transparent;border:none;color:#818cf8;padding:8px 16px;font-size:.82rem;font-weight:600;cursor:pointer;border-radius:6px 6px 0 0;transition:all .2s}
.ai-tab:hover{background:rgba(99,102,241,.1)}
.ai-tab.active{background:rgba(99,102,241,.15);color:#c7d2fe;border-bottom:2px solid #6366f1;margin-bottom:-10px}
.ai-tab-content{display:none}
.ai-tab-content.active{display:block}
</style>

<div class="shop-wrap">
    <div class="shop-header">
        <h1><?= $isEdit ? '✏️ Edit Product' : '➕ Create Product' ?></h1>
        <a href="/admin/shop/products" class="btn-secondary">← Back to Products</a>
    </div>

    <!-- AI Generate Panel -->
    <div class="ai-panel">
        <div class="ai-panel-toggle" onclick="toggleAiPanel()">
            <h3>✨ AI Product Copywriter <span style="font-size:.75rem;font-weight:400;opacity:.7">— generate descriptions, SEO &amp; more</span></h3>
            <span class="chevron" id="ai-chevron">▼</span>
        </div>
        <div class="ai-panel-body" id="ai-panel-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="ai-name" value="<?= $v('name') ?>" placeholder="Auto-filled from product name">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" id="ai-category" value="<?= $isEdit ? h($product['category_name'] ?? '') : '' ?>" placeholder="e.g. Electronics, Clothing">
                </div>
            </div>
            <div class="form-group">
                <label>Features / Keywords</label>
                <textarea id="ai-features" rows="2" placeholder="e.g. wireless, noise-cancelling, 40h battery, premium materials"></textarea>
                <div class="hint">Comma-separated list of product features, selling points, or keywords</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Tone</label>
                    <select id="ai-tone">
                        <option value="professional">Professional</option>
                        <option value="casual">Casual</option>
                        <option value="luxury">Luxury</option>
                        <option value="playful">Playful</option>
                        <option value="technical">Technical</option>
                        <option value="minimal">Minimal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Language</label>
                    <select id="ai-language">
                        <option value="en">🇬🇧 English</option>
                        <option value="pl">🇵🇱 Polish</option>
                        <option value="de">🇩🇪 German</option>
                        <option value="fr">🇫🇷 French</option>
                        <option value="es">🇪🇸 Spanish</option>
                        <option value="it">🇮🇹 Italian</option>
                        <option value="pt">🇵🇹 Portuguese</option>
                        <option value="nl">🇳🇱 Dutch</option>
                        <option value="sv">🇸🇪 Swedish</option>
                        <option value="da">🇩🇰 Danish</option>
                        <option value="no">🇳🇴 Norwegian</option>
                        <option value="fi">🇫🇮 Finnish</option>
                        <option value="cs">🇨🇿 Czech</option>
                        <option value="ro">🇷🇴 Romanian</option>
                        <option value="hu">🇭🇺 Hungarian</option>
                        <option value="tr">🇹🇷 Turkish</option>
                        <option value="ja">🇯🇵 Japanese</option>
                        <option value="ko">🇰🇷 Korean</option>
                        <option value="zh">🇨🇳 Chinese</option>
                        <option value="ru">🇷🇺 Russian</option>
                        <option value="ar">🇸🇦 Arabic</option>
                    </select>
                </div>
            </div>
            <button type="button" class="btn-ai" id="btn-ai-generate" onclick="aiGenerate()">
                <span class="ai-spinner" id="ai-gen-spinner"></span>
                ✨ Generate with AI
            </button>

            <div class="ai-error" id="ai-gen-error"></div>

            <div class="ai-results" id="ai-results">
                <div class="ai-result-item">
                    <label>Short Description</label>
                    <button type="button" class="btn-apply" onclick="aiApply('short_description')">Apply</button>
                    <div class="ai-preview" id="ai-res-short"></div>
                </div>
                <div class="ai-result-item">
                    <label>Description (HTML)</label>
                    <button type="button" class="btn-apply" onclick="aiApply('description')">Apply</button>
                    <div class="ai-preview" id="ai-res-desc"></div>
                </div>
                <div class="ai-result-item">
                    <label>Meta Title</label>
                    <button type="button" class="btn-apply" onclick="aiApply('meta_title')">Apply</button>
                    <div class="ai-preview" id="ai-res-mt"></div>
                </div>
                <div class="ai-result-item">
                    <label>Meta Description</label>
                    <button type="button" class="btn-apply" onclick="aiApply('meta_description')">Apply</button>
                    <div class="ai-preview" id="ai-res-md"></div>
                </div>
                <div class="ai-result-item">
                    <label>Suggested Tags</label>
                    <div class="ai-preview" id="ai-res-tags"></div>
                </div>
                <div style="margin-top:12px">
                    <button type="button" class="btn-ai btn-ai-sm" onclick="aiApplyAll()">✅ Apply All Fields</button>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="<?= $isEdit ? '/admin/shop/products/' . (int)$product['id'] . '/update' : '/admin/shop/products/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" id="product-id-hidden" value="<?= $isEdit ? (int)$product['id'] : '0' ?>">

        <div class="shop-card">
            <h3>📝 Basic Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" value="<?= $v('name') ?>" required id="product-name">
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" value="<?= $v('slug') ?>" id="product-slug" placeholder="auto-generated from name">
                    <div class="hint">Leave empty to auto-generate</div>
                </div>
            </div>
            <div class="form-group">
                <label>Short Description</label>
                <input type="text" name="short_description" value="<?= $v('short_description') ?>" maxlength="255">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="6"><?= h($isEdit ? ($product['description'] ?? '') : '') ?></textarea>
            </div>
        </div>

        <div class="shop-card">
            <h3>💰 Pricing & Inventory</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Price * <?php if ($isEdit): ?><button type="button" class="btn-ai-inline" id="btn-ai-price" onclick="aiSuggestPrice()"><span class="ai-spinner" id="ai-price-spinner" style="width:12px;height:12px;border-width:1.5px"></span>💡 Suggest</button><?php endif; ?></label>
                    <input type="number" name="price" step="0.01" min="0" value="<?= $v('price', '0.00') ?>" required id="product-price">
                    <div class="ai-price-popover" id="ai-price-popover">
                        <div>Suggested: <span class="price-val" id="ai-price-val"></span> <button type="button" class="btn-apply-price" onclick="aiApplyPrice()">Apply</button></div>
                        <div class="price-reasoning" id="ai-price-reasoning"></div>
                        <div class="price-stats">
                            <div class="price-stat"><small>Avg</small><span id="ai-price-avg"></span></div>
                            <div class="price-stat"><small>Min</small><span id="ai-price-min"></span></div>
                            <div class="price-stat"><small>Max</small><span id="ai-price-max"></span></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Sale Price</label>
                    <input type="number" name="sale_price" step="0.01" min="0" value="<?= $isEdit && $product['sale_price'] !== null ? h((string)$product['sale_price']) : '' ?>" placeholder="Leave empty for no sale">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku" value="<?= $v('sku') ?>">
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" min="-1" value="<?= $v('stock', '-1') ?>">
                    <div class="hint">-1 = unlimited stock (∞)</div>
                </div>
            </div>
        </div>

        <div class="shop-card">
            <h3>🖼️ Media & Category</h3>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="image" value="<?= $v('image') ?>" id="product-image" placeholder="https://example.com/image.jpg">
                <img id="img-preview" class="img-preview" alt="Preview">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">— No Category —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ($isEdit && ($product['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Product Type</label>
                    <select name="type">
                        <?php foreach (['physical' => 'Physical', 'digital' => 'Digital', 'service' => 'Service'] as $tk => $tl): ?>
                            <option value="<?= $tk ?>" <?= ($isEdit && ($product['type'] ?? 'physical') === $tk) ? 'selected' : '' ?>><?= $tl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="shop-card">
            <h3>⚙️ Status & Options</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'] as $sk => $sl): ?>
                            <option value="<?= $sk ?>" <?= ($isEdit && ($product['status'] ?? 'draft') === $sk) ? 'selected' : '' ?>><?= $sl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="toggle-wrap">
                        <input type="checkbox" name="featured" value="1" <?= ($isEdit && !empty($product['featured'])) ? 'checked' : '' ?> id="featured-toggle">
                        <label for="featured-toggle" style="margin:0;font-size:.85rem">⭐ Featured Product</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ DIGITAL PRODUCT ═══ -->
        <div class="shop-card" id="digital-card" style="<?= ($isEdit && ($product['type'] ?? 'physical') === 'digital') ? '' : 'display:none;' ?>">
            <h3>💾 Digital Product Settings</h3>
            <div class="form-group">
                <label>Digital File</label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="text" name="digital_file_path" id="digital-file-path" value="<?= $v('digital_file') ?>" placeholder="uploads/digital/myfile.zip" style="flex:1">
                    <label style="background:#6366f1;color:#fff;padding:8px 16px;border-radius:8px;font-size:.85rem;font-weight:600;cursor:pointer;white-space:nowrap;margin:0">
                        📁 Upload
                        <input type="file" id="digital-file-upload" style="display:none" accept="*/*">
                    </label>
                </div>
                <div class="hint">Upload a file or enter the path manually (relative to CMS root)</div>
                <div id="digital-upload-status" style="display:none;margin-top:6px;font-size:.8rem;color:#10b981"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Max Downloads per Purchase</label>
                    <input type="number" name="digital_max_downloads" min="1" max="100" value="3">
                    <div class="hint">How many times a customer can download after purchase</div>
                </div>
                <div class="form-group">
                    <label>Download Expiry (hours)</label>
                    <input type="number" name="digital_expiry_hours" min="1" max="8760" value="72">
                    <div class="hint">How long the download link stays active</div>
                </div>
            </div>
        </div>

        <!-- ═══ PRODUCT VARIANTS ═══ -->
        <div class="shop-card" id="variants-card">
            <h3 style="display:flex;align-items:center;justify-content:space-between;border-bottom:none;padding-bottom:0;margin-bottom:0">
                <span>📦 Product Variants</span>
                <label style="display:flex;align-items:center;gap:8px;font-size:.8rem;font-weight:500;text-transform:none;letter-spacing:0;color:var(--text,#e2e8f0);cursor:pointer">
                    <input type="checkbox" id="variants-toggle" style="width:18px;height:18px;accent-color:#6366f1" <?= (!empty($variants)) ? 'checked' : '' ?>>
                    Enable Variants
                </label>
            </h3>
            <div id="variants-body" style="<?= empty($variants) ? 'display:none;' : '' ?>margin-top:16px;border-top:1px solid var(--border,#334155);padding-top:16px">
                <div id="variants-list"></div>
                <button type="button" class="btn-secondary" onclick="addVariantRow()" style="margin-top:12px;padding:8px 16px;font-size:.82rem">+ Add Variant</button>
            </div>
        </div>

        <div class="shop-card">
            <h3>🎯 SEO <?php if ($isEdit): ?><button type="button" class="btn-ai-inline" id="btn-ai-seo" onclick="aiGenerateSeo()" style="text-transform:none;letter-spacing:0"><span class="ai-spinner" id="ai-seo-spinner" style="width:12px;height:12px;border-width:1.5px"></span>✨ AI SEO</button><?php endif; ?></h3>
            <div class="form-group">
                <label>Meta Title</label>
                <input type="text" name="meta_title" value="<?= $v('meta_title') ?>" maxlength="70" id="meta-title">
            </div>
            <div class="form-group">
                <label>Meta Description</label>
                <textarea name="meta_description" rows="2" maxlength="160" id="meta-description"><?= h($isEdit ? ($product['meta_description'] ?? '') : '') ?></textarea>
            </div>
        </div>

        <?php if ($isEdit): ?>
        <!-- ═══ AI TOOLS PANEL ═══ -->
        <div class="ai-panel" id="ai-tools-panel">
            <div class="ai-panel-toggle" onclick="toggleAiTools()">
                <h3>🧠 AI SEO & Content Tools <span style="font-size:.75rem;font-weight:400;opacity:.7">— analyze, rewrite, research keywords</span></h3>
                <span class="chevron" id="ai-tools-chevron">▼</span>
            </div>
            <div class="ai-panel-body" id="ai-tools-body">
                <!-- Tabs -->
                <div style="display:flex;gap:4px;margin-bottom:16px;border-bottom:2px solid rgba(99,102,241,.2);padding-bottom:8px;flex-wrap:wrap">
                    <button type="button" class="ai-tab active" data-tab="tab-seo-analyze" onclick="switchAiTab(this)">🔬 SEO Analyze</button>
                    <button type="button" class="ai-tab" data-tab="tab-rewrite" onclick="switchAiTab(this)">📝 Rewrite</button>
                    <button type="button" class="ai-tab" data-tab="tab-keywords" onclick="switchAiTab(this)">🔑 Keywords</button>
                    <button type="button" class="ai-tab" data-tab="tab-images" onclick="switchAiTab(this)">🖼️ Images</button>
                </div>

                <!-- Tab: SEO Analyze -->
                <div class="ai-tab-content active" id="tab-seo-analyze">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Focus Keyword</label>
                            <input type="text" id="seo-focus-keyword" value="<?= $v('name') ?>" placeholder="e.g. wireless headphones">
                            <div class="hint">Leave empty to use product name</div>
                        </div>
                        <div class="form-group">
                            <label>Language</label>
                            <select id="seo-analyze-lang">
                                <option value="en">English</option>
                                <option value="pl">Polish</option>
                                <option value="de">German</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn-ai" onclick="runSeoAnalyze()">
                        <span class="ai-spinner" id="seo-analyze-spinner"></span>
                        🔬 Run SEO Analysis
                    </button>
                    <div class="ai-error" id="seo-analyze-error"></div>
                    <div id="seo-analyze-results" style="display:none;margin-top:16px"></div>
                </div>

                <!-- Tab: Rewrite -->
                <div class="ai-tab-content" id="tab-rewrite">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Rewrite Mode</label>
                            <select id="rewrite-mode">
                                <option value="seo">🔍 SEO Optimize</option>
                                <option value="paraphrase">🔄 Paraphrase</option>
                                <option value="expand">📖 Expand</option>
                                <option value="simplify">🎯 Simplify</option>
                                <option value="formalize">👔 Formalize</option>
                                <option value="casual">💬 Casual</option>
                                <option value="summarize">📝 Summarize</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Target Field</label>
                            <select id="rewrite-field">
                                <option value="description">Description</option>
                                <option value="short_description">Short Description</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn-ai" onclick="runRewrite()">
                        <span class="ai-spinner" id="rewrite-spinner"></span>
                        📝 Rewrite Content
                    </button>
                    <div class="ai-error" id="rewrite-error"></div>
                    <div id="rewrite-results" style="display:none;margin-top:16px">
                        <div class="ai-result-item">
                            <label>Rewritten Content</label>
                            <button type="button" class="btn-apply" onclick="applyRewrite()">Apply</button>
                            <div class="ai-preview" id="rewrite-preview" style="max-height:200px"></div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Images -->
                <div class="ai-tab-content" id="tab-images">
                    <p style="color:#818cf8;font-size:.82rem;margin:0 0 16px">AI-powered image processing using Hugging Face. Requires product image to be set.</p>

                    <!-- Current image preview -->
                    <?php $currentImg = $product['image'] ?? ''; ?>
                    <?php if ($currentImg): ?>
                    <div style="margin-bottom:16px;display:flex;align-items:flex-start;gap:16px">
                        <img src="<?= h($currentImg) ?>" style="max-width:150px;max-height:120px;border-radius:8px;border:1px solid #4338ca" alt="Current">
                        <div style="flex:1">
                            <div style="font-size:.8rem;color:#c7d2fe;margin-bottom:8px"><strong>Current Image</strong></div>

                            <!-- Remove Background -->
                            <div style="margin-bottom:10px">
                                <button type="button" class="btn-ai btn-ai-sm" onclick="aiRemoveBg()" style="margin-right:6px">
                                    <span class="ai-spinner" id="rmbg-spinner"></span>
                                    ✂️ Remove Background
                                </button>
                                <button type="button" class="btn-ai btn-ai-sm" onclick="aiAltText()">
                                    <span class="ai-spinner" id="alt-spinner"></span>
                                    🏷️ Generate ALT Text
                                </button>
                            </div>

                            <!-- Enhance -->
                            <div style="margin-bottom:10px">
                                <button type="button" class="btn-ai btn-ai-sm" onclick="aiEnhanceImage()" style="background:linear-gradient(135deg,#059669 0%,#10b981 100%)">
                                    <span class="ai-spinner" id="enhance-spinner"></span>
                                    ✨ Enhance Image
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="padding:20px;text-align:center;color:#818cf8;font-size:.85rem;background:rgba(99,102,241,.05);border-radius:8px;margin-bottom:16px">
                        ⚠️ Set a product image first (in the Media section above), then save the product to enable AI image tools.
                    </div>
                    <?php endif; ?>

                    <!-- Generate from prompt -->
                    <div style="border-top:1px solid rgba(99,102,241,.2);padding-top:14px;margin-top:4px">
                        <div class="form-group">
                            <label>🎨 Generate Product Image from Text</label>
                            <div style="display:flex;gap:8px">
                                <input type="text" id="img-gen-prompt" placeholder="e.g. premium wireless headphones on white background, studio lighting" style="flex:1">
                                <button type="button" class="btn-ai btn-ai-sm" onclick="aiGenerateImage()" style="white-space:nowrap">
                                    <span class="ai-spinner" id="imggen-spinner"></span>
                                    🎨 Generate
                                </button>
                            </div>
                            <div class="hint">Describe the product image you want AI to create (Stable Diffusion XL)</div>
                        </div>
                    </div>

                    <div class="ai-error" id="images-error"></div>

                    <!-- Results area -->
                    <div id="images-results" style="display:none;margin-top:12px">
                        <div class="ai-result-item" style="text-align:center">
                            <label id="images-result-label">Result</label>
                            <div style="margin:8px 0">
                                <img id="images-result-img" src="" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid #4338ca;display:none" alt="AI Result">
                                <div id="images-result-text" style="display:none;font-size:.85rem;color:#e2e8f0;text-align:left"></div>
                            </div>
                            <div id="images-result-actions" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap"></div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Keywords -->
                <div class="ai-tab-content" id="tab-keywords">
                    <div class="form-group">
                        <label>Language</label>
                        <select id="keywords-lang" style="max-width:200px">
                            <option value="en">English</option>
                            <option value="pl">Polish</option>
                            <option value="de">German</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                        </select>
                    </div>
                    <button type="button" class="btn-ai" onclick="runKeywordResearch()">
                        <span class="ai-spinner" id="keywords-spinner"></span>
                        🔑 Research Keywords
                    </button>
                    <div class="ai-error" id="keywords-error"></div>
                    <div id="keywords-results" style="display:none;margin-top:16px"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-actions">
            <a href="/admin/shop/products" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary"><?= $isEdit ? '💾 Update Product' : '➕ Create Product' ?></button>
        </div>
    </form>
</div>

<script>
(function(){
    // Slug auto-generate
    var nameEl = document.getElementById('product-name');
    var slugEl = document.getElementById('product-slug');
    if (nameEl && slugEl) {
        nameEl.addEventListener('input', function() {
            if (!slugEl.dataset.manual) {
                slugEl.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
            // Sync AI panel name
            var aiName = document.getElementById('ai-name');
            if (aiName && !aiName.dataset.manual) aiName.value = this.value;
        });
        slugEl.addEventListener('input', function() { this.dataset.manual = '1'; });
    }
    // Image preview
    var imgEl = document.getElementById('product-image');
    var prevEl = document.getElementById('img-preview');
    if (imgEl && prevEl) {
        function showPreview() {
            if (imgEl.value) { prevEl.src = imgEl.value; prevEl.style.display = 'block'; }
            else { prevEl.style.display = 'none'; }
        }
        imgEl.addEventListener('input', showPreview);
        showPreview();
    }
    // AI name sync
    var aiNameEl = document.getElementById('ai-name');
    if (aiNameEl) aiNameEl.addEventListener('input', function(){ this.dataset.manual = '1'; });
})();

// ─── AI Panel ───
var _aiData = null;

function getCsrf() {
    var el = document.querySelector('input[name="csrf_token"]');
    return el ? el.value : '';
}

function toggleAiPanel() {
    var body = document.getElementById('ai-panel-body');
    var chev = document.getElementById('ai-chevron');
    var open = body.classList.toggle('open');
    chev.classList.toggle('open', open);
}

function aiCall(url, payload, spinnerId, callback) {
    var spinner = document.getElementById(spinnerId);
    if (spinner) spinner.style.display = 'inline-block';
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-Token': getCsrf()},
        body: JSON.stringify(payload),
        credentials: 'same-origin'
    })
    .then(function(r){ return r.json(); })
    .then(function(data){ callback(null, data); })
    .catch(function(e){ callback(e.message || 'Network error', null); })
    .finally(function(){ if (spinner) spinner.style.display = 'none'; });
}

function aiGenerate() {
    var btn = document.getElementById('btn-ai-generate');
    var errEl = document.getElementById('ai-gen-error');
    var resEl = document.getElementById('ai-results');
    btn.disabled = true;
    errEl.classList.remove('show');
    resEl.classList.remove('show');

    var payload = {
        name: document.getElementById('ai-name').value,
        category: document.getElementById('ai-category').value,
        features: document.getElementById('ai-features').value,
        tone: document.getElementById('ai-tone').value,
        language: document.getElementById('ai-language').value
    };

    aiCall('/api/shop/ai/generate', payload, 'ai-gen-spinner', function(err, data){
        btn.disabled = false;
        if (err || !data || !data.ok) {
            errEl.textContent = (data && data.error) ? data.error : (err || 'AI generation failed');
            errEl.classList.add('show');
            return;
        }
        _aiData = data.data;
        document.getElementById('ai-res-short').textContent = _aiData.short_description || '';
        document.getElementById('ai-res-desc').innerHTML = _aiData.description || '';
        document.getElementById('ai-res-mt').textContent = _aiData.meta_title || '';
        document.getElementById('ai-res-md').textContent = _aiData.meta_description || '';
        document.getElementById('ai-res-tags').textContent = (_aiData.tags || []).join(', ');
        resEl.classList.add('show');
    });
}

function aiApply(field) {
    if (!_aiData) return;
    var map = {
        'short_description': function(){ var el = document.querySelector('input[name="short_description"]'); if(el) el.value = _aiData.short_description || ''; },
        'description': function(){ var el = document.querySelector('textarea[name="description"]'); if(el) el.value = _aiData.description || ''; },
        'meta_title': function(){ var el = document.getElementById('meta-title'); if(el) el.value = _aiData.meta_title || ''; },
        'meta_description': function(){ var el = document.getElementById('meta-description'); if(el) el.value = _aiData.meta_description || ''; }
    };
    if (map[field]) map[field]();
}

function aiApplyAll() {
    aiApply('short_description');
    aiApply('description');
    aiApply('meta_title');
    aiApply('meta_description');
}

function aiGenerateSeo() {
    var pid = document.getElementById('product-id-hidden');
    if (!pid || pid.value === '0') return;
    var btn = document.getElementById('btn-ai-seo');
    btn.disabled = true;

    aiCall('/api/shop/ai/seo', {product_id: parseInt(pid.value)}, 'ai-seo-spinner', function(err, data){
        btn.disabled = false;
        if (err || !data || !data.ok) {
            alert((data && data.error) ? data.error : (err || 'SEO generation failed'));
            return;
        }
        var mt = document.getElementById('meta-title');
        var md = document.getElementById('meta-description');
        if (mt) mt.value = data.meta_title || '';
        if (md) md.value = data.meta_description || '';
    });
}

function aiSuggestPrice() {
    var pid = document.getElementById('product-id-hidden');
    if (!pid || pid.value === '0') return;
    var btn = document.getElementById('btn-ai-price');
    var pop = document.getElementById('ai-price-popover');
    btn.disabled = true;
    pop.classList.remove('show');

    aiCall('/api/shop/ai/price', {product_id: parseInt(pid.value)}, 'ai-price-spinner', function(err, data){
        btn.disabled = false;
        if (err || !data || !data.ok) {
            alert((data && data.error) ? data.error : (err || 'Price suggestion failed'));
            return;
        }
        document.getElementById('ai-price-val').textContent = parseFloat(data.suggested_price).toFixed(2);
        document.getElementById('ai-price-reasoning').textContent = data.reasoning || '';
        document.getElementById('ai-price-avg').textContent = parseFloat(data.category_avg).toFixed(2);
        document.getElementById('ai-price-min').textContent = parseFloat(data.category_min).toFixed(2);
        document.getElementById('ai-price-max').textContent = parseFloat(data.category_max).toFixed(2);
        pop.classList.add('show');
    });
}

function aiApplyPrice() {
    var val = document.getElementById('ai-price-val');
    var price = document.getElementById('product-price');
    if (val && price) price.value = val.textContent;
    document.getElementById('ai-price-popover').classList.remove('show');
}

// ─── VARIANTS ───
var variantIndex = 0;

function initVariants() {
    var toggle = document.getElementById('variants-toggle');
    var body = document.getElementById('variants-body');
    if (!toggle || !body) return;

    toggle.addEventListener('change', function() {
        body.style.display = this.checked ? '' : 'none';
    });

    // Pre-populate existing variants
    var existing = <?= json_encode($variants ?? []) ?>;
    if (existing && existing.length > 0) {
        existing.forEach(function(v) { addVariantRow(v); });
    }
}

function addVariantRow(data) {
    var idx = variantIndex++;
    var list = document.getElementById('variants-list');
    if (!list) return;

    var row = document.createElement('div');
    row.className = 'variant-row';
    row.style.cssText = 'background:var(--bg,#0f172a);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;margin-bottom:12px;position:relative';
    row.setAttribute('data-idx', idx);

    var name = (data && (data.variant_name || data.name)) ? (data.variant_name || data.name) : '';
    var price = (data && data.price !== null && data.price !== undefined) ? data.price : '';
    var salePrice = (data && data.sale_price !== null && data.sale_price !== undefined) ? data.sale_price : '';
    var sku = (data && data.sku) ? data.sku : '';
    var stock = (data && data.stock !== null && data.stock !== undefined) ? data.stock : '-1';
    var image = (data && data.image) ? data.image : '';
    var status = (data && data.status) ? data.status : 'active';
    var options = (data && data.options) ? data.options : [];

    var html = '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">';
    html += '<strong style="font-size:.85rem;color:var(--text,#e2e8f0)">Variant #' + (idx + 1) + '</strong>';
    html += '<button type="button" onclick="removeVariantRow(this)" style="background:#ef4444;color:#fff;border:none;padding:4px 12px;border-radius:6px;font-size:.75rem;cursor:pointer">✕ Remove</button>';
    html += '</div>';

    html += '<div class="form-row">';
    html += '<div class="form-group"><label>Variant Name *</label><input type="text" name="variants[' + idx + '][name]" value="' + escH(name) + '" placeholder="e.g. Large Red" required></div>';
    html += '<div class="form-group"><label>SKU</label><input type="text" name="variants[' + idx + '][sku]" value="' + escH(sku) + '"></div>';
    html += '</div>';

    html += '<div class="form-row">';
    html += '<div class="form-group"><label>Price Override</label><input type="number" step="0.01" min="0" name="variants[' + idx + '][price]" value="' + escH(String(price)) + '" placeholder="Leave empty = use product price"></div>';
    html += '<div class="form-group"><label>Sale Price</label><input type="number" step="0.01" min="0" name="variants[' + idx + '][sale_price]" value="' + escH(String(salePrice)) + '"></div>';
    html += '</div>';

    html += '<div class="form-row">';
    html += '<div class="form-group"><label>Stock</label><input type="number" min="-1" name="variants[' + idx + '][stock]" value="' + escH(String(stock)) + '"><div class="hint">-1 = unlimited</div></div>';
    html += '<div class="form-group"><label>Image URL</label><input type="url" name="variants[' + idx + '][image]" value="' + escH(image) + '" placeholder="https://..."></div>';
    html += '</div>';

    // Options (key-value pairs)
    html += '<div style="margin-top:8px">';
    html += '<label style="display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px">Options (attributes)</label>';
    html += '<div class="variant-options" id="variant-opts-' + idx + '">';
    html += '</div>';
    html += '<button type="button" onclick="addOptionRow(' + idx + ')" style="background:transparent;color:#6366f1;border:1px solid #6366f1;padding:4px 12px;border-radius:6px;font-size:.75rem;cursor:pointer;margin-top:6px">+ Add Option</button>';
    html += '</div>';

    row.innerHTML = html;
    list.appendChild(row);

    // Pre-populate options
    if (options && options.length > 0) {
        options.forEach(function(opt) { addOptionRow(idx, opt); });
    }
}

var optionCounters = {};

function addOptionRow(variantIdx, data) {
    if (!optionCounters[variantIdx]) optionCounters[variantIdx] = 0;
    var optIdx = optionCounters[variantIdx]++;
    var container = document.getElementById('variant-opts-' + variantIdx);
    if (!container) return;

    var optName = (data && data.name) ? data.name : '';
    var optValue = (data && data.value) ? data.value : '';

    var row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:8px;align-items:center;margin-bottom:6px';
    row.innerHTML = '<input type="text" name="variants[' + variantIdx + '][options][' + optIdx + '][name]" value="' + escH(optName) + '" placeholder="e.g. Size" style="flex:1;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:6px 10px;border-radius:6px;font-size:.8rem">'
        + '<input type="text" name="variants[' + variantIdx + '][options][' + optIdx + '][value]" value="' + escH(optValue) + '" placeholder="e.g. Medium" style="flex:1;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:6px 10px;border-radius:6px;font-size:.8rem">'
        + '<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;padding:2px 6px" title="Remove">✕</button>';
    container.appendChild(row);
}

function removeVariantRow(btn) {
    var row = btn.closest('.variant-row');
    if (row) row.remove();
}

function escH(s) {
    if (!s) return '';
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML.replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', initVariants);

// ─── AI TOOLS PANEL ───

function toggleAiTools() {
    var body = document.getElementById('ai-tools-body');
    var chev = document.getElementById('ai-tools-chevron');
    if (!body || !chev) return;
    var open = body.classList.toggle('open');
    chev.classList.toggle('open', open);
}

function switchAiTab(btn) {
    document.querySelectorAll('.ai-tab').forEach(function(t){ t.classList.remove('active'); });
    document.querySelectorAll('.ai-tab-content').forEach(function(c){ c.classList.remove('active'); });
    btn.classList.add('active');
    var target = document.getElementById(btn.dataset.tab);
    if (target) target.classList.add('active');
}

function getProductId() {
    var el = document.getElementById('product-id-hidden');
    return el ? parseInt(el.value) : 0;
}

// SEO Analyze
function runSeoAnalyze() {
    var pid = getProductId();
    if (!pid) return;
    var errEl = document.getElementById('seo-analyze-error');
    var resEl = document.getElementById('seo-analyze-results');
    errEl.classList.remove('show');
    resEl.style.display = 'none';

    var payload = {
        product_id: pid,
        focus_keyword: document.getElementById('seo-focus-keyword').value,
        language: document.getElementById('seo-analyze-lang').value
    };

    aiCall('/api/shop/ai/seo-analyze', payload, 'seo-analyze-spinner', function(err, data) {
        if (err || !data || !data.ok) {
            errEl.textContent = (data && data.error) ? data.error : (err || 'Analysis failed');
            errEl.classList.add('show');
            return;
        }
        var d = data.data || data;
        var html = '';

        // Score
        var score = d.health_score || 0;
        var color = score >= 70 ? '#10b981' : score >= 40 ? '#f59e0b' : '#ef4444';
        html += '<div style="display:flex;align-items:center;gap:16px;padding:14px;background:rgba(99,102,241,.08);border-radius:10px;margin-bottom:12px">';
        html += '<div style="font-size:2.2rem;font-weight:800;color:' + color + '">' + score + '</div>';
        html += '<div style="flex:1"><div style="font-weight:600;color:#c7d2fe;margin-bottom:2px">SEO Health Score</div>';
        html += '<div style="font-size:.8rem;color:#818cf8">' + esc(d.summary || '') + '</div></div></div>';

        // Meta suggestions
        if (d.on_page_checks && d.on_page_checks.meta_suggestions) {
            var ms = d.on_page_checks.meta_suggestions;
            html += '<div class="ai-result-item"><label>Recommended Meta Title</label>';
            html += '<button type="button" class="btn-apply" onclick="document.getElementById(\'meta-title\').value=this.dataset.v" data-v="' + esc(ms.recommended_title || '') + '">Apply</button>';
            html += '<div class="ai-preview">' + esc(ms.recommended_title || '') + '</div></div>';
            html += '<div class="ai-result-item"><label>Recommended Meta Description</label>';
            html += '<button type="button" class="btn-apply" onclick="document.getElementById(\'meta-description\').value=this.dataset.v" data-v="' + esc(ms.recommended_meta_description || '') + '">Apply</button>';
            html += '<div class="ai-preview">' + esc(ms.recommended_meta_description || '') + '</div></div>';
        }

        // Quick wins
        if (d.quick_wins && d.quick_wins.length) {
            html += '<div style="margin-top:10px"><strong style="color:#c7d2fe;font-size:.82rem">⚡ Quick Wins:</strong><ul style="margin:4px 0 0;padding-left:18px;color:#e2e8f0;font-size:.82rem">';
            d.quick_wins.forEach(function(w) { html += '<li style="margin-bottom:3px">' + esc(w) + '</li>'; });
            html += '</ul></div>';
        }

        // Actionable tasks
        if (d.actionable_tasks && d.actionable_tasks.length) {
            html += '<div style="margin-top:10px"><strong style="color:#c7d2fe;font-size:.82rem">✅ Tasks:</strong>';
            d.actionable_tasks.slice(0, 5).forEach(function(t) {
                var pColor = t.priority === 'critical' ? '#ef4444' : t.priority === 'high' ? '#f59e0b' : '#94a3b8';
                html += '<div style="border-left:3px solid ' + pColor + ';padding:6px 10px;margin:4px 0;border-radius:0 6px 6px 0;background:rgba(255,255,255,.03);font-size:.8rem">';
                html += '<strong>' + esc(t.task || '') + '</strong><br><span style="color:#818cf8">' + esc(t.details || '') + '</span></div>';
            });
            html += '</div>';
        }

        resEl.innerHTML = html;
        resEl.style.display = 'block';
    });
}

// Rewrite
var _rewriteData = null;

function runRewrite() {
    var pid = getProductId();
    if (!pid) return;
    var errEl = document.getElementById('rewrite-error');
    var resEl = document.getElementById('rewrite-results');
    errEl.classList.remove('show');
    resEl.style.display = 'none';

    var payload = {
        product_id: pid,
        mode: document.getElementById('rewrite-mode').value,
        field: document.getElementById('rewrite-field').value
    };

    aiCall('/api/shop/ai/rewrite', payload, 'rewrite-spinner', function(err, data) {
        if (err || !data || !data.ok) {
            errEl.textContent = (data && data.error) ? data.error : (err || 'Rewrite failed');
            errEl.classList.add('show');
            return;
        }
        _rewriteData = data;
        document.getElementById('rewrite-preview').innerHTML = data.rewritten || '';
        resEl.style.display = 'block';
    });
}

function applyRewrite() {
    if (!_rewriteData) return;
    var field = _rewriteData.field || 'description';
    if (field === 'short_description') {
        var el = document.querySelector('input[name="short_description"]');
        if (el) el.value = _rewriteData.rewritten.replace(/<[^>]*>/g, '');
    } else {
        var el = document.querySelector('textarea[name="description"]');
        if (el) el.value = _rewriteData.rewritten || '';
    }
}

// Keywords
function runKeywordResearch() {
    var pid = getProductId();
    if (!pid) return;
    var errEl = document.getElementById('keywords-error');
    var resEl = document.getElementById('keywords-results');
    errEl.classList.remove('show');
    resEl.style.display = 'none';

    var payload = {
        product_id: pid,
        language: document.getElementById('keywords-lang').value
    };

    aiCall('/api/shop/ai/keywords', payload, 'keywords-spinner', function(err, data) {
        if (err || !data || !data.ok) {
            errEl.textContent = (data && data.error) ? data.error : (err || 'Research failed');
            errEl.classList.add('show');
            return;
        }
        var html = '';

        // Primary keywords
        if (data.primary_keywords && data.primary_keywords.length) {
            html += '<div style="margin-bottom:12px"><strong style="color:#c7d2fe;font-size:.82rem">🎯 Primary Keywords:</strong>';
            html += '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">';
            data.primary_keywords.forEach(function(k) {
                var bg = k.priority === 'high' ? 'rgba(16,185,129,.15)' : k.priority === 'medium' ? 'rgba(245,158,11,.15)' : 'rgba(99,102,241,.15)';
                var clr = k.priority === 'high' ? '#34d399' : k.priority === 'medium' ? '#fbbf24' : '#a5b4fc';
                html += '<span style="background:' + bg + ';color:' + clr + ';padding:4px 10px;border-radius:6px;font-size:.78rem" title="Intent: ' + (k.search_intent || '') + ', Difficulty: ' + (k.difficulty || '') + '">' + esc(k.keyword || '') + '</span>';
            });
            html += '</div></div>';
        }

        // Long tail
        if (data.long_tail_keywords && data.long_tail_keywords.length) {
            html += '<div style="margin-bottom:12px"><strong style="color:#c7d2fe;font-size:.82rem">📏 Long-Tail Keywords:</strong>';
            html += '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">';
            data.long_tail_keywords.forEach(function(k) {
                html += '<span style="background:rgba(99,102,241,.1);color:#a5b4fc;padding:4px 10px;border-radius:6px;font-size:.78rem" title="Intent: ' + (k.search_intent || '') + '">' + esc(k.keyword || '') + '</span>';
            });
            html += '</div></div>';
        }

        // LSI
        if (data.lsi_keywords && data.lsi_keywords.length) {
            html += '<div style="margin-bottom:12px"><strong style="color:#c7d2fe;font-size:.82rem">🧠 LSI / Semantic Keywords:</strong>';
            html += '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">';
            data.lsi_keywords.forEach(function(k) {
                html += '<span style="background:rgba(59,130,246,.12);color:#93c5fd;padding:4px 10px;border-radius:6px;font-size:.78rem">' + esc(k) + '</span>';
            });
            html += '</div></div>';
        }

        // Questions
        if (data.questions && data.questions.length) {
            html += '<div style="margin-bottom:12px"><strong style="color:#c7d2fe;font-size:.82rem">❓ People Also Ask:</strong>';
            html += '<ul style="margin:6px 0 0;padding-left:18px;color:#e2e8f0;font-size:.82rem">';
            data.questions.forEach(function(q) { html += '<li style="margin-bottom:3px">' + esc(q) + '</li>'; });
            html += '</ul></div>';
        }

        // Content suggestions
        if (data.content_suggestions && data.content_suggestions.length) {
            html += '<div style="margin-bottom:12px"><strong style="color:#c7d2fe;font-size:.82rem">💡 Content Suggestions:</strong>';
            data.content_suggestions.forEach(function(s) {
                html += '<div style="background:rgba(255,255,255,.03);padding:8px 12px;margin:4px 0;border-radius:6px;font-size:.8rem">';
                html += '<span style="background:rgba(99,102,241,.2);color:#a5b4fc;padding:2px 8px;border-radius:4px;font-size:.7rem;margin-right:6px">' + esc(s.type || '') + '</span>';
                html += '<strong>' + esc(s.title || '') + '</strong>';
                if (s.target_keyword) html += ' <span style="color:#818cf8;font-size:.75rem">→ ' + esc(s.target_keyword) + '</span>';
                html += '</div>';
            });
            html += '</div>';
        }

        resEl.innerHTML = html;
        resEl.style.display = 'block';
    });
}

function esc(s) {
    if (!s) return '';
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML.replace(/"/g, '&quot;');
}

// ─── AI IMAGE TOOLS ───

function showImageError(msg) {
    var el = document.getElementById('images-error');
    el.textContent = msg;
    el.classList.add('show');
}
function clearImageError() {
    var el = document.getElementById('images-error');
    el.textContent = '';
    el.classList.remove('show');
}
function showImageResult(label, imgSrc, text, actions) {
    var resEl = document.getElementById('images-results');
    var labelEl = document.getElementById('images-result-label');
    var imgEl = document.getElementById('images-result-img');
    var textEl = document.getElementById('images-result-text');
    var actionsEl = document.getElementById('images-result-actions');

    labelEl.textContent = label;

    if (imgSrc) {
        imgEl.src = imgSrc;
        imgEl.style.display = 'block';
    } else {
        imgEl.style.display = 'none';
    }

    if (text) {
        textEl.innerHTML = text;
        textEl.style.display = 'block';
    } else {
        textEl.style.display = 'none';
    }

    actionsEl.innerHTML = actions || '';
    resEl.style.display = 'block';
}

function aiRemoveBg() {
    var pid = getProductId();
    if (!pid) return;
    clearImageError();

    aiCall('/api/shop/ai/remove-bg', {product_id: pid}, 'rmbg-spinner', function(err, data) {
        if (err || !data || !data.ok) {
            showImageError((data && data.error) ? data.error : (err || 'Background removal failed'));
            return;
        }
        var imgPath = data.path;
        var sizeKB = data.size ? Math.round(data.size / 1024) : '?';
        showImageResult(
            '✂️ Background Removed (' + sizeKB + ' KB)',
            imgPath,
            null,
            '<button type="button" class="btn-ai btn-ai-sm" onclick="applyProductImage(\'' + esc(imgPath) + '\')">✅ Use as Product Image</button>' +
            '<a href="' + esc(imgPath) + '" download class="btn-ai btn-ai-sm" style="background:#334155;text-decoration:none">💾 Download</a>'
        );
    });
}

function aiAltText() {
    var pid = getProductId();
    if (!pid) return;
    clearImageError();

    aiCall('/api/shop/ai/alt-text', {product_id: pid}, 'alt-spinner', function(err, data) {
        if (err || !data || !data.ok) {
            showImageError((data && data.error) ? data.error : (err || 'ALT text generation failed'));
            return;
        }
        showImageResult(
            '🏷️ Generated ALT Text',
            null,
            '<div style="background:rgba(99,102,241,.1);padding:12px;border-radius:8px;margin:8px 0">' +
                '<strong style="color:#c7d2fe;font-size:.78rem;display:block;margin-bottom:4px">SEO ALT Text:</strong>' +
                '<span style="color:#e2e8f0">' + esc(data.alt) + '</span>' +
            '</div>' +
            (data.raw_caption ? '<div style="font-size:.75rem;color:#818cf8;margin-top:4px">Raw caption: ' + esc(data.raw_caption) + '</div>' : ''),
            '<button type="button" class="btn-ai btn-ai-sm" onclick="navigator.clipboard.writeText(\'' + esc(data.alt).replace(/'/g, "\\'") + '\');this.textContent=\'✅ Copied!\'">📋 Copy ALT Text</button>'
        );
    });
}

function aiEnhanceImage() {
    var pid = getProductId();
    if (!pid) return;
    clearImageError();

    aiCall('/api/shop/ai/enhance-image', {product_id: pid}, 'enhance-spinner', function(err, data) {
        if (err || !data || !data.ok) {
            showImageError((data && data.error) ? data.error : (err || 'Image enhancement failed'));
            return;
        }
        var imgPath = data.path;
        var sizeKB = data.size ? Math.round(data.size / 1024) : '?';
        showImageResult(
            '✨ Enhanced Image (' + sizeKB + ' KB)',
            imgPath,
            null,
            '<button type="button" class="btn-ai btn-ai-sm" onclick="applyProductImage(\'' + esc(imgPath) + '\')">✅ Use as Product Image</button>' +
            '<a href="' + esc(imgPath) + '" download class="btn-ai btn-ai-sm" style="background:#334155;text-decoration:none">💾 Download</a>'
        );
    });
}

function aiGenerateImage() {
    var prompt = document.getElementById('img-gen-prompt').value.trim();
    if (!prompt) { showImageError('Enter a prompt first'); return; }
    clearImageError();

    var pid = getProductId();
    aiCall('/api/shop/ai/generate-image', {prompt: prompt, product_id: pid}, 'imggen-spinner', function(err, data) {
        if (err || !data || !data.ok) {
            showImageError((data && data.error) ? data.error : (err || 'Image generation failed'));
            return;
        }
        var imgPath = data.path;
        var sizeKB = data.size ? Math.round(data.size / 1024) : '?';
        showImageResult(
            '🎨 AI Generated Image (' + sizeKB + ' KB)',
            imgPath,
            null,
            '<button type="button" class="btn-ai btn-ai-sm" onclick="applyProductImage(\'' + esc(imgPath) + '\')">✅ Use as Product Image</button>' +
            '<a href="' + esc(imgPath) + '" download class="btn-ai btn-ai-sm" style="background:#334155;text-decoration:none">💾 Download</a>'
        );
    });
}

function applyProductImage(path) {
    var imgInput = document.getElementById('product-image');
    if (imgInput) {
        imgInput.value = path;
        imgInput.dispatchEvent(new Event('input'));
    }
}

// ─── DIGITAL PRODUCT SECTION ───
(function(){
    var typeSelect = document.querySelector('select[name="type"]');
    var digitalCard = document.getElementById('digital-card');
    if (typeSelect && digitalCard) {
        typeSelect.addEventListener('change', function() {
            digitalCard.style.display = this.value === 'digital' ? '' : 'none';
        });
    }

    // Digital file upload
    var uploadInput = document.getElementById('digital-file-upload');
    var pathInput = document.getElementById('digital-file-path');
    var statusEl = document.getElementById('digital-upload-status');
    if (uploadInput && pathInput) {
        uploadInput.addEventListener('change', function() {
            if (!this.files || !this.files[0]) return;
            var formData = new FormData();
            formData.append('digital_file', this.files[0]);
            formData.append('csrf_token', getCsrf());
            statusEl.style.display = 'block';
            statusEl.style.color = '#f59e0b';
            statusEl.textContent = 'Uploading...';
            fetch('/admin/shop/digital-upload', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (data.ok) {
                    pathInput.value = data.path;
                    statusEl.style.color = '#10b981';
                    statusEl.textContent = '✅ Uploaded: ' + data.filename;
                } else {
                    statusEl.style.color = '#ef4444';
                    statusEl.textContent = '❌ ' + (data.error || 'Upload failed');
                }
            })
            .catch(function(e){
                statusEl.style.color = '#ef4444';
                statusEl.textContent = '❌ Network error';
            });
        });
    }
})();
</script>

<?php
$content = ob_get_clean();
$title = $pageTitle;
require CMS_APP . '/views/admin/layouts/topbar.php';
