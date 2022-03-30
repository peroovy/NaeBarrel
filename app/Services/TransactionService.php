<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionService
{
    public function GetAll() {
        $filter = new FilterService();
        return $filter->ManyFilters(Transaction::all(), [
            "client" => "client_id",
            "type" => "type"
        ]);
    }
}
