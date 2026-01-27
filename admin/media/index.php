<?php

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/media_library.php';
require_once CMS_ROOT . '/core/ai_hf.php';

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo '403 Forbidden - This page is only accessible in development mode.';
    exit;
}

cms_require_admin_role();

if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

function format_filesize($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    }
    return round($bytes / 1024, 2) . ' KB';
}

$saveMessage = null;
$saveSuccess = null;
$items = [];
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_alt') {
    csrf_validate_or_403();

    try {
        $alts = isset($_POST['alt']) && is_array($_POST['alt']) ? $_POST['alt'] : [];
        $updatedCount = 0;
        $failedCount = 0;

        foreach ($alts as $id => $altText) {
            $id = (string)$id;
            $altText = (string)$altText;

            if (media_library_update_alt($id, $altText)) {
                $updatedCount++;
            } else {
                $failedCount++;
            }
        }

        if ($updatedCount > 0 && $failedCount === 0) {
            $saveSuccess = true;
            $saveMessage = 'ALT text updated for ' . $updatedCount . ' item(s).';
        } elseif ($updatedCount > 0 && $failedCount > 0) {
            $saveSuccess = null;
            $saveMessage = 'ALT text updated for ' . $updatedCount . ' item(s); ' . $failedCount . ' failed.';
        } elseif ($updatedCount === 0 && $failedCount > 0) {
            $saveSuccess = false;
            $saveMessage = 'Failed to update ALT text for selected items.';
        }
    } catch (Exception $e) {
        error_log('admin/media/index.php save_alt error: ' . $e->getMessage());
        $saveSuccess = false;
        $saveMessage = 'An unexpected error occurred while saving ALT text.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_ai_alt') {
    csrf_validate_or_403();

    try {
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        if ($id === '') {
            throw new Exception('Missing file ID');
        }

        $uploadsRoot = CMS_ROOT . '/uploads';
        $absolutePath = $uploadsRoot . '/' . $id;

        $result = ai_hf_generate_alt($absolutePath);

        if ($result['ok']) {
            if (media_library_update_alt($id, $result['alt'])) {
                $saveSuccess = true;
                $saveMessage = 'AI ALT generated successfully: ' . $result['alt'];
            } else {
                $saveSuccess = false;
                $saveMessage = 'Failed to save AI-generated ALT text.';
            }
        } else {
            $saveSuccess = false;
            $saveMessage = 'AI ALT generation failed: ' . esc($result['error']);
        }
    } catch (Exception $e) {
        error_log('admin/media/index.php generate_ai_alt error: ' . $e->getMessage());
        $saveSuccess = false;
        $saveMessage = 'An unexpected error occurred during AI ALT generation.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_generate_ai_alt') {
    csrf_validate_or_403();

    try {
        $allItems = media_library_get_all();
        if (!is_array($allItems)) {
            $allItems = [];
        }

        $filter = isset($_POST['filter']) ? trim($_POST['filter']) : 'all';

        $candidateItems = [];
        foreach ($allItems as $item) {
            $hasAlt = isset($item['alt']) && trim($item['alt']) !== '';
            if (!$hasAlt) {
                $isImage = isset($item['mime']) && strpos($item['mime'], 'image/') === 0;
                if ($isImage) {
                    $candidateItems[] = $item;
                }
            }
        }

        define('MAX_BULK_AI_ALT', 20);
        $processCount = min(count($candidateItems), MAX_BULK_AI_ALT);
        $successCount = 0;
        $failedCount = 0;

        $uploadsRoot = CMS_ROOT . '/uploads';

        for ($i = 0; $i < $processCount; $i++) {
            $item = $candidateItems[$i];
            $id = $item['id'];
            $absolutePath = $uploadsRoot . '/' . $id;

            $result = ai_hf_generate_alt($absolutePath);

            if ($result['ok'] && isset($result['alt']) && trim($result['alt']) !== '') {
                if (media_library_update_alt($id, $result['alt'])) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } else {
                $failedCount++;
            }
        }

        $remainingWithoutAlt = count($candidateItems) - $processCount;

        if ($successCount > 0 && $failedCount === 0) {
            $saveSuccess = true;
            $saveMessage = 'Generated ALT for ' . $successCount . ' image(s) using AI.';
            if ($remainingWithoutAlt > 0) {
                $saveMessage .= ' Remaining images without ALT: ' . $remainingWithoutAlt . '.';
            }
        } elseif ($successCount > 0 && $failedCount > 0) {
            $saveSuccess = null;
            $saveMessage = 'Generated ALT for ' . $successCount . ' image(s); ' . $failedCount . ' failed.';
            if ($remainingWithoutAlt > 0) {
                $saveMessage .= ' Remaining images without ALT: ' . $remainingWithoutAlt . '.';
            }
        } elseif ($successCount === 0 && $failedCount > 0) {
            $saveSuccess = false;
            $saveMessage = 'Failed to generate ALT for ' . $failedCount . ' image(s).';
        } elseif ($successCount === 0 && $failedCount === 0) {
            $saveSuccess = null;
            $saveMessage = 'No images without ALT found to process.';
        }
    } catch (Exception $e) {
        error_log('admin/media/index.php bulk_generate_ai_alt error: ' . $e->getMessage());
        $saveSuccess = false;
        $saveMessage = 'An unexpected error occurred during bulk AI ALT generation.';
    }
}

try {
    $items = media_library_get_all();
    if (!is_array($items)) {
        $items = [];
    }
} catch (Exception $e) {
    error_log('admin/media/index.php load error: ' . $e->getMessage());
    $items = [];
    $errorMessage = 'Unable to load media library.';
}

$allItems = $items;

$totalCount = 0;
$withAltCount = 0;
$missingAltCount = 0;

foreach ($allItems as $item) {
    $totalCount++;
    $hasAlt = isset($item['alt']) && trim($item['alt']) !== '';
    if ($hasAlt) {
        $withAltCount++;
    } else {
        $missingAltCount++;
    }
}

$mediaStats = [
    'total_count' => $totalCount,
    'with_alt_count' => $withAltCount,
    'missing_alt_count' => $missingAltCount
];

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
if (!in_array($filter, ['all', 'missing_alt'])) {
    $filter = 'all';
}

if ($filter === 'missing_alt') {
    $filteredItems = [];
    foreach ($allItems as $item) {
        $hasAlt = isset($item['alt']) && trim($item['alt']) !== '';
        if (!$hasAlt) {
            $filteredItems[] = $item;
        }
    }
    $items = $filteredItems;
} else {
    $items = $allItems;
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="container">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
      <h1>Media Library 2.0</h1>
      <p style="color: #666; margin: 10px 0 0 0;">
        Browse uploaded media files and manage their ALT text for better SEO with AI-powered assistance.
      </p>
    </div>
    <a href="upload.php" class="btn btn-primary">Upload Media</a>
  </div>

  <!-- Tabs Navigation -->
  <div style="display: flex; gap: 0; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0;">
    <button id="tabLibrary" onclick="switchMediaTab('library')" style="padding: 12px 24px; border: none; background: #007bff; color: white; font-weight: 600; cursor: pointer; border-radius: 8px 8px 0 0;">
      📁 My Library
    </button>
    <button id="tabStock" onclick="switchMediaTab('stock')" style="padding: 12px 24px; border: none; background: #f1f5f9; color: #64748b; font-weight: 600; cursor: pointer; border-radius: 8px 8px 0 0; margin-left: 4px;">
      🎬 Stock Videos
    </button>
    <button id="tabStockImages" onclick="switchMediaTab('stockImages')" style="padding: 12px 24px; border: none; background: #f1f5f9; color: #64748b; font-weight: 600; cursor: pointer; border-radius: 8px 8px 0 0; margin-left: 4px;">
      🖼️ Stock Images
    </button>
  </div>

  <!-- Stock Videos Panel -->
  <div id="panelStock" style="display: none;">
    <div class="card" style="margin-bottom: 20px;">
      <div class="card-body" style="padding: 20px;">
        <div style="display: flex; gap: 12px; margin-bottom: 20px;">
          <input type="text" id="stockVideoSearch" placeholder="Search stock videos (e.g. nature, business, technology...)" 
                 style="flex: 1; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
          <button onclick="searchStockVideos()" class="btn btn-primary" style="padding: 12px 24px;">
            🔍 Search
          </button>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <span style="color: #64748b; font-size: 13px;">Popular:</span>
          <button onclick="searchStockVideos('nature')" class="btn btn-secondary btn-sm">Nature</button>
          <button onclick="searchStockVideos('business')" class="btn btn-secondary btn-sm">Business</button>
          <button onclick="searchStockVideos('technology')" class="btn btn-secondary btn-sm">Technology</button>
          <button onclick="searchStockVideos('city')" class="btn btn-secondary btn-sm">City</button>
          <button onclick="searchStockVideos('ocean')" class="btn btn-secondary btn-sm">Ocean</button>
          <button onclick="searchStockVideos('abstract')" class="btn btn-secondary btn-sm">Abstract</button>
        </div>
      </div>
    </div>
    <div id="stockVideoResults" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
      <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: #64748b;">
        <div style="font-size: 48px; margin-bottom: 16px;">🎬</div>
        <p>Search for free stock videos from Pexels</p>
        <p style="font-size: 13px; margin-top: 8px;">Videos are free to use, no attribution required</p>
      </div>
    </div>
  </div>

  <!-- Stock Images Panel -->
  <div id="panelStockImages" style="display: none;">
    <div class="card" style="margin-bottom: 20px;">
      <div class="card-body" style="padding: 20px;">
        <div style="display: flex; gap: 12px; margin-bottom: 20px;">
          <input type="text" id="stockImageSearch" placeholder="Search stock images (e.g. office, nature, people...)" 
                 style="flex: 1; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
          <button onclick="searchStockImages()" class="btn btn-primary" style="padding: 12px 24px;">
            🔍 Search
          </button>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <span style="color: #64748b; font-size: 13px;">Popular:</span>
          <button onclick="searchStockImages('office')" class="btn btn-secondary btn-sm">Office</button>
          <button onclick="searchStockImages('nature')" class="btn btn-secondary btn-sm">Nature</button>
          <button onclick="searchStockImages('people')" class="btn btn-secondary btn-sm">People</button>
          <button onclick="searchStockImages('technology')" class="btn btn-secondary btn-sm">Technology</button>
          <button onclick="searchStockImages('food')" class="btn btn-secondary btn-sm">Food</button>
          <button onclick="searchStockImages('architecture')" class="btn btn-secondary btn-sm">Architecture</button>
        </div>
      </div>
    </div>
    <div id="stockImageResults" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
      <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: #64748b;">
        <div style="font-size: 48px; margin-bottom: 16px;">🖼️</div>
        <p>Search for free stock images from Pexels</p>
        <p style="font-size: 13px; margin-top: 8px;">Images are free to use, no attribution required</p>
      </div>
    </div>
  </div>

  <!-- Library Panel (original content) -->
  <div id="panelLibrary">

  <div class="card" style="margin-bottom: 20px; border-left: 4px solid #007bff;">
    <div class="card-body" style="background: #e7f3ff; padding: 15px;">
      <strong style="color: #004085;">ALT Text Summary:</strong>
      <div style="margin-top: 8px; color: #004085;">
        Total images: <strong><?php echo esc($mediaStats['total_count']); ?></strong> |
        With ALT: <strong><?php echo esc($mediaStats['with_alt_count']); ?></strong> |
        Without ALT: <strong><?php echo esc($mediaStats['missing_alt_count']); ?></strong>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 15px;">
      <strong>Filter:</strong>
      <a href="index.php" style="margin-left: 10px; text-decoration: none; <?php echo $filter === 'all' ? 'font-weight: bold; color: #007bff;' : 'color: #666;'; ?>">
        All images
      </a>
      |
      <a href="index.php?filter=missing_alt" style="text-decoration: none; <?php echo $filter === 'missing_alt' ? 'font-weight: bold; color: #007bff;' : 'color: #666;'; ?>">
        Only images without ALT
      </a>
      <?php if ($filter === 'missing_alt'): ?>
        <span style="margin-left: 10px; color: #666; font-style: italic;">
          (Showing <?php echo count($items); ?> image<?php echo count($items) !== 1 ? 's' : ''; ?> without ALT)
        </span>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($saveMessage !== null): ?>
    <div class="card" style="margin-bottom: 20px; border-left: 4px solid <?php
      echo $saveSuccess === true ? '#28a745' : ($saveSuccess === false ? '#dc3545' : '#ffc107');
    ?>;">
      <div class="card-body" style="background: <?php
        echo $saveSuccess === true ? '#d4edda' : ($saveSuccess === false ? '#f8d7da' : '#fff3cd');
      ?>; color: <?php
        echo $saveSuccess === true ? '#155724' : ($saveSuccess === false ? '#721c24' : '#856404');
      ?>;">
        <?php echo esc($saveMessage); ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($errorMessage !== null): ?>
    <div class="card" style="margin-bottom: 20px; border-left: 4px solid #dc3545;">
      <div class="card-body" style="background: #f8d7da; color: #721c24;">
        <?php echo esc($errorMessage); ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($errorMessage === null && empty($items)): ?>
    <div class="card">
      <div class="card-body" style="padding: 40px; text-align: center; color: #666;">
        <p style="margin: 0; font-size: 16px;">
          <?php if ($filter === 'missing_alt'): ?>
            No images without ALT found. Great job!
          <?php else: ?>
            No media files found in the uploads directory.
          <?php endif; ?>
        </p>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($errorMessage === null && !empty($items)): ?>
    <form method="post" action="">
      <input type="hidden" name="action" value="save_alt">
      <input type="hidden" name="filter" value="<?php echo esc($filter); ?>">
      <?php csrf_field(); ?>

      <div class="card">
        <div class="card-body">
          <table class="table">
            <thead>
              <tr>
                <th style="width: 100px;">Preview</th>
                <th>File</th>
                <th style="width: 100px;">Size</th>
                <th style="width: 150px;">MIME Type</th>
                <th style="width: 300px;">ALT Text</th>
                <th style="width: 180px;">Updated</th>
                <th style="width: 100px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
                <tr>
                  <td>
                    <?php
                    $isImage = isset($item['mime']) && strpos($item['mime'], 'image/') === 0;
                    ?>
                    <?php if ($isImage): ?>
                      <img src="<?php echo esc($item['path']); ?>" alt="" style="max-width: 80px; max-height: 80px; display: block; border: 1px solid #ddd; border-radius: 4px;">
                    <?php else: ?>
                      <span style="color: #999;">—</span>
                    <?php endif; ?>
                  </td>
                  <td style="font-family: monospace; font-size: 13px;">
                    <a href="<?php echo esc($item['path']); ?>" target="_blank" style="color: #007bff; text-decoration: none;">
                      <?php echo esc($item['basename']); ?>
                    </a>
                    <br>
                    <span style="color: #999; font-size: 11px;"><?php echo esc($item['id']); ?></span>
                  </td>
                  <td>
                    <?php echo esc(format_filesize($item['size'])); ?>
                  </td>
                  <td style="font-size: 12px; color: #666;">
                    <?php echo esc($item['mime']); ?>
                  </td>
                  <td>
                    <?php
                    $hasAlt = isset($item['alt']) && trim($item['alt']) !== '';
                    ?>
                    <input
                      type="text"
                      name="alt[<?php echo esc($item['id']); ?>]"
                      value="<?php echo esc($item['alt']); ?>"
                      class="form-control"
                      placeholder="Enter ALT text..."
                      style="width: 100%; margin-bottom: 5px;">
                    <?php if (!$hasAlt && $isImage): ?>
                      <span style="display: inline-block; background: #dc3545; color: white; font-size: 10px; padding: 2px 6px; border-radius: 3px; margin-bottom: 5px;">
                        Missing ALT
                      </span>
                    <?php endif; ?>
                    <?php if ($isImage): ?>
                      <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="action" value="generate_ai_alt">
                        <input type="hidden" name="id" value="<?php echo esc($item['id']); ?>">
                        <?php csrf_field(); ?>
                        <button type="submit" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 3px 8px;">
                          Generate ALT (AI)
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                  <td style="font-size: 12px; color: #666;">
                    <?php if (isset($item['updated']) && $item['updated'] !== null && trim($item['updated']) !== ''): ?>
                      <?php echo esc($item['updated']); ?>
                    <?php else: ?>
                      <span style="color: #999;">—</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="edit.php?id=<?php echo urlencode($item['id']); ?>" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 3px 8px; margin-bottom: 3px; display: inline-block;">
                      Edit
                    </a>
                    <a href="delete.php?file=<?php echo urlencode($item['basename']); ?>" class="btn btn-danger btn-sm" style="font-size: 11px; padding: 3px 8px; display: inline-block;" onclick="return confirm('Delete this file?');">
                      Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
            <p style="margin: 0 0 15px 0; color: #666;">
              Showing <?php echo count($items); ?> file<?php echo count($items) !== 1 ? 's' : ''; ?>
              <?php if ($filter === 'missing_alt'): ?>
                without ALT
              <?php endif; ?>
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center;">
              <button type="submit" class="btn btn-primary">Save ALT text</button>

              <?php if ($mediaStats['missing_alt_count'] > 0): ?>
                <div>
                  <button type="submit" name="action" value="bulk_generate_ai_alt" class="btn btn-success">
                    Generate AI ALT for missing images (up to 20)
                  </button>
                  <div style="margin-top: 5px; font-size: 11px; color: #666;">
                    This will generate ALT text using AI for up to 20 images without ALT
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </form>
  <?php endif; ?>
  </div><!-- end panelLibrary -->
</main>

<script>
function switchMediaTab(tab) {
    // Update tab buttons
    document.getElementById('tabLibrary').style.background = tab === 'library' ? '#007bff' : '#f1f5f9';
    document.getElementById('tabLibrary').style.color = tab === 'library' ? 'white' : '#64748b';
    document.getElementById('tabStock').style.background = tab === 'stock' ? '#007bff' : '#f1f5f9';
    document.getElementById('tabStock').style.color = tab === 'stock' ? 'white' : '#64748b';
    document.getElementById('tabStockImages').style.background = tab === 'stockImages' ? '#007bff' : '#f1f5f9';
    document.getElementById('tabStockImages').style.color = tab === 'stockImages' ? 'white' : '#64748b';
    
    // Show/hide panels
    document.getElementById('panelLibrary').style.display = tab === 'library' ? 'block' : 'none';
    document.getElementById('panelStock').style.display = tab === 'stock' ? 'block' : 'none';
    document.getElementById('panelStockImages').style.display = tab === 'stockImages' ? 'block' : 'none';
}

async function searchStockVideos(query) {
    query = query || document.getElementById('stockVideoSearch').value;
    if (!query) {
        alert('Please enter a search term');
        return;
    }
    
    document.getElementById('stockVideoSearch').value = query;
    const results = document.getElementById('stockVideoResults');
    results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px;"><div class="spinner"></div><p>Searching...</p></div>';
    
    try {
        const response = await fetch(`/api/stock-videos.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.videos && data.videos.length > 0) {
            results.innerHTML = data.videos.map(video => `
                <div class="stock-video-card" style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="position: relative; aspect-ratio: 16/9; background: #1e293b;">
                        <img src="${video.preview}" alt="Video preview" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.7); color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                            ${video.duration || ''}
                        </div>
                    </div>
                    <div style="padding: 12px;">
                        <button onclick="downloadStockVideo('${encodeURIComponent(video.url)}', 'video')" class="btn btn-primary btn-sm" style="width: 100%;">
                            ⬇️ Download to Library
                        </button>
                        <button onclick="copyToClipboard('${video.url}')" class="btn btn-secondary btn-sm" style="width: 100%; margin-top: 8px;">
                            📋 Copy URL
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">No videos found. Try a different search term.</div>';
        }
    } catch (error) {
        results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #dc3545;">Error searching videos. Please check API settings.</div>';
    }
}

async function searchStockImages(query) {
    query = query || document.getElementById('stockImageSearch').value;
    if (!query) {
        alert('Please enter a search term');
        return;
    }
    
    document.getElementById('stockImageSearch').value = query;
    const results = document.getElementById('stockImageResults');
    results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px;"><div class="spinner"></div><p>Searching...</p></div>';
    
    try {
        const response = await fetch(`/api/stock-images.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.images && data.images.length > 0) {
            results.innerHTML = data.images.map(img => `
                <div class="stock-image-card" style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="aspect-ratio: 1; background: #f1f5f9;">
                        <img src="${img.preview}" alt="${img.alt || 'Stock image'}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="padding: 12px;">
                        <button onclick="downloadStockVideo('${encodeURIComponent(img.url)}', 'image')" class="btn btn-primary btn-sm" style="width: 100%;">
                            ⬇️ Download to Library
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">No images found. Try a different search term.</div>';
        }
    } catch (error) {
        results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #dc3545;">Error searching images. Please check API settings.</div>';
    }
}

async function downloadStockVideo(encodedUrl, type) {
    const url = decodeURIComponent(encodedUrl);
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Downloading...';
    btn.disabled = true;
    
    try {
        const response = await fetch('/api/download-stock-media.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url: url, type: type })
        });
        const data = await response.json();
        
        if (data.success) {
            btn.innerHTML = '✅ Downloaded!';
            btn.style.background = '#10b981';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
                btn.disabled = false;
            }, 2000);
        } else {
            throw new Error(data.error || 'Download failed');
        }
    } catch (error) {
        btn.innerHTML = '❌ Failed';
        btn.style.background = '#ef4444';
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
            btn.disabled = false;
        }, 2000);
        alert('Download failed: ' + error.message);
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target;
        const original = btn.innerHTML;
        btn.innerHTML = '✅ Copied!';
        setTimeout(() => btn.innerHTML = original, 1500);
    });
}

// Enter key support
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('stockVideoSearch')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchStockVideos();
    });
    document.getElementById('stockImageSearch')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchStockImages();
    });
});
</script>

<style>
.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e2e8f0;
    border-top-color: #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.stock-video-card:hover, .stock-image-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.2s;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php';
