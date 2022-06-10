<?php

namespace App\Enums;

use BenSampo\Enum\Enum;


final class TransactionTypes extends Enum
{
    const CaseBuying = 3;
    const Sale = 1;
    const Daily = 2;
    const ItemBuying = 4;
}
