<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Quality;
use Illuminate\Http\Request;

class ItemApiController extends Controller
{
    public function items() {
        return Item::all();
    }

    public function index(Item $item_id) {
        return $item_id;
    }

    public function quality(int $quality_id) {
        return Item::all()
            ->where('quality', '=', $quality_id);
    }
}
