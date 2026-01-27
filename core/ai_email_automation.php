<?php

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

function ai_email_automation_config_path()
{
    return CMS_ROOT . '/config/ai_email_automation.json';
}

function ai_email_automation_load()
{
    $path = ai_email_automation_config_path();

    if (!is_readable($path)) {
        return ['sequences' => []];
    }

    $content = file_get_contents($path);
    if ($content === false) {
        return ['sequences' => []];
    }

    $data = json_decode($content, true);
    if (!is_array($data)) {
        return ['sequences' => []];
    }

    if (!isset($data['sequences']) || !is_array($data['sequences'])) {
        $data['sequences'] = [];
    }

    $normalized = [];
    foreach ($data['sequences'] as $seq) {
        if (!is_array($seq)) {
            continue;
        }

        $id = isset($seq['id']) ? trim((string)$seq['id']) : '';
        if ($id === '') {
            continue;
        }

        $normalizedSeq = [
            'id' => $id,
            'event_key' => isset($seq['event_key']) ? (string)$seq['event_key'] : '',
            'name' => isset($seq['name']) ? (string)$seq['name'] : '',
            'goal' => isset($seq['goal']) ? (string)$seq['goal'] : '',
            'audience' => isset($seq['audience']) ? (string)$seq['audience'] : '',
            'language' => isset($seq['language']) ? (string)$seq['language'] : '',
            'tone' => isset($seq['tone']) ? (string)$seq['tone'] : '',
            'steps' => []
        ];

        if (isset($seq['steps']) && is_array($seq['steps'])) {
            foreach ($seq['steps'] as $step) {
                if (!is_array($step)) {
                    continue;
                }

                $normalizedSeq['steps'][] = [
                    'offset_days' => isset($step['offset_days']) ? (int)$step['offset_days'] : 0,
                    'subject' => isset($step['subject']) ? (string)$step['subject'] : '',
                    'preview' => isset($step['preview']) ? (string)$step['preview'] : '',
                    'html' => isset($step['html']) ? (string)$step['html'] : '',
                    'text' => isset($step['text']) ? (string)$step['text'] : '',
                    'cta' => isset($step['cta']) ? (string)$step['cta'] : ''
                ];
            }
        }

        $normalized[] = $normalizedSeq;
    }

    return ['sequences' => $normalized];
}

function ai_email_automation_get_sequence($id)
{
    $id = trim((string)$id);
    if ($id === '') {
        return null;
    }

    $config = ai_email_automation_load();

    foreach ($config['sequences'] as $seq) {
        if ($seq['id'] === $id) {
            return $seq;
        }
    }

    return null;
}

function ai_email_automation_plan_for_event($eventKey, $baseDate, $recipientEmail = null)
{
    $eventKey = trim((string)$eventKey);
    if ($eventKey === '') {
        return [
            'event_key' => '',
            'base_date' => '',
            'recipient_email' => null,
            'sequences' => []
        ];
    }

    if ($baseDate instanceof DateTimeInterface) {
        $baseDateObj = $baseDate;
        $baseDateStr = $baseDateObj->format('Y-m-d');
    } elseif (is_string($baseDate)) {
        $baseDateStr = trim($baseDate);
        try {
            $baseDateObj = new DateTime($baseDateStr);
            $baseDateStr = $baseDateObj->format('Y-m-d');
        } catch (Exception $e) {
            $baseDateObj = new DateTime();
            $baseDateStr = $baseDateObj->format('Y-m-d');
        }
    } else {
        $baseDateObj = new DateTime();
        $baseDateStr = $baseDateObj->format('Y-m-d');
    }

    $recipientEmail = $recipientEmail !== null ? trim((string)$recipientEmail) : null;
    if ($recipientEmail === '') {
        $recipientEmail = null;
    }

    $config = ai_email_automation_load();

    $matchingSequences = [];
    foreach ($config['sequences'] as $seq) {
        if ($seq['event_key'] === $eventKey) {
            $matchingSequences[] = $seq;
        }
    }

    $plannedSequences = [];
    foreach ($matchingSequences as $seq) {
        $plannedSteps = [];
        $stepIndex = 1;

        foreach ($seq['steps'] as $step) {
            $offsetDays = (int)$step['offset_days'];

            $scheduledDate = clone $baseDateObj;
            $scheduledDate->modify(sprintf('%+d days', $offsetDays));

            $plannedSteps[] = [
                'step_index' => $stepIndex,
                'offset_days' => $offsetDays,
                'scheduled_date' => $scheduledDate->format('Y-m-d'),
                'subject' => $step['subject'],
                'preview' => $step['preview'],
                'html' => $step['html'],
                'text' => $step['text'],
                'cta' => $step['cta']
            ];

            $stepIndex++;
        }

        $plannedSequences[] = [
            'id' => $seq['id'],
            'name' => $seq['name'],
            'language' => $seq['language'],
            'tone' => $seq['tone'],
            'steps' => $plannedSteps
        ];
    }

    return [
        'event_key' => $eventKey,
        'base_date' => $baseDateStr,
        'recipient_email' => $recipientEmail,
        'sequences' => $plannedSequences
    ];
}

