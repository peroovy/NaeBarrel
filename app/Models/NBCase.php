<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NBCase
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $price
 * @property string $picture
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CaseItem[] $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|NBCase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NBCase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NBCase query()
 * @method static \Illuminate\Database\Eloquent\Builder|NBCase whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NBCase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NBCase whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NBCase wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NBCase wherePrice($value)
 * @mixin \Eloquent
 */
class NBCase extends Model
{
    use HasFactory;

    protected $table = 'cases';

    public function items() {
        return $this->hasManyThrough(Item::class, CaseItem::class,
        'case_id', 'id', 'id', 'item_id')
            ->get();
    }
}
