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
    public function get_clients(): array|Collection
    {
        return $this->filter_clients()->get();
    }

    public function get_client_by_identifier(string|int $identifier): Client | null
    {
        $filter_field = is_numeric($identifier) ? 'clients.id' : 'clients.login';

        return $this->filter_clients()
            ->where($filter_field, '=', $identifier)
            ->first();
    }

    public function get_inventory(string|int $identifier): Collection | null
    {
        $client = $this->get_client_by_identifier($identifier);

        return $client
            ?->hasManyThrough(Item::class, Inventory::class,
                'client_id', 'id', 'id', 'item_id')
            ->getResults();
    }

    private function filter_clients(): Builder|Client
    {
        return Client::where("permission", "=", Permissions::User);
    }
}
