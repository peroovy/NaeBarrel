<?php

namespace App\Http\Controllers;

use App\Http\Resources\CaseResource;
use App\Models\NBCase;
use Illuminate\Http\Request;

class CaseApiController extends Controller
{
    public function cases() {
        return CaseResource::collection(NBCase::all());
    }

    public function index(NBCase $case_id) {
        return new CaseResource($case_id);
    }
}
