<?php
/**
 * Image SEO Analyzer
 * Premium feature for analyzing image optimization
 *
 * Checks:
 * - Alt text presence and quality
 * - File naming conventions
 * - Image dimensions
 * - Loading attributes
 * - Format optimization suggestions
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

/**
 * Extract all images from HTML content
 *
 * @param string $html HTML content
 * @return array Array of image data
 */
function ai_image_extract_all(string $html): array
{
    $images = [];

    // Match all img tags
    preg_match_all('/<img\s+[^>]*>/is', $html, $matches);

    foreach ($matches[0] as $imgTag) {
        $image = [
            'tag' => $imgTag,
            'src' => '',
            'alt' => null,
            'title' => null,
            'width' => null,
            'height' => null,
            'loading' => null,
            'class' => null,
            'srcset' => null,
        ];

        // Extract src
        if (preg_match('/src=["\']([^"\']+)["\']/i', $imgTag, $srcMatch)) {
            $image['src'] = $srcMatch[1];
        }

        // Extract alt (note: empty alt="" is different from missing alt)
        if (preg_match('/alt=["\']([^"\']*)["\']/', $imgTag, $altMatch)) {
            $image['alt'] = $altMatch[1];
        }

        // Extract title
        if (preg_match('/title=["\']([^"\']*)["\']/', $imgTag, $titleMatch)) {
            $image['title'] = $titleMatch[1];
        }

        // Extract width
        if (preg_match('/width=["\']?(\d+)["\']?/i', $imgTag, $widthMatch)) {
            $image['width'] = (int)$widthMatch[1];
        }

        // Extract height
        if (preg_match('/height=["\']?(\d+)["\']?/i', $imgTag, $heightMatch)) {
            $image['height'] = (int)$heightMatch[1];
        }

        // Extract loading attribute
        if (preg_match('/loading=["\']([^"\']+)["\']/i', $imgTag, $loadingMatch)) {
            $image['loading'] = $loadingMatch[1];
        }

        // Extract class
        if (preg_match('/class=["\']([^"\']+)["\']/i', $imgTag, $classMatch)) {
            $image['class'] = $classMatch[1];
        }

        // Extract srcset
        if (preg_match('/srcset=["\']([^"\']+)["\']/i', $imgTag, $srcsetMatch)) {
            $image['srcset'] = $srcsetMatch[1];
        }

        if (!empty($image['src'])) {
            $images[] = $image;
        }
    }

    return $images;
}

/**
 * Analyze a single image for SEO
 *
 * @param array $image Image data from ai_image_extract_all()
 * @param string $focusKeyword Optional focus keyword to check in alt
 * @return array Analysis result
 */
