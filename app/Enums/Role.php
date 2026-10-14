<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Engineer = 'engineer';
    case Viewer = 'viewer';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Roles that may create and edit cases (and so change their status). */
    public function canManageCases(): bool
    {
        return $this === self::Admin || $this === self::Engineer;
    }
}
