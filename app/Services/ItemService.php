<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

class ItemService
{
    public function get(): Collection
    {
        $filter = new FilterService();
        return $filter->getFiltered(Item::all(), 'quality', 'quality');
    }

    public function exists(string $name): bool {
        return Item::whereName($name)->exists();
    }

    public function create(string $name, string $description, string $price, int $quality, string $picture): Item | null {
        if ($this->exists($name)) {
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
