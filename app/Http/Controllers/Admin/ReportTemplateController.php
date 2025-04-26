<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportTemplateController extends Controller
{
    /**
     * Display a listing of report templates.
     */
    public function index(Request $request)
    {
        $query = ReportTemplate::query();

        // Apply search filter
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        // Apply category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Paginate results
        $templates = $query->paginate(10);

        return response()->json([
            'data' => $templates,
            'categories' => ReportTemplate::distinct('category')->pluck('category')
        ]);
    }

    /**
     * Store a newly created report template.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'fields' => 'required|array',
            'fields.*.id' => 'required|string',
            'fields.*.name' => 'required|string',
            'fields.*.type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $template = ReportTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'fields' => $request->fields,
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Template created successfully',
            'data' => $template
        ], 201);
    }

    /**
     * Display the specified report template.
     */
    public function show(ReportTemplate $reportTemplate)
    {
        return response()->json([
            'data' => $reportTemplate
        ]);
    }

    /**
     * Update the specified report template.
     */
    public function update(Request $request, ReportTemplate $reportTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'fields' => 'required|array',
            'fields.*.id' => 'required|string',
            'fields.*.name' => 'required|string',
            'fields.*.type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $reportTemplate->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'fields' => $request->fields,
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Template updated successfully',
            'data' => $reportTemplate
        ]);
    }

    /**
     * Remove the specified report template.
     */
    public function destroy(ReportTemplate $reportTemplate)
    {
        $reportTemplate->delete();

        return response()->json([
            'message' => 'Template deleted successfully'
        ]);
    }

    /**
     * Get available fields for templates
     */
    public function availableFields()
    {
        return response()->json([
            'data' => [
                [
                    'id' => 'notification_id',
                    'name' => 'Notification ID',
                    'type' => 'text',
                    'sampleData' => 'NTF-12345'
                ],
                [
                    'id' => 'title',
                    'name' => 'Title',
                    'type' => 'text',
                    'sampleData' => 'System Update'
                ],
                // Additional fields would be added here
            ]
        ]);
    }
}