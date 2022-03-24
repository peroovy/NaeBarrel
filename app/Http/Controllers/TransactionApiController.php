<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionApiController extends Controller
{
    public function transactions() {
        return Transaction::all();
    }

    public function index(Transaction $transaction_id) {
        return $transaction_id;
    }

    public function type(int $type_id) {
        return Transaction::all()
            ->where('type', '=', $type_id);
    }
}
