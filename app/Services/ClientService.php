<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use \Illuminate\Database\Eloquent\Collection;

class ClientService
{
    public function get_clients(): Collection
    {
        return Client::all();
    }

    public function get_client_by_identifier(string|int $identifier): Client
    {
        $filter_field = is_numeric($identifier) ? 'clients.id' : 'clients.login';

        return Client::where($filter_field, '=', $identifier)
            ->firstOrFail();
    }

    public function get_inventory(string|int $identifier): Collection
    {
        $client = $this->get_client_by_identifier($identifier);

        return $client
            ->hasManyThrough(Item::class, Inventory::class,
                'client_id', 'id', 'id', 'item_id')
            ->select('items.*', 'inventories.id as inventory_id')
            ->get();
    }

    public function DecreaseBalance(string|int $identifier, int $count): bool
    {
        $client = $this->get_client_by_identifier($identifier);
        if ($client->balance < $count) {
            return false;
        }
        $client->decrement("balance", $count);
        return true;
    }

    public function IncreaseBalance(string|int $identifier, int $count): bool
    {
        $client = $this->get_client_by_identifier($identifier);
        $client->increment("balance", $count);
        return true;
    }

    public function AddItem(string|int $identifier, int $item_id) {
        $client = $this->get_client_by_identifier($identifier);
        Inventory::create([
            "client_id" => $client->id,
            "item_id" => $item_id
        ]);
    }

    public function SellItems(string|int $identifier, array $ids) {
        $to_delete = Inventory::whereIn("id", $ids);
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
        $this->IncreaseBalance($identifier, $coins);
        return $coins;
    }
}
