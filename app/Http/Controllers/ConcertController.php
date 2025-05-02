<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Concert API",
 *     description="API for managing concerts"
 * )
 */
class ConcertController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/concerts",
     *     summary="Get all concerts",
     *     tags={"Concerts"},
     *     @OA\Response(
     *         response=200,
     *         description="List of concerts",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="text"),
     *                 @OA\Property(property="date", type="string", format="date")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Concert::all();
        // beschikbaar door die use te gebruiken vanboven
    }

    /**
     * @OA\Post(
     *     path="/api/concerts",
     *     summary="Create a new concert",
     *     tags={"Concerts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "date"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="text"),
     *             @OA\Property(property="date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Concert created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="text"),
     *             @OA\Property(property="date", type="string", format="date")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        return Concert::create($request->all());
    }

    /**
     * @OA\Get(
     *     path="/api/concerts/{id}",
     *     summary="Get a specific concert",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Concert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Concert details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="text"),
     *             @OA\Property(property="date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        return Concert::find($id);
    }

    /**
     * @OA\Put(
     *     path="/api/concerts/{id}",
     *     summary="Update a concert",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Concert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="text"),
     *             @OA\Property(property="date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Concert updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="text"),
     *             @OA\Property(property="date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert not found"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $concert = Concert::find($id);
        $concert->update($request->all());
        return $concert;
    }

    /**
     * @OA\Delete(
     *     path="/api/concerts/{id}",
     *     summary="Delete a concert",
     *     tags={"Concerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Concert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Concert deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $concert = Concert::find($id);
        $concert->delete();
        return response()->json(null, 204);
    }
}
