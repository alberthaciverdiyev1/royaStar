<?php

namespace App\Modules\User\Enums;

enum UserType: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case Parent = 'parent';
    case School = 'school';
    case Admin = 'admin';
}
