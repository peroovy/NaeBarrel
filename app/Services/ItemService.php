<?php

namespace App\Services;

use App\Models\Item;

class ItemService
{
    public function getAll() {
        $filter = new FilterService();
        return $filter->GetFiltered(Item::all(), 'quality', 'quality');
    }
}
