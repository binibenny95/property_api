<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\NodeRequest;
use App\Models\Node;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\NodeResource;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Nodes",
 *     description="API Endpoints for managing nodes (Corporation, Building, Property, Tenancy Period, Tenant)"
 * )
 */
class NodeController extends Controller
{
//    public function __construct()
//     {
//         $this->middleware('auth:sanctum');
//     }


    /**
     * @OA\Post(
     *     path="/api/nodes",
     *     summary="Create a new node",
     *     description="Create a new node of type Corporation, Building, Property, Tenancy Period, or Tenant",
     *     tags={"Nodes"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Test Corporation"),
     *             @OA\Property(property="type", type="string", enum={"Corporation", "Building", "Property", "Tenancy Period", "Tenant"}, example="Corporation"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="relationship_to_parent", type="string", nullable=true, maxLength=255),
     *             @OA\Property(property="zip_code", type="string", nullable=true, description="Required for Building type", example="12345"),
     *             @OA\Property(property="monthly_rent", type="number", format="float", nullable=true, description="Required for Property type", example=1500.00),
     *             @OA\Property(property="tenancy_active", type="boolean", nullable=true, description="Required for Tenancy Period type", example=true),
     *             @OA\Property(property="move_in_date", type="string", format="date", nullable=true, description="Required for Tenant type", example="2025-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Node created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Test Corporation"),
     *                 @OA\Property(property="type", type="string", example="Corporation"),
     *                 @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="height", type="integer", example=0),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-08-12T10:30:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-08-12T10:30:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="error", type="string", example="Invalid parent-child relationship")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="Tenancy rules violation"),
     *                     @OA\Property(property="message", type="string", example="Only one active tenancy period is allowed per property")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Only admins can create nodes.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function store(NodeRequest $request): JsonResponse
    {
        Log::info('=== NodeController::store called ===', [
        'method' => $request->method(),
        'url' => $request->url(),
        'user' => auth()->id(),
        'input' => $request->all()
    ]);

        $this->authorize('create', Node::class);
        $node = new Node($request->validated());

        if (!$node->validateParentType()) {
            return response()->json([
                'error' => 'Invalid parent-child relationship'
            ], 422);
        }

          if (!$node->validateTenancyRules()) {

            $errorMessage = match($node->type) {
                 'Tenancy Period' => 'Only one active tenancy period is allowed per property',
                 'Tenant' => 'Maximum of 4 tenants allowed per tenancy period',
                 default => 'Tenancy rules validation failed'
          };
            return response()->json([
                'status' => 'Tenancy rules violation',
                'message' => $errorMessage
            ], 422);
        }

        $node->height = $request->parent_id
            ? Node::findOrFail($request->parent_id)->height + 1
            : 0;
        $node->created_by = auth()->id();
        $node->save();
        return (new NodeResource($node))->response()->setStatusCode(201);

    }

     /**
     * @OA\Get(
     *     path="/api/nodes/{node}/children",
     *     summary="Get children of a node",
     *     description="Retrieve all direct children of a specific node",
     *     operationId="getNodeChildren",
     *     tags={"Nodes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="node",
     *         in="path",
     *         required=true,
     *         description="Node ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Children retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Building A"),
     *                     @OA\Property(property="type", type="string", example="Building"),
     *                     @OA\Property(property="parent_id", type="integer", example=1),
     *                     @OA\Property(property="height", type="integer", example=1),
     *                     @OA\Property(property="zip_code", type="string", nullable=true, example="12345"),
     *                     @OA\Property(property="created_at", type="string", format="datetime"),
     *                     @OA\Property(property="updated_at", type="string", format="datetime")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Node not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Node]")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getChildren(Node $node): JsonResponse
    {
        $this->authorize('view', $node);
        $children = $node->children()->get();
        return NodeResource::collection($children)->response();
    }

     /**
     * @OA\Put(
     *     path="/api/nodes/{node}/parent",
     *     summary="Update node parent",
     *     description="Change the parent of an existing node",
     *     operationId="updateNodeParent",
     *     tags={"Nodes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="node",
     *         in="path",
     *         required=true,
     *         description="Node ID",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "parent_id"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Property Unit 101"),
     *             @OA\Property(property="type", type="string", enum={"Corporation", "Building", "Property", "Tenancy Period", "Tenant"}, example="Property"),
     *             @OA\Property(property="parent_id", type="integer", example=2),
     *             @OA\Property(property="relationship_to_parent", type="string", nullable=true, maxLength=255),
     *             @OA\Property(property="zip_code", type="string", nullable=true, description="Required for Building type"),
     *             @OA\Property(property="monthly_rent", type="number", format="float", nullable=true, description="Required for Property type", example=1500.00),
     *             @OA\Property(property="tenancy_active", type="boolean", nullable=true, description="Required for Tenancy Period type"),
     *             @OA\Property(property="move_in_date", type="string", format="date", nullable=true, description="Required for Tenant type")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parent updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="name", type="string", example="Property Unit 101"),
     *                 @OA\Property(property="type", type="string", example="Property"),
     *                 @OA\Property(property="parent_id", type="integer", example=2),
     *                 @OA\Property(property="height", type="integer", example=2),
     *                 @OA\Property(property="monthly_rent", type="number", format="float", example=1500.00),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="error", type="string", example="Invalid parent-child relationship")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="Tenancy rules violation"),
     *                     @OA\Property(property="message", type="string", example="Maximum of 4 tenants allowed per tenancy period")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Only admins can update nodes.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Node not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Node]")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function updateParent(NodeRequest $request, Node $node): JsonResponse
    {
        $node->parent_id = $request->parent_id;

        if (!$node->validateParentType()) {
            return response()->json([
                'error' => 'Invalid parent-child relationship'
            ], 422);
        }

        if (!$node->validateTenancyRules()) {

            $errorMessage = match($node->type) {
                 'Tenancy Period' => 'Only one active tenancy period is allowed per property',
                 'Tenant' => 'Maximum of 4 tenants allowed per tenancy period',
                 default => 'Tenancy rules validation failed'
          };
            return response()->json([
                'status' => 'Tenancy rules violation',
                'message' => $errorMessage
            ], 422);
        }

        $node->height = Node::findOrFail($request->parent_id)->height + 1;
        $node->save();

        return (new NodeResource($node))->response();
    }
}
