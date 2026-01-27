<?php
/**
 * Core Email Sender
 * Supports PHP mail() and SMTP modes
 * No framework dependencies, pure PHP
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/settings_email.php';

if (!function_exists('email_send')) {
    /**
     * Send email via PHP mail() or SMTP
     * @param array $msg Message array with keys: to, subject, body, from_name, from_email, reply_to
     * @return bool Success status
     */
    function email_send(array $msg): bool
    {
        // Load email settings
        $s = email_settings_get();

        // Apply defaults from settings
        $to = trim($msg['to'] ?? '');
        $subject = trim($msg['subject'] ?? '');
        $body = $msg['body'] ?? '';
        $fromEmail = !empty($msg['from_email']) ? trim($msg['from_email']) : ($s['from_email'] ?? '');
        $fromName = !empty($msg['from_name']) ? trim($msg['from_name']) : ($s['from_name'] ?? '');
        $replyTo = !empty($msg['reply_to']) ? trim($msg['reply_to']) : ($s['reply_to_email'] ?? '');

        // Validate required fields
        if ($to === '' || $subject === '' || $fromEmail === '') {
            email_log('validate_failed', $to, $subject, false, 'Missing required fields');
            return false;
        }

        // Validate email addresses
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            email_log('validate_failed', $to, $subject, false, 'Invalid to address');
            return false;
        }
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            email_log('validate_failed', $to, $subject, false, 'Invalid from address');
            return false;
        }
        if ($replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            email_log('validate_failed', $to, $subject, false, 'Invalid reply-to address');
            return false;
        }

        // Determine send mode
        $mode = ($s['send_mode'] === 'phpmail') ? 'phpmail' : 'smtp';

        // Build headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        if ($fromName !== '') {
            $headers .= "From: \"" . str_replace('"', '', $fromName) . "\" <{$fromEmail}>\r\n";
        } else {
            $headers .= "From: {$fromEmail}\r\n";
        }
        if ($replyTo !== '') {
            $headers .= "Reply-To: {$replyTo}\r\n";
        }

        // Send via selected mode
        if ($mode === 'phpmail') {
            $result = @mail($to, $subject, $body, $headers);
            email_log($mode, $to, $subject, $result);
            return $result;
        } else {
            // SMTP mode
            $result = email_send_smtp($to, $fromEmail, $subject, $body, $headers, $s);
            email_log($mode, $to, $subject, $result);
            return $result;
        }
    }
}

if (!function_exists('email_send_smtp')) {
    /**
     * Send email via SMTP using fsockopen
     * @param string $to Recipient email
     * @param string $from Sender email
     * @param string $subject Subject line
     * @param string $body Message body
     * @param string $headers Additional headers
     * @param array $s Settings array
     * @return bool Success status
     */
    function email_send_smtp(string $to, string $from, string $subject, string $body, string $headers, array $s): bool
    {
        // Get SMTP settings from root/config.php constants ONLY
        $host = defined('SMTP_HOST') ? SMTP_HOST : null;
        $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 25;
        $auth = defined('SMTP_AUTH') ? (bool)SMTP_AUTH : false;
        $user = defined('SMTP_USER') ? SMTP_USER : (defined('SMTP_USERNAME') ? SMTP_USERNAME : null);
        $pass = defined('SMTP_PASS') ? SMTP_PASS : (defined('SMTP_PASSWORD') ? SMTP_PASSWORD : null);
        $timeout = 30;

        // Validate SMTP configuration
        if (empty($host)) {
            email_log('smtp', $to, $subject, false, 'smtp_not_configured');
            return false;
        }

        if ($auth && (empty($user) || empty($pass))) {
            email_log('smtp', $to, $subject, false, 'smtp_auth_incomplete');
            return false;
        }

        // Connect to SMTP server
        $errno = 0;
        $errstr = '';
        $sock = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if (!$sock) {
            email_log('smtp', $to, $subject, false, 'smtp_connect_failed');
            return false;
        }

        // Read server greeting
        $response = fgets($sock, 512);
        if (substr($response, 0, 3) !== '220') {
            fclose($sock);
            email_log('smtp', $to, $subject, false, 'smtp_connect_failed');
            return false;
        }

        // EHLO command
        fputs($sock, "EHLO localhost\r\n");
        $response = fgets($sock, 512);
        if (substr($response, 0, 3) !== '250') {
            fclose($sock);
            email_log('smtp', $to, $subject, false, 'smtp_send_failed');
            return false;
        }

        // AUTH LOGIN if enabled
        if ($auth && !empty($user) && !empty($pass)) {
            fputs($sock, "AUTH LOGIN\r\n");
            $response = fgets($sock, 512);
            if (substr($response, 0, 3) !== '334') {
                fclose($sock);
                email_log('smtp', $to, $subject, false, 'smtp_send_failed');
                return false;
            }

            fputs($sock, base64_encode($user) . "\r\n");
            $response = fgets($sock, 512);
            if (substr($response, 0, 3) !== '334') {
                fclose($sock);
                email_log('smtp', $to, $subject, false, 'smtp_send_failed');
                return false;
            }

            fputs($sock, base64_encode($pass) . "\r\n");
            $response = fgets($sock, 512);
            if (substr($response, 0, 3) !== '235') {
                fclose($sock);
                email_log('smtp', $to, $subject, false, 'smtp_send_failed');
                return false;
            }
        }

        // MAIL FROM
        fputs($sock, "MAIL FROM: <{$from}>\r\n");
        $response = fgets($sock, 512);
        if (substr($response, 0, 3) !== '250') {
            fclose($sock);
            email_log('smtp', $to, $subject, false, 'smtp_send_failed');
            return false;
        }

        // RCPT TO
        fputs($sock, "RCPT TO: <{$to}>\r\n");
        $response = fgets($sock, 512);
        if (substr($response, 0, 3) !== '250') {
            fclose($sock);
            email_log('smtp', $to, $subject, false, 'smtp_send_failed');
            return false;
        }

        // DATA
        fputs($sock, "DATA\r\n");
        $response = fgets($sock, 512);
        if (substr($response, 0, 3) !== '354') {
            fclose($sock);
            email_log('smtp', $to, $subject, false, 'smtp_send_failed');
            return false;
        }

        // Send headers and body
        $message = $headers;
        $message .= "Subject: {$subject}\r\n";
        $message .= "\r\n";
        $message .= $body;
        $message .= "\r\n.\r\n";

        fputs($sock, $message);
        $response = fgets($sock, 512);
        if (substr($response, 0, 3) !== '250') {
            fclose($sock);
            email_log('smtp', $to, $subject, false, 'smtp_send_failed');
            return false;
        }

        // QUIT
        fputs($sock, "QUIT\r\n");
        fclose($sock);

        return true;
    }
}

if (!function_exists('email_log')) {
    /**
     * Log email sending events
     * @param string $mode Send mode (phpmail or smtp)
     * @param string $to Recipient
     * @param string $subject Subject line
     * @param bool $ok Success status
     * @param string $error Optional error message
     * @return void
     */
    function email_log(string $mode, string $to, string $subject, bool $ok, string $error = ''): void
    {
        $logPath = CMS_ROOT . '/logs/email.log';
        $logDir = dirname($logPath);

        // Ensure log directory exists
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $entry = [
            'ts' => date('c'),
            'mode' => $mode,
            'to' => $to,
            'subject' => $subject,
            'ok' => $ok
        ];

        if ($error !== '') {
            $entry['error'] = $error;
        }

        $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
    }
}
