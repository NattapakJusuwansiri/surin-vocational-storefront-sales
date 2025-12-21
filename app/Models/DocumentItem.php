<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentItem extends Model
{
    protected $fillable = [
        'document_id',
        'item_name',
        'quantity',
        'price',
        'total',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
