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

    public function ItemExists(string $name): bool {
        return Item::whereName($name)->exists();
    }

    public function CreateItem(string $name, string $description, string $price, int $quality, string $picture): Item | null {
        if ($this->ItemExists($name)) {
            return null;
        }
        return Item::create([
            "name" => $name,
            "description" => $description,
            "price" => $price,
            "quality" => $quality,
            "picture" => $picture
        ]);
    }
}
