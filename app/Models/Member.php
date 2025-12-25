<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'member_code',
        'name',
        'type',
        'credit_balance',
        'points'
    ];

    public function creditLogs()
    {
        return $this->hasMany(MemberCreditLog::class);
    }
}
