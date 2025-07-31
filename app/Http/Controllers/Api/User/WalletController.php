<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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

    public function income()
    {
        try {
            $userId = Auth::id() ?? null;
            $incomes = Income::with('incomeCategory')->whereHas('wallet', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();

            $data = $incomes->map(function ($income) {
                return [
                    'id' => $income->id ?? null,
                    'name' => $income->incomeCategory->name ?? 'No Category',
                    'images' => $income->incomeCategory->image ?? null,
                    'date' => $income->date ? Carbon::parse($income->date)->format('M j, Y') : null,
                    'amount' => $income->amount ?? 0,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Income data fetched successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching income data' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function monthlyIncome()
    {
        try {
            $userId = Auth::id();
            $now = Carbon::now();

            // Alternatif: Buat array kosong dan push satu per satu
            $monthlyData = [];
            $months = [];

            // Generate 12 bulan ke belakang
            for ($i = 11; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $key = $month->format('Y-m');

                $months[] = $key; // Simpan urutan bulan

                $monthlyData[$key] = [
                    'month' => $month->format('M Y'),
                    'month_number' => (int) $month->format('n'),
                    'year' => (int) $month->format('Y'),
                    'total_income' => 0,
                    'transaction_count' => 0,
                ];
            }

            // Ambil data income dalam 12 bulan terakhir
            $incomes = Income::whereHas('wallet', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->whereBetween('date', [
                    $now->copy()->subMonths(11)->startOfMonth(),
                    $now->copy()->endOfMonth()
                ])
                ->get();

            // Group manual tanpa collection
            $groupedIncomes = [];
            foreach ($incomes as $income) {
                $monthKey = Carbon::parse($income->date)->format('Y-m');
                if (!isset($groupedIncomes[$monthKey])) {
                    $groupedIncomes[$monthKey] = [];
                }
                $groupedIncomes[$monthKey][] = $income;
            }

            // Update data dengan income aktual
            foreach ($groupedIncomes as $monthKey => $monthIncomes) {
                if (isset($monthlyData[$monthKey])) {
                    $monthlyData[$monthKey]['total_income'] = array_sum(array_column($monthIncomes, 'amount'));
                    $monthlyData[$monthKey]['transaction_count'] = count($monthIncomes);
                }
            }

            // Buat response array sesuai urutan bulan
            $responseData = [];
            foreach ($months as $monthKey) {
                $responseData[] = $monthlyData[$monthKey];
            }

            // Return JSON
            return response()->json([
                'success' => true,
                'message' => 'Monthly income summary fetched successfully',
                'total_months' => count($responseData), // Should be 12
                'period_start' => $months[0],
                'period_end' => $months[11],
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching monthly income data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function incomeCategoryPost(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {

                $incomeCategory = new IncomeCategory();
                $incomeCategory->name = $request->name;

                // Image processing
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                //Store using storage or public path
                Storage::disk('public')->putFileAs('images/income_categories', $image, $imageName);
                //Store name
                $incomeCategory->image = url('storage/images/income_categories/' . $imageName);
                $incomeCategory->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Income category created successfully',
                    'data' => $incomeCategory
                ], 201);
            }



        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'data' => []
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing request',
                'data' => []
            ], 500);
        }
    }
    public function incomePost(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:income_category,id',
                'amount' => 'required|numeric|min:0',
            ]);

            $income = new Income();
            $income->wallet_id = Auth::user()->wallet->id;
            $income->category_id = $request->category_id;
            $income->date = now(); // Set current date or you can use $request->date if provided
            $income->amount = $request->amount;
            $income->save();

            return response()->json([
                'success' => true,
                'message' => 'Income recorded successfully',
                'data' => $income
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'data' => []
            ], 422);
        } catch (\Exception $e) {
            Log::error('Income Post Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to record income',
                'data' => []
            ], 500);
        }
    }
}
