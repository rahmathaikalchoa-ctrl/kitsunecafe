<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** Human-readable label for badges and filters. */
    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Tailwind classes for the status dot in the badge. */
    public function dotClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-400',
            self::Confirmed => 'bg-blue-400',
            self::Completed => 'bg-green-500',
            self::Cancelled => 'bg-gray-400',
        };
    }

    /** Tailwind classes for the badge pill background/text/border. */
    public function chipClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-700 border-amber-100',
            self::Confirmed => 'bg-blue-50 text-blue-700 border-blue-100',
            self::Completed => 'bg-green-50 text-green-700 border-green-100',
            self::Cancelled => 'bg-gray-100 text-gray-500 border-gray-200',
        };
    }

    /** A customer may only cancel an order that is still pending. */
    public function isCancellable(): bool
    {
        return $this === self::Pending;
    }

    /**
     * Whether an order may move from this status to $next. Forward flow is staff-driven
     * (Pending → Confirmed → Completed); cancellation is allowed until an order is completed.
     * Completed and Cancelled are terminal.
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => in_array($next, [self::Confirmed, self::Cancelled], true),
            self::Confirmed => in_array($next, [self::Completed, self::Cancelled], true),
            self::Completed, self::Cancelled => false,
        };
    }
}
