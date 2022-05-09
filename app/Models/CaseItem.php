<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CaseItem
 *
 * @property int $case_id
 * @property int $item_id
 * @property float $chance
 * @method static \Illuminate\Database\Eloquent\Builder|CaseItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CaseItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CaseItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|CaseItem whereCaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CaseItem whereChance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CaseItem whereItemId($value)
 * @mixin \Eloquent
 */
class CaseItem extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = ["case_id", "item_id"];

    protected $fillable = [
        "case_id",
        "item_id",
        "chance"
    ];

    protected $table = 'case_item';

}