function ai_image_analyze_single(array $image, string $focusKeyword = ''): array
{
    $issues = [];
    $warnings = [];
    $passed = [];
    $score = 100;

    $src = $image['src'] ?? '';
    $alt = $image['alt'];
    $filename = basename(parse_url($src, PHP_URL_PATH) ?? '');
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // 1. Check alt text
    if ($alt === null) {
        $issues[] = [
            'type' => 'missing_alt',
            'message' => 'Missing alt attribute',
            'impact' => 'high',
        ];
        $score -= 25;
    } elseif ($alt === '') {
        // Empty alt is valid for decorative images
        $warnings[] = [
            'type' => 'empty_alt',
            'message' => 'Empty alt text (OK for decorative images)',
            'impact' => 'low',
        ];
        $score -= 5;
    } elseif (strlen($alt) < 5) {
        $warnings[] = [
            'type' => 'short_alt',
            'message' => 'Alt text is very short (' . strlen($alt) . ' chars)',
            'impact' => 'medium',
        ];
        $score -= 10;
    } elseif (strlen($alt) > 125) {
        $warnings[] = [
            'type' => 'long_alt',
            'message' => 'Alt text is too long (' . strlen($alt) . ' chars, recommended: <125)',
            'impact' => 'low',
        ];
        $score -= 5;
    } else {
        $passed[] = 'Alt text present and appropriate length';
    }

    // 2. Check if alt contains focus keyword
    if ($focusKeyword !== '' && $alt !== null && $alt !== '') {
        $kwLower = strtolower($focusKeyword);
        $altLower = strtolower($alt);
        if (strpos($altLower, $kwLower) !== false) {
            $passed[] = 'Alt text contains focus keyword';
            $score += 5; // Bonus
        } else {
            $warnings[] = [
                'type' => 'no_keyword_in_alt',
                'message' => 'Alt text does not contain focus keyword',
                'impact' => 'low',
            ];
        }
    }

    // 3. Check filename
    $filenameBase = pathinfo($filename, PATHINFO_FILENAME);

    // Check for generic/bad filenames
    $genericPatterns = [
        '/^img\d*$/i',
        '/^image\d*$/i',
        '/^photo\d*$/i',
        '/^picture\d*$/i',
        '/^dsc\d+$/i',
        '/^img_\d+$/i',
        '/^screenshot/i',
        '/^\d+$/',
        '/^[a-f0-9]{8,}$/i', // Hash-like
    ];

    $isGenericFilename = false;
    foreach ($genericPatterns as $pattern) {
        if (preg_match($pattern, $filenameBase)) {
            $isGenericFilename = true;
            break;
        }
    }

    if ($isGenericFilename) {
        $issues[] = [
            'type' => 'generic_filename',
            'message' => 'Generic/non-descriptive filename: ' . $filename,
            'impact' => 'medium',
        ];
        $score -= 10;
    } elseif (preg_match('/[A-Z]/', $filenameBase)) {
        $warnings[] = [
            'type' => 'uppercase_filename',
            'message' => 'Filename contains uppercase letters',
            'impact' => 'low',
        ];
        $score -= 3;
    } elseif (strpos($filenameBase, ' ') !== false || strpos($filenameBase, '_') !== false) {
        $warnings[] = [
            'type' => 'filename_spaces',
            'message' => 'Filename uses spaces/underscores instead of hyphens',
            'impact' => 'low',
        ];
        $score -= 3;
    } else {
        $passed[] = 'Filename is SEO-friendly';
    }

    // 4. Check format
    $modernFormats = ['webp', 'avif'];
    $acceptableFormats = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'avif'];

    if (!in_array($extension, $acceptableFormats, true)) {
        $issues[] = [
            'type' => 'unsupported_format',
            'message' => 'Unsupported image format: ' . $extension,
            'impact' => 'high',
        ];
        $score -= 15;
    } elseif (!in_array($extension, $modernFormats, true)) {
        $warnings[] = [
            'type' => 'legacy_format',
            'message' => 'Consider using WebP format for better compression',
            'impact' => 'low',
        ];
        $score -= 3;
    } else {
        $passed[] = 'Using modern image format';
    }

    // 5. Check dimensions
    $hasWidth = $image['width'] !== null;
    $hasHeight = $image['height'] !== null;

    if (!$hasWidth && !$hasHeight) {
        $warnings[] = [
            'type' => 'no_dimensions',
            'message' => 'No width/height attributes (causes layout shift)',
            'impact' => 'medium',
        ];
        $score -= 10;
    } elseif (!$hasWidth || !$hasHeight) {
        $warnings[] = [
            'type' => 'partial_dimensions',
            'message' => 'Only one dimension specified',
            'impact' => 'low',
        ];
        $score -= 5;
    } else {
        $passed[] = 'Width and height specified';

        // Check for oversized images (likely not responsive)
        if ($image['width'] > 2000 || $image['height'] > 2000) {
            $warnings[] = [
                'type' => 'large_dimensions',
                'message' => 'Very large dimensions (' . $image['width'] . 'x' . $image['height'] . ')',
                'impact' => 'medium',
            ];
            $score -= 5;
        }
    }

    // 6. Check lazy loading
    if ($image['loading'] === 'lazy') {
        $passed[] = 'Lazy loading enabled';
    } elseif ($image['loading'] === 'eager') {
        // Eager is fine for above-the-fold images
        $passed[] = 'Eager loading (OK for above-fold)';
    } else {
        $warnings[] = [
            'type' => 'no_lazy_loading',
            'message' => 'No loading attribute (consider lazy loading)',
            'impact' => 'low',
        ];
        $score -= 3;
    }

    // 7. Check srcset for responsive images
    if (!empty($image['srcset'])) {
        $passed[] = 'Responsive srcset provided';
    } else {
        $warnings[] = [
            'type' => 'no_srcset',
            'message' => 'No srcset for responsive images',
            'impact' => 'low',
        ];
    }

    // Normalize score
    $score = max(0, min(100, $score));

    // Determine status
    $status = 'good';
    if ($score < 50) {
        $status = 'poor';
    } elseif ($score < 75) {
        $status = 'needs_improvement';
    }

    return [
        'src' => $src,
        'filename' => $filename,
        'alt' => $alt,
        'width' => $image['width'],
        'height' => $image['height'],
        'format' => $extension,
        'score' => $score,
        'status' => $status,
        'issues' => $issues,
        'warnings' => $warnings,
        'passed' => $passed,
    ];
}

/**
 * Analyze all images in content
 *
 * @param string $html HTML content
 * @param string $focusKeyword Optional focus keyword
 * @return array Complete analysis
 */
