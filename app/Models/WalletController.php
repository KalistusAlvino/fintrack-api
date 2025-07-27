<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletController extends Model
{
    public function index($user_id)
    {
        try {
            $user = User::with('wallet')->findOrFail($user_id);
            return response()->json([
                'success' => true,
                'message' => 'Wallet data fetched successfully',
                'data' =>   [
                    'username' => $user->username,
                    'balance' => $user->wallet->balance ?? 0,
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
}
