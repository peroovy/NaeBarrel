<?php

namespace App\Services;

use App\Models\CaseItem;
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
}
