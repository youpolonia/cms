<?php
/**
 * JTB Theme - Search Results Template
 *
 * @package JTB Theme
 *
 * Variables:
 * @var string $query - Search query
 * @var array $results - Search results
 * @var int $totalResults - Total number of results
 * @var int $currentPage - Current page number
 * @var int $totalPages - Total pages
 */

defined('CMS_ROOT') or die('Direct access not allowed');

$query = $query ?? '';
$results = $results ?? [];
$totalResults = $totalResults ?? 0;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
?>
<div class="jtb-search-results">
    <div class="container">
        <header class="search-header">
            <h1 class="search-title">Search Results</h1>

            <form action="/search" method="get" class="search-form-inline">
                <input type="search" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Search..." aria-label="Search">
                <button type="submit" aria-label="Submit search">Search</button>
            </form>

            <?php if (!empty($query)): ?>
            <p class="search-info">
                <?php if ($totalResults > 0): ?>
                Found <strong><?= $totalResults ?></strong> result<?= $totalResults !== 1 ? 's' : '' ?> for "<strong><?= htmlspecialchars($query) ?></strong>"
                <?php else: ?>
                No results found for "<strong><?= htmlspecialchars($query) ?></strong>"
                <?php endif; ?>
            </p>
            <?php endif; ?>
        </header>

        <?php if (!empty($results)): ?>
        <div class="search-results-list">
            <?php foreach ($results as $result): ?>
            <article class="search-result-item">
                <h2 class="result-title">
                    <a href="<?= htmlspecialchars($result['url'] ?? '/') ?>"><?= htmlspecialchars($result['title'] ?? 'Untitled') ?></a>
                </h2>

                <div class="result-meta">
                    <span class="result-type"><?= htmlspecialchars(ucfirst($result['type'] ?? 'page')) ?></span>
                    <?php if (!empty($result['created_at'])): ?>
                    <span class="separator">•</span>
                    <time datetime="<?= htmlspecialchars($result['created_at']) ?>"><?= date('M j, Y', strtotime($result['created_at'])) ?></time>
                    <?php endif; ?>
                </div>

                <?php if (!empty($result['excerpt'])): ?>
                <p class="result-excerpt"><?= htmlspecialchars(mb_substr(strip_tags($result['excerpt']), 0, 200)) ?>...</p>
                <?php endif; ?>

                <a href="<?= htmlspecialchars($result['url'] ?? '/') ?>" class="result-link">View →</a>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="search-pagination" aria-label="Search results pagination">
            <?php if ($currentPage > 1): ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $currentPage - 1 ?>" class="pagination-link prev">← Previous</a>
            <?php endif; ?>

            <span class="pagination-info">Page <?= $currentPage ?> of <?= $totalPages ?></span>

            <?php if ($currentPage < $totalPages): ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $currentPage + 1 ?>" class="pagination-link next">Next →</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <?php elseif (!empty($query)): ?>
        <div class="search-empty">
            <p>No results match your search. Try different keywords or browse our content:</p>
            <div class="search-suggestions">
                <a href="/" class="suggestion-link">Homepage</a>
                <a href="/blog" class="suggestion-link">Blog</a>
            </div>
        </div>
        <?php else: ?>
        <div class="search-prompt">
            <p>Enter a search term above to find content.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
