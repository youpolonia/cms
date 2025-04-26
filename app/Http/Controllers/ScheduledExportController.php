<?php

namespace App\Http\Controllers;

use App\Models\ScheduledExport;
use App\Http\Requests\StoreScheduledExportRequest;
use App\Http\Requests\UpdateScheduledExportRequest;
use App\Jobs\ExportAnalyticsData;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Scheduled Exports',
    description: 'Manage scheduled data exports'
)]
class ScheduledExportController extends Controller
{
    #[OA\Get(
        path: '/api/scheduled-exports',
        operationId: 'getScheduledExports',
        description: 'List all scheduled exports',
        tags: ['Scheduled Exports'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ScheduledExport')
                )
            )
        ]
    )]
    public function index(): View
    {
        return view('scheduled-exports.index', [
            'exports' => ScheduledExport::with('user')
                ->orderBy('next_run_at')
                ->paginate(20)
        ]);
    }

    #[OA\Get(
        path: '/api/scheduled-exports/create',
        operationId: 'getCreateScheduledExportForm',
        description: 'Get form for creating a new scheduled export',
        tags: ['Scheduled Exports'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Form data'
            )
        ]
    )]
    public function create(): View
    {
        return view('scheduled-exports.create');
    }

    #[OA\Post(
        path: '/api/scheduled-exports',
        operationId: 'createScheduledExport',
        description: 'Create a new scheduled export',
        tags: ['Scheduled Exports'],
        requestBody: new OA\RequestBody(
            description: 'Export data',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ScheduledExport')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Export created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ScheduledExport')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
    public function store(StoreScheduledExportRequest $request): RedirectResponse
    {
        $export = ScheduledExport::create($request->validated());

        return redirect()
            ->route('scheduled-exports.show', $export)
            ->with('success', 'Export scheduled successfully');
    }

    #[OA\Get(
        path: '/api/scheduled-exports/{id}',
        operationId: 'getScheduledExport',
        description: 'Get a specific scheduled export',
        tags: ['Scheduled Exports'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of export to retrieve',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/ScheduledExport')
            ),
            new OA\Response(
                response: 404,
                description: 'Export not found'
            )
        ]
    )]
    public function show(ScheduledExport $export): View
    {
        return view('scheduled-exports.show', compact('export'));
    }

    #[OA\Get(
        path: '/api/scheduled-exports/{id}/edit',
        operationId: 'getEditScheduledExportForm',
        description: 'Get form for editing an existing scheduled export',
        tags: ['Scheduled Exports'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of export to edit',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Form data'
            ),
            new OA\Response(
                response: 404,
                description: 'Export not found'
            )
        ]
    )]
    public function edit(ScheduledExport $export): View
    {
        return view('scheduled-exports.edit', compact('export'));
    }

    #[OA\Put(
        path: '/api/scheduled-exports/{id}',
        operationId: 'updateScheduledExport',
        description: 'Update an existing scheduled export',
        tags: ['Scheduled Exports'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of export to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Updated export data',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ScheduledExport')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Export updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ScheduledExport')
            ),
            new OA\Response(
                response: 404,
                description: 'Export not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
    public function update(UpdateScheduledExportRequest $request, ScheduledExport $export): RedirectResponse
    {
        $export->update($request->validated());

        return redirect()
            ->route('scheduled-exports.show', $export)
            ->with('success', 'Export updated successfully');
    }

    #[OA\Delete(
        path: '/api/scheduled-exports/{id}',
        operationId: 'deleteScheduledExport',
        description: 'Delete a scheduled export',
        tags: ['Scheduled Exports'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of export to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Export deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Export not found'
            )
        ]
    )]
    public function destroy(ScheduledExport $export): RedirectResponse
    {
        $export->delete();

        return redirect()
            ->route('scheduled-exports.index')
            ->with('success', 'Export deleted successfully');
    }

    #[OA\Post(
        path: '/api/scheduled-exports/{id}/run-now',
        operationId: 'runScheduledExportNow',
        description: 'Trigger immediate execution of a scheduled export',
        tags: ['Scheduled Exports'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of export to run',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 202,
                description: 'Export queued for processing'
            ),
            new OA\Response(
                response: 404,
                description: 'Export not found'
            )
        ]
    )]
    public function runNow(ScheduledExport $export): RedirectResponse
    {
        ExportAnalyticsData::dispatch($export);

        return back()
            ->with('success', 'Export queued for immediate processing');
    }
}