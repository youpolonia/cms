<?php
declare(strict_types=1);

namespace Admin;

use Core\Request;
use Core\Session;

class AbTestController
{
    public function index(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();

        $tests = $pdo->query(
            "SELECT t.*, p.title as page_title 
             FROM ab_tests t LEFT JOIN pages p ON p.id = t.page_id
             ORDER BY t.created_at DESC"
        )->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate stats
        foreach ($tests as &$t) {
            $t['total_views'] = ($t['views_a'] ?? 0) + ($t['views_b'] ?? 0);
            $t['total_conversions'] = ($t['conversions_a'] ?? 0) + ($t['conversions_b'] ?? 0);
            $t['rate_a'] = ($t['views_a'] > 0) ? round($t['conversions_a'] / $t['views_a'] * 100, 1) : 0;
            $t['rate_b'] = ($t['views_b'] > 0) ? round($t['conversions_b'] / $t['views_b'] * 100, 1) : 0;
            
            // Simple significance check (need >100 views per variant)
            $t['significant'] = ($t['views_a'] >= 100 && $t['views_b'] >= 100);
            if ($t['significant'] && $t['rate_a'] != $t['rate_b']) {
                $t['suggested_winner'] = $t['rate_a'] > $t['rate_b'] ? 'a' : 'b';
            } else {
                $t['suggested_winner'] = null;
            }
        }
        unset($t);

        $stats = [
            'total' => count($tests),
            'running' => count(array_filter($tests, fn($t) => $t['status'] === 'running')),
            'completed' => count(array_filter($tests, fn($t) => $t['status'] === 'completed')),
        ];

        render('admin/ab-testing/index', ['tests' => $tests, 'stats' => $stats]);
    }

    public function create(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();
        $pages = $pdo->query("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
        render('admin/ab-testing/form', ['test' => null, 'pages' => $pages]);
    }

    public function edit(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();
        $id = (int)$request->param('id');
        $stmt = $pdo->prepare("SELECT * FROM ab_tests WHERE id = ?");
        $stmt->execute([$id]);
        $test = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$test) { Session::flash('error', 'Test not found.'); \Core\Response::redirect('/admin/ab-testing'); }
        
        $pages = $pdo->query("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
        render('admin/ab-testing/form', ['test' => $test, 'pages' => $pages]);
    }

    public function store(Request $request): void
    {
        Session::requireRole('admin');
        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO ab_tests (name, page_id, type, variant_a, variant_b, selector, goal, goal_selector, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'running')"
        );
        $stmt->execute([
            trim($_POST['name'] ?? ''),
            ((int)($_POST['page_id'] ?? 0)) ?: null,
            $_POST['type'] ?? 'headline',
            $_POST['variant_a'] ?? '',
            $_POST['variant_b'] ?? '',
            $_POST['selector'] ?? '',
            $_POST['goal'] ?? 'click',
            $_POST['goal_selector'] ?? '',
        ]);
        Session::flash('success', 'A/B test created and running.');
        \Core\Response::redirect('/admin/ab-testing');
    }

    public function update(Request $request): void
    {
        Session::requireRole('admin');
        $id = (int)$request->param('id');
        $pdo = db();
        $stmt = $pdo->prepare(
            "UPDATE ab_tests SET name=?, page_id=?, type=?, variant_a=?, variant_b=?, selector=?, goal=?, goal_selector=? WHERE id=?"
        );
        $stmt->execute([
            trim($_POST['name'] ?? ''), ((int)($_POST['page_id'] ?? 0)) ?: null,
            $_POST['type'] ?? 'headline', $_POST['variant_a'] ?? '', $_POST['variant_b'] ?? '',
            $_POST['selector'] ?? '', $_POST['goal'] ?? 'click', $_POST['goal_selector'] ?? '', $id
        ]);
        Session::flash('success', 'Test updated.');
        \Core\Response::redirect('/admin/ab-testing');
    }

