<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['store_name', 'primary_color', 'currency', 'enable_guest_checkout', 'stripe_public_key', 'stripe_secret_key'])]
#[Hidden(['stripe_secret_key'])]
class WebshopSetting extends Model
{
    protected $connection = 'tenant';
}
