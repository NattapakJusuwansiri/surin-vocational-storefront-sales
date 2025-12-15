<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory;

    protected $fillable = ['bill_id', 'stock_id', 'quantity', 'price', 'subtotal'];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
    public function bill() {
        return $this->belongsTo(Bill::class);
    }
}
