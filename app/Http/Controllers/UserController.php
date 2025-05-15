<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for user management"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="username", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="profile_photo_url", type="string", format="uri"),
 *     @OA\Property(property="bio", type="string", nullable=true),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="role", type="string", enum={"admin", "superuser", "user"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Search users by name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Search users by email (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", format="email")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort users by field (name, email, created_at) and direction (asc, desc). Example: name:asc",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('sort')) {
            $sortParts = explode(':', $request->sort);
            $field = $sortParts[0];
            $direction = $sortParts[1] ?? 'asc';

            if (in_array($field, ['name', 'email', 'created_at']) && in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($field, $direction);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $users = $query->get();
        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get a specific user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "username"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="bio", type="string", nullable=true),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true),
     *             @OA\Property(property="role", type="string", enum={"admin", "superuser", "user"}, default="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'username' => 'required|string|max:255|unique:users',
            'bio' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'role' => 'nullable|string|in:admin,superuser,user'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'username' => $validated['username'],
            'bio' => $validated['bio'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'role' => $validated['role'] ?? 'user',
            'is_active' => true
        ]);

        return response()->json($user, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="bio", type="string", nullable=true),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="role", type="string", enum={"admin", "superuser", "user"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'username' => 'sometimes|string|max:255|unique:users,username,' . $id,
            'bio' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'sometimes|boolean',
            'role' => 'sometimes|string|in:admin,superuser,user'
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }
}
