<?php

namespace Tests\Feature;

use App\Enums\Permissions;
use App\Enums\Qualities;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Permission;
use App\Models\Quality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\Fluent\Concerns\Interaction;
use Illuminate\Testing\Fluent\Concerns\Matching;
use Tests\TestCase;

class ClientsControllerTest extends TestCase
{
    private Client $user;
    private Client $admin;
    private Client $moder;

    private Item $item;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        $this->user = Client::create([
            "login" => "user",
            "email" => "email@email",
            "password" => "secret",
            "permission" => Permissions::User,
        ]);

        foreach (Qualities::asArray() as $name => $id)
            Quality::insert(["id" => $id, "name" => $name]);

        $this->item = Item::create([
            "name" => "item_1",
            "description" => "item_1_description",
            "price" => 100,
            "quality" => Qualities::Common,
            "picture" => "item_1.jpg"
        ]);

        Inventory::insert(["client_id" => $this->user->id, "item_id" => $this->item->id]);
        $this->user->refresh();

        $this->admin = Client::create([
            "login" => "admin",
            "email" => "email1@email",
            "password" => "secret",
            "permission" => Permissions::Admin
        ]);

        $this->moder = Client::create([
            "login" => "moder",
            "email" => "email2@email",
            "password" => "secret",
            "permission" => Permissions::Moderator
        ]);
    }

    public function test_getting_all()
    {
        $this->get("api/clients")
            ->assertOk()
            ->assertJson(function (AssertableJson $json)
            {
                return $json->has(1)
                    ->has("0", fn (AssertableJson $json) => $this->assert_user($json));
            });
    }

    public function test_getting_one()
    {
        $bad_identifiers = [
            -1, "asdf",
            $this->admin->id, $this->admin->login,
            $this->moder->id, $this->moder->login
        ];

        foreach ($bad_identifiers as $identifier)
            $this->get("api/clients/" . $identifier)->assertNotFound();

        foreach ([$this->user->id, $this->user->login] as $identifier)
        {
            $this->get("api/clients/" . $identifier)
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $this->assert_user($json));
        }
    }

    public function test_getting_inventory()
    {
        $bad_identifiers = [
            -1, "asdf",
            $this->admin->id, $this->admin->login,
            $this->moder->id, $this->moder->login
        ];

        foreach ($bad_identifiers as $identifier)
            $this->get("api/clients/" . $identifier . "/inventory")->assertNotFound();

        foreach ([$this->user->id, $this->user->login] as $identifier)
        {
            $_GET["quality_id"] = $this->item->quality;

            $this->get("api/clients/" . $identifier . "/inventory")
                ->assertOk()
                ->assertJson(function (AssertableJson $json)
                {
                    return $json->has("0", fn (AssertableJson $json) => $this->assert_item($json));
                });

            $_GET["quality_id"] = -1;

            $this->get("api/clients/" . $identifier . "/inventory")
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json->has(0));
        }
    }

    private function assert_user(AssertableJson $json): AssertableJson|Matching
    {
        return $json->where("id", $this->user->id)
            ->where("login", $this->user->login)
            ->where("email", $this->user->email)
            ->where("permission", $this->user->permission)
            ->where("balance", $this->user->balance);
    }

    private function assert_item(AssertableJson $json): Interaction|AssertableJson|Matching
    {
        return $json
            ->where("id", $this->item->id)
            ->where("name", $this->item->name)
            ->where("description", $this->item->description)
            ->where("price", $this->item->price)
            ->where("quality", $this->item->quality)
            ->where("picture", $this->item->picture)
            ->etc();
    }
}
