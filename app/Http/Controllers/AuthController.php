<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password tidak valid',
                'errors' => [
                    'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.']
                ]
            ], 401);
        }

        $user = auth('api')->user();
        $user->load('roles', 'permissions');
        
        // Format permissions untuk frontend (sama seperti method me())
        $formattedPermissions = $user->getAllPermissions()->map(function($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name
            ];
        });
        
        // Tambahkan permissions ke user object
        $userData = $user->toArray();
        $userData['permissions'] = $formattedPermissions;
        
        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $userData,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    }

    public function me()
    {
        $user = auth('api')->user();
        $user->load('roles', 'permissions');
        
        // Format permissions untuk frontend
        $formattedPermissions = $user->getAllPermissions()->map(function($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name
            ];
        });
        
        // Tambahkan permissions ke user object
        $userData = $user->toArray();
        $userData['permissions'] = $formattedPermissions;
        
        return response()->json([
            'user' => $userData,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = auth('api')->login($user);

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }
}
