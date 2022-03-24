<?php

namespace App\Http\Controllers;

use App\Models\NBCase;
use Illuminate\Http\Request;

class CaseApiController extends Controller
{
    public function cases() {
        return NBCase::all();
    }

    public function index(NBCase $case) {
        return $case;
    }

    public function items(NBCase $case) {
        return $case->items;
    }
}
