<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    public function inventory(): AnonymousResourceCollection
    {
        $items = $this->profileService->get_inventory(Auth::user());

        return ItemResource::collection($items);
    }
}
