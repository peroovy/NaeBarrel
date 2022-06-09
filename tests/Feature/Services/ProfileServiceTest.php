<?php

namespace Tests\Feature;

use App\Enums\Permissions;
use App\Enums\Qualities;
use App\Enums\TransactionTypes;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Permission;
use App\Models\Quality;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    private Client $client;
    private int $balance = 100;

    private Item $common;
    private Item $uncommon;
    private int $common_count = 2;
    private int $uncommon_count = 4;

    private ProfileService $service;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ProfileService();

        foreach (TransactionTypes::asArray() as $name => $id)
            TransactionType::insert(["id" => $id, "name" => $name]);

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        $this->client = Client::create([
            "login" => "login1",
            "email" => "email1",
            "password" => "secret1",
            "permission" => Permissions::User
        ]);

        $this->client->balance = $this->balance;
        $this->client->save();

        foreach (Qualities::asArray() as $name => $id)
            Quality::insert(["id" => $id, "name" => $name]);

        $this->common = Item::create([
            "name" => "item_1",
            "description" => "item_1_description",
            "price" => 100,
            "quality" => Qualities::Common,
            "picture" => "item_1.jpg"
        ]);
        $this->uncommon = Item::create([
            "name" => "item_2",
            "description" => "item_2_description",
            "price" => 50,
            "quality" => Qualities::Uncommon,
            "picture" => "item_2.jpg"
        ]);

        for ($i = 0; $i < $this->common_count; $i++)
        {
            Inventory::insert([
                "client_id" => $this->client->id,
                "item_id" => $this->common->id
            ]);
        }

        for ($i = 0; $i < $this->uncommon_count; $i++)
        {
            Inventory::insert([
                "client_id" => $this->client->id,
                "item_id" => $this->uncommon->id
            ]);
        }
    }

    public function test_accrue()
    {
        $amount = 1;
        $new_balance = $this->balance + $amount;
        $this->assertTrue($this->service->try_accrue($this->client, $amount));

        $actual = $this->client->refresh();
        $this->assertEquals($new_balance, $actual->balance);

        $this->assertDatabaseCount(Transaction::class, 1);
        $transaction = Transaction::where("client_id", "=", $this->client->id)
            ->where("type", "=", TransactionTypes::Daily)
            ->firstWhere("accrual", "=", $amount);
        $this->assertNotNull($transaction);

        $this->assertEquals($transaction->created_at, $actual->last_accrual);
    }

    public function test_accrue__invalid_amount()
    {
        $this->assertFalse($this->service->try_accrue($this->client, -10));
        $this->assertFalse($this->service->try_accrue($this->client, 0));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance, $actual->balance);
        $this->assertDatabaseCount(Transaction::class, 0);
    }

    /**
     * @dataProvider get_correct_decreased_counts
     */
    public function test_decreasing_balance(int $count)
    {
        $this->assertTrue($this->service->DecreaseBalance($this->client, $count));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance - $count, $actual->balance);
    }

    public function get_correct_decreased_counts(): array
    {
        return array([1, $this->balance - 1, $this->balance]);
    }

    /**
     * @dataProvider get_bad_decreased_counts
     */
    public function test_decreasing_balance__bad_count(int $count)
    {
        $this->assertFalse($this->service->DecreaseBalance($this->client, $count));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance, $actual->balance);
    }

    public function get_bad_decreased_counts(): array
    {
        return array([-10, -1, 0, $this->balance + 1, 2 * $this->balance]);
    }

    /**
     * @dataProvider get_correct_increased_counts
     */
    public function test_increasing_balance(int $count)
    {
        $this->assertTrue($this->service->IncreaseBalance($this->client, $count));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance + $count, $actual->balance);
    }

    public function get_correct_increased_counts(): array
    {
        return array([1, 10, $this->balance, 3 * $this->balance]);
    }

    /**
     * @dataProvider get_bad_increased_counts
     */
    public function test_increasing_balance__bad_count(int $count)
    {
        $this->assertFalse($this->service->IncreaseBalance($this->client, $count));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance, $actual->balance);
    }

    public function get_bad_increased_counts(): array
    {
        return array([-1, -10, -$this->balance, -3 * $this->balance, 0]);
    }

    /**
     * @dataProvider get_common_and_uncommon_counts
     */
    public function test_selling_items(int $common_count, int $uncommon_count)
    {
        $expected_coins = min($this->common_count, $common_count) * $this->common->price
            + min($this->uncommon_count, $uncommon_count) * $this->uncommon->price;

        $ids = array_merge(array_fill(0, $common_count, $this->common->id),
            array_fill(0, $uncommon_count, $this->uncommon->id));

        $actual_coins = $this->service->SellItems($this->client, $ids);
        $this->assertEquals($expected_coins, $actual_coins);

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance + $expected_coins, $actual->balance);

        $this->assertTrue(Transaction::where("client_id", "=", $this->client->id)
            ->where("accrual", "=", $expected_coins)
            ->where("type", "=", TransactionTypes::Sale)
            ->exists()
        );
    }

    public function get_common_and_uncommon_counts(): array
    {
        return array(
            [$this->common_count, $this->uncommon_count],
            [$this->common_count, 0],
            [0, $this->uncommon_count],
            [$this->common_count - 1, 0],
            [0, $this->uncommon_count - 1],
            [2 * $this->common_count, $this->uncommon_count],
            [2 * $this->common_count, 2 * $this->uncommon_count],
            [1, 1],
            [1, $this->uncommon_count - 1],
            [$this->common_count - 1, 1],
            [0, 0],
        );
    }
}
