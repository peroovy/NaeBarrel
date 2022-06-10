<?php

namespace App\Services;

use App\Enums\TransactionTypes;
use App\Http\Resources\ItemResource;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Market;

class MarketService
{
    private ClientsService $clientsService;
    private ProfileService $profileService;

    public function __construct(ClientsService $clientsService, ProfileService $profileService){
        $this->clientsService = $clientsService;
        $this->profileService = $profileService;
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

    public function buyItem($identifier, $lotId) {
        $lot = Market::where([["id", "=", $lotId]]);
        if (!$lot->exists()) {
            return response(status: 400);
        }
        $lot = $lot->first();
        if (!$this->profileService->decreaseBalance($identifier, $lot['price'], TransactionTypes::ItemBuying)) {
            return ["error_status" => "NotEnoughMoney"];
        }
        $this->profileService->increaseBalance($lot['client_id'], $lot['price'], TransactionTypes::Sale);
        $this->profileService->addItem($identifier, $lot['item_id']);
        $item = Item::whereId($lot['item_id'])->first();
        $lot->delete();
        return new ItemResource($item);
    }
}
