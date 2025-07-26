<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationMail;
use App\Models\temp_user;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Register a new account.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|min:4',
                Rule::unique('temp_users', 'username'),
                'email' => 'required|string|email|max:255',
                Rule::unique('temp_users', 'email'),
                'password' => 'required|string|min:8',
                'confirm_password' => 'required|string|same:password',
            ]);

            $alreadyerExitsUsername = User::where('username', $request->username)
                ->exists();
            $alreadyerExitsEmail = User::where('email', $request->email)
                ->exists();

            if ($alreadyerExitsUsername) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this username already exists',
                    'data' => []
                ], 422);
            }
            if ($alreadyerExitsEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this email already exists',
                    'data' => []
                ], 422);
            }

            $tempUser = temp_user::updateOrCreate([
                'email' => $request->email,
            ], [
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'email_verification_token' => Str::random(64),
            ]);

            Mail::to($tempUser->email)->queue(new VerificationMail($tempUser->email_verification_token));

            return response()->json([
                'success' => true,
                'message' => 'Successfully registered',
                'data' => [
                    'email' => $tempUser->email
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'data' => []
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'data' => []
            ], 500);
        }
    }
    public function verify($token)
    {
        try {
            $temp_user = temp_user::where('email_verification_token', $token)->firstOrFail();
            $user = new User();
            $user->username = $temp_user->username;
            $user->email = $temp_user->email;
            $user->password = $temp_user->password;
            $user->email_verified_at = now();
            $user->save();

            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->save();

            $temp_user->delete();

            return response()->view('success-verify', [
                'email' => $user->email,
                'verification_time' => now()->format('l, H:i \W\I\B')
            ]);

        } catch (\Exception $e) {
            return response()->view('failed-verify');
        }
    }

    public function resendVerification(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:temp_users,email',
            ]);
            $temp_user = temp_user::where('email', $request->email)->firstOrFail();
            $user = User::where('email', $request->email)->first();

            if ($user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified',
                    'data' => []
                ], 422);
            }
            if(!$temp_user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => []
                ], 404);
            }

            $temp_user->email_verification_token = Str::random(64);
            $temp_user->save();

            Mail::to($temp_user->email)->queue(new VerificationMail($temp_user->email_verification_token));

            return response()->json([
                'success' => true,
                'message' => 'Verification email resent successfully',
                'data' => [
                    'email' => $temp_user->email
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Resend Verification Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend verification email: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
