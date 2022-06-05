<?php

namespace Tests\Feature;

use App\Enums\Permissions;
use App\Enums\Qualities;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Permission;
use App\Models\Quality;
use App\Services\ClientsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientsServiceTest extends TestCase
{
    /** @var Client[] */
    private array $clients;

    private Client $client;
    private Client $admin;
    private Client $moderator;

    private ClientsService $service;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ClientsService();

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        $this->clients[] = $this->client = Client::create([
            "login" => "kotic",
            "email" => "pro1337@sobaka.sobaka",
            "password" => "secret",
            "permission" => Permissions::User
        ]);

        Client::create([
            "login" => "sobaka",
            "email" => "kotic@1123",
            "password" => "secret",
            "permission" => Permissions::User
        ]);

        $this->admin = Client::create([
            "login" => "admin",
            "email" => "admin@1123",
            "password" => "secret",
            "permission" => Permissions::Admin
        ]);

        $this->moderator = Client::create([
            "login" => "moder",
            "email" => "moder@1123",
            "password" => "secret",
            "permission" => Permissions::Moderator
        ]);
    }

    public function test_getting_clients()
    {
        foreach ($this->service->get_clients() as $client)
            $this->assertEquals(Permissions::User, $client->permission);
    }

    public function test_getting_client_by_identifier()
    {
        $this->assertEquals($this->client["id"],
            $this->service->get_client_by_identifier($this->client->id)["id"]);

        $this->assertEquals($this->client["id"],
            $this->service->get_client_by_identifier($this->client->login)["id"]);

        $this->assertNull($this->service->get_client_by_identifier($this->admin->id));
        $this->assertNull($this->service->get_client_by_identifier($this->admin->login));
        $this->assertNull($this->service->get_client_by_identifier($this->moderator->id));
        $this->assertNull($this->service->get_client_by_identifier($this->moderator->login));
    }

    public function test_getting_inventory()
    {
        $items = [];

        foreach (Qualities::asArray() as $name => $id)
            Quality::insert(["id" => $id, "name" => $name]);

        $items[] = Item::create([
            "name" => "item_1",
            "description" => "item_1_description",
            "price" => 100,
            "quality" => Qualities::Common,
            "picture" => "item_1.jpg"
        ]);
        $items[] = Item::create([
            "name" => "item_2",
            "description" => "item_2_description",
            "price" => 50,
            "quality" => Qualities::Uncommon,
            "picture" => "item_2.jpg"
        ]);

        foreach ($items as $item)
            Inventory::create(["client_id" => $this->client->id, "item_id" => $item->id]);

        $actual = $this->service->get_inventory($this->client->id);
        $this->assertEquals($actual, $this->service->get_inventory($this->client->login));

        $this->assertEquals(
            array_map(fn(Item $item) => $item->id, $items),
            array_keys($actual->getDictionary())
        );
    }
}
