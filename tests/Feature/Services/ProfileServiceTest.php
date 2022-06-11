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
use App\Services\ClientsService;
use App\Services\ProfileService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    private Client $client;
    private int $balance = 10000;

    private Item $common;
    private Item $uncommon;
    private Inventory $common_in_inventory;
    private Inventory $uncommon_in_inventory;

    private ProfileService $service;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ProfileService(new ClientsService(), new TransactionService());

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

        $this->common_in_inventory = Inventory::create([
            "client_id" => $this->client->id,
            "item_id" => $this->common->id
        ]);

        $this->uncommon_in_inventory = Inventory::create([
            "client_id" => $this->client->id,
            "item_id" => $this->uncommon->id
        ]);
    }

    public function test_accrue()
    {
        $amount = 1;
        $new_balance = $this->balance + $amount;
        $this->assertTrue($this->service->tryAccrue($this->client, $amount));

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
        $this->assertFalse($this->service->tryAccrue($this->client, -10));
        $this->assertFalse($this->service->tryAccrue($this->client, 0));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance, $actual->balance);
        $this->assertDatabaseCount(Transaction::class, 0);
    }

    /**
     * @dataProvider get_correct_decreased_counts
     */
    public function test_decreasing_balance(int $count)
    {
        $this->assertTrue($this->service->decreaseBalance($this->client->id, $count));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance - $count, $actual->balance);
    }

    public function get_correct_decreased_counts(): array
    {
        return array([1], [$this->balance - 1], [$this->balance]);
    }

    /**
     * @dataProvider get_bad_decreased_counts
     */
    public function test_decreasing_balance__bad_count(int $count)
    {
        $this->assertFalse($this->service->decreaseBalance($this->client->id, $count));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance, $actual->balance);
    }

    public function get_bad_decreased_counts(): array
    {
        return array([-10], [-1], [0], [$this->balance + 1], [2 * $this->balance]);
    }

    /**
     * @dataProvider get_correct_increased_counts
     */
    public function test_increasing_balance(int $count)
    {
        $this->assertTrue($this->service->increaseBalance($this->client->id, $count));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance + $count, $actual->balance);
    }

    public function get_correct_increased_counts(): array
    {
        return array([1], [10], [$this->balance], [3 * $this->balance]);
    }

    /**
     * @dataProvider get_bad_increased_counts
     */
    public function test_increasing_balance__bad_count(int $count)
    {
        $this->assertFalse($this->service->increaseBalance($this->client, $count));

        $actual = $this->client->refresh();
        $this->assertEquals($this->balance, $actual->balance);
    }

    public function get_bad_increased_counts(): array
    {
        return array([-1], [-10], [-$this->balance], [-3 * $this->balance], [0]);
    }

    public function test_selling_items()
    {
        $expected_coins = $this->common->price + $this->uncommon->price;
        $expected_balance = $this->client->balance + $expected_coins;

        $coins = $this->service->sellItems($this->client->id, [$this->common->id, $this->uncommon->id]);

        $this->client->refresh();

        $this->assertEquals($expected_coins, $coins);
        $this->assertEquals($expected_balance, $this->client->balance);

        foreach ([$this->common, $this->uncommon] as $item)
        {
            $this->assertDatabaseMissing(Inventory::class, [
                "client_id" => $this->client->id,
                "item_id" => $item->id
            ]);
        }

        $this->assertDatabaseHas(Transaction::class, [
            "client_id" => $this->client->id,
            "type" => TransactionTypes::Sale,
            "accrual" => $expected_coins
        ]);
    }
}
