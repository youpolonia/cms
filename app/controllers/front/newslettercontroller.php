<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Frontend Newsletter Subscription
 */
class NewsletterController
{
    /**
     * Handle newsletter subscription
     * POST /newsletter/subscribe
     */
    public function subscribe(): void
    {
        $request = new Request();
        $email = trim($request->post('email', ''));
        $name = trim($request->post('name', ''));
        $redirect = $request->post('redirect', '/');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Honeypot
        $honeypot = trim($request->post('website_url', ''));
        if (!empty($honeypot)) {
            Session::flash('newsletter_success', 'Thank you for subscribing!');
            Response::redirect($redirect);
            return;
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('newsletter_error', 'Please enter a valid email address.');
            Response::redirect($redirect);
            return;
        }

        // Rate limiting: 3 subscribe attempts per IP per hour
        $pdo = db();
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip VARCHAR(45) NOT NULL,
                action VARCHAR(32) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip_action (ip, action),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND action = 'newsletter' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $stmt->execute([$ip]);
            if ((int)$stmt->fetchColumn() >= 3) {
                Session::flash('newsletter_error', 'Too many attempts. Please try again later.');
                Response::redirect($redirect);
                return;
            }
            $pdo->prepare("INSERT INTO rate_limits (ip, action, created_at) VALUES (?, 'newsletter', NOW())")->execute([$ip]);
        } catch (\Exception $e) {}

        // Use newsletter plugin if available
        $pluginPath = CMS_ROOT . '/plugins/jessie-newsletter/includes/class-newsletter-subscriber.php';
        if (file_exists($pluginPath)) {
            require_once $pluginPath;
            $result = \NewsletterSubscriber::subscribe($email, $name, [], 'website');
            if (!empty($result['error'])) {
                Session::flash('newsletter_error', $result['error']);
            } else {
                Session::flash('newsletter_success', 'You\'re subscribed! Check your email for confirmation.');
            }
        } else {
            // Fallback: store in newsletter_subscribers table directly
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(191) NOT NULL UNIQUE,
                    name VARCHAR(100) DEFAULT '',
                    status ENUM('active','unsubscribed','bounced') DEFAULT 'active',
                    source VARCHAR(50) DEFAULT 'website',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_email (email),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    Session::flash('newsletter_success', 'You\'re already subscribed!');
                } else {
                    $pdo->prepare("INSERT INTO newsletter_subscribers (email, name, status, source, created_at) VALUES (?, ?, 'active', 'website', NOW())")
                        ->execute([$email, substr($name, 0, 100)]);
                    Session::flash('newsletter_success', 'You\'re subscribed! Thank you.');
                }
            } catch (\Exception $e) {
                Session::flash('newsletter_error', 'Something went wrong. Please try again.');
            }
        }

        if (function_exists('cms_event')) {
            cms_event('newsletter.subscribed', ['email' => $email, 'source' => 'website']);
        }

        Response::redirect($redirect);
    }
}
