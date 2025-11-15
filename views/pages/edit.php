<?php
// Check for error messages
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?><!DOCTYPE html>
<html>
<head>
    <title>Edit Page</title>
    <script>
        function validateSlug() {
            const slug = document.getElementById('slug').value;
            if (!/^[a-z0-9-]+$/.test(slug)) {
                alert('Slug can only contain lowercase letters, numbers and hyphens');
                return false;
            }
            return true;
        }
?>    </script>
</head>
<body>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/pages/save" onsubmit="
return validateSlug()">
        <input type="hidden" name="existing_slug" value="<?= htmlspecialchars($slug ?? '') ?>">
        
        <label for="slug">Slug:</label>
        <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($slug ?? '') ?>"
 required>
        <button type="submit">Save</button>
    </form>
</body>
</html>
