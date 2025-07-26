<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            $temp_user = temp_user::where('email', $request->email)->first();

            if ($temp_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not verified',
                    'data' => []
                ], 403);
            }

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $accessToken = $user->createToken('authToken')->accessToken;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
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
                'message' => '',
                'data' => []
            ], 500);
        }
    }
}
