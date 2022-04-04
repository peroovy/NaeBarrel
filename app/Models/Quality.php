<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Quality
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Item[] $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|Quality newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quality newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quality query()
 * @method static \Illuminate\Database\Eloquent\Builder|Quality whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quality whereName($value)
 * @mixin \Eloquent
 */
class Quality extends Model
{
    use HasFactory;

    protected $table = "qualities";

    public function items() {
        return $this->hasMany(Item::class, 'quality');
    }
}
