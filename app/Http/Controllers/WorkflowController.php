<?php

namespace App\Http\Controllers;

use App\Models\ErrorResolutionWorkflow;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index()
    {
        return view('workflows.index', [
            'workflows' => ErrorResolutionWorkflow::with('steps')->get()
        ]);
    }

    public function create()
    {
        return view('workflows.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'error_category_id' => 'required|exists:error_categories,id'
        ]);

        $workflow = ErrorResolutionWorkflow::create($validated);

        return redirect()->route('workflows.show', $workflow);
    }

    public function show(ErrorResolutionWorkflow $workflow)
    {
        return view('workflows.show', [
            'workflow' => $workflow->load('steps')
        ]);
    }

    public function edit(ErrorResolutionWorkflow $workflow)
    {
        return view('workflows.edit', compact('workflow'));
    }

    public function update(Request $request, ErrorResolutionWorkflow $workflow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $workflow->update($validated);

        return redirect()->route('workflows.show', $workflow);
    }

    public function destroy(ErrorResolutionWorkflow $workflow)
    {
        $workflow->delete();
        return redirect()->route('workflows.index');
    }
}