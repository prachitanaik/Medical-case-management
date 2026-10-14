<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Draft = 'Draft';
    case Planning = 'Planning';
    case InReview = 'In Review';
    case Approved = 'Approved';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
