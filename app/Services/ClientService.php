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
            ->getResults();
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
}
