<?php

namespace Tests\Feature;

use App\Enums\Permissions;
use App\Enums\Qualities;
use App\Enums\TransactionTypes;
use App\Models\CaseItem;
use App\Models\Client;
use App\Models\Item;
use App\Models\NBCase;
use App\Models\Permission;
use App\Models\Quality;
use App\Models\TransactionType;
use App\Services\CaseService;
use App\Services\ClientsService;
use App\Services\ProfileService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use phpmock\Mock;
use phpmock\MockBuilder;
use Tests\TestCase;

class CaseServiceTest extends TestCase
{
    private CaseService $service;

    private NBCase $case;
    private NBCase $unsaved_case;
    private Client $client;

    /** @var Item[] */
    private array $items;

    use RefreshDatabase;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->service = new CaseService(new ProfileService(new ClientsService(), new TransactionService()), new TransactionService());

        parent::__construct($name, $data, $dataName);
    }

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        foreach (TransactionTypes::asArray() as $name => $id)
            TransactionType::insert(["id" => $id, "name" => $name]);

        $this->client = Client::create([
            "login" => "login1",
            "email" => "email1",
            "password" => \Hash::make($this->client_password = "secret1"),
            "permission" => Permissions::User,
            "balance" => 100000
        ]);

        $this->case = NBCase::create([
            "name" => "case",
            "description" => "desc",
            "price" => 10,
            "picture" => "123.jpg",
        ]);

        foreach (Qualities::asArray() as $name => $id)
            Quality::insert(["id" => $id, "name" => $name]);

        $this->items[] = Item::create([
                "name" => "item_1",
                "description" => "item_1_description",
                "price" => 100,
                "quality" => Qualities::Common,
                "picture" => "item_1.jpg"
        ]);
        $this->items[] = Item::create([
                "name" => "item_2",
                "description" => "item_2_description",
                "price" => 50,
                "quality" => Qualities::Uncommon,
                "picture" => "item_2.jpg"
        ]);

        $this->unsaved_case = new NBCase([
            "name" => "joke",
            "description" => "desc",
            "price" => 20,
            "picture" => "234.jpg"
        ]);
    }

    public function test_existing_case(string $unknown_name = "pistol")
    {
        $this->assertTrue($this->service->caseExists($this->case->name));
        $this->assertFalse($this->service->caseExists($unknown_name));
    }

    public function test_creating_case__name_already_exists()
    {
        $actual = $this->service->createCase($this->case->name, "another", 10, "345.jpg", []);

        $this->assertNull($actual);
    }

    public function test_creating_case__without_items()
    {
        $expected = $this->unsaved_case;

        $actual = $this->service->createCase(
            $expected->name,
            $expected->description,
            $expected->price,
            $expected->picture,
            items: []
        );

        $this->assert_creating_case($expected, $actual);
        $this->assertEquals(0, CaseItem::where("case_id", "=", $actual->id)->count());
    }

    public function test_creating_case(float $chance = 0.5)
    {
        $expected = $this->unsaved_case;

        $case_items = [];
        foreach ($this->items as $item)
            $case_items[] = ["id" => $item->id, "chance" => $chance];

        $actual = $this->service->createCase($expected->name, $expected->description, $expected->price, $expected->picture, $case_items);

        $this->assert_creating_case($expected, $actual);

        $case_items = CaseItem::where("case_id", "=", $actual->id)->get()->all();
        $this->assertCount(count($this->items), $case_items);
        $this->assertEquals(
            array_map(fn(Item $item) => $item->id, $this->items),
            array_map(fn(CaseItem $case_item) => $case_item->item_id, $case_items)
        );
    }

    public function test_opening_case__without_items()
    {
        $this->unsaved_case->save();

        $this->assertNull($this->service->tryPlayRoulette($this->client, $this->unsaved_case));
    }

    public function test_opening_case()
    {
        $builder = new MockBuilder();
        $mock = $builder->setNamespace("App\Services")
            ->setName("random_int");

        $mock_thirty = $mock->setFunction(fn(int $start, int $end) => 30)->build();
        $mock_eight = $mock->setFunction(fn(int $start, int $end) => 80)->build();
        $mock_hundred = $mock->setFunction(fn(int $start, int $end) => 100)->build();

        $expected_1 = $this->items[0];
        $expected_2 = $this->items[1];

        $this->client->balance = $expected_1->price + $expected_2->price + 1;
        $this->client->save();

        CaseItem::create([
            "case_id" => $this->case->id,
            "item_id" => $expected_1->id,
            "chance" => 0.4,
        ]);
        CaseItem::create([
            "case_id" => $this->case->id,
            "item_id" => $expected_2->id,
            "chance" => 0.8,
        ]);

        $this->assert_opening_case($mock_thirty, $expected_1);
        $this->assert_opening_case($mock_eight, $expected_2);
        $this->assert_opening_case($mock_hundred, $expected_2);
    }

    private function assert_opening_case(Mock $random_int_mock, Item $expected)
    {
        $random_int_mock->enable();
        $actual = $this->service->tryPlayRoulette($this->client, $this->case);
        $random_int_mock->disable();

        $this->assertEquals($expected["id"], $actual["id"]);
    }

    private function assert_creating_case($expected, $actual)
    {
        $this->assertInstanceOf(NBCase::class, $actual);
        $this->assertDatabaseHas(NBCase::class, $actual->getAttributes());
        $this->assertEquals($expected->name, $actual->name);
        $this->assertEquals($expected->description, $actual->description);
        $this->assertEquals($expected->price, $actual->price);
        $this->assertEquals($expected->picture, $actual->picture);
    }
}
