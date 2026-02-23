<?php
declare(strict_types=1);

namespace Admin;

use Core\Request;
use Core\Session;

class ContentCalendarController
{
    public function index(Request $request): void
    {
        Session::requireRole('editor');
        $pdo = db();

        $month = (int)($_GET['month'] ?? date('n'));
        $year = (int)($_GET['year'] ?? date('Y'));
        if ($month < 1) { $month = 12; $year--; }
        if ($month > 12) { $month = 1; $year++; }

        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = (int)date('t', $firstDay);
        $startWeekday = (int)date('w', $firstDay); // 0=Sun

        // Fetch articles scheduled/published this month
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $stmt = $pdo->prepare(
            "SELECT id, title, status, created_at, updated_at,
                    COALESCE(DATE(published_at), DATE(created_at)) as cal_date
             FROM articles 
             WHERE (DATE(created_at) BETWEEN ? AND ?) 
                OR (published_at IS NOT NULL AND DATE(published_at) BETWEEN ? AND ?)
             ORDER BY cal_date"
        );
        $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Also fetch pages modified this month
        $stmt = $pdo->prepare(
            "SELECT id, title, status, created_at, updated_at, DATE(created_at) as cal_date
             FROM pages 
             WHERE DATE(created_at) BETWEEN ? AND ?
             ORDER BY cal_date"
        );
        $stmt->execute([$startDate, $endDate]);
        $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group by date
        $events = [];
        foreach ($articles as $a) {
            $day = (int)date('j', strtotime($a['cal_date']));
            $events[$day][] = [
                'type' => 'article',
                'id' => $a['id'],
                'title' => $a['title'],
                'status' => $a['status'],
                'url' => '/admin/articles/' . $a['id'] . '/edit',
            ];
        }
        foreach ($pages as $p) {
            $day = (int)date('j', strtotime($p['cal_date']));
            $events[$day][] = [
                'type' => 'page',
                'id' => $p['id'],
                'title' => $p['title'],
                'status' => $p['status'],
                'url' => '/admin/pages/' . $p['id'] . '/edit',
            ];
        }

        // Fetch social posts if table exists
        try {
            $stmt = $pdo->prepare(
                "SELECT id, platform, content, status, scheduled_at, published_at,
                        COALESCE(DATE(scheduled_at), DATE(created_at)) as cal_date
                 FROM social_posts
                 WHERE (DATE(scheduled_at) BETWEEN ? AND ?) OR (DATE(created_at) BETWEEN ? AND ?)
                 ORDER BY cal_date"
            );
            $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $sp) {
                $day = (int)date('j', strtotime($sp['cal_date']));
                $events[$day][] = [
                    'type' => 'social',
                    'id' => $sp['id'],
                    'title' => mb_substr($sp['content'], 0, 40) . '…',
                    'status' => $sp['status'],
                    'platform' => $sp['platform'],
                    'url' => '/admin/social-media',
                ];
            }
        } catch (\Throwable $e) {
            // social_posts table may not exist yet
        }

        // Fetch products created/scheduled this month
        try {
            $stmt = $pdo->prepare(
                "SELECT id, name, slug, status, price, created_at, DATE(created_at) as cal_date
                 FROM products
                 WHERE DATE(created_at) BETWEEN ? AND ?
                 ORDER BY created_at"
            );
            $stmt->execute([$startDate, $endDate]);
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $prod) {
                $day = (int)date('j', strtotime($prod['cal_date']));
                $events[$day][] = [
                    'type' => 'product',
                    'id' => $prod['id'],
                    'title' => '🛒 ' . $prod['name'],
                    'status' => $prod['status'],
                    'url' => '/admin/shop/products/' . $prod['id'] . '/edit',
                ];
            }
        } catch (\Throwable $e) {
            // products table may not exist
        }

        // Stats
        $stats = [
            'articles_this_month' => count($articles),
            'pages_this_month' => count($pages),
            'total_events' => array_sum(array_map('count', $events)),
        ];

        render('admin/content-calendar/index', [
            'month' => $month,
            'year' => $year,
            'daysInMonth' => $daysInMonth,
            'startWeekday' => $startWeekday,
            'events' => $events,
            'stats' => $stats,
        ]);
    }
}
