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
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Search concerts by name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         example="Metallica"
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter concerts by specific date (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date"),
     *         example="2024-03-20"
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Filter concerts from this date onwards (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date"),
     *         example="2025-06-01"
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Filter concerts up to this date (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date"),
     *         example="2025-12-31"
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort concerts by field (name, date) and direction (asc, desc). Example: date:desc",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         example="date:desc"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of concerts",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Summer Festival 2024"),
     *                 @OA\Property(property="description", type="string", example="A fantastic summer music festival with multiple stages"),
     *                 @OA\Property(property="date", type="string", format="date", example="2024-07-15")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Concert::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        if ($request->filled('sort')) {
            $sortParts = explode(':', $request->sort);
            $field = $sortParts[0];
            $direction = $sortParts[1] ?? 'asc';

            if (in_array($field, ['name', 'date']) && in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($field, $direction);
            }
        } else {
            $query->orderBy('date', 'asc');
        }

        $concerts = $query->get();
        return response()->json($concerts, 200);
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
     *             @OA\Property(property="description", type="string"),
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
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date'
        ]);

        $concert = Concert::create($validated);
        return response()->json($concert, 201);
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
     *             @OA\Property(property="description", type="string"),
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
        $concert = Concert::find($id);

        if (!$concert) {
            return response()->json(['message' => 'Concert not found'], 404);
        }

        return response()->json($concert, 200);
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
     *             @OA\Property(property="description", type="string"),
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
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Concert not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $concert = Concert::find($id);

        if (!$concert) {
            return response()->json(['message' => 'Concert not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date'
        ]);

        $concert->update($validated);
        return response()->json($concert, 200);
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

        if (!$concert) {
            return response()->json(['message' => 'Concert not found'], 404);
        }

        $concert->delete();
        return response()->json(null, 204);
    }
}
