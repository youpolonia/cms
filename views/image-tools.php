<?php
require_once __DIR__ . '/../media/ai/ImageEditor.php';
require_once __DIR__ . '/../media/ai/ImageGenerator.php';

// Sanitize input
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';
$file = isset($_GET['file']) ? htmlspecialchars($_GET['file']) : '';

// Handle actions
switch ($action) {
    case 'generate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['prompt'])) {
            $prompt = htmlspecialchars($_POST['prompt']);
            try {
                ImageGenerator::generateImage($prompt);
                header('Location: media-gallery-view.php?success=Image+generated');
                exit;
            } catch (Exception $e) {
                header('Location: media-gallery-view.php?error=' . urlencode($e->getMessage()));
                exit;
            }
        }
        break;

    case 'resize':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $width = (int)$_POST['width'];
            $height = (int)$_POST['height'];
            try {
                ImageEditor::resize($file, $width, $height);
                header('Location: media-gallery-view.php?success=Image+resized');
                exit;
            } catch (Exception $e) {
                header('Location: media-gallery-view.php?error=' . urlencode($e->getMessage()));
                exit;
            }
        }
        echo '<form method="POST">
            <h2>Resize Image</h2>
            <input type="number" name="width" placeholder="Width" required>
            <input type="number" name="height" placeholder="Height" required>
            <button type="submit">Resize</button>
        </form>';
        break;

    case 'crop':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $x = (int)$_POST['x'];
            $y = (int)$_POST['y'];
            $width = (int)$_POST['width'];
            $height = (int)$_POST['height'];
            try {
                ImageEditor::crop($file, $x, $y, $width, $height);
                header('Location: media-gallery-view.php?success=Image+cropped');
                exit;
            } catch (Exception $e) {
                header('Location: media-gallery-view.php?error=' . urlencode($e->getMessage()));
                exit;
            }
        }
        echo '<form method="POST">
            <h2>Crop Image</h2>
            <input type="number" name="x" placeholder="X" required>
            <input type="number" name="y" placeholder="Y" required>
            <input type="number" name="width" placeholder="Width" required>
            <input type="number" name="height" placeholder="Height" required>
            <button type="submit">Crop</button>
        </form>';
        break;

    case 'removeBackground':
        try {
            $newFile = ImageEditor::removeBackground($file);
            header('Location: media-gallery-view.php?success=Background+removed');
            exit;
        } catch (Exception $e) {
            header('Location: media-gallery-view.php?error=' . urlencode($e->getMessage()));
            exit;
        }
        break;

    default:
        header('Location: media-gallery-view.php');
        exit;
}
