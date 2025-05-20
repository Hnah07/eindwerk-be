<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ArtistController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/artists",
     *     summary="Get all artists",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Search artists by name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="country",
     *         in="query",
     *         description="Filter artists by country name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort artists by field (name, country.name) and direction (asc, desc). Example: name:asc",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of artists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string", nullable=true),
     *                     @OA\Property(property="image_url", type="string", nullable=true),
     *                     @OA\Property(
     *                         property="country",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Artist::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('country')) {
            $query->whereHas('country', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->country . '%');
            });
        }

        if ($request->filled('sort')) {
            $sortParts = explode(':', $request->sort);
            $field = $sortParts[0];
            $direction = $sortParts[1] ?? 'asc';

            if ($field === 'country.name') {
                $query->join('countries', 'artists.country_id', '=', 'countries.id')
                    ->orderBy('countries.name', $direction)
                    ->select('artists.*');
            } elseif (in_array($field, ['name']) && in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($field, $direction);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $artists = $query->with('country:id,name')
            ->get()
            ->makeHidden(['country_id']);

        return response()->json([
            'data' => $artists
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/artists",
     *     summary="Create a new artist",
     *     tags={"Artists"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "country_id"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the artist",
     *                 minLength=2,
     *                 maxLength=255
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Description of the artist",
     *                 maxLength=255,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="country_id",
     *                 type="integer",
     *                 description="ID of the artist's country"
     *             ),
     *             @OA\Property(
     *                 property="image_url",
     *                 type="string",
     *                 description="URL of the artist's image",
     *                 nullable=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Artist created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string", nullable=true),
     *                 @OA\Property(property="image_url", type="string", nullable=true),
     *                 @OA\Property(
     *                     property="country",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'image_url' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $artist = Artist::create($request->all());
        $artist->load('country:id,name');
        $artist->makeHidden(['country_id']);

        return response()->json([
            'message' => 'Artist created successfully',
            'data' => $artist
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/artists/{id}",
     *     summary="Get a specific artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="image_url", type="string", nullable=true),
     *             @OA\Property(
     *                 property="country",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $artist = Artist::with('country:id,name')
            ->findOrFail($id)
            ->makeHidden(['country_id']);
        return response()->json($artist);
    }

    /**
     * @OA\Put(
     *     path="/api/artists/{id}",
     *     summary="Update an artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the artist",
     *                 minLength=2,
     *                 maxLength=255
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Description of the artist",
     *                 maxLength=255,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="country_id",
     *                 type="integer",
     *                 description="ID of the artist's country"
     *             ),
     *             @OA\Property(
     *                 property="image_url",
     *                 type="string",
     *                 description="URL of the artist's image",
     *                 nullable=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string", nullable=true),
     *                 @OA\Property(property="image_url", type="string", nullable=true),
     *                 @OA\Property(
     *                     property="country",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $artist = Artist::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:2|max:255',
            'description' => 'nullable|string|max:255',
            'country_id' => 'sometimes|required|exists:countries,id',
            'image_url' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $artist->update($request->all());
        $artist->load('country:id,name');
        $artist->makeHidden(['country_id']);

        return response()->json([
            'message' => 'Artist updated successfully',
            'data' => $artist
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/artists/{id}",
     *     summary="Delete an artist",
     *     tags={"Artists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Artist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artist deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artist not found"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $artist = Artist::findOrFail($id);
        $artist->delete();

        return response()->json([
            'message' => 'Artist successfully deleted'
        ]);
    }
}
