<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\ItemResource;
use App\Services\FilterService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\ClientsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClientsController extends Controller
{
    private ClientsService $clientService;
    private FilterService $filterService;

    public function __construct(ClientsService $clientService, FilterService $filterService)
    {
        $this->clientService = $clientService;
        $this->filterService = $filterService;
    }

    public function all(): AnonymousResourceCollection
    {
        $clients = $this->clientService->getClients();
        $filtered = $this->filterService->getFiltered($clients, 'permission_id', 'permission');

        return ClientResource::collection($filtered);
    }

    public function client(string|int $identifier): Response | ClientResource
    {
        $client = $this->clientService->getClientByIdentifier($identifier);

        if (!$client)
            return response(status: 404);

        return new ClientResource($client);
    }

    public function inventory(string|int $identifier): AnonymousResourceCollection | Response
    {
        $items = $this->clientService->getInventory($identifier);

        if ($items == null)
            return response(status: 404);

        $filtered = $this->filterService->getFiltered($items, 'quality_id', 'quality');

        return ItemResource::collection($filtered);
    }
}
