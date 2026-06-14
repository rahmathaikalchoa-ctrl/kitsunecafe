<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_type',
        'table_number',
        'status',
        'notes',
        'total_cents',
    ];

    protected $casts = [
        'order_type' => OrderType::class,
        'table_number' => 'integer',
        'status' => OrderStatus::class,
        'total_cents' => 'integer',
    ];

    /**
     * Customer-facing order reference (e.g. "ORD-0007"), so the raw database id
     * is never shown in the UI.
     */
    protected function reference(): Attribute
    {
        return Attribute::make(
            get: fn (): string => 'ORD-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
