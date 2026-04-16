<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['step', 'is_completed'])]
class SetupProgress extends Model
{
    protected $connection = 'tenant';
}
