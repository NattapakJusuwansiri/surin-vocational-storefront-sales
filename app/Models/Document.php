<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'document_type',
        'document_no',
        'document_date',
        'bill_id',
        'total_amount',
        'vat_amount',
        'seller_name',
        'seller_tax_id',
        'seller_address',
        'buyer_name',
        'buyer_tax_id',
        'buyer_address',
    ];

    public function items()
    {
        return $this->hasMany(DocumentItem::class);
    }
}
