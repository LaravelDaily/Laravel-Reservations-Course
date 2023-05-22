<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'registered_at',
        'company_id',
        'role_id',
    ];
}
