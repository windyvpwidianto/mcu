<?php

namespace App\Enums;

enum HazardStatus: string
{
    case Submitted = 'submitted';
    case InProgress = 'in_progress';
    case Pending = 'pending';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::InProgress => 'In Progress',
            self::Pending => 'Pending',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'gray',
            self::InProgress => 'blue',
            self::Pending => 'yellow',
            self::Closed => 'green',
            self::Cancelled => 'red',
        };
    }
}
