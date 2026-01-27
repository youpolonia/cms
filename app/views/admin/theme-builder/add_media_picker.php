<?php
/**
 * Add MediaPicker buttons to Theme Builder image fields
 */

$file = '/var/www/html/cms/app/views/admin/theme-builder/edit.php';
$content = file_get_contents($file);
$changes = 0;

// 1. Add CSS for .tb-btn-media
$css_find = '.tb-btn-ai:disabled { opacity: 0.6; cursor: wait; transform: none; }';
$css_replace = '.tb-btn-ai:disabled { opacity: 0.6; cursor: wait; transform: none; }
    .tb-btn-media { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #fff; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 12px; transition: all 0.2s; flex: 0 0 auto; }
    .tb-btn-media:hover { opacity: 0.9; transform: translateY(-1px); }';

if (strpos($content, '.tb-btn-media') === false) {
    $content = str_replace($css_find, $css_replace, $content);
    echo "1. CSS added\n";
    $changes++;
}

// 2. Image module - src
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Image URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(content.src || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'src\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Image URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(content.src || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'src\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'src\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "2. Image src - DONE\n";
    $changes++;
}

// 3. Video module - cover_image
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Cover Image URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(content.cover_image || '') + '\" placeholder=\"https://example.com/cover.jpg\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'cover_image\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Cover Image URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(content.cover_image || '') + '\" placeholder=\"https://example.com/cover.jpg\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'cover_image\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'cover_image\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "3. Video cover_image - DONE\n";
    $changes++;
}

// 4. Gallery images
$old = "html += '<input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(img.src || '') + '\" placeholder=\"Image URL\" onchange=\"TB.updateGalleryImage(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\\'src\\',this.value)\">';";
$new = "html += '<input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(img.src || '') + '\" placeholder=\"Image URL\" onchange=\"TB.updateGalleryImage(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\\'src\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateGalleryImage(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + i + ',\\'src\\',url))\">📁</button>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "4. Gallery images - DONE\n";
    $changes++;
}

// 5. Testimonial avatar
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Avatar URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(content.avatar || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'avatar\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Avatar URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(content.avatar || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'avatar\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'avatar\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "5. Testimonial avatar - DONE\n";
    $changes++;
}

// 6. Blurb image
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Image URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(content.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'image\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Image URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(content.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'image\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'image\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "6. Blurb image - DONE\n";
    $changes++;
}

// 7. Hero background_image
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Background Image URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(content.background_image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'background_image\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Background Image URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(content.background_image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'background_image\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'background_image\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "7. Hero background_image - DONE\n";
    $changes++;
}

// 8. Slider slides image
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Background Image URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(slide.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateSliderSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + slideIdx + ',\\'image\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Background Image URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(slide.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateSliderSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + slideIdx + ',\\'image\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateSliderSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + slideIdx + ',\\'image\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "8. Slider slides image - DONE\n";
    $changes++;
}

// 9. Video slider thumbnail
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Custom Thumbnail URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(video.thumbnail || '') + '\" placeholder=\"Leave empty to auto-detect from YouTube/Vimeo\" onchange=\"TB.updateVideoSliderVideo(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + videoIdx + ',\\'thumbnail\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Custom Thumbnail URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(video.thumbnail || '') + '\" placeholder=\"Leave empty to auto-detect from YouTube/Vimeo\" onchange=\"TB.updateVideoSliderVideo(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + videoIdx + ',\\'thumbnail\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateVideoSliderVideo(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + videoIdx + ',\\'thumbnail\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "9. Video slider thumbnail - DONE\n";
    $changes++;
}

// 10. Portfolio items image
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Image URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(item.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updatePortfolioItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + itemIdx + ',\\'image\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Image URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(item.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updatePortfolioItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + itemIdx + ',\\'image\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updatePortfolioItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + itemIdx + ',\\'image\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "10. Portfolio items image - DONE\n";
    $changes++;
}

// 11. Team image
$old = "html += '<input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(content.image || '') + '\" placeholder=\"Image URL\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'image\\',this.value)\">';
                    html += '</div></div>';";
$new = "html += '<input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(content.image || '') + '\" placeholder=\"Image URL\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'image\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'image\\',url))\">📁</button>';
                    html += '</div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "11. Team image - DONE\n";
    $changes++;
}

// 12. Fullwidth image src
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Image URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(content.src || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'src\\',this.value)\"></div>';
                    html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Alt Text</div>";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Image URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(content.src || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'src\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'src\\',url))\">📁</button></div></div>';
                    html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Alt Text</div>";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "12. Fullwidth image src - DONE\n";
    $changes++;
}

// 13. Fullwidth slider slides image
$old = "html += '<div class=\"tb-setting-group\" style=\"margin-bottom:8px\"><div class=\"tb-setting-label\">Image URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(slide.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateFullwidthSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + slideIdx + ',\\'image\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\" style=\"margin-bottom:8px\"><div class=\"tb-setting-label\">Image URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(slide.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateFullwidthSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + slideIdx + ',\\'image\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateFullwidthSlide(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + slideIdx + ',\\'image\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "13. Fullwidth slider slides image - DONE\n";
    $changes++;
}

// 14. Fullwidth header background_image
$old = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Background Image</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(content.background_image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'background_image\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\"><div class=\"tb-setting-label\">Background Image</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(content.background_image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'background_image\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateModuleContent(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\\'background_image\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "14. Fullwidth header background_image - DONE\n";
    $changes++;
}

// 15. Fullwidth portfolio items image
$old = "html += '<div class=\"tb-setting-group\" style=\"margin-bottom:8px\"><div class=\"tb-setting-label\">Image URL</div><input type=\"text\" class=\"tb-setting-input\" value=\"' + this.escapeHtml(item.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateFullwidthPortfolioItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + itemIdx + ',\\'image\\',this.value)\"></div>';";
$new = "html += '<div class=\"tb-setting-group\" style=\"margin-bottom:8px\"><div class=\"tb-setting-label\">Image URL</div><div style=\"display:flex;gap:8px\"><input type=\"text\" class=\"tb-setting-input\" style=\"flex:1\" value=\"' + this.escapeHtml(item.image || '') + '\" placeholder=\"https://...\" onchange=\"TB.updateFullwidthPortfolioItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + itemIdx + ',\\'image\\',this.value)\"><button type=\"button\" class=\"tb-btn-media\" onclick=\"TB.openMediaPicker(url => TB.updateFullwidthPortfolioItem(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',' + itemIdx + ',\\'image\\',url))\">📁</button></div></div>';";
if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "15. Fullwidth portfolio items image - DONE\n";
    $changes++;
}

// Save
if ($changes > 0) {
    file_put_contents($file, $content);
    echo "\n=== COMPLETED: $changes changes applied ===\n";
} else {
    echo "\n=== NO CHANGES NEEDED ===\n";
}
