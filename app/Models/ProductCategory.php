<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name'])]
class ProductCategory extends Model
{
    protected $connection = 'tenant';

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category_product', 'category_id', 'product_id');
    }
}
