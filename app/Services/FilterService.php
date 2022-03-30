<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use phpDocumentor\Reflection\Types\Integer;

class FilterService
{
    public function GetFiltered(Collection $collection, string $filterName, string $columnName): Collection
    {
        if (key_exists($filterName, $_GET)) {
            $value = ((int) $_GET[$filterName] == $_GET[$filterName]) ? $_GET[$filterName] : "'$_GET[$filterName]'";
            return $collection->where($columnName, '=', $value);
        }
        return $collection;
    }
}
