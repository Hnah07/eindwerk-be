<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Country;
use App\Enums\LocationSource;
use App\Enums\LocationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/locations",
     *     summary="Get all locations",
     *     description="Returns a list of all locations. If a search parameter is provided, it will filter locations by name, city, or country name.",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Optional: Search locations by name, city, or country name. If not provided, all locations will be returned.",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         example="Antwerp"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of locations",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Royal Concert Hall"),
     *                 @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *                 @OA\Property(property="longitude", type="number", format="float", example=4.4041),
     *                 @OA\Property(property="latitude", type="number", format="float", example=51.2194),
     *                 @OA\Property(property="street", type="string", example="Koningstraat"),
     *                 @OA\Property(property="housenr", type="string", example="10"),
     *                 @OA\Property(property="zipcode", type="string", example="2000"),
     *                 @OA\Property(property="city", type="string", example="Antwerp"),
     *                 @OA\Property(property="website", type="string", format="uri", example="https://www.royalconcerthall.be", nullable=true),
     *                 @OA\Property(
     *                     property="country",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Belgium"),
     *                     @OA\Property(property="code", type="string", example="BE")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Location::with(['country' => function ($query) {
            $query->select('id', 'name', 'code');
        }]);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('country', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $locations = $query->get();
        return response()->json($locations->makeHidden(['created_at', 'updated_at', 'country_id']));
    }

    /**
     * @OA\Post(
     *     path="/api/locations",
     *     summary="Create a new location",
     *     tags={"Locations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "longitude", "latitude", "street", "housenr", "zipcode", "city", "country_id"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Royal Concert Hall",
     *                 description="Name of the location (required)"
     *             ),
     *             @OA\Property(
     *                 property="source",
     *                 type="string",
     *                 enum={"manual", "api"},
     *                 example="manual",
     *                 description="Source of the location (defaults to manual)"
     *             ),
     *             @OA\Property(
     *                 property="longitude",
     *                 type="number",
     *                 format="float",
     *                 example=4.4041,
     *                 description="Longitude coordinate (required)"
     *             ),
     *             @OA\Property(
     *                 property="latitude",
     *                 type="number",
     *                 format="float",
     *                 example=51.2194,
     *                 description="Latitude coordinate (required)"
     *             ),
     *             @OA\Property(
     *                 property="street",
     *                 type="string",
     *                 example="Koningstraat",
     *                 description="Street name (required)"
     *             ),
     *             @OA\Property(
     *                 property="housenr",
     *                 type="string",
     *                 example="10",
     *                 description="House number (required)"
     *             ),
     *             @OA\Property(
     *                 property="zipcode",
     *                 type="string",
     *                 example="2000",
     *                 description="ZIP/Postal code (required)"
     *             ),
     *             @OA\Property(
     *                 property="city",
     *                 type="string",
     *                 example="Antwerp",
     *                 description="City name (required)"
     *             ),
     *             @OA\Property(
     *                 property="website",
     *                 type="string",
     *                 format="uri",
     *                 example="https://www.royalconcerthall.be",
     *                 nullable=true,
     *                 description="Website URL (optional)"
     *             ),
     *             @OA\Property(
     *                 property="country_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the country (required)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Location created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Royal Concert Hall"),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="longitude", type="number", format="float", example=4.4041),
     *             @OA\Property(property="latitude", type="number", format="float", example=51.2194),
     *             @OA\Property(property="street", type="string", example="Koningstraat"),
     *             @OA\Property(property="housenr", type="string", example="10"),
     *             @OA\Property(property="zipcode", type="string", example="2000"),
     *             @OA\Property(property="city", type="string", example="Antwerp"),
     *             @OA\Property(property="website", type="string", format="uri", example="https://www.royalconcerthall.be", nullable=true),
     *             @OA\Property(property="country_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="country",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Belgium"),
     *                 @OA\Property(property="code", type="string", example="BE")
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
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
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'source' => 'sometimes|required|string|in:manual,api',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'street' => 'required|string|max:255',
            'housenr' => 'required|string|max:10',
            'zipcode' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'country_id' => 'required|exists:countries,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        if (!isset($data['source'])) {
            $data['source'] = LocationSource::MANUAL->value;
        }

        // Set status based on whether the request is coming from Filament (admin) or API (user)
        // If the request has a status field, it's coming from Filament (admin)
        // Otherwise, it's coming from the API (user) and should be pending approval
        if (!isset($data['status'])) {
            $data['status'] = LocationStatus::PENDING_APPROVAL->value;
        }

        $location = Location::create($data);
        return response()->json(
            $location->load(['country' => function ($query) {
                $query->select('id', 'name', 'code');
            }])->makeHidden(['created_at', 'updated_at', 'country_id']),
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/locations/{id}",
     *     summary="Get a specific location",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Location ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Location details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Royal Concert Hall"),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="longitude", type="number", format="float", example=4.4041),
     *             @OA\Property(property="latitude", type="number", format="float", example=51.2194),
     *             @OA\Property(property="street", type="string", example="Koningstraat"),
     *             @OA\Property(property="housenr", type="string", example="10"),
     *             @OA\Property(property="zipcode", type="string", example="2000"),
     *             @OA\Property(property="city", type="string", example="Antwerp"),
     *             @OA\Property(property="website", type="string", format="uri", example="https://www.royalconcerthall.be", nullable=true),
     *             @OA\Property(property="country_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="country",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Belgium"),
     *                 @OA\Property(property="code", type="string", example="BE")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Location not found"
     *     )
     * )
     */
    public function show(Location $location): JsonResponse
    {
        return response()->json(
            $location->load(['country' => function ($query) {
                $query->select('id', 'name', 'code');
            }])->makeHidden(['created_at', 'updated_at', 'country_id'])
        );
    }

    /**
     * @OA\Put(
     *     path="/api/locations/{id}",
     *     summary="Update a location",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Location ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Royal Concert Hall",
     *                 description="Name of the location (optional)"
     *             ),
     *             @OA\Property(
     *                 property="source",
     *                 type="string",
     *                 enum={"manual", "api"},
     *                 example="manual",
     *                 description="Source of the location (optional)"
     *             ),
     *             @OA\Property(
     *                 property="longitude",
     *                 type="number",
     *                 format="float",
     *                 example=4.4041,
     *                 description="Longitude coordinate (optional)"
     *             ),
     *             @OA\Property(
     *                 property="latitude",
     *                 type="number",
     *                 format="float",
     *                 example=51.2194,
     *                 description="Latitude coordinate (optional)"
     *             ),
     *             @OA\Property(
     *                 property="street",
     *                 type="string",
     *                 example="Koningstraat",
     *                 description="Street name (optional)"
     *             ),
     *             @OA\Property(
     *                 property="housenr",
     *                 type="string",
     *                 example="10",
     *                 description="House number (optional)"
     *             ),
     *             @OA\Property(
     *                 property="zipcode",
     *                 type="string",
     *                 example="2000",
     *                 description="ZIP/Postal code (optional)"
     *             ),
     *             @OA\Property(
     *                 property="city",
     *                 type="string",
     *                 example="Antwerp",
     *                 description="City name (optional)"
     *             ),
     *             @OA\Property(
     *                 property="website",
     *                 type="string",
     *                 format="uri",
     *                 example="https://www.royalconcerthall.be",
     *                 nullable=true,
     *                 description="Website URL (optional)"
     *             ),
     *             @OA\Property(
     *                 property="country_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the country (optional)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Location updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Royal Concert Hall"),
     *             @OA\Property(property="source", type="string", enum={"manual", "api"}, example="manual"),
     *             @OA\Property(property="longitude", type="number", format="float", example=4.4041),
     *             @OA\Property(property="latitude", type="number", format="float", example=51.2194),
     *             @OA\Property(property="street", type="string", example="Koningstraat"),
     *             @OA\Property(property="housenr", type="string", example="10"),
     *             @OA\Property(property="zipcode", type="string", example="2000"),
     *             @OA\Property(property="city", type="string", example="Antwerp"),
     *             @OA\Property(property="website", type="string", format="uri", example="https://www.royalconcerthall.be", nullable=true),
     *             @OA\Property(property="country_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="country",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Belgium"),
     *                 @OA\Property(property="code", type="string", example="BE")
     *             ),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Location not found"
     *     )
     * )
     */
    public function update(Request $request, Location $location): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'source' => 'sometimes|required|string|in:manual,api',
            'longitude' => 'sometimes|required|numeric',
            'latitude' => 'sometimes|required|numeric',
            'street' => 'sometimes|required|string|max:255',
            'housenr' => 'sometimes|required|string|max:10',
            'zipcode' => 'sometimes|required|string|max:20',
            'city' => 'sometimes|required|string|max:255',
            'website' => 'nullable|url|max:255',
            'country_id' => 'sometimes|required|exists:countries,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location->update($request->all());
        return response()->json(
            $location->load(['country' => function ($query) {
                $query->select('id', 'name', 'code');
            }])->makeHidden(['created_at', 'updated_at', 'country_id'])
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/locations/{id}",
     *     summary="Delete a location",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Location ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Location deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Location not found"
     *     )
     * )
     */
    public function destroy(Location $location): JsonResponse
    {
        $location->delete();
        return response()->json(null, 204);
    }
}
