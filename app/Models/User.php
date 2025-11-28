<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
        'prefix',
        'name',
        'position',
        'role',
        'employee_id',
        'password_changed', 
    ];

    protected $hidden = [
        'password',
    ];

}