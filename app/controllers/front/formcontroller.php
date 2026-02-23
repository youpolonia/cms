<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class FormController
{
    /**
     * Handle form submission
     * POST /form/{slug}
     */
    public function submit(Request $request): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $slug = $request->param('slug', '');
        if (!$slug) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid form.']);
            return;
        }

        require_once CMS_ROOT . '/core/form_builder.php';
        $fb = new \Core\FormBuilder();
        $form = $fb->getFormBySlug($slug);

        if (!$form) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Form not found.']);
            return;
        }

        // Rate limiting: max 10 submissions per IP per hour
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM form_submissions WHERE form_id = :fid AND ip_address = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
        $stmt->execute(['fid' => $form['id'], 'ip' => $ip]);
        if ((int)$stmt->fetchColumn() >= 10) {
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Too many submissions. Please try again later.']);
            return;
        }

        // Merge POST data
        $data = $_POST;

        $result = $fb->processSubmission((int)$form['id'], $data);

        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(422);
            echo json_encode($result);
        }
    }
}
