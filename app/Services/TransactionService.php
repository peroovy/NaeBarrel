<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionService
{
    public function GetAll() {
        $filter = new FilterService();
        return $filter->ManyFilters(Transaction::all()->sortBy('id'), [
            "client" => "client_id",
            "type" => "type"
        ]);
    }
}
