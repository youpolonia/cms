<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ContentVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content_id' => 'sometimes|exists:contents,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $query = Branch::query()->with(['rootVersion', 'currentHead']);

        if ($request->has('content_id')) {
            $query->whereHas('rootVersion', function ($q) use ($request) {
                $q->where('content_id', $request->input('content_id'));
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'version_id' => 'required|exists:content_versions,id',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $version = ContentVersion::find($request->input('version_id'));
        
        $branch = new Branch([
            'name' => $request->input('name'),
            'root_version_id' => $version->id,
            'current_head_id' => $version->id,
        ]);

        if (!$branch->save()) {
            return response()->json([
                'message' => 'Failed to create branch'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($branch->load('rootVersion', 'currentHead'), Response::HTTP_CREATED);
    }

    public function show(Branch $branch)
    {
        return response()->json($branch->load([
            'rootVersion',
            'currentHead',
            'rootVersion.diffs',
            'rootVersion.contentItem'
        ]));
    }

    public function update(Request $request, Branch $branch)
    {
        $validator = Validator::make($request->all(), [
            'current_head_id' => 'required|exists:content_versions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $branch->update([
            'current_head_id' => $request->input('current_head_id')
        ]);

        return response()->json($branch->fresh(['currentHead', 'rootVersion']));
    }

    public function destroy(Branch $branch)
    {
        try {
            $branch->delete();
            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete branch',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}