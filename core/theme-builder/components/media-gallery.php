<?php
/**
 * Theme Builder 3.0 - Media Gallery Component
 * 
 * Provides reusable Media Gallery modal for image selection.
 * Features: Upload, Library, Stock Photos (Pexels), AI Generate
 * 
 * @package ThemeBuilder
 * @version 3.0
 */

defined('CMS_ROOT') or die('Access denied');

/**
 * Render Media Gallery Modal HTML
 * Include this at the end of your editor template, before </body>
 */
function tb_render_media_gallery_modal(): void {
    $mediaDir = dirname(CMS_APP) . '/uploads/media/';
    ?>
    <!-- Media Gallery Modal -->
    <div class="tb-media-modal" id="tb-media-modal">
        <div class="tb-media-dialog">
            <div class="tb-media-header">
                <h3>📁 Media Library</h3>
                <button type="button" class="tb-media-close" onclick="TB.closeMediaGallery()">×</button>
            </div>
            <div class="tb-media-body">
                <div class="tb-media-tabs">
                    <button type="button" class="tb-media-tab active" data-tab="upload">📤 Upload</button>
                    <button type="button" class="tb-media-tab" data-tab="library">🖼️ Library</button>
                    <button type="button" class="tb-media-tab" data-tab="stock">📷 Stock Photos</button>
                    <button type="button" class="tb-media-tab" data-tab="ai">✨ AI Generate</button>
                </div>

                <!-- Upload Tab -->
                <div class="tb-media-tab-content active" id="tb-media-tab-upload">
                    <div class="tb-upload-area" id="tb-upload-area">
                        <input type="file" id="tb-media-upload" accept="image/*">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📤</div>
                        <div>Drop image here or <label for="tb-media-upload" style="color: var(--tb-accent); cursor: pointer;">browse</label></div>
                    </div>
                    <div id="tb-upload-progress" style="display: none;">
                        <div style="background: var(--tb-border); border-radius: 4px; overflow: hidden;">
                            <div id="tb-upload-bar" style="height: 4px; background: var(--tb-accent); width: 0%; transition: width 0.3s;"></div>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--tb-text-muted); margin-top: 0.5rem;">Uploading...</p>
                    </div>
                </div>

                <!-- Library Tab -->
                <div class="tb-media-tab-content" id="tb-media-tab-library">
                    <div class="tb-media-grid" id="tb-media-grid">
                        <?php
                        if (is_dir($mediaDir)) {
                            $files = scandir($mediaDir, SCANDIR_SORT_DESCENDING);
                            $count = 0;
                            foreach ($files as $file) {
                                if ($file === '.' || $file === '..' || $file === 'thumbs' || is_dir($mediaDir . $file)) continue;
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) continue;
                                echo '<div class="tb-media-item" data-url="/uploads/media/' . htmlspecialchars($file) . '">';
                                echo '<img src="/uploads/media/' . htmlspecialchars($file) . '" alt="" loading="lazy">';
                                echo '<div class="tb-media-filename">' . htmlspecialchars($file) . '</div>';
                                echo '</div>';
                                if (++$count >= 100) break;
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Stock Photos Tab -->
                <div class="tb-media-tab-content" id="tb-media-tab-stock">
                    <div class="tb-stock-search">
                        <input type="text" id="tb-stock-search-input" placeholder="Search free stock photos (Pexels)...">
                        <button type="button" class="tb-btn tb-btn-primary" onclick="TB.searchStockPhotos()">🔍 Search</button>
                    </div>
                    <div id="tb-stock-results">
                        <div class="tb-stock-loading">
                            <p style="font-size: 1.5rem; margin-bottom: 0.5rem;">📷</p>
                            <p>Search for beautiful free photos from Pexels</p>
                        </div>
                    </div>
                </div>

                <!-- AI Generate Tab -->
                <div class="tb-media-tab-content" id="tb-media-tab-ai">
                    <div class="tb-ai-gen-form">
                        <label style="font-weight: 500;">Describe the image you want to create:</label>
                        <textarea class="tb-ai-gen-prompt" id="tb-ai-image-prompt" placeholder="A futuristic cityscape at sunset with flying cars..."></textarea>
                        <div class="tb-ai-gen-options">
                            <select id="tb-ai-image-style">
                                <option value="photorealistic">📸 Photorealistic</option>
                                <option value="digital-art">🎨 Digital Art</option>
                                <option value="illustration">✏️ Illustration</option>
                                <option value="3d-render">🧊 3D Render</option>
                            </select>
                            <select id="tb-ai-image-size">
                                <option value="1024x1024">Square (1024×1024)</option>
                                <option value="1792x1024">Landscape (1792×1024)</option>
                                <option value="1024x1792">Portrait (1024×1792)</option>
                            </select>
                            <button type="button" class="tb-btn tb-btn-ai" onclick="TB.generateAiImage()">✨ Generate</button>
                        </div>
                    </div>
                    <div class="tb-ai-gen-preview" id="tb-ai-gen-preview">
                        <div class="tb-ai-gen-status">
                            <p style="font-size: 2rem; margin-bottom: 0.5rem;">🎨</p>
                            <p>Describe your image and click Generate</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tb-media-footer">
                <button type="button" class="tb-btn" onclick="TB.closeMediaGallery()">Cancel</button>
                <button type="button" class="tb-btn tb-btn-primary" onclick="TB.selectMediaFromGallery()" id="tb-media-select-btn" disabled>Select Image</button>
            </div>
        </div>
    </div>
    <?php
}
