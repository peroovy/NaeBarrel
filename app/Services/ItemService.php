<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

class ItemService
{
    public function GetAll(): Collection
    {
        $filter = new FilterService();
        return $filter->GetFiltered(Item::all(), 'quality', 'quality');
    }
}
