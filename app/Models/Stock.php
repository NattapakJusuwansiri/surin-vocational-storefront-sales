<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    // กำหนดชื่อ table (optional ถ้าเป็น 'stocks' Laravel จะใช้ auto detect)
    protected $table = 'stocks';

    // กำหนดฟิลด์ที่สามารถแก้ไขได้ (Mass Assignment)
    protected $fillable = [
        'name',
        'category',
        'quantity_front',
        'barcode_unit',
        'barcode_pack',
        'barcode_box',
        'product_code',
        'quantity_back',
        'price',
    ];

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

}
