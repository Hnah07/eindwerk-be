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
     *         name="year",
     *         in="query",
     *         description="Filter concerts by year",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         example="2024"
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter concerts by type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"concert", "festival", "dj set", "club show", "theater show"}),
     *         example="festival"
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort concerts by field (name, year) and direction (asc, desc). Example: year:desc",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         example="year:desc"
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
     *                 @OA\Property(property="year", type="integer", example=2024),
     *                 @OA\Property(property="type", type="string", example="festival")
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

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('sort')) {
            $sortParts = explode(':', $request->sort);
            $field = $sortParts[0];
            $direction = $sortParts[1] ?? 'asc';

            if (in_array($field, ['name', 'year']) && in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($field, $direction);
            }
        } else {
            $query->orderBy('year', 'desc');
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
     *             required={"name", "description", "year", "type"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the concert",
     *                 example="Summer Festival 2024",
     *                 minLength=3,
     *                 maxLength=255
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Detailed description of the concert including venue, artists, and other important information",
     *                 example="A fantastic summer music festival with multiple stages featuring top artists from around the world. The event will include food vendors, merchandise stands, and VIP areas.",
     *                 minLength=10
     *             ),
     *             @OA\Property(
     *                 property="year",
     *                 type="integer",
     *                 description="Year of the concert",
     *                 example=2024
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="Type of the concert",
     *                 enum={"concert", "festival", "dj set", "club show", "theater show"},
     *                 example="festival"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Concert created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Summer Festival 2024"),
     *             @OA\Property(property="description", type="string", example="A fantastic summer music festival with multiple stages"),
     *             @OA\Property(property="year", type="integer", example=2024),
     *             @OA\Property(property="type", type="string", example="festival"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-20T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-20T10:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="array",
     *                     @OA\Items(type="string", example="The description field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="year",
     *                     type="array",
     *                     @OA\Items(type="string", example="The year field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="array",
     *                     @OA\Items(type="string", example="The type field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10',
            'year' => 'required|integer|min:1900|max:2100',
            'type' => 'required|string|in:concert,festival,dj set,club show,theater show'
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
     *             @OA\Property(property="year", type="integer"),
     *             @OA\Property(property="type", type="string")
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
     *             @OA\Property(property="year", type="integer"),
     *             @OA\Property(property="type", type="string", enum={"concert", "festival", "dj set", "club show", "theater show"})
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
     *             @OA\Property(property="year", type="integer"),
     *             @OA\Property(property="type", type="string")
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
            'name' => 'sometimes|string|min:3|max:255',
            'description' => 'sometimes|string|min:10',
            'year' => 'sometimes|integer|min:1900|max:2100',
            'type' => 'sometimes|string|in:concert,festival,dj set,club show,theater show'
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
     *         response=200,
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
        return response()->json(['message' => 'Concert successfully deleted'], 200);
    }
}
