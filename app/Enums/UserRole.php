<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SUPERUSER = 'superuser';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::SUPERUSER => 'Super User',
            self::USER => 'Regular User',
        };
    }
}
