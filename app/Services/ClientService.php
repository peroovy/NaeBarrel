<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use \Illuminate\Database\Eloquent\Collection;

class ClientService
{
    public function getClients(): Collection
    {
        return Client::all();
    }

    public function getClientByIdentifier(string|int $identifier): Client
    {
        $filter_field = is_numeric($identifier) ? 'clients.id' : 'clients.login';

        return Client::where($filter_field, '=', $identifier)
            ->firstOrFail();
    }

    public function getInventory(string|int $identifier): Collection
    {
        $client = $this->getClientByIdentifier($identifier);

        return $client
            ->hasManyThrough(Item::class, Inventory::class,
                'client_id', 'id', 'id', 'item_id')
            ->select('items.*', 'inventories.id as inventory_id')
            ->get();
    }

    public function decreaseBalance(string|int $identifier, int $count): bool
    {
        $client = $this->getClientByIdentifier($identifier);
        if ($client->balance < $count) {
            return false;
        }
        $client->decrement("balance", $count);
        return true;
    }

    public function increaseBalance(string|int $identifier, int $count): bool
    {
        $client = $this->getClientByIdentifier($identifier);
        $client->increment("balance", $count);
        return true;
    }

    public function addItem(string|int $identifier, int $item_id) {
        $client = $this->getClientByIdentifier($identifier);
        Inventory::create([
            "client_id" => $client->id,
            "item_id" => $item_id
        ]);
    }

    public function sellItems(string|int $identifier, array $ids) {
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
        $this->increaseBalance($identifier, $coins);
        return $coins;
    }
}
