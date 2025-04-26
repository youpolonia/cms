<?php

namespace App\Http\Controllers;

use App\Models\ContentTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContentTranslationController extends Controller
{
    public function index($contentId)
    {
        $translations = ContentTranslation::where('content_id', $contentId)
            ->get()
            ->mapWithKeys(function ($translation) {
                return [$translation->language_code => $translation->translated_content];
            });

        return response()->json([
            'content_id' => $contentId,
            'translations' => $translations
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content_id' => 'required|exists:contents,id',
            'language_code' => 'required|string|size:2|in:'.implode(',', array_keys(config('app.enabled_languages'))),
            'translated_content' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $translation = ContentTranslation::updateOrCreate(
            [
                'content_id' => $request->content_id,
                'language_code' => $request->language_code
            ],
            [
                'translated_content' => $request->translated_content
            ]
        );

        return response()->json([
            'message' => 'Translation saved successfully',
            'translation' => $translation
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'translated_content' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $translation = ContentTranslation::findOrFail($id);
        $translation->update([
            'translated_content' => $request->translated_content
        ]);

        return response()->json([
            'message' => 'Translation updated successfully',
            'translation' => $translation
        ]);
    }

    public function destroy($id)
    {
        $translation = ContentTranslation::findOrFail($id);
        $translation->delete();

        return response()->json([
            'message' => 'Translation deleted successfully'
        ]);
    }
}