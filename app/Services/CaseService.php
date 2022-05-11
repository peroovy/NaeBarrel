<?php

namespace App\Services;

use App\Http\Resources\ItemResource;
use App\Models\CaseItem;
use App\Models\Client;
use App\Models\NBCase;

class CaseService
{
    public function CaseExists(string $name): bool
    {
        return NBCase::where([["name", "=", $name]])->exists();
    }

    public function CreateCase(string $name, string $description, int $price, string $picture, array $items): \Illuminate\Database\Eloquent\Model|bool|NBCase
    {
        if ($this->CaseExists($name)) {
            return false;
        }
        $case = NBCase::create([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'picture' => $picture
        ]);
        $case_items = [];
        foreach ($items as $item_id => $chance) {
            $case_items[] =[
                "case_id" => $case["id"],
                "item_id" => $item_id,
                "chance" => $chance
            ];
        }
        CaseItem::insert($case_items);
        return $case;
    }

    public function OpenCase(NBCase $case) {
        $items = $case->items();
        if (count($items) == 0) {
            return null;
        }
        $winning = random_int(1, 100) / 100;
        $curr = 0.0;
        foreach ($items as $item) {
            $curr += $item["chance"];
            if ($winning <= $curr) {
                return $item;
            }
        }
        return $items->last();
    }
}
