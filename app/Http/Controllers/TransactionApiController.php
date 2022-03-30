<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionApiController extends Controller
{
    private TransactionService $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    public function transactions() {
        return TransactionResource::collection($this->service->GetAll());
    }

    public function index(Transaction $transaction_id) {
        return new TransactionResource($transaction_id);
    }
}
