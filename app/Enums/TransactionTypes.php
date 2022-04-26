<?php

namespace App\Enums;

use BenSampo\Enum\Enum;


final class TransactionTypes extends Enum
{
    const Buying = 0;
    const Sale = 1;
    const Daily = 2;
}
