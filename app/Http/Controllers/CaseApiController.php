<?php

namespace App\Http\Controllers;

use App\Http\Resources\CaseResource;
use App\Models\Client;
use App\Models\Item;
use App\Models\NBCase;
use App\Services\CaseService;
use App\Services\ClientService;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CaseApiController extends Controller
{
    private CaseService $service;
    private ClientService $clientService;

    public function __construct(CaseService $service, ClientService $clientService)
    {
        $this->service = $service;
        $this->clientService = $clientService;
    }

    public function cases() {
        return CaseResource::collection(NBCase::all());
    }

    public function index(NBCase $case_id) {
        return new CaseResource($case_id);
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            "name" => ["required"],
            "description" => ["required"],
            "price" => ["required"],
            "picture" => ["required"],
            "items" => ["sometimes"]
        ]);

        if ($validator->fails()) {
            return response(status: 400);
        }

        $withItems = array_key_exists("items", $request->all());


        if ($withItems) {
            $item_ids = array_keys($request["items"]);
            $items = Item::findMany($item_ids);
            if (count($items) != count($item_ids) ||
                array_sum($request["items"]) != 1) {
                return response(status: 400);
            }
        }

        $case = $this->service->CreateCase(
            $request["name"],
            $request["description"],
            $request["price"],
            $request["picture"],
            $withItems ? $request["items"] : []);

        if ($case) {
            return new CaseResource($case);
        }
        return response(status: 400);
    }

    public function buy(NBCase $case_id) {
        if (!$this->clientService->DecreaseBalance(Auth::user()->id, $case_id->price)) {
            return "нету денег";
        }
        return $this->service->OpenCase($case_id);
    }
}
