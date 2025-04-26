<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\MediaCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index()
    {
        return Media::with('collections')->paginate(20);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'collection_id' => 'nullable|exists:media_collections,id'
        ]);

        $file = $request->file('file');
        $path = $file->store('media/' . date('Y/m'));

        $media = Media::create([
            'id' => Str::uuid(),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'metadata' => [
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ],
            'uploader_id' => auth()->id()
        ]);

        if ($request->collection_id) {
            $media->collections()->attach($request->collection_id);
        }

        return response()->json($media, 201);
    }

    public function show(Media $media)
    {
        return $media->load('collections');
    }

    public function destroy(Media $media)
    {
        Storage::delete($media->path);
        $media->delete();

        return response()->noContent();
    }
}