if (!function_exists('ai_email_automation_schedule_from_json')) {
    function ai_email_automation_schedule_from_json(string $json, string $recipientEmail, array $options = []): array
    {
        $recipientEmail = trim($recipientEmail);
        if ($recipientEmail === '' || strpos($recipientEmail, '@') === false) {
            return ['ok' => false, 'error' => 'Invalid recipient email.'];
        }

        $data = @json_decode($json, true);
        if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
            return ['ok' => false, 'error' => 'Invalid campaign JSON.'];
        }

        if (!isset($data['emails']) || !is_array($data['emails']) || count($data['emails']) === 0) {
            return ['ok' => false, 'error' => 'Campaign contains no emails.'];
        }

        require_once CMS_ROOT . '/core/Database.php';

        $fromEmail = isset($options['from_email']) && trim($options['from_email']) !== ''
            ? trim($options['from_email'])
            : (defined('DEFAULT_FROM_EMAIL') ? DEFAULT_FROM_EMAIL : 'noreply@localhost');

        $startTime = time();
        if (isset($options['start_time'])) {
            if (is_numeric($options['start_time'])) {
                $startTime = (int)$options['start_time'];
            } else {
                $parsed = @strtotime($options['start_time']);
                if ($parsed !== false) {
                    $startTime = $parsed;
                }
            }
        }

        $intervalMinutes = isset($options['interval_minutes']) ? (int)$options['interval_minutes'] : 1440;
        if ($intervalMinutes < 0) {
            $intervalMinutes = 0;
        } elseif ($intervalMinutes > 10080) {
            $intervalMinutes = 10080;
        }

        $subjectPrefix = isset($options['subject_prefix']) ? (string)$options['subject_prefix'] : '';

        try {
            $db = \core\Database::connection();
            $db->beginTransaction();

            $queued = 0;
            $firstScheduledAt = null;
            $lastScheduledAt = null;

            $checkStmt = $db->query("SHOW COLUMNS FROM email_queue LIKE 'scheduled_at'");
            $hasScheduledAt = $checkStmt->fetch() !== false;

            foreach ($data['emails'] as $index => $email) {
                if (!isset($email['subject']) || !isset($email['html_body'])) {
                    $db->rollBack();
                    return ['ok' => false, 'error' => 'One or more emails are missing required fields.'];
                }

                $scheduledAt = $startTime + ($index * $intervalMinutes * 60);
                if ($firstScheduledAt === null) {
                    $firstScheduledAt = $scheduledAt;
                }
                $lastScheduledAt = $scheduledAt;

                $subject = $subjectPrefix . $email['subject'];
                $htmlBody = isset($email['html_body']) ? $email['html_body'] : '';
                $textBody = isset($email['text_body']) ? $email['text_body'] : '';
                $body = $htmlBody !== '' ? $htmlBody : $textBody;

                if ($hasScheduledAt) {
                    $stmt = $db->prepare(
                        "INSERT INTO email_queue (to_email, from_email, subject, body, status, scheduled_at, created_at)
                         VALUES (:to_email, :from_email, :subject, :body, 'pending', :scheduled_at, NOW())"
                    );
                    $stmt->execute([
                        ':to_email' => $recipientEmail,
                        ':from_email' => $fromEmail,
                        ':subject' => $subject,
                        ':body' => $body,
                        ':scheduled_at' => date('Y-m-d H:i:s', $scheduledAt)
                    ]);
                } else {
                    $stmt = $db->prepare(
                        "INSERT INTO email_queue (to_email, from_email, subject, body, status, created_at)
                         VALUES (:to_email, :from_email, :subject, :body, 'pending', :created_at)"
                    );
                    $stmt->execute([
                        ':to_email' => $recipientEmail,
                        ':from_email' => $fromEmail,
                        ':subject' => $subject,
                        ':body' => $body,
                        ':created_at' => date('Y-m-d H:i:s', $scheduledAt)
                    ]);
                }

                $queued++;
            }

            $db->commit();

            return [
                'ok' => true,
                'queued' => $queued,
                'start_time' => $firstScheduledAt,
                'end_time' => $lastScheduledAt,
                'recipient' => $recipientEmail
            ];

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log('ai_email_automation_schedule_from_json: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Failed to queue campaign emails.'];
        }
    }
}
