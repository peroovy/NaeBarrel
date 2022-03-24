<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NBCase extends Model
{
    use HasFactory;

    protected $table = 'cases';

    public function items() {
        return $this->hasMany(CaseItem::class, 'case_id')
            ->select('items.*', 'case_item.chance')
            ->join('items', 'case_item.item_id', '=', 'items.id');
    }
}
