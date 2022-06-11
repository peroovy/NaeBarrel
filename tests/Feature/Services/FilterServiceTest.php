<?php

namespace Tests\Feature;

use App\Enums\Permissions;
use App\Models\Client;
use App\Models\Permission;
use App\Services\FilterService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterServiceTest extends TestCase
{
    private Collection $clients;
    private Client $client;

    private FilterService $service;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FilterService();

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        $this->client = Client::create([
            "login" => "kotic",
            "email" => "neprosto",
            "password" => "secret",
            "permission" => Permissions::User
        ]);

        Client::create([
            "login" => "sobaka",
            "email" => "prosto",
            "password" => "secret",
            "permission" => Permissions::User
        ]);

        $this->clients = Client::all();
        $_GET = [];
    }

    public function test_getting_filtered()
    {
        foreach ($this->clients as $client)
        {
            $_GET["id"] = (string)$client->id;
            $_GET["login"] = $client->login;

            foreach (["id", "login"] as $param)
            {
                $actual = array_values($this->service->getFiltered($this->clients, $param, $param)->all());
                $this->assertCount(1, $actual);
                $this->assertEquals($client["id"], $actual[0]["id"]);
            }
        }
    }

    public function test_getting_filtered__get_is_empty()
    {
        $_GET = [];
        $this->assertEquals($this->clients, $this->service->getFiltered($this->clients, "id", "id"));
    }

    public function test_getting_filtered__value_does_not_exist()
    {
        $_GET = ["login" => "unknown"];
        $actual = $this->service->getFiltered($this->clients, "login", "login")->all();
        $this->assertCount(0, $actual);
    }

    public function test_many_filters()
    {
        foreach ($this->clients as $client)
        {
            $_GET["id"] = (string)$client->id;
            $_GET["login"] = $client->login;

            $filter = [
                "id" => "id",
                "login" => "login"
            ];

            $actual = array_values($this->service->manyFilters($this->clients, $filter)->all());
            $this->assertCount(1, $actual);
            $this->assertEquals($client["id"], $actual[0]["id"]);
        }
    }

    public function test_many_filters__unknown_first_parameter()
    {
        $this->assert_many_filters__not_found(-1, $this->client->login);
    }

    public function test_many_filters__unknown_last_parameter()
    {
        $this->assert_many_filters__not_found($this->client->id, "unknown");
    }

    public function test_many_filters__unknown_all_parameters()
    {
        $this->assert_many_filters__not_found(-1, "unknown");
    }

    private function assert_many_filters__not_found(int $id, string $login)
    {
        $_GET["id"] = (string)$id;
        $_GET["login"] = $login;

        $filter = [
            "id" => "id",
            "login" => "login"
        ];

        $actual = array_values($this->service->manyFilters($this->clients, $filter)->all());
        $this->assertCount(0, $actual);
    }
}
