<?php

namespace Tests\Feature\Controllers;

use App\Enums\Permissions;
use App\Enums\Qualities;
use App\Enums\TransactionTypes;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Permission;
use App\Models\Quality;
use App\Models\TransactionType;
use App\Services\AuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ItemControllerTest extends TestCase
{
    private Client $moder;
    private array $headers;

    private UploadedFile $file;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = UploadedFile::fake()->create("test.png");


        foreach (Qualities::asArray() as $name => $id)
            Quality::insert(["id" => $id, "name" => $name]);

        foreach (TransactionTypes::asArray() as $name => $id)
            TransactionType::insert(["id" => $id, "name" => $name]);

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        $this->moder = Client::create([
            "login" => "login",
            "email" => "email@email",
            "password" => "secret",
            "permission" => Permissions::Moderator
        ]);
        $this->moder->balance = 10000;
        $this->moder->save();
        $this->moder->refresh();

        $this->headers = [
            "Authorization" => "Bearer " . $this->moder->createToken(AuthenticationService::$TOKEN_NAME)
                    ->plainTextToken
        ];
    }

    /**
     * @dataProvider provide_creating_bad_requests
     */
    public function test_creating__bad_request(array $body)
    {
        $this->postJson("api/items", $body, $this->headers)
            ->assertStatus(400);

        $this->assertDatabaseCount(Item::class, 0);
    }

    public function provide_creating_bad_requests(): array
    {
        return [
            [["name" => "abs"]],
            [["description" => "abs"]],
            [["price" => 123]],
            [["quality" => Qualities::Common]],
            [["name" => 123, "description" => "b", "price" => 123, "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => null, "description" => "b", "price" => 123, "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => [], "description" => "b", "price" => 123, "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => true, "description" => "b", "price" => 123, "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => "a", "description" => 123, "price" => 123, "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => "a", "description" => null, "price" => 123, "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => "a", "description" => [], "price" => 123, "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => "a", "description" => true, "price" => 123, "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => "a", "description" => "b", "price" => "a", "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => "a", "description" => "b", "price" => null, "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => "a", "description" => "b", "price" => [], "picture" => "1.jpg", "quality" => Qualities::Common]],
            [["name" => "a", "description" => "b", "price" => false, "picture" => "1.jpg", "quality" => Qualities::Common]],
        ];
    }

    public function test_creating()
    {
        $body = [
            "name" => "1",
            "description" => "a",
            "price" => 1,
            "quality" => Qualities::Common,
            "picture" => $this->file
        ];

        $this->postJson("api/items", $body, $this->headers)->assertCreated();
        $this->postJson("api/items", $body, $this->headers)->assertStatus(422);

        $this->assert_file_exists();
    }

    /**
     * @dataProvider provide_selling_bad_requests
     */
    public function test_selling__bad_request(array $body)
    {
        $expected_balance = $this->moder->balance;

        $this->postJson("api/items/sell", $body, $this->headers)
            ->assertStatus(400);

        $this->moder->refresh();

        $this->assertEquals($expected_balance, $this->moder->balance);
    }

    public function provide_selling_bad_requests(): array
    {
        return [
            [[]],
            [["item_ids" => 1]],
            [["item_ids" => false]],
            [["item_ids" => []]],
            [["item_ids" => null]],
            [["item_ids" => "a"]],
            [["item_ids" => [1, "a"]]],
            [["item_ids" => ["b", "a"]]],
            [["item_ids" => [false, "a"]]],
            [["item_ids" => [false, 1]]],
        ];
    }

    public function test_selling()
    {

        $client = Client::create([
            "login" => "asdfzxcvzxcvadf",
            "email" => "asdf@214123",
            "password" => "secret",
            "permission" => Permissions::User
        ]);

        $headers = [
            "Authorization" => "Bearer " . $client->createToken(AuthenticationService::$TOKEN_NAME)
                    ->plainTextToken
        ];

        $client->balance = $expected_balance = 10000;
        $client->save();

        $item1 = Item::create([
            "name" => "item1",
            "description" => "description2",
            "price" => 30,
            "quality" => Qualities::Uncommon,
            "picture" => $this->file
        ]);

        $item2 = Item::create([
            "name" => "item2",
            "description" => "description2",
            "price" => 43,
            "quality" => Qualities::Uncommon,
            "picture" => "item.2jpg"
        ]);

        $item1_in_inv = Inventory::create(["client_id" => $client->id, "item_id" => $item1->id]);
        $item2_in_inv = Inventory::create(["client_id" => $client->id, "item_id" => $item2->id]);

        $this->postJson("api/items/sell", ["item_ids" => [-1]], $headers)
            ->assertStatus(422);
        $client->refresh();
        $this->assertEquals($expected_balance, $client->balance);

        $this->postJson("api/items/sell", ["item_ids" => [$item1_in_inv->id, $item2_in_inv->id]], $headers)
            ->assertOk();
        $client->refresh();
        $this->assertEquals($expected_balance + $item1->price + $item2->price, $client->balance);

        $this->postJson("api/items/sell", ["item_ids" => [$item1_in_inv->id, $item2_in_inv->id]], $headers)
            ->assertStatus(422);
    }

    private function assert_file_exists()
    {
        $path = "public\\uploads\\items\\" . $this->file->hashName();

        \Storage::assertExists($path);

        \Storage::delete($path);
    }
}
