<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

class PersonalizationTestData {
    public static function generate($pdo) {
        try {
            $pdo->beginTransaction();

            // Generate test users if needed
            $users = $pdo->query("SELECT id FROM auth_users LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($users)) {
                throw new Exception("No users found in auth_users table");
            }

            // Insert user preferences
            $prefs = [
                ['theme', ['darkMode' => true, 'fontSize' => 'medium']],
                ['notifications', ['email' => true, 'push' => false]],
                ['language', 'en_US'],
                ['timezone', 'UTC'],
                ['layout', 'compact']
            ];
            
            foreach ($users as $userId) {
                foreach ($prefs as $pref) {
                    $stmt = $pdo->prepare(
                        "INSERT INTO user_preferences 
                        (user_id, preference_key, preference_value) 
                        VALUES (?, ?, ?::jsonb)"
                    );
                    $stmt->execute([
                        $userId, 
                        $pref[0],
                        is_array($pref[1]) ? json_encode($pref[1]) : json_encode(['value' => $pref[1]])
                    ]);
                }
            }

            // Insert content recommendations
            $contentIds = ['article-001', 'article-002', 'video-001', 'tutorial-001'];
            foreach ($users as $userId) {
                foreach ($contentIds as $contentId) {
                    $score = mt_rand(500, 1000) / 1000; // 0.5 to 1.0
                    $stmt = $pdo->prepare(
                        "INSERT INTO content_recommendations
                        (user_id, content_id, recommendation_score, recommendation_reason, expires_at)
                        VALUES (?, ?, ?, ?, NOW() + INTERVAL '7 days')"
                    );
                    $stmt->execute([
                        $userId,
                        $contentId,
                        $score,
                        "Recommended based on user activity"
                    ]);
                }
            }

            // Insert metrics
            $metricTypes = ['click_rate', 'engagement', 'conversion', 'time_spent'];
            for ($i = 0; $i < 100; $i++) {
                $type = $metricTypes[array_rand($metricTypes)];
                $value = mt_rand(100, 10000) / 100;
                $userId = $users[array_rand($users)];
                $contentId = $contentIds[array_rand($contentIds)];

                $stmt = $pdo->prepare(
                    "INSERT INTO personalization_metrics
                    (metric_type, metric_value, user_id, content_id, session_id)
                    VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $type,
                    $value,
                    $userId,
                    $contentId,
                    'session-' . bin2hex(random_bytes(8))
                ]);
            }

            $pdo->commit();
            return ['status' => 'success', 'message' => 'Test data generated successfully'];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
