<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class ItemService
{
    public function __construct(private FileService $fileService)
    {
    }

    public function get(): Collection
    {
        $filter = new FilterService();
        return $filter->getFiltered(Item::all(), 'quality', 'quality');
    }

    public function exists(string $name): bool {
        return Item::whereName($name)->exists();
    }

    public function create(string $name, string $description, string $price, int $quality, UploadedFile $picture): Item | null {
        if ($this->exists($name)) {
            return null;
        }

        $uri = $this->fileService->upload($picture, "items");

        return Item::create([
            "name" => $name,
            "description" => $description,
            "price" => $price,
            "quality" => $quality,
            "picture" => $uri
        ]);
    }
}
