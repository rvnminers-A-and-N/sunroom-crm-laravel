<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'User';
    case Manager = 'Manager';
    case Admin = 'Admin';
}
