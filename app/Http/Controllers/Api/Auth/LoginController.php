<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\temp_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);
            $temp_user = temp_user::where('username', $request->username)->first();

            if ($temp_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not verified',
                    'data' => []
                ], 403);
            }

            if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                $user = Auth::user();
                $accessToken = $user->createToken('authToken')->accessToken;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'token' => $accessToken,
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid username or password',
                'data' => []
            ], 401);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'data' => []
            ], 422);
        } catch (\Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Login failed'. $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
