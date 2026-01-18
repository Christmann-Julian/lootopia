<?php

namespace App\Enum;

enum RolesEnum: string
{
    case ROLE_USER = 'ROLE_USER';
    case ROLE_ADMIN = 'ROLE_ADMIN';

    public const VALUES = [
        self::ROLE_USER->value,
        self::ROLE_ADMIN->value,
    ];
}
