<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use App\Models\Wallet;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Storage;

class WalletController extends Controller
{
    public function index()
    {
        try {
            $userId = Auth::id() ?? null; // Use authenticated user ID or provided user ID
            $user = User::with('wallet')->findOrFail($userId);
            return response()->json([
                'success' => true,
                'message' => 'Wallet data fetched successfully',
                'data' => [
                    'username' => $user->username,
                    'balance' => number_format($user->wallet->balance, 2, ',', '.')
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching wallet data',
                'data' => []
            ], 500);
        }
    }

    public function profile()
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Profile data fetched successfully',
                'data' => [
                    'username' => Auth::user()->username,
                    'email' => Auth::user()->email,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching profile data',
                'data' => []
            ], 500);
        }
    }

}
