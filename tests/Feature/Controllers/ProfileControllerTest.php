<?php

namespace Tests\Feature\Controllers;

use App\Enums\Permissions;
use App\Enums\TransactionTypes;
use App\Models\Client;
use App\Models\Permission;
use App\Models\TransactionType;
use App\Services\AuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PhpParser\Node\Expr\CallLike;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    private Client $client;
    private array $headers;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        foreach (TransactionTypes::asArray() as $name => $id)
            TransactionType::insert(["id" => $id, "name" => $name]);

        $this->client = Client::create([
            "login" => "login",
            "email" => "email@email",
            "password" => "secret",
            "permission" => Permissions::User
        ]);

        $this->client->balance = 1000;
        $this->client->save();
        $this->headers = [
            "Authorization" => "Bearer " . $this->client->createToken(AuthenticationService::$TOKEN_NAME)
                    ->plainTextToken
        ];
    }

    /**
     * @dataProvider provide_accrue_bad_requests
     */
    public function test_accrue__bad_request(array $body)
    {
        $expected = $this->client->balance;

        $this->postJson("api/profile/accrue", $body, $this->headers)
            ->assertStatus(400);

        $this->client->refresh();
        $this->assertEquals($expected, $this->client->balance);
    }

    public function provide_accrue_bad_requests(): array
    {
        return [
            [[]],
            [["amount" => 0]],
            [["amount" => null]],
            [["amount" => false]],
            [["amount" => []]],
        ];
    }

    public function test_accrue()
    {
        $accrual = 103;
        $expected = $this->client->balance + $accrual;

        $this->postJson("api/profile/accrue", ["amount" => $accrual], $this->headers)
            ->assertOk();

        $this->client->refresh();
        $this->assertEquals($expected, $this->client->balance);
    }
}
