<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [  
        'total',
        'paid_amount',
        'change_amount',
        'member_id'
    ];

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }
}

