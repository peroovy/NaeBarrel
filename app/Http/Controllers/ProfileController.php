<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ItemResource;
use App\Http\Resources\TransactionResource;
use App\Services\ClientsService;
use App\Services\ProfileService;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService,
        private ClientsService $clientsService
    )
    {

    }

    public function profile(): ClientResource
    {
        return new ClientResource(Auth::user());
    }

    public function inventory(): AnonymousResourceCollection
    {
        $items = $this->clientsService->get_inventory(Auth::user()->id);

        return ItemResource::collection($items);
    }

    public function accrue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails())
            return response(status: 400);

        $is_success = $this->profileService->tryAccrue(Auth::user(), $request['amount']);

        return response(status: $is_success ? 200 : 500);
    }
}
