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
    public function try_accrue(Client $client, int $amount): bool
    {
        if ($amount <= 0)
            return false;

        try
        {
            DB::transaction(function () use ($client, $amount)
            {
                $client->increment('balance', $amount);

                $transaction = Transaction::create([
                    'type' => TransactionTypes::Daily,
                    'client_id' => $client->id,
                    'accrual' => $amount
                ]);

                $client->update(['last_accrual' => $transaction->created_at]);
            });

            return true;
        }
        catch (Throwable $exception)
        {
            return false;
        }
    }

    public function DecreaseBalance(Client $client, int $count): bool
    {
        if ($count <= 0 || $client->balance < $count)
            return false;

        $client->decrement("balance", $count);

        return true;
    }

    public function IncreaseBalance(Client $client, int $count): bool
    {
        if ($count <= 0)
            return false;

        $client->increment("balance", $count);

        return true;
    }

    public function AddItem(Client $client, int $item_id) {
        Inventory::create([
            "client_id" => $client->id,
            "item_id" => $item_id
        ]);
    }

    public function SellItems(Client $client, array $ids): int
    {
        $to_delete = Inventory::where([["client_id", "=", $client->id]])
            ->whereIn("item_id", $ids);
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

        try
        {
            DB::transaction(function () use ($to_delete, $client, $coins)
            {
                $to_delete->delete();

                Transaction::insert([
                    "client_id" => $client->id,
                    "type" => TransactionTypes::Sale,
                    "accrual" => $coins
                ]);

                $this->IncreaseBalance($client, $coins);
            });

            return $coins;
        }
        catch (Throwable $exception)
        {
            return 0;
        }
    }
}
