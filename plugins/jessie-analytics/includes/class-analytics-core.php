<?php
namespace Plugins\JessieAnalytics;

/**
 * Analytics Core — event tracking, reporting, goals, AI insights
 */
class AnalyticsCore {
    private \PDO $pdo;
    private int $userId;

    public function __construct(int $userId) {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 3)); }
        require_once CMS_ROOT . '/db.php';
        $this->pdo = \core\Database::connection();
        $this->userId = $userId;
    }

    // ── Events ──
    public function trackEvent(array $d): int {
        $stmt = $this->pdo->prepare("INSERT INTO analytics_events (user_id, event_type, event_source, page_url, referrer, session_id, ip_hash, user_agent, country, device, metadata_json) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $this->userId, $d['event_type']??'pageview', $d['event_source']??'web',
            $d['page_url']??'', $d['referrer']??'', $d['session_id']??'',
            $d['ip_hash']??'', $d['user_agent']??'', $d['country']??'', $d['device']??'',
            isset($d['metadata']) ? json_encode($d['metadata']) : null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getEvents(string $startDate, string $endDate, ?string $type = null, int $limit = 100): array {
        $sql = "SELECT * FROM analytics_events WHERE user_id = ? AND created_at BETWEEN ? AND ?";
        $params = [$this->userId, $startDate, $endDate];
        if ($type) { $sql .= " AND event_type = ?"; $params[] = $type; }
        $sql .= " ORDER BY created_at DESC LIMIT ?"; $params[] = $limit;
        $stmt = $this->pdo->prepare($sql); $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Aggregated Stats ──
    public function getOverview(string $startDate, string $endDate): array {
        $params = [$this->userId, $startDate, $endDate];
        $pageviews = $this->pdo->prepare("SELECT COUNT(*) FROM analytics_events WHERE user_id=? AND created_at BETWEEN ? AND ? AND event_type='pageview'");
        $pageviews->execute($params);
        $sessions = $this->pdo->prepare("SELECT COUNT(DISTINCT session_id) FROM analytics_events WHERE user_id=? AND created_at BETWEEN ? AND ? AND session_id != ''");
        $sessions->execute($params);
        $conversions = $this->pdo->prepare("SELECT COUNT(*) FROM analytics_events WHERE user_id=? AND created_at BETWEEN ? AND ? AND event_type='conversion'");
        $conversions->execute($params);
        $events = $this->pdo->prepare("SELECT COUNT(*) FROM analytics_events WHERE user_id=? AND created_at BETWEEN ? AND ?");
        $events->execute($params);
        return ['pageviews'=>(int)$pageviews->fetchColumn(),'sessions'=>(int)$sessions->fetchColumn(),'conversions'=>(int)$conversions->fetchColumn(),'total_events'=>(int)$events->fetchColumn()];
    }

    public function getTopPages(string $startDate, string $endDate, int $limit = 10): array {
        $stmt = $this->pdo->prepare("SELECT page_url, COUNT(*) as views FROM analytics_events WHERE user_id=? AND created_at BETWEEN ? AND ? AND event_type='pageview' AND page_url != '' GROUP BY page_url ORDER BY views DESC LIMIT ?");
        $stmt->execute([$this->userId, $startDate, $endDate, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTopReferrers(string $startDate, string $endDate, int $limit = 10): array {
        $stmt = $this->pdo->prepare("SELECT referrer, COUNT(*) as visits FROM analytics_events WHERE user_id=? AND created_at BETWEEN ? AND ? AND referrer != '' GROUP BY referrer ORDER BY visits DESC LIMIT ?");
        $stmt->execute([$this->userId, $startDate, $endDate, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getDeviceBreakdown(string $startDate, string $endDate): array {
        $stmt = $this->pdo->prepare("SELECT device, COUNT(*) as cnt FROM analytics_events WHERE user_id=? AND created_at BETWEEN ? AND ? AND device != '' GROUP BY device ORDER BY cnt DESC");
        $stmt->execute([$this->userId, $startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getDailyTrend(string $startDate, string $endDate): array {
        $stmt = $this->pdo->prepare("SELECT DATE(created_at) as day, COUNT(*) as events, SUM(event_type='pageview') as pageviews, COUNT(DISTINCT session_id) as sessions FROM analytics_events WHERE user_id=? AND created_at BETWEEN ? AND ? GROUP BY day ORDER BY day");
        $stmt->execute([$this->userId, $startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Goals ──
    public function getGoals(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM analytics_goals WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function createGoal(array $d): int {
        $stmt = $this->pdo->prepare("INSERT INTO analytics_goals (user_id, name, event_type, target_value, period) VALUES (?,?,?,?,?)");
        $stmt->execute([$this->userId, $d['name']??'', $d['event_type']??'conversion', (int)($d['target_value']??0), $d['period']??'monthly']);
        return (int)$this->pdo->lastInsertId();
    }

    // ── Reports ──
    public function getReports(): array {
        $stmt = $this->pdo->prepare("SELECT id, name, report_type, status, generated_at, created_at FROM analytics_reports WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function createReport(array $d): int {
        $stmt = $this->pdo->prepare("INSERT INTO analytics_reports (user_id, name, report_type, config_json) VALUES (?,?,?,?)");
        $stmt->execute([$this->userId, $d['name']??'Report', $d['type']??'custom', json_encode($d['config']??[])]);
        return (int)$this->pdo->lastInsertId();
    }

    // ── AI Insights ──
    public function generateInsights(string $startDate, string $endDate): array {
        $overview = $this->getOverview($startDate, $endDate);
        $topPages = $this->getTopPages($startDate, $endDate, 5);
        $referrers = $this->getTopReferrers($startDate, $endDate, 5);
        require_once CMS_ROOT . '/core/ai_content.php';
        $context = "Analytics data for period {$startDate} to {$endDate}:\n";
        $context .= "Pageviews: {$overview['pageviews']}, Sessions: {$overview['sessions']}, Conversions: {$overview['conversions']}\n";
        $context .= "Top pages: " . implode(', ', array_column($topPages, 'page_url')) . "\n";
        $context .= "Top referrers: " . implode(', ', array_column($referrers, 'referrer')) . "\n";
        $prompt = "You are a web analytics expert. Analyze this data and provide 3-5 actionable insights:\n\n{$context}\n\nReturn JSON: {\"insights\":[{\"title\":\"...\",\"description\":\"...\",\"action\":\"...\",\"priority\":\"high|medium|low\"}]}";
        $result = ai_content_generate(['topic' => $prompt]);
        if (!$result['ok']) return ['success' => false, 'error' => $result['error'] ?? 'AI failed'];
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $result['content'] ?? '');
        $raw = preg_replace('/\s*```\s*$/', '', $raw);
        $parsed = json_decode(trim($raw), true);
        return ['success' => true, 'insights' => $parsed['insights'] ?? [], 'overview' => $overview];
    }

    // ── Dashboard Stats (admin) ──
    public function getGlobalStats(): array {
        $events = (int)$this->pdo->query("SELECT COUNT(*) FROM analytics_events")->fetchColumn();
        $today = (int)$this->pdo->query("SELECT COUNT(*) FROM analytics_events WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $goals = (int)$this->pdo->query("SELECT COUNT(*) FROM analytics_goals")->fetchColumn();
        $reports = (int)$this->pdo->query("SELECT COUNT(*) FROM analytics_reports")->fetchColumn();
        return ['total_events' => $events, 'today_events' => $today, 'goals' => $goals, 'reports' => $reports];
    }
}
