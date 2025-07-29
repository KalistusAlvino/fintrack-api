<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeCategory extends Model
{
    protected $table = 'income_category';

    protected $fillable = [
        'name',
        'image'
    ];
}
