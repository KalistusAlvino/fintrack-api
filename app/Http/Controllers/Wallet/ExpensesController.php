<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Expenses;
use App\Models\ExpensesCategory;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ExpensesController extends Controller
{
    public function getExpensesCategory()
    {
        try {
            $categories = ExpensesCategory::all();

            $categoriesData = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => asset($category->image) // generate full URL
                ];
            });


            return response()->json([
                'success' => true,
                'message' => 'Expenses categories fetched successfully',
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

    public function allExpenses() {
          try {
            $userId = Auth::id() ?? null;
            $expenses = Expenses::with('expensesCategory')->whereHas('wallet', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $expenses->map(function ($expenses) {
                return [
                    'id' => $expenses->id ?? null,
                    'name' => $expenses->expensesCategory->name ?? 'No Category',
                    'images' => asset($expenses->expensesCategory->image),
                    'description' => $expenses->description ?? 'No Description',
                    'date' => $expenses->date ? Carbon::parse($expenses->date)->format('M j, Y') : null,
                    'amount' => $expenses->amount ?? 0,
                    'formatted_amount' => number_format($expenses->amount, 2, ',', '.')
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Expenses data fetched successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching expenses data' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

     public function detailExpenses($id)
    {
        try {
            $expenses = Expenses::with('expensesCategory')->whereHas('wallet', function ($query) use ($id) {
                $query->where('id', $id);
            })
                ->firstOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Detail expenses data fetched successfully',
                'data' => [
                    'id' => $expenses->id ?? null,
                    'name' => $expenses->expensesCategory->name ?? 'No Category',
                    'images' => asset($expenses->expensesCategory->image),
                    'description' => $expenses->description ?? 'No Description',
                    'date' => $expenses->date ? Carbon::parse($expenses->date)->format('M j, Y') : null,
                    'amount' => $expenses->amount ?? 0,
                    'formatted_amount' => number_format($expenses->amount, 2, ',', '.')
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching detail expenses data' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

     public function expensesDelete($id){
        try {
            $expenses = Expenses::findOrFail($id);
            $wallet = Auth::user()->wallet;

            // Kurangi saldo wallet sebelum menghapus expenses
            $wallet->update([
                'balance' => $wallet->balance + $expenses->amount
            ]);

            $expenses->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expenses data deleted successfully',
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting expenses data' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function expensesCategoryPost(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {

                $expensesCategory = new ExpensesCategory();
                $expensesCategory->name = $request->name;

                // Image processing
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                //Store using storage or public path
                Storage::disk('public')->putFileAs('images/expenses_categories', $image, $imageName);
                //Store name
                $expensesCategory->image = secure_url('storage/images/expenses_categories/' . $imageName);
                $expensesCategory->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Expenses category created successfully',
                    'data' => $expensesCategory
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
    public function expenses()
    {
        try {
            $userId = Auth::id() ?? null;
            $expenses = Expenses::with('expensesCategory')->whereHas('wallet', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $data = $expenses->map(function ($expenses) {
                return [
                    'id' => $expenses->id ?? null,
                    'name' => $expenses->expensesCategory->name ?? 'No Category',
                    'images' => asset($expenses->expensesCategory->image),
                    'description' => $expenses->description ?? 'No Description',
                    'date' => $expenses->date ? Carbon::parse($expenses->date)->format('M j, Y') : null,
                    'amount' => $expenses->amount ?? 0,
                    'formatted_amount' => number_format($expenses->amount, 2, ',', '.')
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Expenses data fetched successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching expenses data' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function monthlyExpenses()
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
            $incomeData = $user->expenses()
                ->select(
                    DB::raw("DATE_FORMAT(date, '%Y-%m') as month_year"),
                    DB::raw("SUM(Amount) as total_expenses")
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

                // Cek apakah ada data pengeluaran untuk bulan ini. Jika tidak ada, totalnya 0.
                $totalExpenses = $incomeData->get($monthKey)->total_expenses ?? 0;

                $report[] = [
                    'month_name' => $date->format('F Y'), // Format 'July 2025'
                    'month_key' => $monthKey,
                    'total' => (float) $totalExpenses,
                    'formatted_total' => number_format($totalExpenses, 2, ',', '.')
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Success fetching monthly expenses',
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

    public function thisMonthExpenses()
    {
        try {
            $userId = Auth::id() ?? null;
            $expenses = Expenses::whereHas('wallet', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->whereMonth('date', Carbon::now()->month)
                ->whereYear('date', Carbon::now()->year)
                ->sum('amount'); // langsung ambil total jumlah

            return response()->json([
                'success' => true,
                'message' => 'Expenses data fetched successfully',
                'data' => [
                    'total_amount' => (int) $expenses ?? 0,
                    'formatted_total_amount' => number_format($expenses, 2, ',', '.')
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching expenses data' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function expensesPost(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:expenses_category,id',
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0|max:' . Auth::user()->wallet->balance
            ]);

            $expenses = new Expenses();
            $expenses->wallet_id = Auth::user()->wallet->id;
            $expenses->category_id = $request->category_id;
            $expenses->description = $request->description;
            $expenses->date = now(); // Set current date or you can use $request->date if provided
            $expenses->amount = $request->amount;
            $expenses->save();

            $wallet = Auth::user()->wallet;
            $wallet->update([
                'balance' => $wallet->balance - $request->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expenses recorded successfully',
                'data' => $expenses
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'data' => []
            ], 422);
        } catch (\Exception $e) {
            Log::error('Expenses Post Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to record expenses',
                'data' => []
            ], 500);
        }
    }
}
