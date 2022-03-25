<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionType;
use Illuminate\Http\Request;

class TransactionApiController extends Controller
{
    public function transactions() {
        return Transaction::all();
    }

    public function index(Transaction $transaction_id) {
        return $transaction_id;
    }

    public function type(TransactionType $type_id) {
        return $type_id->transactions;
    }
}
