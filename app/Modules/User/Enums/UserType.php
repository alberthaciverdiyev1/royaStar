<?php

namespace App\Modules\User\Enums;

enum UserType: string
{
    case Student = 'student';
    case Admin = 'admin';
}
