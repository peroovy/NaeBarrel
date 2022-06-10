<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketResource;
use App\Models\Market;
use Illuminate\Http\Request;

class MarketApiController extends Controller
{
    public function all() {
        return MarketResource::collection(Market::orderBy('id', 'DESC')->get());
    }
}
