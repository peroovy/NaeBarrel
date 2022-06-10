<?php

namespace App\Services;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionService
{
    public function GetAll(): Collection {
        $filter = new FilterService();
        return $filter->ManyFilters(Transaction::orderBy("updated_at", "DESC")->get(), [
            "client" => "client_id",
            "type" => "type"
        ]);
    }

    public function Create($clientId, $accrual, $type) {
        return new TransactionResource(Transaction::create([
            "type" => $type,
            "accrual" => $accrual,
            "client_id" => $clientId
        ]));
    }
}
