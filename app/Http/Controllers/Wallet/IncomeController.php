<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\IncomeCategory;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class IncomeController extends Controller
{
    public function incomePost(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:income_category,id',
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
            ]);

            $income = new Income();
            $income->wallet_id = Auth::user()->wallet->id;
            $income->category_id = $request->category_id;
            $income->description = $request->description;
            $income->date = now(); // Set current date or you can use $request->date if provided
            $income->amount = $request->amount;
            $income->save();

            $wallet = Auth::user()->wallet;
            $wallet->update([
                'balance' => $wallet->balance + $request->amount
            ]);

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

    public function allIncome()
    {
        try {
            $userId = Auth::id() ?? null;
            $incomes = Income::with('incomeCategory')->whereHas('wallet', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $incomes->map(function ($income) {
                return [
                    'id' => $income->id ?? null,
                    'name' => $income->incomeCategory->name ?? 'No Category',
                    'images' => asset($income->incomeCategory->image),
                    'description' => $income->description ?? 'No Description',
                    'date' => $income->date ? Carbon::parse($income->date)->format('M j, Y') : null,
                    'amount' => $income->amount ?? 0,
                    'formatted_amount' => number_format($income->amount, 2, ',', '.')
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

    public function getIncomeCategory()
    {
        try {
            $categories = IncomeCategory::all();


            $categoriesData = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => asset($category->image) // generate full URL
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Income categories fetched successfully',
                'data' => $categoriesData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching income categories' . $e->getMessage(),
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
                $incomeCategory->image = 'storage/images/income_categories/' . $imageName;
                $incomeCategory->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Income category created successfully',
                    'data' => [
                        'id' => $incomeCategory->id,
                        'name' => $incomeCategory->name,
                        'image' => asset($incomeCategory->image) // Generate full URL
                    ]
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
                'message' => 'Error processing request' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function monthlyIncome()
    {
        try {
            $user = Auth::user();

            // 1. TENTUKAN RENTANG WAKTU (12 BULAN TERAKHIR)
            // Batas akhir adalah hari ini (31 Juli 2025).
            $endDate = Carbon::now();
            // Batas awal adalah 11 bulan sebelum bulan ini, di tanggal 1.
            // Ini akan menghasilkan periode 12 bulan, dari 1 Agustus 2024 hingga 31 Juli 2025.
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();

            // 2. AMBIL DATA DARI DATABASE
            // Ganti 'transaction_date' dengan nama kolom tanggal Anda.
            $incomeData = $user->incomes()
                ->select(
                    DB::raw("DATE_FORMAT(date, '%Y-%m') as month_year"),
                    DB::raw("SUM(Amount) as total_income")
                )
                ->where('date', '>=', $startDate->toDateTimeString())
                ->where('date', '<=', $endDate->toDateTimeString())
                ->groupBy('month_year', 'wallet.user_id') // <-- Tambahkan 'wallet.user_id' di sini
                ->orderBy('month_year', 'asc')
                ->get()
                ->keyBy('month_year'); // Jadikan 'YYYY-MM' sebagai key untuk pencarian cepat

            // 3. BUAT PERIODE LENGKAP & GABUNGKAN DATA
            $report = [];
            $period = CarbonPeriod::create($startDate, '1 month', $endDate);

            foreach ($period as $date) {
                $monthKey = $date->format('Y-m'); // Format '2025-07'

                // Cek apakah ada data pendapatan untuk bulan ini. Jika tidak ada, totalnya 0.
                $totalIncome = $incomeData->get($monthKey)->total_income ?? 0;

                $report[] = [
                    'month_name' => $date->format('F Y'), // Format 'July 2025'
                    'month_key' => $monthKey,
                    'total' => (float) $totalIncome,
                    'formatted_total' => number_format($totalIncome, 2, ',', '.')
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Success fetching monthly income',
                'data' => $report
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
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
            })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $data = $incomes->map(function ($income) {
                return [
                    'id' => $income->id ?? null,
                    'name' => $income->incomeCategory->name ?? 'No Category',
                    'images' => asset($income->incomeCategory->image),
                    'description' => $income->description ?? 'No Description',
                    'date' => $income->date ? Carbon::parse($income->date)->format('M j, Y') : null,
                    'amount' => $income->amount ?? 0,
                    'formatted_amount' => number_format($income->amount, 2, ',', '.')
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

    public function thisMonthIncome()
    {
        try {
            $userId = Auth::id() ?? null;
            $incomes = Income::whereHas('wallet', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->whereMonth('date', Carbon::now()->month)
                ->whereYear('date', Carbon::now()->year)
                ->sum('amount'); // langsung ambil total jumlah

            return response()->json([
                'success' => true,
                'message' => 'Income data fetched successfully',
                'data' => [
                    'total_amount' => (int) $incomes ?? 0,
                    'formatted_total_amount' => number_format($incomes, 2, ',', '.')
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching income data' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