function ai_image_analyze_content(string $html, string $focusKeyword = ''): array
{
    $images = ai_image_extract_all($html);

    if (empty($images)) {
        return [
            'ok' => true,
            'total_images' => 0,
            'images' => [],
            'summary' => [
                'avg_score' => 0,
                'issues_count' => 0,
                'warnings_count' => 0,
                'missing_alt' => 0,
                'generic_filenames' => 0,
                'no_dimensions' => 0,
                'legacy_formats' => 0,
            ],
            'recommendations' => [],
        ];
    }

    $analyzedImages = [];
    $totalScore = 0;
    $totalIssues = 0;
    $totalWarnings = 0;
    $missingAlt = 0;
    $genericFilenames = 0;
    $noDimensions = 0;
    $legacyFormats = 0;

    foreach ($images as $image) {
        $analysis = ai_image_analyze_single($image, $focusKeyword);
        $analyzedImages[] = $analysis;

        $totalScore += $analysis['score'];
        $totalIssues += count($analysis['issues']);
        $totalWarnings += count($analysis['warnings']);

        // Count specific issues
        foreach ($analysis['issues'] as $issue) {
            if ($issue['type'] === 'missing_alt') $missingAlt++;
            if ($issue['type'] === 'generic_filename') $genericFilenames++;
        }
        foreach ($analysis['warnings'] as $warning) {
            if ($warning['type'] === 'no_dimensions') $noDimensions++;
            if ($warning['type'] === 'legacy_format') $legacyFormats++;
        }
    }

    $avgScore = count($images) > 0 ? round($totalScore / count($images)) : 0;

    // Generate recommendations
    $recommendations = [];

    if ($missingAlt > 0) {
        $recommendations[] = [
            'priority' => 'high',
            'message' => "Add alt text to {$missingAlt} image(s) for accessibility and SEO",
        ];
    }

    if ($genericFilenames > 0) {
        $recommendations[] = [
            'priority' => 'medium',
            'message' => "Rename {$genericFilenames} image(s) with descriptive, keyword-rich filenames",
        ];
    }

    if ($noDimensions > 0) {
        $recommendations[] = [
            'priority' => 'medium',
            'message' => "Add width/height attributes to {$noDimensions} image(s) to prevent layout shift",
        ];
    }

    if ($legacyFormats > 0) {
        $recommendations[] = [
            'priority' => 'low',
            'message' => "Consider converting {$legacyFormats} image(s) to WebP format for better performance",
        ];
    }

    if ($focusKeyword !== '') {
        $imagesWithKeyword = 0;
        foreach ($analyzedImages as $img) {
            if ($img['alt'] !== null && stripos($img['alt'], $focusKeyword) !== false) {
                $imagesWithKeyword++;
            }
        }
        if ($imagesWithKeyword === 0 && count($images) > 0) {
            $recommendations[] = [
                'priority' => 'medium',
                'message' => "Include focus keyword '{$focusKeyword}' in at least one image alt text",
            ];
        }
    }

    return [
        'ok' => true,
        'total_images' => count($images),
        'images' => $analyzedImages,
        'summary' => [
            'avg_score' => $avgScore,
            'issues_count' => $totalIssues,
            'warnings_count' => $totalWarnings,
            'missing_alt' => $missingAlt,
            'generic_filenames' => $genericFilenames,
            'no_dimensions' => $noDimensions,
            'legacy_formats' => $legacyFormats,
        ],
        'recommendations' => $recommendations,
    ];
}

/**
 * Generate optimized alt text suggestion
 *
 * @param string $filename Image filename
 * @param string $context Surrounding text context
 * @param string $focusKeyword Optional focus keyword
 * @return string Suggested alt text
 */
function ai_image_suggest_alt(string $filename, string $context = '', string $focusKeyword = ''): string
{
    // Clean filename
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $base = str_replace(['-', '_'], ' ', $base);
    $base = preg_replace('/\d+/', '', $base); // Remove numbers
    $base = trim($base);

    if (empty($base)) {
        $base = 'image';
    }

    // Capitalize words
    $alt = ucwords($base);

    // Add focus keyword if not present
    if ($focusKeyword !== '' && stripos($alt, $focusKeyword) === false) {
        $alt = ucfirst($focusKeyword) . ' - ' . $alt;
    }

    // Limit length
    if (strlen($alt) > 100) {
        $alt = substr($alt, 0, 97) . '...';
    }

    return $alt;
}

/**
 * Get overall image SEO grade
 *
 * @param int $avgScore Average image score
 * @param int $totalImages Total number of images
 * @param int $issuesCount Total issues count
 * @return array Grade with label and color
 */
function ai_image_get_grade(int $avgScore, int $totalImages, int $issuesCount): array
{
    if ($totalImages === 0) {
        return [
            'grade' => 'N/A',
            'label' => 'No images',
            'color' => 'secondary',
        ];
    }

    if ($avgScore >= 90 && $issuesCount === 0) {
        return ['grade' => 'A+', 'label' => 'Excellent', 'color' => 'success'];
    } elseif ($avgScore >= 80) {
        return ['grade' => 'A', 'label' => 'Great', 'color' => 'success'];
    } elseif ($avgScore >= 70) {
        return ['grade' => 'B', 'label' => 'Good', 'color' => 'primary'];
    } elseif ($avgScore >= 60) {
        return ['grade' => 'C', 'label' => 'Fair', 'color' => 'warning'];
    } elseif ($avgScore >= 50) {
        return ['grade' => 'D', 'label' => 'Poor', 'color' => 'warning'];
    } else {
        return ['grade' => 'F', 'label' => 'Critical', 'color' => 'danger'];
    }
}
