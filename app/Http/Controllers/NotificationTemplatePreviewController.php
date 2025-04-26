<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Blade;

class NotificationTemplatePreviewController extends Controller
{
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:notification_templates,id',
            'variables' => 'nullable|array',
            'format' => 'required|in:html,plaintext'
        ]);

        $template = NotificationTemplate::find($validated['template_id']);
        $variables = $validated['variables'] ?? [];

        // Render the template with variables
        $content = $this->renderTemplate(
            $template->content,
            $variables,
            $validated['format']
        );

        return response()->json([
            'preview' => $content,
            'template' => $template->only(['id', 'name', 'description']),
            'variables_used' => array_keys($variables)
        ]);
    }

    protected function renderTemplate($content, $variables, $format)
    {
        try {
            // For HTML templates, use Blade rendering
            if ($format === 'html') {
                return Blade::render($content, $variables);
            }
            
            // For plaintext, do simple variable replacement
            foreach ($variables as $key => $value) {
                $content = str_replace("{{ $key }}", $value, $content);
                $content = str_replace("{{$key}}", $value, $content);
            }
            
            return $content;
        } catch (\Exception $e) {
            return "Error rendering template: " . $e->getMessage();
        }
    }
}