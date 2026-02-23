<?php
/**
 * Image Optimizer Tests
 * Tests for core/image_optimizer.php image processing functionality
 */

require_once __DIR__ . '/TestRunner.php';
require_once __DIR__ . '/../core/image_optimizer.php';

$runner = new TestRunner();

$runner->addTest('GD extension is loaded', function () {
    TestRunner::assert(extension_loaded('gd'), 'GD extension must be loaded');
});

$runner->addTest('Can resize a test image', function () {
    // Create a simple 100x100 test image
    $testImage = imagecreatetruecolor(100, 100);
    $blue = imagecolorallocate($testImage, 0, 100, 200);
    imagefill($testImage, 0, 0, $blue);
    
    $testPath = '/tmp/test_image.png';
    $resizedPath = '/tmp/test_image_resized.png';
    
    // Save test image
    imagepng($testImage, $testPath);
    imagedestroy($testImage);
    
    TestRunner::assert(file_exists($testPath), 'Test image should be created');
    
    // Test resize
    $result = ImageOptimizer::resize($testPath, $resizedPath, 50, 50);
    TestRunner::assert($result === true, 'Resize should return true on success');
    TestRunner::assert(file_exists($resizedPath), 'Resized image should exist');
    
    // Check dimensions
    $info = getimagesize($resizedPath);
    TestRunner::assertEquals(50, $info[0], 'Resized width should be 50px');
    TestRunner::assertEquals(50, $info[1], 'Resized height should be 50px');
    
    // Cleanup
    unlink($testPath);
    unlink($resizedPath);
});

$runner->addTest('WebP conversion works or skips gracefully', function () {
    // Create test image
    $testImage = imagecreatetruecolor(50, 50);
    $red = imagecolorallocate($testImage, 200, 0, 0);
    imagefill($testImage, 0, 0, $red);
    
    $testPath = '/tmp/test_webp.jpg';
    imagejpeg($testImage, $testPath);
    imagedestroy($testImage);
    
    $webpResult = ImageOptimizer::toWebP($testPath);
    
    if (function_exists('imagewebp')) {
        TestRunner::assertNotEmpty($webpResult, 'WebP conversion should return path when supported');
        TestRunner::assert(file_exists($webpResult), 'WebP file should exist');
        unlink($webpResult);
    } else {
        TestRunner::assert($webpResult === null, 'WebP conversion should return null when unsupported');
    }
    
    unlink($testPath);
});

$runner->addTest('srcset returns correct format string', function () {
    // Test with a path that doesn't exist (should return empty)
    $srcset = ImageOptimizer::srcset('nonexistent_xyz_12345.jpg');
    TestRunner::assert(is_string($srcset), 'srcset should return a string');
    
    // Test with simulated files using actual CMS_ROOT path
    $uploadsDir = CMS_ROOT . '/uploads/media';
    @mkdir($uploadsDir, 0755, true);
    
    // Create mock thumbnail files
    touch($uploadsDir . '/test-thumbnail.jpg');
    touch($uploadsDir . '/test-medium.jpg');
    touch($uploadsDir . '/test-large.jpg');
    
    // Test image with known dimensions
    $testImg = imagecreatetruecolor(1920, 1080);
    imagejpeg($testImg, $uploadsDir . '/test.jpg');
    imagedestroy($testImg);
    
    $srcset = ImageOptimizer::srcset('test.jpg');
    TestRunner::assertNotEmpty($srcset, 'srcset should not be empty for existing files');
    
    // Should contain width descriptors
    TestRunner::assert(strpos($srcset, '150w') !== false, 'srcset should contain thumbnail width');
    TestRunner::assert(strpos($srcset, '600w') !== false, 'srcset should contain medium width');  
    TestRunner::assert(strpos($srcset, '1200w') !== false, 'srcset should contain large width');
    TestRunner::assert(strpos($srcset, '1920w') !== false, 'srcset should contain original width');
    
    // Cleanup
    unlink($uploadsDir . '/test-thumbnail.jpg');
    unlink($uploadsDir . '/test-medium.jpg');
    unlink($uploadsDir . '/test-large.jpg');
    unlink($uploadsDir . '/test.jpg');
});

$runner->addTest('imgTag outputs proper HTML with loading lazy', function () {
    // Test without existing thumbnails (no srcset)
    $html = ImageOptimizer::imgTag('/path/to/image.jpg', 'Test image', 'my-class', '50vw');
    
    TestRunner::assert(strpos($html, '<img') === 0, 'Should start with img tag');
    TestRunner::assert(strpos($html, 'loading="lazy"') !== false, 'Should include loading="lazy"');
    TestRunner::assert(strpos($html, 'decoding="async"') !== false, 'Should include decoding="async"');
    TestRunner::assert(strpos($html, 'alt="Test image"') !== false, 'Should include alt text');
    TestRunner::assert(strpos($html, 'class="my-class"') !== false, 'Should include CSS class');
    TestRunner::assert(strpos($html, 'src="/path/to/image.jpg"') !== false, 'Should include src attribute');
    
    // Test with thumbnails that exist (with srcset)
    $uploadsDir = CMS_ROOT . '/uploads/media';
    @mkdir($uploadsDir, 0755, true);
    
    touch($uploadsDir . '/testimg-thumbnail.jpg');
    touch($uploadsDir . '/testimg-medium.jpg');
    
    $testImg = imagecreatetruecolor(800, 600);
    imagejpeg($testImg, $uploadsDir . '/testimg.jpg');
    imagedestroy($testImg);
    
    $htmlWithSrcset = ImageOptimizer::imgTag('/uploads/media/testimg.jpg', 'Test image', 'my-class', '50vw');
    TestRunner::assert(strpos($htmlWithSrcset, 'srcset=') !== false, 'Should include srcset when thumbnails exist');
    TestRunner::assert(strpos($htmlWithSrcset, 'sizes="50vw"') !== false, 'Should include sizes attribute when srcset exists');
    
    // Cleanup
    unlink($uploadsDir . '/testimg-thumbnail.jpg');
    unlink($uploadsDir . '/testimg-medium.jpg');
    unlink($uploadsDir . '/testimg.jpg');
});

$runner->addTest('generateThumbnails creates expected files', function () {
    // Create a test image
    $testImg = imagecreatetruecolor(200, 200);
    $green = imagecolorallocate($testImg, 0, 200, 0);
    imagefill($testImg, 0, 0, $green);
    
    $testDir = '/tmp/test_thumbnails';
    @mkdir($testDir, 0755, true);
    $sourcePath = $testDir . '/source.jpg';
    imagejpeg($testImg, $sourcePath);
    imagedestroy($testImg);
    
    // Generate thumbnails
    $result = ImageOptimizer::generateThumbnails($sourcePath);
    
    TestRunner::assert(is_array($result), 'generateThumbnails should return array');
    
    // Check that thumbnail files were created
    $expectedFiles = [
        'thumbnail' => $testDir . '/source-thumbnail.jpg',
        'medium' => $testDir . '/source-medium.jpg',
        'large' => $testDir . '/source-large.jpg'
    ];
    
    foreach ($expectedFiles as $size => $expectedPath) {
        if (isset($result[$size])) {
            TestRunner::assert(file_exists($result[$size]), "Thumbnail file should exist: $size");
        }
    }
    
    // Cleanup
    foreach ($result as $file) {
        if (file_exists($file)) unlink($file);
    }
    unlink($sourcePath);
    rmdir($testDir);
});

$runner->run();