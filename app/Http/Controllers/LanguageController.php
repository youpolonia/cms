<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LanguageController extends Controller
{
    public function index()
    {
        return response()->json([
            'languages' => config('app.enabled_languages')
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:2|unique:languages,code',
            'name' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $languages = config('app.enabled_languages');
        $languages[$request->code] = $request->name;
        config(['app.enabled_languages' => $languages]);

        return response()->json([
            'message' => 'Language added successfully',
            'language' => [
                'code' => $request->code,
                'name' => $request->name
            ]
        ]);
    }

    public function update(Request $request, $code)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $languages = config('app.enabled_languages');
        if (!array_key_exists($code, $languages)) {
            return response()->json(['error' => 'Language not found'], 404);
        }

        $languages[$code] = $request->name;
        config(['app.enabled_languages' => $languages]);

        return response()->json([
            'message' => 'Language updated successfully',
            'language' => [
                'code' => $code,
                'name' => $request->name
            ]
        ]);
    }

    public function destroy($code)
    {
        $languages = config('app.enabled_languages');
        if (!array_key_exists($code, $languages)) {
            return response()->json(['error' => 'Language not found'], 404);
        }

        unset($languages[$code]);
        config(['app.enabled_languages' => $languages]);

        return response()->json([
            'message' => 'Language removed successfully'
        ]);
    }
}