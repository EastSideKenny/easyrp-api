<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'type', 'quantity_change', 'reference_id'])]
class StockMovement extends Model
{
    protected $connection = 'tenant';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
