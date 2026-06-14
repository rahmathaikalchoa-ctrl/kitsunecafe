<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderType: string
{
    case DineIn = 'dine_in';
    case Takeaway = 'takeaway';

    /** Human-readable label for forms and badges. */
    public function label(): string
    {
        return match ($this) {
            self::DineIn => 'Dine-in',
            self::Takeaway => 'Takeaway',
        };
    }

    /** Only dine-in orders are tied to a table. */
    public function needsTable(): bool
    {
        return $this === self::DineIn;
    }
}
