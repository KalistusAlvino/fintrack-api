<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpensesCategory extends Model
{
    protected $table = 'expenses_category';

    protected $fillable = [
        'name',
        'image'
    ];
}
