<?php

namespace Tests\Feature\Controllers;

use App\Enums\Permissions;
use App\Enums\Qualities;
use App\Enums\TransactionTypes;
use App\Models\CaseItem;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\NBCase;
use App\Models\Permission;
use App\Models\Quality;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Services\AuthenticationService;
use App\Services\CaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\Fluent\Concerns\Matching;
use Tests\TestCase;

class CaseControllerTest extends TestCase
{
    /**
     * @var NBCase[]
     */
    private array $cases;

    private NBCase $case;
    private NBCase $empty_case;
    private Item $item_in_case;
    private Item $item2;

    private Client $client;
    private array $client_headers;

    private UploadedFile $file;

    private Client $admin;
    private array $admin_headers;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = UploadedFile::fake()->create("test.png");

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
        $this->client_headers = [
            "Authorization" => "Bearer " . $this->client->createToken(AuthenticationService::$TOKEN_NAME)
                    ->plainTextToken
        ];

        $this->admin = Client::create([
            "login" => "admin",
            "email" => "admin@admin",
            "password" => "secret",
            "permission" => Permissions::Admin
        ]);
        $this->admin_headers = [
            "Authorization" => "Bearer " . $this->admin->createToken(AuthenticationService::$TOKEN_NAME)
                    ->plainTextToken
        ];

        foreach (Qualities::asArray() as $name => $id)
            Quality::insert(["id" => $id, "name" => $name]);

        $this->item_in_case = Item::create([
            "name" => "item",
            "description" => "description",
            "price" => 10,
            "quality" => Qualities::Common,
            "picture" => $this->file
        ]);

        $this->item2 = Item::create([
            "name" => "item2",
            "description" => "description2",
            "price" => 30,
            "quality" => Qualities::Uncommon,
            "picture" => $this->file
        ]);

        $this->cases[] = $this->case = NBCase::create([
            "name" => "case1",
            "description" => "desc1",
            "price" => 100,
            "picture" => $this->file,
        ]);

        CaseItem::insert(["case_id" => $this->case->id, "item_id" => $this->item_in_case->id, "chance" => 1]);

