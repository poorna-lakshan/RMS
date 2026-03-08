<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Auth\UserRoles;
use App\Models\Auth\UserAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\Auth\UserActivity;
use App\Models\default\DeletedRows;

class UserController extends Controller
{

    // User Registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role_id' => 'required',
            'password' => 'nullable|string|min:1',
            'ware_house_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $user = User::updateOrCreate(
            ['name' => $data['name']],   // unique key
            [
                'email' => $request->input('email'),
                'role_id' => $data['role_id'],
                'ware_house_id' => $data['ware_house_id'] ?? null,
                'password' => !empty($data['password'])
                    ? md5($data['password'])
                    : '',
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'User saved successfully',
            'user' => $user,
        ], 201);
    }

    public function Users()
    {
        $companies = User::with('Warehouse')->get();

        return response()->json($companies);

    }


    public function deleteUser($id)
    {
        // Find the ItemCategory by ID
        $itemCategory = User::find($id);
        // Check if the ItemCategory exists
        if (!$itemCategory) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // Delete the ItemCategory
        $itemCategory->delete();

        $user = DeletedRows::create([
            'type' => 'User',
            'key' => $id,
        ]);

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    // User Login
    public function login(Request $request)
    {
        try {
            // Validate request input
            $credentials = $request->validate([
                'name' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            // Find user by name
            $user = User::where('name', $credentials['name'])->first();

            // Check credentials
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'credentials' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Generate token
            $token = $user->createToken('login-token')->plainTextToken;

            $UserAuth = UserAuth::with('UserActivity')->where('role_id', $user->role_id)->get();

            // Return success response
            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'UserAuth' => $UserAuth,
                'user' => $user->only(['id', 'name', 'email']),
            ], 200);

        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            // Handle other unexpected exceptions
            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(), // Optional: remove in production
            ], 500);
        }
    }

    // User Profile
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    // User Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }


    public function UserRoles()
    {
        $companies = UserRoles::all();

        return response()->json($companies);

    }

    public function deleteUserRoles($id)
    {
        // Find the ItemCategory by ID
        $itemCategory = UserRoles::find($id);

        // Check if the ItemCategory exists
        if (!$itemCategory) {
            return response()->json(['error' => 'UserRoles not found'], 404);
        }

        // Delete the ItemCategory
        $itemCategory->delete();

        // LogHelper::log(
        //     auth()->user()->name,
        //     'delete',
        //     'ItemClass',
        //     'Delete Class: ' . $itemCategory
        // );

        return response()->json(['message' => 'UserRoles deleted successfully'], 200);
    }

    public function createOrUpdateRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|exists:user_role,id',
            'name' => 'required|string|max:255|unique:user_role,name,' . $request->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        $role = UserRoles::updateOrCreate(
            ['id' => $request->id],        // if id exists → update, else create
            ['name' => $validatedData['name']]
        );

        return response()->json([
            'status' => true,
            'message' => $request->id ? 'Role updated successfully' : 'Role created successfully',
            'data' => $role
        ]);
    }


    public function getDefaultPermissions()
    {

        $UserRoles = UserRoles::all();
        $UserActivitys = UserActivity::with('permissions')->get();



        return response()->json([
            'UserRoles' => $UserRoles,
            'UserActivitys' => $UserActivitys,
        ]);
    }


    public function saveRolePermissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:user_role,id',
            'permissions' => 'required|array',
            'permissions.*.activity_id' => 'required|exists:user_activity,id',
            'permissions.*.view' => 'required|boolean',
            'permissions.*.add' => 'required|boolean',
            'permissions.*.edit' => 'required|boolean',
            'permissions.*.delete' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->permissions as $perm) {
            UserAuth::updateOrCreate(
                [
                    'role_id' => $request->role_id,
                    'activity_id' => $perm['activity_id'],
                ],
                [
                    'view' => $perm['view'],
                    'add' => $perm['add'],
                    'edit' => $perm['edit'],
                    'delete' => $perm['delete'],
                ]
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Permissions saved successfully'
        ]);
    }



}
