<?php

namespace App\Http\Controllers;

use App\Http\Resources\CaseResource;
use App\Models\Item;
use App\Models\NBCase;
use App\Services\CaseService;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaseApiController extends Controller
{
    private CaseService $service;

    public function __construct(CaseService $service)
    {
        $this->service = $service;
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
            //исправить
            foreach (array_keys($request["items"]) as $item_id) {
                if (!Item::whereId($item_id)->exists()) {
                    return response(status: 400);
                }
            }
            if (array_sum($request["items"]) != 1) {
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
}