        $this->cases[] = $this->empty_case = NBCase::create([
            "name" => "case2",
            "description" => "desc2",
            "price" => 20,
            "picture" => $this->file,
        ]);
    }

    public function test_getting_cases()
    {
        $response = $this->get("api/cases", $this->client_headers);

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json)
        {
            return $json->has(2)
                ->first(fn (AssertableJson $json) => $this->assert_case_with_items($json));
        });
    }

    public function test_getting_case()
    {
        $this->get("api/cases/-1", $this->client_headers)->assertNotFound();

        $response = $this->get("api/cases/" . $this->case->id, $this->client_headers);

        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $this->assert_case_with_items($json));
    }

    /**
     * @dataProvider provide_bad_creating_bodies
     */
    public function test_creating_case__validation_error(array $body)
    {
        $this->postJson("api/cases", $body, $this->admin_headers)
            ->assertStatus(400);

        if (array_key_exists("items", $body))
            unset($body["items"]);

        if ($body)
            $this->assertDatabaseMissing(NBCase::class, $body);

        unlink($this->file->path());
    }

    public function provide_bad_creating_bodies(): array
    {
        $file = UploadedFile::fake()->create("test.png");

        return array(
          [["name" => "123"]],
          [["description" => "123"]],
          [["price" => 123]],
          [["picture" => "1.jpg"]],
          [["items" => []]],
          [["name" => 123, "description" => "desc", "price" => 123, "picture" => $file, "items" => []]],
          [["name" => null, "description" => "desc", "price" => 123, "picture" => $file, "items" => []]],
          [["name" => "123", "description" => 123, "price" => 123, "picture" => $file, "items" => []]],
          [["name" => "123", "description" => null, "price" => 123, "picture" => $file, "items" => []]],
          [["name" => "123", "description" => "desc", "price" => null, "picture" => $file, "items" => []]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => null, "items" => []]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => $file, "items" => 123]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => $file, "items" => "123"]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => $file, "items" => null]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => $file, "items" => [["id" => 1]]]],
          [["name" => "123", "description" => "desc", "price" => 123, "picture" => $file, "items" => [["chance" => 1.2]]]],
        );
    }

    public function test_creating_case__unique_error()
    {
        $body = [
            "name" => $this->case->name,
            "description" => "desc",
            "price" => 123,
            "picture" => $this->file,
            "items" => []
        ];

        $this->postJson("api/cases", $body, $this->admin_headers)->assertStatus(400);
        $this->assertCount(1, NBCase::where("name", "=", $this->case->name)->get());
    }


    public function test_creating_case__validate_items_error()
    {
        $items_array = [
            [["id" => -1, "chance" => 1]],
            [["id" => $this->item_in_case->id, "chance" => 0.5]],
            [["id" => $this->item_in_case->id, "chance" => 1 + CaseService::$EPS]],
            [["id" => $this->item_in_case->id, "chance" => 1 - CaseService::$EPS]],
            [
                ["id" => $this->item_in_case->id, "chance" => 0.5],
                ["id" => $this->item_in_case->id, "chance" => 0.5]
            ],
            [
                ["id" => $this->item_in_case->id, "chance" => 0.5],
                ["id" => $this->item2->id, "chance" => 0.6],
            ],
            [
                ["id" => $this->item_in_case->id, "chance" => 0.4],
                ["id" => $this->item2->id, "chance" => 0.6 + CaseService::$EPS],
            ],
            [
                ["id" => $this->item_in_case->id, "chance" => 0.4],
                ["id" => $this->item2->id, "chance" => 0.6 - CaseService::$EPS],
            ],
        ];

        foreach ($items_array as $items)
        {
            $body = [
                "name" => "1",
                "description" => "1",
                "price" => 1000,
                "picture" => $this->file,
                "items" => $items
            ];

            $this->postJson("api/cases", $body, $this->admin_headers)->assertStatus(422);
        }
    }

    public function test_creating_case_without_items()
    {
        $body = [
            "name" => "1",
            "description" => "1",
            "price" => 1000,
            "picture" => $this->file,
            "items" => []
        ];

        $this->postJson("api/cases", $body, $this->admin_headers)->assertCreated();
        $this->assertDatabaseHas(NBCase::class, ["name" => $body["name"]]);
        $this->assert_file_exists();
    }

    public function test_creating_case_with_items()
    {
        $body = [
            "name" => "1",
            "description" => "1",
            "price" => 1000,
            "picture" => $this->file,
            "items" => [
                ["id" => $this->item_in_case->id, "chance" => 0.25],
                ["id" => $this->item2->id, "chance" => 0.75],
            ]
        ];

        $this->postJson("api/cases", $body, $this->admin_headers)->assertCreated();
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
        $this->assert_file_exists();
    }

    /**
     * @dataProvider provide_bad_case_id
     */
    public function test_buying__validation_error($id)
    {
        $this->postJson("api/cases/buy", ["case_id" => $id], $this->client_headers)->assertStatus(400);
    }

    public function provide_bad_case_id(): array
    {
        return [["a"], [null], [[]]];
    }

    public function test_buying__not_found()
    {
        $this->postJson("api/cases/buy", ["case_id" => -1], $this->client_headers)->assertStatus(404);
    }

    public function test_buying__opening_error()
    {
        foreach ([$this->case->id, $this->empty_case->id] as $id)
        {
            foreach ([0, $this->case->price - 1] as $balance)
            {
                $this->client->balance = $balance;
                $this->client->save();

                $this->postJson("api/cases/buy", ["case_id" => $id], $this->client_headers)
                    ->assertStatus(422);

                $this->assertDatabaseHas(Client::class, ["login" => $this->client->login, "balance" => $balance]);
            }
        }
    }

    public function test_buying()
    {
        $expected_balance = 1;
        $this->client->balance = $this->case->price + $expected_balance;
        $this->client->save();

        $this->postJson("api/cases/buy", ["case_id" => $this->case->id], $this->client_headers)
            ->assertOk()
            ->assertJson(function (AssertableJson $json)
            {
                return $json->where("id", $this->item_in_case->id)
                    ->where("name", $this->item_in_case->name)
                    ->where("description", $this->item_in_case->description)
                    ->where("price", $this->item_in_case->price)
                    ->where("quality", $this->item_in_case->quality)
                    ->where("picture", URL::asset($this->item_in_case->picture))
                    ->etc();
            });

        $this->assertDatabaseHas(Client::class, ["login" => $this->client->login, "balance" => $expected_balance]);
        $this->assertDatabaseHas(Inventory::class, ["client_id" => $this->client->id, "item_id" => $this->item_in_case->id]);
        $this->assertDatabaseHas(Transaction::class,
            ["client_id" => $this->client->id, "type" => TransactionTypes::CaseBuying, "accrual" => $this->case->price]
        );
    }

    private function assert_case_with_items(AssertableJson $json): AssertableJson|Matching
    {
        return $json->where("id", $this->case->id)
            ->where("name", $this->case->name)
            ->where("description", $this->case->description)
            ->where("price", $this->case->price)
            ->has("items", 1)
            ->has("items.0", fn (AssertableJson $json) => $json
                ->where("id", $this->item_in_case->id)
                ->where("name", $this->item_in_case->name)
                ->where("description", $this->item_in_case->description)
                ->where("price", $this->item_in_case->price)
                ->where("quality", $this->item_in_case->quality)
                ->where("picture", URL::asset($this->item_in_case->picture))
                ->etc()
            )
            ->where("picture", URL::asset($this->case->picture));
    }

    private function assert_file_exists()
    {
        $path = "public\\uploads\\cases\\" . $this->file->hashName();

        \Storage::assertExists($path);

        \Storage::delete($path);
    }
}
