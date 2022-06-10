<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketResource;
use App\Models\Market;
use App\Services\MarketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MarketApiController extends Controller
{
    private MarketService $marketService;

    public function __construct(MarketService $marketService){
        $this->marketService = $marketService;
    }

    public function all() {
        return MarketResource::collection(Market::orderBy('id', 'DESC')->get());
    }

    public function putItem(Request $request) {
        $validator = Validator::make($request->all(), [
            "inventory_id" => ["required"],
            "price" => ["required"]
        ]);
        if ($validator->fails()) {
            return response(status: 400);
        }

        return $this->marketService->putItem(Auth::user()->id, $request['inventory_id'], $request['price']);
    }

    public function buyItem(Request $request) {
        $validator = Validator::make($request->all(), [
            "lot_id" => ["required"]
        ]);
        if ($validator->fails()) {
            return response(status: 400);
        }

        return $this->marketService->buyItem(Auth::user()->id, $request['lot_id']);
    }
}
