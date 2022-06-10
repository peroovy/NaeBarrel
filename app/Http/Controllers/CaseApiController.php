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
            "name" => ["required", "string"],
            "description" => ["required", "string"],
            "price" => ["required", "integer"],
            "picture" => ["required"],
            "items" => ["sometimes", "array"],
            "items.*.id" => ["required", "integer"],
            "items.*.chance" => ["required", "numeric", "gt:0"],
        ]);

        if ($validator->fails()) {
            return response(status: 400);
        }

        $withItems = array_key_exists("items", $request->all());

        if ($withItems && !$this->service->ValidateItems($request["items"]))
            return response(status: 422);

        $case = $this->service->CreateCase(
            $request["name"],
            $request["description"],
            $request["price"],
            $request["picture"],
            $withItems ? $request["items"] : []);

        return $case ? new CaseResource($case) : response(status: 400);
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
