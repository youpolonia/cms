<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../modules/doccompiler/doccompiler.php';
require_once __DIR__ . '/../../core/csrf.php';

// Check admin permissions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'developer') {
    header('Location: /admin/login.php');
    exit;
}

$pageTitle = "Documentation Compiler";
require_once __DIR__ . '/../templates/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $content = $_POST['content'] ?? '';
    $format = $_POST['format'] ?? 'html';
    
    try {
        $result = DocCompiler::generateDocs($content, $format);
        $success = "Documentation generated successfully!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?><div class="container">
    <h1><?= $pageTitle ?></h1>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <pre><?= htmlspecialchars($result) ?></pre>
    <?php endif; ?>    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <?= csrf_field(); 
?>        <div class="form-group">
            <label for="content">Documentation Content:</label>
            <textarea class="form-control" id="content" name="content" rows="10" required><?= $_POST['content'] ?? '' ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="format">Output Format:</label>
            <select class="form-control" id="format" name="format">
                <option value="html">HTML</option>
                <option value="pdf">PDF</option>
                <option value="markdown">Markdown</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Generate Documentation</button>
    </form>
</div>

<?php require_once __DIR__ . '/../templates/footer.php';
