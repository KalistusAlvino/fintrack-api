<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    protected $table = 'expenses';

    protected $fillable = [
        'wallet_id',
        'category_id',
        'description',
        'date',
        'amount',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function expensesCategory()
    {
        return $this->belongsTo(ExpensesCategory::class, 'category_id');
    }
}
