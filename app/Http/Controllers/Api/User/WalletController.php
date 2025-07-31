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
            $userId = Auth::id() ?? null;

            // Buat array 12 bulan ke belakang
            $monthlyData = [];
            $currentDate = now();

            // Generate 12 bulan ke belakang
            for ($i = 11; $i >= 0; $i--) {
                $monthDate = $currentDate->copy()->subMonths($i);
                $monthKey = $monthDate->format('Y-m');

                $monthlyData[$monthKey] = [
                    'month' => $monthDate->format('M Y'), // Jan 2024
                    'month_number' => $monthDate->format('n'), // 1, 2, 3, dst
                    'year' => $monthDate->format('Y'),
                    'total_income' => 0,
                    'count_transactions' => 0
                ];
            }

            // Ambil data income 12 bulan terakhir dengan sum per bulan
            $incomes = Income::selectRaw('DATE_FORMAT(date, "%Y-%m") as month_key,DATE_FORMAT(date, "%M %Y") as month_name,MONTH(date) as month_number,YEAR(date) as year,
                        SUM(amount) as total_amount,COUNT(*) as transaction_count')
                ->whereHas('wallet', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->where('date', '>=', $currentDate->copy()->subMonths(11)->startOfMonth())
                ->where('date', '<=', $currentDate->copy()->endOfMonth())
                ->groupBy('month_key')
                ->orderBy('month_key', 'asc')
                ->get();

            // Merge data aktual dengan template 12 bulan
            foreach ($incomes as $income) {
                if (isset($monthlyData[$income->month_key])) {
                    $monthlyData[$income->month_key]['total_income'] = (float) $income->total_amount;
                    $monthlyData[$income->month_key]['count_transactions'] = $income->transaction_count;
                }
            }

            // Format final data
            $responseData = array_values($monthlyData);


            $data = collect($responseData)->map(function ($response) {
                return [
                    'month' => $response['month_number'],
                    'year' => $response['year'],
                    'total_income' => $response['total_income']
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Monthly income summary fetched successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching monthly income data',
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
