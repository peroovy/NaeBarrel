<?php

namespace App\Http\Controllers;

use App\Http\Resources\CaseResource;
use App\Http\Resources\ItemResource;
use App\Models\Client;
use App\Models\Item;
use App\Models\NBCase;
use App\Services\CaseService;
use App\Services\ClientsService;
use App\Services\ProfileService;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CaseApiController extends Controller
{
    private CaseService $service;
    private ProfileService $profileService;

    public function __construct(CaseService $service, ProfileService $profileService)
    {
        $this->service = $service;
        $this->profileService = $profileService;
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

    public function buy(Request $request) {
        if (!array_key_exists("case_id", $request->all())) {
            return response(status: 400);
        }
        $case = NBCase::whereId($request["case_id"])->first();
        if (!$case->exists()) {
            return response(status: 400);
        }

        $user = Auth::user();

        if (!$this->profileService->DecreaseBalance($user, $case->price)) {
            return "нету денег";
        }
        $item = $this->service->OpenCase($case);
        if ($item == null) {
            return response(status: 400);
        }
        $this->profileService->AddItem($user, $item["id"]);
        return new ItemResource($item);
    }
}
