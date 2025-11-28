<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'quantity',
        'from_location',
        'to_location',
        'unit_type'
    ];

    public function stock() {
        return $this->belongsTo(Stock::class);
    }
}
