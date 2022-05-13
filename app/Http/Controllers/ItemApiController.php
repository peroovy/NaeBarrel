<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\Quality;
use App\Services\ItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemApiController extends Controller
{
    private ItemService $service;
    public function __construct(ItemService $service)
    {
        $this->service = $service;
    }

    public function items() {
        return ItemResource::collection($this->service->GetAll());
    }

    public function index(Item $item_id) {
        return new ItemResource($item_id);
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            "name" => ["required"],
            "description" => ["required"],
            "price" => ["required"],
            "picture" => ["required"],
            "quality" => ["sometimes"]
        ]);
        if ($validator->fails()) {
            return response(status: 400);
        }
        $item = $this->service->CreateItem(
            $request["name"],
            $request["description"],
            $request["price"],
            $request["quality"],
            $request["picture"]
        );
        if ($item) {
            return new ItemResource($item);
        }
        return response(status: 400);
    }
}
