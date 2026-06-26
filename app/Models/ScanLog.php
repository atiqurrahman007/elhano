<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')
                    ->select('id', 'name', 'slug', 'new_price', 'type', 'status');
    }

    public function variable()
    {
        return $this->belongsTo(ProductVariable::class, 'variable_id')
                    ->select('id', 'product_id', 'size', 'color', 'new_price', 'stock', 'pro_barcode');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id')
                    ->select('id', 'name');
    }
}
