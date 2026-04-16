<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['offer_id', 'action', 'ip_address', 'user_agent', 'responded_at'])]
class OfferResponse extends Model
{
    protected $connection = 'tenant';

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
