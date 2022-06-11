<?php

namespace Tests\Feature\Controllers;

use App\Enums\Permissions;
use App\Enums\Qualities;
use App\Models\CaseItem;
use App\Models\Client;
use App\Models\Item;
use App\Models\NBCase;
use App\Models\Permission;
use App\Models\Quality;
use App\Services\AuthenticationService;
use App\Services\CaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\Fluent\Concerns\Matching;
use Tests\TestCase;

class CaseControllerTest extends TestCase
{
    // TODO: add buy tests

    /**
     * @var NBCase[]
     */
    private array $cases;

    private NBCase $case;
    private Item $item1;
    private Item $item2;

    private Client $client;
    private array $headers;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        $this->client = Client::create([
            "login" => "login",
            "email" => "email@email",
            "password" => "secret",
            "permission" => Permissions::Admin
        ]);
        $this->headers = [
            "Authorization" => "Bearer " . $this->client->createToken(AuthenticationService::$TOKEN_NAME)
                    ->plainTextToken
        ];

        foreach (Qualities::asArray() as $name => $id)
            Quality::insert(["id" => $id, "name" => $name]);

        $this->item1 = Item::create([
            "name" => "item",
            "description" => "description",
            "price" => 10,
            "quality" => Qualities::Common,
            "picture" => "item.jpg"
        ]);

        $this->item2 = Item::create([
            "name" => "item2",
            "description" => "description2",
            "price" => 30,
            "quality" => Qualities::Uncommon,
            "picture" => "item.2jpg"
        ]);

        $this->cases[] = $this->case = NBCase::create([
            "name" => "case1",
            "description" => "desc1",
            "price" => 100,
            "picture" => "pic1.jpg",
        ]);

        CaseItem::insert(["case_id" => $this->case->id, "item_id" => $this->item1->id, "chance" => 1]);

        $this->cases[] = NBCase::create([
            "name" => "case2",
            "description" => "desc2",
            "price" => 20,
            "picture" => "pic2.jpg",
        ]);
    }

    public function test_getting_cases()
    {
        $response = $this->get("api/cases", $this->headers);

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json)
        {
            return $json->has(2)
                ->first(fn (AssertableJson $json) => $this->assert_case($json));
        });
    }

    public function test_getting_case()
    {
        $this->get("api/cases/-1", $this->headers)->assertNotFound();

        $response = $this->get("api/cases/" . $this->case->id, $this->headers);

        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $this->assert_case($json));
    }

    /**
     * @dataProvider provide_bad_creating_bodies
     */
    public function test_creating_case__validation_error(array $body)
    {
        $this->postJson("api/cases", $body, $this->headers)
            ->assertStatus(400);

        if (array_key_exists("items", $body))
            unset($body["items"]);

        if ($body)
            $this->assertDatabaseMissing(NBCase::class, $body);
    }

    public function provide_bad_creating_bodies(): array
    {
        return array(
          [["name" => "123"]],
          [["description" => "123"]],
          [["price" => 123]],
          [["picture" => "1.jpg"]],
          [["items" => []]],
          [["name" => 123, "description" => "desc", "price" => 123, "picture" => "1.jpg", "items" => []]],
          [["name" => null, "description" => "desc", "price" => 123, "picture" => "1.jpg", "items" => []]],
          [["name" => "123", "description" => 123, "price" => 123, "picture" => "1.jpg", "items" => []]],
          [["name" => "123", "description" => null, "price" => 123, "picture" => "1.jpg", "items" => []]],
          [["name" => "123", "description" => "desc", "price" => null, "picture" => "1.jpg", "items" => []]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => null, "items" => []]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => "null.jpg", "items" => 123]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => "null.jpg", "items" => "123"]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => "null.jpg", "items" => null]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => "null.jpg", "items" => [["id" => 1]]]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => "null.jpg", "items" => [["chance" => 1.2]]]],
        );
    }

    public function test_creating_case__unique_error()
    {
        $body = [
            "name" => $this->case->name,
            "description" => "desc",
            "price" => 123,
            "picture" => "null.jpg",
            "items" => []
        ];

        $this->postJson("api/cases", $body, $this->headers)->assertStatus(422);
        $this->assertCount(1, NBCase::where("name", "=", $this->case->name)->get());
    }


    public function test_creating_case__validate_items_error()
    {
        $items_array = [
            [["id" => -1, "chance" => 1]],
            [["id" => $this->item1->id, "chance" => 0.5]],
            [["id" => $this->item1->id, "chance" => 1 + CaseService::$EPS]],
            [["id" => $this->item1->id, "chance" => 1 - CaseService::$EPS]],
            [
                ["id" => $this->item1->id, "chance" => 0.5],
                ["id" => $this->item1->id, "chance" => 0.5]
            ],
            [
                ["id" => $this->item1->id, "chance" => 0.5],
                ["id" => $this->item2->id, "chance" => 0.6],
            ],
            [
                ["id" => $this->item1->id, "chance" => 0.4],
                ["id" => $this->item2->id, "chance" => 0.6 + CaseService::$EPS],
            ],
            [
                ["id" => $this->item1->id, "chance" => 0.4],
                ["id" => $this->item2->id, "chance" => 0.6 - CaseService::$EPS],
            ],
        ];

        foreach ($items_array as $items)
        {
            $body = [
                "name" => "1",
                "description" => "1",
                "price" => 1000,
                "picture" => "1.jpg",
                "items" => $items
            ];

            $this->postJson("api/cases", $body, $this->headers)->assertStatus(422);
        }
    }

    public function test_creating_case_without_items()
    {
        $body = [
            "name" => "1",
            "description" => "1",
            "price" => 1000,
            "picture" => "1.jpg",
            "items" => []
        ];

        $this->postJson("api/cases", $body, $this->headers)->assertCreated();
        $this->assertDatabaseHas(NBCase::class, ["name" => $body["name"]]);
    }

    public function test_creating_case_with_items()
    {
        $body = [
            "name" => "1",
            "description" => "1",
            "price" => 1000,
            "picture" => "1.jpg",
            "items" => [
                ["id" => $this->item1->id, "chance" => 0.25],
                ["id" => $this->item2->id, "chance" => 0.75],
            ]
        ];

        $this->postJson("api/cases", $body, $this->headers)->assertCreated();
        $this->assertDatabaseHas(NBCase::class, ["name" => $body["name"]]);

        $actual = NBCase::where("name", "=", $body["name"])->first();

        foreach ($body["items"] as $item)
        {
            $this->assertDatabaseHas(CaseItem::class, [
                "case_id" => $actual->id,
                "item_id" => $item["id"],
                "chance" => $item["chance"]
            ]);
        }
    }

    private function assert_case(AssertableJson $json): AssertableJson|Matching
    {
        return $json->where("id", $this->case->id)
            ->where("name", $this->case->name)
            ->where("description", $this->case->description)
            ->where("price", $this->case->price)
            ->has("items", 1)
            ->has("items.0", fn (AssertableJson $json) => $json
                ->where("id", $this->item1->id)
                ->where("name", $this->item1->name)
                ->where("description", $this->item1->description)
                ->where("price", $this->item1->price)
                ->where("quality", $this->item1->quality)
                ->where("picture", $this->item1->picture)
            );
    }
}