<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Market;

class MarketService
{
    private ClientsService $clientsService;

    public function __construct(ClientsService $clientsService){
        $this->clientsService = $clientsService;
    }

    public function putItem($identifier, $itemId, $price) {
        $client = $this->clientsService->get_client_by_identifier($identifier);
        $item = Inventory::where([["client_id", "=", $client->id], ["item_id", "=", $itemId]]);
        if (!$item->exists()) {
            return response(status: 400);
        }
        $item = $item->first();
        $lot = Market::create([
            "item_id" => $itemId,
            "price" => $price,
            "client_id" => $client->id
        ]);
        $item->delete();
        return $lot;
    }
}
