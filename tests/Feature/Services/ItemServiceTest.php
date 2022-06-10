<?php

namespace Tests\Feature;

use App\Enums\Qualities;
use App\Models\Item;
use App\Models\Quality;
use App\Services\ItemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemServiceTest extends TestCase
{
    private Item $common;
    private Item $uncommon;

    private ItemService $service;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ItemService();
        $_GET = [];

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
    }

    public function test_getting_all()
    {
        $actual = $this->service->get();

        $this->assertEquals(Item::all(), $actual);
    }

    public function test_getting_all__with_filtering()
    {
        foreach ([Qualities::Common => $this->common, Qualities::Uncommon => $this->uncommon] as $value => $obj)
        {
            $_GET["quality"] = $value;
            $actual = $this->service->get()->values();
            $this->assertCount(1, $actual);
            $this->assertEquals($obj["id"], $actual[0]["id"]);
        }
    }

    public function test_getting_all__bad_filtering()
    {
        $_GET["quality"] = -1;
        $this->assertCount(0, $this->service->get());
    }

    public function test_existing_item()
    {
        $this->assertTrue($this->service->exists($this->common->name));
        $this->assertTrue($this->service->exists($this->uncommon->name));
        $this->assertFalse($this->service->exists("unknown"));
    }

    public function test_creating_item()
    {
        $item = new Item([
            "name" => "top",
            "description" => "desc",
            "price" => 10,
            "quality" => Qualities::Common,
            "picture" => "123.jpg"
        ]);

        $actual = $this->service->create(...$item->getAttributes());

        $this->assertInstanceOf(Item::class, $actual);
        $this->assertEquals($item->description, $actual->description);
        $this->assertEquals($item->price, $actual->price);
        $this->assertEquals($item->quality, $actual->quality);
        $this->assertEquals($item->picture, $actual->picture);
        $this->assertDatabaseHas(Item::class, $item->getAttributes());
    }

    public function test_creating_item__name_does_exist()
    {
        $attr = $this->common->getAttributes();
        unset($attr["id"]);
        $this->assertNull($this->service->create(...$attr));
    }
}