    public function toggle(Request $request): void
    {
        Session::requireRole('admin');
        $id = (int)$request->param('id');
        $pdo = db();
        $stmt = $pdo->prepare("SELECT status FROM ab_tests WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();
        $new = $current === 'running' ? 'paused' : 'running';
        $pdo->prepare("UPDATE ab_tests SET status = ?, ended_at = IF(? != 'running', NOW(), NULL) WHERE id = ?")
            ->execute([$new, $new, $id]);
        Session::flash('success', "Test " . ($new === 'running' ? 'resumed' : 'paused') . ".");
        \Core\Response::redirect('/admin/ab-testing');
    }

    public function complete(Request $request): void
    {
        Session::requireRole('admin');
        $id = (int)$request->param('id');
        $winner = in_array($_POST['winner'] ?? '', ['a', 'b']) ? $_POST['winner'] : 'none';
        $pdo = db();
        $pdo->prepare("UPDATE ab_tests SET status='completed', winner=?, ended_at=NOW() WHERE id=?")->execute([$winner, $id]);
        Session::flash('success', 'Test completed. Winner: Variant ' . strtoupper($winner));
        \Core\Response::redirect('/admin/ab-testing');
    }

    public function delete(Request $request): void
    {
        Session::requireRole('admin');
        $id = (int)$request->param('id');
        db()->prepare("DELETE FROM ab_tests WHERE id = ?")->execute([$id]);
        Session::flash('success', 'Test deleted.');
        \Core\Response::redirect('/admin/ab-testing');
    }

    public function results(Request $request): void
    {
        Session::requireRole('admin');
        $id = (int)$request->param('id');
        $pdo = db();
        $stmt = $pdo->prepare("SELECT t.*, p.title as page_title, p.slug as page_slug FROM ab_tests t LEFT JOIN pages p ON p.id = t.page_id WHERE t.id = ?");
        $stmt->execute([$id]);
        $test = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$test) { Session::flash('error', 'Test not found.'); \Core\Response::redirect('/admin/ab-testing'); }

        // Calculate detailed stats
        $test['rate_a'] = ($test['views_a'] > 0) ? round($test['conversions_a'] / $test['views_a'] * 100, 2) : 0;
        $test['rate_b'] = ($test['views_b'] > 0) ? round($test['conversions_b'] / $test['views_b'] * 100, 2) : 0;
        $test['lift'] = ($test['rate_a'] > 0) ? round(($test['rate_b'] - $test['rate_a']) / $test['rate_a'] * 100, 1) : 0;
        $test['total_views'] = $test['views_a'] + $test['views_b'];
        $test['total_conversions'] = $test['conversions_a'] + $test['conversions_b'];
        $test['significant'] = ($test['views_a'] >= 100 && $test['views_b'] >= 100);

        render('admin/ab-testing/results', ['test' => $test]);
    }

    // ─── API ENDPOINTS (public) ───

    /**
     * GET /api/ab-tests — returns active tests for frontend widget
     */
    public function apiList(Request $request): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: public, max-age=60');

        $pdo = db();
        $tests = $pdo->query(
            "SELECT id, type, selector, variant_a, variant_b, goal, goal_selector, page_id
             FROM ab_tests WHERE status = 'running'"
        )->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode(['tests' => $tests]);
    }

    /**
     * POST /api/ab-track — track view or conversion
     */
    public function apiTrack(Request $request): void
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $testId = (int)($data['test_id'] ?? 0);
        $variant = ($data['variant'] ?? '') === 'b' ? 'b' : 'a';
        $type = ($data['type'] ?? '') === 'conversion' ? 'conversion' : 'view';

        if (!$testId) {
            echo json_encode(['ok' => false]);
            return;
        }

        $col = $type === 'conversion' ? "conversions_{$variant}" : "views_{$variant}";
        db()->prepare("UPDATE ab_tests SET {$col} = {$col} + 1 WHERE id = ? AND status = 'running'")
            ->execute([$testId]);

        if ($type === 'conversion' && function_exists('cms_event')) {
            cms_event('ab.conversion', ['test_id' => $testId, 'variant' => $variant]);
        }

        echo json_encode(['ok' => true]);
    }
}
