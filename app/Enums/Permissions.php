<?php

namespace App\Enums;

use BenSampo\Enum\Enum;


final class Permissions extends Enum
{
    const User = 3;
    const Moderator = 2;
    const Admin = 1;
}
