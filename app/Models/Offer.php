<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'offer_number',
    'customer_id',
    'status',
    'issue_date',
    'valid_until',
    'subtotal',
    'tax_total',
    'total',
    'currency',
    'notes',
    'invoice_id',
    'pdf_path',
    'token',
    'created_by',
])]
class Offer extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected function casts(): array
    {
        return [
            'issue_date'  => 'date',
            'valid_until' => 'date',
        ];
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path
            ? Storage::disk('public')->url($this->pdf_path)
            : null;
    }

    protected $appends = ['pdf_url'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfferItem::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
