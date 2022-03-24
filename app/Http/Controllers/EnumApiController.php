<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Quality;
use App\Models\TransactionType;
use Illuminate\Http\Request;

class EnumApiController extends Controller
{
    public function transaction_type() {
        return TransactionType::all();
    }

    public function permissions() {
        return Permission::all();
    }

    public function qualities() {
        return Quality::all();
    }
}
