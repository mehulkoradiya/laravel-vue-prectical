<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = [
        'stock_no',
        'item_code',
        'item_name',
        'quantity',
        'location',
        'store_id',
        'in_stock_date',
        'status'
    ];

    protected $casts = [
        'in_stock_date' => 'date',
        'quantity' => 'integer'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
