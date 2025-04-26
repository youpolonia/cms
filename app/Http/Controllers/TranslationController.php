<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentTranslation;
use App\Http\Requests\TranslationRequest;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    public function getTranslations(Content $content)
    {
        $translations = $content->translations()
            ->get()
            ->mapWithKeys(function ($translation) {
                return [$translation->language_code => $translation->translated_content];
            });

        return response()->json([
            'translations' => $translations,
            'source_language' => config('app.locale', 'en')
        ]);
    }

    public function saveTranslations(TranslationRequest $request, Content $content)
    {
        DB::transaction(function () use ($content, $request) {
            foreach ($request->translations as $languageCode => $translationData) {
                $content->translations()->updateOrCreate(
                    ['language_code' => $languageCode],
                    ['translated_content' => $translationData]
                );
            }
        });

        return response()->json([
            'message' => 'Translations saved successfully',
            'content_id' => $content->id
        ]);
    }

    public function translate(TranslationRequest $request)
    {
        // In a real implementation, this would call a translation service
        // For now, we'll just return the source content as a placeholder
        return response()->json([
            'translation' => $request->content,
            'source_language' => $request->source_language,
            'target_language' => $request->target_language
        ]);
    }
}