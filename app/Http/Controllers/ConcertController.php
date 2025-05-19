<?php

namespace App\Http\Controllers;

use App\Enums\ConcertSource;
use App\Enums\ConcertStatus;
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
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Filter concerts by year",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter concerts by type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"concert", "festival", "dj set", "club show", "theater show"})
     *     ),
     *     @OA\Parameter(
     *         name="location_name",
     *         in="query",
     *         description="Filter by location name (case-insensitive partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter concerts from this date (format: Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter concerts until this date (format: Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort concerts by field (name, year) and direction (asc, desc). Example: year:desc",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of concerts with their occurrences at valid locations",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="year", type="integer"),
     *                     @OA\Property(property="type", type="string"),
     *                     @OA\Property(property="source", type="string", enum={"manual", "api"}),
     *                     @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"}),
     *                     @OA\Property(
     *                         property="occurrences",
     *                         type="array",
     *                         description="List of occurrences with valid locations",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(
     *                                 property="location",
     *                                 type="object",
     *                                 description="Location details (only included for valid locations)",
     *                                 @OA\Property(property="id", type="integer"),
     *                                 @OA\Property(property="name", type="string")
     *                             ),
     *                             @OA\Property(property="date", type="string", format="date-time")
     *                         )
     *                     )
     *                 )
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

        if ($request->has('location_name')) {
            $query->whereHas('locations', function ($q) use ($request) {
                $q->where('locations.name', 'LIKE', '%' . $request->location_name . '%');
            });
        }

        if ($request->has('date_from') || $request->has('date_to')) {
            $query->whereHas('locations', function ($q) use ($request) {
                if ($request->has('date_from')) {
                    $q->where('concert_occurrences.date', '>=', $request->date_from);
                }
                if ($request->has('date_to')) {
                    $q->where('concert_occurrences.date', '<=', $request->date_to);
                }
            });
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

        $concerts = $query->with(['occurrences' => function ($query) {
            $query->with('location:id,name')
                ->whereNotNull('location_id');
        }])->get();

        $transformedConcerts = $concerts->map(function ($concert) {
            $occurrences = $concert->occurrences->map(function ($occurrence) {
                if (!$occurrence->location) {
                    return null;
                }

                return [
                    'location' => [
                        'id' => $occurrence->location->id,
                        'name' => $occurrence->location->name
                    ],
                    'date' => $occurrence->date
                ];
            })->filter();

            return [
                'id' => $concert->id,
                'name' => $concert->name,
                'description' => $concert->description,
                'year' => $concert->year,
                'type' => $concert->type,
                'source' => $concert->source,
                'status' => $concert->status,
                'occurrences' => $occurrences
            ];
        });

        return response()->json([
            'data' => $transformedConcerts
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/concerts",
     *     summary="Create a new concert",
     *     tags={"Concerts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "year", "type", "location_id", "date"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the concert",
     *                 minLength=3,
     *                 maxLength=255
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Detailed description of the concert",
     *                 minLength=10
     *             ),
     *             @OA\Property(
     *                 property="year",
     *                 type="integer",
     *                 description="Year of the concert"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="Type of the concert",
     *                 enum={"concert", "festival", "dj set", "club show", "theater show"}
     *             ),
     *             @OA\Property(
     *                 property="source",
     *                 type="string",
     *                 description="Source of the concert",
     *                 enum={"manual", "api"}
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 description="Status of the concert",
     *                 enum={"pending_approval", "verified", "rejected"}
     *             ),
     *             @OA\Property(
     *                 property="location_id",
     *                 type="integer",
     *                 description="ID of the location where the concert will take place"
     *             ),
     *             @OA\Property(
     *                 property="date",
     *                 type="string",
     *                 format="date-time",
     *                 description="Date and time of the concert"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Concert created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="year", type="integer"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="source", type="string"),
     *                 @OA\Property(property="status", type="string")
     *             )
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
        // 1. Validate input
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10',
            'year' => 'required|integer|min:1900|max:2100',
            'type' => 'required|string|in:concert,festival,dj set,club show,theater show',
            'source' => 'required|string|in:manual,api',
            'status' => 'required|string|in:pending_approval,verified,rejected',
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date'
        ]);

        // 2. Create the concert
        $concert = Concert::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'year' => $validated['year'],
            'type' => $validated['type'],
            'source' => $validated['source'],
            'status' => $validated['status']
        ]);

        // 3. Attach location with date via pivot table
        $concert->locations()->attach($validated['location_id'], [
            'date' => $validated['date']
        ]);

        return response()->json([
            'message' => 'Concert created successfully',
            'data' => $concert
        ], 201);
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
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="source", type="string"),
     *             @OA\Property(property="status", type="string")
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
     *             @OA\Property(property="type", type="string", enum={"concert", "festival", "dj set", "club show", "theater show"}),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}),
     *             @OA\Property(property="status", type="string", enum={"pending_approval", "verified", "rejected"})
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
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="source", type="string"),
     *             @OA\Property(property="status", type="string")
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
            'type' => 'sometimes|string|in:concert,festival,dj set,club show,theater show',
            'source' => 'sometimes|string|in:manual,api',
            'status' => 'sometimes|string|in:pending_approval,verified,rejected'
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
