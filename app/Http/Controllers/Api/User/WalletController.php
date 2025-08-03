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

    public function income()
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
                    'images' => $income->incomeCategory->image ?? null,
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
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('amount'); // langsung ambil total jumlah

            return response()->json([
                'success' => true,
                'message' => 'Income data fetched successfully',
                'data' => [
                    'total_amount' => $incomes ?? 0,
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

    public function monthlyIncome()
    {
        try {
           $user = Auth::user();

            // BAGIAN 1 & 2: PENGAMBILAN DATA (TIDAK BERUBAH)
            // Kode Anda untuk mengambil data 12 bulan terakhir sudah benar.
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();

            $incomeData = $user->incomes()
                ->select(
                    DB::raw("DATE_FORMAT(date, '%Y-%m') as month_year"),
                    DB::raw("SUM(Amount) as total_income")
                )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('month_year')
                ->orderBy('month_year', 'asc')
                ->get()
                ->keyBy('month_year');

            // BAGIAN 3: PEMBUATAN LAPORAN (TIDAK BERUBAH)
            $report = [];
            $period = CarbonPeriod::create($startDate, '1 month', $endDate);

            foreach ($period as $date) {
                $monthKey = $date->format('Y-m');
                $totalIncome = $incomeData->get($monthKey)->total_income ?? 0;

                $report[] = [
                    'month_name' => $date->format('F Y'),
                    'month_key' => $monthKey,
                    'total_income' => (float) $totalIncome,
                    'formatted_income' => number_format($totalIncome, 2, ',', '.')
                ];
            }

            // BAGIAN 4: LOGIKA PENGURUTAN BARU
            // Kita akan urutkan ulang array $report di sini.
            $currentYear = Carbon::now()->year;

            usort($report, function ($a, $b) use ($currentYear) {
                // Ambil tahun dan bulan dari masing-masing item
                list($yearA, $monthA) = explode('-', $a['month_key']);
                list($yearB, $monthB) = explode('-', $b['month_key']);

                // Buat "skor urutan". Bulan di tahun ini mendapat skor lebih rendah (prioritas).
                // Bulan di tahun lalu mendapat skor lebih tinggi (ditaruh di belakang).
                $scoreA = ($yearA == $currentYear) ? (int)$monthA : (int)$monthA + 12;
                $scoreB = ($yearB == $currentYear) ? (int)$monthB : (int)$monthB + 12;

                // Gunakan spaceship operator untuk membandingkan skor
                return $scoreA <=> $scoreB;
            });

            return response()->json([
                'success' => true,
                'message' => 'Success fetching monthly income',
                'data' => $report // Kirim data yang sudah diurutkan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
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
                $incomeCategory->image = secure_url('storage/images/income_categories/' . $imageName);
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
                'message' => 'Error processing request' . $e->getMessage(),
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
}
