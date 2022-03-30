<?php

namespace App\Http\Controllers;

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
        return $this->service->getAll();
    }

    public function index(Item $item_id) {
        return $item_id;
    }
}
