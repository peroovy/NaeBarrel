<?php

namespace App\Services;

use App\Http\Resources\ItemResource;
use App\Models\CaseItem;
use App\Models\Client;
use App\Models\Item;
use App\Models\NBCase;
use http\Exception\UnexpectedValueException;
use Illuminate\Support\Facades\DB;

class CaseService
{
    public static $EPS = 10 ** -10;

    public function CaseExists(string $name): bool
    {
        return NBCase::where([["name", "=", $name]])->exists();
    }

    public function CreateCase(string $name, string $description, int $price, string $picture, array $items): NBCase|null
    {
        if ($this->CaseExists($name))
            return null;

        DB::beginTransaction();
        try
        {
            $case = NBCase::create([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'picture' => $picture
            ]);

            $case_items = [];
            foreach ($items as $item) {
                $case_items[] =[
                    "case_id" => $case->id,
                    "item_id" => $item["id"],
                    "chance" => $item["chance"]
                ];
            }
            CaseItem::insert($case_items);

            DB::commit();
            return $case;
        }
        catch (\Exception $exception)
        {
            DB::rollBack();
            return null;
        }
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
            if ($curr >= $winning) {
                return $item;
            }
        }
        return $items->last();
    }

    public function ValidateItems(array $items): bool
    {
        if (count($items) == 0)
            return true;

        $ids = array_map(fn (array $item) => $item["id"], $items);
        $probability = array_sum(array_map(fn (array $item) => $item["chance"], $items));

        return count($ids) == Item::findMany($ids)->count() && abs($probability - 1) < self::$EPS;
    }
}
