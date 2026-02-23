<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class ContactController
{
    /**
     * Handle contact form submission (AJAX)
     * POST /api/contact
     */
    public function submit(Request $request): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Rate limiting: max 5 submissions per IP per hour
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $pdo = db();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM contact_submissions 
             WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
        $stmt->execute([$ip]);
        if ((int)$stmt->fetchColumn() >= 5) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Too many submissions. Please try again later.']);
            return;
        }

        // Parse input
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $data = $_POST;
        }

        // Honeypot check (if a hidden field "website" is filled, it's a bot)
        if (!empty($data['website'] ?? '')) {
            // Pretend success to not tip off bots
            echo json_encode(['success' => true, 'message' => 'Thank you! We\'ll be in touch.']);
            return;
        }

        // Validate required fields
        $name    = trim($data['name'] ?? '');
        $email   = trim($data['email'] ?? '');
        $phone   = trim($data['phone'] ?? '');
        $subject = trim($data['subject'] ?? '');
        $message = trim($data['message'] ?? '');
        $pageSlug = trim($data['_page_slug'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors[] = 'Name is required.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        if ($message === '') {
            $errors[] = 'Message is required.';
        }
        if (mb_strlen($name) > 255) {
            $errors[] = 'Name is too long.';
        }
        if (mb_strlen($message) > 10000) {
            $errors[] = 'Message is too long (max 10,000 characters).';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Sanitize
        $name    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $email   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $phone   = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        // message stored raw (displayed with htmlspecialchars in admin)

        // Save to database
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO contact_submissions (name, email, phone, subject, message, page_slug, ip_address, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $name,
                $email,
                $phone ?: null,
                $subject ?: null,
                $message,
                $pageSlug ?: null,
                $ip,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
            ]);

            // Try to send email notification if SMTP is configured
            $this->notifyAdmin($pdo, $name, $email, $subject, $message);

            // Fire events
            cms_event('form.submitted', ['form' => 'contact', 'name' => $name, 'email' => $email, 'subject' => $subject, 'message' => mb_substr($message, 0, 500)]);
            cms_event('form.contact', ['name' => $name, 'email' => $email, 'subject' => $subject, 'page' => $pageSlug]);

            echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
        } catch (\PDOException $e) {
            error_log('Contact form error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Something went wrong. Please try again.']);
        }
    }

    /**
     * Send email notification to admin (if email settings exist)
     */
    private function notifyAdmin(\PDO $pdo, string $name, string $email, string $subject, string $message): void
    {
        try {
            // Get admin email from settings
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
            $stmt->execute(['admin_email']);
            $adminEmail = $stmt->fetchColumn();

            if (!$adminEmail) {
                $stmt->execute(['site_email']);
                $adminEmail = $stmt->fetchColumn();
            }

            if (!$adminEmail) {
                return; // No admin email configured
            }

            // Get site name
            $stmt->execute(['site_name']);
            $siteName = $stmt->fetchColumn() ?: 'Jessie CMS';

            $emailSubject = "[{$siteName}] New contact: " . ($subject ?: 'General Inquiry');
            $emailBody = "New contact form submission:\n\n"
                . "Name: {$name}\n"
                . "Email: {$email}\n"
                . "Subject: " . ($subject ?: 'N/A') . "\n\n"
                . "Message:\n{$message}\n\n"
                . "---\n"
                . "View all submissions: /admin/contact-submissions";

            $headers = [
                'From' => "noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
                'Reply-To' => $email,
                'Content-Type' => 'text/plain; charset=UTF-8',
                'X-Mailer' => 'Jessie CMS'
            ];

            // Use cms_send_email if available, otherwise native mail()
            if (function_exists('cms_send_email')) {
                cms_send_email($adminEmail, $emailSubject, $emailBody, $headers);
            } else {
                $headerStr = '';
                foreach ($headers as $k => $v) {
                    $headerStr .= "{$k}: {$v}\r\n";
                }
                @mail($adminEmail, $emailSubject, $emailBody, $headerStr);
            }
        } catch (\Throwable $e) {
            error_log('Contact notification email failed: ' . $e->getMessage());
        }
    }
}
