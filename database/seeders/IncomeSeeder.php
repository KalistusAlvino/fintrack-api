<?php

namespace Database\Seeders;

use App\Models\Income;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class IncomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wallets = Wallet::all();

        foreach ($wallets as $wallet) {
            for ($i = 0; $i < 5; $i++) {
                $amount = rand(50000, 200000); // jumlah income acak
                $categoryId = rand(1, 2); // hanya antara 1 atau 2
                $date = Carbon::now()
                    ->subYears(rand(0, 1))     // mundur 0–1 tahun
                    ->subMonths(rand(0, 11))   // mundur 0–11 bulan
                    ->setDay(rand(1, 28));     // tanggal 1–28, aman untuk semua bulan
                Income::create([
                    'wallet_id' => $wallet->id,
                    'category_id' => $categoryId,
                    'date' => $date,
                    'amount' => $amount,
                ]);

                // Tambahkan jumlah ke saldo wallet
                $wallet->balance += $amount;
                $wallet->save();
            }
        }
    }
}
