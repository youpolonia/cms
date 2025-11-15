<?php
/**
 * Email Notification Dispatcher
 * Uses PHP mail() function or SMTP wrapper
 */
class EmailDispatcher {
    private $fromEmail;
    private $fromName;
    private $smtpConfig;

    public function __construct(string $fromEmail, string $fromName, array $smtpConfig = []) {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->smtpConfig = $smtpConfig;
    }

    public function send(string $to, string $subject, string $message, array $headers = []): bool {
        $defaultHeaders = [
            'From' => "{$this->fromName} <{$this->fromEmail}>",
            'Reply-To' => $this->fromEmail,
            'Content-Type' => 'text/html; charset=UTF-8'
        ];

        $finalHeaders = array_merge($defaultHeaders, $headers);
        $headerString = implode("\r\n", array_map(
            fn($k, $v) => "$k: $v",
            array_keys($finalHeaders),
            $finalHeaders
        ));

        if (!empty($this->smtpConfig)) {
            return $this->sendViaSMTP($to, $subject, $message, $headerString);
        }

        return mail($to, $subject, $message, $headerString);
    }

    private function sendViaSMTP(string $to, string $subject, string $message, string $headers): bool {
        // Basic SMTP implementation without external libraries
        $smtp = fsockopen(
            $this->smtpConfig['host'],
            $this->smtpConfig['port'] ?? 25,
            $errno,
            $errstr,
            30
        );

        if (!$smtp) {
            error_log("SMTP connection failed: $errstr ($errno)");
            return false;
        }

        // SMTP protocol commands
        $commands = [
            "EHLO localhost",
            "AUTH LOGIN",
            base64_encode($this->smtpConfig['username']),
            base64_encode($this->smtpConfig['password']),
            "MAIL FROM:<{$this->fromEmail}>",
            "RCPT TO:<$to>",
            "DATA",
            "Subject: $subject",
            $headers,
            "\r\n$message",
            ".",
            "QUIT"
        ];

        foreach ($commands as $cmd) {
            fputs($smtp, "$cmd\r\n");
            $response = fgets($smtp, 4096);
            if (substr($response, 0, 3) != '250' && substr($response, 0, 3) != '354') {
                error_log("SMTP error: $response");
                fclose($smtp);
                return false;
            }
        }

        fclose($smtp);
        return true;
    }
}
