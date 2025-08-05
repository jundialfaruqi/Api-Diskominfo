<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Permission::query();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $permissions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Permissions retrieved successfully',
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:permissions,name'
            ]);

            $permission = Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web'
            ]);

            return response()->json([
                'message' => 'Permission created successfully',
                'data' => $permission
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): JsonResponse
    {
        return response()->json($permission);
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id
            ]);

            $permission->update([
                'name' => $validated['name']
            ]);

            return response()->json([
                'message' => 'Permission updated successfully',
                'data' => $permission
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        try {
            // Check if permission is assigned to any roles
            if ($permission->roles()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete permission that is assigned to roles'
                ], 400);
            }

            // Check if permission is directly assigned to any users
            if ($permission->users()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete permission that is assigned to users'
                ], 400);
            }

            $permission->delete();

            return response()->json([
                'message' => 'Permission deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permission directly to user.
     */
    public function assignToUser(Request $request, Permission $permission): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id'
            ]);

            $user = \App\Models\User::findOrFail($validated['user_id']);
            $user->givePermissionTo($permission);

            return response()->json([
                'message' => 'Permission assigned to user successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to assign permission to user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove permission from user.
     */
    public function removeFromUser(Request $request, Permission $permission): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id'
            ]);

            $user = \App\Models\User::findOrFail($validated['user_id']);
            $user->revokePermissionTo($permission);

            return response()->json([
                'message' => 'Permission removed from user successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove permission from user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all permissions grouped by category/module.
     */
    /**
     * Get all permissions without pagination (for roles management)
     */
    public function all(): JsonResponse
    {
        try {
            $permissions = Permission::orderBy('name', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'All permissions retrieved successfully',
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function grouped(): JsonResponse
    {
        $permissions = Permission::all();
        
        // Group permissions by their prefix (e.g., 'manage users', 'view users' -> 'users')
        $grouped = $permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? end($parts) : 'general';
        });

        return response()->json($grouped);
    }

    /**
     * Get permission statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            $allPermissions = Permission::all();
            $now = new \DateTime();
            $oneWeekAgo = (clone $now)->modify('-7 days');
            
            $systemPermissions = $allPermissions->filter(function ($permission) {
                return str_contains($permission->name, 'view') || 
                       str_contains($permission->name, 'create') || 
                       str_contains($permission->name, 'edit') || 
                       str_contains($permission->name, 'delete');
            })->count();
            
            $recentPermissions = $allPermissions->filter(function ($permission) use ($oneWeekAgo) {
                return new \DateTime($permission->created_at) > $oneWeekAgo;
            })->count();
            
            $stats = [
                'total_permissions' => $allPermissions->count(),
                'system_permissions' => $systemPermissions,
                'custom_permissions' => $allPermissions->count() - $systemPermissions,
                'recent_permissions' => $recentPermissions
            ];

            return response()->json([
                'success' => true,
                'message' => 'Permission statistics retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permission statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}