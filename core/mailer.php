<?php
declare(strict_types=1);

/**
 * CMS Mailer — sends email via fsockopen SMTP or PHP mail() fallback
 *
 * Settings are read from the `settings` table (key/value):
 *   smtp_host, smtp_port, smtp_user, smtp_pass,
 *   smtp_encryption (tls|ssl|none), smtp_from_email, smtp_from_name
 *
 * @package JessieCMS
 * @since 2026-02-23
 */

if (!function_exists('cms_send_email')) {

    /**
     * Send an email using SMTP (fsockopen) if configured, otherwise PHP mail().
     *
     * @param string $to        Recipient email
     * @param string $subject   Subject line
     * @param string $body      Email body (plain text)
     * @param array  $headers   Extra headers ['Header-Name' => 'value']
     * @return bool
     */
    function cms_send_email(string $to, string $subject, string $body, array $headers = []): bool
    {
        $smtp = _cms_mailer_load_settings();

        // If SMTP is configured, use fsockopen SMTP
        if (!empty($smtp['smtp_host'])) {
            return _cms_mailer_smtp($to, $subject, $body, $headers, $smtp);
        }

        // Fallback to PHP mail()
        return _cms_mailer_phpmail($to, $subject, $body, $headers, $smtp);
    }

    /**
     * Load SMTP settings from the settings table.
     */
    function _cms_mailer_load_settings(): array
    {
        $defaults = [
            'smtp_host'       => '',
            'smtp_port'       => '587',
            'smtp_user'       => '',
            'smtp_pass'       => '',
            'smtp_encryption' => 'tls',
            'smtp_from_email' => '',
            'smtp_from_name'  => '',
        ];

        try {
            $pdo = \core\Database::connection();
            $keys = array_keys($defaults);
            $placeholders = implode(',', array_fill(0, count($keys), '?'));

            $stmt = $pdo->prepare(
                "SELECT `key`, `value` FROM settings WHERE `key` IN ($placeholders)"
            );
            $stmt->execute($keys);

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (array_key_exists($row['key'], $defaults)) {
                    $defaults[$row['key']] = $row['value'] ?? '';
                }
            }
        } catch (\Throwable $e) {
            error_log('cms_send_email: failed to load settings — ' . $e->getMessage());
        }

        return $defaults;
    }

    /**
     * Send via PHP mail().
     */
    function _cms_mailer_phpmail(string $to, string $subject, string $body, array $extra, array $cfg): bool
    {
        $fromName  = $cfg['smtp_from_name'] ?: 'CMS';
        $fromEmail = $cfg['smtp_from_email'] ?: 'noreply@localhost';

        $hdrs = [];
        $hdrs[] = "From: {$fromName} <{$fromEmail}>";
        $hdrs[] = "MIME-Version: 1.0";
        $hdrs[] = "Content-Type: text/plain; charset=UTF-8";

        foreach ($extra as $k => $v) {
            $hdrs[] = "{$k}: {$v}";
        }

        return @mail($to, $subject, $body, implode("\r\n", $hdrs));
    }

    /**
     * Send via raw SMTP using fsockopen + AUTH LOGIN.
     */
    function _cms_mailer_smtp(string $to, string $subject, string $body, array $extra, array $cfg): bool
    {
        $host       = $cfg['smtp_host'];
        $port       = (int)($cfg['smtp_port'] ?: 587);
        $user       = $cfg['smtp_user'];
        $pass       = $cfg['smtp_pass'];
        $encryption = $cfg['smtp_encryption'] ?: 'tls';
        $fromEmail  = $cfg['smtp_from_email'] ?: $user;
        $fromName   = $cfg['smtp_from_name'] ?: 'CMS';

        $log = function (string $msg): void {
            error_log('[CMS_MAILER] ' . $msg);
        };

        // Build the message
        $message  = "From: {$fromName} <{$fromEmail}>\r\n";
        $message .= "To: {$to}\r\n";
        $message .= "Subject: {$subject}\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Date: " . date('r') . "\r\n";
        $message .= "Message-ID: <" . bin2hex(random_bytes(16)) . "@" . gethostname() . ">\r\n";

        foreach ($extra as $k => $v) {
            $message .= "{$k}: {$v}\r\n";
        }

        $message .= "\r\n" . $body;

        // Connect
        $errno  = 0;
        $errstr = '';
        $prefix = ($encryption === 'ssl') ? 'ssl://' : '';
        $timeout = 15;

        $sock = @fsockopen($prefix . $host, $port, $errno, $errstr, $timeout);
        if (!$sock) {
            $log("Connection failed: {$errstr} ({$errno})");
            return false;
        }

        stream_set_timeout($sock, 30);

        // Helper to read a response line
        $read = function () use ($sock, $log): string {
            $response = '';
            while ($line = fgets($sock, 512)) {
                $response .= $line;
                // If the 4th character is a space, this is the last line
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $response;
        };

        // Helper to send a command and check response code
        $send = function (string $cmd, int $expectCode) use ($sock, $read, $log): bool {
            fwrite($sock, $cmd . "\r\n");
            $resp = $read();
            $code = (int)substr($resp, 0, 3);
            if ($code !== $expectCode) {
                $log("Expected {$expectCode}, got {$code}: " . trim($resp) . " (cmd: " . trim($cmd) . ")");
                return false;
            }
            return true;
        };

        try {
            // Read server greeting
            $greeting = $read();
            if ((int)substr($greeting, 0, 3) !== 220) {
                $log("Bad greeting: " . trim($greeting));
                return false;
            }

            // EHLO
            if (!$send("EHLO " . gethostname(), 250)) {
                fclose($sock);
                return false;
            }

            // STARTTLS for 'tls' encryption
            if ($encryption === 'tls') {
                if (!$send("STARTTLS", 220)) {
                    fclose($sock);
                    return false;
                }

                $crypto = stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$crypto) {
                    $log("STARTTLS crypto handshake failed");
                    fclose($sock);
                    return false;
                }

                // Re-EHLO after STARTTLS
                if (!$send("EHLO " . gethostname(), 250)) {
                    fclose($sock);
                    return false;
                }
            }

            // AUTH LOGIN
            if (!empty($user) && !empty($pass)) {
                if (!$send("AUTH LOGIN", 334)) {
                    fclose($sock);
                    return false;
                }
                if (!$send(base64_encode($user), 334)) {
                    fclose($sock);
                    return false;
                }
                if (!$send(base64_encode($pass), 235)) {
                    fclose($sock);
                    return false;
                }
            }

            // MAIL FROM
            if (!$send("MAIL FROM:<{$fromEmail}>", 250)) {
                fclose($sock);
                return false;
            }

            // RCPT TO
            if (!$send("RCPT TO:<{$to}>", 250)) {
                fclose($sock);
                return false;
            }

            // DATA
            if (!$send("DATA", 354)) {
                fclose($sock);
                return false;
            }

            // Send the message body — dot-stuff lines starting with '.'
            $lines = explode("\n", str_replace("\r\n", "\n", $message));
            foreach ($lines as $line) {
                if (isset($line[0]) && $line[0] === '.') {
                    $line = '.' . $line;
                }
                fwrite($sock, $line . "\r\n");
            }

            // End data
            if (!$send(".", 250)) {
                fclose($sock);
                return false;
            }

            // QUIT
            $send("QUIT", 221);
            fclose($sock);
            return true;

        } catch (\Throwable $e) {
            $log("Exception: " . $e->getMessage());
            @fclose($sock);
            return false;
        }
    }
}
