<?php
require_once __DIR__ . '/../../admin/includes/auth.php';
require_once __DIR__ . '/../../includes/form_generator.php';
require_once __DIR__ . '/../core/csrf.php';

// Check permissions
if (!has_permission('content_manage')) {
    header('Location: /admin/dashboard.php');
    exit;
}

$entry_id = $_GET['id'] ?? null;
$entry = $entry_id ? ContentEntry::getById($entry_id) : new ContentEntry();
$content_types = ContentType::getAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $form_data = $_POST;
    $form = new FormGenerator('entry_form');
    
    // Setup form fields based on content type
    $content_type = ContentType::getById($form_data['content_type_id']);
    foreach ($content_type->fields as $field) {
        $form->addField($field['name'], $field['type'], $field['label'], [
            'required' => $field['required'],
            'choices' => $field['choices'] ?? []
        ]);
    }

    if ($form->validate($form_data)) {
        // Save entry
        $entry->title = $form_data['title'];
        $entry->content_type_id = $form_data['content_type_id'];
        $entry->status = isset($_POST['publish']) ? 'published' : 'draft';
        $entry->field_values = $form_data;
        
        if ($entry->save()) {
            header('Location: /admin/content/entries.php');
            exit;
        }
    } else {
        $errors = $form->getErrors();
        $values = $form_data;
    }
}

// Initialize form
$form = new FormGenerator('entry_form');
$form->addField('title', 'text', 'Title', ['required' => true]);
$form->addField('content_type_id', 'select', 'Content Type', [
    'required' => true,
    'choices' => array_column($content_types, 'name', 'id')
]);

if ($entry_id) {
    $form->setValues($entry->field_values);
}

if (isset($errors)) {
    $form->setErrors($errors);
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $entry_id ? 'Edit' : 'Add' ?> Content Entry</title>
    <link rel="stylesheet" href="/admin/assets/css/content.css">
</head>
<body>
    <?php require_once __DIR__ . '/../../admin/includes/header.php'; 
?>    <main class="content-container">
        <h1><?= $entry_id ? 'Edit' : 'Add' ?> Content Entry</h1>
        
        <?= $form->render() 
?>        <div class="form-actions">
            <button type="submit" form="entry_form" name="save" class="button">Save as Draft</button>
            <button type="submit" form="entry_form" name="publish" class="button primary">Publish</button>
            <a href="/admin/content/entries.php" class="button secondary">Cancel</a>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../admin/includes/footer.php';
</body>
</html>
