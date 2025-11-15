<?php

class GalleryBlock extends BaseBlockHandler {
    public static function register(): void {
        BlockRenderer::registerHandler('gallery', new self());
    }

    public function renderEdit(array $config): string {
        $images = htmlspecialchars(json_encode($config['images'] ?? []), ENT_QUOTES, 'UTF-8');
        return <<<HTML
<div class="gallery-editor">
  <input type="file" multiple accept="image/*" @change="handleUpload">
  <div class="preview">
    <img v-for="img in images" :src="img.url" :key="img.id">
  </div>
  <input type="hidden" name="images" :value="imagesJson">
</div>
<script>
window.galleryConfig = {
  images: $images
};
</script>
HTML;
    }

    public function renderPreview(array $config): string {
        $html = '<div class="gallery-preview">';
        foreach ($config['images'] ?? [] as $image) {
            $url = htmlspecialchars($image['url'], ENT_QUOTES, 'UTF-8');
            $html .= "<img src=\"$url\">";
        }
        return $html . '</div>';
    }

    public function validate(array $config): bool {
        return isset($config['images']) && is_array($config['images']);
    }
}
