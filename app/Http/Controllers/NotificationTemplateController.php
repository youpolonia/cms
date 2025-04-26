<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NotificationTemplateController extends Controller
{
    public function index()
    {
        return response()->json([
            'templates' => config('notification.templates', [])
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $templates = config('notification.templates', []);
        $key = Str::slug($request->name);
        
        $templates[$key] = $validator->validated();
        $this->updateConfig($templates);

        return response()->json([
            'message' => 'Template created successfully',
            'template' => $templates[$key]
        ]);
    }

    public function update(Request $request, $id)
    {
        $templates = config('notification.templates', []);

        if (!isset($templates[$id])) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'subject' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'variables' => 'nullable|array',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $templates[$id] = array_merge($templates[$id], $validator->validated());
        $this->updateConfig($templates);

        return response()->json([
            'message' => 'Template updated successfully',
            'template' => $templates[$id]
        ]);
    }

    protected function updateConfig(array $templates)
    {
        $content = "<?php\n\nreturn " . var_export(['templates' => $templates], true) . ";\n";
        file_put_contents(config_path('notification.php'), $content);
    }
}