<?php

declare(strict_types=1);

namespace App\Util;

enum AccountRole: string
{
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_USER  = 'ROLE_USER';
}
