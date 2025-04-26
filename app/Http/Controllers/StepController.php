<?php

namespace App\Http\Controllers;

use App\Models\ErrorResolutionStep;
use App\Models\ErrorResolutionWorkflow;
use Illuminate\Http\Request;

class StepController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ErrorResolutionStep::class, 'step');
    }

    public function create(ErrorResolutionWorkflow $workflow)
    {
        $this->authorize('create', [ErrorResolutionStep::class, $workflow]);
        return view('steps.create', compact('workflow'));
    }

    public function store(Request $request, ErrorResolutionWorkflow $workflow)
    {
        $this->authorize('create', [ErrorResolutionStep::class, $workflow]);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'order' => 'required|integer|min:1'
        ]);

        $workflow->steps()->create($validated);

        return redirect()->route('workflows.show', $workflow);
    }

    public function edit(ErrorResolutionWorkflow $workflow, ErrorResolutionStep $step)
    {
        $this->authorize('update', [$step, $workflow]);
        return view('steps.edit', compact('workflow', 'step'));
    }

    public function update(Request $request, ErrorResolutionWorkflow $workflow, ErrorResolutionStep $step)
    {
        $this->authorize('update', [$step, $workflow]);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'order' => 'required|integer|min:1'
        ]);

        $step->update($validated);

        return redirect()->route('workflows.show', $workflow);
    }

    public function destroy(ErrorResolutionWorkflow $workflow, ErrorResolutionStep $step)
    {
        $this->authorize('delete', [$step, $workflow]);
        $step->delete();
        return redirect()->route('workflows.show', $workflow);
    }
}