<?php

namespace App\Enums;

use BenSampo\Enum\Enum;


final class Permissions extends Enum
{
    const User = 0;
    const Moderator = 1;
    const Admin = 2;
}
