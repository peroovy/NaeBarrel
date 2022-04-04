<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\ItemResource;
use App\Services\FilterService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\ClientService;
use Illuminate\Http\Request;

class ClientsController extends Controller
{
    private ClientService $clientService;
    private FilterService $filterService;

    public function __construct(ClientService $clientService, FilterService $filterService)
    {
        $this->clientService = $clientService;
        $this->filterService = $filterService;
    }

    public function all(): AnonymousResourceCollection
    {
        $clients = $this->clientService->get_clients();
        $filtered = $this->filterService->GetFiltered($clients, 'permission_id', 'permission');

        return ClientResource::collection($filtered);
    }

    public function client(string|int $identifier): ClientResource
    {
        $client = $this->clientService->get_client_by_identifier($identifier);

        return new ClientResource($client);
    }

    public function inventory(string|int $identifier): AnonymousResourceCollection
    {
        $items = $this->clientService->get_inventory($identifier);
        $filtered = $this->filterService->GetFiltered($items, 'quality_id', 'quality');

        return ItemResource::collection($filtered);
    }
}
