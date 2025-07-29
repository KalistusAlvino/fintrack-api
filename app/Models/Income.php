<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $table = 'income';

    protected $fillable = [
        'wallet_id',
        'category_id',
        'amount',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function incomeCategory()
    {
        return $this->belongsTo(IncomeCategory::class, 'category_id');
    }
}
