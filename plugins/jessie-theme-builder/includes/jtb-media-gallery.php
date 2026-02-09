<?php
/**
 * JTB Media Gallery Component
 *
 * Provides reusable Media Gallery modal for image selection.
 * Features: Upload, Library, Stock Photos (Pexels), AI Generate
 *
 * Based on Theme Builder 3.0 media-gallery.php
 * Adapted for Jessie Theme Builder with 'jtb-' prefix
 *
 * @package JessieThemeBuilder
 * @version 1.0
 */

defined('CMS_ROOT') or define('CMS_ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))));

/**
 * Render JTB Media Gallery Modal HTML
 * Include this at the end of your editor template, before </body>
 *
 * @param string $csrfToken CSRF token for API calls
 * @param string $pexelsApiKey Pexels API key for stock photos
 */
function jtb_render_media_gallery_modal(string $csrfToken = '', string $pexelsApiKey = ''): void {
    // Get media directory - check multiple possible locations
    $mediaDir = null;
    $possiblePaths = [
        CMS_ROOT . '/uploads/media/',
        dirname(CMS_ROOT) . '/uploads/media/',
        '/var/www/cms/uploads/media/',
    ];

    foreach ($possiblePaths as $path) {
        if (is_dir($path)) {
            $mediaDir = $path;
            break;
        }
    }
    ?>
    <!-- JTB Media Gallery Modal -->
    <div class="jtb-media-modal" id="jtb-media-modal">
        <div class="jtb-media-dialog">
            <div class="jtb-media-header">
                <h3>Media Library</h3>
                <button type="button" class="jtb-media-close" onclick="JTB.closeMediaGallery()">&times;</button>
            </div>
            <div class="jtb-media-body">
                <div class="jtb-media-tabs">
                    <button type="button" class="jtb-media-tab active" data-tab="upload">Upload</button>
                    <button type="button" class="jtb-media-tab" data-tab="library">Library</button>
                    <button type="button" class="jtb-media-tab" data-tab="stock">Stock Photos</button>
                    <button type="button" class="jtb-media-tab" data-tab="ai">AI Generate</button>
                </div>

                <!-- Upload Tab -->
                <div class="jtb-media-tab-content active" id="jtb-media-tab-upload">
                    <div class="jtb-upload-area" id="jtb-upload-area">
                        <input type="file" id="jtb-media-upload" accept="image/*" style="display:none">
                        <div class="jtb-upload-icon">📁</div>
                        <p>Drop image here or <label for="jtb-media-upload" class="jtb-upload-browse">browse</label></p>
                        <p class="jtb-upload-hint">Supported: JPG, PNG, GIF, WebP, SVG</p>
                    </div>
                    <div id="jtb-upload-progress" style="display: none;">
                        <div class="jtb-progress-track">
                            <div id="jtb-upload-bar" class="jtb-progress-bar"></div>
                        </div>
                        <p class="jtb-upload-status">Uploading...</p>
                    </div>
                </div>

                <!-- Library Tab -->
                <div class="jtb-media-tab-content" id="jtb-media-tab-library">
                    <div class="jtb-media-grid" id="jtb-media-grid">
                        <?php
                        // FIXED 2026-02-03: Scan multiple upload directories
                        $mediaDirs = [
                            ['path' => CMS_ROOT . '/uploads/media/', 'url' => '/uploads/media/'],
                            ['path' => CMS_ROOT . '/uploads/ai-images/', 'url' => '/uploads/ai-images/'],
                            ['path' => CMS_ROOT . '/uploads/jtb/', 'url' => '/uploads/jtb/'],
                        ];

                        $allFiles = [];
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

                        foreach ($mediaDirs as $dir) {
                            if (is_dir($dir['path'])) {
                                $iterator = new RecursiveIteratorIterator(
                                    new RecursiveDirectoryIterator($dir['path'], RecursiveDirectoryIterator::SKIP_DOTS)
                                );
                                foreach ($iterator as $file) {
                                    if ($file->isFile()) {
                                        $ext = strtolower($file->getExtension());
                                        $filename = $file->getFilename();
                                        // Skip thumbnails
                                        if (strpos($filename, '_thumb') !== false) continue;
                                        if (!in_array($ext, $allowedExts)) continue;

                                        $relativePath = str_replace($dir['path'], '', $file->getPathname());
                                        $relativePath = str_replace('\\', '/', $relativePath);

                                        $allFiles[] = [
                                            'filename' => $filename,
                                            'url' => $dir['url'] . $relativePath,
                                            'mtime' => $file->getMTime()
                                        ];
                                    }
                                }
                            }
                        }

                        // Sort by modification time (newest first)
                        usort($allFiles, function($a, $b) {
                            return $b['mtime'] - $a['mtime'];
                        });

                        // Render files
                        $count = 0;
                        foreach ($allFiles as $fileInfo) {
                            $url = htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8');
                            $filename = htmlspecialchars($fileInfo['filename'], ENT_QUOTES, 'UTF-8');
                            echo '<div class="jtb-media-item" data-url="' . $url . '">';
                            echo '<img src="' . $url . '" alt="" loading="lazy">';
                            echo '<div class="jtb-media-filename">' . $filename . '</div>';
                            echo '<div class="jtb-media-checkbox"></div>';
                            echo '</div>';
                            if (++$count >= 100) break;
                        }

                        if ($count === 0) {
                            echo '<div class="jtb-media-empty">No images found in library</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Stock Photos Tab -->
                <div class="jtb-media-tab-content" id="jtb-media-tab-stock">
                    <div class="jtb-stock-search">
                        <input type="text" id="jtb-stock-search-input" placeholder="Search free stock photos (e.g., nature, business, food)...">
                        <button type="button" class="jtb-btn jtb-btn-primary" onclick="JTB.searchStockPhotos()">Search</button>
                    </div>
                    <div id="jtb-stock-results" class="jtb-stock-results">
                        <div class="jtb-stock-placeholder">
                            <div class="jtb-stock-placeholder-icon">📷</div>
                            <p>Search for beautiful free photos from Pexels</p>
                        </div>
                    </div>
                    <div class="jtb-stock-attribution">
                        Photos provided by <a href="https://www.pexels.com" target="_blank" rel="noopener">Pexels</a>
                    </div>
                </div>

                <!-- AI Generate Tab -->
                <div class="jtb-media-tab-content" id="jtb-media-tab-ai">
                    <div class="jtb-ai-form">
                        <label class="jtb-ai-label">Describe the image you want to create:</label>
                        <textarea class="jtb-ai-prompt" id="jtb-ai-image-prompt" rows="3" placeholder="A serene mountain landscape at sunset with snow-capped peaks..."></textarea>
                        <div class="jtb-ai-options">
                            <div class="jtb-ai-option">
                                <label>Style</label>
                                <select id="jtb-ai-image-style">
                                    <option value="photorealistic">Photorealistic</option>
                                    <option value="digital-art">Digital Art</option>
                                    <option value="illustration">Illustration</option>
                                    <option value="3d-render">3D Render</option>
                                    <option value="anime">Anime</option>
                                    <option value="watercolor">Watercolor</option>
                                </select>
                            </div>
                            <div class="jtb-ai-option">
                                <label>Size</label>
                                <select id="jtb-ai-image-size">
                                    <option value="1024x1024">Square (1024x1024)</option>
                                    <option value="1792x1024">Landscape (1792x1024)</option>
                                    <option value="1024x1792">Portrait (1024x1792)</option>
                                </select>
                            </div>
                            <button type="button" class="jtb-btn jtb-btn-primary jtb-ai-generate-btn" onclick="JTB.generateAiImage()">Generate</button>
                        </div>
                    </div>
                    <div class="jtb-ai-preview" id="jtb-ai-preview">
                        <div class="jtb-ai-placeholder">
                            <div class="jtb-ai-placeholder-icon">🎨</div>
                            <p>Describe your image and click Generate</p>
                            <p class="jtb-ai-hint">Generation may take 10-30 seconds</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="jtb-media-footer">
                <button type="button" class="jtb-btn" onclick="JTB.closeMediaGallery()">Cancel</button>
                <button type="button" class="jtb-btn jtb-btn-primary" onclick="JTB.selectMediaFromGallery()" id="jtb-media-select-btn" disabled>Select Image</button>
            </div>
        </div>
    </div>

    <!-- JTB Media Gallery Configuration -->
    <script>
    window.JTB_MEDIA_CONFIG = {
        csrfToken: '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>',
        pexelsApiKey: '<?= htmlspecialchars($pexelsApiKey, ENT_QUOTES, 'UTF-8') ?>',
        uploadEndpoint: '/api/jtb/upload',
        aiEndpoint: '/admin/api/ai-image-generate.php'
    };
    </script>
    <?php
}
