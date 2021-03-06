<?php

namespace App\Http\Controllers;

use App\Enums\TransactionTypes;
use App\Http\Resources\CaseResource;
use App\Http\Resources\ItemResource;
use App\Models\Client;
use App\Models\Item;
use App\Models\NBCase;
use App\Models\TransactionType;
use App\Services\CaseService;
use App\Services\ClientsService;
use App\Services\ProfileService;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class CaseApiController extends Controller
{
    private CaseService $caseService;

    public function __construct(CaseService $service)
    {
        $this->caseService = $service;
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
            "price" => ["required", "numeric"],
            "picture" => ["required", "file", "mimes:jpeg,jpg,png"]
        ]);

        if ($validator->fails()) {
            return response(status: 400);
        }

        $withItems = array_key_exists("items", $request->all());

        $items = null;

        if ($withItems) {
            try {
                $items = json_decode($request['items']);
            } catch (JsonException $e) {
                return response(status: 400);
            }
        }

        if ($withItems && !$this->caseService->validateItems($items))
            return response(status: 422);

        $case = $this->caseService->createCase(
            $request["name"],
            $request["description"],
            $request["price"],
            $request->file("picture"),
            $withItems ? $items : []);

        return $case ? new CaseResource($case) : response(status: 400);
    }

    public function buy(Request $request) {
        $validator = Validator::make($request->all(), [
           "case_id" => ["required", "numeric"]
        ]);

        if ($validator->fails())
            return response(status: 400);

        $case = $this->caseService->getCase($request["case_id"]);
        if (!$case)
            return response(status: 404);

        $item = $this->caseService->tryPlayRoulette(
            user: Auth::user(),
            case: $case
        );

        return $item ? new ItemResource($item) : response(status: 422);
    }
}
