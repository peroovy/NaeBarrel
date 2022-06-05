<?php

namespace Tests\Feature;

use App\Enums\Permissions;
use App\Enums\TransactionTypes;
use App\Models\Client;
use App\Models\Permission;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    private Client $client;
    private array $transactions;

    private TransactionService $service;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TransactionService();
        $_GET = [];

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        $this->client = Client::create([
            "login" => "sobaka",
            "email" => "kotic@1123",
            "password" => "secret",
            "permission" => Permissions::User
        ]);

        $another = Client::create([
            "login" => "kotic",
            "email" => "sobaka@1123",
            "password" => "secret",
            "permission" => Permissions::User
        ]);

        foreach (TransactionTypes::asArray() as $name => $id)
        {
            TransactionType::insert(["id" => $id, "name" => $name]);
            $this->transactions[] = Transaction::create([
                "client_id" => $this->client->id,
                "type" => $id,
                "accrual" => 10
            ]);
        }

        $this->transactions[] = Transaction::create([
            "client_id" => $another->id,
            "type" => TransactionTypes::Buying,
            "accrual" => 10
        ]);
    }

    public function test_getting_all()
    {
        $actual = $this->service->GetAll()->getQueueableIds();

        $this->assertEquals(
            array_map(fn(Transaction $transaction) => $transaction->id, $this->transactions),
            $actual
        );
    }

    public function test_getting_all__specific_client()
    {
        $_GET["client"] = $this->client->id;
        $this->assert_getting_all(Transaction::where("client_id", "=", $_GET["client"]));
    }

    public function test_getting_all__specific_type()
    {
        $_GET["type"] = TransactionTypes::Buying;
        $this->assert_getting_all(Transaction::where("type", "=", $_GET["type"]));
    }

    private function assert_getting_all(Builder $where)
    {
        $actual = array_values($this->service->GetAll()->getDictionary());
        $expected = array_values($where->get()->getDictionary());

        $this->assertEquals($expected, $actual);
    }
}
