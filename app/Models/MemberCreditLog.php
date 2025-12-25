<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCreditLog extends Model
{
    protected $fillable = [
        'member_id',
        'bill_id',
        'amount',
        'type',
        'remark'
    ];
}
