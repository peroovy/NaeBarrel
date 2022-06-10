<?php

namespace App\Services;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TransactionService
{
    public function getAll(): Collection {
        $filter = new FilterService();
        return $filter->manyFilters(Transaction::orderBy("updated_at", "DESC")->get(), [
            "client" => "client_id",
            "type" => "type"
        ]);
    }

    public function declare(int $clientId, int $accrual, int $type): Transaction
    {
        return Transaction::create([
            "type" => $type,
            "accrual" => $accrual,
            "client_id" => $clientId
        ]);
    }
}
