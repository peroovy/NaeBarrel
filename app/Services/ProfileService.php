<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;

class ProfileService
{
    public function get_inventory(Client $client): array
    {
        return $client
            ->hasManyThrough(Item::class, Inventory::class,
                'client_id', 'id', 'id', 'item_id')
            ->getResults();
    }

    public function DecreaseBalance(Client $client, int $count): bool
    {
        if ($client->balance < $count) {
            return false;
        }
        $client->decrement("balance", $count);
        return true;
    }

    public function IncreaseBalance(Client $client, int $count): bool
    {
        $client->increment("balance", $count);
        return true;
    }

    public function AddItem(Client $client, int $item_id) {
        Inventory::create([
            "client_id" => $client->id,
            "item_id" => $item_id
        ]);
    }

    public function SellItems(Client $client, array $ids) {
        $to_delete = Inventory::where([["client_id", "=", $client->id]])
            ->whereIn("item_id", $ids);
        $coins = 0;
        $items_count = [];
        foreach ($to_delete->get() as $slot) {
            $item = $slot["item_id"];
            if (!array_key_exists($item, $items_count)) {
                $items_count[$item] = 0;
            }
            $items_count[$item] += 1;
        }
        foreach (array_keys($items_count) as $item) {
            $coins += Item::whereId($item)->first()->price * $items_count[$item];
        }
        $to_delete->delete();
        $this->IncreaseBalance($client, $coins);
        return $coins;
    }
}
