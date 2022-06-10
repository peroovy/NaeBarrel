<?php

namespace App\Services;

use App\Enums\Permissions;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use \Illuminate\Database\Eloquent\Collection;

class ClientsService
{
    public function getClients(): array|Collection
    {
        return $this->filterClients()->get();
    }

    public function getClientByIdentifier(string|int $identifier): Client | null
    {
        $filter_field = is_numeric($identifier) ? 'clients.id' : 'clients.login';

        return $this->filterClients()
            ->where($filter_field, '=', $identifier)
            ->first();
    }

    public function getInventory(string|int $identifier): Collection | null
    {
        $client = $this->getClientByIdentifier($identifier);

        return $client
            ?->hasManyThrough(Item::class, Inventory::class,
                'client_id', 'id', 'id', 'item_id')
            ->select('items.*', 'inventories.id as inv_id')
            ->getResults();
    }

    private function filterClients(): Builder|Client
    {
        return Client::where("permission", "=", Permissions::User);
    }
}
