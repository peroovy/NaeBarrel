<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Traits\EnumeratesValues;
use phpDocumentor\Reflection\Types\Integer;

class FilterService
{
    public function getFiltered(Collection $collection, string $filterName, string $columnName): Collection
    {
        if (key_exists($filterName, $_GET)) {
            return $collection->where($columnName, '=', $_GET[$filterName]);
        }
        return $collection;
    }

    public function manyFilters(Collection $collection, array $filters): Collection
    {
        foreach ($filters as $filterName => $columnName) {
            $collection = $this->getFiltered($collection, $filterName, $columnName);
        }
        return $collection;
    }
}
