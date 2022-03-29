<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Http\Resources\InventoryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\ClientService;
use Illuminate\Http\Request;

class ClientsController extends Controller
{
    private ClientService $clientService;

    public function __construct(ClientService $clientsService)
    {
        $this->clientService = $clientsService;
    }

    public function all(): AnonymousResourceCollection
    {
        $clients = $this->clientService->get_clients();

        return ClientResource::collection($clients);
    }

    public function client(string|int $identifier): ClientResource
    {
        $client = $this->clientService->get_client_by_identifier($identifier);

        return new ClientResource($client);
    }

    public function inventory(string|int $identifier): AnonymousResourceCollection
    {
        $items = $this->clientService->get_inventory($identifier);

        return InventoryResource::collection($items);
    }
}
