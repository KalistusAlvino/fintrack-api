<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class temp_user extends Model
{
    protected $table = 'temp_users';
    protected $fillable = [
        'username',
        'email',
        'email_verification_token',
        'password',
    ];
}
