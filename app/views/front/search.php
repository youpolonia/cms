<?php
/**
 * Search results page — content only (wrapped by render_with_theme)
 * Variables available: $query, $results, $total, $currentPage, $totalPages
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = 'Search' . ($query ? ': ' . htmlspecialchars($query) : '');
$description = $query ? "Search results for {$query}" : 'Search';
?>
<section class="search-page" style="padding: 120px 0 80px; min-height: 60vh;">
    <div class="container" style="max-width: 800px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2rem; margin-bottom: 24px; color: var(--text, #e2e8f0);">
            <?php if ($query): ?>
                Search results for "<strong><?= htmlspecialchars($query) ?></strong>"
                <span style="font-size: 0.9rem; font-weight: 400; color: var(--muted, #94a3b8); margin-left: 8px;">(<?= $total ?> found)</span>
            <?php else: ?>
                Search
            <?php endif; ?>
        </h1>

        <form action="/search" method="get" style="margin-bottom: 32px;">
            <div style="display: flex; gap: 8px;">
                <input type="search" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Search pages and articles..."
                       style="flex:1; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border, #334155); background: var(--bg-card, rgba(255,255,255,0.05)); color: var(--text, #e2e8f0); font-size: 1rem;"
                       minlength="2" required>
                <button type="submit" style="padding: 12px 24px; border-radius: 8px; background: var(--primary, #6366f1); color: #fff; border: none; cursor: pointer; font-size: 1rem;">
                    Search
                </button>
            </div>
        </form>

        <?php if ($query && empty($results)): ?>
            <div style="text-align: center; padding: 40px 0; color: var(--muted, #94a3b8);">
                <div style="font-size: 3rem; margin-bottom: 12px;">🔍</div>
                <p>No results found for "<strong><?= htmlspecialchars($query) ?></strong>".</p>
                <p style="font-size: 0.9rem; margin-top: 8px;">Try different keywords or check your spelling.</p>
            </div>
        <?php elseif (!empty($results)): ?>
            <div class="search-results">
                <?php foreach ($results as $r): ?>
                <article style="padding: 20px 0; border-bottom: 1px solid var(--border, rgba(255,255,255,0.1));">
                    <a href="<?= htmlspecialchars($r['url']) ?>" style="text-decoration: none;">
                        <h2 style="font-size: 1.15rem; color: var(--primary, #6366f1); margin-bottom: 4px;">
                            <?= htmlspecialchars($r['title']) ?>
                        </h2>
                    </a>
                    <div style="font-size: 0.75rem; color: var(--muted, #94a3b8); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <?= $r['type'] === 'article' ? '📰 Article' : '📄 Page' ?>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--text, #cbd5e1); line-height: 1.6;">
                        <?= $r['excerpt'] ?>
                    </p>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav style="display: flex; justify-content: center; gap: 6px; margin-top: 32px;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="/search?q=<?= urlencode($query) ?>&page=<?= $i ?>"
                       style="padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border, #334155); color: var(--text, #e2e8f0); text-decoration: none; <?= $i === $currentPage ? 'background: var(--primary, #6366f1); border-color: var(--primary);' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
