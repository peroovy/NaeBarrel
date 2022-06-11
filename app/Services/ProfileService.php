<?php

namespace App\Services;

use App\Enums\TransactionTypes;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProfileService
{
    private ClientsService $clientsService;
    private TransactionService $transactionService;

    public function __construct(ClientsService $clientsService, TransactionService $transactionService) {
        $this->clientsService = $clientsService;
        $this->transactionService = $transactionService;
    }


    public function try_accrue(Client $client, int $amount): bool
    {
        if ($amount <= 0)
            return false;

        try
        {
            DB::transaction(function () use ($client, $amount)
            {
                $client->increment('balance', $amount);

                $transaction = $this->transactionService->Create($client->id, $amount, TransactionTypes::Daily);

                $client->update(['last_accrual' => $transaction->created_at]);
            });

            return true;
        }
        catch (Throwable $exception)
        {
            return false;
        }
    }

    public function DecreaseBalance(string|int $identifier, int $count, $transType): bool
    {
        $client = $this->clientsService->get_client_by_identifier($identifier);
        if ($client->balance < $count) {
            return false;
        }
        $client->decrement("balance", $count);
        $this->transactionService->Create($client->id, -$count, $transType);
        return true;
    }

    public function IncreaseBalance(string|int $identifier, int $count, $transType): bool
    {
        $client = $this->clientsService->get_client_by_identifier($identifier);
        $client->increment("balance", $count);
        $this->transactionService->Create($client->id, $count, $transType);
        return true;
    }

    public function AddItem(string|int $identifier, int $item_id) {
        $client = $this->clientsService->get_client_by_identifier($identifier);
        Inventory::create([
            "client_id" => $client->id,
            "item_id" => $item_id
        ]);
    }

    public function SellItems(string|int $identifier, array $ids) {
        $client = $this->clientsService->get_client_by_identifier($identifier);
        $to_delete = Inventory::whereIn("id", $ids)->where([["client_id", "=", $client->id]]);
        $coins = 0;
        $items_count = [];
        foreach ($to_delete->get() as $slot) {
            $item = $slot["item_id"];
            if (!array_key_exists($item, $items_count)) {
                $items_count[$item] = 0;
            }
            $items_count[$item] += 1;
        }
        foreach (array_keys($items_count) as $item) {
            $coins += Item::whereId($item)->first()->price * $items_count[$item];
        }
        $to_delete->delete();
        $this->IncreaseBalance($identifier, $coins, TransactionTypes::Sale);
        return $coins;
    }

    public function DeleteProfile(string|int $identifier) {
        $client = $this->clientsService->get_client_by_identifier($identifier);
        $client->dead = true;
        $client->save();
        return response(status: 200);
    }
}
