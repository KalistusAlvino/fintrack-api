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

    public function expenses() {
        return $this->hasMany(ExpensesCategory::class,'category_id','id');
    }
}
