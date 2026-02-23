<?php
declare(strict_types=1);

namespace Core;

class FormBuilder
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = db();
    }

    public function getForm(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM forms WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $form = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$form) return null;
        $form['fields'] = json_decode($form['fields'], true) ?: [];
        $form['settings'] = json_decode($form['settings'] ?? 'null', true);
        return $form;
    }

    public function getFormBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM forms WHERE slug = :slug AND active = 1 LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $form = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$form) return null;
        $form['fields'] = json_decode($form['fields'], true) ?: [];
        $form['settings'] = json_decode($form['settings'] ?? 'null', true);
        return $form;
    }

    public function listForms(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM forms ORDER BY updated_at DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createForm(array $data): int
    {
        $slug = $this->generateSlug($data['name'] ?? 'form');
        $stmt = $this->pdo->prepare("
            INSERT INTO forms (name, slug, fields, settings, success_message, redirect_url, email_to, active)
            VALUES (:name, :slug, :fields, :settings, :success_message, :redirect_url, :email_to, :active)
        ");
        $stmt->execute([
            'name'            => $data['name'] ?? 'Untitled Form',
            'slug'            => $slug,
            'fields'          => json_encode($data['fields'] ?? [], JSON_UNESCAPED_UNICODE),
            'settings'        => json_encode($data['settings'] ?? null, JSON_UNESCAPED_UNICODE),
            'success_message' => $data['success_message'] ?? 'Thank you! Your form has been submitted.',
            'redirect_url'    => $data['redirect_url'] ?? null,
            'email_to'        => $data['email_to'] ?? null,
            'active'          => (int)($data['active'] ?? 1),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateForm(int $id, array $data): bool
    {
        $sets = [];
        $params = ['id' => $id];

        foreach (['name', 'success_message', 'redirect_url', 'email_to'] as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (array_key_exists('fields', $data)) {
            $sets[] = 'fields = :fields';
            $params['fields'] = json_encode($data['fields'], JSON_UNESCAPED_UNICODE);
        }
        if (array_key_exists('settings', $data)) {
            $sets[] = 'settings = :settings';
            $params['settings'] = json_encode($data['settings'], JSON_UNESCAPED_UNICODE);
        }
        if (array_key_exists('active', $data)) {
            $sets[] = 'active = :active';
            $params['active'] = (int)$data['active'];
        }

        if (empty($sets)) return false;

        $sql = "UPDATE forms SET " . implode(', ', $sets) . " WHERE id = :id";
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function deleteForm(int $id): bool
    {
        return $this->pdo->prepare("DELETE FROM forms WHERE id = :id")->execute(['id' => $id]);
    }

    public function renderForm(array $form): string
    {
        $fields = $form['fields'] ?? [];
        $e = function(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
        $html = '<form class="fb-form" method="POST" action="/form/' . $e($form['slug']) . '" enctype="multipart/form-data" data-form-id="' . (int)$form['id'] . '">' . "\n";

        if (function_exists('csrf_token')) {
            $html .= '<input type="hidden" name="csrf_token" value="' . $e(csrf_token()) . '">' . "\n";
        }

        $html .= '<div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">';
        $html .= '<input type="text" name="_hp_email" tabindex="-1" autocomplete="off">';
        $html .= '</div>' . "\n";

        $html .= '<div class="fb-fields">' . "\n";
        foreach ($fields as $field) {
            $html .= $this->renderField($field, $e);
        }
        $html .= '</div>' . "\n";
        $html .= '<div class="fb-submit"><button type="submit" class="fb-btn">Submit</button></div>' . "\n";
        $html .= '</form>' . "\n";

        return $html;
    }

    private function renderField(array $f, callable $e): string
    {
        $type      = $f['type'] ?? 'text';
        $label     = $f['label'] ?? '';
        $name      = $f['name'] ?? '';
        $required  = !empty($f['required']);
        $ph        = $f['placeholder'] ?? '';
        $helpText  = $f['helpText'] ?? '';
        $width     = $f['width'] ?? 'full';
        $options   = $f['options'] ?? [];
        $validation = $f['validation'] ?? [];

        $wc = $width === 'half' ? 'fb-field-half' : 'fb-field-full';
        $ra = $required ? ' required' : '';
        $rs = $required ? ' <span class="fb-req">*</span>' : '';

        if ($type === 'heading') {
            return '<div class="fb-field ' . $wc . '"><h3 class="fb-heading">' . $e($label) . '</h3></div>' . "\n";
        }
        if ($type === 'paragraph') {
            return '<div class="fb-field ' . $wc . '"><p class="fb-paragraph">' . $e($label) . '</p></div>' . "\n";
        }

        $en = $e($name);
        $el = $e($label);
        $ep = $e($ph);

        $out = '<div class="fb-field ' . $wc . '">' . "\n";
        if ($type !== 'hidden') {
            $out .= '  <label class="fb-label" for="fb-' . $en . '">' . $el . $rs . '</label>' . "\n";
        }

        switch ($type) {
            case 'text':
                $out .= '  <input type="text" id="fb-' . $en . '" name="' . $en . '" placeholder="' . $ep . '" class="fb-input"' . $ra . '>' . "\n";
                break;
            case 'email':
                $out .= '  <input type="email" id="fb-' . $en . '" name="' . $en . '" placeholder="' . $ep . '" class="fb-input"' . $ra . '>' . "\n";
                break;
            case 'phone':
                $out .= '  <input type="tel" id="fb-' . $en . '" name="' . $en . '" placeholder="' . $ep . '" class="fb-input"' . $ra . '>' . "\n";
                break;
            case 'number':
                $min = isset($validation['min']) ? ' min="' . (int)$validation['min'] . '"' : '';
                $max = isset($validation['max']) ? ' max="' . (int)$validation['max'] . '"' : '';
                $out .= '  <input type="number" id="fb-' . $en . '" name="' . $en . '" placeholder="' . $ep . '" class="fb-input"' . $min . $max . $ra . '>' . "\n";
                break;
            case 'textarea':
                $out .= '  <textarea id="fb-' . $en . '" name="' . $en . '" placeholder="' . $ep . '" class="fb-input fb-textarea" rows="4"' . $ra . '></textarea>' . "\n";
                break;
            case 'select':
                $out .= '  <select id="fb-' . $en . '" name="' . $en . '" class="fb-input fb-select"' . $ra . '>' . "\n";
                $out .= '    <option value="">' . ($ep ?: '— Select —') . '</option>' . "\n";
                foreach ($options as $opt) {
                    $eo = $e((string)$opt);
                    $out .= '    <option value="' . $eo . '">' . $eo . '</option>' . "\n";
                }
                $out .= '  </select>' . "\n";
                break;
            case 'radio':
                $out .= '  <div class="fb-radio-group">' . "\n";
                foreach ($options as $opt) {
                    $eo = $e((string)$opt);
                    $out .= '    <label class="fb-radio-label"><input type="radio" name="' . $en . '" value="' . $eo . '"' . $ra . '> ' . $eo . '</label>' . "\n";
                }
                $out .= '  </div>' . "\n";
                break;
            case 'checkbox':
                $out = '<div class="fb-field ' . $wc . '">' . "\n";
                $out .= '  <label class="fb-checkbox-label"><input type="checkbox" name="' . $en . '" value="1"' . $ra . '> ' . $el . $rs . '</label>' . "\n";
                break;
            case 'checkbox_group':
                $out .= '  <div class="fb-checkbox-group">' . "\n";
                foreach ($options as $opt) {
                    $eo = $e((string)$opt);
                    $out .= '    <label class="fb-checkbox-label"><input type="checkbox" name="' . $en . '[]" value="' . $eo . '"> ' . $eo . '</label>' . "\n";
                }
                $out .= '  </div>' . "\n";
                break;
            case 'date':
                $out .= '  <input type="date" id="fb-' . $en . '" name="' . $en . '" class="fb-input"' . $ra . '>' . "\n";
                break;
            case 'time':
                $out .= '  <input type="time" id="fb-' . $en . '" name="' . $en . '" class="fb-input"' . $ra . '>' . "\n";
                break;
            case 'file':
                $accept = !empty($validation['accept']) ? ' accept="' . $e($validation['accept']) . '"' : '';
                $out .= '  <input type="file" id="fb-' . $en . '" name="' . $en . '" class="fb-input fb-file"' . $accept . $ra . '>' . "\n";
                break;
            case 'hidden':
                $val = $e($f['defaultValue'] ?? '');
                return '<input type="hidden" name="' . $en . '" value="' . $val . '">' . "\n";
        }

        if ($helpText) {
            $out .= '  <small class="fb-help">' . $e($helpText) . '</small>' . "\n";
        }
        $out .= '</div>' . "\n";
        return $out;
    }

    public function processSubmission(int $formId, array $data): array
    {
        $form = $this->getForm($formId);
        if (!$form) return ['success' => false, 'message' => 'Form not found.', 'errors' => []];
        if (!$form['active']) return ['success' => false, 'message' => 'This form is no longer accepting submissions.', 'errors' => []];

        if (!empty($data['_hp_email'] ?? '')) {
            return ['success' => true, 'message' => $form['success_message']];
        }

        $fields = $form['fields'] ?? [];
        $errors = [];
        $cleanData = [];

        foreach ($fields as $field) {
            $type = $field['type'] ?? 'text';
            $name = $field['name'] ?? '';
            $req = !empty($field['required']);

            if (in_array($type, ['heading', 'paragraph'])) continue;

            $value = $data[$name] ?? null;

            if ($type === 'file') {
                $value = $this->handleFileUpload($formId, $name, $field);
            }

            if ($req && ($value === null || $value === '' || $value === [])) {
                $errors[] = ($field['label'] ?? $name) . ' is required.';
                continue;
            }

            if ($type === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = ($field['label'] ?? $name) . ' must be a valid email address.';
            }

            if ($value !== null && $value !== '') {
                $cleanData[$name] = $value;
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Please fix the errors below.', 'errors' => $errors];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO form_submissions (form_id, data, ip_address, user_agent, page_url)
            VALUES (:form_id, :data, :ip, :ua, :page_url)
        ");
        $stmt->execute([
            'form_id'  => $formId,
            'data'     => json_encode($cleanData, JSON_UNESCAPED_UNICODE),
            'ip'       => $_SERVER['REMOTE_ADDR'] ?? null,
            'ua'       => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            'page_url' => mb_substr($data['_page_url'] ?? ($_SERVER['HTTP_REFERER'] ?? ''), 0, 500),
        ]);

        $this->pdo->prepare("UPDATE forms SET submissions_count = submissions_count + 1 WHERE id = :id")->execute(['id' => $formId]);

        if (!empty($form['email_to'])) {
            $this->sendNotificationEmail($form, $cleanData);
        }

        return [
            'success'      => true,
            'message'      => $form['success_message'] ?: 'Thank you! Your form has been submitted.',
            'redirect_url' => $form['redirect_url'] ?: null,
        ];
    }

    private function handleFileUpload(int $formId, string $fieldName, array $field): ?string
    {
        if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) return null;
        $file = $_FILES[$fieldName];
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $maxSize = (int)(($field['validation']['maxSize'] ?? 5) * 1024 * 1024);
        if ($file['size'] > $maxSize) return null;

        $uploadDir = CMS_ROOT . '/public/uploads/forms/' . $formId;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $uploadDir . '/' . $safeName;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return '/uploads/forms/' . $formId . '/' . $safeName;
        }
        return null;
    }

    private function sendNotificationEmail(array $form, array $data): void
    {
        $to = $form['email_to'];
        $subject = 'New submission: ' . $form['name'];
        $body = "New form submission received for \"{$form['name']}\"\n\n";
        foreach ($data as $key => $value) {
            if (is_array($value)) $value = implode(', ', $value);
            $body .= ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
        }
        $body .= "\n---\nSubmitted: " . date('Y-m-d H:i:s');
        $body .= "\nIP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        @mail($to, $subject, $body, $headers);
    }

    public function getSubmissions(int $formId, int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM form_submissions WHERE form_id = :fid");
        $countStmt->execute(['fid' => $formId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT * FROM form_submissions WHERE form_id = :fid ORDER BY created_at DESC LIMIT :lim OFFSET :off");
        $stmt->bindValue('fid', $formId, \PDO::PARAM_INT);
        $stmt->bindValue('lim', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['data'] = json_decode($row['data'], true) ?: [];
        }

        return [
            'submissions' => $rows,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'totalPages'  => $total > 0 ? (int)ceil($total / $perPage) : 1,
        ];
    }

    public function markRead(int $submissionId): bool
    {
        return $this->pdo->prepare("UPDATE form_submissions SET is_read = 1 WHERE id = :id")->execute(['id' => $submissionId]);
    }

    public function exportCsv(int $formId): string
    {
        $form = $this->getForm($formId);
        if (!$form) return '';

        $fieldNames = [];
        foreach ($form['fields'] ?? [] as $f) {
            if (in_array($f['type'] ?? '', ['heading', 'paragraph'])) continue;
            $fieldNames[] = $f['name'] ?? '';
        }

        $stmt = $this->pdo->prepare("SELECT * FROM form_submissions WHERE form_id = :fid ORDER BY created_at ASC");
        $stmt->execute(['fid' => $formId]);
        $submissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_merge($fieldNames, ['submitted_at', 'ip_address']));

        foreach ($submissions as $sub) {
            $d = json_decode($sub['data'], true) ?: [];
            $row = [];
            foreach ($fieldNames as $fn) {
                $val = $d[$fn] ?? '';
                if (is_array($val)) $val = implode(', ', $val);
                $row[] = $val;
            }
            $row[] = $sub['created_at'];
            $row[] = $sub['ip_address'];
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv;
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        if ($slug === '') $slug = 'form';

        $base = $slug;
        $i = 1;
        while (true) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM forms WHERE slug = :slug");
            $stmt->execute(['slug' => $slug]);
            if ((int)$stmt->fetchColumn() === 0) break;
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
