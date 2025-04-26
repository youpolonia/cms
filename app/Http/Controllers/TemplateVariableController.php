<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateVariableController extends Controller
{
    public function index()
    {
        $documentation = $this->getDocumentation();
        $variables = $this->parseVariables($documentation);

        return response()->json([
            'variables' => $variables,
            'documentation' => $documentation
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $documentation = $this->getDocumentation();
        $variables = $this->parseVariables($documentation);

        $filtered = collect($variables)->filter(function($var) use ($query) {
            return Str::contains(strtolower($var['name']), strtolower($query)) || 
                   Str::contains(strtolower($var['description']), strtolower($query));
        });

        return response()->json([
            'variables' => $filtered->values(),
            'count' => $filtered->count()
        ]);
    }

    protected function getDocumentation()
    {
        $files = [
            'template_variables.md' => Storage::get('docs/template_variables.md'),
            'custom_variables.md' => Storage::exists('docs/custom_variables.md') ? 
                Storage::get('docs/custom_variables.md') : ''
        ];

        return implode("\n\n", $files);
    }

    protected function parseVariables($content)
    {
        $pattern = '/### `(\w+)`\n\n(.*?)(?=\n\n###|$)/s';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        return collect($matches)->map(function($match) {
            return [
                'name' => $match[1],
                'description' => trim($match[2])
            ];
        })->toArray();
    }
}