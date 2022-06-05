<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionService
{
    public function GetAll(): Collection {
        $filter = new FilterService();
        return $filter->ManyFilters(Transaction::all()->sortBy('created_at'), [
            "client" => "client_id",
            "type" => "type"
        ]);
    }
}
