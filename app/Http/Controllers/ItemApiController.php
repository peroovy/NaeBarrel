<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\Quality;
use App\Services\ItemService;
use Illuminate\Http\Request;

class ItemApiController extends Controller
{
    private ItemService $service;
    public function __construct(ItemService $service)
    {
        $this->service = $service;
    }

    public function items() {
        return ItemResource::collection($this->service->getAll());
    }

    public function index(Item $item_id) {
        return new ItemResource($item_id);
    }
}
