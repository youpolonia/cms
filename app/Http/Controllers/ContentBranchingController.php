<?php

namespace App\Http\Controllers;

use App\Repositories\ContentVersionRepository;
use Illuminate\Http\Request;

class ContentBranchingController extends Controller
{
    public function __construct(
        private ContentVersionRepository $versionRepository
    ) {}

    public function store(Request $request, string $contentId)
    {
        // Validate request data
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'source_version' => 'required|string|uuid'
        ]);

        // Create new branch  
        $branch = $this->versionRepository->createBranch(
            $contentId,
            $validated['branch_name'], 
            $validated['source_version']
        );

        return response()->json([
            'data' => $branch,
            'message' => __('Branch created successfully')
        ], 201);
    }

    public function index(string $contentId)
    {
        $branches = $this->versionRepository->getAllBranches($contentId);

        return response()->json([
            'data' => $branches
        ]);
    }

    public function merge(Request $request, string $contentId, string $branchId)
    {
        $validated = $request->validate([
            'target_branch' => 'required|string|uuid',
            'strategy' => 'in:merge,rebase,overwrite'
        ]);

        $result = $this->versionRepository->mergeBranch(
            $contentId,
            $branchId,
            $validated['target_branch'],
            $validated['strategy'] ?? 'merge'
        );

        return response()->json([
            'data' => $result,
            'message' => __('Branch merged successfully')
        ]);
    }
}