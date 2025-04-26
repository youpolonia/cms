<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Knowledge",
 *     description="Knowledge base content operations"
 * )
 */
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class KnowledgeController extends Controller
{
    const MAX_CHUNK_SIZE = 2 * 1024 * 1024; // 2MB
    const MAX_TOTAL_SIZE = 20 * 1024 * 1024; // 20MB
    const COMPRESSION_THRESHOLD = 1024 * 1024; // 1MB
    
    private $maxChunkSize;
    private $maxTotalSize;
    private $redisPrefix;
    private $chunkTtl;
    private $memoryOptimization;

    public function __construct()
    {
        $this->maxChunkSize = config('mcp.knowledge.max_chunk_size');
        $this->maxTotalSize = config('mcp.knowledge.max_total_size');
        $this->redisPrefix = config('mcp.knowledge.redis_prefix');
        $this->chunkTtl = config('mcp.knowledge.chunk_ttl');
        $this->memoryOptimization = config('mcp.knowledge.memory_optimization');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required'
        ]);

        if (is_string($validated['value']) && strlen($validated['value']) > self::MAX_TOTAL_SIZE) {
            return response()->json([
                'success' => false,
                'message' => 'Value exceeds maximum size of '.self::MAX_TOTAL_SIZE.' bytes'
            ], 413);
        }

        // For small values, store directly
        if (is_string($validated['value']) && strlen($validated['value']) <= $this->maxChunkSize) {
            Cache::put($this->redisPrefix.$validated['key'], $validated['value'], $this->chunkTtl);
            return response()->json([
                'success' => true,
                'key' => $validated['key'],
                'chunks' => 1
            ]);
        }

        // For large values, compress and split into chunks
        $valueToStore = $validated['value'];
        $compressed = false;
        
        if (strlen($valueToStore) > self::COMPRESSION_THRESHOLD) {
            $valueToStore = gzcompress($valueToStore, 6);
            $compressed = true;
        }

        $chunks = str_split($valueToStore, self::MAX_CHUNK_SIZE);
        $chunkCount = count($chunks);

        Redis::transaction(function($redis) use ($validated, $chunks, $chunkCount, $compressed) {
            $redis->del($this->redisPrefix.$validated['key'].':chunks');
            
            foreach ($chunks as $index => $chunk) {
                $redis->hset($this->redisPrefix.$validated['key'].':chunks', $index, $chunk);
            }
            
            $redis->set($this->redisPrefix.$validated['key'].':meta', json_encode([
                'chunks' => $chunkCount,
                'created_at' => now()->toDateTimeString(),
                'ttl' => $this->chunkTtl,
                'compressed' => $compressed
            ]), 'EX', $this->chunkTtl);
        });

        return response()->json([
            'success' => true,
            'key' => $validated['key'],
            'chunks' => $chunkCount
        ]);
    }

    public function retrieve($key)
    {
        $meta = Cache::get($this->redisPrefix.$key.':meta');

        if ($meta) {
            // Handle chunked value
            $meta = json_decode($meta, true);
            $chunks = Redis::hgetall($this->redisPrefix.$key.':chunks');
            
            if (count($chunks) !== $meta['chunks']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incomplete knowledge chunks'
                ], 500);
            }

            ksort($chunks);
            $value = implode('', $chunks);
            
            if ($meta['compressed'] ?? false) {
                $value = gzuncompress($value);
            }
        } else {
            // Handle single value
            $value = Cache::get($this->redisPrefix.$key);
        }

        if (is_null($value)) {
            return response()->json([
                'success' => false,
                'message' => 'Key not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'key' => $key,
            'value' => $value,
            'chunks' => $meta['chunks'] ?? 1
        ]);
    }

    /**
     * @OA\Get(
     *     path="/knowledge",
     *     tags={"Knowledge"},
     *     summary="List all knowledge content",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Content")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json([
            'data' => app('knowledge')->getKnowledgeContent()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/knowledge/{id}",
     *     tags={"Knowledge"},
     *     summary="Get knowledge content by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of knowledge content",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Content"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function show($id)
    {
        $content = app('knowledge')->getKnowledgeContent()
            ->firstWhere('id', $id);

        if (!$content) {
            return response()->json([
                'message' => 'Knowledge content not found'
            ], 404);
        }

        return response()->json([
            'data' => $content
        ]);
    }

    /**
     * @OA\Get(
     *     path="/knowledge/search",
     *     tags={"Knowledge"},
     *     summary="Search knowledge content",
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of results",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Content")
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:3',
            'limit' => 'sometimes|integer|min:1|max:100'
        ]);

        $results = app('knowledge')->searchKnowledgeContent(
            $validated['query'],
            $validated['limit'] ?? 10
        );

        return response()->json([
            'data' => $results
        ]);
    }
}