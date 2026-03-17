<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'name', 'description'])]
class Feature extends Model
{
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features');
    }
}
