<?php

namespace App\Services;

use App\Enums\TransactionTypes;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\NBCase;
use App\Models\Transaction;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProfileService
{
    public function __construct(
        private ClientsService $clientsService,
        private TransactionService $transactionService,
    )
    {
    }

    public function tryAccrue(Client $client, int $amount): bool
    {
        if ($amount <= 0)
            return false;

        try
        {
            DB::transaction(function () use ($client, $amount)
            {
                $client->increment('balance', $amount);

                $transaction = $this->transactionService->declare($client->id, $amount, TransactionTypes::Daily);

                $client->update(['last_accrual' => $transaction->created_at]);
            });

            return true;
        }
        catch (Throwable $exception)
        {
            return false;
        }
    }

    public function decreaseBalance(string|int $identifier, int $count): bool
    {
        if ($count <= 0)
            return false;

        $client = $this->clientsService->getClientByIdentifier($identifier);
        if ($client->balance < $count) {
            return false;
        }
        $client->decrement("balance", $count);

        return true;
    }

    public function increaseBalance(string|int $identifier, int $count): bool
    {
        if ($count <= 0)
            return false;

        $client = $this->clientsService->getClientByIdentifier($identifier);
        $client->increment("balance", $count);

        return true;
    }

    public function addItem(string|int $identifier, int $item_id) {
        $client = $this->clientsService->getClientByIdentifier($identifier);
        Inventory::create([
            "client_id" => $client->id,
            "item_id" => $item_id
        ]);
    }

    public function sellItems(string|int $client_identifier, array $ids): int|null
    {
        $client = $this->clientsService->getClientByIdentifier($client_identifier);

        if (!$client)
            throw new Exception("client is null");

        $to_delete = Inventory::whereIn("id", $ids)->where("client_id", "=", $client->id);
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

        DB::beginTransaction();
        try
        {
            $to_delete->delete();

            $this->increaseBalance($client_identifier, $coins);

            $this->transactionService->declare($client->id, $coins, TransactionTypes::Sale);

            DB::commit();
            return $coins;
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            return null;
        }
    }
}
