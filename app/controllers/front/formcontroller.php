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

    /**
     * Embeddable JS widget for a form
     * GET /form-embed/{slug}.js
     */
    public function embed(Request $request): void
    {
        header('Content-Type: application/javascript; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        $slug = $request->param('slug', '');
        if (!$slug) {
            echo '/* Form not found */';
            return;
        }

        require_once CMS_ROOT . '/core/form_builder.php';
        $fb = new \Core\FormBuilder();
        $form = $fb->getFormBySlug($slug);

        if (!$form) {
            echo '/* Form not found */';
            return;
        }

        $fields = $fb->getFormFields((int)$form['id']);
        $formName = htmlspecialchars($form['name'] ?? 'Contact Form', ENT_QUOTES, 'UTF-8');
        $submitUrl = '/form/' . urlencode($slug);
        $csrfToken = csrf_token();

        // Build fields HTML
        $fieldsHtml = '';
        foreach ($fields as $f) {
            $label = htmlspecialchars($f['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $name = htmlspecialchars($f['name'] ?? $f['field_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $type = $f['type'] ?? 'text';
            $req = !empty($f['required']) ? 'required' : '';

            if ($type === 'textarea') {
                $fieldsHtml .= "<div class=\"jf-field\"><label>{$label}</label><textarea name=\"{$name}\" {$req}></textarea></div>";
            } elseif ($type === 'select') {
                $opts = '';
                $options = is_string($f['options'] ?? '') ? explode("\n", $f['options']) : ($f['options'] ?? []);
                foreach ($options as $o) {
                    $o = trim($o);
                    if ($o !== '') $opts .= '<option value="' . htmlspecialchars($o, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($o, ENT_QUOTES, 'UTF-8') . '</option>';
                }
                $fieldsHtml .= "<div class=\"jf-field\"><label>{$label}</label><select name=\"{$name}\" {$req}><option value=\"\">--</option>{$opts}</select></div>";
            } else {
                $fieldsHtml .= "<div class=\"jf-field\"><label>{$label}</label><input type=\"{$type}\" name=\"{$name}\" {$req}/></div>";
            }
        }

        echo <<<JS
(function(){
  var c=document.getElementById('jessie-form-{$slug}');
  if(!c)return;
  c.innerHTML='<form class="jf-embed" id="jf-{$slug}">' +
    '<h3>{$formName}</h3>' +
    '{$fieldsHtml}' +
    '<input type="hidden" name="_token" value="{$csrfToken}"/>' +
    '<button type="submit">Submit</button>' +
    '<div class="jf-msg" style="display:none"></div>' +
    '</form>' +
    '<style>.jf-embed{font-family:sans-serif;max-width:500px}.jf-field{margin:0 0 12px}.jf-field label{display:block;margin-bottom:4px;font-weight:600}.jf-field input,.jf-field textarea,.jf-field select{width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box}.jf-embed button{background:#2563eb;color:#fff;border:none;padding:10px 24px;border-radius:4px;cursor:pointer}.jf-embed button:hover{background:#1d4ed8}.jf-msg{margin-top:8px;padding:8px;border-radius:4px}</style>';

  document.getElementById('jf-{$slug}').addEventListener('submit',function(e){
    e.preventDefault();
    var fd=new FormData(this),btn=this.querySelector('button'),msg=this.querySelector('.jf-msg');
    btn.disabled=true;btn.textContent='Sending...';
    fetch('{$submitUrl}',{method:'POST',body:fd})
      .then(function(r){return r.json()})
      .then(function(d){
        msg.style.display='block';
        msg.style.background=d.success?'#dcfce7':'#fee2e2';
        msg.style.color=d.success?'#166534':'#991b1b';
        msg.textContent=d.message||'Done';
        if(d.success)e.target.reset();
        btn.disabled=false;btn.textContent='Submit';
      })
      .catch(function(){
        msg.style.display='block';msg.style.background='#fee2e2';msg.style.color='#991b1b';
        msg.textContent='Error sending form.';
        btn.disabled=false;btn.textContent='Submit';
      });
  });
})();
JS;
    }
}
