<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaService
{
    public function upload(UploadedFile $file, array $metadata = [], bool $isPublic = false): Media
    {
        $path = $file->store('media', 'public');
        $filename = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $size = $file->getSize();

        return Media::create([
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $size,
            'disk' => 'public',
            'is_public' => $isPublic,
            'metadata' => $metadata,
            'user_id' => auth()->id()
        ]);
    }

    public function transform(Media $media, array $transformations): string
    {
        $image = Image::make(Storage::disk($media->disk)->get($media->path));

        foreach ($transformations as $method => $params) {
            if (method_exists($image, $method)) {
                call_user_func_array([$image, $method], (array)$params);
            }
        }

        $transformedPath = 'transforms/'.pathinfo($media->path, PATHINFO_FILENAME).'_'.md5(json_encode($transformations)).'.'.pathinfo($media->path, PATHINFO_EXTENSION);

        Storage::disk($media->disk)->put($transformedPath, $image->encode());

        return $transformedPath;
    }

    public function getMCPMediaService(): MCPMediaService
    {
        return app(MCPMediaService::class);
    }
}